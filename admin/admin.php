<?php

/*
Class Name: ECOMMERCE_NOTIFICATION_Admin_Admin
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2017 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ECOMMERCE_NOTIFICATION_Admin_Admin {
	function __construct() {

		add_filter( 'plugin_action_links_ecommerce-notification/ecommerce-notification.php', array(
			$this,
			'settings_link'
		) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 99999 );
	}

	function admin_init() {
		$params = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		$key    = $params->get_field( 'key' );
		new VillaTheme_Plugin_Check_Update ( ECOMMERCE_NOTIFICATION_VERSION,                    // current version
			'https://villatheme.com/wp-json/downloads/v3',  // update path
			'ecommerce-notification/ecommerce-notification.php',                  // plugin file slug
			'ecommerce-notification', '8894', $key );
		$setting_url = admin_url( '?page=ecommerce-notification' );
		new VillaTheme_Plugin_Updater( 'ecommerce-notification/ecommerce-notification.php', 'ecommerce-notification', $setting_url );
	}

	/**
	 *
	 * @param string $version
	 *
	 * @return bool
	 */
	protected function ecommerce_version_check( $version = '3.0' ) {

		global $ecommerce;

		if ( version_compare( $ecommerce->version, $version, ">=" ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Init Script in Admin
	 */
	public function admin_enqueue_scripts() {
		$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		if ( $page == 'ecommerce-notification' ) {
			global $wp_scripts;
			$scripts = $wp_scripts->registered;
			//			print_r($scripts);
			foreach ( $scripts as $k => $script ) {
				preg_match( '/^\/wp-/i', $script->src, $result );
				if ( count( array_filter( $result ) ) < 1 ) {
					wp_dequeue_script( $script->handle );
				}
			}

			/*Stylesheet*/
			wp_enqueue_style( 'ecommerce-notification-image', ECOMMERCE_NOTIFICATION_CSS . 'image.min.css' );
			wp_enqueue_style( 'ecommerce-notification-transition', ECOMMERCE_NOTIFICATION_CSS . 'transition.min.css' );
			wp_enqueue_style( 'ecommerce-notification-form', ECOMMERCE_NOTIFICATION_CSS . 'form.min.css' );
			wp_enqueue_style( 'ecommerce-notification-icon', ECOMMERCE_NOTIFICATION_CSS . 'icon.min.css' );
			wp_enqueue_style( 'ecommerce-notification-dropdown', ECOMMERCE_NOTIFICATION_CSS . 'dropdown.min.css' );
			wp_enqueue_style( 'ecommerce-notification-checkbox', ECOMMERCE_NOTIFICATION_CSS . 'checkbox.min.css' );
			wp_enqueue_style( 'ecommerce-notification-segment', ECOMMERCE_NOTIFICATION_CSS . 'segment.min.css' );
			wp_enqueue_style( 'ecommerce-notification-menu', ECOMMERCE_NOTIFICATION_CSS . 'menu.min.css' );
			wp_enqueue_style( 'ecommerce-notification-tab', ECOMMERCE_NOTIFICATION_CSS . 'tab.css' );
			wp_enqueue_style( 'ecommerce-notification-front', ECOMMERCE_NOTIFICATION_CSS . 'ecommerce-notification.css' );
			wp_enqueue_style( 'ecommerce-notification', ECOMMERCE_NOTIFICATION_CSS . 'ecommerce-notification-admin.css' );
			wp_enqueue_style( 'select2', ECOMMERCE_NOTIFICATION_CSS . 'select2.min.css' );
			wp_enqueue_script( 'select2-v4', ECOMMERCE_NOTIFICATION_JS . 'select2.js', array( 'jquery' ), '4.0.3', true );

			/*Script*/
			wp_enqueue_script( 'ecommerce-notification-dependsOn', ECOMMERCE_NOTIFICATION_JS . 'dependsOn-1.0.2.min.js', array( 'jquery' ), ECOMMERCE_NOTIFICATION_VERSION, true );
			wp_enqueue_script( 'ecommerce-notification-transition', ECOMMERCE_NOTIFICATION_JS . 'transition.min.js', array( 'jquery' ), ECOMMERCE_NOTIFICATION_VERSION, true );
			wp_enqueue_script( 'ecommerce-notification-dropdown', ECOMMERCE_NOTIFICATION_JS . 'dropdown.js', array( 'jquery' ), ECOMMERCE_NOTIFICATION_VERSION, true );
			wp_enqueue_script( 'ecommerce-notification-checkbox', ECOMMERCE_NOTIFICATION_JS . 'checkbox.js', array( 'jquery' ), ECOMMERCE_NOTIFICATION_VERSION, true );
			wp_enqueue_script( 'ecommerce-notification-tab', ECOMMERCE_NOTIFICATION_JS . 'tab.js', array( 'jquery' ), ECOMMERCE_NOTIFICATION_VERSION, true );
			wp_enqueue_script( 'ecommerce-notification-address', ECOMMERCE_NOTIFICATION_JS . 'jquery.address-1.6.min.js', array( 'jquery' ), ECOMMERCE_NOTIFICATION_VERSION, true );
			wp_enqueue_script( 'ecommerce-notification', ECOMMERCE_NOTIFICATION_JS . 'ecommerce-notification-admin.js', array( 'jquery' ), ECOMMERCE_NOTIFICATION_VERSION, true );

			/*Color picker*/
			wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array(
				'jquery-ui-draggable',
				'jquery-ui-slider',
				'jquery-touch-punch'
			), false, 1 );

			/*Custom*/
			$params           = new ECOMMERCE_NOTIFICATION_Admin_Settings();
			$highlight_color  = $params->get_field( 'highlight_color' );
			$text_color       = $params->get_field( 'text_color' );
			$background_color = $params->get_field( 'background_color' );
			$custom_css       = "
                #message-purchased{
                        background-color: {$background_color};
                        color:{$text_color};
                }
                 #message-purchased a{
                        color:{$highlight_color};
                }
                ";
			wp_add_inline_style( 'ecommerce-notification', $custom_css );

		}
	}

	/**
	 * Link to Settings
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public function settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=ecommerce-notification" title="' . __( 'Settings', 'ecommerce-notification' ) . '">' . __( 'Settings', 'ecommerce-notification' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}


	/**
	 * Function init when run plugin+
	 */
	function init() {
		/*Register post type*/

		load_plugin_textdomain( 'ecommerce-notification' );
		$this->load_plugin_textdomain();

	}


	/**
	 * load Language translate
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'ecommerce-notification' );
		// Admin Locale
		if ( is_admin() ) {
			load_textdomain( 'ecommerce-notification', ECOMMERCE_NOTIFICATION_LANGUAGES . "ecommerce-notification-$locale.mo" );
		}

		// Global + Frontend Locale
		load_textdomain( 'ecommerce-notification', ECOMMERCE_NOTIFICATION_LANGUAGES . "ecommerce-notification-$locale.mo" );
		load_plugin_textdomain( 'ecommerce-notification', false, ECOMMERCE_NOTIFICATION_LANGUAGES );
	}

	/**
	 * Register a custom menu page.
	 */
	public function menu_page() {
		add_menu_page( esc_html__( 'eCommerce Notification', 'ecommerce-notification' ), esc_html__( 'eCom Notification', 'ecommerce-notification' ), 'manage_options', 'ecommerce-notification', array(
			'ECOMMERCE_NOTIFICATION_Admin_Settings',
			'page_callback'
		), 'dashicons-megaphone', 2 );

	}

}

?>