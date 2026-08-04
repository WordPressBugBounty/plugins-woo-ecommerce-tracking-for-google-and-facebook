<?php
/**
 * GA4 Measurement Protocol backend tracking.
 *
 * Sends purchase-related events server-side when Backend Tracking is enabled.
 *
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/includes
 * @since      3.8.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AET_GA4_Measurement_Protocol
 */
class AET_GA4_Measurement_Protocol {

	const AS_HOOK          = 'aet_mp_send_event';
	const META_PURCHASE    = '_aet_backend_purchase_sent';
	const META_CLIENT_ID   = '_aet_ga_client_id';
	const META_REFUND      = '_aet_backend_refund_sent';
	const COOKIE_CLIENT_ID = 'aet_ga_client_id';

	/**
	 * Singleton instance.
	 *
	 * @var AET_GA4_Measurement_Protocol|null
	 */
	private static $instance = null;

	/**
	 * Get singleton.
	 *
	 * @return AET_GA4_Measurement_Protocol
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	public function init() {
		add_action( self::AS_HOOK, array( $this, 'process_queued_event' ), 10, 1 );
		add_action( 'aet_mp_send_event_cron', array( $this, 'process_queued_event' ), 10, 1 );

		// Capture GA client_id before checkout completes (for MP association).
		add_action( 'wp_footer', array( $this, 'maybe_output_client_id_capture' ), 99 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'store_client_id_on_order' ), 20, 3 );
		add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'store_client_id_on_wc_order' ), 20, 1 );
	}

	/**
	 * Whether Measurement Protocol backend tracking is fully configured and enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$settings = $this->get_settings();
		return (
			'on' === $settings['mp_backend_tracking']
			&& ! empty( $settings['mp_measurement_id'] )
			&& ! empty( $settings['mp_api_secret'] )
		);
	}

	/**
	 * Get MP-related settings from aet_et_tracking_settings.
	 *
	 * @return array
	 */
	public function get_settings() {
		$raw = json_decode( (string) get_option( 'aet_et_tracking_settings', '{}' ), true );
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		$measurement_id = ! empty( $raw['mp_measurement_id'] ) ? $raw['mp_measurement_id'] : '';
		if ( empty( $measurement_id ) && ! empty( $raw['manually_et_px_ver_4'] ) ) {
			$measurement_id = $raw['manually_et_px_ver_4'];
		}

		return array(
			'mp_backend_tracking' => isset( $raw['mp_backend_tracking'] ) ? $raw['mp_backend_tracking'] : 'off',
			'mp_measurement_id'   => sanitize_text_field( $measurement_id ),
			'mp_api_secret'       => isset( $raw['mp_api_secret'] ) ? (string) $raw['mp_api_secret'] : '',
			'mp_debug_mode'       => isset( $raw['mp_debug_mode'] ) ? $raw['mp_debug_mode'] : 'off',
			'user_id_tracking'    => isset( $raw['user_id_tracking'] ) ? $raw['user_id_tracking'] : 'off',
		);
	}

