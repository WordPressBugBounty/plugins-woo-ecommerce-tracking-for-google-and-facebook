<?php
/**
 * Fired during plugin activation
 *
 * @link       https://www.thedotstore.com
 * @since      3.0
 *
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/includes
 */
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      3.0
 * @package    Advance_Ecommerce_Tracking
 * @subpackage Advance_Ecommerce_Tracking/includes
 * @author     Thedotstore <wordpress@multidots.in>
 */
class Advance_Ecommerce_Tracking_Activator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    3.0
	 */
	public static function activate() {
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) && ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
			wp_die( "<strong> Ecommerce Tracking For WooCommerce</strong> Plugin requires <strong>WooCommerce</strong> <a href='" . esc_url( get_admin_url( null, 'plugins.php' ) ) . "'>Plugins page</a>." );
		} else {
			set_transient( '_aet_welcome_ecommerce_screen_activation_redirect_data', true, 30 );
		}
	}
}
