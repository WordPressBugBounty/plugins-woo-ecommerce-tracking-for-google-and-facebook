<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once( plugin_dir_path( __FILE__ ) . 'header/plugin-header.php' );
$submit_text      = __( 'Save changes', 'advance-ecommerce-tracking' );
$track_setting    = filter_input( INPUT_POST, 'track_setting', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$aet_admin_object = new Advance_Ecommerce_Tracking_Admin( '', '' );
if ( isset( $track_setting ) ) {
	$post_wpnonce         = filter_input( INPUT_POST, 'aet_et_conditions_save', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$post_retrieved_nonce = isset( $post_wpnonce ) ? sanitize_text_field( wp_unslash( $post_wpnonce ) ) : '';
	if ( ! wp_verify_nonce( $post_retrieved_nonce, 'aet_et_save_action' ) ) {
		die( 'Failed security check' );
	} else {
		$post_data = $_POST;
		$aet_admin_object->aet_save_settings( $post_data );
	}
}
$aet_et_tracking_settings   = $aet_admin_object->aet_ad_get_setting_option( 'et' );

$manually_et_px_ver_4       = empty( $aet_et_tracking_settings->manually_et_px_ver_4 ) ? '' : $aet_et_tracking_settings->manually_et_px_ver_4;
$at_enable                  = empty( $aet_et_tracking_settings->at_enable ) ? '' : $aet_et_tracking_settings->at_enable;
$enhance_ecommerce_tracking = empty( $aet_et_tracking_settings->enhance_ecommerce_tracking ) ? '' : $aet_et_tracking_settings->enhance_ecommerce_tracking;
$search_tracking            = empty( $aet_et_tracking_settings->search_tracking ) ? '' : $aet_et_tracking_settings->search_tracking;
$privacy_policy             = empty( $aet_et_tracking_settings->privacy_policy ) ? '' : $aet_et_tracking_settings->privacy_policy;
$google_analytics_opt_out   = empty( $aet_et_tracking_settings->google_analytics_opt_out ) ? '' : $aet_et_tracking_settings->google_analytics_opt_out;
$trc_guest_users   			= empty( $aet_et_tracking_settings->trc_guest_users ) ? '' : $aet_et_tracking_settings->trc_guest_users;
$demogr_int_rema_adver      = empty( $aet_et_tracking_settings->demogr_int_rema_adver ) ? '' : $aet_et_tracking_settings->demogr_int_rema_adver;
$track_404                  = empty( $aet_et_tracking_settings->track_404 ) ? '' : $aet_et_tracking_settings->track_404;
$file_downloads             = empty( $aet_et_tracking_settings->file_downloads ) ? '' : $aet_et_tracking_settings->file_downloads;
$exl_tracking_for_roles     = empty( $aet_et_tracking_settings->exl_tracking_for_roles ) ? array() : $aet_et_tracking_settings->exl_tracking_for_roles;
$user_id_tracking           = empty( $aet_et_tracking_settings->user_id_tracking ) ? '' : $aet_et_tracking_settings->user_id_tracking;
$form_tracking              = empty( $aet_et_tracking_settings->form_tracking ) ? '' : $aet_et_tracking_settings->form_tracking;
$comment_tracking           = empty( $aet_et_tracking_settings->comment_tracking ) ? '' : $aet_et_tracking_settings->comment_tracking;
$sign_in_tracking           = empty( $aet_et_tracking_settings->sign_in_tracking ) ? '' : $aet_et_tracking_settings->sign_in_tracking;
$sign_out_tracking          = empty( $aet_et_tracking_settings->sign_out_tracking ) ? '' : $aet_et_tracking_settings->sign_out_tracking;
$product_review_tracking    = empty( $aet_et_tracking_settings->product_review_tracking ) ? '' : $aet_et_tracking_settings->product_review_tracking;
$sign_up_tracking           = empty( $aet_et_tracking_settings->sign_up_tracking ) ? '' : $aet_et_tracking_settings->sign_up_tracking;
$custom_event               = empty( $aet_et_tracking_settings->custom_event ) ? 'on' : $aet_et_tracking_settings->custom_event;
$mp_backend_tracking        = empty( $aet_et_tracking_settings->mp_backend_tracking ) ? '' : $aet_et_tracking_settings->mp_backend_tracking;
$mp_measurement_id          = empty( $aet_et_tracking_settings->mp_measurement_id ) ? '' : $aet_et_tracking_settings->mp_measurement_id;
$mp_api_secret              = empty( $aet_et_tracking_settings->mp_api_secret ) ? '' : $aet_et_tracking_settings->mp_api_secret;
$mp_debug_mode              = empty( $aet_et_tracking_settings->mp_debug_mode ) ? '' : $aet_et_tracking_settings->mp_debug_mode;
if ( empty( $mp_measurement_id ) && ! empty( $manually_et_px_ver_4 ) ) {
	$mp_measurement_id = $manually_et_px_ver_4;
}
$get_data                   = filter_input( INPUT_GET, 'data', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$get_four_data              = filter_input( INPUT_GET, 'fdata', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$get_four_data              = isset( $get_four_data ) && !empty( $get_four_data ) ? $get_four_data : '';
$get_four_data_val          = urldecode( base64_decode( $get_four_data ) );

if( isset( $get_four_data_val ) && !empty( $get_four_data_val ) ){
	$four_id = $get_four_data_val;
	$set_arry = get_option('aet_et_tracking_settings') ;
	$set_arr = json_decode( $set_arry, true );
	$set_arr = NULL === $set_arr ? array() : $set_arr;
	
	if( array_key_exists( 'manually_et_px_ver_4', $set_arr ) ){
		$set_arr['manually_et_px_ver_4'] = $four_id;
		$nset_array = wp_json_encode($set_arr);
		update_option( 'aet_et_tracking_settings', $nset_array );
	}else{
		$set_arr['manually_et_px_ver_4'] = $four_id;
		$nset_array = wp_json_encode($set_arr);
		update_option( 'aet_et_tracking_settings', $nset_array );
	}
	$aet_admin_object->aet_sync_ga4_connected_date( $four_id );
}

if ( isset( $get_data ) ) {
	$aet_admin_object->aet_update_selected_ua_id( $get_data, 'et', 'ecommerce', 'update', 'load' );
}

$setup_link        = $aet_admin_object->aet_setup_link( '' );
$selected_data_ua  = get_option( 'selected_data_ua_et' );
$allowed_tooltip_html = wp_kses_allowed_html( 'post' )['span'];
$wizard_style_attr = '';
$data_style_attr   = '';
if ( empty( $selected_data_ua ) ) {
	$wizard_style_attr = "display:block;";
	$data_style_attr   = "display:none;";
} else {
	$wizard_style_attr = "display:none;";
	$data_style_attr   = "display:block;";
}
$get_act = filter_input( INPUT_GET, 'act', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
if ( isset( $get_act ) ) {
	$aet_admin_object->aet_update_selected_ua_id( $get_data, 'et', 'ecommerce', 'delete', 'load' );
}

?>
	<div class="waet-section-left aet-settings-page">
		<div class="waet-table res-cl">
			<div class="title_div">
				<h2>
					<?php esc_html_e( 'Ecommerce Tracking Configuration', 'advance-ecommerce-tracking' ); ?>
				</h2>
			</div>

			<div class="table-outer" id="table_outer_wizard" style="<?php echo esc_attr( $wizard_style_attr ); ?>">
				<div class="wizard_section" id="wizard_id">
					<div class="sub_wizard" id="sub_wizard_id">
						<div class="first_step steping_div" id="first_step">
							<div class="aet-welcome-setup">
								<div class="aet-welcome-setup__hero">
									<div class="aet-welcome-setup__icon">
										<img src="<?php echo esc_url( AET_PLUGIN_URL . 'admin/images/WSFL-1.png' ); ?>" alt="" />
									</div>
									<h2><?php esc_html_e( 'Welcome to Ecommerce Tracking!', 'advance-ecommerce-tracking' ); ?> 🚀</h2>
									<p>
										<?php esc_html_e( 'Ecommerce Tracking makes it effortless to set up Google Analytics in WordPress the right way. Follow our documentation to get started quickly.', 'advance-ecommerce-tracking' ); ?>
									</p>
									<a href="<?php echo esc_url( 'https://docs.thedotstore.com/article/546-how-to-connect-your-google-analytics-tracking-id' ); ?>" target="_blank" rel="noopener noreferrer" class="aet-welcome-setup__doc-btn">
										<span class="dashicons dashicons-book-alt" aria-hidden="true"></span>
										<?php esc_html_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>
									</a>
								</div>

								<div class="aet-welcome-setup__cards">
									<span class="aet-welcome-setup__divider" aria-hidden="true"><?php esc_html_e( 'OR', 'advance-ecommerce-tracking' ); ?></span>

									<div class="aet-welcome-setup__card aet-welcome-setup__card--google">
										<div class="aet-welcome-setup__card-head">
											<span class="aet-welcome-setup__card-icon aet-welcome-setup__card-icon--google" aria-hidden="true">
												<span class="dashicons dashicons-admin-tools"></span>
											</span>
											<div>
												<h3 class="aet-welcome-setup__card-title">
													<?php esc_html_e( 'Connect with Google', 'advance-ecommerce-tracking' ); ?>
													<span class="aet-welcome-setup__badge"><?php esc_html_e( 'Recommended', 'advance-ecommerce-tracking' ); ?></span>
												</h3>
											</div>
										</div>
										<p class="aet-welcome-setup__card-desc">
											<?php esc_html_e( 'Connect your Google Analytics account in just a few clicks. Quick, secure and hassle-free.', 'advance-ecommerce-tracking' ); ?>
										</p>
										<ul class="aet-welcome-setup__features">
											<li><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php esc_html_e( 'Secure authentication with Google', 'advance-ecommerce-tracking' ); ?></li>
											<li><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php esc_html_e( 'Automatically fetches your GA4 properties', 'advance-ecommerce-tracking' ); ?></li>
											<li><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><?php esc_html_e( 'Recommended for most users', 'advance-ecommerce-tracking' ); ?></li>
										</ul>
										<div class="sub_wizard_button sub_wizard_content_common" id="sub_wizard_button">
											<a href="<?php echo esc_url( $setup_link ); ?>" id="start_to_setup" data-attr="second_step" class="aet-welcome-setup__connect-btn sub_wizard_button_a sub_wizard_button_first_a">
												<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path fill="#fff" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#fff" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#fff" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#fff" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
												<?php esc_html_e( 'Connect with Google', 'advance-ecommerce-tracking' ); ?>
											</a>
										</div>
										<p class="aet-welcome-setup__secure-note">
											<span class="dashicons dashicons-lock" aria-hidden="true"></span>
											<?php esc_html_e( 'Secure & Private. We never store your credentials.', 'advance-ecommerce-tracking' ); ?>
										</p>
									</div>

									<div class="aet-welcome-setup__card aet-welcome-setup__card--manual">
										<div class="aet-welcome-setup__card-head">
											<span class="aet-welcome-setup__card-icon aet-welcome-setup__card-icon--manual" aria-hidden="true">
												<span class="dashicons dashicons-editor-kitchensink"></span>
											</span>
											<div>
												<h3 class="aet-welcome-setup__card-title">
													<?php esc_html_e( 'Enter GA4 Measurement ID', 'advance-ecommerce-tracking' ); ?>
												</h3>
											</div>
										</div>
										<p class="aet-welcome-setup__card-desc">
											<?php esc_html_e( 'Already have your GA4 Measurement ID? Enter it manually to get started.', 'advance-ecommerce-tracking' ); ?>
										</p>
										<div class="aet-welcome-setup__field sub_wizard_fieldset sub_wizard_content_common" id="sub_wizard_field">
											<label for="manually_et_px">
												<?php esc_html_e( 'GA4 Measurement ID', 'advance-ecommerce-tracking' ); ?>
											</label>
											<div class="field_div">
												<input type="text" name="manually_et_px" id="manually_et_px" value="" data-attr="et" data-attr-two="ecommerce" class="manually_et_px_class" placeholder="G-XXXXXXXXXX" />
											</div>
											<p class="aet-welcome-setup__field-hint"><?php esc_html_e( 'Format: G-XXXXXXXXXX', 'advance-ecommerce-tracking' ); ?></p>
										</div>
										<div class="sub_wizard_field sub_wizard_content_common">
											<input type="button" class="button aet-welcome-setup__submit-btn sub_wizard_button_a" name="update_manually_et_px" id="update_manually_et_px" value="<?php echo esc_attr__( 'Submit & Save', 'advance-ecommerce-tracking' ); ?>" data-attr="et" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php require_once plugin_dir_path( __FILE__ ) . 'aet-et-settings-form.php'; ?>
		</div>
	</div>
</div>
</div>
</div>
</div>
<?php