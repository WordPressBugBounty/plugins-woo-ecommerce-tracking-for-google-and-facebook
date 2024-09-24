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
 * API for Measurement protocol.
 *
 * @since 3.0
 */
function aet_measurement_protocol_api_url() {
    $debug = DEBUG_OPTION;
    if ( true === $debug ) {
        return esc_url( 'https://www.google-analytics.com/debug/collect' );
    } else {
        return esc_url( 'https://www.google-analytics.com/collect' );
    }
}

/**
 * Output for the debug.
 *
 * @param array|mixed $var
 *
 * @since 3.0
 */
function aet_measurement_protocol_api_debug_output(  $var  ) {
    return;
}

/**
 * Analytics API call.
 *
 * @param array $aet_api_args
 *
 * @return mixed $response
 *
 * @since 3.0
 */
function aet_measurement_protocol_api_call(  $aet_api_args = array()  ) {
    $get_http_client_ip = filter_input( INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $get_http_x_forwarded = filter_input( INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $get_http_remote_address = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $get_http_accept_lang = filter_input( INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $get_request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $get_http_user_agent = filter_input( INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $user_agent = '';
    if ( !empty( $aet_api_args['user-agent'] ) ) {
        $user_agent = $aet_api_args['user-agent'];
        unset($aet_api_args['user-agent']);
    }
    $order_id = 0;
    if ( !empty( $aet_api_args['payment_id'] ) ) {
        $order_id = $aet_api_args['payment_id'];
        unset($aet_api_args['payment_id']);
    }
    if ( is_user_logged_in() ) {
        $aet_api_args['uid'] = get_current_user_id();
    }
    $defaults = array(
        't'  => 'event',
        'ec' => '',
        'ea' => '',
        'el' => '',
        'ev' => null,
    );
    $body = array_merge( $defaults, $aet_api_args );
    $client_ip = '';
    if ( !empty( $get_http_client_ip ) && !filter_var( $get_http_client_ip, FILTER_VALIDATE_IP ) === false ) {
        $client_ip = $get_http_client_ip;
    } elseif ( !empty( $get_http_x_forwarded ) && !filter_var( $get_http_x_forwarded, FILTER_VALIDATE_IP ) === false ) {
        $client_ip = $get_http_x_forwarded;
    } else {
        $client_ip = $get_http_remote_address;
    }
    $site_language = ( isset( $get_http_accept_lang ) ? explode( ',', $get_http_accept_lang ) : array() );
    $site_language = reset( $site_language );
    $site_language = sanitize_text_field( $site_language );
    $default_body = array(
        'v'   => '1',
        'tid' => aet_get_tracking_id( 'et' ),
        'cid' => aet_measurement_protocol_get_client_id( $order_id ),
        'ni'  => true,
        'dh'  => str_replace( array('http://', 'https://'), '', site_url() ),
        'dp'  => $get_request_uri,
        'dt'  => get_the_title(),
        'ul'  => $site_language,
        'uip' => $client_ip,
        'ua'  => ( !empty( $user_agent ) ? $user_agent : $get_http_user_agent ),
        'z'   => time(),
    );
    $body = wp_parse_args( $body, $default_body );
    foreach ( $body as $key => $value ) {
        if ( empty( $value ) ) {
            unset($body[$key]);
        }
    }
    $aet_api_args = array(
        'method'   => 'POST',
        'blocking' => false,
        'body'     => $body,
    );
    if ( !empty( $user_agent ) ) {
        $aet_api_args['user-agent'] = $user_agent;
    }
    $response = wp_remote_post( aet_measurement_protocol_api_url(), $aet_api_args );
    if ( true === DEBUG_OPTION ) {
        aet_measurement_protocol_api_debug_output( $body );
        aet_measurement_protocol_api_debug_output( $response );
    } else {
        return $response;
    }
}

/**
 * Check page is reload or not.
 *
 * @return string $url
 *
 * @since 3.0
 */
function aet_is_page_reload() {
    $get_request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $get_http_refer = filter_input( INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    if ( !isset( $get_http_refer ) ) {
        return false;
    }
    return wp_parse_url( $get_http_refer, PHP_URL_PATH ) === wp_parse_url( $get_request_uri, PHP_URL_PATH );
}

/**
 * Get client id regarding order id.
 *
 * @return bool $order_id
 *
 * @return string $order_id_wth_uuid if $order is not empty otherwise it will return $ga_uuid.
 *                if $ga_uuid is empty then it generate clients id from cookie and return client id.
 *
 * @since 3.0
 */
function aet_measurement_protocol_get_client_id(  $order_id = false  ) {
    if ( is_object( $order_id ) ) {
        $order_id = $order_id->ID;
    }
    $ga_uuid = aet_measurement_protocol_get_uuid();
    $order_id_wth_uuid = ( !empty( $order_id ) ? get_post_meta( $order_id, 'order_id_wth_uuid', true ) : false );
    if ( !empty( $order_id ) && !empty( $order_id_wth_uuid ) ) {
        return $order_id_wth_uuid;
    } else {
        if ( !empty( $ga_uuid ) ) {
            return $ga_uuid;
        } else {
            return aet_measurement_protocol_generate_uuid();
        }
    }
}

/**
 * It will returns the client id from cookie. Like: GA1.2.XXXXXXX.YYYYY _ga=1.2.XXXXXXX.YYYYYY -- We want the XXXXXXX.YYYYYY part.
 *
 * @return bool|string false
 *
 * @link  https://developers.google.com/analytics/devguides/collection/analyticsjs/domains#getClientId
 *
 * @since 3.0
 */
function aet_measurement_protocol_get_uuid() {
    $cookie_ga = filter_input( INPUT_COOKIE, '_ga', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    if ( empty( $cookie_ga ) ) {
        return false;
    }
    $ga_cookie = $cookie_ga;
    $cookie_parts = explode( '.', $ga_cookie );
    if ( is_array( $cookie_parts ) && !empty( $cookie_parts[2] ) && !empty( $cookie_parts[3] ) ) {
        $uuid = (string) $cookie_parts[2] . '.' . (string) $cookie_parts[3];
        if ( is_string( $uuid ) ) {
            return $uuid;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/**
 * Generate temporarily uuid.
 *
 * @return string Like: %04x%04x-%04x-%04x-%04x-%04x%04x%04x
 *
 * @since 3.0
 */
function aet_measurement_protocol_generate_uuid() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        wp_rand( 0, 0xffff ),
        wp_rand( 0, 0xffff ),
        wp_rand( 0, 0xffff ),
        wp_rand( 0, 0xfff ) | 0x4000,
        wp_rand( 0, 0x3fff ) | 0x8000,
        wp_rand( 0, 0xffff ),
        wp_rand( 0, 0xffff ),
        wp_rand( 0, 0xffff )
    );
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
function aet_ft_tracking_user(  $args  ) {
    $user = wp_get_current_user();
    $track_user = true;
    if ( is_multisite() && is_super_admin() ) {
        $track_user = false;
    }
    $ua = aet_get_tracking_id( $args );
    if ( empty( $ua ) ) {
        $track_user = false;
    }
    return apply_filters( 'aet_ft_tracking_user', $track_user, $user );
}

/**
 * Check ecommerce tracking is enable or not for fb.
 *
 * @param string $args
 *
 * @return string $fb_ecommerce_tracking
 *
 * @since 3.0
 */
function aet_check_fb_ecommerce_tracking(  $args  ) {
    $aet_et_tracking_settings = aet_get_setting_option( $args );
    $fb_ecommerce_tracking = ( empty( $aet_et_tracking_settings->fb_ecommerce_tracking ) ? '' : $aet_et_tracking_settings->fb_ecommerce_tracking );
    return $fb_ecommerce_tracking;
}

/**
 * Check google conversion tracking is enable or not for fb.
 *
 * @param string $args
 *
 * @return string $gc_enable
 *
 * @since 3.0
 */
function aet_check_gc_conversion_tracking(  $args  ) {
    $aet_et_tracking_settings = aet_get_setting_option( $args );
    $gc_enable = ( empty( $aet_et_tracking_settings->gc_enable ) ? '' : $aet_et_tracking_settings->gc_enable );
    return $gc_enable;
}

/**
 * Check google conversion id is empty or not for fb.
 *
 * @param string $args
 *
 * @return string $gc_id
 *
 * @since 3.0
 */
function aet_check_gc_id(  $args  ) {
    $aet_et_tracking_settings = aet_get_setting_option( $args );
    $gc_id = ( empty( $aet_et_tracking_settings->gc_id ) ? '' : $aet_et_tracking_settings->gc_id );
    return $gc_id;
}

/**
 * Check google conversion id is empty or not for fb.
 *
 * @param string $args
 *
 * @return string $gc_label
 *
 * @since 3.0
 */
function aet_check_gc_label(  $args  ) {
    $aet_et_tracking_settings = aet_get_setting_option( $args );
    $gc_label = ( empty( $aet_et_tracking_settings->gc_label ) ? '' : $aet_et_tracking_settings->gc_label );
    return $gc_label;
}
