<?php
/**
 * LordCros Main Theme Functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Custom Body Classes */
if ( ! function_exists( 'lordcros_custom_body_classes' ) ) {
	function lordcros_custom_body_classes( $classes ) {
		$side_header_layouts = array( 'header-layout-4', 'header-layout-6', 'header-layout-10' );
		$current_header_layout = lordcros_get_opt( 'header_layout', 'header-layout-1' );

		if ( in_array( $current_header_layout, $side_header_layouts ) ) {
			$side_header_num = str_replace( 'header-layout-', '', $current_header_layout );
			$classes[] = 'left-menu-bar left-header-style-' . $side_header_num;
		}

		return $classes;
	}
}
add_filter( 'body_class','lordcros_custom_body_classes' );

/* Add WP admin custom CSS & JS */
if ( ! function_exists( 'lordcros_admin_style' ) ) {
	function lordcros_admin_style() {
		$theme_version = lordcros_theme_version();
		$suffix = WP_DEBUG ? '' : '.min';

		wp_enqueue_style( 'font-awesome', LORDCROS_URI . '/inc/fonts/font-awesome/css/all.min.css', NULL, '5.7.0', 'all' );
		wp_enqueue_style( 'jquery-ui', LORDCROS_URI . '/css/admin/jquery-ui.min.css', NULL, '1.12.1', 'all' );
		wp_enqueue_style( 'lordcros-admin-style', LORDCROS_URI . '/css/admin/admin' . $suffix . '.css', false, $theme_version, 'all' );
	}
}
add_action( 'admin_enqueue_scripts', 'lordcros_admin_style' );

if ( ! function_exists( 'lordcros_admin_script' ) ) {
	function lordcros_admin_script() {
		$theme_version = lordcros_theme_version();
		$suffix = WP_DEBUG ? '' : '.min';

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'lordcros-admin-script', LORDCROS_URI . '/js/admin' . $suffix . '.js', array( 'jquery' ), false );
	}
}
add_action( 'admin_enqueue_scripts', 'lordcros_admin_script' );