	/**
	 * Queue and send a purchase event for an order via Measurement Protocol.
	 *
	 * @param int $order_id Order ID.
	 * @return bool True if queued or already sent.
	 */
	public function queue_purchase_for_order( $order_id ) {
		$order_id = absint( $order_id );
		if ( ! $order_id || ! $this->is_enabled() ) {
			return false;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			return false;
		}

		if ( 'yes' === $order->get_meta( self::META_PURCHASE, true ) ) {
			return true;
		}

		// Also respect legacy browser-tracked marker to avoid double hits after mode switch.
		$browser_sent = $order->get_meta( 'aet_ga_placed_order_success', true );
		if ( 'true' === $browser_sent || true === $browser_sent ) {
			$order->update_meta_data( self::META_PURCHASE, 'yes' );
			$order->save();
			return true;
		}

		if ( ! function_exists( 'aet_get_purchase_tracking_data' ) ) {
			return false;
		}

		$data = aet_get_purchase_tracking_data( $order_id );
		if ( ! $data || empty( $data['transaction_id'] ) ) {
			return false;
		}

		$params = array(
			'transaction_id' => (string) $data['transaction_id'],
			'currency'       => isset( $data['currency'] ) ? (string) $data['currency'] : get_woocommerce_currency(),
			'value'          => isset( $data['value'] ) ? (float) $data['value'] : 0,
			'tax'            => isset( $data['tax'] ) ? (float) $data['tax'] : 0,
			'shipping'       => isset( $data['shipping'] ) ? (float) $data['shipping'] : 0,
			'items'          => isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array(),
		);

		if ( ! empty( $data['coupons_list'] ) ) {
			$params['coupon'] = (string) $data['coupons_list'];
		}

		$client_id = $this->get_or_create_client_id( $order );
		$user_id   = $this->get_user_id_for_order( $order );

		// Mark before queue to prevent duplicate scheduling on refresh.
		$order->update_meta_data( self::META_PURCHASE, 'yes' );
		$order->update_meta_data( 'aet_ga_placed_order_success', 'true' );
		$order->update_meta_data( self::META_CLIENT_ID, $client_id );
		$order->save();

		$this->enqueue_event(
			array(
				'event_name' => 'purchase',
				'params'     => $params,
				'client_id'  => $client_id,
				'user_id'    => $user_id,
				'order_id'   => $order_id,
			)
		);

		return true;
	}

	/**
	 * Queue a refund event for an order via Measurement Protocol.
	 *
	 * @param int $order_id Order ID.
	 * @return bool
	 */
	public function queue_refund_for_order( $order_id ) {
		$order_id = absint( $order_id );
		if ( ! $order_id || ! $this->is_enabled() ) {
			return false;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			return false;
		}

		if ( 'yes' === $order->get_meta( self::META_REFUND, true ) ) {
			return true;
		}

		$transaction_id = $order->get_order_number();
		if ( empty( $transaction_id ) ) {
			$transaction_id = (string) $order_id;
		}

		$params = array(
			'transaction_id' => (string) $transaction_id,
			'value'          => (float) $order->get_total_refunded(),
			'currency'       => $order->get_currency(),
		);

		$client_id = $this->get_or_create_client_id( $order );
		$user_id   = $this->get_user_id_for_order( $order );

		$order->update_meta_data( self::META_REFUND, 'yes' );
		$order->save();

		$this->enqueue_event(
			array(
				'event_name' => 'refund',
				'params'     => $params,
				'client_id'  => $client_id,
				'user_id'    => $user_id,
				'order_id'   => $order_id,
			)
		);

		return true;
	}

	/**
	 * Schedule async send (Action Scheduler preferred).
	 *
	 * @param array $payload Event payload.
	 */
	public function enqueue_event( $payload ) {
		if ( empty( $payload['event_name'] ) || empty( $payload['client_id'] ) ) {
			return;
		}

		if ( function_exists( 'as_enqueue_async_action' ) ) {
			as_enqueue_async_action( self::AS_HOOK, array( $payload ), 'aet-measurement-protocol' );
			return;
		}

		if ( function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action( time() + 5, self::AS_HOOK, array( $payload ), 'aet-measurement-protocol' );
			return;
		}

		// Fallback: WP-Cron single event (may run later depending on traffic).
		$args = array( $payload );
		if ( ! wp_next_scheduled( 'aet_mp_send_event_cron', $args ) ) {
			wp_schedule_single_event( time() + 5, 'aet_mp_send_event_cron', $args );
		}
	}

	/**
	 * Process a queued MP event (Action Scheduler / cron callback).
	 *
	 * @param array $payload Event payload.
	 */
	public function process_queued_event( $payload ) {
		if ( ! is_array( $payload ) || empty( $payload['event_name'] ) ) {
			return;
		}

		$this->send_event(
			$payload['event_name'],
			isset( $payload['params'] ) && is_array( $payload['params'] ) ? $payload['params'] : array(),
			isset( $payload['client_id'] ) ? $payload['client_id'] : '',
			isset( $payload['user_id'] ) ? $payload['user_id'] : ''
		);
	}

