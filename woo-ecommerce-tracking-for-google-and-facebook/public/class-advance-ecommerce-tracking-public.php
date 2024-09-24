<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.thedotstore.com
 * @since      3.0
 *
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/public
 */
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/public
 * @author     Thedotstore <wordpress@multidots.in>
 */
class Advance_Ecommerce_Tracking_Public {
    /**
     * Add inline js.
     *
     * @since    3.0
     * @access   private
     * @var      array $aet_js
     */
    private $admin_obj = '';

    /**
     * Add inline js.
     *
     * @since    3.0
     * @access   private
     * @var      array $aet_js
     */
    private $aet_js = array();

    /**
     * Stepping array.
     *
     * @since    3.0
     * @access   private
     * @var      array $aet_steps
     */
    private $aet_int_aas_array = array();

    /**
     * Detail page variable.
     *
     * @since    3.0
     * @access   private
     * @var      array $aet_steps
     */
    private $single_page = false;

    /**
     * The ID of this plugin.
     *
     * @since    3.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    3.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Store analytics data.
     *
     * @since    3.0
     * @access   private
     * @var      string $aet_data Store analytics data.
     */
    private $aet_data = array();

    /**
     * Store analytics 4 data.
     *
     * @since    3.0
     * @access   private
     * @var      string $aet_4_data Store analytics 4 data.
     */
    private $aet_4_data = array();

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     *
     * @since    3.0
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->aet_int_aas_array = $this->aet_interation_action_and_steps();
        $this->admin_obj = new Advance_Ecommerce_Tracking_Admin('', '');
        $this->aet_data = aet_get_all_aet_tracking_data( 'et' );
        $this->aet_4_data = aet_get_aet_4_tracking_data( 'et' );
    }

    /**
     * User interaction steps.
     *
     * @since 3.0
     */
    private function aet_interation_action_and_steps() {
        return array(
            'clicked_product'    => array(
                'action' => 'click',
                'step'   => 1,
            ),
            'viewed_product'     => array(
                'action' => 'detail',
                'step'   => 2,
            ),
            'added_to_cart'      => array(
                'action' => 'add',
                'step'   => 3,
            ),
            'started_checkout'   => array(
                'action' => 'checkout',
                'step'   => 4,
            ),
            'completed_purchase' => array(
                'action' => 'purchase',
                'step'   => 5,
            ),
        );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    3.0
     */
    public function enqueue_styles() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Advance_Ecommerce_Tracking_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Advance_Ecommerce_Tracking_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/advance-ecommerce-tracking-public.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Load E-commerce tracking plugin.
     *
     * @param array  $aet_options
     *
     * @param string $enhance_ecommerce_tracking
     *
     * @return array $aet_options
     */
    public function aet_require_ec( $aet_options, $enhance_ecommerce_tracking ) {
        if ( 'on' === $enhance_ecommerce_tracking ) {
            if ( empty( $aet_options['ec'] ) ) {
                $aet_options['ec'] = "'require', 'ec'";
            }
        }
        return $aet_options;
    }

    /**
     * Add tracking code here.
     *
     * @since 3.0
     */
    public function aet_add_tracking_code() {
        $track_user = aet_tracking_user( 'et' );
        $src = $this->aet_tracking_url( 'ec' );
        $aet_options = $this->aet_tracking_option();
        $ua = aet_get_tracking_id( 'et' );
        if ( $track_user ) {
            $google_analytics_opt_code = '';
            $google_analytics_opt_out = $this->aet_data['google_analytics_opt_out'];
            if ( 'on' === $google_analytics_opt_out ) {
                $google_analytics_opt_code = "let uaDisableID = 'ga-disable-" . $ua . "'" . ";\n\t\t\t\t\tif (document.cookie.indexOf(uaDisableID + '=true') > -1) {\n\t\t\t\t\t\twindow[uaDisableID] = true;\n\t\t\t\t\t}\n\t\t\t\t\t\n\t\t\t\t\tfunction uaOptout () {\n\t\t\t\t\t\tvar expDate = new Date;\n\t\t\t\t\t\texpDate.setMonth(expDate.getMonth() + 26);\n\t\t\t\t\t\tdocument.cookie = uaDisableID + '=true; expires='+expDate.toGMTString() + ';path =/';\n\t\t\t\t\t\twindow[uaDisableID] = true;\n\t\t\t\t\t}";
            }
            $script_js = "(function (i, s, o, g, r, a, m) {i['GoogleAnalyticsObject'] = r;i[r] = i[r] || function () {\n\t\t\t\t\t\t   (i[r].q = i[r].q || []).push(arguments);}, i[r].l = 1 * new Date();a = s.createElement(o),\n\t\t\t\t\t\t    m = s.getElementsByTagName(o)[0];a.async = 1;a.src = g;m.parentNode.insertBefore(a, m);})\n\t\t\t\t\t        (window, document, 'script', '" . $src . "', '" . self::aet_analytics_var() . "');";
            ?>
			<script type="text/javascript">
				<?php 
            echo wp_kses_post( $script_js ) . "\n";
            if ( count( $aet_options ) >= 1 ) {
                foreach ( $aet_options as $value ) {
                    if ( !is_array( $value ) ) {
                        echo wp_kses_post( self::aet_analytics_var() ) . '(' . wp_kses_post( $value ) . ");\n";
                    } else {
                        if ( !empty( $value['value'] ) ) {
                            echo '' . wp_kses_post( $value['value'] ) . "\n";
                        }
                    }
                }
            }
            ?>
								window['<?php 
            echo wp_kses_post( self::aet_analytics_var() );
            ?>'] = <?php 
            echo wp_kses_post( self::aet_analytics_var() );
            ?>;
			</script>
			<?php 
        }
    }

    /**
     * Add analytics-4 tracking code here.
     *
     * @since 3.0
     */
    public function aet_4_add_tracking_code() {
        // Global site tag (gtag.js) - Google Analytics
        $aet_4_data = $this->aet_4_data;
        $ip_anonymization = $this->aet_data['ip_anonymization'];
        $aet_et_tracking_settings = json_decode( get_option( 'aet_et_tracking_settings' ), true );
        $demography = ( isset( $aet_et_tracking_settings['demogr_int_rema_adver'] ) ? $aet_et_tracking_settings['demogr_int_rema_adver'] : '' );
        $mepfour = ( isset( $aet_et_tracking_settings['manually_et_px_ver_4'] ) ? $aet_et_tracking_settings['manually_et_px_ver_4'] : '' );
        $anonym = '';
        $demography_status = '';
        if ( isset( $demography ) && "off" === $demography ) {
            $demography_status = "gtag('set', 'allow_ad_personalization_signals', false );";
        }
        if ( "on" === $ip_anonymization ) {
            $anonym = ", { 'anonymize_ip': true }";
        }
        $google_analytics_opt_out = $this->aet_data['google_analytics_opt_out'];
        if ( 'on' === $google_analytics_opt_out ) {
            $google_analytics_opt_code = "let ga4DisableID = 'ga-disable-" . $mepfour . "';\n\t\t\t\tif (document.cookie.indexOf(ga4DisableID + '=true') > -1) {\n\t\t\t\t\twindow[ga4DisableID] = true;\n\t\t\t\t}\n\t\t\t\t\n\t\t\t\tfunction ga4Optout () {\n\t\t\t\t\tvar expDate = new Date;\n\t\t\t\t\texpDate.setMonth(expDate.getMonth() + 26);\n\t\t\t\t\tdocument.cookie = ga4DisableID + '=true; expires='+expDate.toGMTString() + ';path =/';\n\t\t\t\t\twindow[ga4DisableID] = true;\n\t\t\t\t}";
        }
        ?>
		<script async src="https://www.googletagmanager.com/gtag/js?id=<?php 
        echo esc_attr( $aet_4_data );
        ?>"></script> <?php 
        // phpcs:ignore
        ?>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());

		  gtag('config', '<?php 
        echo esc_html__( $aet_4_data, 'advance-ecommerce-tracking' );
        ?>' <?php 
        echo $anonym;
        // phpcs:ignore
        ?>);
		  <?php 
        echo ( isset( $google_analytics_opt_code ) && !empty( $google_analytics_opt_code ) ? $google_analytics_opt_code : '' );
        // phpcs:ignore
        echo ( isset( $demography_status ) && !empty( $demography_status ) ? $demography_status : '' );
        // phpcs:ignore
        ?>
		</script>
		<?php 
    }

    /**
     * URl for the tracking.
     *
     * @param string $args
     *
     * @return string $src
     *
     * @since 3.0
     */
    public function aet_tracking_url( $args ) {
        $debug_mode = DEBUG_OPTION;
        if ( 'ec' === $args ) {
            if ( true === $debug_mode ) {
                $src = apply_filters( 'aet_analytics_src', '//www.google-analytics.com/analytics_debug.js' );
            } else {
                $src = apply_filters( 'aet_analytics_src', '//www.google-analytics.com/analytics.js' );
            }
        }
        return $src;
    }

    /**
     * Get tracking option.
     *
     * @return array $aet_options
     *
     * @since 3.0
     */
    public function aet_tracking_option() {
        global $wp_query;
        $enhance_ecommerce_tracking = $this->aet_data['enhance_ecommerce_tracking'];
        $ip_anonymization = $this->aet_data['ip_anonymization'];
        $ua = aet_get_tracking_id( 'et' );
        $aet_options = array();
        $track_user = aet_tracking_user( 'et' );
        if ( $track_user ) {
            $aet_options['create'] = "'create', '" . esc_js( $ua ) . "', '" . esc_js( 'auto' ) . "'";
            if ( 'on' === $ip_anonymization ) {
                $aet_options['anonymize_ips'] = "'set', 'anonymizeIp', true";
            }
            $aet_options = apply_filters( 'aet_tracking_require_filter', $aet_options, $enhance_ecommerce_tracking );
            $aet_options['send'] = "'send','pageview'";
            $aet_options = apply_filters( 'aet_tracking_options_end', $aet_options );
            return $aet_options;
        }
    }

    /**
     * Create unique tracker variable for analytics.
     *
     * @since 3.0
     */
    public static function aet_analytics_var() {
        return apply_filters( 'aet_ga_tracker_variable', '__gatd' );
    }

    /**
     *
     * Get taxonomy list
     *
     * @param string $taxonomy .
     *
     * @param int    $post_id
     *
     * @return array $results
     *
     * @since 3.0
     */
    public function aet_get_taxonomy_list( $taxonomy, $post_id ) {
        $terms = get_the_terms( $post_id, $taxonomy );
        $results = array();
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return array();
        }
        foreach ( $terms as $term ) {
            $results[] = html_entity_decode( $term->name );
        }
        return $results;
    }

    /**
     * Get action for user interaction.
     *
     * @param string $event_key
     *
     * @return array $action
     *
     * @since 3.0
     */
    private function aet_get_interation_action( $event_key ) {
        $action = '';
        if ( isset( $this->aet_int_aas_array[$event_key], $this->aet_int_aas_array[$event_key]['action'] ) ) {
            $action = $this->aet_int_aas_array[$event_key]['action'];
        }
        return $action;
    }

    /**
     * Get step for user interaction.
     *
     * @param string $event_key
     *
     * @return array $step
     *
     * @since 3.0
     */
    private function aet_get_interation_step( $event_key ) {
        $step = '';
        if ( isset( $this->aet_int_aas_array[$event_key], $this->aet_int_aas_array[$event_key]['step'] ) ) {
            $step = $this->aet_int_aas_array[$event_key]['step'];
        }
        return $step;
    }

    /**
     * Track checkout page.
     *
     * @since 3.0
     */
    public function aet_checkout_process() {
        $track_user = aet_tracking_user( 'et' );
        if ( $track_user ) {
            $enhance_ecommerce_tracking = $this->aet_data['enhance_ecommerce_tracking'];
            if ( 'on' === $enhance_ecommerce_tracking ) {
                if ( aet_is_page_reload() ) {
                    return;
                }
                $get_cart = WC()->cart->get_cart();
                if ( empty( $get_cart ) ) {
                    return;
                }
                $aet_api_attr = array(
                    't'              => 'event',
                    'ec'             => 'Checkout',
                    'ea'             => 'Initial Checkout',
                    'el'             => 'Checkout Section',
                    'ev'             => '',
                    'cos'            => 1,
                    'pa'             => $this->aet_get_interation_action( 'started_checkout' ),
                    'pal'            => '',
                    'nonInteraction' => true,
                );
                $items = array();
                $i = 0;
                foreach ( $get_cart as $item ) {
                    $i++;
                    $product_id = $item['product_id'];
                    $product = null;
                    $attribute_value_implode = '';
                    if ( !empty( $item['variation_id'] ) ) {
                        $product = wc_get_product( $item['variation_id'] );
                        $product_title = $product->get_name();
                        if ( $product->is_type( 'variable' ) ) {
                            $variation_attributes = $product->get_variation_attributes();
                            $variation_attributes_array = array();
                            foreach ( $variation_attributes as $term_slug ) {
                                $variation_attributes_array[] = ucwords( $term_slug );
                            }
                            $total_attribute_value = count( $variation_attributes_array );
                            if ( $total_attribute_value > 1 ) {
                                $attribute_value_implode = implode( ', ', $variation_attributes_array );
                            } else {
                                $attribute_value_implode = $variation_attributes_array['0'];
                            }
                        }
                    } else {
                        $product = wc_get_product( $product_id );
                        $product_title = $product->get_name();
                    }
                    $categories = implode( ', ', $this->aet_get_taxonomy_list( 'product_cat', $product_id ) );
                    $prd_key = 'pr' . $i . 'id';
                    $prd_name = 'pr' . $i . 'nm';
                    $prd_cat = 'pr' . $i . 'ca';
                    $prd_va = 'pr' . $i . 'va';
                    $prd_pr = 'pr' . $i . 'pr';
                    $prd_qt = 'pr' . $i . 'qt';
                    $prd_ps = 'pr' . $i . 'ps';
                    $items[$prd_key] = $product_id;
                    // Product ID
                    $items[$prd_name] = $product_title;
                    // Product Name
                    $items[$prd_cat] = $categories;
                    // Product Category
                    $items[$prd_va] = $attribute_value_implode;
                    // Product Variation Title
                    $items[$prd_qt] = $item['quantity'];
                    // Product Quantity
                    $items[$prd_pr] = $product->get_price();
                    // Product Price
                    $items[$prd_ps] = $i;
                    // Product Order
                }
                $aet_api_attr = array_merge( $aet_api_attr, $items );
                aet_measurement_protocol_api_call( $aet_api_attr );
            }
        }
    }

    /**
     * Store order id after order complete
     *
     * @since 3.0
     */
    public function aet_store_order_id( $order_id ) {
        $track_user = aet_tracking_user( 'et' );
        if ( $track_user ) {
            $enhance_ecommerce_tracking = $this->aet_data['enhance_ecommerce_tracking'];
            if ( 'on' === $enhance_ecommerce_tracking ) {
                $get_order_id = get_post_meta( $order_id, 'order_id_wth_uuid', true );
                if ( !empty( $get_order_id ) ) {
                    return;
                }
                $ga_uuid = aet_measurement_protocol_get_client_id();
                if ( $ga_uuid ) {
                    update_post_meta( $order_id, 'order_id_wth_uuid', $ga_uuid );
                }
            }
        }
    }

    /**
     * Add order to analytics
     *
     * @since 3.0
     */
    public function aet_order_pro_comp( $order_id ) {
        $track_user = aet_tracking_user( 'et' );
        if ( $track_user ) {
            $enhance_ecommerce_tracking = $this->aet_data['enhance_ecommerce_tracking'];
            if ( 'on' === $enhance_ecommerce_tracking ) {
                $aet_placed_order_success = get_post_meta( $order_id, 'aet_placed_order_success', true );
                if ( 'true' === $aet_placed_order_success ) {
                    return;
                }
                $order = wc_get_order( $order_id );
                $discount = '';
                if ( count( $order->get_coupon_codes() ) > 0 ) {
                    foreach ( $order->get_coupon_codes() as $coupon_code ) {
                        if ( !$coupon_code ) {
                            continue;
                        } else {
                            $discount = $coupon_code;
                            break;
                        }
                    }
                }
                $ga_uuid = aet_measurement_protocol_get_client_id( $order_id );
                $aet_api_attr = array(
                    't'   => 'event',
                    'ec'  => 'Checkout',
                    'ea'  => 'Completed Checkout',
                    'el'  => $order_id,
                    'ev'  => round( $order->get_total() ),
                    'cos' => 2,
                    'pa'  => $this->aet_get_interation_action( 'completed_purchase' ),
                    'cid' => $ga_uuid,
                    'ti'  => $order_id,
                    'ta'  => null,
                    'tr'  => $order->get_total(),
                    'tt'  => $order->get_total_tax(),
                    'ts'  => $order->get_shipping_total(),
                    'tcc' => $discount,
                );
                if ( is_user_logged_in() ) {
                    $aet_api_attr['uid'] = $order->get_user_id();
                    // UserID tracking
                }
                // Declare items in cart
                $cart_contents = $order->get_items();
                $items = array();
                $i = 0;
                foreach ( $cart_contents as $item ) {
                    $i++;
                    $variation_id = ( $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id() );
                    $product_id = ( $variation_id > 0 ? wp_get_post_parent_id( $variation_id ) : 0 );
                    $product = null;
                    $attribute_value_implode = '';
                    if ( false === $product_id ) {
                        $product_id = $variation_id;
                        $product = wc_get_product( $product_id );
                        $product_title = $product->get_name();
                    } else {
                        $product = wc_get_product( $variation_id );
                        $product_title = $product->get_name();
                        if ( $product->is_type( 'variable' ) ) {
                            $variation_attributes = $product->get_variation_attributes();
                            $variation_attributes_array = array();
                            foreach ( $variation_attributes as $term_slug ) {
                                $variation_attributes_array[] = (string) $term_slug;
                            }
                            $total_attribute_value = count( $variation_attributes_array );
                            if ( $total_attribute_value > 1 ) {
                                $attribute_value_implode = implode( ', ', $variation_attributes_array );
                            } else {
                                $attribute_value_implode = $variation_attributes_array['0'];
                            }
                        }
                    }
                    $categories = implode( ', ', $this->aet_get_taxonomy_list( 'product_cat', $product_id ) );
                    $prd_key = 'pr' . $i . 'id';
                    $prd_name = 'pr' . $i . 'nm';
                    $prd_cat = 'pr' . $i . 'ca';
                    $prd_va = 'pr' . $i . 'va';
                    $prd_pr = 'pr' . $i . 'pr';
                    $prd_qt = 'pr' . $i . 'qt';
                    $prd_ps = 'pr' . $i . 'ps';
                    $items[$prd_key] = $product_id;
                    // Product ID
                    $items[$prd_name] = $product_title;
                    // Product Name
                    $items[$prd_cat] = $categories;
                    // Product Category
                    $items[$prd_va] = $attribute_value_implode;
                    // Product Variation Title
                    $items[$prd_pr] = $order->get_item_total( $item );
                    // Product Price
                    $items[$prd_qt] = $item->get_quantity();
                    // Product Quantity
                    $items[$prd_ps] = $i;
                    // Product Order
                }
                $aet_api_attr = array_merge( $aet_api_attr, $items );
                aet_measurement_protocol_api_call( $aet_api_attr );
                update_post_meta( $order_id, 'aet_placed_order_success', 'true' );
            }
        }
    }

    /**
     * Change return URL for Paypal. Using default URL it override transaction's data. So need to change URL.
     *
     * @param string $paypal_url
     *
     * @return string $paypal_url
     *
     * @since 3.0
     */
    public function aet_change_return_url( $paypal_url ) {
        $track_user = aet_tracking_user( 'et' );
        if ( $track_user ) {
            $enhance_ecommerce_tracking = $this->aet_data['enhance_ecommerce_tracking'];
            if ( 'on' === $enhance_ecommerce_tracking ) {
                $paypal_url = remove_query_arg( 'utm_nooverride', $paypal_url );
                $paypal_url = add_query_arg( 'utm_nooverride', '1', $paypal_url );
                return $paypal_url;
            }
        } else {
            return $paypal_url;
        }
    }

    /**
     * Add js code for tracking in one variable
     *
     * @since 3.0
     */
    public function aet_et_tracking_imp_js_code_in_footer() {
        $track_user = aet_tracking_user( 'et' );
        if ( $track_user ) {
            $enhance_ecommerce_tracking = $this->aet_data['enhance_ecommerce_tracking'];
            if ( 'on' === $enhance_ecommerce_tracking ) {
                if ( !empty( $this->aet_js['impression'] ) ) {
                    foreach ( $this->aet_js['impression'] as $imporession_code ) {
                        wc_enqueue_js( $imporession_code );
                    }
                    wc_enqueue_js( $this->aet_send_event_hit__premium_only(
                        'event',
                        'Products',
                        'Impression',
                        'Impression',
                        '',
                        'true'
                    ) );
                }
                if ( !empty( $this->aet_js['event'] ) ) {
                    foreach ( $this->aet_js['event'] as $event_code ) {
                        wc_enqueue_js( $event_code );
                    }
                }
            }
        }
    }

    /**
     * Add js code for tracking in one variable for GA4
     *
     * @since 3.0
     */
    public function aet_et_4_tracking_imp_js_code_in_footer() {
        $track_404 = $this->aet_data['track_404'];
        if ( 'on' === $track_404 ) {
            $aet_et_tracking_settings = json_decode( get_option( 'aet_et_tracking_settings' ), true );
            $aet_enable = $aet_et_tracking_settings['at_enable'];
            if ( 'UA' === $aet_enable ) {
                return;
            }
            if ( is_404() ) {
                $event_code = 'gtag("event", "404 Error", {
					event_category:"404 Not Found",
					event_label:"404 Not Found",
				});';
                wc_enqueue_js( $event_code );
            }
        }
    }

    /**
     * Call the event object
     *
     * @param string $event_name
     * @param mixed  $params
     * @param string $method
     *
     * @return string
     */
    public function call_event( $event_name, $params, $method ) {
        return sprintf(
            "fbq('%s', '%s', %s);",
            $method,
            $event_name,
            wp_json_encode( $params, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT )
        );
    }

    /**
     * Init Call the event object
     *
     * @param string $event_name
     * @param mixed  $params
     * @param string $method
     *
     * @return string
     */
    public function init_call_event( $event_name, $method ) {
        return sprintf( "fbq('%s', '%s');", $method, $event_name );
    }

    /**
     * Post timout exceed.
     *
     *
     * @return int $time
     *
     * @since 3.0
     */
    public function aet_post_timeout() {
        return 5;
    }

    /**
     * Enhanced E-commerce tracking for purchsed items.
     *
     * @access public
     * @return void
     */
    public function aet_ga_thankyou( $order_id ) {
        global $woocommerce;
        // Get the order and output tracking code
        $order = new WC_Order($order_id);
        $code = '';
        //Get payment method
        $payment_method = $order->get_payment_method();
        //Get Applied Coupon Codes
        $coupons_list = '';
        if ( version_compare( $woocommerce->version, "3.7", ">" ) ) {
            if ( $order->get_coupon_codes() ) {
                $coupons_count = count( $order->get_coupon_codes() );
                $i = 1;
                foreach ( $order->get_coupon_codes() as $coupon ) {
                    $coupons_list .= $coupon;
                    if ( $i < $coupons_count ) {
                        $coupons_list .= ', ';
                    }
                    $i++;
                }
            }
        } else {
            if ( $order->get_used_coupons() ) {
                $coupons_count = count( $order->get_used_coupons() );
                $i = 1;
                foreach ( $order->get_used_coupons() as $coupon ) {
                    $coupons_list .= $coupon;
                    if ( $i < $coupons_count ) {
                        $coupons_list .= ', ';
                    }
                    $i++;
                }
            }
        }
        $currency = get_woocommerce_currency();
        // Order items
        if ( $order->get_items() ) {
            $orderpage_prod = "";
            foreach ( $order->get_items() as $item ) {
                $_product = $item->get_product();
                if ( version_compare( $woocommerce->version, "2.7", "<" ) ) {
                    $categories = get_the_terms( $_product->ID, "product_cat" );
                } else {
                    $categories = get_the_terms( $_product->get_id(), "product_cat" );
                }
                $allcategories = "";
                if ( $categories ) {
                    $cat_count = 2;
                    $loop_count = 1;
                    foreach ( $categories as $term ) {
                        if ( $loop_count === 1 ) {
                            $allcategories .= 'item_category: "' . $term->name . '",';
                        } else {
                            $allcategories .= 'item_category' . $cat_count . ': "' . $term->name . '",';
                            $cat_count++;
                        }
                        $loop_count++;
                    }
                }
                if ( !empty( $_product->get_regular_price() ) && !empty( $_product->get_sale_price() ) ) {
                    $discount = $this->cal_prod_discount( $_product->get_regular_price(), $_product->get_sale_price() );
                }
                if ( empty( $discount ) ) {
                    $discount = 0;
                }
                $qty = $item["qty"];
                if ( empty( $qty ) ) {
                    $qty = '""';
                } else {
                    $qty = esc_js( $item["qty"] );
                }
                $orderpage_prod .= '{
					item_id: "SKU_' . esc_js( $_product->get_id() ) . '",
					item_name: "' . esc_js( $_product->get_title() ) . '",
					coupon: "' . esc_js( $coupons_list ) . '",
					currency: "' . esc_js( $currency ) . '",
					discount: ' . esc_js( $discount ) . ',
					price: ' . esc_js( $order->get_item_total( $item ) ) . ',
					' . $allcategories . '
					quantity: ' . $qty . '
				},';
            }
            $orderpage_prod = rtrim( $orderpage_prod, "," );
        }
        $tvc_sc = $order->get_total_shipping();
        $code .= '
		gtag("event", "purchase", {
			event_category:"Enhanced-Ecommerce",
			event_label:"purchase",
			currency: "' . esc_js( $currency ) . '",
			transaction_id: ' . esc_js( $order->get_order_number() ) . ',
			value: ' . esc_js( $order->get_total() ) . ',
			coupon: "' . esc_js( $coupons_list ) . '",
			shipping: ' . esc_js( $tvc_sc ) . ',
			tax: ' . esc_js( $order->get_total_tax() ) . ',
			items: [ ' . $orderpage_prod . ' ],
		});';
        $this->wc_version_compare( $code );
    }

    /**
     * Enhanced E-commerce bind product data.
     *
     * @access public
     * @return void
     */
    public function aet_bind_product_metadata() {
        global $product, $woocommerce;
        if ( version_compare( $woocommerce->version, "2.7", "<" ) ) {
            $category = get_the_terms( $product->Id, "product_cat" );
        } else {
            $category = get_the_terms( $product->get_id(), "product_cat" );
        }
        $allcategories = "";
        if ( $category ) {
            $cat_count = 2;
            $loop_count = 1;
            foreach ( $category as $term ) {
                if ( $loop_count === 1 ) {
                    $allcategories .= 'item_category: "' . $term->name . '",';
                } else {
                    $allcategories .= 'item_category' . $cat_count . ': "' . $term->name . '",';
                    $cat_count++;
                }
                $loop_count++;
            }
        }
        //remove last comma(,) if multiple categories are there
        $categories = rtrim( $allcategories, "," );
        //declare all variable as a global which will used for make json
        global 
            $homepage_json_fp,
            $homepage_json_ATC_link,
            $homepage_json_rp,
            $prodpage_json_relProd,
            $catpage_json,
            $prodpage_json_ATC_link,
            $catpage_json_ATC_link
        ;
        //is home page then make all necessory json
        if ( is_home() || is_front_page() ) {
            if ( !is_array( $homepage_json_fp ) && !is_array( $homepage_json_rp ) && !is_array( $homepage_json_ATC_link ) ) {
                $homepage_json_fp = array();
                $homepage_json_rp = array();
                $homepage_json_ATC_link = array();
            }
            // ATC link Array
            if ( version_compare( $woocommerce->version, "2.7", "<" ) ) {
                $homepage_json_ATC_link[$product->id] = array(
                    "ATC-link" => $product->id,
                );
            } else {
                $homepage_json_ATC_link[$product->get_id()] = array(
                    "ATC-link" => $product->get_id(),
                );
            }
            //check if product is featured product or not
            if ( $product->is_featured() ) {
                //check if product is already exists in homepage featured json
                if ( version_compare( $woocommerce->version, "2.7", "<" ) ) {
                    if ( !array_key_exists( $product->id, $homepage_json_fp ) ) {
                        $homepage_json_fp[$product->id] = array(
                            "tvc_id"   => esc_html( $product->id ),
                            "tvc_i"    => esc_html( 'SKU_' . $product->id ),
                            "tvc_n"    => esc_html( $product->get_title() ),
                            "tvc_p"    => esc_html( $product->get_price() ),
                            "tvc_c"    => $categories,
                            "ATC-link" => $product->add_to_cart_url(),
                        );
                        //else add product in homepage recent product json
                    } else {
                        $homepage_json_rp[$product->get_id()] = array(
                            "tvc_id" => esc_html( $product->get_id() ),
                            "tvc_i"  => esc_html( 'SKU_' . $product->get_id() ),
                            "tvc_n"  => esc_html( $product->get_title() ),
                            "tvc_p"  => esc_html( $product->get_price() ),
                            "tvc_c"  => $categories,
                        );
                    }
                } else {
                    if ( !array_key_exists( $product->get_id(), $homepage_json_fp ) ) {
                        $homepage_json_fp[$product->get_id()] = array(
                            "tvc_id"   => esc_html( $product->get_id() ),
                            "tvc_i"    => esc_html( 'SKU_' . $product->get_id() ),
                            "tvc_n"    => esc_html( $product->get_title() ),
                            "tvc_p"    => esc_html( $product->get_price() ),
                            "tvc_c"    => $categories,
                            "ATC-link" => $product->add_to_cart_url(),
                        );
                        //else add product in homepage recent product json
                    } else {
                        $homepage_json_rp[$product->get_id()] = array(
                            "tvc_id" => esc_html( $product->get_id() ),
                            "tvc_i"  => esc_html( 'SKU_' . $product->get_id() ),
                            "tvc_n"  => esc_html( $product->get_title() ),
                            "tvc_p"  => esc_html( $product->get_price() ),
                            "tvc_c"  => $categories,
                        );
                    }
                }
            } else {
                //else prod add in homepage recent json
                if ( version_compare( $woocommerce->version, "2.7", "<" ) ) {
                    $homepage_json_rp[$product->id] = array(
                        "tvc_id" => esc_html( $product->id ),
                        "tvc_i"  => esc_html( 'SKU_' . $product->id ),
                        "tvc_n"  => esc_html( $product->get_title() ),
                        "tvc_p"  => esc_html( $product->get_price() ),
                        "tvc_c"  => $categories,
                    );
                } else {
                    $homepage_json_rp[$product->get_id()] = array(
                        "tvc_id" => esc_html( $product->get_id() ),
                        "tvc_i"  => esc_html( 'SKU_' . $product->get_id() ),
                        "tvc_n"  => esc_html( $product->get_title() ),
                        "tvc_p"  => esc_html( $product->get_price() ),
                        "tvc_c"  => $categories,
                    );
                }
            }
        } else {
            if ( is_product() ) {
                if ( !is_array( $prodpage_json_relProd ) && !is_array( $prodpage_json_ATC_link ) ) {
                    $prodpage_json_relProd = array();
                    $prodpage_json_ATC_link = array();
                }
                // ATC link Array
                if ( version_compare( $woocommerce->version, "2.7", "<" ) ) {
                    $prodpage_json_ATC_link[$product->id] = array(
                        "ATC-link" => $product->id,
                    );
                    $prodpage_json_relProd[$product->id] = array(
                        "tvc_id" => esc_html( $product->id ),
                        "tvc_i"  => esc_html( 'SKU_' . $product->id ),
                        "tvc_n"  => esc_html( $product->get_title() ),
                        "tvc_p"  => esc_html( $product->get_price() ),
                        "tvc_c"  => $categories,
                    );
                } else {
                    $prodpage_json_ATC_link[$product->get_id()] = array(
                        "ATC-link" => $product->get_id(),
                    );
                    $prodpage_json_relProd[$product->get_id()] = array(
                        "tvc_id" => esc_html( $product->get_id() ),
                        "tvc_i"  => esc_html( 'SKU_' . $product->get_id() ),
                        "tvc_n"  => esc_html( $product->get_title() ),
                        "tvc_p"  => esc_html( $product->get_price() ),
                        "tvc_c"  => $categories,
                    );
                }
            } else {
                if ( is_product_category() || is_search() || is_shop() ) {
                    if ( !is_array( $catpage_json ) && !is_array( $catpage_json_ATC_link ) ) {
                        $catpage_json = array();
                        $catpage_json_ATC_link = array();
                    }
                    //cat page ATC array
                    if ( version_compare( $woocommerce->version, "2.7", "<" ) ) {
                        $catpage_json_ATC_link[$product->id] = array(
                            "ATC-link" => $product->id,
                        );
                        $catpage_json[$product->id] = array(
                            "tvc_id" => esc_html( $product->id ),
                            "tvc_i"  => esc_html( 'SKU_' . $product->id ),
                            "tvc_n"  => esc_html( $product->get_title() ),
                            "tvc_p"  => esc_html( $product->get_price() ),
                            "tvc_c"  => $categories,
                        );
                    } else {
                        $catpage_json_ATC_link[$product->get_id()] = array(
                            "ATC-link" => $product->get_id(),
                        );
                        $catpage_json[$product->get_id()] = array(
                            "tvc_id" => esc_html( $product->get_id() ),
                            "tvc_i"  => esc_html( 'SKU_' . $product->get_id() ),
                            "tvc_n"  => esc_html( $product->get_title() ),
                            "tvc_p"  => esc_html( $product->get_price() ),
                            "tvc_c"  => $categories,
                        );
                    }
                }
            }
        }
    }

    /**
     * woocommerce version compare
     *
     * @access public
     * @return void
     */
    function wc_version_compare( $codeSnippet ) {
        global $woocommerce;
        if ( version_compare( $woocommerce->version, "2.1", ">=" ) ) {
            wc_enqueue_js( $codeSnippet );
        } else {
            $woocommerce->add_inline_js( $codeSnippet );
        }
    }

    /**
     * Calculate Product discount
     *
     * @access private
     * @param mixed $type
     * @return bool
     */
    function cal_prod_discount( $t_rprc, $t_sprc ) {
        //older $product Object
        $t_dis = '0';
        //calculate discount
        if ( !empty( $t_rprc ) && !empty( $t_sprc ) ) {
            $t_dis = sprintf( "%.2f", ($t_rprc - $t_sprc) / $t_rprc * 100 );
        }
        return $t_dis;
    }

}
