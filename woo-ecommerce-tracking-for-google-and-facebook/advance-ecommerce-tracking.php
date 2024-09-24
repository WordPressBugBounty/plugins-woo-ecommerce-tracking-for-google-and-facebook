<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.thedotstore.com
 * @since             3.0
 * @package           Advance_Ecommerce_Tracking
 *
 * @wordpress-plugin
 * Plugin Name: Advance Ecommerce Tracking
 * Plugin URI:        https://www.thedotstore.com/woocommerce-enhanced-ecommerce-analytics-integration-with-conversion-tracking
 * Description:       Allows you to use Enhanced Ecommerce tracking without adding any new complex codes on your WooCommerce.
 * Version:           3.8.0
 * Author:            theDotstore
 * Author URI:        https://www.thedotstore.com
 * License:           GPLv3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       advance-ecommerce-tracking
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * WC requires at least: 5.3
 * WC tested up to:      9.1.2
 * WP tested up to:      6.5.5
 * Requires PHP:         7.2
 * Requires at least:    5.0
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
if ( !function_exists( 'aet_fs' ) ) {
    // Create a helper function for easy SDK access.
    function aet_fs() {
        global $aet_fs;
        if ( !isset( $aet_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $aet_fs = fs_dynamic_init( array(
                'id'              => '3475',
                'slug'            => 'advance-ecommerce-tracking',
                'type'            => 'plugin',
                'public_key'      => 'pk_0dbe70558f17f7a0881498011f656',
                'is_premium'      => false,
                'premium_suffix'  => 'Premium',
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'has_affiliation' => 'selected',
                'menu'            => array(
                    'slug'       => 'aet-et-settings',
                    'first-path' => 'admin.php?page=aet-et-settings',
                    'contact'    => false,
                    'support'    => false,
                    'network'    => true,
                ),
                'is_live'         => true,
            ) );
        }
        return $aet_fs;
    }

    // Init Freemius.
    aet_fs();
    // Signal that SDK was initiated.
    do_action( 'aet_fs_loaded' );
    aet_fs()->get_upgrade_url();
    aet_fs()->add_action( 'after_uninstall', 'aet_fs_uninstall_cleanup' );
}
/**
 * Currently plugin version.
 * Start at version 3.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if ( !defined( 'AET_VERSION' ) ) {
    define( 'AET_VERSION', '3.8.0' );
}
if ( !defined( 'AET_PLUGIN_URL' ) ) {
    define( 'AET_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( !defined( 'AET_PLUGIN_DIR' ) ) {
    define( 'AET_PLUGIN_DIR', dirname( __FILE__ ) );
}
if ( !defined( 'AET_PLUGIN_DIR_PATH' ) ) {
    define( 'AET_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'AET_PLUGIN_NAME' ) ) {
    define( 'AET_PLUGIN_NAME', 'Ecommerce Tracking' );
}
if ( !defined( 'AET_VERSION_NAME' ) ) {
    define( 'AET_VERSION_NAME', 'Free Version' );
}
if ( !defined( 'DEBUG_OPTION' ) ) {
    define( 'DEBUG_OPTION', false );
}
// Define plugin basename constant
if ( !defined( 'AET_PRO_PLUGIN_BASENAME' ) ) {
    define( 'AET_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( !defined( 'AET_STORE_URL' ) ) {
    define( 'AET_STORE_URL', 'https://www.thedotstore.com/' );
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-advance-ecommerce-tracking-activator.php
 */
function activate_advance_ecommerce_tracking() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-advance-ecommerce-tracking-activator.php';
    Advance_Ecommerce_Tracking_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-advance-ecommerce-tracking-deactivator.php
 */
function deactivate_advance_ecommerce_tracking() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-advance-ecommerce-tracking-deactivator.php';
    Advance_Ecommerce_Tracking_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_advance_ecommerce_tracking' );
register_deactivation_hook( __FILE__, 'deactivate_advance_ecommerce_tracking' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-advance-ecommerce-tracking.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    3.0
 */
function run_advance_ecommerce_tracking() {
    $plugin = new Advance_Ecommerce_Tracking();
    $plugin->run();
}

add_action( 'plugins_loaded', 'aet_plugin_init' );
function aet_plugin_init() {
    /* Check if WooCommerce is Active */
    $active_plugins = get_option( 'active_plugins', array() );
    /* If this is a multisite installation, consider network-wide plugins */
    if ( is_multisite() ) {
        $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
        $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
        $active_plugins = array_unique( $active_plugins );
        /* If WooCommerce is not active, display an admin notice and deactivate this plugin */
        if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', $active_plugins ), true ) ) {
            add_action( 'admin_notices', 'aet_plugin_admin_notice' );
            add_action( 'admin_init', 'aet_deactivate_plugin' );
        } else {
            /* If WooCommerce is active, run advance eCommerce tracking */
            run_advance_ecommerce_tracking();
        }
    } else {
        /* For non-multisite installations, check if WooCommerce is active */
        if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
            /* If WooCommerce is not active, display an admin notice and deactivate this plugin */
            add_action( 'admin_notices', 'aet_plugin_admin_notice' );
            add_action( 'admin_init', 'aet_deactivate_plugin' );
        } else {
            /* If WooCommerce is active, run advance eCommerce tracking */
            run_advance_ecommerce_tracking();
        }
    }
    /* Load the plugin's text domain for localization */
    load_plugin_textdomain( 'advance-ecommerce-tracking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * Show admin notice in case of WooCommerce plguin is missing
 */
function aet_plugin_admin_notice() {
    $aet_plugin = AET_PLUGIN_NAME;
    $wc_plugin = 'WooCommerce';
    echo '<div class="error"><p>' . sprintf( wp_kses_post( '%1$s is deactivated as it requires %2$s  to be installed and active.' ), '<strong>' . esc_html( $aet_plugin ) . '</strong>', '<strong>' . esc_html( $wc_plugin ) . '</strong>' ) . '</p></div>';
}

/**
 * Deactivate the plugin.
 */
function aet_deactivate_plugin() {
    deactivate_plugins( plugin_basename( __FILE__ ) );
    $activate_plugin_unset = filter_input( INPUT_GET, 'activate', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    unset($activate_plugin_unset);
}

/**
 * Admin notice for plugin activation.
 *
 * @since    3.0
 */
function aet_admin_notice_function() {
    $screen = get_current_screen();
    $screen_id = ( $screen ? $screen->id : '' );
    if ( strpos( $screen_id, 'dotstore-plugins_page' ) || strpos( $screen_id, 'plugins' ) ) {
        $aet_admin = filter_input( INPUT_GET, 'aet-hide-notice', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $wc_notice_nonce = filter_input( INPUT_GET, '_aet_notice_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if ( isset( $aet_admin ) && $aet_admin === 'aet_admin' && wp_verify_nonce( sanitize_text_field( $wc_notice_nonce ), 'aet_hide_notices_nonce' ) ) {
            delete_transient( 'aet-admin-notice' );
        }
        /* Check transient, if available display notice */
        if ( get_transient( 'aet-admin-notice' ) ) {
            ?>
			<div id="message"
			     class="updated woocommerce-message woocommerce-admin-promo-messages welcome-panel aet-panel">
				<a class="woocommerce-message-close notice-dismiss"
				   href="<?php 
            echo esc_url( wp_nonce_url( add_query_arg( 'aet-hide-notice', 'aet_admin' ), 'aet_hide_notices_nonce', '_aet_notice_nonce' ) );
            ?>">
				</a>
				<p>
					<?php 
            echo sprintf( wp_kses( __( '<strong>Advance Ecommerce Tracking is successfully installed and ready to go.</strong>', 'advance-ecommerce-tracking' ), array(
                'strong' => array(),
            ), esc_url( admin_url( 'options-general.php' ) ) ) );
            ?>
				</p>
				<p>
					<?php 
            echo wp_kses_post( __( 'Click on settings button and do your setting as per your requirement.', 'advance-ecommerce-tracking' ) );
            ?>
				</p>
				<?php 
            $url = add_query_arg( array(
                'page' => 'aet-pro-list',
            ), admin_url( 'admin.php' ) );
            ?>
				<p>
					<a href="<?php 
            echo esc_url( $url );
            ?>" class="button button-primary">
						<?php 
            esc_html_e( 'Settings', 'advance-ecommerce-tracking' );
            ?>
					</a>
				</p>
			</div>
			<?php 
        }
    } else {
        return;
    }
}

function aet_upgrade_completed(  $upgrader_object, $options  ) {
    $our_plugin = plugin_basename( __FILE__ );
    if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
        foreach ( $options['plugins'] as $plugin ) {
            if ( $plugin === $our_plugin ) {
                delete_transient( 'aet_updated' );
            }
        }
    }
}

add_action(
    'upgrader_process_complete',
    'aet_upgrade_completed',
    10,
    2
);
/**
 * Hide freemius account tab
 *
 * @since    3.9.3
 */
if ( !function_exists( 'aet_hide_account_tab' ) ) {
    function aet_hide_account_tab() {
        return true;
    }

    aet_fs()->add_filter( 'hide_account_tabs', 'aet_hide_account_tab' );
}
/**
 * Include plugin header on freemius account page
 *
 * @since    1.0.0
 */
if ( !function_exists( 'aet_load_plugin_header_after_account' ) ) {
    function aet_load_plugin_header_after_account() {
        require_once plugin_dir_path( __FILE__ ) . 'admin/partials/header/plugin-header.php';
    }

    aet_fs()->add_action( 'after_account_details', 'aet_load_plugin_header_after_account' );
}
/**
 * Hide billing and payments details from freemius account page
 *
 * @since    3.9.3
 */
if ( !function_exists( 'aet_hide_billing_and_payments_info' ) ) {
    function aet_hide_billing_and_payments_info() {
        return true;
    }

    aet_fs()->add_action( 'hide_billing_and_payments_info', 'aet_hide_billing_and_payments_info' );
}
/**
 * Hide powerd by popup from freemius account page
 *
 * @since    3.9.3
 */
if ( !function_exists( 'aet_hide_freemius_powered_by' ) ) {
    function aet_hide_freemius_powered_by() {
        return true;
    }

    aet_fs()->add_action( 'hide_freemius_powered_by', 'aet_hide_freemius_powered_by' );
}
/**
 * Start plugin setup wizard before license activation screen
 *
 * @since    3.9.3
 */
if ( !function_exists( 'aet_load_plugin_setup_wizard_connect_before' ) ) {
    function aet_load_plugin_setup_wizard_connect_before() {
        require_once plugin_dir_path( __FILE__ ) . 'admin/partials/dots-plugin-setup-wizard.php';
        ?>
        <div class="tab-panel" id="step5">
            <div class="ds-wizard-wrap">
                <div class="ds-wizard-content">
                    <h2 class="cta-title"><?php 
        echo esc_html__( 'Activate Plugin', 'advance-ecommerce-tracking' );
        ?></h2>
                </div>
        <?php 
    }

    aet_fs()->add_action( 'connect/before', 'aet_load_plugin_setup_wizard_connect_before' );
}
/**
 * End plugin setup wizard after license activation screen
 *
 * @since    3.9.3
 */
if ( !function_exists( 'aet_load_plugin_setup_wizard_connect_after' ) ) {
    function aet_load_plugin_setup_wizard_connect_after() {
        ?>
        </div>
        </div>
        </div>
        </div>
        <?php 
    }

    aet_fs()->add_action( 'connect/after', 'aet_load_plugin_setup_wizard_connect_after' );
}
/**
 * 
 * HPOS compatibility check
 * 
 */
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );