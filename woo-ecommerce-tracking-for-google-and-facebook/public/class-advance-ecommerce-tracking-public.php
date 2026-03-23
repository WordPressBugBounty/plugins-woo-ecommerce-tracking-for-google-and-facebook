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
     * Admin object reference.
     *
     * @since    3.0
     * @access   private
     * @var      Advance_Ecommerce_Tracking_Admin $admin_obj
     */
    private $admin_obj = null;

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
        $this->admin_obj = new Advance_Ecommerce_Tracking_Admin('', '');
        $this->aet_data = aet_get_all_aet_tracking_data( 'et' );
        $this->aet_4_data = aet_get_aet_4_tracking_data( 'et' );
    }

    /**
     * Add inline JavaScript using wp_add_inline_script (replacement for deprecated wc_enqueue_js).
     *
     * Uses our own script handles (not jQuery) because our code runs in wp_footer/body
     * after jQuery is already printed in wp_head - attaching to jQuery would be too late.
     *
     * @since 3.0
     * @param string $code           The JavaScript code to add.
     * @param bool   $needs_jquery   Whether the code requires jQuery (add jquery as dependency).
     */
    private function aet_add_inline_script( $code, $needs_jquery = false ) {
        $handle = ( $needs_jquery ? 'aet-ga4-inline-jquery' : 'aet-ga4-inline' );
        $deps = ( $needs_jquery ? array('jquery') : array() );
        if ( !wp_script_is( $handle, 'registered' ) ) {
            wp_register_script(
                $handle,
                '',
                $deps,
                $this->version,
                true
            );
        }
        // Always enqueue: wp_add_inline_script only prints when the script is in the queue.
        // Skipping enqueue when handle was already registered could leave it out of the queue.
        wp_enqueue_script( $handle );
        wp_add_inline_script( $handle, $code );
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
        $css_file_path = plugin_dir_path( __FILE__ ) . 'css/advance-ecommerce-tracking-public.css';
        if ( file_exists( $css_file_path ) && filesize( $css_file_path ) > 0 ) {
            wp_enqueue_style(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'css/advance-ecommerce-tracking-public.css',
                array(),
                $this->version,
                'all'
            );
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
        $config_params = array();
        $demography_status = '';
        if ( isset( $demography ) && "off" === $demography ) {
            $demography_status = "gtag('set', 'allow_ad_personalization_signals', false );";
        }
        if ( "on" === $ip_anonymization ) {
            $config_params['anonymize_ip'] = true;
        }
        // User ID tracking for GA4 - uses aet_track_user_property__premium_only when enabled
        $user_id_tracking = ( isset( $this->aet_data['user_id_tracking'] ) ? $this->aet_data['user_id_tracking'] : '' );
        if ( 'on' === $user_id_tracking && aet_fs()->is__premium_only() && aet_fs()->can_use_premium_code() ) {
            $aet_user_args = $this->aet_track_user_property__premium_only();
            if ( is_user_logged_in() && !empty( $aet_user_args['user_id']['uid'] ) ) {
                $config_params['user_id'] = (string) $aet_user_args['user_id']['uid'];
            } else {
                $config_params['user_id'] = null;
            }
        }
        $anonym = ( !empty( $config_params ) ? ', ' . wp_json_encode( $config_params ) : '' );
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
     * Add js code for tracking in one variable for GA4
     *
     * @since 3.0
     */
    public function aet_et_4_tracking_imp_js_code_in_footer() {
        $track_user = aet_tracking_user( 'et' );
        if ( $track_user && aet_fs()->is__premium_only() && aet_fs()->can_use_premium_code() ) {
            $custom_event = $this->aet_data['custom_event'];
            if ( 'on' === $custom_event ) {
                echo wp_kses_post( $this->aet_ga_display_custom_event_tracking__premium_only() ?? '' );
            }
        }
        $track_404 = $this->aet_data['track_404'];
        if ( 'on' === $track_404 ) {
            if ( is_404() ) {
                $event_code = 'gtag("event", "404 Error", {
					event_category:"404 Not Found",
					event_label:"404 Not Found",
				});';
                $this->aet_add_inline_script( $event_code );
            }
        }
        // Site search tracking for GA4
        $search_tracking = ( isset( $this->aet_data['search_tracking'] ) ? $this->aet_data['search_tracking'] : '' );
        if ( 'on' === $search_tracking && is_search() ) {
            $search_term = get_search_query();
            if ( !empty( $search_term ) ) {
                $event_code = 'gtag("event", "view_search_results", { search_term: "' . esc_js( $search_term ) . '" });';
                $this->aet_add_inline_script( $event_code );
            }
        }
        // Sign in event: fire on next page load after login when sign_in_tracking is on.
        $sign_in_tracking = ( isset( $this->aet_data['sign_in_tracking'] ) ? $this->aet_data['sign_in_tracking'] : '' );
        if ( 'on' === $sign_in_tracking && get_transient( 'aet_ga4_track_sign_in' ) ) {
            delete_transient( 'aet_ga4_track_sign_in' );
            $event_code = 'gtag("event", "sign_in", { event_category: "Enhanced-Ecommerce", event_label: "sign_in" });';
            $this->aet_add_inline_script( $event_code );
        }
        // Sign out event: fire on next page load after logout when sign_out_tracking is on.
        $sign_out_tracking = ( isset( $this->aet_data['sign_out_tracking'] ) ? $this->aet_data['sign_out_tracking'] : '' );
        if ( 'on' === $sign_out_tracking && get_transient( 'aet_ga4_track_sign_out' ) ) {
            delete_transient( 'aet_ga4_track_sign_out' );
            $event_code = 'gtag("event", "sign_out", { event_category: "Enhanced-Ecommerce", event_label: "sign_out" });';
            $this->aet_add_inline_script( $event_code );
        }
        // Sign up event: fire on next page load after registration when sign_up_tracking is on.
        $sign_up_tracking = ( isset( $this->aet_data['sign_up_tracking'] ) ? $this->aet_data['sign_up_tracking'] : '' );
        if ( 'on' === $sign_up_tracking && get_transient( 'aet_ga4_track_sign_up' ) ) {
            delete_transient( 'aet_ga4_track_sign_up' );
            $event_code = 'gtag("event", "sign_up", { event_category: "Enhanced-Ecommerce", event_label: "sign_up" });';
            $this->aet_add_inline_script( $event_code );
        }
        // Refund events: fire pending refunds when order was fully refunded (Enhanced Ecommerce, no separate setting).
        $pending_refunds = get_option( 'aet_ga4_pending_refunds', array() );
        if ( is_array( $pending_refunds ) && !empty( $pending_refunds ) ) {
            foreach ( $pending_refunds as $refund ) {
                $transaction_id = ( isset( $refund['transaction_id'] ) ? $refund['transaction_id'] : '' );
                $value = ( isset( $refund['value'] ) ? (float) $refund['value'] : 0 );
                $currency = ( isset( $refund['currency'] ) ? $refund['currency'] : get_woocommerce_currency() );
                if ( !empty( $transaction_id ) ) {
                    $event_code = 'gtag("event", "refund", { event_category: "Enhanced-Ecommerce", event_label: "refund", transaction_id: "' . esc_js( $transaction_id ) . '", value: ' . esc_js( $value ) . ', currency: "' . esc_js( $currency ) . '" });';
                    $this->aet_add_inline_script( $event_code );
                }
            }
            update_option( 'aet_ga4_pending_refunds', array() );
        }
    }

    /**
     * Enhanced E-commerce tracking for purchsed items.
     *
     * @access public
     * @return void
     */
    public function aet_ga_thankyou( $order_id ) {
        global $woocommerce;
        $order = wc_get_order( $order_id );
        if ( !$order || !is_a( $order, 'WC_Order' ) ) {
            return;
        }
        $aet_placed_order_success = $order->get_meta( 'aet_ga_placed_order_success', true );
        if ( 'true' === $aet_placed_order_success || true === $aet_placed_order_success ) {
            return;
        }
        // Get the order and output tracking code
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
                $discount = 0;
                if ( !empty( $_product->get_regular_price() ) && !empty( $_product->get_sale_price() ) ) {
                    $discount = wc_format_decimal( (float) $_product->get_regular_price() - (float) $_product->get_sale_price(), wc_get_price_decimals() );
                }
                $qty = $item["qty"];
                if ( empty( $qty ) ) {
                    $qty = '""';
                } else {
                    $qty = esc_js( $item["qty"] );
                }
                $sku = ( !empty( $_product->get_sku() ) ? $_product->get_sku() : 'SKU_' . $_product->get_id() );
                $item_brand = apply_filters( 'aet_item_brand', '' );
                $brand_property = ( !empty( $item_brand ) ? 'item_brand: "' . esc_js( $item_brand ) . '",' : '' );
                $orderpage_prod .= '{
					item_id: "' . esc_js( $sku ) . '",
					item_name: "' . html_entity_decode( addslashes( $_product->get_name() ) ) . '",
					' . $brand_property . '
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
        $tvc_sc = $order->get_shipping_total();
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
        $order->update_meta_data( 'aet_ga_placed_order_success', 'true' );
        $order->save();
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
                if ( is_product_category() || is_search() || is_shop() || is_product_tag() ) {
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
        $this->aet_add_inline_script( $codeSnippet, true );
    }

}
