<?php
/*
Plugin Name: WordPress eCommerce Notification
Plugin URI: http://villatheme.com
Description: Increase conversion rate by highlighting other customers that have bought products.
Version: 1.0.5
Author: Andy Ha (villatheme.com)
Author URI: http://villatheme.com
Copyright 2017-2018 VillaTheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'ECOMMERCE_NOTIFICATION_VERSION', '1.0.5' );
/**
 * Detect plugin. For use on Front End only.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "ecommerce-notification" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "define.php";
require_once $init_file;


/**
 * Class VI_ECOMMERCE_NOTIFICATION
 */
class VI_ECOMMERCE_NOTIFICATION {
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'uninstall' ) );

	}

	function global_note() {

	}

	/**
	 * When active plugin Function will be call
	 */
	public function install() {
		global $wp_version;
		if ( version_compare( $wp_version, "2.9", "<" ) ) {
			deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
			wp_die( "This plugin requires WordPress version 2.9 or higher." );
		}
		$json_data = '{"enable_mobile":"1","post_type":"","virtual_name":"Oliver\r\nJack\r\nHarry\r\nJacob\r\nCharlie","virtual_time":"10","country":"1","virtual_city":"New York City\r\nLos Angeles\r\nChicago\r\nDallas-Fort Worth\r\nHouston\r\nPhiladelphia\r\nWashington, D.C.","virtual_country":"United States","ipfind_auth_key":"","product_sizes":"thumbnail","message_purchased":"Someone in {city}, {country} purchased a {product_with_link} {time_ago}","highlight_color":"#000000","text_color":"#000000","background_color":"#ffffff","image_position":"0","position":"0","show_close_icon":"1","message_display_effect":"zoomIn","message_hidden_effect":"zoomOut","loop":"1","next_time":"60","notification_per_page":"30","initial_delay_min":"2","initial_delay":"0","display_time":"5","sound":"cool.mp3","custom_shortcode":"{number} people seeing this product right now","min_number":"100","max_number":"200","conditional_tags":"","history_time":"30"}';
		if ( ! get_option( 'ecommerce_notification_params', '' ) ) {
			update_option( 'ecommerce_notification_params', json_decode( $json_data, true ) );
		}
	}

	/**
	 * When deactive function will be call
	 */
	public function uninstall() {

	}
}

new VI_ECOMMERCE_NOTIFICATION();