<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.thedotstore.com
 * @since      3.0
 *
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/admin
 * @author     Thedotstore <wordpress@multidots.in>
 */
class Advance_Ecommerce_Tracking_Admin {
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
     * The server url.
     *
     * @since    3.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $base_url = 'http://pluginsdemo.thedotstore.com';

    /**
     * The server path.
     *
     * @since    3.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $base_path = 'v2';

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     *
     * @since    3.0
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Get custom event list.
     *
     * @param string $get_post_id
     *
     * @param string $post_type
     *
     * @return string $default_lang
     *
     * @since  3.4
     *
     */
    public static function aet_get_custom_event_list( $get_post_id, $post_type ) {
        $aet_args = array(
            'post_type'        => $post_type,
            'posts_per_page'   => -1,
            'orderby'          => 'menu_order',
            'order'            => 'ASC',
            'suppress_filters' => false,
        );
        if ( !empty( $get_post_id ) ) {
            $aet_args['post__in'] = array($get_post_id);
        }
        $aet_all_event_list = new WP_Query($aet_args);
        $aet_all_event = $aet_all_event_list->get_posts();
        if ( !empty( $get_post_id ) ) {
            return ( !empty( $aet_all_event ) ? $aet_all_event[0] : null );
        }
        return $aet_all_event;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @param string $hook display current page name
     *
     * @since    3.0
     */
    public function aet_enqueue_styles( $hook ) {
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
        if ( false !== strpos( $hook, '_aet' ) ) {
            wp_enqueue_style(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'css/advance-ecommerce-tracking-admin.css',
                array(),
                $this->version,
                'all'
            );
            wp_enqueue_style(
                'advance-ecommerce-tracking-main-style',
                plugin_dir_url( __FILE__ ) . 'css/style.css',
                array(),
                $this->version,
                false
            );
            wp_enqueue_style(
                'advance-ecommerce-tracking-media',
                plugin_dir_url( __FILE__ ) . 'css/media.css',
                array(),
                $this->version,
                false
            );
            wp_enqueue_style(
                'advance-ecommerce-tracking-webkit',
                plugin_dir_url( __FILE__ ) . 'css/webkit.css',
                array(),
                $this->version,
                false
            );
            wp_enqueue_style(
                $this->plugin_name . 'plugin-new-style',
                plugin_dir_url( __FILE__ ) . 'css/plugin-new-style.css',
                array(),
                'all'
            );
            wp_enqueue_style(
                $this->plugin_name . 'plugin-setup-wizard',
                plugin_dir_url( __FILE__ ) . 'css/plugin-setup-wizard.css',
                array(),
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @param string $hook display current page name
     *
     * @since    3.0
     */
    public function aet_enqueue_scripts( $hook ) {
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
        if ( false !== strpos( $hook, '_aet' ) ) {
            global $wp;
            $current_url = home_url( add_query_arg( $wp->query_vars, $wp->request ) );
            wp_enqueue_script(
                $this->plugin_name . '-api',
                esc_url( 'https://apis.google.com/js/api.js' ),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . 'freemius_pro',
                'https://checkout.freemius.com/checkout.min.js',
                array('jquery'),
                $this->version,
                true
            );
            wp_enqueue_script(
                $this->plugin_name . '-help-scout-beacon-js',
                plugin_dir_url( __FILE__ ) . 'js/help-scout-beacon.js',
                array('jquery'),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url( __FILE__ ) . 'js/advance-ecommerce-tracking-admin.js',
                array('jquery', 'jquery-ui-dialog'),
                $this->version,
                false
            );
            wp_localize_script( $this->plugin_name, 'aet_vars', array(
                'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
                'trash_url'               => esc_url( AET_PLUGIN_URL . 'admin/images/rubbish-bin.png' ),
                'aet_chk_nonce_ajax'      => wp_create_nonce( 'aet_chk_nonce' ),
                'current_url'             => $current_url,
                'dpb_api_url'             => AET_STORE_URL,
                'setup_wizard_ajax_nonce' => wp_create_nonce( 'wizard_ajax_nonce' ),
            ) );
            if ( !(aet_fs()->is__premium_only() && aet_fs()->can_use_premium_code()) ) {
                wp_enqueue_style(
                    $this->plugin_name . 'upgrade-dashboard-style',
                    plugin_dir_url( __FILE__ ) . 'css/upgrade-dashboard.css',
                    array(),
                    'all'
                );
            }
            wp_enqueue_style(
                $this->plugin_name . 'font-awesome',
                plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Redirect to the setting page after activate plugin.
     *
     * @since    3.0
     */
    public function aet_welcome_ecommerce_tracking_do_activation_redirect() {
        $get_activate_multi = filter_input( INPUT_GET, 'activate-multi', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        // if no activation redirect
        if ( !get_transient( '_aet_welcome_ecommerce_screen_activation_redirect_data' ) ) {
            return;
        }
        // Delete the redirect transient
        delete_transient( '_aet_welcome_ecommerce_screen_activation_redirect_data' );
        // if activating from network, or bulk
        if ( is_network_admin() || isset( $get_activate_multi ) ) {
            return;
        }
        // Redirect to extra cost welcome  page
        wp_safe_redirect( add_query_arg( array(
            'page' => 'aet-et-settings',
        ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Add menu in admin side.
     *
     * @since    3.0
     */
    public function aet_admin_menu() {
        global $GLOBALS;
        if ( empty( $GLOBALS['admin_page_hooks']['dots_store'] ) ) {
            add_menu_page(
                'Dotstore Plugins',
                'Dotstore Plugins',
                'null',
                'dots_store',
                array($this, 'dot_store_menu_advanced_ecommerce_tracking_pro_page'),
                'dashicons-marker',
                25
            );
        }
        add_submenu_page(
            'dots_store',
            'Ecommerce Tracking',
            'Ecommerce Tracking',
            'manage_options',
            'aet-et-settings',
            array($this, 'aet_ecommerce_tracking_settings'),
            1
        );
        add_submenu_page(
            'dots_store',
            'Custom Event List',
            'Custom Event List',
            'manage_options',
            'aet-cat-list',
            array($this, 'aet_cat_list_page'),
            3
        );
        add_submenu_page(
            'dots_store',
            'Getting Started',
            'Getting Started',
            'manage_options',
            'aet-get-started',
            array($this, 'aet_get_started_page'),
            4
        );
        add_submenu_page(
            'dots_store',
            'Get Premium',
            'Get Premium',
            'manage_options',
            'aet-premium',
            array($this, 'aet_premium_page'),
            6
        );
    }

    /**
     * Ecommerce Tracking Setting Page.
     *
     * @since    3.0
     */
    public function aet_ecommerce_tracking_settings() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/aet-et-settings.php';
    }

    /**
     * Add custom css for dotstore icon in admin area
     *
     * @since  3.9.3
     *
     */
    public function aet_dot_store_icon_css() {
        echo '<style>
	    .toplevel_page_dots_store .dashicons-marker::after{content:"";border:3px solid;position:absolute;top:14px;left:15px;border-radius:50%;opacity: 0.6;}
	    li.toplevel_page_dots_store:hover .dashicons-marker::after,li.toplevel_page_dots_store.current .dashicons-marker::after{opacity: 1;}
	    @media only screen and (max-width: 960px){
	    	.toplevel_page_dots_store .dashicons-marker::after{left:14px;}
	    }
	  	</style>';
    }

    /**
     * Google Analytics Custom Event List.
     *
     * @since 3.0
     */
    public function aet_cat_list_page() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/aet-cat-list.php';
    }

    /**
     * Quick guide page.
     *
     * @since    3.0
     */
    public function aet_get_started_page() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/aet-get-started-page.php';
    }

    /**
     * Premium plugin page.
     *
     * @since    3.0
     */
    public function aet_premium_page() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/aet-premium-page.php';
    }

    /**
     * Remove submenu from admin screen.
     *
     * @since    3.0
     */
    public function aet_remove_admin_submenus() {
        remove_submenu_page( 'dots_store', 'dots_store' );
        remove_submenu_page( 'dots_store', 'aet-get-started' );
        remove_submenu_page( 'dots_store', 'aet-premium' );
        remove_submenu_page( 'dots_store', 'aet-ft-settings' );
        remove_submenu_page( 'dots_store', 'aet-cat-list' );
        remove_submenu_page( 'dots_store', 'aet-gc-settings' );
    }

    /**
     * Create a menu for plugin.
     *
     * @param string $current current page.
     *
     * @since    3.0
     */
    public function aet_menus( $current = 'aet-et-settings' ) {
        $menu_title = '';
        $menu_url = '';
        $menu_slug = '';
        $licence_url = '';
        $wpfp_menus = array(
            'main_menu' => array(
                'pro_menu'  => array(
                    'aet-et-settings' => array(
                        'menu_title' => __( 'Ecommerce Tracking', 'advance-ecommerce-tracking' ),
                        'menu_slug'  => 'aet-et-settings',
                        'menu_url'   => $this->aet_plugins_url(
                            '',
                            'aet-et-settings',
                            '',
                            '',
                            ''
                        ),
                    ),
                    $menu_slug        => array(
                        'menu_title' => $menu_title,
                        'menu_slug'  => $menu_slug,
                        'menu_url'   => $menu_url,
                    ),
                    'license'         => array(
                        'menu_title' => __( 'License', 'advance-ecommerce-tracking' ),
                        'menu_slug'  => 'license',
                        'menu_url'   => $licence_url,
                    ),
                ),
                'free_menu' => array(
                    'aet-et-settings' => array(
                        'menu_title' => __( 'Ecommerce Tracking', 'advance-ecommerce-tracking' ),
                        'menu_slug'  => 'aet-et-settings',
                        'menu_url'   => $this->aet_plugins_url(
                            '',
                            'aet-et-settings',
                            '',
                            '',
                            ''
                        ),
                    ),
                    'aet-cat-list'    => array(
                        'menu_title' => __( 'Custom Event', 'advance-ecommerce-tracking' ),
                        'menu_slug'  => 'aet-cat-list',
                        'menu_url'   => $this->aet_plugins_url(
                            '',
                            'aet-cat-list',
                            '',
                            '',
                            ''
                        ),
                    ),
                    'aet-premium'     => array(
                        'menu_title' => __( 'Get Premium', 'advance-ecommerce-tracking' ),
                        'menu_slug'  => 'aet-premium',
                        'menu_url'   => $this->aet_plugins_url(
                            '',
                            'aet-premium',
                            '',
                            '',
                            ''
                        ),
                    ),
                ),
            ),
        );
        ?>
		<div class="dots-menu-main">
			<nav>
				<ul>
					<?php 
        $main_current = $current;
        $sub_current = $current;
        foreach ( $wpfp_menus['main_menu'] as $main_menu_slug => $main_wpfp_menu ) {
            if ( 'free_menu' === $main_menu_slug || 'common_menu' === $main_menu_slug ) {
                foreach ( $main_wpfp_menu as $menu_slug => $wpfp_menu ) {
                    if ( 'aet-information' === $main_current ) {
                        $main_current = 'aet-get-started';
                    }
                    if ( 'aet-et-settings-account' === $main_current ) {
                        $main_current = 'aet-get-started';
                    }
                    $class = ( $menu_slug === $main_current ? 'active' : '' );
                    if ( !empty( $wpfp_menu['menu_title'] ) ) {
                        ?>
										<li>
											<a class="dotstore_plugin <?php 
                        echo ( 'aet-premium' === $menu_slug ? esc_attr( 'dots_get_premium' ) : '' );
                        ?> <?php 
                        echo esc_attr( $class );
                        ?>"
											   href="<?php 
                        echo esc_url( $wpfp_menu['menu_url'] );
                        ?>">
												<?php 
                        esc_html_e( $wpfp_menu['menu_title'], 'advance-ecommerce-tracking' );
                        ?>
											</a>
											<?php 
                        if ( isset( $wpfp_menu['sub_menu'] ) && !empty( $wpfp_menu['sub_menu'] ) ) {
                            ?>
												<ul class="sub-menu">
													<?php 
                            foreach ( $wpfp_menu['sub_menu'] as $sub_menu_slug => $wpfp_sub_menu ) {
                                $sub_class = ( $sub_menu_slug === $sub_current ? 'active' : '' );
                                if ( !empty( $wpfp_sub_menu['menu_title'] ) ) {
                                    ?>
															<li>
																<a class="dotstore_plugin <?php 
                                    echo esc_attr( $sub_class );
                                    ?>"
																   href="<?php 
                                    echo esc_url( $wpfp_sub_menu['menu_url'] );
                                    ?>">
																	<?php 
                                    esc_html_e( $wpfp_sub_menu['menu_title'], 'advance-ecommerce-tracking' );
                                    ?>
																</a>
															</li>
														<?php 
                                }
                            }
                            ?>
												</ul>
											<?php 
                        }
                        ?>
										</li>
										<?php 
                    }
                }
            }
        }
        ?>
				</ul>
			</nav>
		</div>
		<?php 
    }

    /**
     * Admin footer review
     *
     * @since 1.0.0
     */
    public function aet_admin_footer_review() {
        $url = '';
        $url = esc_url( 'https://wordpress.org/plugins/woo-ecommerce-tracking-for-google-and-facebook/#reviews' );
        $html = sprintf( wp_kses( __( '<strong>We need your support</strong> to keep updating and improving the plugin. Please <a href="%1$s" target="_blank">help us by leaving a good review</a> :) Thanks!', 'advance-ecommerce-tracking' ), array(
            'strong' => array(),
            'a'      => array(
                'href'   => array(),
                'target' => 'blank',
            ),
        ) ), esc_url( $url ) );
        echo wp_kses_post( $html );
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
    public function aet_ad_get_setting_option( $args ) {
        $option_key = 'aet_' . $args . '_tracking_settings';
        $get_option_data = json_decode( get_option( $option_key ) );
        return $get_option_data;
    }

    /**
     * Plugins URL.
     *
     * @since    3.0
     */
    public function aet_plugins_url(
        $id,
        $page,
        $tab,
        $action,
        $nonce
    ) {
        $query_args = array();
        if ( '' !== $page ) {
            $query_args['page'] = $page;
        }
        if ( '' !== $tab ) {
            $query_args['tab'] = $tab;
        }
        if ( '' !== $action ) {
            $query_args['action'] = $action;
        }
        if ( '' !== $id ) {
            $query_args['id'] = $id;
        }
        if ( '' !== $nonce ) {
            $query_args['_wpnonce'] = wp_create_nonce( 'afrsmnonce' );
        }
        return esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) );
    }

    /**
     * Save analytics data
     *
     * @param array $data Get all tracking data.
     *
     * @return string redirect page
     *
     * @since 3.0
     */
    public function aet_save_settings( $data ) {
        if ( empty( $data ) ) {
            return false;
        }
        $aet_track_save = filter_input( INPUT_POST, 'track_save', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $aet_track_type = filter_input( INPUT_POST, 'track_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $option_key = 'aet_' . $aet_track_type . '_tracking_settings';
        $tracking_settings_array = array();
        if ( 'ecommerce' === $aet_track_save ) {
            $aet_et_conditions_save = filter_input( INPUT_POST, 'aet_et_conditions_save', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            if ( !empty( $data ) && wp_verify_nonce( sanitize_text_field( $aet_et_conditions_save ), 'aet_et_save_action' ) ) {
                $manually_et_px_ver_4 = filter_input( INPUT_POST, 'manually_et_px_ver_4', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $tracking_settings_array['manually_et_px_ver_4'] = $manually_et_px_ver_4;
                $get_at_tracking_option_enable = filter_input( INPUT_POST, 'at_enable', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $at_tracking_option_enable = ( isset( $get_at_tracking_option_enable ) ? sanitize_text_field( $get_at_tracking_option_enable ) : 'UA' );
                $tracking_settings_array['at_enable'] = $at_tracking_option_enable;
                $get_enhance_ecommerce_tracking = filter_input( INPUT_POST, 'enhance_ecommerce_tracking', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $enhance_ecommerce_tracking = ( isset( $get_enhance_ecommerce_tracking ) ? sanitize_text_field( $get_enhance_ecommerce_tracking ) : 'off' );
                $tracking_settings_array['enhance_ecommerce_tracking'] = $enhance_ecommerce_tracking;
                $get_ip_anonymization = filter_input( INPUT_POST, 'ip_anonymization', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $ip_anonymization = ( isset( $get_ip_anonymization ) ? sanitize_text_field( $get_ip_anonymization ) : 'off' );
                $tracking_settings_array['ip_anonymization'] = $ip_anonymization;
                $get_privacy_policy = filter_input( INPUT_POST, 'exl_tracking_privacy_policy', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $privacy_policy = ( isset( $get_privacy_policy ) ? sanitize_text_field( $get_privacy_policy ) : 'off' );
                $tracking_settings_array['privacy_policy'] = $privacy_policy;
                $get_google_analytics_opt_out = filter_input( INPUT_POST, 'google_analytics_opt_out', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $google_analytics_opt_out = ( isset( $get_google_analytics_opt_out ) ? sanitize_text_field( $get_google_analytics_opt_out ) : 'off' );
                $tracking_settings_array['google_analytics_opt_out'] = $google_analytics_opt_out;
            }
        }
        if ( !empty( $tracking_settings_array ) ) {
            update_option( $option_key, wp_json_encode( $tracking_settings_array ) );
        }
        wp_safe_redirect( add_query_arg( array(
            'page' => 'aet-' . $aet_track_type . '-settings',
        ), admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Update id manually.
     *
     * @since 3.0
     */
    public function aet_update_manually_id() {
        check_ajax_referer( 'aet_chk_nonce', 'nonce' );
        $get_val = filter_input( INPUT_GET, 'get_val', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_attr = filter_input( INPUT_GET, 'get_attr', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $get_attr_two = filter_input( INPUT_GET, 'get_attr_two', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( !empty( $get_val ) && isset( $get_val ) ) {
            $set_arry = get_option( 'aet_et_tracking_settings' );
            $set_arr = json_decode( $set_arry, true );
            $set_arr = ( NULL === $set_arr ? array() : $set_arr );
            if ( array_key_exists( 'manually_et_px_ver_4', $set_arr ) ) {
                $set_arr['manually_et_px_ver_4'] = $get_val;
                $nset_array = wp_json_encode( $set_arr );
                update_option( 'aet_et_tracking_settings', $nset_array );
            } else {
                $set_arr['manually_et_px_ver_4'] = $get_val;
                $nset_array = wp_json_encode( $set_arr );
                update_option( 'aet_et_tracking_settings', $nset_array );
            }
        }
        $this->aet_update_selected_ua_id(
            $get_val,
            $get_attr,
            $get_attr_two,
            'update',
            'ajax'
        );
    }

    /**
     * Update UA id.
     *
     * @param string $active_data With urlencode and base64encode data.
     *
     * @param string $request
     *
     * @param string $request_url
     *
     * @param string $action      update data.
     *
     * @since 3.0
     */
    public function aet_update_selected_ua_id(
        $active_data,
        $request,
        $request_url,
        $action,
        $save_type
    ) {
        $option_key = 'selected_data_ua_' . $request;
        if ( 'ajax' === $save_type ) {
            $get_selected_data_ua = $active_data;
        } else {
            $get_selected_data_ua = urldecode( base64_decode( $active_data ) );
        }
        if ( 'update' === $action ) {
            update_option( $option_key, $get_selected_data_ua );
        } else {
            update_option( $option_key, '' );
        }
        $query_param = add_query_arg( array(
            'page' => 'aet-' . $request . '-settings',
        ), admin_url( 'admin.php' ) );
        if ( 'ajax' === $save_type ) {
            echo wp_kses_post( $query_param );
            wp_die();
        } else {
            wp_safe_redirect( $query_param );
            exit;
        }
    }

    /**
     * Setup Link.
     *
     * @since 3.0
     */
    public function aet_setup_link( $args ) {
        $https = filter_input( INPUT_SERVER, 'HTTPS', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $http_host = filter_input( INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( isset( $https ) && $https === 'on' ) {
            $link = 'https';
        } else {
            $link = 'http';
        }
        $link .= '://';
        $link .= $http_host;
        $link .= $request_uri;
        if ( 'ft' === $args ) {
            $server_URL = $this->base_url . '/' . $this->base_path . '/fb';
        } else {
            $server_URL = $this->base_url . '/' . $this->base_path . '/ec';
        }
        $setup_link = $server_URL . '?extra_url=' . rawurlencode( base64_encode( $link ) ) . '&chk=' . base64_encode( 'refer' );
        //Need to remove static URL
        return $setup_link;
    }

    /**
     * Get user roles.
     *
     * @since 3.0
     */
    public function aet_get_editable_user_roles() {
        global $wp_roles;
        $all_roles = $wp_roles->roles;
        $roles = array();
        $editable_roles = apply_filters( 'editable_roles', $all_roles );
        foreach ( $editable_roles as $id => $name ) {
            $roles[$id] = translate_user_role( $name['name'] );
        }
        return $roles;
    }

    /**
     * Our pages URL.
     *
     * @param string $page
     *
     * @param int    $id
     *
     * @param string $action
     *
     * @param string $wp_nonce
     *
     * @param bool   $admin_url
     *
     * @return string $url
     *
     * @since 3.0
     */
    public function aet_pages_url(
        $page,
        $id,
        $action,
        $wp_nonce,
        $admin_url
    ) {
        $args = array();
        if ( $page ) {
            $args['page'] = $page;
        }
        if ( $id ) {
            $args['id'] = $id;
        }
        if ( $action ) {
            $args['action'] = $action;
        }
        if ( $wp_nonce ) {
            $args['_wpnonce'] = $wp_nonce;
        }
        if ( true === $admin_url ) {
            $get_admin_url = admin_url( 'admin.php' );
        } else {
            $get_admin_url = admin_url( 'admin.php' );
            //You can change as per your request
        }
        add_query_arg( $args, $get_admin_url );
        $url = esc_url( add_query_arg( $args, $get_admin_url ) );
        return $url;
    }

    /**
     * Get dynamic promotional bar of plugin
     *
     * @param   String  $plugin_slug  slug of the plugin added in the site option
     * @since    3.9.3
     * 
     * @return  null
     */
    public function aet_get_promotional_bar( $plugin_slug = '' ) {
        $promotional_bar_upi_url = AET_STORE_URL . 'wp-json/dpb-promotional-banner/v2/dpb-promotional-banner?' . wp_rand();
        $promotional_banner_request = wp_remote_get( $promotional_bar_upi_url );
        //phpcs:ignore
        if ( empty( $promotional_banner_request->errors ) ) {
            $promotional_banner_request_body = $promotional_banner_request['body'];
            $promotional_banner_request_body = json_decode( $promotional_banner_request_body, true );
            echo '<div class="dynamicbar_wrapper">';
            if ( !empty( $promotional_banner_request_body ) && is_array( $promotional_banner_request_body ) ) {
                foreach ( $promotional_banner_request_body as $promotional_banner_request_body_data ) {
                    $promotional_banner_id = $promotional_banner_request_body_data['promotional_banner_id'];
                    $promotional_banner_cookie = $promotional_banner_request_body_data['promotional_banner_cookie'];
                    $promotional_banner_image = $promotional_banner_request_body_data['promotional_banner_image'];
                    $promotional_banner_description = $promotional_banner_request_body_data['promotional_banner_description'];
                    $promotional_banner_button_group = $promotional_banner_request_body_data['promotional_banner_button_group'];
                    $dpb_schedule_campaign_type = $promotional_banner_request_body_data['dpb_schedule_campaign_type'];
                    $promotional_banner_target_audience = $promotional_banner_request_body_data['promotional_banner_target_audience'];
                    if ( !empty( $promotional_banner_target_audience ) ) {
                        $plugin_keys = array();
                        if ( is_array( $promotional_banner_target_audience ) ) {
                            foreach ( $promotional_banner_target_audience as $list ) {
                                $plugin_keys[] = $list['value'];
                            }
                        } else {
                            $plugin_keys[] = $promotional_banner_target_audience['value'];
                        }
                        $display_banner_flag = false;
                        if ( in_array( 'all_customers', $plugin_keys, true ) || in_array( $plugin_slug, $plugin_keys, true ) ) {
                            $display_banner_flag = true;
                        }
                    }
                    if ( true === $display_banner_flag ) {
                        if ( 'default' === $dpb_schedule_campaign_type ) {
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $banner_cookie_visible_once = filter_input( INPUT_COOKIE, 'banner_show_once_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $flag = false;
                            if ( empty( $banner_cookie_show ) && empty( $banner_cookie_visible_once ) ) {
                                setcookie( 'banner_show_' . $promotional_banner_cookie, 'yes', time() + 86400 * 7 );
                                //phpcs:ignore
                                setcookie( 'banner_show_once_' . $promotional_banner_cookie, 'yes' );
                                //phpcs:ignore
                                $flag = true;
                            }
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            if ( !empty( $banner_cookie_show ) || true === $flag ) {
                                $banner_cookie = filter_input( INPUT_COOKIE, 'banner_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                                $banner_cookie = ( isset( $banner_cookie ) ? $banner_cookie : '' );
                                if ( empty( $banner_cookie ) && 'yes' !== $banner_cookie ) {
                                    ?>
                            	<div class="dpb-popup <?php 
                                    echo ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' );
                                    ?>">
                                    <?php 
                                    if ( !empty( $promotional_banner_image ) ) {
                                        ?>
                                        <img src="<?php 
                                        echo esc_url( $promotional_banner_image );
                                        ?>"/>
                                        <?php 
                                    }
                                    ?>
                                    <div class="dpb-popup-meta">
                                        <p>
                                            <?php 
                                    echo wp_kses_post( str_replace( array('<p>', '</p>'), '', $promotional_banner_description ) );
                                    if ( !empty( $promotional_banner_button_group ) ) {
                                        foreach ( $promotional_banner_button_group as $promotional_banner_button_group_data ) {
                                            ?>
                                                    <a href="<?php 
                                            echo esc_url( $promotional_banner_button_group_data['promotional_banner_button_link'] );
                                            ?>" target="_blank"><?php 
                                            echo esc_html( $promotional_banner_button_group_data['promotional_banner_button_text'] );
                                            ?></a>
                                                    <?php 
                                        }
                                    }
                                    ?>
                                    	</p>
                                    </div>
                                    <a href="javascript:void(0);" data-bar-id="<?php 
                                    echo esc_attr( $promotional_banner_id );
                                    ?>" data-popup-name="<?php 
                                    echo ( isset( $promotional_banner_cookie ) ? esc_attr( $promotional_banner_cookie ) : 'default-banner' );
                                    ?>" class="dpbpop-close"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><path id="Icon_material-close" data-name="Icon material-close" d="M17.5,8.507,16.493,7.5,12.5,11.493,8.507,7.5,7.5,8.507,11.493,12.5,7.5,16.493,8.507,17.5,12.5,13.507,16.493,17.5,17.5,16.493,13.507,12.5Z" transform="translate(-7.5 -7.5)" fill="#acacac"/></svg></a>
                                </div>
                                <?php 
                                }
                            }
                        } else {
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $banner_cookie_visible_once = filter_input( INPUT_COOKIE, 'banner_show_once_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            $flag = false;
                            if ( empty( $banner_cookie_show ) && empty( $banner_cookie_visible_once ) ) {
                                setcookie( 'banner_show_' . $promotional_banner_cookie, 'yes' );
                                //phpcs:ignore
                                setcookie( 'banner_show_once_' . $promotional_banner_cookie, 'yes' );
                                //phpcs:ignore
                                $flag = true;
                            }
                            $banner_cookie_show = filter_input( INPUT_COOKIE, 'banner_show_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                            if ( !empty( $banner_cookie_show ) || true === $flag ) {
                                $banner_cookie = filter_input( INPUT_COOKIE, 'banner_' . $promotional_banner_cookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                                $banner_cookie = ( isset( $banner_cookie ) ? $banner_cookie : '' );
                                if ( empty( $banner_cookie ) && 'yes' !== $banner_cookie ) {
                                    ?>
                    			<div class="dpb-popup <?php 
                                    echo ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' );
                                    ?>">
                                    <?php 
                                    if ( !empty( $promotional_banner_image ) ) {
                                        ?>
                                            <img src="<?php 
                                        echo esc_url( $promotional_banner_image );
                                        ?>"/>
                                        <?php 
                                    }
                                    ?>
                                    <div class="dpb-popup-meta">
                                        <p>
                                            <?php 
                                    echo wp_kses_post( str_replace( array('<p>', '</p>'), '', $promotional_banner_description ) );
                                    if ( !empty( $promotional_banner_button_group ) ) {
                                        foreach ( $promotional_banner_button_group as $promotional_banner_button_group_data ) {
                                            ?>
                                                    <a href="<?php 
                                            echo esc_url( $promotional_banner_button_group_data['promotional_banner_button_link'] );
                                            ?>" target="_blank"><?php 
                                            echo esc_html( $promotional_banner_button_group_data['promotional_banner_button_text'] );
                                            ?></a>
                                                    <?php 
                                        }
                                    }
                                    ?>
                                        </p>
                                    </div>
                                    <a href="javascript:void(0);" data-bar-id="<?php 
                                    echo esc_attr( $promotional_banner_id );
                                    ?>" data-popup-name="<?php 
                                    echo ( isset( $promotional_banner_cookie ) ? esc_html( $promotional_banner_cookie ) : 'default-banner' );
                                    ?>" class="dpbpop-close"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><path id="Icon_material-close" data-name="Icon material-close" d="M17.5,8.507,16.493,7.5,12.5,11.493,8.507,7.5,7.5,8.507,11.493,12.5,7.5,16.493,8.507,17.5,12.5,13.507,16.493,17.5,17.5,16.493,13.507,12.5Z" transform="translate(-7.5 -7.5)" fill="#acacac"/></svg></a>
                                </div>
                                <?php 
                                }
                            }
                        }
                    }
                }
            }
            echo '</div>';
        }
    }

    /**
     * Get and save plugin setup wizard data
     * 
     * @since    3.9.3
     * 
     */
    public function aet_plugin_setup_wizard_submit() {
        check_ajax_referer( 'wizard_ajax_nonce', 'nonce' );
        $survey_list = filter_input( INPUT_GET, 'survey_list', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( !empty( $survey_list ) && 'Select One' !== $survey_list ) {
            update_option( 'aet_where_hear_about_us', $survey_list );
        }
        wp_die();
    }

    /**
     * Send setup wizard data to sendinblue
     * 
     * @since    3.9.3
     * 
     */
    public function aet_send_wizard_data_after_plugin_activation() {
        $send_wizard_data = filter_input( INPUT_GET, 'send-wizard-data', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( isset( $send_wizard_data ) && !empty( $send_wizard_data ) ) {
            if ( !get_option( 'aet_data_submited_in_sendiblue' ) ) {
                $aet_where_hear = get_option( 'aet_where_hear_about_us' );
                $get_user = aet_fs()->get_user();
                $data_insert_array = array();
                if ( isset( $get_user ) && !empty( $get_user ) ) {
                    $data_insert_array = array(
                        'user_email'              => $get_user->email,
                        'ACQUISITION_SURVEY_LIST' => $aet_where_hear,
                    );
                }
                $feedback_api_url = AET_STORE_URL . 'wp-json/dotstore-sendinblue-data/v2/dotstore-sendinblue-data?' . wp_rand();
                $query_url = $feedback_api_url . '&' . http_build_query( $data_insert_array );
                if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
                    $response = vip_safe_wp_remote_get(
                        $query_url,
                        3,
                        1,
                        20
                    );
                } else {
                    $response = wp_remote_get( $query_url );
                    // phpcs:ignore
                }
                if ( !is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                    update_option( 'aet_data_submited_in_sendiblue', '1' );
                    delete_option( 'aet_where_hear_about_us' );
                }
            }
        }
    }

}