	/**
	 * Send an event to GA4 Measurement Protocol.
	 *
	 * @param string $event_name Event name.
	 * @param array  $params     Event params.
	 * @param string $client_id  Client ID.
	 * @param string $user_id    Optional user ID.
	 * @return true|WP_Error
	 */
	public function send_event( $event_name, $params, $client_id, $user_id = '' ) {
		$settings = $this->get_settings();
		if ( empty( $settings['mp_measurement_id'] ) || empty( $settings['mp_api_secret'] ) || empty( $client_id ) ) {
			return new WP_Error( 'aet_mp_missing_config', __( 'Measurement Protocol is not configured.', 'advance-ecommerce-tracking' ) );
		}

		$body = array(
			'client_id' => (string) $client_id,
			'events'    => array(
				array(
					'name'   => sanitize_key( $event_name ),
					'params' => $this->sanitize_event_params( $params ),
				),
			),
		);

		if ( ! empty( $user_id ) ) {
			$body['user_id'] = (string) $user_id;
		}

		$query = array(
			'measurement_id' => $settings['mp_measurement_id'],
			'api_secret'     => $settings['mp_api_secret'],
		);

		// Always use the live collect endpoint so events are recorded in GA4.
		// Debug Mode only controls logging (and optional DebugView flag below).
		$url = add_query_arg( $query, 'https://www.google-analytics.com/mp/collect' );

		if ( 'on' === $settings['mp_debug_mode'] ) {
			$body['events'][0]['params']['debug_mode'] = true;
		}

		$response = wp_remote_post(
			$url,
			array(
				// phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout -- GA4 MP can be slow on shared hosts; used for async purchase/refund delivery.
				'timeout' => 15,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( 'on' === $settings['mp_debug_mode'] ) {
			$this->log_debug(
				array(
					'url'      => preg_replace( '/api_secret=[^&]+/', 'api_secret=***', $url ),
					'body'     => $body,
					'response' => is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_body( $response ),
					'code'     => is_wp_error( $response ) ? 0 : wp_remote_retrieve_response_code( $response ),
				)
			);
		}

		if ( is_wp_error( $response ) ) {
			if ( 'on' === $settings['mp_debug_mode'] ) {
				$this->log_debug( array( 'error' => $response->get_error_message() ) );
			}
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			$error = new WP_Error(
				'aet_mp_http_error',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Measurement Protocol HTTP error: %d', 'advance-ecommerce-tracking' ),
					$code
				)
			);
			if ( 'on' === $settings['mp_debug_mode'] ) {
				$this->log_debug( array( 'error' => $error->get_error_message(), 'code' => $code ) );
			}
			return $error;
		}

		return true;
	}

	/**
	 * Output small JS to persist GA client_id for MP association.
	 */
	public function maybe_output_client_id_capture() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		if ( ! function_exists( 'is_checkout' ) || ( ! is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) ) {
			return;
		}
		?>
		<script>
		(function () {
			try {
				var match = document.cookie.match(/(?:^|; )_ga=([^;]*)/);
				if (!match) { return; }
				var parts = decodeURIComponent(match[1]).split('.');
				if (parts.length < 4) { return; }
				var cid = parts[2] + '.' + parts[3];
				var maxAge = 60 * 60 * 24 * 400;
				document.cookie = '<?php echo esc_js( self::COOKIE_CLIENT_ID ); ?>=' + encodeURIComponent(cid) + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
			} catch (e) {}
		})();
		</script>
		<?php
	}

	/**
	 * Store client_id on classic checkout order.
	 *
	 * @param int      $order_id Order ID.
	 * @param array    $posted   Posted data.
	 * @param WC_Order $order    Order object.
	 */
	public function store_client_id_on_order( $order_id, $posted, $order ) {
		unset( $posted );
		if ( $order instanceof WC_Order ) {
			$this->store_client_id_on_wc_order( $order );
			return;
		}
		$wc_order = wc_get_order( $order_id );
		if ( $wc_order ) {
			$this->store_client_id_on_wc_order( $wc_order );
		}
	}

