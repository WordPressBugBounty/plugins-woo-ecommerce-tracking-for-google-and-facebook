<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
$submit_text = __( 'Save changes', 'advance-ecommerce-tracking' );
$track_setting = filter_input( INPUT_POST, 'track_setting', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$aet_admin_object = new Advance_Ecommerce_Tracking_Admin('', '');
if ( isset( $track_setting ) ) {
    $post_wpnonce = filter_input( INPUT_POST, 'aet_et_conditions_save', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
    $post_retrieved_nonce = ( isset( $post_wpnonce ) ? sanitize_text_field( wp_unslash( $post_wpnonce ) ) : '' );
    if ( !wp_verify_nonce( $post_retrieved_nonce, 'aet_et_save_action' ) ) {
        die( 'Failed security check' );
    } else {
        $post_data = $_POST;
        $aet_admin_object->aet_save_settings( $post_data );
    }
}
$aet_et_tracking_settings = $aet_admin_object->aet_ad_get_setting_option( 'et' );
$manually_et_px_ver_4 = ( empty( $aet_et_tracking_settings->manually_et_px_ver_4 ) ? '' : $aet_et_tracking_settings->manually_et_px_ver_4 );
$at_enable = ( empty( $aet_et_tracking_settings->at_enable ) ? '' : $aet_et_tracking_settings->at_enable );
$at_tracking_option_enable = ( empty( $aet_et_tracking_settings->at_enable ) ? '' : $aet_et_tracking_settings->at_enable );
$enhance_ecommerce_tracking = ( empty( $aet_et_tracking_settings->enhance_ecommerce_tracking ) ? '' : $aet_et_tracking_settings->enhance_ecommerce_tracking );
$search_tracking = ( empty( $aet_et_tracking_settings->search_tracking ) ? '' : $aet_et_tracking_settings->search_tracking );
$ip_anonymization = ( empty( $aet_et_tracking_settings->ip_anonymization ) ? '' : $aet_et_tracking_settings->ip_anonymization );
$privacy_policy = ( empty( $aet_et_tracking_settings->privacy_policy ) ? '' : $aet_et_tracking_settings->privacy_policy );
$google_analytics_opt_out = ( empty( $aet_et_tracking_settings->google_analytics_opt_out ) ? '' : $aet_et_tracking_settings->google_analytics_opt_out );
$trc_guest_users = ( empty( $aet_et_tracking_settings->trc_guest_users ) ? '' : $aet_et_tracking_settings->trc_guest_users );
$demogr_int_rema_adver = ( empty( $aet_et_tracking_settings->demogr_int_rema_adver ) ? '' : $aet_et_tracking_settings->demogr_int_rema_adver );
$track_404 = ( empty( $aet_et_tracking_settings->track_404 ) ? '' : $aet_et_tracking_settings->track_404 );
$file_downloads = ( empty( $aet_et_tracking_settings->file_downloads ) ? '' : $aet_et_tracking_settings->file_downloads );
$enhanced_link_attribution = ( empty( $aet_et_tracking_settings->enhanced_link_attribution ) ? '' : $aet_et_tracking_settings->enhanced_link_attribution );
$exl_tracking_for_roles = ( empty( $aet_et_tracking_settings->exl_tracking_for_roles ) ? array() : $aet_et_tracking_settings->exl_tracking_for_roles );
$user_id_tracking = ( empty( $aet_et_tracking_settings->user_id_tracking ) ? '' : $aet_et_tracking_settings->user_id_tracking );
$form_tracking = ( empty( $aet_et_tracking_settings->form_tracking ) ? '' : $aet_et_tracking_settings->form_tracking );
$comment_tracking = ( empty( $aet_et_tracking_settings->comment_tracking ) ? '' : $aet_et_tracking_settings->comment_tracking );
$custom_event = ( empty( $aet_et_tracking_settings->custom_event ) ? 'on' : $aet_et_tracking_settings->custom_event );
$get_data = filter_input( INPUT_GET, 'data', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$get_four_data = filter_input( INPUT_GET, 'fdata', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$get_four_data = ( isset( $get_four_data ) && !empty( $get_four_data ) ? $get_four_data : '' );
$get_four_data_val = urldecode( base64_decode( $get_four_data ) );
if ( isset( $get_four_data_val ) && !empty( $get_four_data_val ) ) {
    $four_id = $get_four_data_val;
    $set_arry = get_option( 'aet_et_tracking_settings' );
    $set_arr = json_decode( $set_arry, true );
    $set_arr = ( NULL === $set_arr ? array() : $set_arr );
    if ( array_key_exists( 'manually_et_px_ver_4', $set_arr ) ) {
        $set_arr['manually_et_px_ver_4'] = $four_id;
        $nset_array = wp_json_encode( $set_arr );
        update_option( 'aet_et_tracking_settings', $nset_array );
    } else {
        $set_arr['manually_et_px_ver_4'] = $four_id;
        $nset_array = wp_json_encode( $set_arr );
        update_option( 'aet_et_tracking_settings', $nset_array );
    }
}
if ( isset( $get_data ) ) {
    $aet_admin_object->aet_update_selected_ua_id(
        $get_data,
        'et',
        'ecommerce',
        'update',
        'load'
    );
}
$setup_link = $aet_admin_object->aet_setup_link( '' );
$selected_data_ua = get_option( 'selected_data_ua_et' );
$allowed_tooltip_html = wp_kses_allowed_html( 'post' )['span'];
$wizard_style_attr = '';
$data_style_attr = '';
if ( empty( $selected_data_ua ) ) {
    $wizard_style_attr = "display:block;";
    $data_style_attr = "display:none;";
} else {
    $wizard_style_attr = "display:none;";
    $data_style_attr = "display:block;";
}
$get_act = filter_input( INPUT_GET, 'act', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
if ( isset( $get_act ) ) {
    $aet_admin_object->aet_update_selected_ua_id(
        $get_data,
        'et',
        'ecommerce',
        'delete',
        'load'
    );
}
?>
	<div class="waet-section-left">
		<div class="waet-table res-cl">
			<div class="title_div">
				<h2>
					<?php 
esc_html_e( 'Ecommerce Tracking Configuration', 'advance-ecommerce-tracking' );
?>
				</h2>
			</div>

			<div class="table-outer" id="table_outer_wizard" style="<?php 
echo esc_attr( $wizard_style_attr );
?>">
				<div class="wizard_section" id="wizard_id">
					<div class="progress_bar">

					</div>
					<div class="sub_wizard" id="sub_wizard_id">
						<div class="sub_wizard_logo_container">
							<div class="sub_wizard_logo sub_wizard_bg_img">
							</div>
						</div>
						<div class="first_step steping_div" id="first_step">
							<div class="sub_wizard_content">
								<div class="sub_wizard_heading sub_wizard_content_common">
									<h2><?php 
echo esc_html__( 'Welcome to Ecommerce Tracking', 'advance-ecommerce-tracking' );
?></h2>
								</div>
								<div class="sub_wizard_description sub_wizard_content_common">
									<p>
										<?php 
echo esc_html__( 'Ecommerce Tracking makes it "effortless" to setup Google Analytics in WordPress, the Right way.
										You can watch video tutorial.', 'advance-ecommerce-tracking' );
?>
									</p>
								</div>
								<div class="sub_wizard_button sub_wizard_content_common" id="sub_wizard_button">
									<a href="<?php 
echo esc_url( $setup_link );
?>" id="start_to_setup" data-attr="second_step" class="button button-secondory button-large sub_wizard_button_a sub_wizard_button_first_a">
										<?php 
echo esc_html__( 'Start to Setup', 'advance-ecommerce-tracking' );
?>
									</a>
									<a href="<?php 
echo esc_url( 'https://docs.thedotstore.com/category/545-premium-plugin-settings' );
?>" target="_blank" class="button button-secondory button-large sub_wizard_button_a sub_wizard_button_second_a">
										<?php 
echo esc_html__( 'Documentation', 'advance-ecommerce-tracking' );
?>
									</a>
								</div>
							</div>
							<div class="sub_wizard_content">
								<div class="strong_content">
									<strong>
										<?php 
echo esc_html__( 'OR Enter manually analytics id in below field', 'advance-ecommerce-tracking' );
?>
									</strong>
								</div>
								<div class="sub_wizard_fieldset sub_wizard_content_common" id="sub_wizard_field">
									<div class="label_div">
										<label><?php 
echo esc_html__( 'Enter GA4 ID', 'advance-ecommerce-tracking' );
?></label>
									</div>
									<div class="field_div">
										<input type="text" name="manually_et_px" id="manually_et_px" value="" data-attr="et" data-attr-two="ecommerce" class="manually_et_px_class"/>
									</div>
								</div>
								<div class="sub_wizard_field sub_wizard_content_common">
									<input type="button" class="button button-primary button-large sub_wizard_button_a" name="update_manually_et_px" id="update_manually_et_px" value="<?php 
echo esc_attr_e( 'Submit', 'advance-ecommerce-tracking' );
?>" data-attr="et"/>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="table-outer" id="table_outer_data" style="<?php 
echo esc_attr( $data_style_attr );
?>">
				<form method="POST" name="aetfrm" action="">
					<?php 
wp_nonce_field( 'aet_et_save_action', 'aet_et_conditions_save' );
?>
					<input type="hidden" name="track_save" id="track_save" value="ecommerce"/>
					<input type="hidden" name="track_type" id="track_type" value="et"/>
					<div class="general_setting" id="general_setting">
						<table class="form-table table-outer">
							<tbody>
							<tr valign="top">
								<th class="titledesc" scope="row">
									<label for="onoffswitch"><?php 
esc_html_e( 'Google Analytics Account', 'advance-ecommerce-tracking' );
?></label>
								</th>
								<td class="forminp">
									<div class="profile_button_div">
										<a href="javascript:void(0);" id="reconnect_to_wizard" class="button button-secondory button-large general_setting_a general_setting_first_a">
											<?php 
echo esc_html__( 'Reconnect to Wizard', 'advance-ecommerce-tracking' );
?>
										</a>
										<a href="<?php 
echo esc_url( $setup_link );
?>&act=logout" id="discoonect" class="button button-secondory button-large general_setting_a general_setting_second_a">
											<?php 
echo esc_html__( 'Disconnect', 'advance-ecommerce-tracking' );
?>
										</a>
										<span class="advance_ecommerce_tracking_tab_description ett"></span>
										<p class="description" style="display:none;">
											<?php 
esc_html_e( 'You can change analytics ID using Reconnect button and also
										you can disconnect analytics ID if not need.', 'advance-ecommerce-tracking' );
?>
										</p>
									</div>
								</td>
							</tr>
							<?php 
$newfeaturepro = "[new]";
?>
							<tr valign="top">
								<th class="titledesc" scope="row">
									<label for="onoffswitch"><?php 
esc_html_e( 'Google Analytics 4 Account', 'advance-ecommerce-tracking' );
?></label>
									<?php 
if ( !empty( $newfeaturepro ) ) {
    ?>
									<span class="aet-new-feture-master"><?php 
    esc_html_e( $newfeaturepro, 'advance-ecommerce-tracking' );
    ?></span>
									<?php 
}
?>
								</th>
								<td class="forminp">
									<?php 
if ( !empty( $manually_et_px_ver_4 ) ) {
    ?>
										<label class="switch">
											<?php 
    echo esc_html__( 'Active Google Analytics 4 Account', 'advance-ecommerce-tracking' );
    ?>
											&#58;&nbsp;<?php 
    echo esc_html( $manually_et_px_ver_4 );
    ?>
										</label>
									<?php 
}
?>
									<div class="profile_button_div">
										<input type="text" name="manually_et_px_ver_4" id="manually_et_px_ver_4" value="<?php 
echo esc_attr( $manually_et_px_ver_4 );
?>" data-attr="et" data-attr-two="ecommerce" class="manually_et_px_class" placeholder="Enter GA4 ID" />
										<!--<a href="<?php 
echo esc_url( $setup_link );
?>&act=logout" id="discoonect" class="button button-primary button-large general_setting_a general_setting_second_a">
											<?php 
echo esc_html__( 'Disconnect', 'advance-ecommerce-tracking' );
?>
										</a>-->
										<span class="advance_ecommerce_tracking_tab_description et"></span>
										<p class="description" style="display:none;">
											<?php 
$html = sprintf(
    '%s<a href=%s target="_blank">%s</a>',
    esc_html__( 'You can disconnect analytics ID if not need. ', 'advance-ecommerce-tracking' ),
    esc_url( 'https://support.google.com/analytics/answer/10447272?hl=en&ref_topic=9303319#zippy=%2Cwoocommerce' ),
    esc_html__( 'View More', 'advance-ecommerce-tracking' )
);
echo wp_kses_post( $html );
?>
										</p>
									</div>
								</td>
							</tr>
							<tr valign="top">
								<th class="titledesc" scope="row">
									<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Enable Analytics Tracking', 'advance-ecommerce-tracking' );
?></label>
									<?php 
if ( !empty( $newfeaturepro ) ) {
    ?>
									<span class="aet-new-feture-master"><?php 
    esc_html_e( $newfeaturepro, 'advance-ecommerce-tracking' );
    ?></span>
									<?php 
}
?>
								</th>
								<td class="forminp">
									<select name="at_enable" id="at_tracking_option">
										<option value="off" <?php 
if ( $at_enable === "off" ) {
    echo esc_attr( 'selected' );
}
?> >Select</option>
										<option value="UA" disabled ><?php 
esc_html_e( 'UA - Deprecated', 'advance-ecommerce-tracking' );
?></option>
										<option value="GA4" <?php 
if ( $at_enable === "GA4" ) {
    echo esc_attr( 'selected' );
}
?> ><?php 
esc_html_e( 'GA4', 'advance-ecommerce-tracking' );
?></option>
										<option value="BOTH" <?php 
if ( $at_enable === "BOTH" ) {
    echo esc_attr( 'selected' );
}
?> ><?php 
esc_html_e( 'BOTH', 'advance-ecommerce-tracking' );
?></option>
									</select>
									<span class="advance_ecommerce_tracking_tab_description et"></span>	
									<p class="description" style="display:none;">
										<?php 
$html = sprintf( '%s', esc_html__( 'Enable Analytics trackings on your site using this option', 'advance-ecommerce-tracking' ) );
echo wp_kses_post( $html );
?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th class="titledesc" scope="row">
									<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Enable Enhanced eCommerce', 'advance-ecommerce-tracking' );
?></label>
								</th>
								<td class="forminp">
									<span class="switch"> <input type="checkbox" name="enhance_ecommerce_tracking" id="enhance_ecommerce_tracking" value="on" <?php 
checked( $enhance_ecommerce_tracking, 'on' );
?>> <div class="slider round"></div> </span>
									<span class="advance_ecommerce_tracking_tab_description"></span>
									<p class="description" style="display:none;">
										<?php 
$html = sprintf(
    '%s<a href=%s target="_blank">%s</a>',
    esc_html__( 'This option will sent website\'s data to Google Analytics,
										    like: Transaction, Revenue. See the setup guide for', 'advance-ecommerce-tracking' ),
    esc_url( AET_PLUGIN_URL . 'admin/images/eet.png' ),
    esc_html__( ' enhanced ecommerce', 'advance-ecommerce-tracking' )
);
echo wp_kses_post( $html );
?>
									</p>
								</td>
							</tr>
							<?php 
?>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Search Tracking', 'advance-ecommerce-tracking' );
?><span class="aet-pro-label"></span></label>
									</th>
									<td class="forminp">
										<span class="switch aet-pro-feature"> <input type="checkbox" name="search_tracking" id="search_tracking" value="on" disabled> <div class="slider round"></div> </span>
										<span class="advance_ecommerce_tracking_tab_description"></span>
										<p class="description" style="display:none;">
											<?php 
$html = sprintf(
    '%s<a href=%s target="_blank">%s</a>%s',
    esc_html__( 'This option will be sent website\'s search term to Google Analytics .
									    See the setup guide for', 'advance-ecommerce-tracking' ),
    esc_url( AET_PLUGIN_URL . 'admin/images/search.png' ),
    esc_html__( ' Search. ', 'advance-ecommerce-tracking' ),
    esc_html__( 'You can view this report in Behavior > Site Search.', 'advance-ecommerce-tracking' )
);
echo wp_kses_post( $html );
?>
										</p>
									</td>
								</tr>
								<?php 
$newfeature = "[new]";
?>
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="reconnect_to_wizard"><?php 
esc_html_e( 'IP Anonymization', 'advance-ecommerce-tracking' );
?></label>
											<?php 
if ( !empty( $newfeature ) ) {
    ?>
											<span class="aet-new-feture-master"><?php 
    esc_html_e( $newfeature, 'advance-ecommerce-tracking' );
    ?></span>
											<?php 
}
?>
										</th>
										<td class="forminp">
											<span class="switch"> <input type="checkbox" name="ip_anonymization" id="ip_anonymization" value="on" <?php 
checked( $ip_anonymization, 'on' );
?>> <div class="slider round"></div> </span>
											<span class="advance_ecommerce_tracking_tab_description"></span>
											<p class="description" style="display:none;">
												<?php 
$html = sprintf(
    '%s<a href=%s target="_blank">%s</a>',
    esc_html__( 'This option will be allow to request that all of their users
												IP addresses be anonymized within Google Analytics.', 'advance-ecommerce-tracking' ),
    esc_url( 'https://support.google.com/analytics/answer/2763052?hl=en' ),
    esc_html__( ' Read More', 'advance-ecommerce-tracking' )
);
echo wp_kses_post( $html );
?>
											</p>
										</td>
									</tr>									
									<tr valign="top">
										<th class="titledesc" scope="row">
											<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Google Analytics Opt Out', 'advance-ecommerce-tracking' );
?></label>
											<?php 
if ( !empty( $newfeature ) ) {
    ?>
											<span class="aet-new-feture-master"><?php 
    esc_html_e( $newfeature, 'advance-ecommerce-tracking' );
    ?></span>
											<?php 
}
?>
										</th>
										<td class="forminp">
											<span class="switch"> <input type="checkbox" name="google_analytics_opt_out" id="google_analytics_opt_out" value="on" <?php 
checked( $google_analytics_opt_out, 'on' );
?>> <div class="slider round"></div> </span>
											<span class="advance_ecommerce_tracking_tab_description"></span>
											<p class="description" style="display:none;">
												<?php 
$html = sprintf(
    '%s<br>%s<br><strong>%s</strong><br>%s',
    esc_html__( 'When you will enable this option then plugin will stop to sending data to Google Analytics.', 'advance-ecommerce-tracking' ),
    esc_html__( 'If you want to allow opt out to user then please add below link in your theme.', 'advance-ecommerce-tracking' ),
    esc_html__( '<a href="javascript:ga4Optout()">Click here to opt-out of Google Analytics 4</a>', 'advance-ecommerce-tracking' ),
    esc_html__( 'When user will click on this link after that data will not send to google analytics.', 'advance-ecommerce-tracking' )
);
echo wp_kses_post( $html );
?>
											</p>
										</td>
									</tr>
									<?php 
?>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard">
											<?php 
esc_html_e( 'Add Code to Track the Login Step of Guest Users (Optional)', 'advance-ecommerce-tracking' );
?>
											<?php 
if ( !empty( $newfeaturepro ) ) {
    ?>
											<span class="aet-new-feture-master"><?php 
    esc_html_e( $newfeaturepro, 'advance-ecommerce-tracking' );
    ?></span>
											<?php 
}
?>
											<span class="aet-pro-label"></span>
										</label>
									</th>
									<td class="forminp">
										<span class="switch aet-pro-feature"> <input type="checkbox" name="trc_guest_users" id="trc_guest_users" value="on" disabled> <div class="slider round"></div> </span>
										<span class="advance_ecommerce_tracking_tab_description"></span>
										<p class="description" style="display:none;">
											<?php 
esc_html_e( 'This feature will fire event when the guest user process for a checkout.', 'advance-ecommerce-tracking' );
?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard">
											<?php 
esc_html_e( 'Demographics and Interests Reports for Remarketing and Advertising', 'advance-ecommerce-tracking' );
?>
											<span class="aet-pro-label"></span>
										</label>
									</th>
									<td class="forminp">
										<span class="switch aet-pro-feature"> <input type="checkbox" name="demogr_int_rema_adver" id="demogr_int_rema_adver" value="on" disabled> <div class="slider round"></div> </span>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Track 404 (Not found) Errors', 'advance-ecommerce-tracking' );
?><span class="aet-pro-label"></span></label>
									</th>
									<td class="forminp">
										<span class="switch aet-pro-feature"> <input type="checkbox" name="track_404" id="track_404" value="on" disabled> <div class="slider round"></div> </span>
										<span class="advance_ecommerce_tracking_tab_description"></span>
										<p class="description" style="display:none;">
											<?php 
esc_html_e( 'This feature will be sent event to analytics whenever a user lands on your 404 Error Page.
									You can view this report in Behavior > Events section. (Category Name - 404 Error)', 'advance-ecommerce-tracking' );
?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard"><?php 
esc_html_e( 'File Downloads', 'advance-ecommerce-tracking' );
?><span class="aet-pro-label"></span></label>
									</th>
									<td class="forminp">
										<span class="switch aet-pro-feature"> <input type="checkbox" name="file_downloads" id="file_downloads" value="on" disabled> <div class="slider round"></div> </span>
										<span class="advance_ecommerce_tracking_tab_description"></span>
										<p class="description" style="display:none;">
											<?php 
esc_html_e( 'This feature will be sent event to analytics whenever a user view or
									download file from this type(zip, exe, pdf, doc, docx, xls, ppt, csv, xml).
									You can view this report in Behavior > Events section. (Category Name - File Download)', 'advance-ecommerce-tracking' );
?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Enhanced Link Attribution', 'advance-ecommerce-tracking' );
?><span class="aet-pro-label"></span></label>
									</th>
									<td class="forminp">
										<span class="switch aet-pro-feature"> <input type="checkbox" name="enhanced_link_attribution" id="enhanced_link_attribution" value="on" disabled> <div class="slider round"></div> </span>
										<span class="advance_ecommerce_tracking_tab_description"></span>
										<p class="description" style="display:none;">
											<?php 
$html = sprintf(
    '%s<br><strong>%s</strong>%s<br>%s<strong>%s</strong>%s<a href=%s target="_blank">%s</a>',
    esc_html__( 'Enhanced Link Attribution improves the accuracy of your In-Page Analytics report by automatically
									differentiating between multiple links to the same URL on a single page by using link element IDs.', 'advance-ecommerce-tracking' ),
    esc_html__( ' Note:', 'advance-ecommerce-tracking' ),
    esc_html__( ' for the most accurate link attribution,
									each of the links on your page should have a unique element ID.', 'advance-ecommerce-tracking' ),
    esc_html__( 'Please enable ', 'advance-ecommerce-tracking' ),
    esc_html__( 'Use enhanced link attribution', 'advance-ecommerce-tracking' ),
    esc_html__( ' option for this feature.', 'advance-ecommerce-tracking' ),
    esc_url( AET_PLUGIN_URL . 'admin/images/enhance_link.png' ),
    esc_html__( ' Click Here', 'advance-ecommerce-tracking' )
);
echo wp_kses_post( $html );
?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard"><?php 
esc_html_e( 'User ID Tracking', 'advance-ecommerce-tracking' );
?><span class="aet-pro-label"></span></label>
									</th>
									<td class="forminp">
										<span class="switch aet-pro-feature"> <input type="checkbox" name="user_id_tracking" id="user_id_tracking" value="on" disabled> <div class="slider round"></div> </span>
										<span class="advance_ecommerce_tracking_tab_description"></span>
										<p class="description" style="display:none;">
											<?php 
$html = sprintf(
    '%s<br><strong>%s</strong><a href=%s target="_blank">%s</a>',
    esc_html__( 'You can send a User ID when a pageview (or other data) is sent to Google Analytics.
										This allows you to tie sessions together when the User ID is the same,
										even if the sessions are on different devices.', 'advance-ecommerce-tracking' ),
    esc_html__( ' Please follow screenshot how to enable this option on google analytics section.', 'advance-ecommerce-tracking' ),
    esc_url( AET_PLUGIN_URL . 'admin/images/user_id_tracking.png' ),
    esc_html__( ' Click Here', 'advance-ecommerce-tracking' )
);
echo wp_kses_post( $html );
?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Form Tracking', 'advance-ecommerce-tracking' );
?><span class="aet-pro-label"></span></label>
									</th>
									<td class="forminp">
										<span class="switch aet-pro-feature"> <input type="checkbox" name="form_tracking" id="form_tracking" value="on" disabled> <div class="slider round"></div> </span>
										<span class="advance_ecommerce_tracking_tab_description"></span>
										<p class="description" style="display:none;">
											<?php 
$html = sprintf(
    '%s<br><strong>%s</strong>%s<br>%s',
    esc_html__( 'This feature will be send forms event to analytics when forms are submitted on site.
										You can view this report in Behavior > Events section. (Category Name - Form)', 'advance-ecommerce-tracking' ),
    esc_html__( ' Note: ', 'advance-ecommerce-tracking' ),
    esc_html__( ' We get default form name for those plugins. Contact Form 7, WPForms, Formidable Forms, Mailchimp Form, Gravity Form, Caldera Forms, Ninja Form.
										If you have any custom form then please add below field in your form.
										Using this you can easily understood which form has been submitted.', 'advance-ecommerce-tracking' ),
    esc_html__( ' <input type="hidden" name="aet_form" value="Enter your form name"/>', 'advance-ecommerce-tracking' )
);
echo wp_kses_post( $html );
?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Comment Tracking', 'advance-ecommerce-tracking' );
?><span class="aet-pro-label"></span></label>
									</th>
									<td class="forminp">
										<span class="switch aet-pro-feature"> <input type="checkbox" name="comment_tracking" id="comment_tracking" value="on" disabled> <div class="slider round"></div> </span>
										<span class="advance_ecommerce_tracking_tab_description"></span>
										<p class="description" style="display:none;">
											<?php 
esc_html_e( 'This feature will be send data to analytics when comment is posted on your website.
									You can view this report in Behavior > Events section. (Category Name - Comment)', 'advance-ecommerce-tracking' );
?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Custom Event', 'advance-ecommerce-tracking' );
?>
											<?php 
if ( !empty( $newfeaturepro ) ) {
    ?>
											<span class="aet-new-feture-master"><?php 
    esc_html_e( $newfeaturepro, 'advance-ecommerce-tracking' );
    ?></span>
											<?php 
}
?>
											<span class="aet-pro-label"></span>
										</label>
									</th>
									<td class="forminp">
										<span class="switch aet-pro-feature"> <input type="checkbox" name="custom_event" id="custom_event" value="on" disabled> <div class="slider round"></div> </span>
										<span class="advance_ecommerce_tracking_tab_description"></span>
										<p class="description" style="display:none;">
											<?php 
$html = sprintf( '%s<strong>%s</strong>', esc_html__( 'With custom events, you can track important actions as per your requirement.
										When you checked this checkbox and save this then new menu will display
										with name ', 'advance-ecommerce-tracking' ), esc_html__( 'Google Analytics Custom Event', 'advance-ecommerce-tracking' ) );
echo wp_kses_post( $html );
?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Excluding traking for roles', 'advance-ecommerce-tracking' );
?><span class="aet-pro-label"></span></label>
									</th>
									<td class="forminp">
										<?php 
$get_roles = $aet_admin_object->aet_get_editable_user_roles();
foreach ( $get_roles as $key => $get_role ) {
    ?>
											<span class="exl_tracking_user_roles">
												<span>
													<input type="checkbox" name="exl_tracking_for_roles[]" value="<?php 
    echo esc_attr( $key );
    ?>" disabled/>
													<label><?php 
    echo esc_html( $get_role );
    ?></label>
												</span>
											</span>
											<?php 
}
?>
										<span class="advance_ecommerce_tracking_tab_description"></span>
										<p class="description" style="display:none;">
											<?php 
esc_html_e( 'With this features, users that have roles from above selected roles
									will not be tracked into Google Analytics.', 'advance-ecommerce-tracking' );
?>
										</p>
									</td>
								</tr>
								<?php 
?>
								<tr valign="top">
									<th class="titledesc" scope="row">
										<label for="reconnect_to_wizard"><?php 
esc_html_e( 'Privacy Policy*', 'advance-ecommerce-tracking' );
?></label>
									</th>
									<td class="forminp eat_privacy_policy">
										<input type="checkbox" required name="exl_tracking_privacy_policy" value="on" <?php 
checked( $privacy_policy, 'on' );
?>/>
										<?php 
echo esc_html_e( 'Accept Privacy Policy of Plugin', 'advance-ecommerce-tracking' );
?>
										<p class='smalltxt'><?php 
echo esc_html_e( 'By using theDotstore Plugin, you agree to theDotstore plugin\'s ', 'advance-ecommerce-tracking' );
?>
											<a href='https://www.iubenda.com/privacy-policy/15880757' target="_blank"><?php 
echo esc_html_e( 'Privacy Policy', 'advance-ecommerce-tracking' );
?></a>
										</p>
									</td>
								</tr>
							</tbody>
						</table>
						<p class="submit">
							<input type="submit" name="track_setting" class="button button-primary button-large" value="<?php 
echo esc_attr( $submit_text );
?>">
						</p>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</div>
<?php 
?><?php