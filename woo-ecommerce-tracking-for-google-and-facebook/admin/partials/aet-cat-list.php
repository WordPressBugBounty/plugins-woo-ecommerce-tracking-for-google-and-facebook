<?php

// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( __FILE__ ) . 'header/plugin-header.php';
function aet_custom_event_free_plugin_content() {
    ?>
	<div class="waet-section-left">
		<div class="waet-table res-cl">
			<h2>
				<?php 
    esc_html_e( 'Google Analytics Custom Event', 'advance-ecommerce-tracking' );
    ?>
				<a class="wp-core-ui button-primary msbb-cls aet-pro-feature" href="javascript:void(0);">
					<?php 
    esc_html_e( 'Add New Event', 'advance-ecommerce-tracking' );
    ?>
				</a>
				<span class="aet-pro-label"></span>
			</h2>
			<div class="table-outer" id="table_outer_data">
				<form method="POST" name="aet_cat_frm" action="">
					<div class="general_setting aet-upgrade-pro-to-unlock" id="custom_event_general_setting">
						<table class="form-table table-outer">
							<tbody>
							<tr valign="top">
								<th class="titledesc" scope="row">
									<label for="event_type" class="event-label"><?php 
    esc_html_e( 'Event Type', 'advance-ecommerce-tracking' );
    ?></label>
									<p class="short-description"><?php 
    esc_html_e( 'Choose what action you want to track', 'advance-ecommerce-tracking' );
    ?></p>
								</th>
								<td class="forminp">
									<select name="event_type" id="event_type" disabled>
										<option value="click" selected="selected"><?php 
    esc_html_e( 'Click on Selector', 'advance-ecommerce-tracking' );
    ?></option>
									</select>
									<span class="advance_ecommerce_tracking_tab_description"></span>
									<p class="description" style="display:none;">
										<?php 
    esc_html_e( 'Choose what fires this event. Based on the event type the rest of the settings below will change.', 'advance-ecommerce-tracking' );
    ?>
									</p>
								</td>
							</tr>
							<tr valign="top" class="aet-field-click aet-field-scroll_depth">
								<th class="titledesc" scope="row">
									<label for="selector_attr" class="event-label"><?php 
    esc_html_e( 'Selector', 'advance-ecommerce-tracking' );
    ?></label>
									<p class="short-description"><?php 
    esc_html_e( 'Enter the class, ID, or selector of the element you want to track.', 'advance-ecommerce-tracking' );
    ?></p>
								</th>
								<td class="forminp">
									<input type="text" name="selector_attr" id="selector_attr" placeholder="Enter selector (Scroll Depth: leave empty to track the whole page)" value="contact-me" disabled />
									<span class="advance_ecommerce_tracking_tab_description"></span>
									<p class="description" style="display:none;">
										<?php 
    $html = sprintf(
        '%s<a href=%s target="_blank">%s</a>',
        esc_html__( 'Select element\'s selector where you want to apply this event.
										Please check screenshot for selector\'s.', 'advance-ecommerce-tracking' ),
        esc_url( AET_PLUGIN_URL . 'admin/images/selector.png' ),
        esc_html__( ' Click Here', 'advance-ecommerce-tracking' )
    );
    echo wp_kses_post( $html );
    ?>
									</p>
								</td>
							</tr>
							<tr valign="top" class="aet-field-click aet-field-scroll_depth">
								<th class="titledesc" scope="row">
									<label for="selector_type" class="event-label"><?php 
    esc_html_e( 'Selector Type', 'advance-ecommerce-tracking' );
    ?></label>
									<p class="short-description"><?php 
    esc_html_e( 'Tell us whether the value above is a Class, ID, CSS Selector.', 'advance-ecommerce-tracking' );
    ?></p>
								</th>
								<td class="forminp">
									<select name="selector_type" id="selector_type" disabled>
										<option value="class"><?php 
    echo esc_html_e( 'Class', 'advance-ecommerce-tracking' );
    ?></option>
										<option value="id"><?php 
    echo esc_html_e( 'ID', 'advance-ecommerce-tracking' );
    ?></option>
									</select>
									<span class="advance_ecommerce_tracking_tab_description"></span>
									<p class="description" style="display:none;">
										<?php 
    $html = sprintf(
        '%s<a href=%s target="_blank">%s</a>',
        esc_html__( 'Select element\'s selector type where you want to apply this event.
										Please check screenshot for selector\'s type.', 'advance-ecommerce-tracking' ),
        esc_url( AET_PLUGIN_URL . 'admin/images/selector_type.png' ),
        esc_html__( ' Click Here', 'advance-ecommerce-tracking' )
    );
    echo wp_kses_post( $html );
    ?>
									</p>
								</td>
							</tr>
							<tr valign="top">
								<th class="titledesc" scope="row">
									<label for="event_category" class="event-label"><?php 
    esc_html_e( 'Event Category', 'advance-ecommerce-tracking' );
    ?></label>
									<p class="short-description"><?php 
    esc_html_e( 'Group similar events together to keep your reports organized.', 'advance-ecommerce-tracking' );
    ?></p>
								</th>
								<td class="forminp">
									<input type="text" name="event_category" id="event_category" placeholder="Enter Event Category" value="contact_me" disabled />
									<span class="advance_ecommerce_tracking_tab_description"></span>
									<p class="description" style="display:none;">
										<?php 
    esc_html_e( 'Enter the event name which you want to display in analytics section.
									You can view this report in Behavior > Events section. (Event Category - You have entered name display here) ', 'advance-ecommerce-tracking' );
    ?>
									</p>
								</td>
							</tr>
							<tr valign="top" class="aet-field-shared">
								<th class="titledesc" scope="row">
									<label for="event_action" class="event-label"><?php 
    esc_html_e( 'Event Action', 'advance-ecommerce-tracking' );
    ?></label>
									<p class="short-description"><?php 
    esc_html_e( 'Describe what happened when the event was triggered.', 'advance-ecommerce-tracking' );
    ?></p>
								</th>
								<td class="forminp">
									<input type="text" name="event_action" id="event_action" placeholder="Enter Event Action" value="Button Clicked" disabled />
									<span class="advance_ecommerce_tracking_tab_description"></span>
									<p class="description" style="display:none;">
										<?php 
    esc_html_e( 'Enter the event action name which you want to display in analytics section.
									You can view this report in Behavior > Events section. (Event Action - You have entered name display here) ', 'advance-ecommerce-tracking' );
    ?>
									</p>
								</td>
							</tr>
							<tr valign="top" class="aet-field-shared">
								<th class="titledesc" scope="row">
									<label for="event_label" class="event-label"><?php 
    esc_html_e( 'Event Label', 'advance-ecommerce-tracking' );
    ?></label>
									<p class="short-description"><?php 
    esc_html_e( 'This is the event name that will appear in Google Analytics 4.', 'advance-ecommerce-tracking' );
    ?></p>
								</th>
								<td class="forminp">
									<input type="text" name="event_label" id="event_label" placeholder="Enter Event Label" value="contact button clicked" disabled />
									<span class="advance_ecommerce_tracking_tab_description"></span>
									<p class="description" style="display:none;">
										<?php 
    esc_html_e( 'Enter the event label name which you want to display in analytics section.
									You can view this report in Behavior > Events section. (Event Label - You have entered name display here) ', 'advance-ecommerce-tracking' );
    ?>
									</p>
								</td>
							</tr>
							<tr valign="top" class="aet-field-shared">
								<th class="titledesc" scope="row">
									<label for="event_value" class="event-label"><?php 
    esc_html_e( 'Event Value', 'advance-ecommerce-tracking' );
    ?></label>
									<p class="short-description"><?php 
    esc_html_e( 'Add a number to this event if you want to measure or compare it in your reports.', 'advance-ecommerce-tracking' );
    ?></p>
								</th>
								<td class="forminp">
									<input type="text" name="event_value" id="event_value" placeholder="Enter Event Value" value="1" disabled />
									<span class="advance_ecommerce_tracking_tab_description"></span>
									<p class="description" style="display:none;">
										<?php 
    esc_html_e( 'Enter the event value which will count unique event. Value must be an integer.', 'advance-ecommerce-tracking' );
    ?>
									</p>
								</td>
							</tr>
							</tbody>
						</table>
						<p class="submit">
							<input type="submit" name="custom_event_setting" class="button button-primary button-large" value="<?php 
    esc_attr_e( 'Save Event', 'advance-ecommerce-tracking' );
    ?>">
						</p>
					</div>
				</form>
			</div>
		</div>
	</div>
	<?php 
}

aet_custom_event_free_plugin_content();
?>
</div>
</div>
</div>
</div>