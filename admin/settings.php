<?php

/*
Class Name: WP_SM_Admin_Settings
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2016 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ECOMMERCE_NOTIFICATION_Admin_Settings {
	static $params;

	public function __construct() {
		add_action( 'admin_init', array( $this, 'save_meta_boxes' ) );
		add_action( 'wp_ajax_wcn_search_product', array( $this, 'search_product' ) );
	}

	/*Ajax Search*/
	public function search_product( $x = '' ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		ob_start();

		$keyword   = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );
		$post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );
		if ( empty( $keyword ) ) {
			die();
		}
		$arg            = array(
			'post_status'    => 'publish',
			'post_type'      => $post_type,
			'posts_per_page' => 50,
			's'              => $keyword

		);
		$the_query      = new WP_Query( $arg );
		$found_products = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$product          = array( 'id' => get_the_ID(), 'text' => get_the_title() );
				$found_products[] = $product;
			}
		}
		wp_send_json( $found_products );
		die;
	}

	/**
	 * Get files in directory
	 *
	 * @param $dir
	 *
	 * @return array|bool
	 */
	static private function scan_dir( $dir ) {
		$ignored = array( '.', '..', '.svn', '.htaccess', 'test-log.log' );

		$files = array();
		foreach ( scandir( $dir ) as $file ) {
			if ( in_array( $file, $ignored ) ) {
				continue;
			}
			$files[ $file ] = filemtime( $dir . '/' . $file );
		}
		arsort( $files );
		$files = array_keys( $files );

		return ( $files ) ? $files : false;
	}

	private function stripslashes_deep( $value ) {
		$value = is_array( $value ) ? array_map( 'stripslashes_deep', $value ) : stripslashes( $value );

		return $value;
	}

	/**
	 * Save post meta
	 *
	 * @param $post
	 *
	 * @return bool
	 */
	public function save_meta_boxes() {
		if ( ! isset( $_POST['_ecommerce_notification_nonce'] ) || ! isset( $_POST['ecommerce_notification_params'] ) ) {
			return false;
		}
		if ( ! wp_verify_nonce( $_POST['_ecommerce_notification_nonce'], 'ecommerce_notification_save_email_settings' ) ) {
			return false;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$_POST['ecommerce_notification_params']['conditional_tags'] = $this->stripslashes_deep( $_POST['ecommerce_notification_params']['conditional_tags'] );
		update_option( '_ecommerce_notification_prefix', substr( md5( date( "YmdHis" ) ), 0, 10 ) );
		update_option( 'ecommerce_notification_params', $_POST['ecommerce_notification_params'] );
		if ( is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' ) ) {
			$cache = new WpFastestCache();
			$cache->deleteCache( true );
		}
	}

	/**
	 * Set Nonce
	 * @return string
	 */
	protected static function set_nonce() {
		return wp_nonce_field( 'ecommerce_notification_save_email_settings', '_ecommerce_notification_nonce' );
	}

	/**
	 * Set field in meta box
	 *
	 * @param      $field
	 * @param bool $multi
	 *
	 * @return string
	 */
	protected static function set_field( $field, $multi = false ) {
		if ( $field ) {
			if ( $multi ) {
				return 'ecommerce_notification_params[' . $field . '][]';
			} else {
				return 'ecommerce_notification_params[' . $field . ']';
			}
		} else {
			return '';
		}
	}

	/**
	 * Get Post Meta
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public static function get_field( $field, $default = '' ) {
		$params = get_option( 'ecommerce_notification_params', array() );
		if ( self::$params ) {
			$params = self::$params;
		} else {
			self::$params = $params;
		}
		if ( isset( $params[ $field ] ) && $field ) {
			return $params[ $field ];
		} else {
			return $default;
		}
	}

	/**
	 * Get list shortcode
	 * @return array
	 */
	public static function page_callback() {
		self::$params = get_option( 'ecommerce_notification_params', array() );
		?>
		<div class="wrap ecommerce-notification">
			<h2><?php esc_attr_e( 'WordPress eCommerce Notification Settings', 'ecommerce-notification' ) ?></h2>

			<div class="top-help">
				<a target="_blank" class="button" href="https://villatheme.com/supports/forum/plugins/ecommerce-notification/"><?php esc_html_e( 'Get Support', 'ecommerce-notification' ) ?></a>
				<a target="_blank" class="button" href="http://docs.villatheme.com/?item=ecommerce-notification"><?php esc_html_e( 'Documentation', 'ecommerce-notification' ) ?></a>
			</div>
			<form method="post" action="" class="vi-ui form">
				<?php echo ent2ncr( self::set_nonce() ) ?>

				<div class="vi-ui attached tabular menu">
					<div class="item active" data-tab="general"><?php esc_html_e( 'General', 'ecommerce-notification' ) ?></div>
					<div class="item" data-tab="design"><?php esc_html_e( 'Design', 'ecommerce-notification' ) ?></div>
					<div class="item" data-tab="products"><?php esc_html_e( 'Products', 'ecommerce-notification' ) ?></div>
					<div class="item" data-tab="product-detail"><?php esc_html_e( 'Product Detail', 'ecommerce-notification' ) ?></div>
					<div class="item" data-tab="time"><?php esc_html_e( 'Time', 'ecommerce-notification' ) ?></div>
					<div class="item" data-tab="sound"><?php esc_html_e( 'Sound', 'ecommerce-notification' ) ?></div>
					<div class="item" data-tab="short-code"><?php esc_html_e( 'Short code', 'ecommerce-notification' ) ?></div>
					<div class="item" data-tab="assign"><?php esc_html_e( 'Assign', 'ecommerce-notification' ) ?></div>
					<div class="item" data-tab="logs"><?php esc_html_e( 'Report', 'ecommerce-notification' ) ?></div>
					<div class="item" data-tab="update"><?php esc_html_e( 'Update', 'ecommerce-notification' ) ?></div>
				</div>
				<div class="vi-ui bottom attached tab segment active" data-tab="general">
					<!-- Tab Content !-->
					<table class="optiontable form-table">
						<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'enable' ) ?>">
									<?php esc_html_e( 'Enable', 'ecommerce-notification' ) ?>
								</label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php echo self::set_field( 'enable' ) ?>" type="checkbox" <?php checked( self::get_field( 'enable' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'enable' ) ?>" />
									<label></label>
								</div>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'enable_mobile' ) ?>">
									<?php esc_html_e( 'Mobile', 'ecommerce-notification' ) ?>
								</label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php echo self::set_field( 'enable_mobile' ) ?>" type="checkbox" <?php checked( self::get_field( 'enable_mobile' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'enable_mobile' ) ?>" />
									<label></label>
								</div>
								<p class="description"><?php esc_html_e( 'Notification will show on mobile and responsive.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<!--Products-->
				<div class="vi-ui bottom attached tab segment" data-tab="products">
					<!-- Tab Content !-->
					<table class="optiontable form-table">
						<tbody>

						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Select Post Type', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<?php

								$args = array(
									'public'   => true,
									'_builtin' => false
								);

								$output = 'objects'; // names or objects, note names is the default

								$post_types = get_post_types( $args, $output );


								?>
								<select name="<?php echo self::set_field( 'post_type' ) ?>" class="vi-ui fluid dropdown vi-select-post_type">
										<option value="post" <?php selected( self::get_field( 'post_type' ), 'post' ); ?>><?php echo esc_html__( 'Post', 'ecommerce-notification' ) ?></option>
										<option value="page" <?php selected( self::get_field( 'post_type' ), 'page' ); ?>><?php echo esc_html__( 'Page', 'ecommerce-notification' ) ?></option>
									<?php if ( count( $post_types ) ) { ?>

										<?php foreach ( $post_types as $post_type ) { ?>
											<option value="<?php echo esc_attr( $post_type->name ) ?>" <?php selected( self::get_field( 'post_type' ), $post_type->name ); ?>><?php echo esc_html( $post_type->label ) . " ({$post_type->name})" ?></option>
										<?php }
									}
									?>
								</select>

								<p class="description"><?php esc_html_e( 'Please select post_type what you want get product.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top" class="select_product">
							<th scope="row">
								<label><?php esc_html_e( 'Select Items', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<?php
								$products_ach    = self::get_field( 'archive_products', array() );
								$post_type       = self::get_field( 'post_type' );
								$arg             = array(
									'post_status'    => 'publish',
									'post_type'      => $post_type,
									'posts_per_page' => - 1,
									'post__in'       => $products_ach
								);
								$products_result = new WP_Query( $arg );
								//print_r($products_result);
								?>
								<select multiple="multiple" name="<?php echo self::set_field( 'archive_products', true ) ?>" class="product-search vi-select-items" placeholder="<?php esc_attr_e( 'Please select products', 'ecommerce-notification' ) ?>">
									<?php if ( count( $products_ach ) ) {
										$post_type       = self::get_field( 'post_type' );
										$arg             = array(
											'post_status'    => 'publish',
											'post_type'      => $post_type,
											'posts_per_page' => - 1,
											'post__in'       => $products_ach
										);
										$products_result = new WP_Query( $arg );
										if ( $products_result->have_posts() ) {
											while ( $products_result->have_posts() ) {
												$products_result->the_post(); ?>
												<option selected="selected" value="<?php echo esc_attr( get_the_ID() ); ?>"><?php echo esc_html( get_the_title() ); ?></option>
												<?php
											}
											wp_reset_postdata();
										}
									} ?>
								</select>
							</td>
						</tr>
						<tr valign="top" class="select_product">
							<th scope="row">
								<label><?php esc_html_e( 'Virtual First Name', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<textarea name="<?php echo self::set_field( 'virtual_name' ) ?>"><?php echo self::get_field( 'virtual_name' ) ?></textarea>

								<p class="description"><?php esc_html_e( 'Virtual first name what will show on notification. Each first name on a line.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top" class="select_product">
							<th scope="row">
								<label><?php esc_html_e( 'Virtual Time', 'ecommerce-notification' ) ?></label></th>
							<td>
								<div class="vi-ui form">
									<div class="inline fields">
										<input type="number" name="<?php echo self::set_field( 'virtual_time' ) ?>" value="<?php echo self::get_field( 'virtual_time', '10' ) ?>" />
										<label><?php esc_html_e( 'hours', 'ecommerce-notification' ) ?></label>
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'Time will auto get random in this time threshold ago.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top" class="select_product">
							<th scope="row">
								<label><?php esc_html_e( 'Address', 'ecommerce-notification' ) ?></label></th>
							<td>
								<select name="<?php echo self::set_field( 'country' ) ?>" class="vi-ui fluid dropdown">
									<option <?php selected( self::get_field( 'country' ), 0 ) ?> value="0"><?php esc_attr_e( 'Auto Detect', 'ecommerce-notification' ) ?></option>
									<option <?php selected( self::get_field( 'country' ), 1 ) ?> value="1"><?php esc_attr_e( 'Virtual', 'ecommerce-notification' ) ?></option>
								</select>

								<p class="description"><?php esc_html_e( 'You can use auto detect address or make virtual address of customer.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top" class="virtual_address hidden">
							<th scope="row">
								<label><?php esc_html_e( 'Virtual City', 'ecommerce-notification' ) ?></label></th>
							<td>
								<textarea name="<?php echo self::set_field( 'virtual_city' ) ?>"><?php echo self::get_field( 'virtual_city' ) ?></textarea>

								<p class="description"><?php esc_html_e( 'Virtual city name what will show on notification. Each city name on a line.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top" class="virtual_address hidden">
							<th scope="row">
								<label><?php esc_html_e( 'Virtual Country', 'ecommerce-notification' ) ?></label></th>
							<td>
								<input type="text" name="<?php echo self::set_field( 'virtual_country' ) ?>" value="<?php echo self::get_field( 'virtual_country' ) ?>" />

								<p class="description"><?php esc_html_e( 'Virtual country name what will show on notification.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top" class="detect_address">
							<th scope="row">
								<label><?php esc_html_e( 'Ipfind Auth Key', 'ecommerce-notification' ) ?></label></th>
							<td>
								<input type="text" name="<?php echo self::set_field( 'ipfind_auth_key' ) ?>" value="<?php echo self::get_field( 'ipfind_auth_key', '' ) ?>" />

								<p class="description"><?php esc_html_e( 'When you use detect IP, please enter your auth key. You can get at https://ipfind.co', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Product image size', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<select name="<?php echo self::set_field( 'product_sizes' ) ?>" class="vi-ui fluid dropdown">
									<option <?php selected( self::get_field( 'product_sizes' ), 'thumbnail' ) ?> value="thumbnail"><?php esc_attr_e( 'thumbnail', 'ecommerce-notification' ) ?> - <?php echo ( get_option( "thumbnail_size_w" ) && get_option( "thumbnail_size_h" ) ) ? get_option( "thumbnail_size_w" ) . 'x' . get_option( "thumbnail_size_h" ) : ''; ?></option>
									<option <?php selected( self::get_field( 'product_sizes' ), 'medium' ) ?> value="medium"><?php esc_attr_e( 'medium', 'ecommerce-notification' ) ?> - <?php echo ( get_option( "medium_size_w" ) && get_option( "medium_size_h" ) ) ? get_option( "medium_size_w" ) . 'x' . get_option( "medium_size_h" ) : ''; ?></option>
									<option <?php selected( self::get_field( 'product_sizes' ), 'large' ) ?> value="large"><?php esc_attr_e( 'large', 'ecommerce-notification' ) ?> - <?php echo ( get_option( "large_size_w" ) && get_option( "large_size_h" ) ) ? get_option( "large_size_w" ) . 'x' . get_option( "large_size_h" ) : ''; ?></option>
								</select>

								<p class="description"><?php esc_html_e( 'Image size will get form your WordPress site.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'cache_enable' ) ?>"><?php esc_html_e( 'Cache', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php echo self::set_field( 'cache_enable' ) ?>" type="checkbox" <?php checked( self::get_field( 'cache_enable' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'cache_enable' ) ?>" />
									<label></label>
								</div>
								<p class="description"><?php esc_html_e( 'Virtual name and city will be saved in cookie. Your site is faster.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<!-- Product detail !-->
				<div class="vi-ui bottom attached tab segment" data-tab="product-detail">
					<!-- Tab Content !-->
					<table class="optiontable form-table">
						<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'enable_single_product' ) ?>"><?php esc_html_e( 'Run single product', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php echo self::set_field( 'enable_single_product' ) ?>" type="checkbox" <?php checked( self::get_field( 'enable_single_product' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'enable_single_product' ) ?>" />
									<label></label>
								</div>
								<p class="description"><?php esc_html_e( 'Notification will only display current product in product detail page that they are viewing.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<!-- Design !-->
				<div class="vi-ui bottom attached tab segment" data-tab="design">
					<!-- Tab Content !-->
					<table class="optiontable form-table">
						<tbody>
						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Message purchased', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<textarea name="<?php echo self::set_field( 'message_purchased' ) ?>"><?php echo strip_tags( self::get_field( 'message_purchased', 'Someone in {city}, {country} purchased a {product_with_link} {time_ago}' ) ) ?></textarea>
								<ul class="description" style="list-style: none">
									<li>
										<span>{first_name}</span> - <?php esc_html_e( 'Customer\'s first name', 'ecommerce-notification' ) ?>
									</li>
									<li>
										<span>{city}</span> - <?php esc_html_e( 'Customer\'s city', 'ecommerce-notification' ) ?>
									</li>
									<li>
										<span>{country}</span> - <?php esc_html_e( 'Customer\'s country', 'ecommerce-notification' ) ?>
									</li>
									<li>
										<span>{product}</span> - <?php esc_html_e( 'Product title', 'ecommerce-notification' ) ?>
									</li>
									<li>
										<span>{product_with_link}</span> - <?php esc_html_e( 'Product title with link', 'ecommerce-notification' ) ?>
									</li>
									<li>
										<span>{time_ago}</span> - <?php esc_html_e( 'Time after purchase', 'ecommerce-notification' ) ?>
									</li>
									<li>
										<span>{custom}</span> - <?php esc_html_e( 'Use custom shortcode', 'ecommerce-notification' ) ?>
									</li>
								</ul>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Highlight color', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<input data-ele="highlight" type="text" class="color-picker" name="<?php echo self::set_field( 'highlight_color' ) ?>" value="<?php echo self::get_field( 'highlight_color', '#000000' ) ?>" style="background-color: <?php echo esc_attr( self::get_field( 'highlight_color', '#000000' ) ) ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Text color', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<input data-ele="textcolor" style="background-color: <?php echo esc_attr( self::get_field( 'text_color', '#000000' ) ) ?>" type="text" class="color-picker" name="<?php echo self::set_field( 'text_color' ) ?>" value="<?php echo self::get_field( 'text_color', '#000000' ) ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Background color', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<input style="background-color: <?php echo esc_attr( self::get_field( 'background_color', '#ffffff' ) ) ?>" data-ele="backgroundcolor" type="text" class="color-picker" name="<?php echo self::set_field( 'background_color' ) ?>" value="<?php echo self::get_field( 'background_color', '#ffffff' ) ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Image Position', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<select name="<?php echo self::set_field( 'image_position' ) ?>" class="vi-ui fluid dropdown">
									<option <?php selected( self::get_field( 'image_position' ), 0 ) ?> value="0"><?php esc_attr_e( 'Left', 'ecommerce-notification' ) ?></option>
									<option <?php selected( self::get_field( 'image_position' ), 1 ) ?> value="1"><?php esc_attr_e( 'Right', 'ecommerce-notification' ) ?></option>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Position', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui form">
									<div class="fields">
										<div class="four wide field">
											<img src="<?php echo ECOMMERCE_NOTIFICATION_IMAGES . 'position_1.jpg' ?>" class="vi-ui centered medium image middle aligned " />

											<div class="vi-ui toggle checkbox center aligned segment">
												<input id="<?php echo self::set_field( 'position' ) ?>" type="radio" <?php checked( self::get_field( 'position', 0 ), 0 ) ?> tabindex="0" class="hidden" value="0" name="<?php echo self::set_field( 'position' ) ?>" />
												<label><?php esc_attr_e( 'Bottom left', 'ecommerce-notification' ) ?></label>
											</div>

										</div>
										<div class="four wide field">
											<img src="<?php echo ECOMMERCE_NOTIFICATION_IMAGES . 'position_2.jpg' ?>" class="vi-ui centered medium image middle aligned " />

											<div class="vi-ui toggle checkbox center aligned segment">
												<input id="<?php echo self::set_field( 'position' ) ?>" type="radio" <?php checked( self::get_field( 'position' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'position' ) ?>" />
												<label><?php esc_attr_e( 'Bottom right', 'ecommerce-notification' ) ?></label>
											</div>
										</div>
										<div class="four wide field">
											<img src="<?php echo ECOMMERCE_NOTIFICATION_IMAGES . 'position_4.jpg' ?>" class="vi-ui centered medium image middle aligned " />

											<div class="vi-ui toggle checkbox center aligned segment">
												<input id="<?php echo self::set_field( 'position' ) ?>" type="radio" <?php checked( self::get_field( 'position' ), 2 ) ?> tabindex="0" class="hidden" value="2" name="<?php echo self::set_field( 'position' ) ?>" />
												<label><?php esc_attr_e( 'Top left', 'ecommerce-notification' ) ?></label>
											</div>
										</div>
										<div class="four wide field">
											<img src="<?php echo ECOMMERCE_NOTIFICATION_IMAGES . 'position_3.jpg' ?>" class="vi-ui centered medium image middle aligned " />

											<div class="vi-ui toggle checkbox center aligned segment">
												<input id="<?php echo self::set_field( 'position' ) ?>" type="radio" <?php checked( self::get_field( 'position' ), 3 ) ?> tabindex="0" class="hidden" value="3" name="<?php echo self::set_field( 'position' ) ?>" />
												<label><?php esc_attr_e( 'Top right', 'ecommerce-notification' ) ?></label>
											</div>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'show_close_icon' ) ?>">
									<?php esc_html_e( 'Show Close Icon', 'ecommerce-notification' ) ?>
								</label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php echo self::set_field( 'show_close_icon' ) ?>" type="checkbox" <?php checked( self::get_field( 'show_close_icon' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'show_close_icon' ) ?>" />
									<label></label>
								</div>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'message_display_effect' ) ?>">
									<?php esc_html_e( 'Message display effect', 'ecommerce-notification' ) ?>
								</label>
							</th>
							<td>
								<select name="<?php echo self::set_field( 'message_display_effect' ) ?>" class="vi-ui fluid dropdown">
									<optgroup label="Bouncing Entrances">
										<option <?php selected( self::get_field( 'message_display_effect' ), 'bounceIn' ) ?> value="bounceIn"><?php esc_attr_e( 'bounceIn', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'bounceInDown' ) ?> value="bounceInDown"><?php esc_attr_e( 'bounceInDown', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'bounceInLeft' ) ?> value="bounceInLeft"><?php esc_attr_e( 'bounceInLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'bounceInRight' ) ?> value="bounceInRight"><?php esc_attr_e( 'bounceInRight', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'bounceInUp' ) ?> value="bounceInUp"><?php esc_attr_e( 'bounceInUp', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Fading Entrances">
										<option <?php selected( self::get_field( 'message_display_effect' ), 'fade-in' ) ?> value="fade-in"><?php esc_attr_e( 'fadeIn', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'fadeInDown' ) ?> value="fadeInDown"><?php esc_attr_e( 'fadeInDown', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'fadeInDownBig' ) ?> value="fadeInDownBig"><?php esc_attr_e( 'fadeInDownBig', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'fadeInLeft' ) ?> value="fadeInLeft"><?php esc_attr_e( 'fadeInLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'fadeInLeftBig' ) ?> value="fadeInLeftBig"><?php esc_attr_e( 'fadeInLeftBig', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'fadeInRight' ) ?> value="fadeInRight"><?php esc_attr_e( 'fadeInRight', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'fadeInRightBig' ) ?> value="fadeInRightBig"><?php esc_attr_e( 'fadeInRightBig', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'fadeInUp' ) ?> value="fadeInUp"><?php esc_attr_e( 'fadeInUp', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'fadeInUpBig' ) ?> value="fadeInUpBig"><?php esc_attr_e( 'fadeInUpBig', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Flippers">
										<option <?php selected( self::get_field( 'message_display_effect' ), 'flipInX' ) ?> value="flipInX"><?php esc_attr_e( 'flipInX', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'flipInY' ) ?> value="flipInY"><?php esc_attr_e( 'flipInY', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Lightspeed">
										<option <?php selected( self::get_field( 'message_display_effect' ), 'lightSpeedIn' ) ?> value="lightSpeedIn"><?php esc_attr_e( 'lightSpeedIn', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Rotating Entrances">
										<option <?php selected( self::get_field( 'message_display_effect' ), 'rotateIn' ) ?> value="rotateIn"><?php esc_attr_e( 'rotateIn', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'rotateInDownLeft' ) ?> value="rotateInDownLeft"><?php esc_attr_e( 'rotateInDownLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'rotateInDownRight' ) ?> value="rotateInDownRight"><?php esc_attr_e( 'rotateInDownRight', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'rotateInUpLeft' ) ?> value="rotateInUpLeft"><?php esc_attr_e( 'rotateInUpLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'rotateInUpRight' ) ?> value="rotateInUpRight"><?php esc_attr_e( 'rotateInUpRight', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Sliding Entrances">
										<option <?php selected( self::get_field( 'message_display_effect' ), 'slideInUp' ) ?> value="slideInUp"><?php esc_attr_e( 'slideInUp', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'slideInDown' ) ?> value="slideInDown"><?php esc_attr_e( 'slideInDown', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'slideInLeft' ) ?> value="slideInLeft"><?php esc_attr_e( 'slideInLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'slideInRight' ) ?> value="slideInRight"><?php esc_attr_e( 'slideInRight', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Zoom Entrances">
										<option <?php selected( self::get_field( 'message_display_effect' ), 'zoomIn' ) ?> value="zoomIn"><?php esc_attr_e( 'zoomIn', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'zoomInDown' ) ?> value="zoomInDown"><?php esc_attr_e( 'zoomInDown', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'zoomInLeft' ) ?> value="zoomInLeft"><?php esc_attr_e( 'zoomInLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'zoomInRight' ) ?> value="zoomInRight"><?php esc_attr_e( 'zoomInRight', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_display_effect' ), 'zoomInUp' ) ?> value="zoomInUp"><?php esc_attr_e( 'zoomInUp', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Special">
										<option <?php selected( self::get_field( 'message_display_effect' ), 'rollIn' ) ?> value="rollIn"><?php esc_attr_e( 'rollIn', 'ecommerce-notification' ) ?></option>
									</optgroup>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'message_hidden_effect' ) ?>">
									<?php esc_html_e( 'Message hidden effect', 'ecommerce-notification' ) ?>
								</label>
							</th>
							<td>
								<select name="<?php echo self::set_field( 'message_hidden_effect' ) ?>" class="vi-ui fluid dropdown">
									<optgroup label="Bouncing Exits">
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'bounceOut' ) ?> value="bounceOut"><?php esc_attr_e( 'bounceOut', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'bounceOutDown' ) ?> value="bounceOutDown"><?php esc_attr_e( 'bounceOutDown', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'bounceOutLeft' ) ?> value="bounceOutLeft"><?php esc_attr_e( 'bounceOutLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'bounceOutRight' ) ?> value="bounceOutRight"><?php esc_attr_e( 'bounceOutRight', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'bounceOutUp' ) ?> value="bounceOutUp"><?php esc_attr_e( 'bounceOutUp', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Fading Exits">
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'fade-out' ) ?> value="fade-out"><?php esc_attr_e( 'fadeOut', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'fadeOutDown' ) ?> value="fadeOutDown"><?php esc_attr_e( 'fadeOutDown', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'fadeOutDownBig' ) ?> value="fadeOutDownBig"><?php esc_attr_e( 'fadeOutDownBig', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'fadeOutLeft' ) ?> value="fadeOutLeft"><?php esc_attr_e( 'fadeOutLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'fadeOutLeftBig' ) ?> value="fadeOutLeftBig"><?php esc_attr_e( 'fadeOutLeftBig', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'fadeOutRight' ) ?> value="fadeOutRight"><?php esc_attr_e( 'fadeOutRight', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'fadeOutRightBig' ) ?> value="fadeOutRightBig"><?php esc_attr_e( 'fadeOutRightBig', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'fadeOutUp' ) ?> value="fadeOutUp"><?php esc_attr_e( 'fadeOutUp', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'fadeOutUpBig' ) ?> value="fadeOutUpBig"><?php esc_attr_e( 'fadeOutUpBig', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Flippers">
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'flipOutX' ) ?> value="flipOutX"><?php esc_attr_e( 'flipOutX', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'flipOutY' ) ?> value="flipOutY"><?php esc_attr_e( 'flipOutY', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Lightspeed">
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'lightSpeedOut' ) ?> value="lightSpeedOut"><?php esc_attr_e( 'lightSpeedOut', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Rotating Exits">
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'rotateOut' ) ?> value="rotateOut"><?php esc_attr_e( 'rotateOut', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'rotateOutDownLeft' ) ?> value="rotateOutDownLeft"><?php esc_attr_e( 'rotateOutDownLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'rotateOutDownRight' ) ?> value="rotateOutDownRight"><?php esc_attr_e( 'rotateOutDownRight', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'rotateOutUpLeft' ) ?> value="rotateOutUpLeft"><?php esc_attr_e( 'rotateOutUpLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'rotateOutUpRight' ) ?> value="rotateOutUpRight"><?php esc_attr_e( 'rotateOutUpRight', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Sliding Exits">
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'slideOutUp' ) ?> value="slideOutUp"><?php esc_attr_e( 'slideOutUp', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'slideOutDown' ) ?> value="slideOutDown"><?php esc_attr_e( 'slideOutDown', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'slideOutLeft' ) ?> value="slideOutLeft"><?php esc_attr_e( 'slideOutLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'slideOutRight' ) ?> value="slideOutRight"><?php esc_attr_e( 'slideOutRight', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Zoom Exits">
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'zoomOut' ) ?> value="zoomOut"><?php esc_attr_e( 'zoomOut', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'zoomOutDown' ) ?> value="zoomOutDown"><?php esc_attr_e( 'zoomOutDown', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'zoomOutLeft' ) ?> value="zoomOutLeft"><?php esc_attr_e( 'zoomOutLeft', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'zoomOutRight' ) ?> value="zoomOutRight"><?php esc_attr_e( 'zoomOutRight', 'ecommerce-notification' ) ?></option>
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'zoomOutUp' ) ?> value="zoomOutUp"><?php esc_attr_e( 'zoomOutUp', 'ecommerce-notification' ) ?></option>
									</optgroup>
									<optgroup label="Special">
										<option <?php selected( self::get_field( 'message_hidden_effect' ), 'rollOut' ) ?> value="rollOut"><?php esc_attr_e( 'rollOut', 'ecommerce-notification' ) ?></option>
									</optgroup>
								</select>
							</td>
						</tr>
						</tbody>
					</table>
					<?php
					$class = array();
					switch ( self::get_field( 'position' ) ) {
						case 1:
							$class[] = 'bottom_right';
							break;
						case 2:
							$class[] = 'top_left';
							break;
						case 3:
							$class[] = 'top_right';
							break;
						default:
							$class[] = '';
					}
					$class[] = self::get_field( 'image_position' ) ? 'img-right' : '';
					?>
					<div style="display: block;" class="customized  <?php echo esc_attr( implode( ' ', $class ) ) ?>" id="message-purchased"
						 data-effect_display="<?php echo esc_attr( self::get_field( 'message_display_effect' ) ); ?>"
						 data-effect_hidden="<?php echo esc_attr( self::get_field( 'message_hidden_effect' ) ); ?>">
						<img src="<?php echo esc_url( ECOMMERCE_NOTIFICATION_IMAGES . 'demo-image.jpg' ) ?>">

						<p>Joe Doe in London, England purchased a
							<a href="#">Ninja Silhouette</a>
							<small>About 9 hours ago</small>
						</p>
						<span id="notify-close"></span>

					</div>
				</div>
				<!-- Time !-->
				<div class="vi-ui bottom attached tab segment" data-tab="time">
					<!-- Tab Content !-->
					<table class="optiontable form-table">
						<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'loop' ) ?>"><?php esc_html_e( 'Loop', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php echo self::set_field( 'loop' ) ?>" type="checkbox" <?php checked( self::get_field( 'loop' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'loop' ) ?>" />
									<label></label>
								</div>
							</td>
						</tr>
						<tr valign="top" class="hidden time_loop">
							<th scope="row">
								<label><?php esc_html_e( 'Next time display', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui form">
									<div class="inline fields">
										<input type="number" name="<?php echo self::set_field( 'next_time' ) ?>" value="<?php echo self::get_field( 'next_time', 60 ) ?>" />
										<label><?php esc_html_e( 'seconds', 'ecommerce-notification' ) ?></label>
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'Time to show next notification ', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top" class="hidden time_loop">
							<th scope="row">
								<label><?php esc_html_e( 'Notification per page', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<input type="number" name="<?php echo self::set_field( 'notification_per_page' ) ?>" value="<?php echo self::get_field( 'notification_per_page', 30 ) ?>" />

								<p class="description"><?php esc_html_e( 'Number of notifications on a page.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'initial_delay_random' ) ?>"><?php esc_html_e( 'Initial time random', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php echo self::set_field( 'initial_delay_random' ) ?>" type="checkbox" <?php checked( self::get_field( 'initial_delay_random' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'initial_delay_random' ) ?>" />
									<label></label>
								</div>
								<p class="description"><?php esc_html_e( 'Initial time will be random from 0 to current vaule.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top" class="hidden initial_delay_random">
							<th scope="row">
								<label><?php esc_html_e( 'Minimum initial delay time', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui form">
									<div class="inline fields">
										<input type="number" name="<?php echo self::set_field( 'initial_delay_min' ) ?>" value="<?php echo self::get_field( 'initial_delay_min', 0 ) ?>" />
										<label><?php esc_html_e( 'seconds', 'ecommerce-notification' ) ?></label>
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'Time will be random from Initial delay time min to Initial time.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Initial delay', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui form">
									<div class="inline fields">
										<input type="number" name="<?php echo self::set_field( 'initial_delay' ) ?>" value="<?php echo self::get_field( 'initial_delay', 0 ) ?>" />
										<label><?php esc_html_e( 'seconds', 'ecommerce-notification' ) ?></label>
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'When your site loads, notifications will show after this amount of time', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Display time', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui form">
									<div class="inline fields">
										<input type="number" name="<?php echo self::set_field( 'display_time' ) ?>" value="<?php echo self::get_field( 'display_time', 5 ) ?>" />
										<label><?php esc_html_e( 'seconds', 'ecommerce-notification' ) ?></label>
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'Time your notification display.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<!-- Sound !-->
				<div class="vi-ui bottom attached tab segment" data-tab="sound">
					<!-- Tab Content !-->
					<table class="optiontable form-table">
						<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'sound_enable' ) ?>"><?php esc_html_e( 'Enable', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php echo self::set_field( 'sound_enable' ) ?>" type="checkbox" <?php checked( self::get_field( 'sound_enable' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'sound_enable' ) ?>" />
									<label></label>
								</div>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Sound', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<?php
								$sounds = self::scan_dir( ECOMMERCE_NOTIFICATION_SOUNDS );
								?>
								<select name="<?php echo self::set_field( 'sound' ) ?>" class="vi-ui fluid dropdown">
									<?php foreach ( $sounds as $sound ) { ?>
										<option <?php selected( self::get_field( 'sound', 'cool' ), $sound ) ?> value="<?php echo esc_attr( $sound ) ?>"><?php echo esc_html( $sound ) ?></option>
									<?php } ?>
								</select>

								<p class="description"><?php echo esc_html__( 'Please select sound. Notification rings when show.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<!-- Short code !-->
				<div class="vi-ui bottom attached tab segment" data-tab="short-code">
					<!-- Tab Content !-->
					<table class="optiontable form-table">
						<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'custom_shortcode' ) ?>"><?php esc_html_e( 'Custom', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<input id="<?php echo self::set_field( 'custom_shortcode' ) ?>" type="text" tabindex="0" value="<?php echo self::get_field( 'custom_shortcode', esc_attr( '{number} people seeing this product right now' ) ) ?>" name="<?php echo self::set_field( 'custom_shortcode' ) ?>" />

								<p class="description"><?php esc_html_e( 'This is {custom} shortcode content.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'min_number' ) ?>"><?php esc_html_e( 'Min Number', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<input id="<?php echo self::set_field( 'min_number' ) ?>" type="number" tabindex="0" value="<?php echo self::get_field( 'min_number', 100 ) ?>" name="<?php echo self::set_field( 'min_number' ) ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'max_number' ) ?>"><?php esc_html_e( 'Max number', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<input id="<?php echo self::set_field( 'max_number' ) ?>" type="number" tabindex="0" value="<?php echo self::get_field( 'max_number', 200 ) ?>" name="<?php echo self::set_field( 'max_number' ) ?>" />

								<p class="description"><?php esc_html_e( 'Number will random from Min number to Max number', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<!-- Assign !-->
				<div class="vi-ui bottom attached tab segment" data-tab="assign">
					<!-- Tab Content !-->
					<table class="optiontable form-table">
						<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'is_home' ) ?>"><?php esc_html_e( 'Home page', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php echo self::set_field( 'is_home' ) ?>" type="checkbox" <?php checked( self::get_field( 'is_home' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'is_home' ) ?>" />
									<label></label>
								</div>
								<p class="description"><?php esc_html_e( 'Turn on is hidden notification on Home page', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Conditional Tags', 'ecommerce-notification' ) ?>
							</th>
							<td>
								<input placeholder="<?php esc_html_e( 'eg: !is_page(34,98,73)', 'ecommerce-notification' ) ?>" type="text" value="<?php echo htmlentities( self::get_field( 'conditional_tags' ) ) ?>" name="<?php echo self::set_field( 'conditional_tags' ) ?>" />

								<p class="description"><?php esc_html_e( 'Let you adjust which pages will appear using WP\'s conditional tags.', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<!-- Logs !-->
				<div class="vi-ui bottom attached tab segment" data-tab="logs">
					<!-- Tab Content !-->
					<table class="optiontable form-table">
						<tbody>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo self::set_field( 'save_logs' ) ?>"><?php esc_html_e( 'Save Logs', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui toggle checkbox">
									<input id="<?php echo self::set_field( 'save_logs' ) ?>" type="checkbox" <?php checked( self::get_field( 'save_logs' ), 1 ) ?> tabindex="0" class="hidden" value="1" name="<?php echo self::set_field( 'save_logs' ) ?>" />
									<label></label>
								</div>
							</td>
						</tr>
						<tr valign="top" class="hidden save_logs">
							<th scope="row">
								<label><?php esc_html_e( 'History time', 'ecommerce-notification' ) ?></label>
							</th>
							<td>
								<div class="vi-ui form">
									<div class="inline fields">
										<input type="text" name="<?php echo self::set_field( 'history_time' ) ?>" value="<?php echo self::get_field( 'history_time', 30 ) ?>" />
										<label><?php esc_html_e( 'days', 'ecommerce-notification' ) ?></label>
									</div>
								</div>
								<p class="description"><?php echo esc_html__( 'Logs will be saved at ', 'ecommerce-notification' ) . ECOMMERCE_NOTIFICATION_CACHE . esc_html__( ' in time', 'ecommerce-notification' ) ?></p>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<!-- Logs !-->
				<div class="vi-ui bottom attached tab segment" data-tab="update">
					<!-- Tab Content !-->
					<table class="optiontable form-table">
						<tbody>

						<tr valign="top">
							<th scope="row">
								<label><?php esc_html_e( 'Purchased code', 'ecommerce-notification' ) ?></label>
							</th>
							<td>

								<input type="text" name="<?php echo self::set_field( 'key' ) ?>" value="<?php echo self::get_field( 'key' ) ?>" />

							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<p class="villa-enotify-btn-save">
					<input type="submit" class="button button-primary" value=" <?php esc_html_e( 'Save', 'ecommerce-notification' ) ?> " />
				</p>
			</form>
		</div>
	<?php }
} ?>