/* Add WP front-end custom CSS & JS */
if ( ! function_exists( 'lordcros_style' ) ) {
	function lordcros_style() {
		$theme_version = lordcros_theme_version();
		$suffix = WP_DEBUG ? '' : '.min';

		if ( ! class_exists( 'ReduxFramework' ) ) {
			wp_enqueue_style( 'lordcros-default-google-fonts', lordcros_default_google_fonts(), array(), $theme_version, 'all' );
		}

		wp_enqueue_style( 'jquery-ui', '//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css', NULL, '1.11.2', 'all' );
		wp_enqueue_style( 'lordcros-font-awesome', LORDCROS_URI . '/inc/fonts/font-awesome/css/all.min.css', NULL, '5.7.0', 'all' );
		wp_enqueue_style( 'lordcros-plugins', LORDCROS_URI . '/css/plugins.css', array(), $theme_version, 'all' );

		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			wp_enqueue_style( 'lordcros-theme-style', LORDCROS_URI . '/css/theme' . $blog_id . '.css', array(), $theme_version, 'all' );
		} else {
			wp_enqueue_style( 'lordcros-theme-style', LORDCROS_URI . '/css/theme.css', array(), $theme_version, 'all' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'lordcros_style' );

if ( ! function_exists( 'lordcros_script' ) ) {
	function lordcros_script() {
		$theme_version = lordcros_theme_version();
		$suffix = WP_DEBUG ? '' : '.min';
		$google_map_api_key = lordcros_get_opt( 'google_map_api_key', '' );

		wp_enqueue_script( 'jquery-ui' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'imagesloaded' );
		wp_enqueue_script( 'owl-carousel', LORDCROS_URI . '/js/owl.carousel' . $suffix . '.js', array( 'jquery' ), '2.3.4', true );
		wp_enqueue_script( 'popper', LORDCROS_URI . '/js/popper' . $suffix . '.js', array( 'jquery' ), '1.11.0', true );
		wp_enqueue_script( 'jquery-validate', LORDCROS_URI . '/js/jquery.validate' . $suffix . '.js', array( 'jquery' ), '1.19.0', true );
		wp_enqueue_script( 'jquery-cookie', LORDCROS_URI . '/js/jquery.cookie' . $suffix . '.js', array( 'jquery' ), '1.4.1', true );
		wp_enqueue_script( 'jquery-magnific', LORDCROS_URI . '/js/jquery.magnific-popup' . $suffix . '.js', array( 'jquery' ), '1.1.0', true );
		wp_enqueue_script( 'moment', LORDCROS_URI . '/js/moment' . $suffix . '.js', array( 'jquery' ), '2.24.0', true );
		wp_enqueue_script( 'moment-timezone-data', LORDCROS_URI . '/js/moment-timezone-with-data' . $suffix . '.js', array( 'jquery' ), '0.5.23', true );
		wp_enqueue_script( 'jquery-countdown', LORDCROS_URI . '/js/jquery.countdown' . $suffix . '.js', array('jquery', 'jquery-cookie'), '2.22.0', true );
		wp_enqueue_script( 'bootstrap', LORDCROS_URI . '/js/bootstrap' . $suffix . '.js', array( 'jquery' ), '4.0', true );
		wp_enqueue_script( 'isotope', LORDCROS_URI . '/js/isotope.pkgd' . $suffix . '.js', array( 'jquery' ), '3.0.6', true );
		wp_enqueue_script( 'google-maps', '//maps.googleapis.com/maps/api/js?key=' . $google_map_api_key , array( 'jquery' ), false, true );
		wp_enqueue_script( 'gmap3', LORDCROS_URI . '/js/gmap3.min.js', array( 'jquery' ), false, true );

		if ( is_singular() ) {
			wp_enqueue_script( 'comment-reply' );
		}

		if ( is_page_template( 'templates/template-checkout.php' ) && lordcros_get_opt( 'stripe_payment' ) ) {
			wp_enqueue_script( 'stripe', 'https://js.stripe.com/v2/', array( 'jquery' ), '', true );
		}
		wp_enqueue_script( 'lordcros-theme-scripts', LORDCROS_URI . '/js/theme-scripts' . $suffix . '.js', array( 'jquery' ), $theme_version, true );

		$ajax_nonce = wp_create_nonce( "lordcros-ajax" );
		wp_localize_script( 'lordcros-theme-scripts', 'js_lordcros_vars', array(
			'ajax_nonce'				=>	$ajax_nonce,
			'ajax_url'					=>	admin_url( 'admin-ajax.php' ),
			'stripe_publishable_key'	=>	lordcros_get_opt( 'stripe_publishable_key', 'none' ),
			'cookies_law_version'		=>	lordcros_get_opt( 'cookies_version', 1 ),
			'timer_days'				=>	esc_html__( 'Days', 'lordcros' ),
			'timer_hours'				=>	esc_html__( 'Hours', 'lordcros' ),
			'timer_mins'				=>	esc_html__( 'Minutes', 'lordcros' ),
			'timer_sec'					=>	esc_html__( 'Seconds', 'lordcros' ),
			'month_Jan'					=>	esc_html__( 'January', 'lordcros' ),
			'month_Feb'					=>	esc_html__( 'February', 'lordcros' ),
			'month_Mar'					=>	esc_html__( 'March', 'lordcros' ),
			'month_Apr'					=>	esc_html__( 'April', 'lordcros' ),
			'month_May'					=>	esc_html__( 'May', 'lordcros' ),
			'month_Jun'					=>	esc_html__( 'June', 'lordcros' ),
			'month_Jul'					=>	esc_html__( 'July', 'lordcros' ),
			'month_Aug'					=>	esc_html__( 'August', 'lordcros' ),
			'month_Sep'					=>	esc_html__( 'September', 'lordcros' ),
			'month_Oct'					=>	esc_html__( 'October', 'lordcros' ),
			'month_Nov'					=>	esc_html__( 'November', 'lordcros' ),
			'month_Dec'					=>	esc_html__( 'December', 'lordcros' ),
			'month_short_Jan'			=>	esc_html__( 'Jan', 'lordcros' ),
			'month_short_Feb'			=>	esc_html__( 'Feb', 'lordcros' ),
			'month_short_Mar'			=>	esc_html__( 'Mar', 'lordcros' ),
			'month_short_Apr'			=>	esc_html__( 'Apr', 'lordcros' ),
			'month_short_May'			=>	esc_html__( 'May', 'lordcros' ),
			'month_short_Jun'			=>	esc_html__( 'Jun', 'lordcros' ),
			'month_short_Jul'			=>	esc_html__( 'Jul', 'lordcros' ),
			'month_short_Aug'			=>	esc_html__( 'Aug', 'lordcros' ),
			'month_short_Sep'			=>	esc_html__( 'Sep', 'lordcros' ),
			'month_short_Oct'			=>	esc_html__( 'Oct', 'lordcros' ),
			'month_short_Nov'			=>	esc_html__( 'Nov', 'lordcros' ),
			'month_short_Dec'			=>	esc_html__( 'Dec', 'lordcros' ),
			'week_Sun'					=>	esc_html__( 'Sun', 'lordcros' ),
			'week_Mon'					=>	esc_html__( 'Mon', 'lordcros' ),
			'week_Tue'					=>	esc_html__( 'Tue', 'lordcros' ),
			'week_Wed'					=>	esc_html__( 'Wed', 'lordcros' ),
			'week_Thu'					=>	esc_html__( 'Thu', 'lordcros' ),
			'week_Fri'					=>	esc_html__( 'Fri', 'lordcros' ),
			'week_Sat'					=>	esc_html__( 'Sat', 'lordcros' ),
			'coupon_code_error_msg'		=>	esc_html__( 'Please enter correct coupon code!', 'lordcros' )
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'lordcros_script' );

/* Default google fonts */
if ( ! function_exists( 'lordcros_default_google_fonts' ) ) {
	function lordcros_default_google_fonts() {
		$fonts_url = '';
		$default_google_fonts = 'Roboto+Slab:100,300,400,700|Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i';

		$query_args = array(
			'family'	=>	$default_google_fonts
		);

		$fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css' );

		return esc_url_raw( $fonts_url );
	}
}

/* Page class generator */
if ( ! function_exists( 'lordcros_page_classes' ) ) {
	function lordcros_page_classes() {
		$page_classes = array();
		$page_classes[] = 'lordcros-page-content';

		$header_overlap = lordcros_get_opt( 'header_overlap' );
		$header_layout = lordcros_get_opt( 'header_layout', 'header-layout-1' );

		if ( $header_overlap && ! in_array( $header_layout, array( 'header-layout-10' ) ) ) {
			$page_classes[] = 'lordcros-header-overlap';
		}

		echo 'class="' . esc_attr( implode( ' ', $page_classes ) ) . '"';
	}
}

/* Header class generator */
if ( ! function_exists( 'lordcros_header_classes' ) ) {
	function lordcros_header_classes( $layout ) {
		$header_class = array();
		$header_class[] = 'lordcros-header';
		$header_class[] = $layout;

		$header_overlap = lordcros_get_opt( 'header_overlap' );
		$sticky_header = lordcros_get_opt( 'sticky_header_setting' );

		if ( $header_overlap && ! in_array( $layout, array( 'header-layout-10' ) ) ) {
			$header_class[] = 'lordcros-header-overlap';
		}

		if ( $sticky_header && ! in_array( $layout, array( 'header-layout-6', 'header-layout-10' ) ) ) {
			$header_class[] = 'sticky-header-enable';
		}

		echo 'class="' . esc_attr( implode( ' ', $header_class ) ) . '"';
	}
}

/* Sticky header class generator */
if ( ! function_exists( 'lordcros_sticky_header_classes' ) ) {
	function lordcros_sticky_header_classes( $layout ) {
		$sticky_header_class = array();
		$sticky_header_class[] = 'lordcros-sticky-header';
		$sticky_header_class[] = $layout;

		echo 'class="' . esc_attr( implode( ' ', $sticky_header_class ) ) . '"';
	}
}

/* Get currency symbol */
if ( ! function_exists( 'lordcros_get_currency_symbol' ) ) {
	function lordcros_get_currency_symbol() {
		return lordcros_get_opt( 'currency_symbol', '$' );
	}
}

add_filter( 'currency_symbol', 'lordcros_get_currency_symbol' );

/* Get currency cpde */
if ( ! function_exists( 'lordcros_get_currency_code' ) ) {
	function lordcros_get_currency_code() {
		return lordcros_get_opt( 'currency_code', 'USD' );
	}
}

/* price format */
if ( ! function_exists( 'lordcros_get_price_format' ) ) {
	function lordcros_get_price_format( $type = "" ) {

		$currency_pos = lordcros_get_opt( 'cs_pos', 'left' );
		$format = '%1$s%2$s';

		if ( 'special' == $type ) {
			switch ( $currency_pos ) {
				case 'right' :
					$format = '<span>%2$s<sup>%1$s</sup></span>';
					break;
				case 'left_space' :
					$format = '<span><sup>%1$s</sup>&nbsp;%2$s</span>';
					break;
				case 'right_space' :
					$format = '<span>%2$s&nbsp;<sup>%1$s</sup></span>';
					break;
				case 'left' :
				default:
					$format = '<span><sup>%1$s</sup>%2$s</span>';
					break;
			}
		} else {
			switch ( $currency_pos ) {
				case 'left' :
					$format = '%1$s%2$s';
					break;
				case 'right' :
					$format = '%2$s%1$s';
					break;
				case 'left_space' :
					$format = '%1$s&nbsp;%2$s';
					break;
				case 'right_space' :
					$format = '%2$s&nbsp;%1$s';
					break;
			}
		}

		return apply_filters( 'lordcros_price_format', $format, $currency_pos, $type );
	}
}

/* Get price */
if ( ! function_exists( 'lordcros_price' ) ) {
	function lordcros_price( $amount, $type = "" ) {

		$currency_symbol = lordcros_get_currency_symbol();
		$decimal_prec = lordcros_get_opt( 'decimal_prec', 2 );
		$decimal_sep = lordcros_get_opt( 'decimal_sep', '.' );
		$thousands_sep = lordcros_get_opt( 'thousands_sep', ',' );
		$price_label = number_format( $amount, $decimal_prec, $decimal_sep, $thousands_sep );
		$format = lordcros_get_price_format( $type );

		return sprintf( $format, $currency_symbol, $price_label );
	}
}

/* Get current page url */
if ( ! function_exists( 'lordcros_get_current_page_url' ) ) {
	function lordcros_get_current_page_url() {
		global $wp;
		return esc_url( home_url( add_query_arg( array(), $wp->request ) ) );
	}
}

/* Generate and show lordcros page heading */
if ( ! function_exists( 'lordcros_page_heading' ) ) {
	function lordcros_page_heading() {
		global $post;

		$page_id = lordcros_get_page_id();
		$banner_class = '';

		if ( is_404() ) {

			$show_page_heading = get_post_meta( $page_id, 'lordcros_show_page_heading', true );
			$title = esc_html__( 'PAGE NOT FOUND!', 'lordcros' );

			$header_images = get_post_meta( $page_id, 'lordcros_header_image', true );
			if ( ! empty( $header_images ) ) {
				if ( is_array( $header_images ) ) {
					$header_image = reset( $header_images );
				} else {
					$header_image = $header_images;
				}
				$header_image = wp_get_attachment_image_src( $header_image, 'lordcros-page-banner' );
			}

			if ( ! empty( $header_image ) ) {
				$banner_class .= 'banner-background-enable';
				$background_style = '.page-banner { background-image: url(' . esc_url( $header_image[0] ) . '); }';

				wp_register_style( 'lordcros-theme-inline-style', false );
				wp_enqueue_style( 'lordcros-theme-inline-style' );
				wp_add_inline_style( 'lordcros-theme-inline-style', $background_style );
			} else {
				$banner_class .= 'banner-background-disable';
			}
			?>

			<div class="page-banner <?php echo esc_attr( $banner_class ); ?>">
				<div class="container">
					<h1 class="entry-title"><?php echo '' . $title; ?></h1>
				</div>
			</div>

			<?php
			return;
		}

		if ( is_singular( 'room' ) ) {
			$title = get_the_title();

			$header_images = get_post_meta( $page_id, 'lordcros_header_image', true );
			if ( ! empty( $header_images ) ) {
				if ( is_array( $header_images ) ) {
					$header_image = reset( $header_images );
				} else {
					$header_image = $header_images;
				}
				$header_image = wp_get_attachment_image_src( $header_image, 'lordcros-page-banner' );
			}

			if ( ! empty( $header_image ) ) {
				$banner_class .= 'banner-background-enable';
				$background_style = '.page-banner { background-image: url(' . esc_url( $header_image[0] ) . '); }';

				wp_register_style( 'lordcros-theme-inline-style', false );
				wp_enqueue_style( 'lordcros-theme-inline-style' );
				wp_add_inline_style( 'lordcros-theme-inline-style', $background_style );
			} else {
				$banner_class .= 'banner-background-disable';
			}
			?>

			<div class="page-banner <?php echo esc_attr( $banner_class ); ?>">
				<div class="container">
					<h1 class="entry-title"><?php echo esc_html( $title ); ?></h1>
					<?php
					if ( lordcros_show_breadcrumbs() ) {
						lordcros_breadcrumbs();
					}
					?>
				</div>
			</div>

			<?php
			return;
		}

		if ( is_singular( 'service' ) ) {
			$title = get_the_title();

			$header_images = get_post_meta( $page_id, 'lordcros_header_image', true );
			if ( ! empty( $header_images ) ) {
				if ( is_array( $header_images ) ) {
					$header_image = reset( $header_images );
				} else {
					$header_image = $header_images;
				}
				$header_image = wp_get_attachment_image_src( $header_image, 'lordcros-page-banner' );
			}

			if ( ! empty( $header_image ) ) {
				$banner_class .= 'banner-background-enable';
				$background_style = '.page-banner { background-image: url(' . esc_url( $header_image[0] ) . '); }';

				wp_register_style( 'lordcros-theme-inline-style', false );
				wp_enqueue_style( 'lordcros-theme-inline-style' );
				wp_add_inline_style( 'lordcros-theme-inline-style', $background_style );
			} else {
				$banner_class .= 'banner-background-disable';
			}
			?>

			<div class="page-banner <?php echo esc_attr( $banner_class ); ?>">
				<div class="container">
					<h1 class="entry-title"><?php echo esc_html( $title ); ?></h1>
					<?php
					if ( lordcros_show_breadcrumbs() ) {
						lordcros_breadcrumbs();
					}
					?>
				</div>
			</div>

			<?php
			return;
		}

		if ( is_singular( 'page' ) ) {
			$show_page_heading = get_post_meta( $page_id, 'lordcros_show_page_heading', true );
			if ( ! empty( $show_page_heading ) && $show_page_heading == 'hide' ) {
				return;
			}

			$title = get_the_title();

			$header_images = get_post_meta( $page_id, 'lordcros_header_image', true );
			if ( ! empty( $header_images ) ) {
				if ( is_array( $header_images ) ) {
					$header_image = reset( $header_images );
				} else {
					$header_image = $header_images;
				}
				$header_image = wp_get_attachment_image_src( $header_image, 'lordcros-page-banner' );
			}

			if ( ! empty( $header_image ) ) {
				$banner_class .= 'banner-background-enable';
				$background_style = '.page-banner { background-image: url(' . esc_url( $header_image[0] ) . '); }';

				wp_register_style( 'lordcros-theme-inline-style', false );
				wp_enqueue_style( 'lordcros-theme-inline-style' );
				wp_add_inline_style( 'lordcros-theme-inline-style', $background_style );
			} else {
				$banner_class .= 'banner-background-disable';
			}
			?>

			<div class="page-banner <?php echo esc_attr( $banner_class ); ?>">
				<div class="container">
					<h1 class="entry-title"><?php echo esc_html( $title ); ?></h1>
					<?php
					if ( lordcros_show_breadcrumbs() ) {
						lordcros_breadcrumbs();
					}
					?>
				</div>
			</div>

			<?php
			return;
		}

		if ( is_archive( 'service' ) ) {
			$title = esc_html__( 'Hotel Services', 'lordcros' );

			$header_images = get_post_meta( $page_id, 'lordcros_header_image', true );
			if ( ! empty( $header_images ) ) {
				if ( is_array( $header_images ) ) {
					$header_image = reset( $header_images );
				} else {
					$header_image = $header_images;
				}
				$header_image = wp_get_attachment_image_src( $header_image, 'lordcros-page-banner' );
			}

			if ( ! empty( $header_image ) ) {
				$banner_class .= 'banner-background-enable';
				$background_style = '.page-banner { background-image: url(' . esc_url( $header_image[0] ) . '); }';

				wp_register_style( 'lordcros-theme-inline-style', false );
				wp_enqueue_style( 'lordcros-theme-inline-style' );
				wp_add_inline_style( 'lordcros-theme-inline-style', $background_style );
			} else {
				$banner_class .= 'banner-background-disable';
			}
			?>

			<div class="page-banner <?php echo esc_attr( $banner_class ); ?>">
				<div class="container">
					<h1 class="entry-title"><?php echo esc_html( $title ); ?></h1>
					<?php
					if ( lordcros_show_breadcrumbs() ) {
						lordcros_breadcrumbs();
					}
					?>
				</div>
			</div>

			<?php
			return;

		}

		if ( is_archive( 'room' ) ) {
			$page_id = lordcros_get_opt( 'room_search_page' );

			$show_page_heading = get_post_meta( $page_id, 'lordcros_show_page_heading', true );
			if ( ! empty( $show_page_heading ) && $show_page_heading == 'hide' ) {
				return;
			}

			$title = get_the_title( $page_id );

			$header_images = get_post_meta( $page_id, 'lordcros_header_image', true );
			if ( ! empty( $header_images ) ) {
				if ( is_array( $header_images ) ) {
					$header_image = reset( $header_images );
				} else {
					$header_image = $header_images;
				}
				$header_image = wp_get_attachment_image_src( $header_image, 'lordcros-page-banner' );
			}

			if ( ! empty( $header_image ) ) {
				$banner_class .= 'banner-background-enable';
				$background_style = '.page-banner { background-image: url(' . esc_url( $header_image[0] ) . '); }';

				wp_register_style( 'lordcros-theme-inline-style', false );
				wp_enqueue_style( 'lordcros-theme-inline-style' );
				wp_add_inline_style( 'lordcros-theme-inline-style', $background_style );
			} else {
				$banner_class .= 'banner-background-disable';
			}
			?>

			<div class="page-banner <?php echo esc_attr( $banner_class ); ?>">
				<div class="container">
					<h1 class="entry-title"><?php echo esc_html( $title ); ?></h1>
					<?php
					if ( lordcros_show_breadcrumbs() ) {
						lordcros_breadcrumbs();
					}
					?>
				</div>
			</div>

			<?php
			return;
		}

		// Heading for blog post and archives
		if ( is_singular( 'post' ) || lordcros_is_blog_archive() ) {

			$show_page_heading = get_post_meta( $page_id, 'lordcros_show_page_heading', true );
			if ( ! empty( $show_page_heading ) && $show_page_heading == 'hide' ) {
				return;
			}

			$title = ( ! empty( $page_id ) ) ? get_the_title( $page_id ) : esc_html__( 'Blog', 'lordcros' );

			if ( is_tag() ) {
				$title = esc_html__( 'Tag Archives: ', 'lordcros' )  . single_tag_title( '', false ) ;
			}

			if ( is_category() ) {
				$title = '<span>' . single_cat_title( '', false ) . '</span>';
			}

			if ( is_date() ) {
				if ( is_day() ) :
					$title = esc_html__( 'Daily Archives: ', 'lordcros' ) . get_the_date();
				elseif ( is_month() ) :
					$title = esc_html__( 'Monthly Archives: ', 'lordcros' ) . get_the_date( _x( 'F Y', 'monthly archives date format', 'lordcros' ) );
				elseif ( is_year() ) :
					$title = esc_html__( 'Yearly Archives: ', 'lordcros' ) . get_the_date( _x( 'Y', 'yearly archives date format', 'lordcros' ) );
				else :
					$title = esc_html__( 'Archives', 'lordcros' );
				endif;
			}

			if ( is_author() ) {
				/*
				 * Queue the first post, that way we know what author
				 * we're dealing with (if that is the case).
				 *
				 * We reset this later so we can run the loop
				 * properly with a call to rewind_posts().
				 */
				the_post();

				$title = esc_html__( 'Posts by ', 'lordcros' ) . '<span class="vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . get_the_author() . '</a></span>';

				/*
				 * Since we called the_post() above, we need to
				 * rewind the loop back to the beginning that way
				 * we can run the loop properly, in full.
				 */
				rewind_posts();
			}

			if ( is_search() ) {
				$title = esc_html__( 'Search Results for: ', 'lordcros' ) . get_search_query();
			}

			$header_images = get_post_meta( $page_id, 'lordcros_header_image', true );
			if ( ! empty( $header_images ) ) {
				if ( is_array( $header_images ) ) {
					$header_image = reset( $header_images );
				} else {
					$header_image = $header_images;
				}
				$header_image = wp_get_attachment_image_src( $header_image, 'lordcros-page-banner' );
			}

			if ( ! empty( $header_image ) ) {
				$banner_class .= 'banner-background-enable';
				$background_style = '.page-banner { background-image: url(' . esc_url( $header_image[0] ) . '); }';

				wp_register_style( 'lordcros-theme-inline-style', false );
				wp_enqueue_style( 'lordcros-theme-inline-style' );
				wp_add_inline_style( 'lordcros-theme-inline-style', $background_style );
			} else {
				$banner_class .= 'banner-background-disable';
			}
			?>

			<div class="page-banner <?php echo esc_attr( $banner_class ); ?>">
				<div class="container">
					<h1 class="entry-title"><?php echo '' . $title; ?></h1>
					<?php
					if ( lordcros_show_breadcrumbs() ) {
						lordcros_breadcrumbs();
					}
					?>
				</div>
			</div>

			<?php
			return;
		}
	}
}

/* Configurate footer layouts */
if ( ! function_exists( 'lordcros_footer_configuration' ) ) {
	function lordcros_footer_configuration( $layout = 'footer-column-1' ) {
		$layout_config = apply_filters( 'lordcros_footer_layout_config', array(
			'footer-column-1'		=>	array(
				'column'			=>	array(
					'col-md-12 col-sm-12'
				)
			),

			'footer-column-2'		=>	array(
				'column'			=>	array(
					'col-md-6 col-sm-6',
					'col-md-6 col-sm-6'
				)
			),

			'footer-column-3'		=>	array(
				'column'			=>	array(
					'col-md-4 col-sm-4',
					'col-md-4 col-sm-4',
					'col-md-4 col-sm-4'
				)
			),

			'footer-column-4'		=>	array(
				'column'			=>	array(
					'col-md-3 col-sm-6',
					'col-md-3 col-sm-6',
					'col-md-3 col-sm-6',
					'col-md-3 col-sm-6'
				)
			),

			'footer-narrow-column-4'	=>	array(
				'column'				=>	array(
					'col-md-4 col-sm-8 col-xs-12',
					'col-md-2 col-sm-4 col-xs-6',
					'col-md-2 col-sm-4 col-xs-6',
					'col-md-4 col-sm-8 col-xs-12'
				)
			),

			'footer-column-5'		=>	array(
				'column'			=>	array(
					'col-md-3 col-sm-6',
					'col-md-3 col-sm-6',
					'col-md-2 col-sm-4',
					'col-md-2 col-sm-4',
					'col-md-2 col-sm-4'
				)
			),

			'footer-column-6'	=>	array(
				'column'		=>	array(
					'col-md-2 col-sm-4',
					'col-md-2 col-sm-4',
					'col-md-2 col-sm-4',
					'col-md-2 col-sm-4',
					'col-md-2 col-sm-4',
					'col-md-2 col-sm-4'
				)
			)
		) );

		return ( isset( $layout_config[$layout] ) ) ? $layout_config[$layout] : array();
	}
}

/* Return CSS classes for page main content area */
if ( ! function_exists( 'lordcros_get_content_class' ) ) {
	function lordcros_get_content_class() {
		$content_class  = '';
		$cl = 'col-lg-';
		$sidebar_size = 4;
		$content_size = 8;

		$layout = lordcros_page_layout();

		if ( 'full-width' == $layout ) {
			$sidebar_size = 0;
			$content_size = 12;
		}

		$content_size  = 12 - $sidebar_size;
		$content_class = $cl . $content_size;

		if ( 'sidebar-left' == $layout ) {
			$content_class .= ' order-lg-2';
		}

		return $content_class;
	}
}

/* Get current page ID */
if ( ! function_exists( 'lordcros_get_page_id' ) ) {
	function lordcros_get_page_id() {
		global $post;

		$page_id = 0;
		$page_for_posts = get_option( 'page_for_posts' );

		if ( isset( $post->ID ) ) {
			$page_id = $post->ID;
		}

		if ( lordcros_is_blog_archive() || is_404() || is_archive( 'service' ) ) {
			$page_id = $page_for_posts;
		}

		return $page_id;
	}
}

/* Return bool by checking if current page is blog page */
if( ! function_exists( 'lordcros_is_blog_archive' ) ) {
	function lordcros_is_blog_archive() {
		return ( is_home() || is_search() || is_tag() || is_category() || is_date() || is_author() );
	}
}

/* Blog archive page main loop */
if ( ! function_exists( 'lordcros_main_loop' ) ) {
	add_action( 'lordcros_main_loop', 'lordcros_main_loop' );

	function lordcros_main_loop() {

		$blog_style = lordcros_get_opt( 'blog_style', 'layout-1' );

		if ( have_posts() ) {

			// Show necessary description : tag, category, author bio
			if ( is_tag() && tag_description() ) {
				?>

				<div class="archive-meta"><?php echo tag_description() ?></div>

				<?php
			}

			if ( is_category() && category_description() ) {
				?>

				<div class="archive-meta"><?php echo category_description() ?></div>

				<?php
			}

			// Show main blog posts
			$classes = array();
			$classes[] = 'shortcode-latest-posts';
			$classes[] = 'lordcros-latest-posts';
			$classes[] = 'post-' . $blog_style;
			if ( $blog_style != 'layout-1' ) {
				$col = lordcros_get_opt( 'blog_grid_columns', '2' );
			}
			?>

			<div class="lordcros-blog-wrapper <?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-paged="1" data-source="main_loop" <?php echo ( ! empty( $col ) ) ? 'data-col="' . $col . '"' : ''; ?>>

				<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'content', get_post_format() );
					endwhile;
				?>

			</div>

			<div class="lordcros-pagination">
				<?php
					echo paginate_links( array(
							'type'		=> 'list',
							'prev_text'	=> esc_html__( 'Prev', 'lordcros' ),
							'next_text'	=> esc_html__( 'Next', 'lordcros' ),
						) );
				?>
			</div>

			<?php
		} else {
			get_template_part( 'content', 'none' );
		}

	}
}

/* Return the sidebar name of current page */
if ( ! function_exists( 'lordcros_get_sidebar_name' ) ) {
	function lordcros_get_sidebar_name() {

		$specific = '';
		$page_id = lordcros_get_page_id();
		$sidebar_name = '';

		if ( is_singular( array( 'post', 'service' ) ) ) {
			$sidebar_name = 'lordcros-post-sidebar';
		} elseif ( lordcros_is_blog_archive() ) {
			$sidebar_name = 'lordcros-blog-sidebar';
		}

		if ( 0 != $page_id ) {
			$specific = get_post_meta( $page_id, 'lordcros_custom_sidebar', true );
		}

		if ( '' != $specific && 'default' != $specific ) {
			$sidebar_name = $specific;
		}

		return $sidebar_name;
	}
}

/* Return CSS classes for page Sidebar container */
if ( ! function_exists( 'lordcros_get_sidebar_class' ) ) {
	function lordcros_get_sidebar_class() {
		$sidebar_class = '';
		$cl = 'col-lg-';
		$sidebar_size = 4;
		$content_size = 8;

		$layout = lordcros_page_layout();

		$content_size = 12 - $sidebar_size;

		if ( 'full-width' == $layout ) {
			$sidebar_size = 0;
			$content_size = 12;
		}

		$sidebar_class = $cl . $sidebar_size;

		if ( 'sidebar-left' == $layout ) {
			$sidebar_class .= ' order-lg-1';
		}

		return $sidebar_class;
	}
}

/* Strip variable tags */
if ( ! function_exists( 'lordcros_strip_tags' ) ) {
	function lordcros_strip_tags( $content ) {
		$content = str_replace( ']]>', ']]&gt;', $content );
		$temp = preg_replace( array( "/<script.*?\/script>/s", "/<style.*?\/style>/s" ), "", $content );
		if ( NULL !== $temp ) {
			$content = $temp;
		}
		$content = strip_tags( $content );

		return $content;
	}
}

/* Blog Share Button */
if ( ! function_exists( 'lordcros_blog_share_buttons' ) ) {
	function lordcros_blog_share_buttons( $link ) {

		if ( ! class_exists( 'LordCros_Core' ) ) return;

		echo lordcros_core_blog_social_buttons( $link );
	}
}

/* Post Tags */
if ( ! function_exists( 'lordcros_post_tags' ) ) {
	function lordcros_post_tags() {

		if ( ! has_tag() ) {
			return;
		}

		?>

		<div class="post-tags">
			<span class="tag-title"><?php echo esc_html__( 'Tags:', 'lordcros' ); ?></span>
			<?php echo get_the_tag_list( '', ' ' ); ?>
		</div>

		<?php
	}
}

/* Post Share Button */
if ( ! function_exists( 'lordcros_post_share_buttons' ) ) {
	function lordcros_post_share_buttons() {

		if ( ! class_exists( 'LordCros_Core' ) || ! defined( 'WPB_VC_VERSION' ) ) return;

		?>

		<div class="post-share-links">
			<span class="link-title"><?php echo esc_html__( 'Compartir:', 'lordcros' ); ?></span>
			<?php
				echo lordcros_core_shortcode_social_buttons( array(
						'type'			=>	'share',
						'btn_size'		=>	'small',
						'btn_shape'		=>	'square',
						'btn_style'		=>	'hover_colored',
						'icon_clr'		=>	'#7d7d7d',
						'icon_bg_clr'	=>	'#e4e4e4',
					) );
			?>
		</div>

		<?php
	}
}

/* Post Pagination */
if ( ! function_exists( 'lordcros_post_pagination' ) ) {
	function lordcros_post_pagination() {
		global $post;

		$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
		$next = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous ) {
			return;
		}

		?>

		<div class="post-pagination">
			<nav role="navigation" class="single-post-navigation">
				<div class="nav-previous">
					<?php previous_post_link( '%link', '<i class="fas fa-angle-left"></i> <div class="post-title"> <div class="nav-title">' . esc_html__( 'Anterior', 'lordcros' ) . '</div> <span> %title </span></div>' ); ?>
				</div>

				<div class="nav-next">
					<?php next_post_link( '%link', '<i class="fas fa-angle-right"></i> <div class="post-title"><div class="nav-title">' . esc_html__( 'Siguiente', 'lordcros' ) . '</div> <span> %title </span></div>' ); ?>
				</div>
			</nav>
		</div>

		<?php
	}
}

/* Post Comments */
if ( ! function_exists( 'lordcros_post_comments_field' ) ) {
	 function lordcros_post_comments_field() {
		$enable_page_comments = lordcros_get_opt( 'page_comments_enable', 1 );
		$enable_post_comments = lordcros_get_opt( 'post_comments_enable', 1 );

		if ( is_page() && ! $enable_page_comments ) {
			return;
		}

		if ( is_singular( 'post' ) && ! $enable_post_comments ) {
			return;
		}

		?>

		<div class="post-comments-field">
			<?php
				wp_reset_postdata();
				comments_template();
			?>
		</div>

		<?php
	 }
}

/* Comments Template */
if ( ! function_exists( 'lordcros_comment' ) ) {
	function lordcros_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		?>

		<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">

			<div class="comment-body">
				<div class="img-thumbnail">
					<?php echo get_avatar( $comment, 80 ); ?>
				</div>

				<div class="comment-block">
					<div class="comment-info">
						<span class="comment-by"><?php echo get_comment_author_link(); ?></span>
						<div class="comment-date">
							<?php printf( esc_html__( '%1$s at %2$s', 'lordcros' ), get_comment_date(),  get_comment_time() ); ?>
						</div>
					</div>
					<div class="comment-text">
						<?php if ( '0' == $comment->comment_approved ) : ?>
							<em><?php echo esc_html__( 'Your comment is awaiting moderation.', 'lordcros' ); ?></em>
							<br />
						<?php endif; ?>
						<?php comment_text() ?>
					</div>
					<div class="comment-action">
						<span> <?php edit_comment_link( esc_html__( 'Edit', 'lordcros' ), '  ', '' ); ?></span>
						<span> <?php comment_reply_link( array_merge( $args, array( 'reply_text' => esc_html__( 'Reply', 'lordcros' ), 'add_below' => 'comment', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?></span>
					</div>
				</div>
			</div>

		<?php
	}
}

/* Show breadcrumbs */
if ( ! function_exists( 'lordcros_show_breadcrumbs' ) ) {
	function lordcros_show_breadcrumbs() {

		$page_id = lordcros_get_page_id();

		$show_breadcrumbs = get_post_meta( $page_id, 'lordcros_show_breadcrumbs', true );
		if ( empty( $show_breadcrumbs ) || $show_breadcrumbs == 'inherit' ) {
			$show_breadcrumbs = lordcros_get_opt( 'show_breadcrumbs', true );
			if ( $show_breadcrumbs ) {
				$show_breadcrumbs = 'show';
			}
		}

		return $show_breadcrumbs == 'show';

	}
}

/* Breadcrumbs function */
if ( ! function_exists( 'lordcros_breadcrumbs' ) ) {
	function lordcros_breadcrumbs() {

		/* === OPTIONS === */
		$text['home']     = esc_html__( 'Inicio', 'lordcros' ); // text for the 'Home' link
		$text['category'] = esc_html__( 'Archive by Category "%s"', 'lordcros' ); // text for a category page
		$text['search']   = esc_html__( 'Search Results for "%s" Query', 'lordcros' ); // text for a search results page
		$text['tag']      = esc_html__( 'Posts Tagged "%s"', 'lordcros' ); // text for a tag page
		$text['author']   = esc_html__( 'Articles Posted by %s', 'lordcros' ); // text for an author page
		$text['404']      = esc_html__( 'Error 404', 'lordcros' ); // text for the 404 page

		$show_current_post  = 0; // 1 - show current post
		$show_current       = 1; // 1 - show current post/page/category title in breadcrumbs, 0 - don't show
		$show_on_home       = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
		$show_home_link     = 1; // 1 - show the 'Home' link, 0 - don't show
		$show_title         = 1; // 1 - show the title for the links, 0 - don't show
		$delimiter          = ' &#47; '; // delimiter between crumbs
		$before             = '<span class="current">'; // tag before the current crumb
		$after              = '</span>'; // tag after the current crumb
		/* === END OF OPTIONS === */

		global $post;

		$home_link    = home_url( '/' );
		$link_before  = '<span typeof="v:Breadcrumb">';
		$link_after   = '</span>';
		$link_attr    = ' rel="v:url" property="v:title"';
		$link         = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $link_after;
		$parent_id    = $parent_id_sec = ( ! empty( $post ) && is_a( $post, 'WP_Post' ) ) ? $post->post_parent : 0;
		$frontpage_id = get_option( 'page_on_front' );

		if ( is_home() || is_front_page() ) {

			if ( 1 == $show_on_home ) {
				echo '<div class="breadcrumbs"><a href="' . $home_link . '">' . $text['home'] . '</a></div>';
			}

		} else {
			echo '<div class="breadcrumbs">';

			if ( 1 == $show_home_link ) {
				echo '<a href="' . $home_link . '" rel="v:url" property="v:title">' . $text['home'] . '</a>';

				if ( 0 == $frontpage_id || $parent_id != $frontpage_id ) {
					echo esc_html( $delimiter );
				}
			}

			if ( is_category() ) {
				$this_cat = get_category( get_query_var( 'cat' ), false );

				if ( 0 != $this_cat->parent ) {
					$cats = get_category_parents( $this_cat->parent, TRUE, $delimiter );

					if ( 0 == $show_current ) {
						$cats = preg_replace( "#^(.+)$delimiter$#", "$1", $cats );
					}

					$cats = str_replace( '<a', $link_before . '<a' . $link_attr, $cats );
					$cats = str_replace( '</a>', '</a>' . $link_after, $cats );

					if ( 0 == $show_title ) {
						$cats = preg_replace( '/ title="(.*?)"/', '', $cats );
					}

					echo '' . $cats;
				}

				if ( 1 == $show_current ) {
					echo '' . $before . sprintf( $text['category'], single_cat_title( '', false ) ) . $after;
				}

			} elseif ( is_search() ) {
				echo '' . $before . sprintf( $text['search'], get_search_query() ) . $after;

			} elseif ( is_day() ) {
				echo sprintf( $link, get_year_link( get_the_time( 'Y' ) ), get_the_time( 'Y' ) ) . $delimiter;
				echo sprintf( $link, get_month_link( get_the_time( 'Y' ),get_the_time( 'm' ) ), get_the_time( 'F' ) ) . $delimiter;
				echo '' . $before . get_the_time( 'd' ) . $after;

			} elseif ( is_month() ) {
				echo sprintf( $link, get_year_link( get_the_time( 'Y' ) ), get_the_time('Y') ) . $delimiter;
				echo '' . $before . get_the_time( 'F' ) . $after;

			} elseif ( is_year() ) {
				echo '' . $before . get_the_time( 'Y' ) . $after;

			} elseif ( is_single() && ! is_attachment() ) {
				if ( 'post' != get_post_type() ) {
					$post_type = get_post_type_object( get_post_type() );
					$slug = $post_type->rewrite;
					printf( $link, $home_link . $slug['slug'] . '/', $post_type->labels->singular_name );

					if ( 1 == $show_current ) {
						echo esc_html( $delimiter ) . $before . get_the_title() . $after;
					}
				} else {
					$cat = get_the_category();
					$cat = $cat[0];
					$cats = get_category_parents( $cat, TRUE, $delimiter );

					if ( 0 == $show_current ) {
						$cats = preg_replace( "#^(.+)$delimiter$#", "$1", $cats );
					}

					$cats = str_replace( '<a', $link_before . '<a' . $link_attr, $cats );
					$cats = str_replace( '</a>', '</a>' . $link_after, $cats );
					if ( 0 == $show_title ) {
						$cats = preg_replace( '/ title="(.*?)"/', '', $cats );
					}

					echo '' . $cats;

					if ( 1 == $show_current_post ) {
						echo '' . $before . get_the_title() . $after;
					}
				}

			} elseif ( ! is_single() && ! is_page() && 'post' != get_post_type() && ! is_404() ) {
				$post_type = get_post_type_object( get_post_type() );
				if ( is_object( $post_type ) ) {
					echo '' . $before . $post_type->labels->singular_name . $after;
				}

			} elseif ( is_attachment() ) {
				$parent = get_post( $parent_id );
				$cat = get_the_category( $parent->ID );
				$cat = $cat[0];

				if ( $cat ) {
					$cats = get_category_parents( $cat, TRUE, $delimiter );
					$cats = str_replace( '<a', $link_before . '<a' . $link_attr, $cats );
					$cats = str_replace( '</a>', '</a>' . $link_after, $cats );

					if ( 0 == $show_title ) {
						$cats = preg_replace( '/ title="(.*?)"/', '', $cats );
					}

					echo '' . $cats;
				}

				printf( $link, get_permalink( $parent ), $parent->post_title );

				if ( 1 == $show_current ) {
					echo esc_html( $delimiter ) . $before . get_the_title() . $after;
				}

			} elseif ( is_page() && ! $parent_id ) {
				if ( 1 == $show_current ) {
					echo '' . $before . get_the_title() . $after;
				}

			} elseif ( is_page() && $parent_id ) {
				if ( $parent_id != $frontpage_id ) {
					$breadcrumbs = array();

					while ( $parent_id ) {
						$page = get_post( $parent_id );

						if ( $parent_id != $frontpage_id ) {
							$breadcrumbs[] = sprintf( $link, get_permalink( $page->ID ), get_the_title( $page->ID ) );
						}

						$parent_id = $page->post_parent;
					}

					$breadcrumbs = array_reverse( $breadcrumbs );

					for ( $i = 0; $i < count($breadcrumbs); $i++ ) {
						echo '' . $breadcrumbs[$i];
						if ( $i != count( $breadcrumbs ) - 1 ) {
							echo esc_html( $delimiter );
						}
					}
				}

				if ( 1 == $show_current ) {
					if ( 1== $show_home_link || ( 0 != $parent_id_sec && $parent_id_sec != $frontpage_id ) ) {
						echo esc_html( $delimiter );
					}

					echo '' . $before . get_the_title() . $after;
				}

			} elseif ( is_tag() ) {
				echo '' . $before . sprintf( $text['tag'], single_tag_title( '', false ) ) . $after;

			} elseif ( is_author() ) {
				global $author;

				$userdata = get_userdata( $author );
				echo '' . $before . sprintf( $text['author'], $userdata->display_name ) . $after;

			} elseif ( is_404() ) {
				echo '' . $before . $text['404'] . $after;

			} elseif ( has_post_format() && ! is_singular() ) {
				echo get_post_format_string( get_post_format() );
			}

			if ( get_query_var( 'paged' ) ) {
				if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) {
					echo ' (';
				}

				echo esc_html__( 'Page', 'lordcros' ) . ' ' . get_query_var( 'paged' );

				if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) {
					echo ')';
				}
			}

			echo '</div><!-- .breadcrumbs -->';
		}
	}
}

/* Get Page Layout function */
if( ! function_exists( 'lordcros_page_layout' ) ) {
	function lordcros_page_layout() {

		$page_id = lordcros_get_page_id();
		$layout = get_post_meta( $page_id, 'lordcros_page_layout', true );

		if ( empty( $layout ) || $layout == 'inherit' ) {
			if ( is_singular( 'room' ) ) {
				$layout = lordcros_get_opt( 'room_page_layout', 'layout-1' );
			} elseif ( is_singular( 'post' ) ) {
				$layout = lordcros_get_opt( 'page_sidebar', 'sidebar-right' );
			} elseif ( lordcros_is_blog_archive() ) {
				$layout = lordcros_get_opt( 'blog_layout', 'sidebar-right' );
			} else {
				$layout = lordcros_get_opt( 'page_sidebar', 'full-width' );
			}
		}

		return $layout;
	}
}

/* Get post image */
if ( ! function_exists( 'lordcros_get_post_thumbnail' ) ) {
	function lordcros_get_post_thumbnail( $size = 'medium', $attach_id = false ) {
		global $post;

		$img = '';

		if ( has_post_thumbnail() ) {
			if ( function_exists( 'wpb_getImageBySize' ) ) {
				if ( ! $attach_id ) {
					$attach_id = get_post_thumbnail_id();
				}

				$img = wpb_getImageBySize( array(
					'attach_id'	 => $attach_id,
					'thumb_size' => $size,
					'class'      => 'attachment-large wp-post-image'
				) );

				$img = $img['thumbnail'];
			} else {
				$img = get_the_post_thumbnail( $post->ID, $size );
			}
		}

		return $img;
	}
}

/* Extra Footer Section */
if ( ! function_exists( 'lordcros_extra_footer_section' ) ) {
	add_action( 'wp_footer', 'lordcros_extra_footer_section', 999 );

	function lordcros_extra_footer_section() {
		if ( ! lordcros_get_opt( 'show_cookie' ) ) {
			return;
		}

		$page_id = lordcros_get_opt( 'cookies_policy_page' );
		?>

			<div class="lordcros-cookies-popup">
				<div class="lordcros-cookies-inner container">
					<div class="cookies-info-text">
						<?php echo do_shortcode( lordcros_get_opt( 'cookies_text' ) ); ?>
					</div>

					<div class="cookies-buttons">
						<?php if ( $page_id ): ?>
							<a href="<?php echo get_permalink( $page_id ); ?>" class="read-more-cookies button"><?php echo esc_html__( 'More info' , 'lordcros' ); ?></a>
						<?php endif ?>

						<a href="#" class="accept-cookie-btn button"><?php echo esc_html__( 'Accept' , 'lordcros' ); ?></a>
					</div>
				</div>
			</div>

		<?php
	}
}

/* functio to Add Menu into Wordpress TopNav Admin-bar */
if ( ! function_exists( 'lordcros_add_wp_toolbar_menu_item' ) ) {
	function lordcros_add_wp_toolbar_menu_item( $title, $parent = false, $href = '', $custom_meta = array(), $custom_id = '' ) {

		if ( current_user_can( 'edit_theme_options' ) ) {
			if ( ! is_super_admin() || ! is_admin_bar_showing() ) {
				return;
			}

			global $wp_admin_bar;

			// Set custom ID
			if ( $custom_id ) {
				$id = $custom_id;
			} else {
				// Generate ID based on $title
				$id = strtolower( str_replace( ' ', '-', $title ) );
			}

			// links from the current host will open in the current window
			$meta = strpos( $href, site_url() ) !== false ? array() : array( 'target' => '_blank' ); // external links open in new tab/window
			$meta = array_merge( $meta, $custom_meta );

			$wp_admin_bar->add_node( array(
				'parent' => $parent,
				'id'     => $id,
				'title'  => $title,
				'href'   => $href,
				'meta'   => $meta,
			) );
		}

	}
}

/* Template Redirect for "Coming Soon" mode  */
if ( ! function_exists( 'lordcros_redirect_page_template' ) ) {
	add_action( 'template_redirect', 'lordcros_redirect_page_template', 99 );

	function lordcros_redirect_page_template() {
		$coming_soon_mode = lordcros_get_opt( 'coming_soon_mode' );

		if ( isset( $coming_soon_mode ) && ! empty( $coming_soon_mode ) && ! is_user_logged_in() ) {
			if ( ! is_home() && ! is_front_page() ) {
				wp_redirect( site_url() );
				die;
			}
		}
	}
}

/* Load "Coming Soon" page template */
if ( ! function_exists( 'lordcros_load_coming_soon_page' ) ) {
	add_filter( 'template_include', 'lordcros_load_coming_soon_page', 99 );

	function lordcros_load_coming_soon_page( $template ) {
		$coming_soon_mode = lordcros_get_opt( 'coming_soon_mode' );

		if ( isset( $coming_soon_mode ) && ! empty( $coming_soon_mode ) && ! is_user_logged_in() ) {
			return lordcros_get_template_part( 'coming', 'soon' );
		}

		return $template;
	}
}

/* function to register required plugins */
if ( ! function_exists( 'lordcros_register_required_plugins' ) ) {
	add_action( 'tgmpa_register', 'lordcros_register_required_plugins' );

	function lordcros_register_required_plugins() {
		$plugins = array(
			array(
				'name'					=> 'LordCros Core',
				'slug'					=> 'lordcros-core',
				'source'				=> LORDCROS_LIB . '/plugins/lordcros-core.zip',
				'required'				=> true,
				'force_activation'		=> false,
				'force_deactivation'	=> false,
				'version'				=> '1.1.0',
				'image_url'				=> LORDCROS_URI . '/images/plugins/lordcros_core.jpg',
				'check_str'				=> 'LordCros_Core'
			),
			array(
				'name'					=>	'Redux Framework',
				'slug'					=>	'redux-framework',
				'required'				=>	true,
				'image_url'				=>	LORDCROS_URI . '/images/plugins/redux_options.jpg',
				'check_str'				=>	'ReduxFramework'
			),
			array(
				'name'					=>	'Meta Box',
				'slug'					=>	'meta-box',
				'required'				=>	true,
				'image_url'				=>	LORDCROS_URI . '/images/plugins/metabox.jpg',
				'check_str'				=>	'RWMB_Loader'
			),
			array(
				'name'					=>	'Meta Box Conditional Logic',
				'slug'					=>	'meta-box-conditional-logic',
				'source'				=>	LORDCROS_LIB . '/plugins/meta-box-conditional-logic.zip',
				'required'				=>	true,
				'version'				=>	'1.6.5',
				'force_activation'		=>	false,
				'force_deactivation'	=>	false,
				'image_url'				=>	LORDCROS_URI . '/images/plugins/mb-conditional-logic.jpg',
				'check_str'				=>	'MB_Conditional_Logic'
			),
			array(
				'name'					=>	'Meta Box Tabs',
				'slug'					=>	'meta-box-tabs',
				'source'				=>	LORDCROS_LIB . '/plugins/meta-box-tabs.zip',
				'required'				=>	true,
				'version'				=>	'1.1.4',
				'force_activation'		=>	false,
				'force_deactivation'	=>	false,
				'image_url'				=>	LORDCROS_URI . '/images/plugins/metabox-tabs.jpg',
				'check_str'				=>	'MB_Tabs'
			),
			array(
				'name'					=>	'WPBakery Page Builder',
				'slug'					=>	'js_composer',
				'source'				=>	LORDCROS_LIB . '/plugins/js_composer.zip',
				'required'				=>	true,
				'version'				=>	'6.0.5',
				'force_activation'		=>	false,
				'force_deactivation'	=>	false,
				'image_url'				=>	LORDCROS_URI . '/images/plugins/visual_composer.jpg',
				'check_str'				=>	'Vc_Manager'
			),
			array(
				'name'					=>	'Revolution Slider',
				'slug'					=>	'revslider',
				'source'				=>	LORDCROS_LIB . '/plugins/revslider.zip',
				'required'				=>	true,
				'version'				=>	'5.4.8.3',
				'force_activation'		=>	false,
				'force_deactivation'	=>	false,
				'image_url'				=>	LORDCROS_URI . '/images/plugins/revolution_slider.jpg',
				'check_str'				=>	'RevSliderFront'
			),
			array(
				'name'					=>	'Contact Form 7',
				'slug'					=>	'contact-form-7',
				'required'				=>	true,
				'image_url'				=>	LORDCROS_URI . '/images/plugins/contact_form_7.jpg',
				'check_str'				=>	'WPCF7'
			),
			array(
				'name'					=>	'MailChimp for Wordpress',
				'slug'					=>	'mailchimp-for-wp',
				'required'				=>	true,
				'image_url'				=>	LORDCROS_URI . '/images/plugins/mailchimp-for-wp.jpg',
				'check_str'				=>	'MC4WP_MailChimp'
			)
		);

		$config = array(
			'default_path'		=> '',
			'menu'				=> 'install-required-plugins',
			'has_notices'		=> true,
			'dismissable'		=> true,
			'dismiss_msg'		=> '',
			'is_automatic'		=> false,
			'message'			=> '',
			'strings'			=> array(
				'page_title'						=> esc_html__( 'Install Required Plugins', 'lordcros' ),
				'menu_title'						=> esc_html__( 'Install Plugins', 'lordcros' ),
				'installing'						=> esc_html__( 'Installing Plugin: %s', 'lordcros' ), // %s = plugin name.
				'oops'								=> esc_html__( 'Something went wrong with the plugin API.', 'lordcros' ),
				'notice_can_install_required'		=> _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'lordcros' ), // %1$s = plugin name(s).
				'notice_can_install_recommended'	=> _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'lordcros' ), // %1$s = plugin name(s).
				'notice_cannot_install'				=> _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'lordcros' ),
				'notice_can_activate_required'		=> _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'lordcros' ),
				'notice_can_activate_recommended'	=> _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'lordcros' ),
				'notice_cannot_activate'			=> _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'lordcros' ),
				'notice_ask_to_update'				=> _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'lordcros' ),
				'notice_cannot_update'				=> _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'lordcros' ),
				'install_link'						=> _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'lordcros' ),
				'activate_link'						=> _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'lordcros' ),
				'return'							=> esc_html__( 'Return to Required Plugins Installer', 'lordcros' ),
				'plugin_activated'					=> esc_html__( 'Plugin activated successfully.', 'lordcros' ),
				'complete'							=> esc_html__( 'All plugins installed and activated successfully. %s', 'lordcros' ),
				'nag_type'							=> 'updated'
			)
		);

		tgmpa( $plugins, $config );

	}
}

