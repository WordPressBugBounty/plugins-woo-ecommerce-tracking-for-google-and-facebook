<?php
/**
 * GA4 Analytics Overview Dashboard (Premium).
 *
 * Combined: OAuth helper, Data API client, and admin dashboard controller.
 *
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/includes
 * @since      3.8.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AET_GA4_OAuth
 *
 * Connect uses DotStore hosted OAuth (Client Secret stays on DotStore).
 */
class AET_GA4_OAuth {

	const ACCOUNT_SUMMARIES_URL = 'https://analyticsadmin.googleapis.com/v1beta/accountSummaries';
	const SCOPE                 = 'openid email https://www.googleapis.com/auth/analytics.readonly';
	const STATE_TRANSIENT       = 'aet_ga4_oauth_state_';
	const PROXY_DEFAULT_BASE    = 'https://pluginsdemo.thedotstore.com/v2/ga4-dashboard';

	/**
	 * DotStore GA4 dashboard OAuth proxy base URL (no trailing slash).
	 *
	 * @return string
	 */
	public static function get_proxy_base_url() {
		$base = self::PROXY_DEFAULT_BASE;

		/**
		 * Filter DotStore GA4 OAuth proxy base URL.
		 *
		 * @param string $base Base URL without trailing slash.
		 */
		$base = (string) apply_filters( 'aet_ga4_oauth_proxy_base', $base );
		$base = untrailingslashit( esc_url_raw( $base ) );

		if ( 0 !== strpos( $base, 'https://' ) ) {
			return self::PROXY_DEFAULT_BASE;
		}

		return $base;
	}

	/**
	 * Whether OAuth connect is available (hosted proxy is always ready).
	 *
	 * @return bool
	 */
	public static function is_app_configured() {
		return true;
	}

	/**
	 * Build Connect with Google URL (DotStore hosted OAuth).
	 *
	 * @param string $return_page Page slug to return to after connect.
	 * @return string
	 */
	public static function get_auth_url( $return_page = 'aet-dashboard' ) {
		return self::get_hosted_connect_url( $return_page );
	}

