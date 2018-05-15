<?php

/**
 * Class ECOMMERCE_NOTIFICATION_Frontend_Logs
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ECOMMERCE_NOTIFICATION_Frontend_Logs {
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'init' ) );
	}

	/**
	 * Detect IP
	 */
	public function init() {
		if ( ! isset( $_GET['link'] ) ) {
			return false;
		}
		if ( wp_verify_nonce( $_GET['link'], 'wocommerce_notification_click' ) ) {
			$this->save_click();
		} else {
			return false;
		}
	}

	/**
	 * Save click
	 */
	private function save_click() {
		/*Check Save Logs Option*/
		$params = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		if ( $params->get_field( 'save_logs' ) ) {
			global $post;
			$product_id = $post->ID;
			$file_name  = mktime( 0, 0, 0, date( "m" ), date( "d" ), date( "Y" ) ) . '.txt';
			$file_path  = ECOMMERCE_NOTIFICATION_CACHE . $file_name;
			if ( is_file( $file_path ) ) {
				file_put_contents( $file_path, ',' . $product_id, FILE_APPEND );
			} else {
				file_put_contents( $file_path, $product_id );
			}
		} else {
			return false;
		}
	}

}