<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.thedotstore.com
 * @since      3.0
 *
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/includes
 */
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      3.0
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/includes
 * @author     Thedotstore <wordpress@multidots.in>
 */
if ( !class_exists( 'Advance_Ecommerce_Tracking' ) ) {
    class Advance_Ecommerce_Tracking {
        /**
         * The loader that's responsible for maintaining and registering all hooks that power
         * the plugin.
         *
         * @since    3.0
         * @access   protected
         * @var      Advance_Ecommerce_Tracking_Loader $loader Maintains and registers all hooks for the plugin.
         */
        protected $loader;

        /**
         * The unique identifier of this plugin.
         *
         * @since    3.0
         * @access   protected
         * @var      string $plugin_name The string used to uniquely identify this plugin.
         */
        protected $plugin_name;

        /**
         * The current version of the plugin.
         *
         * @since    3.0
         * @access   protected
         * @var      string $version The current version of the plugin.
         */
        protected $version;

        /**
         * Define the core functionality of the plugin.
         *
         * Set the plugin name and the plugin version that can be used throughout the plugin.
         * Load the dependencies, define the locale, and set the hooks for the admin area and
         * the public-facing side of the site.
         *
         * @since    3.0
         */
        public function __construct() {
            if ( defined( 'AET_VERSION' ) ) {
                $this->version = AET_VERSION;
            } else {
                $this->version = '3.0';
            }
            $this->plugin_name = 'advance-ecommerce-tracking';
            $this->load_dependencies();
            $this->set_locale();
            $this->define_admin_hooks();
            $this->define_public_hooks();
            add_filter(
                'plugin_row_meta',
                array($this, 'plugin_row_meta_action_links'),
                20,
                3
            );
        }

        /**
         * Load the required dependencies for this plugin.
         *
         * Include the following files that make up the plugin:
         *
         * - Advance_Ecommerce_Tracking_Loader. Orchestrates the hooks of the plugin.
         * - Advance_Ecommerce_Tracking_i18n. Defines internationalization functionality.
         * - Advance_Ecommerce_Tracking_Admin. Defines all hooks for the admin area.
         * - Advance_Ecommerce_Tracking_Public. Defines all hooks for the public side of the site.
         *
         * Create an instance of the loader which will be used to register the hooks
         * with WordPress.
         *
         * @since    3.0
         * @access   private
         */
        private function load_dependencies() {
            /**
             * The class responsible for orchestrating the actions and filters of the
             * core plugin.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advance-ecommerce-tracking-loader.php';
            /**
             * The class responsible for defining internationalization functionality
             * of the plugin.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-advance-ecommerce-tracking-i18n.php';
            /**
             * The class responsible for defining all actions that occur in the admin area.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-advance-ecommerce-tracking-admin.php';
            /**
             * The class responsible for defining all actions that occur in the public-facing
             * side of the site.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/required_function.php';
            /**
             * The class responsible for defining all actions that occur in the public-facing
             * side of the site.
             */
            require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-advance-ecommerce-tracking-public.php';
            $this->loader = new Advance_Ecommerce_Tracking_Loader();
        }

        /**
         * Define the locale for this plugin for internationalization.
         *
         * Uses the Advance_Ecommerce_Tracking_i18n class in order to set the domain and to register the hook
         * with WordPress.
         *
         * @since    3.0
         * @access   private
         */
        private function set_locale() {
            $plugin_i18n = new Advance_Ecommerce_Tracking_i18n();
            $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
        }

        /**
         * Register all of the hooks related to the admin area functionality
         * of the plugin.
         *
         * @since    3.0
         * @access   private
         */
        private function define_admin_hooks() {
            $plugin_admin = new Advance_Ecommerce_Tracking_Admin($this->get_plugin_name(), $this->get_version());
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'aet_enqueue_styles' );
            $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'aet_enqueue_scripts' );
            $this->loader->add_action( 'admin_init', $plugin_admin, 'aet_welcome_ecommerce_tracking_do_activation_redirect' );
            $this->loader->add_action( 'admin_head', $plugin_admin, 'aet_dot_store_icon_css' );
            $this->loader->add_action( 'admin_menu', $plugin_admin, 'aet_admin_menu' );
            $this->loader->add_action( 'admin_head', $plugin_admin, 'aet_remove_admin_submenus' );
            $this->loader->add_action( 'wp_ajax_aet_update_manually_id', $plugin_admin, 'aet_update_manually_id' );
            $this->loader->add_action( 'wp_ajax_aet_wc_multiple_delete_row__premium_only', $plugin_admin, 'aet_wc_multiple_delete_row__premium_only' );
            $this->loader->add_action( 'wp_ajax_aet_plugin_setup_wizard_submit', $plugin_admin, 'aet_plugin_setup_wizard_submit' );
            $this->loader->add_action( 'admin_init', $plugin_admin, 'aet_send_wizard_data_after_plugin_activation' );
            $get_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
            if ( !empty( $get_page ) && false !== strpos( $get_page, 'aet' ) ) {
                $this->loader->add_filter( 'admin_footer_text', $plugin_admin, 'aet_admin_footer_review' );
            }
        }

        /**
         * Add review stars in plugin row meta
         *
         * @since 1.0.0
         */
        public function plugin_row_meta_action_links( $plugin_meta, $plugin_file, $plugin_data ) {
            if ( isset( $plugin_data['TextDomain'] ) && $plugin_data['TextDomain'] !== 'advance-ecommerce-tracking' ) {
                return $plugin_meta;
            }
            $url = '';
            $url = esc_url( 'https://wordpress.org/plugins/woo-ecommerce-tracking-for-google-and-facebook/#reviews' );
            $plugin_meta[] = sprintf( '<a href="%s" target="_blank" style="color:#f5bb00;">%s</a>', $url, esc_html( '★★★★★' ) );
            return $plugin_meta;
        }

        /**
         * The name of the plugin used to uniquely identify it within the context of
         * WordPress and to define internationalization functionality.
         *
         * @return    string    The name of the plugin.
         * @since     3.0
         */
        public function get_plugin_name() {
            return $this->plugin_name;
        }

        /**
         * Retrieve the version number of the plugin.
         *
         * @return    string    The version number of the plugin.
         * @since     3.0
         */
        public function get_version() {
            return $this->version;
        }

        /**
         * Register all of the hooks related to the public-facing functionality
         * of the plugin.
         *
         * @since    3.0
         * @access   private
         */
        private function define_public_hooks() {
            if ( !is_admin() ) {
                $plugin_public = new Advance_Ecommerce_Tracking_Public($this->get_plugin_name(), $this->get_version());
                $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
                $ua = get_option( 'selected_data_ua_et' );
                $track_user = aet_tracking_user( 'et' );
                if ( $ua ) {
                    $aet_et_tracking_settings = json_decode( get_option( 'aet_et_tracking_settings' ), true );
                    $mepfour = ( isset( $aet_et_tracking_settings['manually_et_px_ver_4'] ) ? $aet_et_tracking_settings['manually_et_px_ver_4'] : '' );
                    if ( empty( $mepfour ) ) {
                        $mepfour = false;
                    }
                    if ( !empty( $aet_et_tracking_settings ) && isset( $aet_et_tracking_settings['at_enable'] ) && isset( $aet_et_tracking_settings['enhance_ecommerce_tracking'] ) ) {
                        $aet_enable = $aet_et_tracking_settings['at_enable'];
                        $enhance_ecommerce_tracking = $aet_et_tracking_settings['enhance_ecommerce_tracking'];
                    } else {
                        $aet_enable = 'off';
                        $enhance_ecommerce_tracking = 'off';
                        $mepfour = false;
                    }
                } else {
                    $aet_enable = 'off';
                    $enhance_ecommerce_tracking = 'off';
                    $mepfour = false;
                }
                if ( 'UA' === $aet_enable || 'BOTH' === $aet_enable || 'on' === $aet_enable ) {
                    $this->loader->add_action( 'wp_head', $plugin_public, 'aet_add_tracking_code' );
                    if ( 'on' === $enhance_ecommerce_tracking ) {
                        $this->loader->add_filter(
                            'aet_tracking_require_filter',
                            $plugin_public,
                            'aet_require_ec',
                            10,
                            2
                        );
                        $this->loader->add_action( 'woocommerce_after_checkout_form', $plugin_public, 'aet_checkout_process' );
                        $this->loader->add_action(
                            'woocommerce_checkout_order_processed',
                            $plugin_public,
                            'aet_store_order_id',
                            10,
                            2
                        );
                        $this->loader->add_action(
                            'woocommerce_order_status_processing',
                            $plugin_public,
                            'aet_order_pro_comp',
                            10
                        );
                        $this->loader->add_action(
                            'woocommerce_order_status_completed',
                            $plugin_public,
                            'aet_order_pro_comp',
                            10
                        );
                        $this->loader->add_action(
                            'woocommerce_thankyou',
                            $plugin_public,
                            'aet_order_pro_comp',
                            10
                        );
                        $this->loader->add_filter( 'woocommerce_get_return_url', $plugin_public, 'aet_change_return_url' );
                        $this->loader->add_filter( 'http_request_timeout', $plugin_public, 'aet_post_timeout' );
                    }
                }
                if ( ('BOTH' === $aet_enable || 'GA4' === $aet_enable) && $track_user ) {
                    if ( 'on' === $enhance_ecommerce_tracking && $mepfour !== false ) {
                        $this->loader->add_action(
                            'woocommerce_thankyou',
                            $plugin_public,
                            'aet_ga_thankyou',
                            10,
                            1
                        );
                    }
                }
                $aet_4_enable = $aet_enable;
                if ( ('GA4' === $aet_4_enable || 'BOTH' === $aet_4_enable) && $track_user ) {
                    if ( $mepfour !== false ) {
                        $this->loader->add_action( 'wp_head', $plugin_public, 'aet_4_add_tracking_code' );
                    }
                }
            }
        }

        /**
         * Run the loader to execute all of the hooks with WordPress.
         *
         * @since    3.0
         */
        public function run() {
            $this->loader->run();
        }

        /**
         * The reference to the class that orchestrates the hooks with the plugin.
         *
         * @return    Advance_Ecommerce_Tracking_Loader    Orchestrates the hooks of the plugin.
         * @since     3.0
         */
        public function get_loader() {
            return $this->loader;
        }

    }

}