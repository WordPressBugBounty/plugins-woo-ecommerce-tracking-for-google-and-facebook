(
	function ($) {
		'use strict';
		
		/**
		 * All of the code for your admin-facing JavaScript source
		 * should reside in this file.
		 *
		 * Note: It has been assumed you will write jQuery code here, so the
		 * $ function reference has been prepared for usage within the scope
		 * of this function.
		 *
		 * This enables you to define handlers, for when the DOM is ready:
		 *
		 * $(function() {
		 *
		 * });
		 *
		 * When the window is loaded:
		 *
		 * $( window ).load(function() {
		 *
		 * });
		 *
		 * ...and/or other possibilities.
		 *
		 * Ideally, it is not considered best practise to attach more than a
		 * single DOM-ready or window-load handler for a particular page.
		 * Although scripts in the WordPress core, Plugins and Themes may be
		 * practising this, we should strive to set a better example in our own work.
		 */
		$(window).load(function () {
			function getUrlVars () {
				var vars = [], hash, get_current_url;
				get_current_url = window.location.href;
				var hashes = get_current_url.slice(get_current_url.indexOf('?') + 1).split('&');
				for (var i = 0; i < hashes.length; i++) {
					hash = hashes[i].split('=');
					vars.push(hash[0]);
					vars[hash[0]] = hash[1];
				}
				return vars;
			}
			
			let getURLVARS = getUrlVars();
			let pluginspagesarray = [
				'aet-et-settings',
				'aet-ft-settings',
				'aet-gc-settings',
				'aet-get-started',
				'aet-cat-list'
			];
			if (getURLVARS) {
				let getURLVARSPage = getURLVARS.page;
				if ($.inArray(getURLVARSPage, pluginspagesarray) !== -1) {
					$('a[href="admin.php?page=aet-et-settings"]').parent().addClass('current');
					$('a[href="admin.php?page=aet-et-settings"]').addClass('current');
				}
			}
			
			function setAllAttributes (element, attributes) {
				Object.keys(attributes).forEach(function (key) {
					element.setAttribute(key, attributes[key]);
				});
				return element;
			}
			
			function reconnect_to_wizard () {
				$('#table_outer_wizard').show();
				$('#general_setting').hide();
				let table_outer_wizard_id = document.getElementById('table_outer_wizard').getElementsByClassName('sub_wizard_button')[0];
				if ($('#cancel_button').length === 0) {
					let new_a = document.createElement('a');
					new_a = setAllAttributes(new_a, {
							'href': 'javascript:void(0);',
							'id': 'cancel_button',
							'class': 'button button-secondory button-large sub_wizard_button_a sub_wizard_button_third_a'
						}
					);
					new_a.textContent = 'Cancel';
					table_outer_wizard_id.appendChild(new_a);
				}
			}
			
			function cancel_button () {
				$('#general_setting').show();
				$('#table_outer_wizard').hide();
			}
			
			function update_manually_ID (get_val, get_attr, get_attr_two) {
				$.ajax({
					type: 'GET',
					url: aet_vars.ajaxurl,
					data: {
						'action': 'aet_update_manually_id',
						'get_val': get_val,
						'get_attr': get_attr,
						'get_attr_two': get_attr_two,
						'nonce': aet_vars.aet_chk_nonce_ajax,
					},
					success: function (response) {
						location.reload(response);
					}
				});
			}
			
			$('body').on('click', '#reconnect_to_wizard', function () {
				reconnect_to_wizard();
			});
			$('body').on('click', '#cancel_button', function () {
				cancel_button();
			});
			
			$( 'a[href="admin.php?page=aet-et-settings"]' ).parents().addClass( 'current wp-has-current-submenu' );
			$( 'a[href="admin.php?page=aet-et-settings"]' ).addClass( 'current' );
			
			$('#general_setting table tr td span.switch').click( function(){
				if( true === $(this).find('input').is(':checked') ){
					$(this).find('input').prop('checked', false);
				}else{
					$(this).find('input').prop('checked', true);
				}
			});

			/* description toggle */
			$('span.advance_ecommerce_tracking_tab_description').click(function (event) {
				event.preventDefault();
				$(this).next('p.description').toggle();
			});
			$('body').on('click', '#update_manually_ft_px, #update_manually_et_px', function () {
				let btn_attr = $(this).attr('data-attr');
				let get_attr = $('#manually_' + btn_attr + '_px').attr('data-attr');
				let get_val = $.trim($('#manually_' + btn_attr + '_px').val());
				let get_attr_two = $('#manually_' + btn_attr + '_px').attr('data-attr-two');
				console.log(get_val);
				
				if ('' === get_val) {
					console.log('blank');
					let sub_wizard_field = document.getElementById('sub_wizard_field').getElementsByClassName('field_div')[0];
					if ($('#main_error_div').length === 0) {
						let div = document.createElement('div');
						div = setAllAttributes(div, {
								'id': 'main_error_div',
								'class': 'error_div'
							}
						);
						let span = document.createElement('span');
						span = setAllAttributes(span, {
								'id': 'error_span',
								'class': 'error'
							}
						);
						span.textContent = 'Please enter ID here';
						div.appendChild(span);
						sub_wizard_field.appendChild(div);
					}
				}
				update_manually_ID(get_val, get_attr, get_attr_two);
			});
			
			/*Custom event section*/
			$('.all_ft_tr').hide();
			
			let selected = $('#event_type').find('option:selected');
			let select_val = $('#event_type').val();
			let extra_attr = selected.data('attr');
			getFieldBasedOnSelector(extra_attr, select_val);
			
			function getFieldBasedOnSelector (extra_attr, select_val) {
				$.each(extra_attr, function (key, value) {
					$('#tr_ft_' + key).show();
					if ('none' !== value) {
						//$('#tr_ft_' + key + ' input').attr('require', 'true');
					}
				});
				if ('Custom' === select_val) {
					$('#tr_ft_custom_event').show();
				} else {
					$('#tr_ft_custom_event').hide();
					$('#tr_ft_custom_event').find('input').val('');
				}
			}
			
			$('body').on('change', '#event_type', function () {
				$('.all_ft_tr').hide();
				let select_val = $(this).val();
				let selected = $(this).find('option:selected');
				let extra_attr = selected.data('attr');
				getFieldBasedOnSelector(extra_attr, select_val);
			});
			
			function adCustomParamField (count_cp) {
				let in_append_elem = document.getElementById('tr_ft_contents').getElementsByClassName('ft_contents_div')[0];
				let parent_div = document.createElement('div');
				parent_div = setAllAttributes(parent_div, {
					'id': 'total_custom_param_' + count_cp,
					'class': 'main_div_custom_param total_custom_param'
				});
				
				let input_parent_div1 = document.createElement('div');
				input_parent_div1 = setAllAttributes(input_parent_div1, {
					'id': 'ip_div_' + count_cp,
					'class': 'ip_div ip_div_' + count_cp,
				});
				
				let input_parent_div2 = document.createElement('div');
				input_parent_div2 = setAllAttributes(input_parent_div2, {
					'id': 'ip_div_' + count_cp,
					'class': 'ip_div ip_div_' + count_cp,
				});
				
				let input_parent_div3 = document.createElement('div');
				input_parent_div3 = setAllAttributes(input_parent_div3, {
					'id': 'param_delete',
					'class': 'param_delete',
					'data-id': count_cp
				});
				
				let child_input1 = document.createElement('input');
				child_input1 = setAllAttributes(child_input1, {
					'type': 'text',
					'id': 'key_' + count_cp,
					'name': 'key_param[]',
					'class': 'key_input key_' + count_cp,
					'placeholder': 'key',
					'value': '',
				});
				
				let child_input2 = document.createElement('input');
				child_input2 = setAllAttributes(child_input2, {
					'type': 'text',
					'id': 'value_' + count_cp,
					'name': 'value_param[]',
					'class': 'value_input value_' + count_cp,
					'placeholder': 'Value',
					'value': '',
				});
				
				let child_a = document.createElement('a');
				child_a = setAllAttributes(child_a, {
					'href': 'javascript:void(0);',
					'class': 'a_param_delete',
					'id': 'a_param_delete'
				});
				
				let child_a_img = document.createElement('img');
				child_a_img = setAllAttributes(child_a_img, {
					'src': aet_vars.trash_url,
				});
				
				child_a.appendChild(child_a_img);
				input_parent_div1.appendChild(child_input1);
				input_parent_div2.appendChild(child_input2);
				input_parent_div3.appendChild(child_a);
				parent_div.appendChild(input_parent_div1);
				parent_div.appendChild(input_parent_div2);
				parent_div.appendChild(input_parent_div3);
				in_append_elem.appendChild(parent_div);
			}
			
			$('body').on('click', '#custom_parameter_btn_div', function () {
				let count_cp = $('#tr_ft_contents .total_custom_param').length;
				if (count_cp === 0) {
					count_cp = 1;
				} else {
					count_cp = count_cp + 1;
				}
				adCustomParamField(count_cp);
			});
			
			$('body').on('click', '#a_param_delete', function () {
				let data_id = $(this).parent().attr('data-id');
				$('#total_custom_param_' + data_id).remove();
			});
			
			$('#custom_event_general_setting input#event_value').keypress(function (e) {
				var regex = new RegExp('^[0-9.]+$');
				var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
				if (regex.test(str)) {
					return true;
				}
				e.preventDefault();
				return false;
			});
			
			$('body').on('click', '.condition-check-all', function () {
				$('input.multiple_delete_chk:checkbox').not(this).prop('checked', this.checked);
			});
			
			$('#delete-custom-event').click(function () {
				let dynamic_string = $(this).attr('data-attr');
				if (0 === $('.multiple_delete_chk:checkbox:checked').length) {
					alert('Please select at least one ' + dynamic_string + ' event');
					return false;
				}
				if (confirm('Are You Sure You Want to Delete Selected ' + dynamic_string + ' Event?')) {
					var all_checked_val = [];
					$('.multiple_delete_chk:checked').each(function () {
						all_checked_val.push($(this).val());
					});
					$.ajax({
						type: 'GET',
						url: aet_vars.ajaxurl,
						nonce: aet_vars.aet_chk_nonce_ajax,
						data: {
							'action': 'aet_wc_multiple_delete_row__premium_only',
							'nonce': aet_vars.aet_chk_nonce_ajax,
							'all_checked_val': all_checked_val
						},
						success: function (response) {
							if ('1' === response) {
								alert('Delete Successfully');
								$('.multiple_delete_chk').prop('checked', false);
								location.reload();
							}
						}
					});
				}
			});
			
			$('body').on('click', '.dotstore-important-link .content_box .et-star-rating label', function(e){
				e.stopImmediatePropagation();
				var rurl = $('#et-review-url').val();
				window.open( rurl, '_blank' );
			});

			/** Dynamic Promotional Bar START */
			$(document).on('click', '.dpbpop-close', function () {
				var popupName 		= $(this).attr('data-popup-name');
				setCookie( 'banner_' + popupName, 'yes', 60 * 24 * 7);
				$('.' + popupName).hide();
			});

	
			$(document).on('click', '.dpb-popup .dpb-popup-meta', function () {
				var promotional_id         = $(this).parent().find('.dpbpop-close').attr('data-bar-id');
				var popupName                 = $(this).parent().find('.dpbpop-close').attr('data-popup-name');
				setCookie( 'banner_' + popupName, 'yes', 60 * 24 * 7);
				$('.' + popupName).hide();
	
				//Create a new Student object using the values from the textfields
				var apiData = {
					'bar_id' : promotional_id
				};
	
				$.ajax({
					type: 'POST',
					url: aet_vars.dpb_api_url + 'wp-content/plugins/dots-dynamic-promotional-banner/bar-response.php',
					data: JSON.stringify(apiData),// now data come in this function
					dataType: 'json',
					cors: true,
					contentType:'application/json',
					
					success: function (data) {
						console.log(data);
					},
					error: function () {
					}
				 });
			});
			/** Dynamic Promotional Bar END */
			/** Plugin Setup Wizard Script START */
			// Hide & show wizard steps based on the url params 
			var urlParams = new URLSearchParams(window.location.search);
			if (urlParams.has('require_license')) {
			$('.ds-plugin-setup-wizard-main .tab-panel').hide();
			$( '.ds-plugin-setup-wizard-main #step5' ).show();
			} else {
				$( '.ds-plugin-setup-wizard-main #step1' ).show();
			}
			
			// Plugin setup wizard steps script
			$(document).on('click', '.ds-plugin-setup-wizard-main .tab-panel .btn-primary:not(.ds-wizard-complete)', function () {
				var curruntStep = jQuery(this).closest('.tab-panel').attr('id');
				var nextStep = 'step' + ( parseInt( curruntStep.slice(4,5) ) + 1 ); // Masteringjs.io

				if( 'step5' !== curruntStep ) {
					// Youtube videos stop on next step
	                $('iframe[src*="https://www.youtube.com/embed/"]').each(function(){
	                   $(this).attr('src', $(this).attr('src'));
	                   return false;
	                });
	                
					jQuery( '#' + curruntStep ).hide();
					jQuery( '#' + nextStep ).show();   
				}
			});

			// Get allow for marketing or not
			if ( $( '.ds-plugin-setup-wizard-main .ds_count_me_in' ).is( ':checked' ) ) {
				$('#fs_marketing_optin input[name="allow-marketing"][value="true"]').prop('checked', true);
			} else {
				$('#fs_marketing_optin input[name="allow-marketing"][value="false"]').prop('checked', true);
			}

			// Get allow for marketing or not on change	    
			$(document).on( 'change', '.ds-plugin-setup-wizard-main .ds_count_me_in', function() {
				if ( this.checked ) {
					$('#fs_marketing_optin input[name="allow-marketing"][value="true"]').prop('checked', true);
				} else {
					$('#fs_marketing_optin input[name="allow-marketing"][value="false"]').prop('checked', true);
				}
			});

			// Complete setup wizard
			$(document).on( 'click', '.ds-plugin-setup-wizard-main .tab-panel .ds-wizard-complete', function() {
				if ( $( '.ds-plugin-setup-wizard-main .ds_count_me_in' ).is( ':checked' ) ) {
					$( '.fs-actions button'  ).trigger('click');
				} else {
					$('.fs-actions #skip_activation')[0].click();
				}
			});

			// Send setup wizard data on Ajax callback
			$(document).on( 'click', '.ds-plugin-setup-wizard-main .fs-actions button', function() {
				var wizardData = {
					'action': 'aet_plugin_setup_wizard_submit',
					'survey_list': $('.ds-plugin-setup-wizard-main .ds-wizard-where-hear-select').val(),
					'nonce': aet_vars.setup_wizard_ajax_nonce
				};

				$.ajax({
					url: aet_vars.ajaxurl,
					data: wizardData,
					success: function ( success ) {
						console.log(success);
					}
				});
			});
			/** Plugin Setup Wizard Script End */
			
		    /** Upgrade Dashboard Script START */
		    // Dashboard features popup script
		    $(document).on('click', '.dotstore-upgrade-dashboard .premium-key-fetures .premium-feature-popup', function (event) {
		        let $trigger = $('.feature-explanation-popup, .feature-explanation-popup *');
		        if(!$trigger.is(event.target) && $trigger.has(event.target).length === 0){
		            $('.feature-explanation-popup-main').not($(this).find('.feature-explanation-popup-main')).hide();
		            $(this).parents('li').find('.feature-explanation-popup-main').show();
		            $('body').addClass('feature-explanation-popup-visible');
		        }
		    });
		    $(document).on('click', '.dotstore-upgrade-dashboard .popup-close-btn', function () {
		        $(this).parents('.feature-explanation-popup-main').hide();
		        $('body').removeClass('feature-explanation-popup-visible');
		    });
		    /** Upgrade Dashboard Script End */

	   		// Script for Beacon configuration
      		var helpBeaconCookie = getCookie( 'aet-help-beacon-hide' );
        	if ( ! helpBeaconCookie ) {
	            Beacon('init', 'afe1c188-3c3b-4c5f-9dbd-87329301c920');
	            Beacon('config', {
	                display: {
	                    style: 'icon',
	                    iconImage: 'message',
	                    zIndex: '99999'
	                }
	            });

	            // Add plugin articles IDs to display in beacon
	            Beacon('suggest', ['62b2ae2c35a1630c11984003', '62b1a352e13ae052a9d690a9', '62b1a4a8e13ae052a9d690b0', '62b1a3d2b2955b2490b58bc1', '62b1a40db2955b2490b58bc3']);

	            // Add custom close icon form beacon
	            setTimeout(function() {
	                if ( $( '.hsds-beacon .BeaconFabButtonFrame' ).length > 0 ) {
	                    let newElement = document.createElement('span');
	                    newElement.classList.add('dashicons', 'dashicons-no-alt', 'dots-beacon-close');
	                    let container = document.getElementsByClassName('BeaconFabButtonFrame');
	                    container[0].appendChild( newElement );
	                }
	            }, 3000);

	            // Hide beacon
	            $(document).on('click', '.dots-beacon-close', function(){
	                Beacon('destroy');
	                setCookie( 'aet-help-beacon-hide' , 'true', 24 * 60 );
	            });
        	}

        	// Script for upgrade to pro popup
        	$(document).on('click', '#dotsstoremain .aet-pro-label, #dotsstoremain .aet-upgrade-pro-to-unlock', function(){
				$('body').addClass('aet-modal-visible');
			});
			$(document).on('click', '#dotsstoremain .modal-close-btn', function(){
				$('body').removeClass('aet-modal-visible');
			});
			$(document).on('click', '.upgrade-to-pro-modal-main .upgrade-now', function(e){
	            e.preventDefault();
	            $('body').removeClass('aet-modal-visible');
	            let couponCode = $('.upgrade-to-pro-discount-code').val();
	            upgradeToProFreemius( couponCode );
	        });
	        $(document).on('click', '.dots-header .dots-upgrade-btn, .dotstore-upgrade-dashboard .upgrade-now', function(e){
	            e.preventDefault();
	            upgradeToProFreemius( '' );
	        });
		});
		
		// Set cookies
		function setCookie(name, value, minutes) {
			var expires = '';
			if (minutes) {
				var date = new Date();
				date.setTime(date.getTime() + (minutes * 60 * 1000));
				expires = '; expires=' + date.toUTCString();
			}
			document.cookie = name + '=' + (value || '') + expires + '; path=/';
		}

		// Get cookies
	   	function getCookie(name) {
	        let nameEQ = name + '=';
	        let ca = document.cookie.split(';');
	        for (let i = 0; i < ca.length; i++) {
	            let c = ca[i].trim();
	            if (c.indexOf(nameEQ) === 0) {
	                return c.substring(nameEQ.length, c.length);
	            }
	        }
	        return null;
	   	}
	   	
	   	/** Script for Freemius upgrade popup */
	    function upgradeToProFreemius( couponCode ) {
	        let handler;
	        handler = FS.Checkout.configure({
	            plugin_id: '3475',
	            plan_id: '5543',
	            public_key:'pk_9edf804dccd14eabfd00ff503acaf',
	            image: 'https://www.thedotstore.com/wp-content/uploads/sites/1417/2023/09/Enhanced-Ecommerce-Google-Analytics-for-WooCommerce-Banner-New.png',
	            coupon: couponCode,
	        });
	        handler.open({
	            name: 'Advance Ecommerce Tracking',
	            subtitle: 'You’re a step closer to our Pro features',
	            licenses: jQuery('input[name="licence"]:checked').val(),
	            purchaseCompleted: function( response ) {
	                console.log (response);
	            },
	            success: function (response) {
	                console.log (response);
	            }
	        });
	    }
	}
)(jQuery);