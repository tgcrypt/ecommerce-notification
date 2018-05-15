'use strict';
jQuery(document).ready(function () {
	if (jQuery('#message-purchased').length > 0) {
		var data = jQuery('#message-purchased').data();
		var notify = woo_notification;
		notify.loop = data.loop;
		notify.init_delay = data.initial_delay;
		notify.total = data.notification_per_page;
		notify.display_time = data.display_time;
		notify.next_time = data.next_time;
		if (data.product) {
			notify.id = data.product;
		}
		notify.init();
	}

	jQuery('#notify-close').on('click', function () {
		woo_notification.message_hide();
	});
});


var woo_notification = {
	loop        : 0,
	init_delay  : 5,
	total       : 30,
	display_time: 5,
	next_time   : 60,
	count       : 0,
	intel       : 0,
	id          : 0,
	init        : function () {
		setTimeout(function () {
			woo_notification.get_product();
		}, this.init_delay * 1000);
		if (this.loop) {
			this.intel = setInterval(function () {
				woo_notification.get_product();
			}, this.next_time * 1000);
		}
	},
	message_show: function () {
		var count = this.count++;
		if (this.total <= count) {
			window.clearInterval(this.intel);
			return;
		}
		var message_id = jQuery('#message-purchased'),
			msg_display_effect = jQuery('#message-purchased').data('display_effect'),
			msg_hidden_effect = jQuery('#message-purchased').data('hidden_effect');
		if (message_id.hasClass(msg_hidden_effect)) {
			jQuery(message_id).removeClass(msg_hidden_effect);
		}
		jQuery(message_id).addClass(msg_display_effect).show();
		this.audio();
		setTimeout(function () {
			woo_notification.message_hide();
		}, this.display_time * 1000);
	},

	message_hide: function () {
		var message_id = jQuery('#message-purchased'),
			msg_display_effect = jQuery('#message-purchased').data('display_effect'),
			msg_hidden_effect = jQuery('#message-purchased').data('hidden_effect');
		if (message_id.hasClass(msg_display_effect)) {
			jQuery(message_id).removeClass(msg_display_effect);
		}
		jQuery('#message-purchased').addClass(msg_hidden_effect);
	},
	get_product : function () {
		var str_data;
		if (this.id) {
			str_data = '&id=' + this.id;
		} else {
			str_data = '';
		}
		jQuery.ajax({
			type   : 'POST',
			data   : 'action=woonotification_get_product' + str_data,
			url    : ecommerce_notification_ajax_url,
			success: function (html) {
				var content = jQuery(html).children();
				jQuery("#message-purchased").html(content);
				woo_notification.message_show();
				jQuery('#notify-close').on('click', function () {
					woo_notification.message_hide();
				});
			},
			error  : function (html) {
			}
		})
	},
	close_notify: function () {
		jQuery('#notify-close').on('click', function () {
			woo_notification.message_hide();
		});
	},
	audio       : function () {
		if (jQuery('#ecommerce-notification-audio').length > 0) {
			var audio = document.getElementById("ecommerce-notification-audio");
			var initSound = function () {
				audio.play();
				setTimeout(function () {
					audio.stop();
				}, 0);
				document.removeEventListener('touchstart', initSound, false);
			};
			document.addEventListener('touchstart', initSound, false);
			audio.play();
		}
	}
}