	/**
	 * Store client_id on order from cookie / generated UUID.
	 *
	 * @param WC_Order $order Order object.
	 */
	public function store_client_id_on_wc_order( $order ) {
		if ( ! $this->is_enabled() || ! $order || ! is_a( $order, 'WC_Order' ) ) {
			return;
		}
		if ( $order->get_meta( self::META_CLIENT_ID, true ) ) {
			return;
		}
		$client_id = $this->read_client_id_from_request();
		if ( empty( $client_id ) ) {
			$client_id = wp_generate_uuid4();
		}
		$order->update_meta_data( self::META_CLIENT_ID, sanitize_text_field( $client_id ) );
		$order->save();
	}

	/**
	 * Resolve client_id for an order.
	 *
	 * @param WC_Order $order Order.
	 * @return string
	 */
	public function get_or_create_client_id( $order ) {
		$existing = $order->get_meta( self::META_CLIENT_ID, true );
		if ( ! empty( $existing ) ) {
			return (string) $existing;
		}

		$from_request = $this->read_client_id_from_request();
		if ( ! empty( $from_request ) ) {
			return $from_request;
		}

		return wp_generate_uuid4();
	}

	/**
	 * Read client_id cookie (plugin cookie or _ga).
	 *
	 * @return string
	 */
	private function read_client_id_from_request() {
		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended, WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE -- MP needs _ga / client_id cookie when browser hit was missed.
		if ( ! empty( $_COOKIE[ self::COOKIE_CLIENT_ID ] ) ) {
			return sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_CLIENT_ID ] ) );
		}
		if ( ! empty( $_COOKIE['_ga'] ) ) {
			$parts = explode( '.', sanitize_text_field( wp_unslash( $_COOKIE['_ga'] ) ) );
			if ( count( $parts ) >= 4 ) {
				return $parts[2] . '.' . $parts[3];
			}
		}
		// phpcs:enable
		return '';
	}

	/**
	 * Optional user_id when setting enabled.
	 *
	 * @param WC_Order $order Order.
	 * @return string
	 */
	private function get_user_id_for_order( $order ) {
		$settings = $this->get_settings();
		if ( 'on' !== $settings['user_id_tracking'] ) {
			return '';
		}
		$user_id = $order->get_user_id();
		return $user_id ? (string) $user_id : '';
	}

	/**
	 * Sanitize event params recursively for JSON body.
	 *
	 * @param array $params Params.
	 * @return array
	 */
	private function sanitize_event_params( $params ) {
		$clean = array();
		foreach ( (array) $params as $key => $value ) {
			$key = sanitize_key( $key );
			if ( 'items' === $key && is_array( $value ) ) {
				$items = array();
				foreach ( $value as $item ) {
					if ( ! is_array( $item ) ) {
						continue;
					}
					$clean_item = array();
					foreach ( $item as $ik => $iv ) {
						$ik = sanitize_key( $ik );
						if ( is_numeric( $iv ) ) {
							$clean_item[ $ik ] = 0 + $iv;
						} else {
							$clean_item[ $ik ] = sanitize_text_field( (string) $iv );
						}
					}
					$items[] = $clean_item;
				}
				$clean[ $key ] = $items;
				continue;
			}
			if ( is_bool( $value ) ) {
				$clean[ $key ] = $value;
			} elseif ( is_numeric( $value ) ) {
				$clean[ $key ] = 0 + $value;
			} else {
				$clean[ $key ] = sanitize_text_field( (string) $value );
			}
		}
		return $clean;
	}

	/**
	 * Debug logger (only when mp_debug_mode is on).
	 *
	 * @param mixed $data Data to log.
	 */
	private function log_debug( $data ) {
		if ( function_exists( 'wc_get_logger' ) ) {
			$logger = wc_get_logger();
			$logger->debug( wp_json_encode( $data ), array( 'source' => 'aet-measurement-protocol' ) );
			return;
		}
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[AET Measurement Protocol] ' . wp_json_encode( $data ) );
	}
}
