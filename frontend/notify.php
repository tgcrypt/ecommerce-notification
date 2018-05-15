<?php

/**
 * Class ECOMMERCE_NOTIFICATION_Frontend_Notify
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ECOMMERCE_NOTIFICATION_Frontend_Notify {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'init_scripts' ) );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );

		add_action( 'wp_ajax_nopriv_woonotification_get_product', array( $this, 'product_html' ) );
		add_action( 'wp_ajax_woonotification_get_product', array( $this, 'product_html' ) );
	}

	/**
	 * Show HTML on front end
	 */
	public function product_html() {
		$params = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		$enable = $params->get_field( 'enable' );
		if ( $enable ) {
			echo $this->show_product( true );
		}

		die;
	}

	/**
	 * Detect IP
	 */
	public function init() {
		$params         = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		$detect_country = $params->get_field( 'country' );
		$detect         = isset( $_COOKIE['ip'] ) ? 1 : 0;
		if ( ! $detect_country && ! $detect ) {
			$data            = $this->geoCheckIP( $this->getIP() );
			$data['country'] = isset( $data['country'] ) ? $data['country'] : 'United States';
			$data['city']    = isset( $data['city'] ) ? $data['city'] : 'New York City';

			setcookie( 'ip', 1, time() + 7 * 60 * 60 * 24, '/' );
			setcookie( 'country', $data['country'], time() + 7 * 60 * 60 * 24, '/' );
			setcookie( 'city', $data['city'], time() + 7 * 60 * 60 * 24, '/' );
		}
		/*Make cache folder*/
		if ( ! is_dir( ECOMMERCE_NOTIFICATION_CACHE ) ) {
			mkdir( ECOMMERCE_NOTIFICATION_CACHE, '0755', true );
			chmod( ECOMMERCE_NOTIFICATION_CACHE, 0755 );
			file_put_contents(
				ECOMMERCE_NOTIFICATION_CACHE . '.htaccess', '<IfModule !mod_authz_core.c>
Order deny,allow
Deny from all
</IfModule>
<IfModule mod_authz_core.c>
  <RequireAll>
    Require all denied
  </RequireAll>
</IfModule>
'
			);
		}

	}


	/**
	 * Show HTML code
	 */
	public function wp_footer() {
		$params        = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		$enable        = $params->get_field( 'enable' );
		$logic_value   = $params->get_field( 'conditional_tags' );
		$sound_enable  = $params->get_field( 'sound_enable' );
		$sound         = $params->get_field( 'sound' );
		$enable_mobile = $params->get_field( 'enable_mobile' );
		$is_home       = $params->get_field( 'is_home' );
		$is_checkout   = $params->get_field( 'is_checkout' );
		$is_cart       = $params->get_field( 'is_cart' );
		// Include and instantiate the class.
		$detect = new VillaTheme_Mobile_Detect;

		// Any mobile device (phones or tablets).
		if ( $detect->isMobile() ) {
			if ( ! $enable_mobile ) {
				return false;
			}
		}
		/*Assign page*/
		if ( $is_home && is_home() ) {
			return;
		}
		if ( $is_checkout && is_checkout() ) {
			return;
		}
		if ( $is_cart && is_cart() ) {
			return;
		}
		if ( $logic_value ) {
			if ( stristr( $logic_value, "return" ) === false ) {
				$logic_value = "return (" . $logic_value . ");";
			}
			if ( ! eval( $logic_value ) ) {
				return;
			}
		}
		if ( $enable ) {
			echo $this->show_product();
		}

		if ( $sound_enable ) { ?>
			<audio id="ecommerce-notification-audio">
				<source src="<?php echo esc_url( ECOMMERCE_NOTIFICATION_SOUNDS_URL . $sound ) ?>">
			</audio>
		<?php }
	}

	/**
	 * Add Script and Style
	 */
	function init_scripts() {
		$params      = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		$is_home     = $params->get_field( 'is_home' );
		$is_checkout = $params->get_field( 'is_checkout' );
		$is_cart     = $params->get_field( 'is_cart' );
		/*Conditional tags*/
		$logic_value = $params->get_field( 'conditional_tags' );
		/*Assign page*/
		if ( $is_home && is_home() ) {
			return;
		}
		if ( $is_checkout && is_checkout() ) {
			return;
		}
		if ( $is_cart && is_cart() ) {
			return;
		}
		if ( $logic_value ) {
			if ( stristr( $logic_value, "return" ) === false ) {
				$logic_value = "return (" . $logic_value . ");";
			}
			if ( ! eval( $logic_value ) ) {
				return;
			}
		}

		wp_enqueue_style( 'ecommerce-notification', ECOMMERCE_NOTIFICATION_CSS . 'ecommerce-notification.css', array(), ECOMMERCE_NOTIFICATION_VERSION );

		wp_enqueue_script( 'ecommerce-notification', ECOMMERCE_NOTIFICATION_JS . 'ecommerce-notification.js', array( 'jquery' ), ECOMMERCE_NOTIFICATION_VERSION );

		/*Custom*/

		$highlight_color  = $params->get_field( 'highlight_color' );
		$text_color       = $params->get_field( 'text_color' );
		$background_color = $params->get_field( 'background_color' );
		$custom_css       = "
                #message-purchased{
                        background-color: {$background_color} !important;
                        color:{$text_color} !important;
                }
                 #message-purchased a{
                        color:{$highlight_color} !important;
                }
                ";

		wp_add_inline_style( 'ecommerce-notification', $custom_css );

		/*Add ajax url*/
		/*Custom*/
		$script = 'var ecommerce_notification_ajax_url = "' . admin_url( 'admin-ajax.php' ) . '"';
		wp_add_inline_script( 'ecommerce-notification', $script );
	}

	/**
	 * Show product
	 *
	 * @param $product_id Product ID
	 *
	 */
	protected function show_product( $fisrt = false ) {
		$params                 = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		$image_position         = $params->get_field( 'image_position' );
		$position               = $params->get_field( 'position' );
		$loop                   = $params->get_field( 'loop' );
		$initial_delay          = $params->get_field( 'initial_delay' );
		$message_display_effect = $params->get_field( 'message_display_effect' );
		$message_hidden_effect  = $params->get_field( 'message_hidden_effect' );
		$initial_delay_random   = $params->get_field( 'initial_delay_random' );
		if ( $initial_delay_random ) {
			$initial_delay_min = $params->get_field( 'initial_delay_min' );
			$initial_delay     = rand( $initial_delay_min, $initial_delay );
		}
		$notification_per_page = $params->get_field( 'notification_per_page' );
		$display_time          = $params->get_field( 'display_time' );
		$next_time             = $params->get_field( 'next_time' );
		$class                 = array();
		$class[]               = $image_position ? 'img-right' : '';
		$enable_single_product = $params->get_field( 'enable_single_product' );
		$post_id               = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $enable_single_product && is_single() ) {
			global $post;
			$product_id_str = 'data-product="' . esc_attr( $post->ID ) . '">';
		} elseif ( $post_id ) {
			$product_id_str = 'data-product="' . esc_attr( $post_id ) . '">';
		} else {
			$product_id_str = '';
		}

		switch ( $position ) {
			case  1:
				$class[] = 'bottom_right';
				break;
			case  2:
				$class[] = 'top_left';
				break;
			case  3:
				$class[] = 'top_right';
				break;
		}
		ob_start();
		?>
		<div id="message-purchased" class="customized <?php echo implode( ' ', $class ) ?>" style="display: none;"
		     data-loop="<?php echo esc_attr( $loop ) ?>"
		     data-initial_delay="<?php echo esc_attr( $initial_delay ) ?>"
		     data-notification_per_page="<?php echo esc_attr( $notification_per_page ) ?>"
		     data-display_time="<?php echo esc_attr( $display_time ) ?>"
		     data-next_time="<?php echo esc_attr( $next_time ) ?>"
		     data-display_effect="<?php echo empty( $message_display_effect ) ? esc_attr( 'fade-in' ) : esc_attr( $message_display_effect ); ?>"
		     data-hidden_effect="<?php echo empty( $message_hidden_effect ) ? esc_attr( 'fade-out' ) : esc_attr( $message_hidden_effect ); ?>"
			<?php echo $product_id_str ?>>

			<?php if ( $fisrt ) {
				echo $this->message_purchased();
			} ?>

		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Message purchased
	 *
	 * @param $product_id
	 */
	protected function message_purchased() {
		$params            = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		$message_purchased = $params->get_field( 'message_purchased' );
		$show_close_icon   = $params->get_field( 'show_close_icon' );

		$messsage = '';
		$keys     = array(
			'{first_name}',
			'{city}',
			'{country}',
			'{product}',
			'{product_with_link}',
			'{time_ago}',
			'{custom}'
		);


		$item = $this->get_item();

		if ( $item ) {
			$item_id = $item['id'];
		} else {
			return false;
		}

		$first_name = trim( $item['first_name'] );
		$city       = trim( $item['city'] );
		$country    = trim( $item['country'] );
		$time       = trim( $item['time'] );

		$item = esc_html( get_the_title( $item_id ) );

		// do stuff for everything else
		$link = get_permalink( $item_id );
		$link = wp_nonce_url( $link, 'wocommerce_notification_click', 'link' );

		ob_start(); ?>
		<a href="<?php echo esc_url( $link ) ?>"><?php echo esc_html( get_the_title( $item_id ) ) ?></a>
		<?php $product_with_link = ob_get_clean();
		ob_start(); ?>
		<small><?php echo esc_html__( 'About', 'ecommerce-notification' ) . ' ' . esc_html( $time ) . ' ' . esc_html__( 'ago', 'ecommerce-notification' ) ?></small>
		<?php $time_ago = ob_get_clean();
		$product_thumb  = $params->get_field( 'product_sizes', 'thumbnail' );
		if ( has_post_thumbnail( $item_id ) ) {
			$messsage .= '<img src="' . esc_url( get_the_post_thumbnail_url( $item_id, $product_thumb ) ) . '" class="wcn-product-image"/>';
		}


		//Get custom shortcode
		$custom_shortcode = $this->get_custom_shortcode();
		$replaced         = array(
			$first_name,
			$city,
			$country,
			$item,
			$product_with_link,
			$time_ago,
			$custom_shortcode
		);
		$messsage .= str_replace( $keys, $replaced, '<p>' . strip_tags( $message_purchased ) . '</p>' );
		ob_start();
		if ( $show_close_icon ) {
			?>
			<span id="notify-close"></span>
			<?php
		}
		$messsage .= ob_get_clean();

		return $messsage;
	}

	/**
	 *
	 * @return mixed
	 */
	protected function get_custom_shortcode() {
		$params            = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		$message_shortcode = $params->get_field( 'custom_shortcode' );
		$min_number        = $params->get_field( 'min_number', 0 );
		$max_number        = $params->get_field( 'max_number', 0 );

		$number  = rand( $min_number, $max_number );
		$message = preg_replace( '/\{number\}/i', $number, $message_shortcode );

		return $message;
	}

	/**
	 * Process product
	 * @return bool
	 */
	protected function get_item() {

		$params = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		/*Single page*/
		$enable_single_product = $params->get_field( 'enable_single_product' );
		$item_id               = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( $enable_single_product && $item_id ) {

			$item = get_post( $item_id );
			$p_id = $item->ID;

			$data = array(
				'id'         => $p_id,
				'time'       => '',
				'first_name' => '',
				'city'       => '',
				'country'    => ''
			);

			$virtual_name   = $params->get_field( 'virtual_name' );
			$virtual_time   = $params->get_field( 'virtual_time' );
			$detect_country = $params->get_field( 'country' );

			if ( $virtual_name ) {
				$virtual_name = explode( "\n", $virtual_name );
				$virtual_name = array_filter( $virtual_name );
			}

			if ( ! $detect_country ) {
				$detect_data = $this->detect_country();

				$country = isset( $detect_data['country'] ) ? $detect_data['country'] : '';
				$city    = isset( $detect_data['city'] ) ? $detect_data['city'] : '';
			} else {
				$country = $params->get_field( 'virtual_country' );
				$city    = $params->get_field( 'virtual_city' );
				if ( $city ) {
					$city = explode( "\n", $city );
					$city = array_filter( $city );
				}
			}


			if ( is_array( $city ) ) {
				$index     = array_rand( $city );
				$city_text = $city[ $index ];
			} else {
				$city_text = $city;
			}

			if ( is_array( $virtual_name ) ) {
				$index             = array_rand( $virtual_name );
				$virtual_name_text = $virtual_name[ $index ];
			} else {
				$virtual_name_text = $virtual_name;
			}


			$data['time']       = $this->time_substract( current_time( 'timestamp' ) - rand( 10, $virtual_time * 3600 ), true );
			$data['first_name'] = $virtual_name_text;
			$data['city']       = $city_text;
			$data['country']    = $country;

			return $data;
		}

		$prefix = ecommerce_notification_prefix();
		/*Process cache*/
		$cache = get_transient( $prefix );
		if ( ! is_array( get_transient( $prefix ) ) ) {
			$cache = array();
		}
		$data_cache = array_filter( $cache );
		$sec_datas  = count( $data_cache ) ? $data_cache : array();
		if ( count( $sec_datas ) ) {
			/*Process data with product up sell*/
			$index = rand( 0, count( $sec_datas ) - 1 );
			$data  = $sec_datas[ $index ];
			if ( ! $params->get_field( 'cache_enable' ) ) {
				$virtual_time = $params->get_field( 'virtual_time' );
				$data['time'] = $this->time_substract( current_time( 'timestamp' ) - rand( 10, $virtual_time * 3600 ), true );
				/*Change virtual name*/
				$virtual_name = $params->get_field( 'virtual_name' );
				if ( $virtual_name ) {
					$virtual_name       = explode( "\n", $virtual_name );
					$virtual_name       = array_filter( $virtual_name );
					$index              = array_rand( $virtual_name );
					$virtual_name_text  = $virtual_name[ $index ];
					$data['first_name'] = $virtual_name_text;
				}
				/*Change city*/
				if ( $params->get_field( 'country' ) ) {
					/*Change city*/
					$city = $params->get_field( 'virtual_city' );
					if ( $city ) {
						$city         = explode( "\n", $city );
						$city         = array_filter( $city );
						$index        = array_rand( $city );
						$city_text    = $city[ $index ];
						$data['city'] = $city_text;
					}
				} else {
					$detect_data     = $this->detect_country();
					$data['city']    = $detect_data['city'];
					$data['country'] = $detect_data['country'];
				}
			}

			return $data;


		}
		/*Params from Settings*/
		$archive_products = $params->get_field( 'archive_products' );
		$post_type        = $params->get_field( 'post_type' );
		$virtual_name     = $params->get_field( 'virtual_name' );
		$virtual_time     = $params->get_field( 'virtual_time' );
		$detect_country   = $params->get_field( 'country' );

		if ( $virtual_name ) {
			$virtual_name = explode( "\n", $virtual_name );
			$virtual_name = array_filter( $virtual_name );
		}

		if ( ! $detect_country ) {
			$detect_data = $this->detect_country();

			$country = isset( $detect_data['country'] ) ? $detect_data['country'] : '';
			$city    = isset( $detect_data['city'] ) ? $detect_data['city'] : '';
		} else {
			$country = $params->get_field( 'virtual_country' );
			$city    = $params->get_field( 'virtual_city' );
			if ( $city ) {
				$city = explode( "\n", $city );
				$city = array_filter( $city );
			}
		}
		$archive_products = is_array( $archive_products ) ? $archive_products : array();

		if ( count( array_filter( $archive_products ) ) < 1 ) {
			$args      = array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC'
			);
			$the_query = new WP_Query( $args );
			if ( $the_query->have_posts() ) {
				$archive_products = array();
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					$archive_products[] = get_the_ID();
				}
				// Reset Post Data
				wp_reset_postdata();
			}

		}
		$products = array();
		if ( count( $archive_products ) ) {
			foreach ( $archive_products as $archive_product ) {
				if ( is_array( $city ) ) {
					$index     = array_rand( $city );
					$city_text = $city[ $index ];
				} else {
					$city_text = $city;
				}

				if ( is_array( $virtual_name ) ) {
					$index             = array_rand( $virtual_name );
					$virtual_name_text = $virtual_name[ $index ];
				} else {
					$virtual_name_text = $virtual_name;
				}


				$product['id']         = $archive_product;
				$product['time']       = $this->time_substract( current_time( 'timestamp' ) - rand( 10, $virtual_time * 3600 ), true );
				$product['first_name'] = $virtual_name_text;
				$product['city']       = $city_text;
				$product['country']    = $country;
				$products[]            = $product;
			}
		}

		if ( count( $products ) ) {
			$index = rand( 0, count( $products ) - 1 );
			$data  = $products[ $index ];
			set_transient( $prefix, $products, 3600 );
		} else {
			return false;
		}

		return $data;

	}

	/**
	 * Detect country and city
	 *
	 * @return array
	 */
	protected function detect_country() {
		$ip = isset( $_COOKIE['ip'] ) ? $_COOKIE['ip'] : '';
		if ( $ip || isset( $_COOKIE['ip'] ) ) {
			$data['city'] = isset( $_COOKIE['city'] ) ? $_COOKIE['city'] : '';
			if ( ! $data['city'] && isset( $_COOKIE['city'] ) ) {
				$data['city'] = $_COOKIE['city'];
			}
			$data['country'] = isset( $_COOKIE['country'] ) ? $_COOKIE['country'] : '';
			if ( ! $data['country'] && isset( $_COOKIE['country'] ) ) {
				$data['country'] = $_COOKIE['country'];
			}
		} else {
			$ip = $this->getIP();
			if ( $ip ) {
				$data = $this->geoCheckIP( $ip );
			} else {
				$data = array();
			}
		}

		return $data;
	}

	/**
	 * Get ip of client
	 *
	 * @return mixed ip of client
	 */
	protected function getIP() {
		if ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$ipaddress = getenv( 'HTTP_CLIENT_IP' );
		} else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
		} else if ( getenv( 'HTTP_X_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED' );
		} else if ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
		} else if ( getenv( 'HTTP_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED' );
		} else if ( getenv( 'REMOTE_ADDR' ) ) {
			$ipaddress = getenv( 'REMOTE_ADDR' );
		} else {
			$ipaddress = 0;
		}

		return $ipaddress;
	}

	/**
	 * Get time
	 *
	 * @param      $time
	 * @param bool $number
	 * @param bool $calculate
	 *
	 * @return bool|string
	 */
	protected function time_substract( $time, $number = false, $calculate = false ) {
		if ( ! $number ) {
			if ( $time ) {
				$time = strtotime( $time );
			} else {
				return false;
			}
		}
		if ( ! $calculate ) {
			$current_time   = current_time( 'timestamp' );
			$time_substract = $current_time - $time;
		} else {
			$time_substract = $time;
		}
		//return $time_substract;

		if ( $time_substract > 0 ) {

			/*Check day*/
			$day = $time_substract / ( 24 * 3600 );
			$day = intval( $day );
			if ( $day > 1 ) {
				return $day . ' ' . esc_html__( 'days', 'ecommerce-notification' );
			} elseif ( $day > 0 ) {
				return $day . ' ' . esc_html__( 'day', 'ecommerce-notification' );
			}

			/*Check hour*/
			$hour = $time_substract / ( 3600 );
			$hour = intval( $hour );
			if ( $hour > 1 ) {
				return $hour . ' ' . esc_html__( 'hours', 'ecommerce-notification' );
			} elseif ( $hour > 0 ) {
				return $hour . ' ' . esc_html__( 'hour', 'ecommerce-notification' );
			}

			/*Check min*/
			$min = $time_substract / ( 60 );
			$min = intval( $min );
			if ( $min > 1 ) {
				return $min . ' ' . esc_html__( 'minutes', 'ecommerce-notification' );
			} elseif ( $min > 0 ) {
				return $min . ' ' . esc_html__( 'minute', 'ecommerce-notification' );
			}

			return intval( $time_substract ) . ' ' . esc_html__( 'seconds', 'ecommerce-notification' );

		} else {
			return esc_html__( 'a few seconds', 'ecommerce-notification' );
		}


	}

	/**
	 * Get an array with geoip-infodata
	 *
	 * @param $ip
	 *
	 * @return bool
	 */
	protected function geoCheckIP( $ip ) {
		$params   = new ECOMMERCE_NOTIFICATION_Admin_Settings();
		$auth_key = $params->get_field( 'ipfind_auth_key' );
		if ( ! $auth_key ) {
			return false;
		}
		//check, if the provided ip is valid
		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			throw new InvalidArgumentException( "IP is not valid" );
		}

		//contact ip-server
		$response = @file_get_contents( 'https://ipfind.co?ip=' . $ip . '&auth=' . trim( $auth_key ) );
		file_put_contents( ECOMMERCE_NOTIFICATION_CACHE . 'ip.txt', "\n" . date( "H:i:s" ), FILE_APPEND );
		if ( empty( $response ) ) {
			return false;
			throw new InvalidArgumentException( "Error contacting Geo-IP-Server" );

		} else {
			$response = json_decode( $response );
		}

		$ipInfo["city"]    = $response->city;
		$ipInfo["country"] = $response->country;

		return $ipInfo;
	}

}