/* Full Screen Searchbox */
if ( ! function_exists( 'lordcros_full_screen_searchbox' ) ) {
	add_action( 'lordcros_after_footer_content', 'lordcros_full_screen_searchbox', 10 );

	function lordcros_full_screen_searchbox() {
		?>

		<div id="lordcros-full-screen-search" class="full-screen-search-form">
			<span class="form-close"></span>

			<div class="form-inner">
				<form role="search" method="get" id="searchform" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="screen-reader-text"><?php echo _x( 'Search for:', 'label', 'lordcros' ); ?></span>
					<input type="hidden" name="post_type[]" value="post">
					<input type="hidden" name="post_type[]" value="room">
					<input type="hidden" name="post_type[]" value="service">
					<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search for &hellip;', 'placeholder', 'lordcros' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
					<button type="submit" id="searchsubmit" class="searchsubmit"><i class="lordcros lordcros-search"></i></button>
				</form>
			</div>
		</div>

		<?php
	}
}

/* Get current user info */
if ( ! function_exists( 'lordcros_get_current_user_info' ) ) {
	function lordcros_get_current_user_info( ) {
		$user_info = array(
			'display_name'	=> '',
			'first_name'	=> '',
			'last_name'		=> '',
			'email'			=> '',
			'country_code'	=> '',
			'phone'			=> '',
			'birthday'		=> '',
			'address'		=> '',
			'city'			=> '',
			'zip'			=> '',
			'country'		=> '',
			'photo_url'		=> '',
		);

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			$user_info['display_name'] = $current_user->user_firstname . ' ' . $current_user->user_lastname;
			$user_info['login'] = $current_user->user_login;
			$user_info['first_name'] = $current_user->user_firstname;
			$user_info['last_name'] = $current_user->user_lastname;
			$user_info['email'] = $current_user->user_email;
			$user_info['description'] = $current_user->description;
			$user_info['phone'] = get_user_meta( $user_id, 'phone', true );
			$user_info['birthday'] = get_user_meta( $user_id, 'birthday', true );
			$user_info['address'] = get_user_meta( $user_id, 'address', true );
			$user_info['city'] = get_user_meta( $user_id, 'city', true );
			$user_info['zip'] = get_user_meta( $user_id, 'zip', true );
			$user_info['country'] = get_user_meta( $user_id, 'country', true );
			$user_info['photo_url'] = ( isset( $current_user->photo_url ) && ! empty( $current_user->photo_url ) ) ? $current_user->photo_url : '';
		}

		return $user_info;
	}
}

