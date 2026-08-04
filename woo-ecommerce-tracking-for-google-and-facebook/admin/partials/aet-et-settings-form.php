<?php
/**
 * Ecommerce Tracking settings form (redesigned layout).
 *
 * @package Advance_Ecommerce_Tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'aet-et-settings-helpers.php';

$can_use_premium = aet_fs()->is__premium_only() && aet_fs()->can_use_premium_code();
$show_new_ip     = ! $can_use_premium;
$is_connected    = ! empty( $manually_et_px_ver_4 );
$connected_date  = get_option( 'aet_ga4_connected_date', '' );

// Backfill for stores already connected before the option was stored.
if ( $is_connected && empty( $connected_date ) ) {
	$connected_date = wp_date( get_option( 'date_format' ) );
	update_option( 'aet_ga4_connected_date', $connected_date );
}

$enhance_ecommerce_desc = '';
if ( $can_use_premium ) {
	$enhance_ecommerce_desc = sprintf(
		'%s',
		esc_html__( 'This option will sent website\'s data to Google Analytics, like: Transaction, Revenue, Product View, Add to Cart, Remove From Cart, Apply Coupon, Increase and Decrease Cart Qty or etc.', 'advance-ecommerce-tracking' )
	);
} else {
	$enhance_ecommerce_desc = sprintf(
		'%s',
		esc_html__( 'This option will sent website\'s data to Google Analytics, like: Transaction, Revenue.', 'advance-ecommerce-tracking' )
	);
}

?>
<div class="table-outer" id="table_outer_data" style="<?php echo esc_attr( $data_style_attr ); ?>">
	<form method="POST" name="aetfrm" action="">
		<?php wp_nonce_field( 'aet_et_save_action', 'aet_et_conditions_save' ); ?>
		<input type="hidden" name="track_save" id="track_save" value="ecommerce"/>
		<input type="hidden" name="track_type" id="track_type" value="et"/>
		<div class="general_setting" id="general_setting">

			<div class="aet-settings-card aet-analytics-connection">
				<div class="aet-settings-card__header">
					<h3><?php esc_html_e( 'Analytics Connection', 'advance-ecommerce-tracking' ); ?></h3>
					<div class="aet-connection-status <?php echo $is_connected ? 'is-connected' : ''; ?>">
						<span class="aet-connection-status__dot"></span>
						<span class="aet-connection-status__text">
							<?php echo $is_connected ? esc_html__( 'Connected', 'advance-ecommerce-tracking' ) : esc_html__( 'Not Connected', 'advance-ecommerce-tracking' ); ?>
						</span>
					</div>
				</div>
				<div class="aet-settings-card__body">
					<div class="aet-connection-box">
						<div class="aet-connection-box__info">
							<div class="aet-connection-box__icon" aria-hidden="true">
							<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"> <!-- Dot --> <circle cx="4" cy="19" r="2.2" fill="#E37400"/> <!-- Short Bar --> <rect x="8" y="10" width="4" height="11" rx="2" fill="#E37400"/> <!-- Tall Bar --> <rect x="15" y="3" width="4" height="18" rx="2" fill="#F9AB00"/> </svg>
							</div>
							<div class="aet-connection-box__details">
								<div class="aet-connection-box__title">
									<strong><?php esc_html_e( 'Active Google Analytics 4 Account', 'advance-ecommerce-tracking' ); ?></strong>
									<span class="aet-connection-box__badge"><?php esc_html_e( 'GA4', 'advance-ecommerce-tracking' ); ?></span>
								</div>
								<p class="aet-connection-box__id">
									<?php echo $is_connected ? esc_html( $manually_et_px_ver_4 ) : esc_html__( 'No GA4 Measurement ID configured', 'advance-ecommerce-tracking' ); ?>
								</p>
								<?php if ( $is_connected && ! empty( $connected_date ) ) : ?>
									<p class="aet-connection-box__date">
										<?php
										printf(
											/* translators: %s: connection date */
											esc_html__( 'Connected on %s', 'advance-ecommerce-tracking' ),
											esc_html( $connected_date )
										);
										?>
									</p>
								<?php endif; ?>
							</div>
						</div>
						<div class="aet-connection-box__actions">
							<a href="javascript:void(0);" id="reconnect_to_wizard" class="button button-secondory button-large general_setting_a general_setting_first_a">
								<?php esc_html_e( 'Reconnect to Wizard', 'advance-ecommerce-tracking' ); ?>
							</a>
							<a href="<?php echo esc_url( $setup_link ); ?>&act=logout" id="discoonect" class="button button-secondory button-large general_setting_a general_setting_second_a">
								<?php esc_html_e( 'Disconnect', 'advance-ecommerce-tracking' ); ?>
							</a>
						</div>
					</div>
					<div class="aet-connection-manual-input">
						<label for="manually_et_px_ver_4">
							<?php esc_html_e( 'GA4 Measurement ID', 'advance-ecommerce-tracking' ); ?>
							<?php
							aet_render_wc_help_tip(
								sprintf(
									'%s<a href="%s" target="_blank">%s</a>',
									esc_html__( 'You can disconnect analytics ID if not need. ', 'advance-ecommerce-tracking' ),
									esc_url( 'https://support.google.com/analytics/answer/10447272?hl=en&ref_topic=9303319#zippy=%2Cwoocommerce' ),
									esc_html__( 'View More', 'advance-ecommerce-tracking' )
								)
							);
							?>
						</label>
						<input type="text" name="manually_et_px_ver_4" id="manually_et_px_ver_4" value="<?php echo esc_attr( $manually_et_px_ver_4 ); ?>" data-attr="et" data-attr-two="ecommerce" class="manually_et_px_class" placeholder="<?php esc_attr_e( 'Enter GA4 ID', 'advance-ecommerce-tracking' ); ?>" />
					</div>
				</div>
			</div>

			<div class="aet-settings-card aet-tracking-config">
				<div class="aet-settings-card__header">
					<h3><?php esc_html_e( 'Tracking Configuration', 'advance-ecommerce-tracking' ); ?></h3>
					<div class="aet-expand-all-wrap">
						<button type="button" class="aet-expand-all-btn" id="aet_expand_all" aria-expanded="true">
							<span class="aet-expand-all-text"><?php esc_html_e( 'Collapse All', 'advance-ecommerce-tracking' ); ?></span>
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</button>
					</div>
				</div>
				<div class="aet-settings-card__body">

					<div class="aet-config-section is-expanded" data-section="core">
						<button type="button" class="aet-config-section__toggle" aria-expanded="true">
							<span class="aet-config-section__toggle-left">
								<span class="aet-config-section__icon"><span class="dashicons dashicons-chart-bar"></span></span>
								<span class="aet-config-section__title-wrap">
									<h4><?php esc_html_e( 'Core Tracking', 'advance-ecommerce-tracking' ); ?></h4>
									<p><?php esc_html_e( 'Essential tracking for your store and visitors', 'advance-ecommerce-tracking' ); ?></p>
								</span>
							</span>
							<span class="aet-config-section__actions">
								<a href="<?php echo esc_url( 'https://docs.thedotstore.com/article/1516-how-to-enable-core-ecommerce-tracking-in-woocommerce' ); ?>" class="aet-config-section__docs" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>" title="<?php esc_attr_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>"><span class="dashicons dashicons-share-alt2" aria-hidden="true"></span></a>
								<span class="aet-config-section__chevron dashicons dashicons-arrow-down-alt2"></span>
							</span>
						</button>
						<div class="aet-config-section__body">
							<div class="aet-config-grid">
								<?php
								aet_render_setting_toggle(
									array(
										'id'          => 'at_tracking_option',
										'name'        => 'at_enable',
										'label'       => __( 'Enable GA4 Analytics Tracking', 'advance-ecommerce-tracking' ),
										'value'       => 'GA4',
										'checked'     => in_array( $at_enable, array( 'GA4', 'UA', 'BOTH', 'on' ), true ) ? 'on' : 'off',
										'description' => __( 'Enable Analytics trackings on your site using this option', 'advance-ecommerce-tracking' ),
										'field_key'   => 'at_enable',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'enhance_ecommerce_tracking',
										'name'        => 'enhance_ecommerce_tracking',
										'label'       => __( 'Enable Enhanced eCommerce', 'advance-ecommerce-tracking' ),
										'checked'     => $enhance_ecommerce_tracking,
										'description' => $enhance_ecommerce_desc,
										'field_key'   => 'enhance_ecommerce_tracking',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'search_tracking',
										'name'        => 'search_tracking',
										'label'       => __( 'Search Tracking', 'advance-ecommerce-tracking' ),
										'checked'     => $search_tracking,
										'description' => sprintf(
											'%s%s',
											esc_html__( 'This option will be sent website\'s search term to Google Analytics.', 'advance-ecommerce-tracking' ),
											esc_html__( 'View in GA4: Reports > Engagement > Events (event name: view_search_results).', 'advance-ecommerce-tracking' )
										),
										'field_key'   => 'search_tracking',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'user_id_tracking',
										'name'        => 'user_id_tracking',
										'label'       => __( 'User ID Tracking', 'advance-ecommerce-tracking' ),
										'checked'     => $user_id_tracking,
										'description' => sprintf(
											'%s',
											esc_html__( 'Sends the logged-in user ID to GA4 for cross-device user tracking. In GA4, set Reporting identity to Blended or Observed (Admin → Data display → Reporting identity). View data in Explore → User explorer.', 'advance-ecommerce-tracking' )
										),
										'field_key'   => 'user_id_tracking',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'google_analytics_opt_out',
										'name'        => 'google_analytics_opt_out',
										'label'       => __( 'Google Analytics Opt Out', 'advance-ecommerce-tracking' ),
										'checked'     => $google_analytics_opt_out,
										'description' => sprintf(
											'%s<br>%s',
											esc_html__( 'When you will enable this option then plugin will stop to sending data to Google Analytics. ', 'advance-ecommerce-tracking' ),
											esc_html__( '<a href="https://docs.thedotstore.com/article/551-how-to-enable-google-analytics-opt-out-for-site-users" target="_blank">Click here for step by step guide</a>', 'advance-ecommerce-tracking' ),
										),
										'field_key'   => 'google_analytics_opt_out',
										'new_badge'   => $show_new_ip,
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'trc_guest_users',
										'name'        => 'trc_guest_users',
										'label'       => __( 'Add Code to Track Login Step of Guest Users', 'advance-ecommerce-tracking' ),
										'checked'     => $trc_guest_users,
										'description' => esc_html__( 'This feature will fire event when the guest user process for a checkout.', 'advance-ecommerce-tracking' ),
										'field_key'   => 'trc_guest_users'
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'demogr_int_rema_adver',
										'name'        => 'demogr_int_rema_adver',
										'label'       => __( 'Demographics & Interests Reports', 'advance-ecommerce-tracking' ),
										'checked'     => $demogr_int_rema_adver,
										'field_key'   => 'demogr_int_rema_adver',
									)
								);
								?>
							</div>
						</div>
					</div>

					<div class="aet-config-section is-expanded" data-section="additional">
						<button type="button" class="aet-config-section__toggle" aria-expanded="true">
							<span class="aet-config-section__toggle-left">
								<span class="aet-config-section__icon"><span class="dashicons dashicons-admin-generic"></span></span>
								<span class="aet-config-section__title-wrap">
									<h4><?php esc_html_e( 'Additional Tracking', 'advance-ecommerce-tracking' ); ?></h4>
									<p><?php esc_html_e( 'Advanced tracking & error monitoring', 'advance-ecommerce-tracking' ); ?></p>
								</span>
							</span>
							<span class="aet-config-section__actions">
								<a href="<?php echo esc_url( 'https://docs.thedotstore.com/article/1517-how-to-enable-additional-tracking-events-in-woocommerce' ); ?>" class="aet-config-section__docs" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>" title="<?php esc_attr_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>"><span class="dashicons dashicons-share-alt2" aria-hidden="true"></span></a>
								<span class="aet-config-section__chevron dashicons dashicons-arrow-down-alt2"></span>
							</span>
						</button>
						<div class="aet-config-section__body">
							<div class="aet-config-grid">
								<?php
								aet_render_setting_toggle(
									array(
										'id'          => 'track_404',
										'name'        => 'track_404',
										'label'       => __( 'Track 404 (Not found) Errors', 'advance-ecommerce-tracking' ),
										'checked'     => $track_404,
										'description' => esc_html__( 'This feature will be sent event to analytics whenever a user lands on your 404 Error Page. View in GA4: Reports > Engagement > Events (event name: 404 Error).', 'advance-ecommerce-tracking' ),
										'field_key'   => 'track_404',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'file_downloads',
										'name'        => 'file_downloads',
										'label'       => __( 'File Downloads', 'advance-ecommerce-tracking' ),
										'checked'     => $file_downloads,
										'description' => esc_html__( 'This feature will be sent event to analytics whenever a user view or download file from this type(zip, exe, pdf, doc, docx, xls, ppt, csv, xml). View in GA4: Reports > Engagement > Events (event name: file_download).', 'advance-ecommerce-tracking' ),
										'field_key'   => 'file_downloads',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'form_tracking',
										'name'        => 'form_tracking',
										'label'       => __( 'Form Tracking', 'advance-ecommerce-tracking' ),
										'checked'     => $form_tracking,
										'description' => sprintf(
											'%s<br><strong>%s</strong>%s<br>%s',
											esc_html__( 'This feature will send form events to analytics when forms are submitted on your site. View in GA4: Reports > Engagement > Events (event name: Form).', 'advance-ecommerce-tracking' ),
											esc_html__( ' Note: ', 'advance-ecommerce-tracking' ),
											esc_html__( ' We get default form name for those plugins. Contact Form 7, WPForms, Formidable Forms, Mailchimp Form, Gravity Form, Caldera Forms, Ninja Form. If you have any custom form then please add below field in your form. Using this you can easily understood which form has been submitted.', 'advance-ecommerce-tracking' ),
											esc_html__( ' <input type="hidden" name="aet_form" value="Enter your form name"/>', 'advance-ecommerce-tracking' )
										),
										'field_key'   => 'form_tracking',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'comment_tracking',
										'name'        => 'comment_tracking',
										'label'       => __( 'Comment Tracking', 'advance-ecommerce-tracking' ),
										'checked'     => $comment_tracking,
										'description' => esc_html__( 'This feature will send data to analytics when a comment is posted on your website. View in GA4: Reports > Engagement > Events (event name: Comment).', 'advance-ecommerce-tracking' ),
										'field_key'   => 'comment_tracking',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'product_review_tracking',
										'name'        => 'product_review_tracking',
										'label'       => __( 'Leaving Product Review Tracking', 'advance-ecommerce-tracking' ),
										'checked'     => $product_review_tracking,
										'description' => esc_html__( 'Sends a leave_product_review event when a customer submits a product review. View in GA4: Reports > Engagement > Events (event name: leave_product_review).', 'advance-ecommerce-tracking' ),
										'field_key'   => 'product_review_tracking',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'custom_event',
										'name'        => 'custom_event',
										'label'       => __( 'Custom Event', 'advance-ecommerce-tracking' ),
										'checked'     => $custom_event,
										'description' => sprintf(
											'%s<strong>%s</strong>',
											esc_html__( 'With custom events, you can track important actions as per your requirement. When you checked this checkbox and save this then new menu will display with name ', 'advance-ecommerce-tracking' ),
											esc_html__( 'Google Analytics Custom Event', 'advance-ecommerce-tracking' )
										),
										'field_key'   => 'custom_event',
										'new_badge'   => true,
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'sign_in_tracking',
										'name'        => 'sign_in_tracking',
										'label'       => __( 'Sign In Tracking', 'advance-ecommerce-tracking' ),
										'checked'     => $sign_in_tracking,
										'description' => esc_html__( 'Sends a sign_in event to analytics when a user logs in. View in GA4: Reports > Engagement > Events (event name: sign_in).', 'advance-ecommerce-tracking' ),
										'field_key'   => 'sign_in_tracking',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'sign_out_tracking',
										'name'        => 'sign_out_tracking',
										'label'       => __( 'Sign Out Tracking', 'advance-ecommerce-tracking' ),
										'checked'     => $sign_out_tracking,
										'description' => esc_html__( 'Sends a sign_out event to analytics when a user logs out. View in GA4: Reports > Engagement > Events (event name: sign_out).', 'advance-ecommerce-tracking' ),
										'field_key'   => 'sign_out_tracking',
									)
								);

								aet_render_setting_toggle(
									array(
										'id'          => 'sign_up_tracking',
										'name'        => 'sign_up_tracking',
										'label'       => __( 'Sign Up Tracking', 'advance-ecommerce-tracking' ),
										'checked'     => $sign_up_tracking,
										'description' => esc_html__( 'Enable to send a sign_up event when a visitor registers. Disable to stop tracking this event. View in GA4: Reports > Engagement > Events (event name: sign_up).', 'advance-ecommerce-tracking' ),
										'field_key'   => 'sign_up_tracking',
									)
								);
								?>
							</div>
						</div>
					</div>

					<div class="aet-config-section aet-block-conversion-section is-expanded" data-section="block-conversion">
						<button type="button" class="aet-config-section__toggle" aria-expanded="true">
							<span class="aet-config-section__toggle-left">
								<span class="aet-config-section__icon"><span class="dashicons dashicons-block-default"></span></span>
								<span class="aet-config-section__title-wrap">
									<h4 class="aet-config-section__title">
										<label>
											<?php esc_html_e( 'Block Conversion Tracking', 'advance-ecommerce-tracking' ); ?>
											<?php
											aet_render_wc_help_tip(
												esc_html__( 'Track clicks on supported Gutenberg blocks by enabling Conversion Tracking in each block\'s settings sidebar. This is configured in the editor, not with a global toggle here.', 'advance-ecommerce-tracking' )
											);
											?>
											<span class="aet-new-feture-master"><?php esc_html_e( '[New] ', 'advance-ecommerce-tracking' ); ?></span>
										</label>
										<?php if ( ! $can_use_premium ) : ?>
											<span class="aet-pro-label"></span>
										<?php endif; ?>
									</h4>
									<p><?php esc_html_e( 'Track clicks on Gutenberg blocks from the block editor sidebar.', 'advance-ecommerce-tracking' ); ?></p>
								</span>
							</span>
							<span class="aet-config-section__actions">
								<a href="<?php echo esc_url( 'https://docs.thedotstore.com/article/1518-how-to-track-gutenberg-block-clicks-in-woocommerce-with-ga4' ); ?>" class="aet-config-section__docs" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>" title="<?php esc_attr_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>"><span class="dashicons dashicons-share-alt2" aria-hidden="true"></span></a>
								<span class="aet-config-section__chevron dashicons dashicons-arrow-down-alt2"></span>
							</span>
						</button>
						<div class="aet-config-section__body">
							<p class="aet-block-conversion__desc">
								<?php esc_html_e( 'Use Conversion Tracking on supported Gutenberg blocks to send click events to Google Analytics 4. Enable it per block in the editor — there is no global on/off setting on this page.', 'advance-ecommerce-tracking' ); ?>
							</p>
							<ol class="aet-block-conversion__steps">
								<li><?php esc_html_e( 'Open a page or post in the block editor.', 'advance-ecommerce-tracking' ); ?></li>
								<li><?php esc_html_e( 'Select a supported block (for example, Button or Image).', 'advance-ecommerce-tracking' ); ?></li>
								<li><?php esc_html_e( 'In the block settings sidebar, open Conversion Tracking and turn it on. Optionally add a custom label.', 'advance-ecommerce-tracking' ); ?></li>
								<li><?php esc_html_e( 'Save or update the page. Clicks are sent to GA4 under Reports → Engagement → Events.', 'advance-ecommerce-tracking' ); ?></li>
							</ol>
							<div class="aet-block-conversion__blocks">
								<strong><?php esc_html_e( 'Supported blocks:', 'advance-ecommerce-tracking' ); ?></strong>
								<ul class="aet-block-conversion__block-list">
									<li><?php esc_html_e( 'Button', 'advance-ecommerce-tracking' ); ?></li>
									<li><?php esc_html_e( 'File', 'advance-ecommerce-tracking' ); ?></li>
									<li><?php esc_html_e( 'Social Link', 'advance-ecommerce-tracking' ); ?></li>
									<li><?php esc_html_e( 'Image', 'advance-ecommerce-tracking' ); ?></li>
									<li><?php esc_html_e( 'Read More', 'advance-ecommerce-tracking' ); ?></li>
									<li><?php esc_html_e( 'Search', 'advance-ecommerce-tracking' ); ?></li>
								</ul>
							</div>
							<p class="aet-block-conversion__note">
								<?php esc_html_e( 'Note: For Image blocks, conversion tracking applies only when the image is linked.', 'advance-ecommerce-tracking' ); ?>
							</p>
						</div>
					</div>

					<div class="aet-config-section aet-roles-section is-expanded" data-section="roles">
						<button type="button" class="aet-config-section__toggle" aria-expanded="true">
							<span class="aet-config-section__toggle-left">
								<span class="aet-config-section__icon"><span class="dashicons dashicons-groups"></span></span>
								<span class="aet-config-section__title-wrap">
									<h4 class="aet-config-section__title">
										<label>
											<?php esc_html_e( 'Exclude tracking for roles', 'advance-ecommerce-tracking' ); ?>
											<?php
											aet_render_wc_help_tip(
												esc_html__( 'With this features, users that have roles from above selected roles will not be tracked into Google Analytics.', 'advance-ecommerce-tracking' )
											);
											?>
										</label>
										<?php if ( aet_is_premium_setting_locked( 'exl_tracking_for_roles' ) ) : ?>
											<span class="aet-pro-label"></span>
										<?php endif; ?>
									</h4>
									<p><?php esc_html_e( 'Select user roles to exclude from analytics tracking', 'advance-ecommerce-tracking' ); ?></p>
								</span>
							</span>
							<span class="aet-config-section__actions">
								<a href="<?php echo esc_url( 'https://docs.thedotstore.com/article/559-how-to-exclude-tracking-for-several-user-roles-in-your-woocommerce-store' ); ?>" class="aet-config-section__docs" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>" title="<?php esc_attr_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>"><span class="dashicons dashicons-share-alt2" aria-hidden="true"></span></a>
								<span class="aet-config-section__chevron dashicons dashicons-arrow-down-alt2"></span>
							</span>
						</button>
						<div class="aet-config-section__body">
							<div class="aet-roles-grid">
								<?php
								$get_roles     = $aet_admin_object->aet_get_editable_user_roles();
								$roles_locked  = aet_is_premium_setting_locked( 'exl_tracking_for_roles' );
								foreach ( $get_roles as $key => $get_role ) {
									?>
									<span class="exl_tracking_user_roles">
										<span>
											<input type="checkbox" name="exl_tracking_for_roles[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $exl_tracking_for_roles, true ) ); ?> <?php disabled( $roles_locked ); ?>/>
											<label><?php echo esc_html( $get_role ); ?></label>
										</span>
									</span>
									<?php
								}
								?>
							</div>
						</div>
					</div>

					<div class="aet-config-section aet-mp-section is-expanded" data-section="measurement-protocol">
						<button type="button" class="aet-config-section__toggle" aria-expanded="true">
							<span class="aet-config-section__toggle-left">
								<span class="aet-config-section__icon"><span class="dashicons dashicons-cloud-upload"></span></span>
								<span class="aet-config-section__title-wrap">
									<h4 class="aet-config-section__title">
										<label>
											<?php esc_html_e( 'Measurement Protocol Backend Tracking', 'advance-ecommerce-tracking' ); ?>
											<?php
											aet_render_wc_help_tip(
												esc_html__( 'When enabled, purchase-related events are sent securely from your server directly to Google Analytics 4 using the Measurement Protocol.', 'advance-ecommerce-tracking' )
											);
											?>
											<span class="aet-new-feture-master"><?php esc_html_e( '[New] ', 'advance-ecommerce-tracking' ); ?></span>
										</label>
									</h4>
									<p><?php esc_html_e( 'Send purchase-related events from your server to Google Analytics 4.', 'advance-ecommerce-tracking' ); ?></p>
								</span>
							</span>
							<span class="aet-config-section__actions">
								<a href="<?php echo esc_url( 'https://docs.thedotstore.com/article/1514-how-to-set-up-ga4-measurement-protocol-backend-tracking-in-woocommerce' ); ?>" class="aet-config-section__docs" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>" title="<?php esc_attr_e( 'Documentation', 'advance-ecommerce-tracking' ); ?>"><span class="dashicons dashicons-share-alt2" aria-hidden="true"></span></a>
								<span class="aet-config-section__chevron dashicons dashicons-arrow-down-alt2"></span>
							</span>
						</button>
						<div class="aet-config-section__body">
							<p class="aet-mp-backend-tracking__desc">
								<?php esc_html_e( 'When enabled, purchase-related events are sent securely from your server directly to Google Analytics 4 using the Measurement Protocol.', 'advance-ecommerce-tracking' ); ?>
							</p>
							<div class="aet-config-grid">
								<?php
								aet_render_setting_toggle(
									array(
										'id'          => 'mp_backend_tracking',
										'name'        => 'mp_backend_tracking',
										'label'       => __( 'Enable Backend Tracking', 'advance-ecommerce-tracking' ),
										'checked'     => $mp_backend_tracking,
										'description' => __( 'Send purchase and refund events from your server. Browser purchase events are disabled to prevent duplicates.', 'advance-ecommerce-tracking' ),
										'field_key'   => 'mp_backend_tracking',
									)
								);
								?>
								<div class="aet-config-item aet-mp-field">
									<div class="aet-config-item__label">
										<label for="mp_measurement_id"><?php esc_html_e( 'Measurement ID', 'advance-ecommerce-tracking' ); ?></label>
									</div>
									<div class="aet-config-item__control">
										<input type="text" name="mp_measurement_id" id="mp_measurement_id" value="<?php echo esc_attr( $mp_measurement_id ); ?>" <?php if( isset($mp_measurement_id) && !empty($mp_measurement_id) ) : ?> readonly <?php endif; ?> placeholder="G-XXXXXXXXXX" />
									</div>
								</div>
								<div class="aet-config-item aet-mp-field">
									<div class="aet-config-item__label">
										<label for="mp_api_secret">
											<?php esc_html_e( 'API Secret', 'advance-ecommerce-tracking' ); ?>
											<?php
											aet_render_wc_help_tip(
												esc_html__( 'Find this in Google Analytics 4: Admin → Data Streams → select your web stream → Measurement Protocol API secrets → Create (or copy an existing secret). Paste the full secret here.', 'advance-ecommerce-tracking' )
											);
											?>
										</label>
									</div>
									<div class="aet-config-item__control">
										<input type="password" name="mp_api_secret" id="mp_api_secret" value="<?php echo esc_attr( $mp_api_secret ); ?>" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Enter API Secret', 'advance-ecommerce-tracking' ); ?>" />
									</div>
								</div>
								<?php
								aet_render_setting_toggle(
									array(
										'id'          => 'mp_debug_mode',
										'name'        => 'mp_debug_mode',
										'label'       => __( 'Enable Debug Mode', 'advance-ecommerce-tracking' ),
										'checked'     => $mp_debug_mode,
										'description' => __( 'Logs Measurement Protocol requests and responses to WooCommerce logs (source: aet-measurement-protocol).', 'advance-ecommerce-tracking' ),
										'field_key'   => 'mp_debug_mode',
									)
								);
								?>
							</div>
						</div>
					</div>

					<div class="aet-config-section aet-privacy-section is-expanded" data-section="privacy">
						<button type="button" class="aet-config-section__toggle" aria-expanded="true">
							<span class="aet-config-section__toggle-left">
								<span class="aet-config-section__icon"><span class="dashicons dashicons-privacy"></span></span>
								<span class="aet-config-section__title-wrap">
									<h4 class="aet-config-section__title">
										<label>
											<?php esc_html_e( 'Privacy Policy', 'advance-ecommerce-tracking' ); ?>
											<?php
											aet_render_wc_help_tip(
												sprintf(
													'%s <a href="%s" target="_blank">%s</a>',
													esc_html__( 'By using theDotstore Plugin, you agree to theDotstore plugin\'s Privacy Policy.', 'advance-ecommerce-tracking' ),
													esc_url( 'https://www.iubenda.com/privacy-policy/15880757' ),
													esc_html__( 'Privacy Policy', 'advance-ecommerce-tracking' )
												)
											);
											?>
										</label>
									</h4>
									<p><?php esc_html_e( 'TheDotstore plugin\'s Privacy Policy.', 'advance-ecommerce-tracking' ); ?></p>
								</span>
							</span>
							<span class="aet-config-section__chevron dashicons dashicons-arrow-down-alt2"></span>
						</button>
						<div class="aet-config-section__body">
							<div class="aet-privacy-content eat_privacy_policy">
								<input type="checkbox" required name="exl_tracking_privacy_policy" value="on" <?php checked( $privacy_policy, 'on' ); ?>/>
								<div class="aet-privacy-text">
									<?php esc_html_e( 'I accept Privacy Policy of Plugin', 'advance-ecommerce-tracking' ); ?>
									<p class="smalltxt">
										<?php esc_html_e( 'By using theDotstore Plugin, you agree to theDotstore plugin\'s ', 'advance-ecommerce-tracking' ); ?>
										<a href="https://www.iubenda.com/privacy-policy/15880757" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy Policy', 'advance-ecommerce-tracking' ); ?></a>
									</p>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>

			<div class="aet-settings-actions">
				<input type="submit" name="track_setting" class="button button-primary button-large" value="<?php echo esc_attr( $submit_text ); ?>">
			</div>

			

		</div>
	</form>
</div>
