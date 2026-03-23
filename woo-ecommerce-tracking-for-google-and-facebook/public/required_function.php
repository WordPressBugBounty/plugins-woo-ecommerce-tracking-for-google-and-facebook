<?php

/**
 * Get Tracking ID.
 *
 * @param string $args
 *
 * @return string $selected_data_ua
 *
 * @since 3.0
 */
function aet_get_tracking_id(  $args  ) {
    $selected_data_ua = get_option( 'selected_data_ua_' . $args );
    return $selected_data_ua;
}

/**
 * Get option.
 *
 * @param string $args
 *
 * @return string $get_option_data
 *
 * @since 3.0
 */
function aet_get_setting_option(  $args  ) {
    $option_key = 'aet_' . $args . '_tracking_settings';
    $get_option_data = json_decode( get_option( $option_key ) );
    return $get_option_data;
}

/**
 * Check user role is log-in or not for tracking.
 *
 * @param string $args
 *
 * @return bool $track_user
 *
 * @since 3.0
 */
function aet_tracking_user(  $args  ) {
    $user = wp_get_current_user();
    $track_user = true;
    if ( is_multisite() && is_super_admin() ) {
        $track_user = false;
    }
    return apply_filters( 'aet_tracking_user', $track_user, $user );
}

/**
 * Check enhance ecommerce is enable or not.
 *
 * @param string $args
 *
 * @return string $enhance_ecommerce_tracking
 *
 * @since 3.0
 */
function aet_get_aet_4_tracking_data(  $args  ) {
    $aet_et_tracking_settings = aet_get_setting_option( $args );
    $manually_et_px_ver_4 = ( empty( $aet_et_tracking_settings->manually_et_px_ver_4 ) ? '' : $aet_et_tracking_settings->manually_et_px_ver_4 );
    return $manually_et_px_ver_4;
}

/**
 * Check enhance ecommerce is enable or not.
 *
 * @param string $args
 *
 * @return string $enhance_ecommerce_tracking
 *
 * @since 3.0
 */
function aet_check_enhance_ecommerce_enable(  $args  ) {
    $aet_et_tracking_settings = aet_get_setting_option( $args );
    $enhance_ecommerce_tracking = ( empty( $aet_et_tracking_settings->enhance_ecommerce_tracking ) ? '' : $aet_et_tracking_settings->enhance_ecommerce_tracking );
    return $enhance_ecommerce_tracking;
}

/**
 * Check IP anonymization is enable or not.
 *
 * @param string $args
 *
 * @return string $ip_anonymization
 *
 * @since 3.0
 */
function aet_check_ip_anonymization(  $args  ) {
    $aet_et_tracking_settings = aet_get_setting_option( $args );
    $ip_anonymization = ( empty( $aet_et_tracking_settings->ip_anonymization ) ? '' : $aet_et_tracking_settings->ip_anonymization );
    return $ip_anonymization;
}

/**
 * Check google analytics opt out enable or not.
 *
 * @param string $args
 *
 * @return string $google_analytics_opt_out
 *
 * @since 3.0
 */
function aet_check_google_analytics_opt_out(  $args  ) {
    $aet_et_tracking_settings = aet_get_setting_option( $args );
    $google_analytics_opt_out = ( empty( $aet_et_tracking_settings->google_analytics_opt_out ) ? '' : $aet_et_tracking_settings->google_analytics_opt_out );
    return $google_analytics_opt_out;
}

/**
 * Get all analytics tracking data.
 *
 * @param string $args
 *
 * @return array $pass_aet_array
 *
 * @since 3.0
 */
function aet_get_all_aet_tracking_data(  $args  ) {
    $pass_aet_array = array();
    $enhance_ecommerce_tracking = aet_check_enhance_ecommerce_enable( $args );
    $pass_aet_array['enhance_ecommerce_tracking'] = $enhance_ecommerce_tracking;
    $ip_anonymization = aet_check_ip_anonymization( $args );
    $pass_aet_array['ip_anonymization'] = $ip_anonymization;
    $google_analytics_opt_out = aet_check_google_analytics_opt_out( $args );
    $pass_aet_array['google_analytics_opt_out'] = $google_analytics_opt_out;
    return $pass_aet_array;
}