/* update user recent activity */
if ( ! function_exists( 'lordcros_update_user_recent_activity' ) ) {
	function lordcros_update_user_recent_activity( $post_id ) {
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$recent_activity_array = array();
			$recent_activity = get_user_meta( $user_id , 'recent_activity', true );

			if ( ! empty( $recent_activity ) ) {
				$recent_activity_array = unserialize($recent_activity);

				// add current acc id to recent activity
				if ( ( $key = array_search( $post_id, $recent_activity_array ) ) !== false ) {
					// if already exitst unset it first
					unset( $recent_activity_array[$key] );
				}
				array_unshift( $recent_activity_array, $post_id );

				// make recent activity size smaller than 10
				$user_activity_maximum_len = 10;
				if ( count( $recent_activity_array ) > $user_activity_maximum_len ) {
					$temp = array_chunk( $recent_activity_array, $user_activity_maximum_len );
					$recent_activity_array = $temp[0];
				}
			} else {
				$recent_activity_array = array( $post_id );
			}

			update_user_meta( $user_id, 'recent_activity', serialize( $recent_activity_array ) );
		}
	}
}

/* get avatar function */
if ( ! function_exists( 'lordcros_get_avatar' ) ) {
	function lordcros_get_avatar( $user_data ) {
		$size = empty( $user_data['size'] ) ? 96 : $user_data['size'];
		$photo = '';
		if ( ! empty( $user_data['id'] ) ) {
			$photo_url = get_user_meta( $user_data['id'], 'photo_url', true );
			if ( ! empty( $photo_url ) ) {
				$photo = '<img width="' . $size . '" height="' . $size . '" alt="avatar" src="' . $photo_url . '">';
			}
		}
		if ( empty( $photo ) ) {
			$photo = lordcros_get_default_avatar( $user_data['email'], $size );
		}
		return wp_kses_post( $photo );
	}
}

