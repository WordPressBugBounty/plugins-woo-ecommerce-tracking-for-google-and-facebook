/**
 * Adds a "Conversion Tracking" panel to the Block Editor sidebar for
 * every block listed in AET_TRACKABLE_BLOCKS.
 *
 * No build step required — runs directly on the wp-* script handles
 * already shipped with WordPress core.
 *
 * @package Advance_Ecommerce_Tracking
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.hooks || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.compose || ! wp.i18n ) {
		return;
	}

	var addFilter = wp.hooks.addFilter;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var Fragment = wp.element.Fragment;
	var createElement = wp.element.createElement;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var ToggleControl = wp.components.ToggleControl;
	var TextControl = wp.components.TextControl;
	var __ = wp.i18n.__;

	// Keep this list in sync with $aet_conversion_trackable_blocks in
	// public/class-advance-ecommerce-tracking-public.php
	var AET_TRACKABLE_BLOCKS = [
		'core/button',
		'core/file',
		'core/social-link',
		'core/image',
		'core/read-more',
        'core/search',
	];

	function aetIsTrackable( name ) {
		return AET_TRACKABLE_BLOCKS.indexOf( name ) !== -1;
	}

	/**
	 * 1) Register the two custom attributes so the block parser knows to
	 * read/write them from the saved block markup (the HTML comment).
	 */
	function aetAddConversionTrackingAttributes( settings, name ) {
		if ( ! aetIsTrackable( name ) ) {
			return settings;
		}

		settings.attributes = Object.assign( {}, settings.attributes, {
			aetEnableConversionTracking: {
				type: 'boolean',
				default: false,
			},
			aetConversionLabel: {
				type: 'string',
				default: '',
			},
		} );

		return settings;
	}

	addFilter(
		'blocks.registerBlockType',
		'advance-ecommerce-tracking/add-conversion-tracking-attributes',
		aetAddConversionTrackingAttributes
	);

	/**
	 * 2) Render the "Conversion Tracking" panel inside the block Inspector
	 * (right-hand sidebar) for every eligible block.
	 */
	var aetWithConversionTrackingControls = createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			if ( ! aetIsTrackable( props.name ) ) {
				return createElement( BlockEdit, props );
			}

			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var isEnabled = !! attributes.aetEnableConversionTracking;

			return createElement(
				Fragment,
				null,
				createElement( BlockEdit, props ),
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{ title: __( 'Conversion Tracking', 'advance-ecommerce-tracking' ), initialOpen: false },
						createElement( ToggleControl, {
							label: __( 'Enable Conversion Tracking', 'advance-ecommerce-tracking' ),
							checked: isEnabled,
							onChange: function ( value ) {
								setAttributes( { aetEnableConversionTracking: value } );
							},
						} ),
						isEnabled &&
							createElement( TextControl, {
								label: __( 'Conversion Tracking Label', 'advance-ecommerce-tracking' ),
								placeholder: __( 'Custom Tracking Label', 'advance-ecommerce-tracking' ),
								value: attributes.aetConversionLabel,
								onChange: function ( value ) {
									setAttributes( { aetConversionLabel: value } );
								},
							} ),
						isEnabled &&
							props.name === 'core/image' &&
							attributes.linkDestination === 'none' &&
							createElement(
								'p',
								{ style: { color: '#cc1818', fontSize: '12px', marginTop: '8px' } },
								__(
									'This image isn\'t linked to anything, so no click can be tracked. Set a link under this block\'s "Link" settings first.',
									'advance-ecommerce-tracking'
								)
							),
                        isEnabled &&
							props.name === 'core/search' &&
							attributes.buttonPosition === 'no-button' &&
							createElement(
								'p',
								{ style: { color: '#cc1818', fontSize: '12px', marginTop: '8px' } },
								__(
									'This search block has no visible button (submits on Enter only), so no click can be tracked. Choose a button layout under this block\'s settings first.',
									'advance-ecommerce-tracking'
								)
							)
					)
				)
			);
		};
	}, 'aetWithConversionTrackingControls' );

	addFilter(
		'editor.BlockEdit',
		'advance-ecommerce-tracking/with-conversion-tracking-controls',
		aetWithConversionTrackingControls
	);
} )( window.wp );