/**
 * Get purchase tracking data for an order.
 * Used by frontend thank you page and admin order tracking for manual orders.
 *
 * @param int $order_id Order ID.
 * @return array|false Array of tracking data or false if order invalid.
 * @since 3.0
 */
function aet_get_purchase_tracking_data(  $order_id  ) {
    if ( !function_exists( 'wc_get_order' ) || !function_exists( 'wc_format_decimal' ) ) {
        return false;
    }
    $order = wc_get_order( $order_id );
    if ( !$order || !is_a( $order, 'WC_Order' ) ) {
        return false;
    }
    global $woocommerce;
    $woo_version = ( isset( $woocommerce->version ) && !empty( $woocommerce->version ) ? $woocommerce->version : '3.0' );
    $payment_method = $order->get_payment_method();
    $coupons_list = '';
    if ( version_compare( $woo_version, '3.7', '>' ) ) {
        $codes = $order->get_coupon_codes();
        if ( !empty( $codes ) ) {
            $coupons_list = ( is_array( $codes ) ? implode( ', ', $codes ) : (string) $codes );
        }
    } else {
        $codes = $order->get_coupon_codes();
        if ( !empty( $codes ) ) {
            $coupons_list = ( is_array( $codes ) ? implode( ', ', $codes ) : (string) $codes );
        }
    }
    $currency = get_woocommerce_currency();
    $orderpage_prod = '';
    $items = $order->get_items();
    if ( !empty( $items ) ) {
        foreach ( $items as $item ) {
            $_product = ( is_callable( array($item, 'get_product') ) ? $item->get_product() : null );
            if ( !$_product || !is_a( $_product, 'WC_Product' ) ) {
                continue;
            }
            $product_id = ( version_compare( $woo_version, '2.7', '<' ) && isset( $_product->ID ) ? $_product->ID : $_product->get_id() );
            $categories = get_the_terms( $product_id, 'product_cat' );
            $allcategories = '';
            if ( !empty( $categories ) && !is_wp_error( $categories ) ) {
                $cat_count = 2;
                $loop_count = 1;
                foreach ( $categories as $term ) {
                    if ( 1 === $loop_count ) {
                        $allcategories .= 'item_category: "' . esc_js( $term->name ) . '",';
                    } else {
                        $allcategories .= 'item_category' . $cat_count . ': "' . esc_js( $term->name ) . '",';
                        $cat_count++;
                    }
                    $loop_count++;
                }
            }
            $discount = 0;
            $regular = $_product->get_regular_price();
            $sale = $_product->get_sale_price();
            if ( !empty( $regular ) && !empty( $sale ) ) {
                $decimals = ( function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2 );
                $discount = wc_format_decimal( (float) $regular - (float) $sale, $decimals );
            }
            $qty = 1;
            if ( is_array( $item ) && isset( $item['qty'] ) ) {
                $qty = $item['qty'];
            } elseif ( is_callable( array($item, 'get_quantity') ) ) {
                $qty = $item->get_quantity();
            }
            $qty_js = ( empty( $qty ) || '' === $qty ? '""' : esc_js( $qty ) );
            $sku = $_product->get_sku();
            $sku = ( !empty( $sku ) ? $sku : 'SKU_' . $product_id );
            $item_brand = apply_filters( 'aet_item_brand', '' );
            $brand_property = ( !empty( $item_brand ) ? 'item_brand: "' . esc_js( $item_brand ) . '",' : '' );
            $item_total = ( is_callable( array($order, 'get_item_total') ) ? $order->get_item_total( $item ) : 0 );
            $orderpage_prod .= '{
				item_id: "' . esc_js( $sku ) . '",
				item_name: "' . html_entity_decode( addslashes( $_product->get_name() ) ) . '",
				' . $brand_property . '
				coupon: "' . esc_js( $coupons_list ) . '",
				currency: "' . esc_js( $currency ) . '",
				discount: ' . esc_js( $discount ) . ',
				price: ' . esc_js( $item_total ) . ',
				' . $allcategories . '
				quantity: ' . $qty_js . '
			},';
        }
        $orderpage_prod = rtrim( $orderpage_prod, ',' );
    }
    return array(
        'currency'       => $currency,
        'transaction_id' => $order->get_order_number(),
        'value'          => $order->get_total(),
        'coupons_list'   => $coupons_list,
        'shipping'       => $order->get_shipping_total(),
        'tax'            => $order->get_total_tax(),
        'payment_method' => $payment_method,
        'items_json'     => $orderpage_prod,
    );
}

