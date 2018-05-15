<?php

/*
Class Name: ECOMMERCE_NOTIFICATION_Admin_System
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2015 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ECOMMERCE_NOTIFICATION_Admin_System {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu_page' ) );
	}

	public function page_callback() { ?>
		<h2><?php esc_html_e( 'System Status', 'ecommerce-notification' ) ?></h2>
		<table cellspacing="0" id="status" class="widefat">
			<tbody>

			<tr>
				<td data-export-label="<?php esc_html_e( 'Log Directory Writable', 'ecommerce-notification' ) ?>"><?php esc_html_e( 'Log Directory Writable', 'ecommerce-notification' ) ?></td>
				<td>
					<?php
					if ( @fopen( ECOMMERCE_NOTIFICATION_CACHE . 'test-log.log', 'a' ) ) {
						echo '<mark class="yes">&#10004; <code class="private">' . ECOMMERCE_NOTIFICATION_CACHE . '</code></mark> ';
					} else {
						printf( '<mark class="error">&#10005; ' . esc_html__( 'To allow logging, make <code>%s</code> writable or define a custom <code>ECOMMERCE_NOTIFICATION_CACHE</code>.', 'ecommerce-notification' ) . '</mark>', ECOMMERCE_NOTIFICATION_CACHE );
					}
					?>

				</td>
			</tr>
			<tr>
				<td data-export-label="file_get_contents">file_get_contents</td>
				<td>
					<?php
					if ( function_exists( 'file_get_contents' ) ) {
						echo '<mark class="yes">&#10004; <code class="private"></code></mark> ';
					} else {
						echo '<mark class="error">&#10005; </mark>';
					}
					?>
				</td>
			</tr>
			<tr>
				<td data-export-label="file_put_contents">file_put_contents</td>
				<td>
					<?php
					if ( function_exists( 'file_put_contents' ) ) {
						echo '<mark class="yes">&#10004; <code class="private"></code></mark> ';
					} else {
						echo '<mark class="error">&#10005; </mark>';
					}
					?>

				</td>
			</tr>
			<tr>
				<td data-export-label="mkdir">mkdir</td>
				<td>
					<?php
					if ( function_exists( 'mkdir' ) ) {
						echo '<mark class="yes">&#10004; <code class="private"></code></mark> ';
					} else {
						echo '<mark class="error">&#10005; </mark>';
					}
					?>

				</td>
			</tr>
			<tr>
				<td data-export-label="<?php esc_html_e( 'PHP Time Limit', 'ecommerce-notification' ) ?>"><?php esc_html_e( 'PHP Time Limit', 'ecommerce-notification' ) ?></td>
				<td><?php echo ini_get( 'max_execution_time' ); ?></td>
			</tr>
			<tr>
				<td data-export-label="<?php esc_html_e( 'PHP Max Input Vars', 'ecommerce-notification' ) ?>"><?php esc_html_e( 'PHP Max Input Vars', 'ecommerce-notification' ) ?></td>

				<td><?php echo ini_get( 'max_input_vars' ); ?></td>
			</tr>
			<tr>
				<td data-export-label="<?php esc_html_e( 'Memory Limit', 'ecommerce-notification' ) ?>"><?php esc_html_e( 'Memory Limit', 'ecommerce-notification' ) ?></td>

				<td><?php echo ini_get( 'memory_limit' ); ?></td>
			</tr>

			</tbody>
		</table>
	<?php }

	/**
	 * Register a custom menu page.
	 */
	public function menu_page() {
		add_submenu_page(
			'ecommerce-notification',
			esc_html__( 'System Status', 'ecommerce-notification' ),
			esc_html__( 'System Status', 'ecommerce-notification' ),
			'manage_options',
			'ecommerce-notification-status',
			array( $this, 'page_callback' )
		);

	}
}

?>