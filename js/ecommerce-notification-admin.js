'use strict';
jQuery(document).ready(function () {
	jQuery('.vi-ui.tabular.menu .item').tab({
		history    : true,
		historyType: 'hash'
	});

	/*Setup tab*/
	var tabs,
		tabEvent = false,
		initialTab = 'general',
		navSelector = '.vi-ui.menu',
		navFilter = function (el) {
			return jQuery(el).attr('href').replace(/^#/, '');
		},
		panelSelector = '.vi-ui.tab',
		panelFilter = function () {
			jQuery(panelSelector + ' a').filter(function () {
				return jQuery(navSelector + ' a[title=' + jQuery(this).attr('title') + ']').size() != 0;
			}).each(function (event) {
				jQuery(this).attr('href', '#' + $(this).attr('title').replace(/ /g, '_'));
			});
		};
	// Initializes plugin features
	jQuery.address.strict(false).wrap(true);

	if (jQuery.address.value() == '') {
		jQuery.address.history(false).value(initialTab).history(true);
	}

	// Address handler
	jQuery.address.init(function (event) {

		// Adds the ID in a lazy manner to prevent scrolling
		jQuery(panelSelector).attr('id', initialTab);

		// Enables the plugin for all the content links
		jQuery(panelSelector + ' a').address(function () {
			return navFilter(this);
		});

		panelFilter();

		// Tabs setup
		tabs = jQuery('.vi-ui.menu')
			.tab({
				history    : true,
				historyType: 'hash'
			})

		// Enables the plugin for all the tabs
		jQuery(navSelector + ' a').click(function (event) {
			tabEvent = true;
			jQuery.address.value(navFilter(event.target));
			tabEvent = false;
			return false;
		});

	});
	/*End setup tab*/
	jQuery('.vi-ui.checkbox').checkbox();
	jQuery('select.vi-select-post_type').dropdown({
		onChange:function(value,text){
			jQuery('.vi-select-items').html('');
		}
	});
	jQuery('select.vi-ui.dropdown').dropdown();
	/*Search*/

	jQuery(".product-search").select2({
		placeholder       : "Please fill in your  product title",
		ajax              : {
			url           : "admin-ajax.php?action=wcn_search_product",
			dataType      : 'json',
			type          : "GET",
			quietMillis   : 50,
			delay         : 250,
			data          : function (params) {
				var post_type = jQuery('select[name="ecommerce_notification_params[post_type]"]').val();

				return {
					keyword  : params.term,
					post_type: post_type
				};
			},
			processResults: function (data) {
				return {
					results: data
				};
			},
			cache         : true
		},
		escapeMarkup      : function (markup) {
			return markup;
		}, // let our custom formatter work
		minimumInputLength: 1
	});

	/*Add field depend*/
	/*Products*/

	jQuery('.virtual_address').dependsOn({
		'select[name="ecommerce_notification_params[country]"]': {
			values: ['1']
		}
	});
	jQuery('.detect_address').dependsOn({
		'select[name="ecommerce_notification_params[country]"]': {
			values: ['0']
		}
	});

	/*Time*/
	jQuery('.time_loop').dependsOn({
		'input[name="ecommerce_notification_params[loop]"]': {
			checked: true
		}
	});
	/*Initial time random*/
	jQuery('.initial_delay_random').dependsOn({
		'input[name="ecommerce_notification_params[initial_delay_random]"]': {
			checked: true
		}
	});
	/*Logs*/
	jQuery('.save_logs').dependsOn({
		'input[name="ecommerce_notification_params[save_logs]"]': {
			checked: true
		}
	});
// Color picker
	jQuery('.color-picker').iris({
		change: function (event, ui) {
			jQuery(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
			var ele = jQuery(this).data('ele');
			if (ele == 'highlight') {
				jQuery('#message-purchased').find('a').css({'color': ui.color.toString()});
			} else if (ele == 'textcolor') {
				jQuery('#message-purchased').css({'color': ui.color.toString()});
			} else {
				jQuery('#message-purchased').css({backgroundColor: ui.color.toString()});
			}
		},
		hide  : true,
		border: true
	}).click(function () {
		jQuery('.iris-picker').hide();
		jQuery(this).closest('td').find('.iris-picker').show();
	});

	jQuery('body').click(function () {
		jQuery('.iris-picker').hide();
	});
	jQuery('.color-picker').click(function (event) {
		event.stopPropagation();
	});
	jQuery('input[name="ecommerce_notification_params[position]"]').on('change', function () {
		var data = jQuery(this).val();
		if (data == 1) {
			jQuery('#message-purchased').removeClass('top_left top_right').addClass('bottom_right');
		} else if (data == 2) {
			jQuery('#message-purchased').removeClass('bottom_right top_right').addClass('top_left');
		} else if (data == 3) {
			jQuery('#message-purchased').removeClass('bottom_right top_left').addClass('top_right');
		} else {
			jQuery('#message-purchased').removeClass('bottom_right top_left top_right');
		}
	});
	jQuery('select[name="ecommerce_notification_params[image_position]"]').on('change', function () {
		var data = jQuery(this).val();
		if (data == 1) {
			jQuery('#message-purchased').addClass('img-right');
		} else {
			jQuery('#message-purchased').removeClass('img-right');
		}
	});

	/*add optgroup to select box semantic*/
	jQuery('.vi-ui.dropdown.selection').has('optgroup').each(function () {
		var $menu = jQuery('<div/>').addClass('menu');
		jQuery(this).find('optgroup').each(function () {
			$menu.append("<div class=\"header\">" + this.label + "</div><div class=\"divider\"></div>");
			return jQuery(this).children().each(function () {
				return $menu.append("<div class=\"item\" data-value=\"" + this.value + "\">" + this.innerHTML + "</div>");
			});
		});
		return jQuery(this).find('.menu').html($menu.html());
	});

	jQuery('#message-purchased').attr('data-effect_display', '');
	jQuery('#message-purchased').attr('data-effect_hidden', '');
	jQuery('select[name="ecommerce_notification_params[message_display_effect]').on('change', function () {
		var data = jQuery(this).val(),
			message_purchased = jQuery('#message-purchased');

		switch (data) {
			case 'bounceIn':
				message_purchased.attr('data-effect_display', 'bounceIn');
				break;
			case 'bounceInDown':
				message_purchased.attr('data-effect_display', 'bounceInDown');
				break;
			case 'bounceInLeft':
				message_purchased.attr('data-effect_display', 'bounceInLeft');
				break;
			case 'bounceInRight':
				message_purchased.attr('data-effect_display', 'bounceInRight');
				break;
			case 'bounceInUp':
				message_purchased.attr('data-effect_display', 'bounceInUp');
				break;
			case 'fade-in':
				message_purchased.attr('data-effect_display', 'fade-in');
				break;
			case 'fadeInDown':
				message_purchased.attr('data-effect_display', 'fadeInDown');
				break;
			case 'fadeInDownBig':
				message_purchased.attr('data-effect_display', 'fadeInDownBig');
				break;
			case 'fadeInLeft':
				message_purchased.attr('data-effect_display', 'fadeInLeft');
				break;
			case 'fadeInLeftBig':
				message_purchased.attr('data-effect_display', 'fadeInLeftBig');
				break;
			case 'fadeInRight':
				message_purchased.attr('data-effect_display', 'fadeInRight');
				break;
			case 'fadeInRightBig':
				message_purchased.attr('data-effect_display', 'fadeInRightBig');
				break;
			case 'fadeInUp':
				message_purchased.attr('data-effect_display', 'fadeInUp');
				break;
			case 'fadeInUpBig':
				message_purchased.attr('data-effect_display', 'fadeInUpBig');
				break;
			case 'flipInX':
				message_purchased.attr('data-effect_display', 'flipInX');
				break;
			case 'flipInY':
				message_purchased.attr('data-effect_display', 'flipInY');
				break;
			case 'lightSpeedIn':
				message_purchased.attr('data-effect_display', 'lightSpeedIn');
				break;
			case 'rotateIn':
				message_purchased.attr('data-effect_display', 'rotateIn');
				break;
			case 'rotateInDownLeft':
				message_purchased.attr('data-effect_display', 'rotateInDownLeft');
				break;
			case 'rotateInDownRight':
				message_purchased.attr('data-effect_display', 'rotateInDownRight');
				break;
			case 'rotateInUpLeft':
				message_purchased.attr('data-effect_display', 'rotateInUpLeft');
				break;
			case 'rotateInUpRight':
				message_purchased.attr('data-effect_display', 'rotateInUpRight');
				break;
			case 'slideInUp':
				message_purchased.attr('data-effect_display', 'slideInUp');
				break;
			case 'slideInDown':
				message_purchased.attr('data-effect_display', 'slideInDown');
				break;
			case 'slideInLeft':
				message_purchased.attr('data-effect_display', 'slideInLeft');
				break;
			case 'slideInRight':
				message_purchased.attr('data-effect_display', 'slideInRight');
				break;
			case 'zoomIn':
				message_purchased.attr('data-effect_display', 'zoomIn');
				break;
			case 'zoomInDown':
				message_purchased.attr('data-effect_display', 'zoomInDown');
				break;
			case 'zoomInLeft':
				message_purchased.attr('data-effect_display', 'zoomInLeft');
				break;
			case 'zoomInRight':
				message_purchased.attr('data-effect_display', 'zoomInRight');
				break;
			case 'zoomInUp':
				message_purchased.attr('data-effect_display', 'zoomInUp');
				break;
			case 'rollIn':
				message_purchased.attr('data-effect_display', 'rollIn');
				break;
		}

	});

	jQuery('select[name="ecommerce_notification_params[message_hidden_effect]').on('change', function () {
		var data = jQuery(this).val(),
			message_purchased = jQuery('#message-purchased');

		switch (data) {
			case 'bounceOut':
				message_purchased.attr('data-effect_hidden', 'bounceOut');
				break;
			case 'bounceOutDown':
				message_purchased.attr('data-effect_hidden', 'bounceOutDown');
				break;
			case 'bounceOutLeft':
				message_purchased.attr('data-effect_hidden', 'bounceOutLeft');
				break;
			case 'bounceOutRight':
				message_purchased.attr('data-effect_hidden', 'bounceOutRight');
				break;
			case 'bounceOutUp':
				message_purchased.attr('data-effect_hidden', 'bounceOutUp');
				break;
			case 'fade-out':
				message_purchased.attr('data-effect_hidden', 'fade-out');
				break;
			case 'fadeOutDown':
				message_purchased.attr('data-effect_hidden', 'fadeOutDown');
				break;
			case 'fadeOutDownBig':
				message_purchased.attr('data-effect_hidden', 'fadeOutDownBig');
				break;
			case 'fadeOutLeft':
				message_purchased.attr('data-effect_hidden', 'fadeOutLeft');
				break;
			case 'fadeOutLeftBig':
				message_purchased.attr('data-effect_hidden', 'fadeOutLeftBig');
				break;
			case 'fadeOutRight':
				message_purchased.attr('data-effect_hidden', 'fadeOutRight');
				break;
			case 'fadeOutRightBig':
				message_purchased.attr('data-effect_hidden', 'fadeOutRightBig');
				break;
			case 'fadeOutUp':
				message_purchased.attr('data-effect_hidden', 'fadeOutUp');
				break;
			case 'fadeOutUpBig':
				message_purchased.attr('data-effect_hidden', 'fadeOutUpBig');
				break;
			case 'flipOutX':
				message_purchased.attr('data-effect_hidden', 'flipOutX');
				break;
			case 'flipOutY':
				message_purchased.attr('data-effect_hidden', 'flipOutY');
				break;
			case 'lightSpeedOut':
				message_purchased.attr('data-effect_hidden', 'lightSpeedOut');
				break;
			case 'rotateOut':
				message_purchased.attr('data-effect_hidden', 'rotateOut');
				break;
			case 'rotateOutDownLeft':
				message_purchased.attr('data-effect_hidden', 'rotateOutDownLeft');
				break;
			case 'rotateOutDownRight':
				message_purchased.attr('data-effect_hidden', 'rotateOutDownRight');
				break;
			case 'rotateOutUpLeft':
				message_purchased.attr('data-effect_hidden', 'rotateOutUpLeft');
				break;
			case 'rotateOutUpRight':
				message_purchased.attr('data-effect_hidden', 'rotateOutUpRight');
				break;
			case 'slideOutUp':
				message_purchased.attr('data-effect_hidden', 'slideOutUp');
				break;
			case 'slideOutDown':
				message_purchased.attr('data-effect_hidden', 'slideOutDown');
				break;
			case 'slideOutLeft':
				message_purchased.attr('data-effect_hidden', 'slideOutLeft');
				break;
			case 'slideOutRight':
				message_purchased.attr('data-effect_hidden', 'slideOutRight');
				break;
			case 'zoomOut':
				message_purchased.attr('data-effect_hidden', 'zoomOut');
				break;
			case 'zoomOutDown':
				message_purchased.attr('data-effect_hidden', 'zoomOutDown');
				break;
			case 'zoomOutLeft':
				message_purchased.attr('data-effect_hidden', 'zoomOutLeft');
				break;
			case 'zoomOutRight':
				message_purchased.attr('data-effect_hidden', 'zoomOutRight');
				break;
			case 'zoomOutUp':
				message_purchased.attr('data-effect_hidden', 'zoomOutUp');
				break;
			case 'rollOut':
				message_purchased.attr('data-effect_hidden', 'rollOut');
				break;
		}

	});
});