/* check if gravatar exists function */
if ( ! function_exists( 'lordcros_get_default_avatar' ) ) {
	function lordcros_get_default_avatar( $email, $size ) {
		return get_avatar( $email, $size );
	}
}

/* Update user profile on dashboard edit */
if ( ! function_exists( 'lordcros_user_update_profile' ) ) {
	function lordcros_user_update_profile() {
		$user_id = get_current_user_id();

		if ( isset( $_POST['action'] ) && $_POST['action'] == 'update_profile' ) {
			if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'update_profile' ) ) {

				$update_data = array(
					'ID'			=> $user_id,
					'first_name'	=> isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '',
					'last_name'		=> isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '',
					'user_email'	=> isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '',
					'birthday'		=> isset( $_POST['birthday'] ) ? sanitize_text_field( $_POST['birthday'] ) : '',
					'phone'			=> isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '',
					'address'		=> isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '',
					'city'			=> isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '',
					'country'		=> isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '',
					'zip'			=> isset( $_POST['zip'] ) ? sanitize_text_field( $_POST['zip'] ) : '',
					'description'	=> isset( $_POST['description'] ) ? sanitize_text_field( $_POST['description'] ) : '',
					);

				$update_user_meta = array(
					'birthday'		=> isset( $_POST['birthday'] ) ? sanitize_text_field( $_POST['birthday'] ) : '',
					'country_code'	=> isset( $_POST['country_code'] ) ? sanitize_text_field( $_POST['country_code'] ) : '',
					'phone'			=> isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '',
					'address'		=> isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '',
					'city'			=> isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '',
					'country'		=> isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '',
					'zip'			=> isset( $_POST['zip'] ) ? sanitize_text_field( $_POST['zip'] ) : '',
				);

				$update_data['photo_url'] = '';

				if ( ! isset( $_FILES['photo'] ) || ( $_FILES['photo']['size'] == 0 ) ) {
					if ( ! empty( $_POST['remove_photo'] ) ) {
						$update_data['photo_url'] = '';
					}
				} else {
					if ( ! function_exists( 'wp_handle_upload' ) ) {
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}

					$uploadedfile = $_FILES['photo'];
					$upload_overrides = array( 'test_form' => false );
					$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
					$update_data['photo_url'] = $movefile['url'];
				}
				wp_update_user( $update_data );

				foreach( $update_user_meta as $key => $value ) {
					update_user_meta( $user_id, $key, $value );
				}

				if ( ! empty( $_POST['remove_photo'] ) || ! empty( $update_data['photo_url'] ) ) {
					update_user_meta( $user_id, 'photo_url', $update_data['photo_url'] );
				}

				echo '<div class="alert alert-success">' . esc_html__( 'Your profile is updated successfully.', 'lordcros' ) . '<i class="lordcros lordcros-cancel"></i></div>';
			} else {
				echo '<div class="alert alert-error">' . esc_html__( 'Sorry, your nonce did not verify.', 'lordcros' ) . '<i class="lordcros lordcros-cancel"></i></div>';
			}
		}
	}

	add_action( 'lordcros_before_dashboard', 'lordcros_user_update_profile' );
}

