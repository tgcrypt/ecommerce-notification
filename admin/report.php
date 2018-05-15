<?php

/*
Class Name: ECOMMERCE_NOTIFICATION_Admin_Report
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2015 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ECOMMERCE_NOTIFICATION_Admin_Report {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

	}
	/**
	 * Add script
	 */
	public function admin_enqueue_scripts() {
		$page    = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		$id      = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : '';
		$subpage = isset( $_GET['subpage'] ) ? $_GET['subpage'] : '';
		if ( $page == 'ecommerce-notification-report' ) {
			wp_enqueue_style( 'jquery-ui-datepicker', ECOMMERCE_NOTIFICATION_CSS . 'jquery-ui-1.10.1.css' );
			wp_enqueue_style( 'jquery-ui-datepicker-latoja', ECOMMERCE_NOTIFICATION_CSS . 'latoja.datepicker.css' );
			wp_enqueue_style( 'ecommerce-notification-menu', ECOMMERCE_NOTIFICATION_CSS . 'menu.min.css' );
			wp_enqueue_style( 'ecommerce-notification-form', ECOMMERCE_NOTIFICATION_CSS . 'form.min.css' );


			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'ecommerce-notification-chart', ECOMMERCE_NOTIFICATION_JS . 'Chart.min.js', array( 'jquery' ) );
			wp_enqueue_script( 'ecommerce-notification-report', ECOMMERCE_NOTIFICATION_JS . 'ecommerce-notification-admin-report.js', array( 'jquery' ) );


			/*Custom*/

			if ( $id && $subpage ) {
				$data = $this->get_data( $id );
			} else {
				$data = $this->get_data();
			}
			if ( $data ) {

				/*Labels*/
				$labels = array();
				if ( count( $data->label ) ) {
					foreach ( $data->label as $label ) {
						$labels[] = date( "M d", $label );
					}
				}
				$labels = '"' . implode( '","', $labels ) . '"';

				/*Data*/
				$counts = array();

				if ( count( $data->data ) ) {
					if ( $id && $subpage ) {
						$counts = $data->data;
					} else {
						foreach ( $data->data as $count ) {
							$counts[] = count( $count );
						}
					}
				}
				$counts = '"' . implode( '","', $counts ) . '"';


				/*Javascript*/
				$script = '
					var woo_notification_labels = [' . $labels . '];
					var woo_notification_label = ["' . esc_js( __( 'Click', 'ecommerce-notification' ) ) . '"];
					var woo_notification_data = [' . $counts . '];';
				wp_add_inline_script( 'ecommerce-notification-report', $script );

			} else {

				$script = '
					var woo_notification_labels = [];
					var woo_notification_label = ["' . esc_js( __( 'Click', 'ecommerce-notification' ) ) . '"];
					var woo_notification_data = [];';
				wp_add_inline_script( 'ecommerce-notification-report', $script );
			}
		}
	}

	/**
	 * Get Click Quantity
	 *
	 * @return Object
	 */
	private function get_data( $id = false ) {
		$start_date = '';
		$end_date   = '';
		if ( isset( $_POST['_wpnonce'] ) ) {
			if ( wp_verify_nonce( $_POST['_wpnonce'], 'ecommerce_notification_filter_date' ) ) {
				$start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
				$end_date   = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';
				/*Convert to int*/
				$start_date = strtotime( $start_date );
				$end_date   = strtotime( $end_date );
			}
		}


		$files = $this->scan_dir( ECOMMERCE_NOTIFICATION_CACHE );
		if ( ! is_array( $files ) ) {
			return false;
		}
		$data  = new stdClass();
		$files = array_map( 'intval', $files );
		asort( $files );
		$files = array_values( $files );

		/*Filter files*/
		if ( $start_date || $end_date ) {
			$new_arg = array();
			if ( $start_date && $end_date ) {
				foreach ( $files as $file ) {
					if ( $file >= $start_date && $file <= $end_date ) {
						$new_arg[] = $file;
					}
				}
			} elseif ( $start_date ) {
				foreach ( $files as $file ) {
					if ( $file >= $start_date ) {
						$new_arg[] = $file;
					}
				}
			} else {
				foreach ( $files as $file ) {
					if ( $file <= $end_date ) {
						$new_arg[] = $file;
					}
				}
			}

			$files = $new_arg;

			if ( count( $files ) < 1 ) {
				return false;
			}
		}

		$data->label = $files;
		$temp        = array();
		foreach ( $files as $file ) {
			$content = file_get_contents( ECOMMERCE_NOTIFICATION_CACHE . $file . '.txt' );
			if ( $content ) {
				$array = explode( ',', $content );
				if ( $id ) {
					$counts = array_count_values( $array );
					$temp[] = isset( $counts[$id] ) ? $counts[$id] : 0;
				} else {
					$temp[] = $array;
				}
			}
		}
		if ( count( $temp ) ) {
			$data->data = $temp;
		} else {
			$data->data = false;
		}

		return $data;
	}

	/**
	 * Get files in directory
	 *
	 * @param $dir
	 *
	 * @return array|bool
	 */
	private function scan_dir( $dir ) {
		$ignored = array( '.', '..', '.svn', '.htaccess', 'test-log.log' );

		$files = array();
		foreach ( scandir( $dir ) as $file ) {
			if ( in_array( $file, $ignored ) ) {
				continue;
			}
			$files[$file] = filemtime( $dir . '/' . $file );
		}
		arsort( $files );
		$files = array_keys( $files );

		return ( $files ) ? $files : false;
	}

	/**
	 * HTML Reporting
	 */
	public function page_callback() {
		$start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
		$end_date   = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';
		$active     = isset( $_GET['subpage'] ) ? 1 : 0;

		?>
		<h2><?php esc_html_e( 'WooCommerce Notification Reporting', 'ecommerce-notification' ) ?></h2>
		<div class="vi-ui secondary pointing menu">
			<a class="item <?php echo ! $active ? 'active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=ecommerce-notification-report' ) ?>"><?php esc_html_e( 'Clicks by date', 'ecommerce-notification' ) ?></a>
			<a class="item <?php echo $active ? 'active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=ecommerce-notification-report&subpage=byproduct' ) ?>"><?php esc_html_e( 'Clicks by product', 'ecommerce-notification' ) ?></a>
		</div>
		<?php if ( ! $active ) { ?>
			<form class="vi-ui form" action="" method="post">
				<?php wp_nonce_field( 'ecommerce_notification_filter_date', '_wpnonce' ) ?>
				<div class="inline fields">
					<div class="two field">
						<label><?php esc_html_e( 'From', 'ecommerce-notification' ) ?></label>
						<input type="text" name="start_date" class="datepicker" value="<?php echo esc_attr( $start_date ) ?>" />
					</div>
					<div class="two field">
						<label><?php esc_html_e( 'To', 'ecommerce-notification' ) ?></label>
						<input type="text" name="end_date" class="datepicker" value="<?php echo esc_attr( $end_date ) ?>" />
					</div>
					<div class="two field">
						<input type="submit" value=" <?php esc_html_e( 'SUBMIT', 'ecommerce-notification' ) ?> " class="button button-primary" />
					</div>
				</div>
			</form>
		<?php } ?>
		<div class="vi-ui form">
			<div class="fields">
				<?php if ( $active ) { ?>
					<div class="five wide field">
						<h3><?php echo esc_html__( 'Top Click', 'ecommerce-notification' ) ?></h3>
						<table class="table" width="100%" cellspacing="2" cellpadding="2">
							<tr>
								<th align="left" width="80%"><?php esc_html_e( 'Products', 'ecommerce-notification' ) ?></th>
								<th align="left" width="20%"><?php esc_html_e( 'Clicked', 'ecommerce-notification' ) ?></th>
							</tr>
							<?php
							$data = new stdClass();
							$data = $this->get_data();
							if ( isset( $data->data ) ) {
								$result   = array_reduce( $data->data, 'array_merge', array() );
								$products = array_count_values( $result );
								arsort( $products );
								foreach ( $products as $k => $count ) { ?>
									<tr>
										<td>
											<a href="<?php echo admin_url( 'admin.php?page=ecommerce-notification-report&subpage=byproduct&id=' . $k ) ?>">
												<?php echo esc_html( get_post_field( 'post_title', $k ) ) ?>
											</a>
										</td>
										<td>
											<span class="count"><?php echo esc_html( $count ) ?></span>
										</td>
									</tr>
								<?php }
							}
							?>
						</table>
					</div>
				<?php } ?>
				<div class="eleven wide field">
					<canvas id="myChart"></canvas>
				</div>

			</div>
		</div>
	<?php }

	/**
	 * Register a custom menu page.
	 */
	public function menu_page() {
		add_submenu_page(
			'ecommerce-notification',
			esc_html__( 'Report', 'ecommerce-notification' ),
			esc_html__( 'Report', 'ecommerce-notification' ),
			'manage_options',
			'ecommerce-notification-report',
			array( $this, 'page_callback' )
		);

	}
}

?>