	/**
	 * Build DotStore hosted connect URL (Client Secret never leaves DotStore).
	 *
	 * @param string $return_page Page slug.
	 * @return string
	 */
	public static function get_hosted_connect_url( $return_page = 'aet-dashboard' ) {
		$return_page = sanitize_key( $return_page );
		if ( 'aet-dashboard' !== $return_page && 'aet-et-settings' !== $return_page ) {
			$return_page = 'aet-dashboard';
		}

		$state = wp_generate_password( 32, false );
		set_transient( self::STATE_TRANSIENT . get_current_user_id(), $state, 15 * MINUTE_IN_SECONDS );

		$return_url = add_query_arg(
			array(
				'page' => $return_page,
			),
			admin_url( 'admin.php' )
		);

		$args = array(
			'extra_url' => rawurlencode( base64_encode( $return_url ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Matches existing DotStore wizard contract.
			'chk'       => base64_encode( 'refer' ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Matches existing DotStore wizard contract.
			'state'     => $state,
			'site'      => rawurlencode( home_url( '/' ) ),
		);

		return add_query_arg( $args, self::get_proxy_base_url() . '/' );
	}

	/**
	 * Process hosted OAuth handoff return on the dashboard page.
	 *
	 * Expects one-time handoff code (never refresh tokens in the query string).
	 *
	 * @return array|WP_Error|null Null when no handoff params present.
	 */
	public static function process_hosted_handoff() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'aet_ga4_oauth', __( 'Permission denied.', 'advance-ecommerce-tracking' ) );
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- OAuth return; validated via stored state transient.
		$handoff = isset( $_GET['aet_ga4_handoff'] ) ? sanitize_text_field( wp_unslash( $_GET['aet_ga4_handoff'] ) ) : '';
		$state   = isset( $_GET['aet_ga4_state'] ) ? sanitize_text_field( wp_unslash( $_GET['aet_ga4_state'] ) ) : '';
		$error   = isset( $_GET['aet_ga4_oauth_error'] ) ? sanitize_text_field( wp_unslash( $_GET['aet_ga4_oauth_error'] ) ) : '';
		// phpcs:enable

		if ( '' !== $error ) {
			return new WP_Error( 'aet_ga4_oauth', $error );
		}

		if ( '' === $handoff ) {
			return null;
		}

		if ( '' === $state || ! preg_match( '/^[A-Za-z0-9_-]{16,64}$/', $handoff ) ) {
			return new WP_Error( 'aet_ga4_oauth', __( 'Invalid OAuth handoff.', 'advance-ecommerce-tracking' ) );
		}

		$expected = get_transient( self::STATE_TRANSIENT . get_current_user_id() );
		delete_transient( self::STATE_TRANSIENT . get_current_user_id() );
		if ( ! $expected || ! hash_equals( (string) $expected, (string) $state ) ) {
			return new WP_Error( 'aet_ga4_oauth', __( 'OAuth state mismatch. Please try connecting again.', 'advance-ecommerce-tracking' ) );
		}

		$token_data = self::redeem_hosted_handoff( $handoff, $state );
		if ( is_wp_error( $token_data ) ) {
			return $token_data;
		}

		$refresh = isset( $token_data['refresh_token'] ) ? (string) $token_data['refresh_token'] : '';
		$access  = isset( $token_data['access_token'] ) ? (string) $token_data['access_token'] : '';

		if ( '' === $refresh ) {
			$existing = AET_GA4_Data_API::get_credentials();
			$refresh  = ! empty( $existing['refresh_token'] ) ? $existing['refresh_token'] : '';
		}

		if ( '' === $refresh || '' === $access ) {
			return new WP_Error(
				'aet_ga4_oauth',
				__( 'No tokens returned from DotStore OAuth handoff. Please try connecting again.', 'advance-ecommerce-tracking' )
			);
		}

		$email = isset( $token_data['email'] ) ? sanitize_email( (string) $token_data['email'] ) : '';
		if ( '' === $email ) {
			$email = self::fetch_user_email( $access );
		}

		AET_GA4_Data_API::save_oauth_tokens(
			$refresh,
			$access,
			isset( $token_data['expires_in'] ) ? (int) $token_data['expires_in'] : 3600,
			$email,
			'hosted'
		);

		$properties = self::list_ga4_properties( $access );
		if ( is_wp_error( $properties ) ) {
			return $properties;
		}

		if ( 1 === count( $properties ) ) {
			AET_GA4_Data_API::save_property_id( $properties[0]['id'], isset( $properties[0]['name'] ) ? $properties[0]['name'] : '' );
		} else {
			set_transient( 'aet_ga4_oauth_properties_' . get_current_user_id(), $properties, 30 * MINUTE_IN_SECONDS );
		}

		return array(
			'return_page' => 'aet-dashboard',
			'properties'  => $properties,
			'auto_picked' => ( 1 === count( $properties ) ),
		);
	}

	/**
	 * Redeem one-time handoff code from DotStore (server-to-server).
	 *
	 * @param string $handoff Handoff code.
	 * @param string $state   State token.
	 * @return array|WP_Error
	 */
	public static function redeem_hosted_handoff( $handoff, $state ) {
		$url = self::get_proxy_base_url() . '/redeem.php';

		$response = wp_remote_post(
			$url,
			array(
				// phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout -- OAuth handoff redeem can exceed 5s.
				'timeout' => 20,
				'headers' => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'handoff'  => (string) $handoff,
						'state'    => (string) $state,
						'site'     => home_url( '/' ),
						'plugin'   => 'advance-ecommerce-tracking',
						'version'  => defined( 'AET_VERSION' ) ? AET_VERSION : '',
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 || ! is_array( $data ) ) {
			$message = __( 'Unable to complete Google connection via DotStore.', 'advance-ecommerce-tracking' );
			if ( is_array( $data ) && ! empty( $data['message'] ) && is_string( $data['message'] ) ) {
				$message = sanitize_text_field( $data['message'] );
			}
			return new WP_Error( 'aet_ga4_oauth', $message );
		}

		if ( empty( $data['access_token'] ) ) {
			return new WP_Error( 'aet_ga4_oauth', __( 'DotStore handoff did not return an access token.', 'advance-ecommerce-tracking' ) );
		}

		return $data;
	}

	/**
	 * Refresh access token via DotStore proxy (Client Secret stays server-side).
	 *
	 * @param string $refresh_token Refresh token.
	 * @return array|WP_Error
	 */
	public static function refresh_access_token_via_proxy( $refresh_token ) {
		$url = self::get_proxy_base_url() . '/refresh.php';

		$response = wp_remote_post(
			$url,
			array(
				// phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout -- OAuth token refresh can exceed 5s.
				'timeout' => 20,
				'headers' => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'refresh_token' => (string) $refresh_token,
						'site'          => home_url( '/' ),
						'plugin'        => 'advance-ecommerce-tracking',
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['access_token'] ) ) {
			$message = __( 'Failed to refresh Google access token. Please reconnect.', 'advance-ecommerce-tracking' );
			if ( ! empty( $data['message'] ) && is_string( $data['message'] ) ) {
				$message = sanitize_text_field( $data['message'] );
			} elseif ( ! empty( $data['error_description'] ) ) {
				$message = sanitize_text_field( (string) $data['error_description'] );
			}
			return new WP_Error( 'aet_ga4_oauth', $message );
		}

		return $data;
	}

	/**
	 * Refresh access token via DotStore proxy.
	 *
	 * @param string $refresh_token Refresh token.
	 * @return array|WP_Error
	 */
	public static function refresh_access_token( $refresh_token ) {
		return self::refresh_access_token_via_proxy( $refresh_token );
	}

	/**
	 * List GA4 properties the user can access.
	 *
	 * @param string $access_token Access token.
	 * @return array|WP_Error List of { id, name, account }.
	 */
	public static function list_ga4_properties( $access_token = '' ) {
		if ( '' === $access_token ) {
			$token = AET_GA4_Data_API::get_access_token_public();
			if ( is_wp_error( $token ) ) {
				return $token;
			}
			$access_token = $token;
		}

		$response = wp_remote_get(
			self::ACCOUNT_SUMMARIES_URL . '?pageSize=200',
			array(
				// phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout -- Account Summaries listing can be slow for large Google accounts.
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 ) {
			$message = __( 'Unable to list GA4 properties.', 'advance-ecommerce-tracking' );
			if ( ! empty( $data['error']['message'] ) ) {
				$message = $data['error']['message'];
			}
			return new WP_Error( 'aet_ga4_oauth', $message );
		}

		$list = array();
		$summaries = isset( $data['accountSummaries'] ) ? $data['accountSummaries'] : array();
		foreach ( $summaries as $account ) {
			$account_name = isset( $account['displayName'] ) ? $account['displayName'] : '';
			$props        = isset( $account['propertySummaries'] ) ? $account['propertySummaries'] : array();
			foreach ( $props as $prop ) {
				$resource = isset( $prop['property'] ) ? $prop['property'] : '';
				// property is like "properties/123456789"
				$id = preg_replace( '/\D/', '', (string) $resource );
				if ( '' === $id ) {
					continue;
				}
				$list[] = array(
					'id'      => $id,
					'name'    => isset( $prop['displayName'] ) ? $prop['displayName'] : $id,
					'account' => $account_name,
				);
			}
		}

		return $list;
	}

	/**
	 * Best-effort Google account email via tokeninfo.
	 *
	 * @param string $access_token Access token.
	 * @return string
	 */
	public static function fetch_user_email( $access_token ) {
		if ( '' === $access_token ) {
			return '';
		}
		$response = wp_remote_get(
			'https://www.googleapis.com/oauth2/v3/userinfo',
			array(
				// phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout -- Google userinfo lookup can exceed 5s.
				'timeout' => 15,
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
			)
		);
		if ( is_wp_error( $response ) ) {
			return '';
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		// analytics.readonly alone may not return email; optional.
		return ! empty( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
	}

	/**
	 * Pending properties for picker UI.
	 *
	 * @return array
	 */
	public static function get_pending_properties() {
		$list = get_transient( 'aet_ga4_oauth_properties_' . get_current_user_id() );
		return is_array( $list ) ? $list : array();
	}

	/**
	 * Clear pending property list.
	 */
	public static function clear_pending_properties() {
		delete_transient( 'aet_ga4_oauth_properties_' . get_current_user_id() );
	}
}

/**
 * Class AET_GA4_Data_API
 */
class AET_GA4_Data_API {

	const OPTION_KEY      = 'aet_ga4_dashboard_credentials';
	const TOKEN_TRANSIENT = 'aet_ga4_oauth_access_token';
	const CACHE_TTL       = 600;
	const DATA_API_URL    = 'https://analyticsdata.googleapis.com/v1beta/';

	/**
	 * Whether OAuth + property are configured.
	 *
	 * @return bool
	 */
	public static function is_connected() {
		$creds = self::get_credentials();
		return ( ! empty( $creds['property_id'] ) && ! empty( $creds['refresh_token'] ) );
	}

	/**
	 * Whether Google account is linked (tokens present), even before property pick.
	 *
	 * @return bool
	 */
	public static function is_google_linked() {
		$creds = self::get_credentials();
		return ! empty( $creds['refresh_token'] );
	}

	/**
	 * Get stored credentials (decrypted).
	 *
	 * @return array
	 */
	public static function get_credentials() {
		$raw = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		$refresh = '';
		if ( ! empty( $raw['refresh_token_enc'] ) ) {
			$refresh = self::decrypt_value( $raw['refresh_token_enc'] );
		}

		return array(
			'property_id'   => isset( $raw['property_id'] ) ? preg_replace( '/\D/', '', (string) $raw['property_id'] ) : '',
			'property_name' => isset( $raw['property_name'] ) ? sanitize_text_field( $raw['property_name'] ) : '',
			'google_email'  => isset( $raw['google_email'] ) ? sanitize_email( $raw['google_email'] ) : '',
			'refresh_token' => is_string( $refresh ) ? $refresh : '',
			'auth_mode'     => isset( $raw['auth_mode'] ) ? sanitize_key( $raw['auth_mode'] ) : '',
		);
	}

	/**
	 * Public status for UI (no secrets).
	 *
	 * @return array
	 */
	public static function get_status() {
		$creds = self::get_credentials();
		return array(
			'connected'     => self::is_connected(),
			'google_linked' => self::is_google_linked(),
			'property_id'   => $creds['property_id'],
			'property_name' => $creds['property_name'],
			'google_email'  => $creds['google_email'],
			'oauth_ready'   => class_exists( 'AET_GA4_OAuth' ) ? AET_GA4_OAuth::is_app_configured() : false,
		);
	}

	/**
	 * Save OAuth tokens after connect.
	 *
	 * @param string $refresh_token Refresh token.
	 * @param string $access_token  Access token.
	 * @param int    $expires_in    Expiry seconds.
	 * @param string $google_email  Optional email.
	 * @param string $auth_mode     hosted|manual.
	 */
	public static function save_oauth_tokens( $refresh_token, $access_token, $expires_in = 3600, $google_email = '', $auth_mode = 'manual' ) {
		$raw = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}

		$auth_mode = sanitize_key( $auth_mode );
		if ( ! in_array( $auth_mode, array( 'hosted', 'manual' ), true ) ) {
			$auth_mode = 'manual';
		}

		$raw['refresh_token_enc'] = self::encrypt_value( (string) $refresh_token );
		$raw['auth_mode']         = $auth_mode;
		if ( '' !== $google_email ) {
			$raw['google_email'] = sanitize_email( $google_email );
		}
		// Clear legacy service-account fields if present.
		unset( $raw['private_key_enc'], $raw['client_email'], $raw['project_id'] );

		update_option( self::OPTION_KEY, $raw, false );

		$expires = max( 60, (int) $expires_in - 60 );
		set_transient( self::TOKEN_TRANSIENT, (string) $access_token, $expires );
		self::clear_report_cache();
	}

	/**
	 * Update property ID (and optional name).
	 *
	 * @param string $property_id   Property ID.
	 * @param string $property_name Display name.
	 * @return true|WP_Error
	 */
	public static function save_property_id( $property_id, $property_name = '' ) {
		$property_id = preg_replace( '/\D/', '', (string) $property_id );
		if ( empty( $property_id ) ) {
			return new WP_Error( 'aet_ga4_property', __( 'GA4 Property ID is required.', 'advance-ecommerce-tracking' ) );
		}

		$raw = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		$raw['property_id'] = $property_id;
		if ( '' !== $property_name ) {
			$raw['property_name'] = sanitize_text_field( $property_name );
		}
		update_option( self::OPTION_KEY, $raw, false );
		self::clear_report_cache();

		if ( class_exists( 'AET_GA4_OAuth' ) ) {
			AET_GA4_OAuth::clear_pending_properties();
		}

		return true;
	}

	/**
	 * Disconnect / clear credentials.
	 */
	public static function disconnect() {
		delete_option( self::OPTION_KEY );
		delete_option( 'aet_ga4_oauth_app' );
		delete_transient( self::TOKEN_TRANSIENT );
		self::clear_report_cache();
		if ( class_exists( 'AET_GA4_OAuth' ) ) {
			AET_GA4_OAuth::clear_pending_properties();
		}
	}

	/**
	 * Test connection with a lightweight report.
	 *
	 * @return true|WP_Error
	 */
	public static function test_connection() {
		if ( ! self::is_connected() ) {
			return new WP_Error( 'aet_ga4_not_connected', __( 'Connect with Google and select a GA4 property first.', 'advance-ecommerce-tracking' ) );
		}

		$result = self::run_report(
			array(
				array( 'name' => 'sessions' ),
			),
			array(),
			array(
				'startDate' => '7daysAgo',
				'endDate'   => 'today',
			),
			0,
			false
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	/**
	 * Public access-token getter for Admin API helpers.
	 *
	 * @return string|WP_Error
	 */
	public static function get_access_token_public() {
		return self::get_access_token();
	}

	/**
	 * Build full overview payload for the dashboard UI.
	 *
	 * @param string $start_date   YYYY-MM-DD.
	 * @param string $end_date     YYYY-MM-DD.
	 * @param string $cmp_start    Comparison start YYYY-MM-DD.
	 * @param string $cmp_end      Comparison end YYYY-MM-DD.
	 * @return array|WP_Error
	 */
	public static function get_overview( $start_date, $end_date, $cmp_start, $cmp_end ) {
		if ( ! self::is_connected() ) {
			return new WP_Error( 'aet_ga4_not_connected', __( 'Dashboard credentials are not configured.', 'advance-ecommerce-tracking' ) );
		}

		$cache_key = 'aet_ga4_ov_' . md5( wp_json_encode( array( $start_date, $end_date, $cmp_start, $cmp_end, self::get_credentials()['property_id'] ) ) );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$current = array(
			'startDate' => $start_date,
			'endDate'   => $end_date,
		);
		$compare = array(
			'startDate' => $cmp_start,
			'endDate'   => $cmp_end,
		);

		$kpis = self::fetch_kpis( $current, $compare );
		if ( is_wp_error( $kpis ) ) {
			return $kpis;
		}

		$new_vs_returning = self::fetch_new_vs_returning( $current );
		if ( is_wp_error( $new_vs_returning ) ) {
			return $new_vs_returning;
		}

		$devices = self::fetch_devices( $current );
		if ( is_wp_error( $devices ) ) {
			return $devices;
		}

		$heatmap = self::fetch_heatmap( $current );
		if ( is_wp_error( $heatmap ) ) {
			return $heatmap;
		}

		$pages = self::fetch_pages( $current );
		if ( is_wp_error( $pages ) ) {
			return $pages;
		}

		$channels = self::fetch_channels( $current );
		if ( is_wp_error( $channels ) ) {
			return $channels;
		}

		$data = array(
			'kpis'             => $kpis,
			'new_vs_returning' => $new_vs_returning,
			'devices'          => $devices,
			'heatmap'          => $heatmap,
			'pages'            => $pages,
			'channels'         => $channels,
		);

		set_transient( $cache_key, $data, self::CACHE_TTL );

		return $data;
	}

	/**
	 * KPI cards + sparklines + comparison.
	 *
	 * @param array $current Current date range.
	 * @param array $compare Comparison date range.
	 * @return array|WP_Error
	 */
	private static function fetch_kpis( $current, $compare ) {
		$metrics = array(
			array( 'name' => 'sessions' ),
			array( 'name' => 'screenPageViews' ),
			array( 'name' => 'averageSessionDuration' ),
			array( 'name' => 'bounceRate' ),
		);

		$current_report = self::run_report( $metrics, array(), $current, 0, false );
		if ( is_wp_error( $current_report ) ) {
			return $current_report;
		}
		$compare_report = self::run_report( $metrics, array(), $compare, 0, false );
		if ( is_wp_error( $compare_report ) ) {
			return $compare_report;
		}

		$spark = self::run_report(
			$metrics,
			array( array( 'name' => 'date' ) ),
			$current,
			90,
			false
		);
		if ( is_wp_error( $spark ) ) {
			return $spark;
		}

		$current_vals = self::extract_metric_values( $current_report, 0 );
		$compare_vals = self::extract_metric_values( $compare_report, 0 );
		$series       = self::extract_daily_series( $spark );

		return array(
			'sessions' => array(
				'value'      => (float) $current_vals[0],
				'previous'   => (float) $compare_vals[0],
				'change'     => self::percent_change( $current_vals[0], $compare_vals[0] ),
				'sparkline'  => $series[0],
			),
			'page_views' => array(
				'value'      => (float) $current_vals[1],
				'previous'   => (float) $compare_vals[1],
				'change'     => self::percent_change( $current_vals[1], $compare_vals[1] ),
				'sparkline'  => $series[1],
			),
			'avg_duration' => array(
				'value'      => (float) $current_vals[2],
				'previous'   => (float) $compare_vals[2],
				'change'     => self::percent_change( $current_vals[2], $compare_vals[2] ),
				'sparkline'  => $series[2],
			),
			'bounce_rate' => array(
				'value'      => (float) $current_vals[3],
				'previous'   => (float) $compare_vals[3],
				'change'     => self::percent_change( $current_vals[3], $compare_vals[3] ),
				'sparkline'  => $series[3],
			),
		);
	}

	/**
	 * New vs returning users.
	 *
	 * @param array $current Date range.
	 * @return array|WP_Error
	 */
	private static function fetch_new_vs_returning( $current ) {
		$result = self::run_report(
			array( array( 'name' => 'totalUsers' ) ),
			array( array( 'name' => 'newVsReturning' ) ),
			$current,
			10,
			false
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$new        = 0;
		$returning  = 0;
		$rows       = isset( $result['rows'] ) ? $result['rows'] : array();
		foreach ( $rows as $row ) {
			$label = isset( $row['dimensionValues'][0]['value'] ) ? strtolower( $row['dimensionValues'][0]['value'] ) : '';
			$val   = isset( $row['metricValues'][0]['value'] ) ? (float) $row['metricValues'][0]['value'] : 0;
			if ( 'new' === $label ) {
				$new = $val;
			} elseif ( 'returning' === $label ) {
				$returning = $val;
			}
		}

		$total = $new + $returning;
		return array(
			'new'       => $new,
			'returning' => $returning,
			'total'     => $total,
			'new_pct'   => $total > 0 ? round( ( $new / $total ) * 100, 1 ) : 0,
			'ret_pct'   => $total > 0 ? round( ( $returning / $total ) * 100, 1 ) : 0,
		);
	}

	/**
	 * Device breakdown tabs.
	 *
	 * @param array $current Date range.
	 * @return array|WP_Error
	 */
	private static function fetch_devices( $current ) {
		$map = array(
			'os'        => 'operatingSystem',
			'browsers'  => 'browser',
			'platforms' => 'deviceCategory',
			'screens'   => 'screenResolution',
		);

		$out = array();
		foreach ( $map as $key => $dimension ) {
			$result = self::run_report(
				array( array( 'name' => 'sessions' ) ),
				array( array( 'name' => $dimension ) ),
				$current,
				10,
				true
			);
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			$out[ $key ] = self::rows_to_named_list( $result, 'sessions' );
		}

		return $out;
	}

	/**
	 * Weekday × hour heatmap.
	 *
	 * @param array $current Date range.
	 * @return array|WP_Error
	 */
	private static function fetch_heatmap( $current ) {
		$sessions = self::run_report(
			array( array( 'name' => 'sessions' ) ),
			array(
				array( 'name' => 'dayOfWeek' ),
				array( 'name' => 'hour' ),
			),
			$current,
			200,
			false
		);
		if ( is_wp_error( $sessions ) ) {
			return $sessions;
		}

		$page_views = self::run_report(
			array( array( 'name' => 'screenPageViews' ) ),
			array(
				array( 'name' => 'dayOfWeek' ),
				array( 'name' => 'hour' ),
			),
			$current,
			200,
			false
		);
		if ( is_wp_error( $page_views ) ) {
			return $page_views;
		}

		return array(
			'sessions'   => self::rows_to_heatmap( $sessions ),
			'page_views' => self::rows_to_heatmap( $page_views ),
		);
	}

	/**
	 * Top / entry / exit pages.
	 *
	 * @param array $current Date range.
	 * @return array|WP_Error
	 */
	private static function fetch_pages( $current ) {
		$top = self::run_report(
			array( array( 'name' => 'screenPageViews' ) ),
			array( array( 'name' => 'pagePath' ) ),
			$current,
			10,
			true
		);
		if ( is_wp_error( $top ) ) {
			return $top;
		}

		$entry = self::run_report(
			array( array( 'name' => 'sessions' ) ),
			array( array( 'name' => 'landingPage' ) ),
			$current,
			10,
			true
		);
		if ( is_wp_error( $entry ) ) {
			return $entry;
		}

		// GA4 has no classic "exits" metric; approximate with pagePath + sessions.
		$exit = self::run_report(
			array( array( 'name' => 'sessions' ) ),
			array( array( 'name' => 'pagePath' ) ),
			$current,
			10,
			true
		);
		if ( is_wp_error( $exit ) ) {
			return $exit;
		}

		return array(
			'top'   => self::rows_to_named_list( $top, 'views' ),
			'entry' => self::rows_to_named_list( $entry, 'views' ),
			'exit'  => self::rows_to_named_list( $exit, 'views' ),
		);
	}

	/**
	 * Top channels.
	 *
	 * @param array $current Date range.
	 * @return array|WP_Error
	 */
	private static function fetch_channels( $current ) {
		$result = self::run_report(
			array( array( 'name' => 'sessions' ) ),
			array( array( 'name' => 'sessionDefaultChannelGroup' ) ),
			$current,
			10,
			true
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$list  = self::rows_to_named_list( $result, 'sessions' );
		$total = 0;
		foreach ( $list as $row ) {
			$total += (float) $row['value'];
		}
		foreach ( $list as &$row ) {
			$row['pct'] = $total > 0 ? round( ( $row['value'] / $total ) * 100, 1 ) : 0;
		}
		unset( $row );

		return $list;
	}

	/**
	 * Run a GA4 report.
	 *
	 * @param array        $metrics    Metrics.
	 * @param array        $dimensions Dimensions.
	 * @param array        $date_range Single range or list of ranges.
	 * @param int          $limit      Row limit.
	 * @param bool         $order_desc Order by first metric desc.
	 * @return array|WP_Error
	 */
	public static function run_report( $metrics, $dimensions, $date_range, $limit = 10, $order_desc = true ) {
		$token = self::get_access_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$creds       = self::get_credentials();
		$property_id = $creds['property_id'];

		$date_ranges = isset( $date_range['startDate'] ) ? array( $date_range ) : $date_range;

		$body = array(
			'metrics'    => $metrics,
			'dateRanges' => $date_ranges,
		);

		if ( ! empty( $dimensions ) ) {
			$body['dimensions'] = $dimensions;
		}
		if ( $limit > 0 ) {
			$body['limit'] = (string) $limit;
		}
		if ( $order_desc && ! empty( $metrics ) ) {
			$body['orderBys'] = array(
				array(
					'metric' => array(
						'metricName' => $metrics[0]['name'],
					),
					'desc'   => true,
				),
			);
		}

		$url  = self::DATA_API_URL . 'properties/' . rawurlencode( $property_id ) . ':runReport';
		$args = array(
			// phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout -- GA4 Data API runReport often needs >5s.
			'timeout' => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
		);

		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 ) {
			$message = __( 'GA4 Data API request failed.', 'advance-ecommerce-tracking' );
			if ( is_array( $data ) && ! empty( $data['error']['message'] ) ) {
				$message = $data['error']['message'];
			}
			return new WP_Error( 'aet_ga4_api', $message, array( 'status' => $code ) );
		}

		return is_array( $data ) ? $data : array();
	}

	/**
	 * Get / refresh access token via OAuth refresh token.
	 *
	 * @return string|WP_Error
	 */
	private static function get_access_token() {
		$cached = get_transient( self::TOKEN_TRANSIENT );
		if ( is_string( $cached ) && '' !== $cached ) {
			return $cached;
		}

		$creds = self::get_credentials();
		if ( empty( $creds['refresh_token'] ) ) {
			return new WP_Error( 'aet_ga4_creds', __( 'Google account is not connected.', 'advance-ecommerce-tracking' ) );
		}

		$token_data = AET_GA4_OAuth::refresh_access_token( $creds['refresh_token'] );
		if ( is_wp_error( $token_data ) ) {
			return $token_data;
		}

		$expires = ! empty( $token_data['expires_in'] ) ? max( 60, (int) $token_data['expires_in'] - 60 ) : 3500;
		set_transient( self::TOKEN_TRANSIENT, $token_data['access_token'], $expires );

		return $token_data['access_token'];
	}

	/**
	 * Encrypt secret with WordPress salts (public for OAuth helper).
	 *
	 * @param string $plain Plain text.
	 * @return string
	 */
	public static function encrypt_value( $plain ) {
		$key    = hash( 'sha256', wp_salt( 'auth' ), true );
		$iv     = substr( hash( 'sha256', wp_salt( 'secure_auth' ), true ), 0, 16 );
		$cipher = openssl_encrypt( $plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );
		if ( false === $cipher ) {
			return '';
		}
		return base64_encode( $cipher );
	}

	/**
	 * Decrypt secret (public for OAuth helper).
	 *
	 * @param string $encoded Encoded cipher text.
	 * @return string
	 */
	public static function decrypt_value( $encoded ) {
		$key = hash( 'sha256', wp_salt( 'auth' ), true );
		$iv  = substr( hash( 'sha256', wp_salt( 'secure_auth' ), true ), 0, 16 );
		$raw = base64_decode( (string) $encoded, true );
		if ( false === $raw ) {
			return '';
		}
		$plain = openssl_decrypt( $raw, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );
		return false === $plain ? '' : $plain;
	}

	/**
	 * Extract metric values from a totals-style report (no dimensions).
	 *
	 * @param array $report API response.
	 * @param int   $range_index Date range index (usually 0).
	 * @return array
	 */
	private static function extract_metric_values( $report, $range_index = 0 ) {
		$values = array( 0, 0, 0, 0 );

		if ( isset( $report['rows'][0]['metricValues'] ) ) {
			foreach ( $report['rows'][0]['metricValues'] as $i => $mv ) {
				$values[ $i ] = isset( $mv['value'] ) ? (float) $mv['value'] : 0;
			}
			return $values;
		}

		if ( isset( $report['totals'][ $range_index ]['metricValues'] ) ) {
			foreach ( $report['totals'][ $range_index ]['metricValues'] as $i => $mv ) {
				$values[ $i ] = isset( $mv['value'] ) ? (float) $mv['value'] : 0;
			}
		}

		return $values;
	}

	/**
	 * Daily sparkline series per metric.
	 *
	 * @param array $report API response with date dimension.
	 * @return array
	 */
	private static function extract_daily_series( $report ) {
		$series = array( array(), array(), array(), array() );
		$rows   = isset( $report['rows'] ) ? $report['rows'] : array();

		usort(
			$rows,
			function ( $a, $b ) {
				$da = isset( $a['dimensionValues'][0]['value'] ) ? $a['dimensionValues'][0]['value'] : '';
				$db = isset( $b['dimensionValues'][0]['value'] ) ? $b['dimensionValues'][0]['value'] : '';
				return strcmp( $da, $db );
			}
		);

		foreach ( $rows as $row ) {
			for ( $i = 0; $i < 4; $i++ ) {
				$series[ $i ][] = isset( $row['metricValues'][ $i ]['value'] ) ? (float) $row['metricValues'][ $i ]['value'] : 0;
			}
		}

		return $series;
	}

	/**
	 * Convert rows to name/value list.
	 *
	 * @param array  $report API response.
	 * @param string $value_key Value key name.
	 * @return array
	 */
	private static function rows_to_named_list( $report, $value_key = 'value' ) {
		$list  = array();
		$rows  = isset( $report['rows'] ) ? $report['rows'] : array();
		$total = 0;
		foreach ( $rows as $row ) {
			$val = isset( $row['metricValues'][0]['value'] ) ? (float) $row['metricValues'][0]['value'] : 0;
			$total += $val;
		}
		foreach ( $rows as $row ) {
			$name = isset( $row['dimensionValues'][0]['value'] ) ? $row['dimensionValues'][0]['value'] : '(not set)';
			$val  = isset( $row['metricValues'][0]['value'] ) ? (float) $row['metricValues'][0]['value'] : 0;
			$list[] = array(
				'name'     => $name,
				$value_key => $val,
				'value'    => $val,
				'pct'      => $total > 0 ? round( ( $val / $total ) * 100, 1 ) : 0,
			);
		}
		return $list;
	}

	/**
	 * Convert dayOfWeek × hour rows into heatmap matrix.
	 *
	 * @param array $report API response.
	 * @return array { cells: [{day, hour, value}], max: float }
	 */
	private static function rows_to_heatmap( $report ) {
		$cells = array();
		$max   = 0;
		$rows  = isset( $report['rows'] ) ? $report['rows'] : array();

		foreach ( $rows as $row ) {
			// GA4 dayOfWeek: 0 = Sunday … 6 = Saturday. Convert to Mon=0 … Sun=6 for UI.
			$dow_raw = isset( $row['dimensionValues'][0]['value'] ) ? (int) $row['dimensionValues'][0]['value'] : 0;
			$hour    = isset( $row['dimensionValues'][1]['value'] ) ? (int) $row['dimensionValues'][1]['value'] : 0;
			$val     = isset( $row['metricValues'][0]['value'] ) ? (float) $row['metricValues'][0]['value'] : 0;
			$day     = ( 0 === $dow_raw ) ? 6 : ( $dow_raw - 1 );

			$cells[] = array(
				'day'   => $day,
				'hour'  => $hour,
				'value' => $val,
			);
			if ( $val > $max ) {
				$max = $val;
			}
		}

		return array(
			'cells' => $cells,
			'max'   => $max,
		);
	}

	/**
	 * Percent change helper.
	 *
	 * @param float $current Current.
	 * @param float $previous Previous.
	 * @return float
	 */
	private static function percent_change( $current, $previous ) {
		$current  = (float) $current;
		$previous = (float) $previous;
		if ( 0.0 === $previous ) {
			return $current > 0 ? 100.0 : 0.0;
		}
		return round( ( ( $current - $previous ) / $previous ) * 100, 1 );
	}

	/**
	 * Clear cached overview reports.
	 */
	public static function clear_report_cache() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aet_ga4_ov_%' OR option_name LIKE '_transient_timeout_aet_ga4_ov_%'" );
	}
}

/**
 * Class AET_GA4_Dashboard
 */
class AET_GA4_Dashboard {

	const PAGE_SLUG = 'aet-dashboard';

	/**
	 * Singleton.
	 *
	 * @var AET_GA4_Dashboard|null
	 */
	private static $instance = null;

	/**
	 * Get singleton.
	 *
	 * @return AET_GA4_Dashboard
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
		add_action( 'admin_menu', array( $this, 'register_menu' ), 11 );
		add_filter( 'aet_plugin_menus', array( $this, 'inject_plugin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_init', array( $this, 'handle_hosted_handoff' ) );

		add_action( 'wp_ajax_aet_ga4_dashboard_save_property', array( $this, 'ajax_save_property' ) );
		add_action( 'wp_ajax_aet_ga4_dashboard_disconnect', array( $this, 'ajax_disconnect' ) );
		add_action( 'wp_ajax_aet_ga4_dashboard_test', array( $this, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_aet_ga4_dashboard_overview', array( $this, 'ajax_overview' ) );
	}

	/**
	 * Whether current user can manage the dashboard.
	 *
	 * @return bool
	 */
	private function can_manage() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Register WP submenu before Ecommerce Tracking.
	 */
	public function register_menu() {
		add_submenu_page(
			'dots_store',
			__( 'Dashboard', 'advance-ecommerce-tracking' ),
			__( 'Dashboard', 'advance-ecommerce-tracking' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' ),
			0
		);
	}

	/**
	 * Inject Dashboard into in-plugin nav before Ecommerce Tracking.
	 *
	 * @param array $menus Menu tree.
	 * @return array
	 */
	public function inject_plugin_menu( $menus ) {
		if ( ! is_array( $menus ) || empty( $menus['main_menu'] ) ) {
			return $menus;
		}

		$item = array(
			'menu_title' => __( 'Dashboard', 'advance-ecommerce-tracking' ),
			'menu_slug'  => self::PAGE_SLUG,
			'menu_url'   => esc_url(
				add_query_arg(
					array( 'page' => self::PAGE_SLUG ),
					admin_url( 'admin.php' )
				)
			),
		);

		foreach ( array( 'pro_menu', 'free_menu' ) as $group ) {
			if ( empty( $menus['main_menu'][ $group ] ) || ! is_array( $menus['main_menu'][ $group ] ) ) {
				continue;
			}
			$menus['main_menu'][ $group ] = array_merge(
				array( self::PAGE_SLUG => $item ),
				$menus['main_menu'][ $group ]
			);
		}

		return $menus;
	}

	/**
	 * Handle DotStore hosted OAuth one-time handoff on dashboard return.
	 */
	public function handle_hosted_handoff() {
		if ( ! $this->can_manage() ) {
			return;
		}

		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( self::PAGE_SLUG !== $page ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Presence check only; validated in process_hosted_handoff().
		if ( empty( $_GET['aet_ga4_handoff'] ) && empty( $_GET['aet_ga4_oauth_error'] ) ) {
			return;
		}

		$result = AET_GA4_OAuth::process_hosted_handoff();
		if ( null === $result ) {
			return;
		}

		if ( is_wp_error( $result ) ) {
			$redirect = add_query_arg(
				array(
					'page'            => self::PAGE_SLUG,
					'aet_ga4_oauth'   => 'error',
					'aet_ga4_message' => rawurlencode( $result->get_error_message() ),
				),
				admin_url( 'admin.php' )
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		$args = array(
			'page'          => self::PAGE_SLUG,
			'aet_ga4_oauth' => ! empty( $result['auto_picked'] ) ? 'connected' : 'pick_property',
		);

		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Render dashboard page.
	 */
	public function render_page() {
		if ( ! $this->can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'advance-ecommerce-tracking' ) );
		}
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/aet-dashboard.php';
	}

	/**
	 * Enqueue dashboard assets (Overview page only).
	 *
	 * @param string $hook Hook suffix.
	 */
	public function enqueue_assets( $hook ) {
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( self::PAGE_SLUG !== $page ) {
			return;
		}

		$admin_url = plugin_dir_url( dirname( __FILE__ ) ) . 'admin/';
		$version   = defined( 'AET_VERSION' ) ? AET_VERSION : '3.8.4';
		$status    = AET_GA4_Data_API::get_status();
		$connected = ! empty( $status['connected'] );

		$auth_url = AET_GA4_OAuth::get_auth_url( self::PAGE_SLUG );

		wp_enqueue_style(
			'aet-ga4-dashboard',
			$admin_url . 'css/aet-dashboard.css',
			array(),
			$version
		);

		$deps = array( 'jquery' );
		if ( $connected ) {
			wp_enqueue_script(
				'chart-js',
				'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
				array(),
				'4.4.1',
				true
			);
			$deps[] = 'chart-js';
		}

		wp_enqueue_script(
			'aet-ga4-dashboard',
			$admin_url . 'js/aet-dashboard.js',
			$deps,
			$version,
			true
		);

		$end       = gmdate( 'Y-m-d' );
		$start     = gmdate( 'Y-m-d', strtotime( '-29 days' ) );
		$cmp_end   = gmdate( 'Y-m-d', strtotime( $start . ' -1 day' ) );
		$cmp_start = gmdate( 'Y-m-d', strtotime( $cmp_end . ' -29 days' ) );

		wp_localize_script(
			'aet-ga4-dashboard',
			'aetGa4Dash',
			array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'aet_ga4_dashboard' ),
				'connected'    => $connected,
				'authUrl'      => $auth_url,
				'pendingProps' => AET_GA4_OAuth::get_pending_properties(),
				'status'       => $status,
				'dashboardUrl' => esc_url( add_query_arg( array( 'page' => self::PAGE_SLUG ), admin_url( 'admin.php' ) ) ),
				'defaults'     => array(
					'start'     => $start,
					'end'       => $end,
					'cmp_start' => $cmp_start,
					'cmp_end'   => $cmp_end,
				),
				'i18n'         => array(
					'loading'     => __( 'Loading analytics…', 'advance-ecommerce-tracking' ),
					'failed'      => __( 'Unable to load analytics data.', 'advance-ecommerce-tracking' ),
					'vs_previous' => __( 'vs previous period', 'advance-ecommerce-tracking' ),
					'new_users'   => __( 'New Users', 'advance-ecommerce-tracking' ),
					'returning'   => __( 'Returning Users', 'advance-ecommerce-tracking' ),
					'low'         => __( 'Low', 'advance-ecommerce-tracking' ),
					'high'        => __( 'High', 'advance-ecommerce-tracking' ),
					'saving'      => __( 'Saving…', 'advance-ecommerce-tracking' ),
					'disconnect'  => __( 'Disconnecting…', 'advance-ecommerce-tracking' ),
					'saved'       => __( 'Saved.', 'advance-ecommerce-tracking' ),
					'pick'        => __( 'Please select a GA4 property.', 'advance-ecommerce-tracking' ),
				),
			)
		);
	}

	/**
	 * AJAX: save selected property.
	 */
	public function ajax_save_property() {
		$this->verify_ajax();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in verify_ajax().
		$property_id   = isset( $_POST['property_id'] ) ? sanitize_text_field( wp_unslash( $_POST['property_id'] ) ) : '';
		$property_name = isset( $_POST['property_name'] ) ? sanitize_text_field( wp_unslash( $_POST['property_name'] ) ) : '';
		// phpcs:enable

		$result = AET_GA4_Data_API::save_property_id( $property_id, $property_name );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'GA4 property saved.', 'advance-ecommerce-tracking' ),
				'status'  => AET_GA4_Data_API::get_status(),
			)
		);
	}

	/**
	 * AJAX: disconnect.
	 */
	public function ajax_disconnect() {
		$this->verify_ajax();
		AET_GA4_Data_API::disconnect();
		wp_send_json_success(
			array(
				'message' => __( 'Dashboard disconnected.', 'advance-ecommerce-tracking' ),
				'status'  => AET_GA4_Data_API::get_status(),
			)
		);
	}

	/**
	 * AJAX: test connection.
	 */
	public function ajax_test_connection() {
		$this->verify_ajax();
		$result = AET_GA4_Data_API::test_connection();
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Connection successful.', 'advance-ecommerce-tracking' ) ) );
	}

	/**
	 * AJAX: overview data.
	 */
	public function ajax_overview() {
		$this->verify_ajax();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in verify_ajax().
		$start     = isset( $_POST['start'] ) ? sanitize_text_field( wp_unslash( $_POST['start'] ) ) : '';
		$end       = isset( $_POST['end'] ) ? sanitize_text_field( wp_unslash( $_POST['end'] ) ) : '';
		$cmp_start = isset( $_POST['cmp_start'] ) ? sanitize_text_field( wp_unslash( $_POST['cmp_start'] ) ) : '';
		$cmp_end   = isset( $_POST['cmp_end'] ) ? sanitize_text_field( wp_unslash( $_POST['cmp_end'] ) ) : '';
		// phpcs:enable

		if ( ! $this->is_valid_date( $start ) || ! $this->is_valid_date( $end ) || ! $this->is_valid_date( $cmp_start ) || ! $this->is_valid_date( $cmp_end ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid date range.', 'advance-ecommerce-tracking' ) ) );
		}

		$data = AET_GA4_Data_API::get_overview( $start, $end, $cmp_start, $cmp_end );
		if ( is_wp_error( $data ) ) {
			wp_send_json_error( array( 'message' => $data->get_error_message() ) );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Verify AJAX nonce + caps.
	 */
	private function verify_ajax() {
		if ( ! $this->can_manage() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'advance-ecommerce-tracking' ) ), 403 );
		}
		check_ajax_referer( 'aet_ga4_dashboard', 'nonce' );
	}

	/**
	 * Validate YYYY-MM-DD.
	 *
	 * @param string $date Date.
	 * @return bool
	 */
	private function is_valid_date( $date ) {
		if ( ! is_string( $date ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return false;
		}
		$parts = array_map( 'intval', explode( '-', $date ) );
		return checkdate( $parts[1], $parts[2], $parts[0] );
	}
}