/* login failed function */
if ( ! function_exists( 'lordcros_login_failed' ) ) {
	function lordcros_login_failed( $user ) {

		$login_page = lordcros_get_opt( 'login_page' );
		if ( ! empty( $login_page ) ) {
			wp_redirect( add_query_arg( array( 'login' => 'failed', 'user' => $user ), get_permalink( $login_page ) ) );
			exit();
		}
	}
}
add_action( 'wp_login_failed', 'lordcros_login_failed' );


/* Authentication function */
if ( ! function_exists( 'lordcros_authenticate' ) ) {
	function lordcros_authenticate( $user, $username, $password ){

		$login_page = lordcros_get_opt( 'login_page' );

		if ( ! empty( $login_page ) && ( empty( $username ) || empty( $password ) ) && empty( $_GET['no_redirect'] ) ) {
			wp_redirect( add_query_arg( $_GET, get_permalink( $login_page ) ) );
			exit;
		}
	}
}
add_filter( 'authenticate', 'lordcros_authenticate', 1, 3 );

if ( ! function_exists( 'lordcros_login_url' ) ) {
	function lordcros_login_url() {
		$login_page = lordcros_get_opt( 'login_page' );
		if ( ! empty( $login_page ) ) {
			return get_permalink( $login_page );
		} else {
			$redirect_url_on_login = '';
			if ( ! empty( lordcros_get_opt( 'redirect_page' ) ) ) {
				$redirect_url_on_login = get_permalink( lordcros_get_opt( 'redirect_page' ) );
			}
			return wp_login_url( $redirect_url_on_login );
		}
	}
}
