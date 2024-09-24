<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$plugin_name = AET_PLUGIN_NAME;
$plugin_version = AET_VERSION;
$version_label = '';
$plugin_slug = '';
$version_label = __( 'Free', 'advance-ecommerce-tracking' );
$plugin_slug = 'basic_e_tracking';
$current_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$aet_get_started = ( isset( $current_page ) && 'aet-get-started' === $current_page ? 'active' : '' );
$aet_admin_object = new Advance_Ecommerce_Tracking_Admin('', '');
?>
<div id="dotsstoremain">
	<div class="all-pad">
        <?php 
$aet_admin_object->aet_get_promotional_bar( $plugin_slug );
?>
		<header class="dots-header">
			<div class="dots-plugin-details">
				<div class="dots-header-left">
					<div class="dots-logo-main">
						<img src="<?php 
echo esc_url( AET_PLUGIN_URL . 'admin/images/WSFL-1.png' );
?>">
					</div>
					<div class="plugin-name">
						<div class="title"><?php 
esc_html_e( $plugin_name, 'advance-ecommerce-tracking' );
?></div>
					</div>
					<span class="version-label <?php 
echo esc_attr( $plugin_slug );
?>"><?php 
echo esc_html( $version_label );
?></span>
                    <span class="version-number"><?php 
echo esc_html( 'v' . $plugin_version );
?></span>
				</div>
				<div class="dots-header-right">
                    <div class="button-dots">
                        <a target="_blank" href="<?php 
echo esc_url( 'http://www.thedotstore.com/support/' );
?>"><?php 
esc_html_e( 'Support', 'advance-ecommerce-tracking' );
?></a>
                    </div>
                    <div class="button-dots">
                        <a target="_blank" href="<?php 
echo esc_url( 'https://www.thedotstore.com/feature-requests/' );
?>"><?php 
esc_html_e( 'Suggest', 'advance-ecommerce-tracking' );
?></a>
                    </div>
                    <?php 
$plugin_help_url = 'https://docs.thedotstore.com/category/545-premium-plugin-settings';
?>
                    <div class="button-dots <?php 
echo ( aet_fs()->is__premium_only() && aet_fs()->can_use_premium_code() ? '' : 'last-link-button' );
?>">
                        <a target="_blank" href="<?php 
echo esc_url( $plugin_help_url );
?>"><?php 
esc_html_e( 'Help', 'advance-ecommerce-tracking' );
?></a>
                    </div>

                    <div class="button-dots">
                        <?php 
?>
                            <a class="dots-upgrade-btn" target="_blank" href="javascript:void(0);"><?php 
esc_html_e( 'Upgrade Now', 'advance-ecommerce-tracking' );
?></a>
                            <?php 
?>
                    </div>		
				</div>
			</div>
            <div class="dots-bottom-menu-main">
                <?php 
$current_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$aet_admin_object->aet_menus( $current_page );
?>
                <div class="dots-getting-started">
                    <a href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'aet-get-started',
), admin_url( 'admin.php' ) ) );
?>" class="<?php 
echo esc_attr( $aet_get_started );
?>"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5zM3.25 12a8.75 8.75 0 1117.5 0 8.75 8.75 0 01-17.5 0zM12 8.75a1.5 1.5 0 01.167 2.99c-.465.052-.917.44-.917 1.01V14h1.5v-.845A3 3 0 109 10.25h1.5a1.5 1.5 0 011.5-1.5zM11.25 15v1.5h1.5V15h-1.5z" fill="#a0a0a0"></path></svg></a>
                </div>
            </div>
		</header>
        <!-- Upgrade to pro popup -->
        <?php 
if ( !(aet_fs()->is__premium_only() && aet_fs()->can_use_premium_code()) ) {
    require_once AET_PLUGIN_DIR_PATH . 'admin/partials/dots-upgrade-popup.php';
}
?>
        <div class="dots-settings-inner-main">
            <div class="dots-settings-left-side">
                <hr class="wp-header-end" />