<?php
/**
 * Settings page UI helpers for Ecommerce Tracking settings.
 *
 * @package Advance_Ecommerce_Tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a premium-only setting is locked for the current user.
 *
 * @param string $field_key Setting field key.
 * @return bool
 */
function aet_is_premium_setting_locked( $field_key ) {
	$premium_only_fields = array(
		'search_tracking',
		'trc_guest_users',
		'demogr_int_rema_adver',
		'track_404',
		'file_downloads',
		'user_id_tracking',
		'form_tracking',
		'comment_tracking',
		'sign_in_tracking',
		'sign_out_tracking',
		'product_review_tracking',
		'sign_up_tracking',
		'custom_event',
		'exl_tracking_for_roles',
	);

	if ( ! in_array( $field_key, $premium_only_fields, true ) ) {
		return false;
	}

	if ( aet_fs()->is__premium_only() && aet_fs()->can_use_premium_code() ) {
		return false;
	}

	return true;
}

/**
 * Render a WooCommerce-style hover help tip.
 * Allows links in tip content (wc_help_tip / wc_sanitize_tooltip strips <a>).
 *
 * @param string $description Tooltip HTML or text.
 */
function aet_render_wc_help_tip( $description ) {
	if ( empty( $description ) ) {
		return;
	}

	$allowed_html = array(
		'br'     => array(),
		'em'     => array(),
		'strong' => array(),
		'small'  => array(),
		'span'   => array(
			'class' => true,
			'style' => true,
		),
		'ul'     => array(),
		'li'     => array(),
		'ol'     => array(),
		'p'      => array(),
		'a'      => array(
			'href'   => true,
			'target' => true,
			'rel'    => true,
			'title'  => true,
			'class'  => true,
		),
	);

	$sanitized_tip = htmlspecialchars(
		wp_kses(
			html_entity_decode( $description ),
			$allowed_html
		),
		ENT_QUOTES,
		get_bloginfo( 'charset' )
	);

	$aria_label = wp_strip_all_tags( $description );

	printf(
		'<span class="woocommerce-help-tip aet-help-tip" tabindex="0" aria-label="%1$s" data-tip="%2$s"></span>',
		esc_attr( $aria_label ),
		$sanitized_tip // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped via htmlspecialchars above for attribute use.
	);
}

/**
 * Render a toggle setting row.
 *
 * @param array $args Setting arguments.
 */
function aet_render_setting_toggle( $args ) {
	$defaults = array(
		'id'          => '',
		'name'        => '',
		'label'       => '',
		'value'       => 'on',
		'checked'     => '',
		'description' => '',
		'field_key'   => '',
		'new_badge'   => false,
	);

	$args      = wp_parse_args( $args, $defaults );
	$is_locked = aet_is_premium_setting_locked( $args['field_key'] );
	$switch_class = $is_locked ? 'switch aet-pro-feature' : 'switch';
	$disabled     = $is_locked ? 'disabled' : '';
	$checked_attr = ( ! $is_locked && 'on' === $args['checked'] ) ? 'checked' : '';

	?>
	<div class="aet-config-item">
		<div class="aet-config-item__label">
			<label for="<?php echo esc_attr( $args['id'] ); ?>">
				<?php echo esc_html( $args['label'] ); ?>
				<?php if ( $args['new_badge'] ) : ?>
					<span class="aet-new-feture-master"><?php esc_html_e( '[new]', 'advance-ecommerce-tracking' ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $args['description'] ) ) : ?>
					<?php aet_render_wc_help_tip( $args['description'] ); ?>
				<?php endif; ?>
			</label>
			<?php if ( $is_locked ) : ?>
				<span class="aet-pro-label"></span>
			<?php endif; ?>
		</div>
		<div class="aet-config-item__control">
			<span class="<?php echo esc_attr( $switch_class ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $args['name'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" <?php echo esc_attr( $checked_attr ); ?> <?php echo esc_attr( $disabled ); ?>>
				<div class="slider round"></div>
			</span>
		</div>
	</div>
	<?php
}