/**
 * Set transient to fire sign_in event on next frontend page load.
 * Only when sign_in_tracking is enabled and premium code is allowed.
 *
 * @param string  $user_login Username.
 * @param WP_User $user       User object.
 *
 * @since 3.0
 */
function aet_track_sign_in_set_transient(  $user_login, $user  ) {
    if ( !function_exists( 'aet_fs' ) || !aet_fs()->is__premium_only() || !aet_fs()->can_use_premium_code() ) {
        return;
    }
    $settings = json_decode( (string) get_option( 'aet_et_tracking_settings', '{}' ), true );
    if ( empty( $settings['sign_in_tracking'] ) || 'on' !== $settings['sign_in_tracking'] ) {
        return;
    }
    set_transient( 'aet_ga4_track_sign_in', 1, 60 );
}

/**
 * Set transient to fire sign_out event on next frontend page load.
 * Only when sign_out_tracking is enabled and premium code is allowed.
 *
 * @since 3.0
 */
function aet_track_sign_out_set_transient() {
    if ( !function_exists( 'aet_fs' ) || !aet_fs()->is__premium_only() || !aet_fs()->can_use_premium_code() ) {
        return;
    }
    $settings = json_decode( (string) get_option( 'aet_et_tracking_settings', '{}' ), true );
    if ( empty( $settings['sign_out_tracking'] ) || 'on' !== $settings['sign_out_tracking'] ) {
        return;
    }
    set_transient( 'aet_ga4_track_sign_out', 1, 60 );
}

/**
 * Set transient to fire sign_up event on next frontend page load.
 * Only when sign_up_tracking is enabled and premium code is allowed.
 *
 * @param int $user_id User ID.
 *
 * @since 3.0
 */
function aet_track_sign_up_set_transient(  $user_id  ) {
    if ( !function_exists( 'aet_fs' ) || !aet_fs()->is__premium_only() || !aet_fs()->can_use_premium_code() ) {
        return;
    }
    $settings = json_decode( (string) get_option( 'aet_et_tracking_settings', '{}' ), true );
    if ( empty( $settings['sign_up_tracking'] ) || 'on' !== $settings['sign_up_tracking'] ) {
        return;
    }
    set_transient( 'aet_ga4_track_sign_up', 1, 60 );
}

/**
 * Queue refund data to send refund event on next frontend page load.
 * Part of Enhanced Ecommerce; runs when order is fully refunded (no separate setting).
 *
 * @param int $order_id Order ID.
 * @param int $refund_id Refund ID (optional, WC 2.2+).
 *
 * @since 3.0
 */
function aet_track_refund_queue(  $order_id, $refund_id = 0  ) {
    if ( !function_exists( 'aet_fs' ) || !aet_fs()->is__premium_only() || !aet_fs()->can_use_premium_code() ) {
        return;
    }
    if ( !function_exists( 'wc_get_order' ) ) {
        return;
    }
    $order = wc_get_order( $order_id );
    if ( !$order || !is_a( $order, 'WC_Order' ) ) {
        return;
    }
    $total_refunded = $order->get_total_refunded();
    $currency = $order->get_currency();
    $transaction_id = $order->get_order_number();
    if ( empty( $transaction_id ) ) {
        $transaction_id = (string) $order_id;
    }
    $pending = get_option( 'aet_ga4_pending_refunds', array() );
    if ( !is_array( $pending ) ) {
        $pending = array();
    }
    $pending[] = array(
        'transaction_id' => $transaction_id,
        'value'          => (float) $total_refunded,
        'currency'       => $currency,
    );
    update_option( 'aet_ga4_pending_refunds', $pending );
}
