<?php
/**
 * Header Elements Define Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Theme favicon */
if ( ! function_exists( 'lordcros_favicon' ) ) {
	function lordcros_favicon() {
		if ( function_exists( 'wp_site_icon' ) && has_site_icon() ) :
			wp_site_icon();
		else:
			// Get Default favicon
			$favicon = LORDCROS_URI . '/favicon.png';

			// Get Default Retina favicon
			$fav_retina = LORDCROS_URI . '/images/favicon_retina.png';

			// Get Uploaded favicon
			$fav_uploaded = lordcros_get_opt( 'favicon' );
			if ( isset( $fav_uploaded['url'] ) && '' != $fav_uploaded['url'] ) {
				$favicon = $fav_uploaded['url'];
			}

			// Get Uploaded Retina favicon
			$fav_retina_uploaded = lordcros_get_opt( 'favicon_retina' );
			if ( isset( $fav_retina_uploaded['url'] ) && '' != $fav_retina_uploaded['url'] ) {
				$fav_retina = $fav_retina_uploaded['url'];
			}
			?>

			<link rel="shortcut icon" href="<?php echo esc_url($favicon); ?>">
			<link rel="apple-touch-icon-precomposed" sizes="160x160" href="<?php echo esc_url($fav_retina); ?>">

			<?php
		endif;
	}

	add_action( 'wp_head', 'lordcros_favicon' );
}

/* Header Logo */
if ( ! function_exists( 'lordcros_header_block_logo' ) ) {
	function lordcros_header_block_logo() {
		$logo = LORDCROS_URI . '/images/logo.png';
		$logo_uploaded = lordcros_get_opt( 'logo' );

		if ( isset( $logo_uploaded['url'] ) && '' != $logo_uploaded['url'] ) {
			if ( is_ssl() ) {
				$logo = str_replace( 'http"//', 'https://', $logo_uploaded['url'] );
			} else {
				$logo = $logo_uploaded['url'];
			}
		}

		ob_start();
		?>

		<div class="site-logo">
			<a href="<?php echo esc_url( home_url('/') ); ?>" rel="home">
				<img class="normal-logo" src="<?php echo esc_url( $logo ); ?>" title="<?php bloginfo( 'description' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
			</a>
		</div>

		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Header Logo */
if ( ! function_exists( 'lordcros_header_block_sticky_logo' ) ) {
	function lordcros_header_block_sticky_logo() {
		$sticky_logo = LORDCROS_URI . '/images/sticky-logo.png';
		$sticky_logo_uploaded = lordcros_get_opt( 'alternative_logo' );

		if ( isset( $sticky_logo_uploaded['url'] ) && '' != $sticky_logo_uploaded['url'] ) {
			if ( is_ssl() ) {
				$sticky_logo = str_replace( 'http"//', 'https://', $sticky_logo_uploaded['url'] );
			} else {
				$sticky_logo = $sticky_logo_uploaded['url'];
			}
		}

		ob_start();
		?>

		<div class="site-logo">
			<a href="<?php echo esc_url( home_url('/') ); ?>" rel="home">
				<img class="sticky-logo" src="<?php echo esc_url( $sticky_logo ); ?>" title="<?php bloginfo( 'description' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
			</a>
		</div>

		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Main Navigation */
if ( ! function_exists( 'lordcros_header_block_main_navigation' ) ) {
	function lordcros_header_block_main_navigation() {
		if ( ! has_nav_menu( 'main-navigation' ) ) {
			return;
		}

		ob_start();
		?>

		<nav class="main-navigation site-nav lordcros-navigation">
			<?php
				$walker = new LordCrosWalker;

				wp_nav_menu( array(
					'theme_location'	=>	'main-navigation',
					'fallback_cb'		=>	false,
					'container'			=>	false,
					'items_wrap'		=>	'<ul id="%1$s" class="menu-main-navigation lordcros-menu-nav">%3$s</ul>',
					'walker'			=>	$walker
				) );
			?>
		</nav>

		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Search Form */
if ( ! function_exists( 'lordcros_header_block_search' ) ) {
	function lordcros_header_block_search() {
		$search_icon = lordcros_get_opt( 'header_search_icon' );

		if ( ! $search_icon ) {
			return;
		}
		ob_start();
		?>

		<div class="search-button">
			<a href="#" class="full-screen-search"><i class="lordcros lordcros-search"></i></a>
		</div>

		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Header Room Search Form */
if ( ! function_exists( 'lordcros_room_header_search_form' ) ) {
	function lordcros_room_header_search_form( $field = false ) {
		$room_search_form = lordcros_get_opt( 'header_room_search' );
		$room_search_page = lordcros_get_opt( 'room_search_page' );

		if ( ! $room_search_form ) {
			return;
		}

		$current_day = date( 'd' );
		$current_month = date( 'M' );
		$next_day = date( 'd', strtotime( '+1 day' ) );
		$month_next_day = date( 'M', strtotime( '+1 day' ) );

		ob_start();
		?>
			<?php if ( ! empty( $room_search_page ) ) : ?>
				<div class="room-search-form">
					<form action="<?php echo esc_url( get_permalink( $room_search_page ) ); ?>" method="get">
						<?php if ( $field ) : ?>
							<div class="form-input-area">
								<div id="form-check-in" class="search-calendar-show">
									<div class="check-in-section-wrap">
										<span class="section-title"><?php echo esc_html__( 'Check-In', 'lordcros' ); ?></span>
										<div class="section-content">
											<div class="day-val"><?php echo esc_html( $current_day ); ?></div>
											<div class="leftside-inner">
												<span class="month-val"><?php echo esc_html( $current_month ); ?></span>
												<i class="fas fa-chevron-down"></i>
											</div>
										</div>
									</div>

									<input type="hidden" id="lc-booking-date-month-from" class="lc-booking-date-month-from">
									<input type="hidden" id="lc-booking-date-day-from" class="lc-booking-date-day-from">
									<input type="text" name="date_from" class="lc-booking-date-range-from" placeholder="Check In">
								</div>
								<div id="form-check-out" class="search-calendar-show">
									<div class="check-out-section-wrap">
										<span class="section-title"><?php echo esc_html__( 'Check-Out', 'lordcros' ); ?></span>
										<div class="section-content">
											<div class="day-val"><?php echo esc_html( $next_day ); ?></div>
											<div class="leftside-inner">
												<span class="month-val"><?php echo esc_html( $month_next_day ); ?></span>
												<i class="fas fa-chevron-down"></i>
											</div>
										</div>
									</div>

									<input type="hidden" id="lc-booking-date-month-to" class="lc-booking-date-month-to">
									<input type="hidden" id="lc-booking-date-day-to" class="lc-booking-date-day-to">
									<input type="text" name="date_to" class="lc-booking-date-range-to" placeholder="Check Out">
								</div>
								<div id="form-guests-num" class="search-guest-count">
									<div class="guest-section-wrap">
										<span class="section-title"><?php echo esc_html__( 'Guests', 'lordcros' ); ?></span>
										<div class="section-content">
											<div class="guest-val">1</div>
											<div class="leftside-inner">
												<i class="fas fa-chevron-up"></i>
												<i class="fas fa-chevron-down"></i>
											</div>
										</div>
									</div>

									<input type="number" name="adults" id="lc-booking-form-guests" class="lc-booking-form-guests" placeholder="Guest" min="1" value="1">
								</div>
							</div>
						<?php endif; ?>

						<div class="form-submit-wrap">
							<button type="submit" class="room-search-submit"><i class="lordcros lordcros-bell"></i><?php echo esc_html__( 'Check Rooms', 'lordcros' ); ?></button>
						</div>
					</form>
				</div>
			<?php endif; ?>
		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Multi-Language Switcher */
if ( ! function_exists( 'lordcros_multi_language_switcher' ) ) {
	function lordcros_multi_language_switcher() {
		$header_lang_switch = lordcros_get_opt( 'header_lang_switch' );

		if ( ! $header_lang_switch ) {
			return;
		}

		$language_count = 1;
		$languages = array();

		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$languages = icl_get_languages( 'skip_missing=1&orderby=code' );
			$language_count = count( $languages );
		}

		if ( $language_count > 1 ) {
			?>
				<div class="wcml_language_switcher language-picker">
					<?php
						foreach ( $languages as $l ) {

							if ( $l['active'] ) {
								?>
								<a href="javascript: void(0)" class="wcml_selected_language"><?php echo esc_html($l['language_code']); ?></a>
								<ul>
								<?php
							} else {
								echo '<li><a href="' . esc_url( $l['url'] ) . '">' . esc_html( $l['language_code'] ) . '</a></li>';
							}

						}
					?>

					</ul>
				</div>
			<?php
		} else {
			/* Demo Content Html */
			?>
			<div class="wcml_language_switcher language-picker">
				<a href="javascript: void(0)" class="wcml_selected_language">EN</a>
				<ul>
					<li><a href="#">TR</a></li>
					<li><a href="#">EN</a></li>
					<li><a href="#">FR</a></li>
					<li><a href="#">BR</a></li>
				</ul>
			</div>
			<?php
		}
	}
}

/* Multi-Language Switcher Arrange */
if ( ! function_exists( 'lordcros_multi_language_switcher_arrange' ) ) {
	function lordcros_multi_language_switcher_arrange() {
		$header_lang_switch = lordcros_get_opt( 'header_lang_switch' );

		if ( ! $header_lang_switch ) {
			return;
		}

		$language_count = 1;
		$languages = array();

		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$languages = icl_get_languages( 'skip_missing=1&orderby=code' );
			$language_count = count( $languages );
		}

		if ( $language_count > 1 ) {
			?>
				<div class="wcml_language_switcher language-picker-arrange">
					<ul>
					<?php
						foreach ( $languages as $l ) {
							if ( $l['active'] ) {
								echo '<li><a href="' . esc_url( $l['url'] ) . '" class="wcml_selected_language">' . esc_html( $l['language_code'] ) . '</a></li>';
							} else {
								echo '<li><a href="' . esc_url( $l['url'] ) . '">' . esc_html( $l['language_code'] ) . '</a></li>';
							}
						}
					?>

					</ul>
				</div>
			<?php
		} else {
			/* Demo Content Html */
			?>
			<div class="wcml_language_switcher language-picker-arrange">
				<ul>
					<li><a href="#" class="wcml_selected_language">EN</a></li>
					<li><a href="#">TR</a></li>
					<li><a href="#">FR</a></li>
					<li><a href="#">BR</a></li>
				</ul>
			</div>
			<?php
		}
	}
}

/* Social Share/Follow Links */
if ( ! function_exists( 'lordcros_header_social_links' ) ) {
	function lordcros_header_social_links( $style = 'default' ) {
		$social_links = lordcros_get_opt( 'header_social_icons' );

		if ( ! $social_links ) {
			return;
		}

		if ( class_exists( 'LordCros_Core' ) ) {
			?>
				<div class="header-social-links">
					<?php echo do_shortcode('[lc_social_buttons type="share" btn_size="small" btn_shape="circle" btn_style="' . $style . '"]'); ?>
				</div>
			<?php
		}
	}
}

/* Contact Phone Number */
if ( ! function_exists( 'lordcros_contact_phone_num' ) ) {
	function lordcros_contact_phone_num() {
		$phone_num_state = lordcros_get_opt( 'header_phone_num' );
		$phone_num_txt = lordcros_get_opt( 'phone_num_val' );
		$phone_num_val = preg_replace("/[^0-9]/", '', $phone_num_txt);

		if ( ! $phone_num_state ) {
			return;
		}
		ob_start();
		?>
			<div class="contact-phone-num">
				<a href="tel:<?php echo esc_attr( $phone_num_val ); ?>"><i class="lordcros lordcros-phone"></i><?php echo '' . $phone_num_txt ?></a>
			</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Copyright Text */
if ( ! function_exists( 'lordcros_header_block_copyright' ) ) {
	function lordcros_header_block_copyright() {
		$header_copyright_state = lordcros_get_opt( 'header_copyright' );
		$header_copyright_txt = lordcros_get_opt( 'header_copyright_txt' );

		if ( ! $header_copyright_state ) {
			return;
		}
		ob_start();
		?>
			<div class="lordcros-header-copyright-txt">
				<p><?php echo '' . $header_copyright_txt; ?></p>
			</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Contact Info E-mail Address */
if ( ! function_exists( 'lordcros_header_info_mail' ) ) {
	function lordcros_header_info_mail() {
		$email_show_state = lordcros_get_opt( 'header_email' );
		$email_address = lordcros_get_opt( 'email_address' );

		if ( ! $email_show_state ) {
			return;
		}
		ob_start();
		?>
			<div class="info-email-wrap">
				<a href="mailto:<?php echo esc_attr( $email_address ); ?>"><?php echo esc_html( $email_address ); ?></a>
			</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Current Location Weather Info */
if ( ! function_exists( 'lordcros_weather_info' ) ) {
	function lordcros_weather_info() {
		$weather_state = lordcros_get_opt( 'header_weather' );
		$weatherAPIkey = lordcros_get_opt( 'openweather_map_api_key' );
		$cityInfo = lordcros_get_opt( 'weahter_city_name_id' );

		if ( ! $weather_state || ! $weatherAPIkey ) {
			return;
		}

		if ( is_numeric( $cityInfo ) ) {
			$googleApiUrl = "http://api.openweathermap.org/data/2.5/weather?id=" . $cityInfo . "&APPID=" . $weatherAPIkey;
		} else {
			$googleApiUrl = "http://api.openweathermap.org/data/2.5/weather?q=" . $cityInfo . "&APPID=" . $weatherAPIkey;
		}

		$response = wp_remote_get( $googleApiUrl );
		if ( ! is_array( $response ) ) {
			return '';
		}

		$response = $response['body'];
		$data = json_decode( $response );
		$weatherDesc = ucwords( $data->weather[0]->description );
		$celsiusTemp = (int)( $data->main->temp - 273.15 );
		$fahrenheitTemp = (int)( ($celsiusTemp * 9 / 5) + 32 );
		$weatherIcon = $data->weather[0]->icon;
		$weatherIconClass = '';

		if ( '01d' == $weatherIcon ) { $weatherIconClass = 'lordcros-sun'; } elseif ( '01n' == $weatherIcon ) { $weatherIconClass = 'lordcros-half-moon'; }
		elseif ( '02d' == $weatherIcon ) { $weatherIconClass = 'lordcros-clound-sun'; } elseif ( '02n' == $weatherIcon ) { $weatherIconClass = 'lordcros-cloudy-night'; }
		elseif ( '03d' == $weatherIcon ) { $weatherIconClass = 'lordcros-cloud'; } elseif ( '03n' == $weatherIcon ) { $weatherIconClass = 'lordcros-cloud'; }
		elseif ( '04d' == $weatherIcon ) { $weatherIconClass = 'lordcros-cloudy'; } elseif ( '04n' == $weatherIcon ) { $weatherIconClass = 'lordcros-cloudy'; }
		elseif ( '09d' == $weatherIcon ) { $weatherIconClass = 'lordcros-rain'; } elseif ( '09n' == $weatherIcon ) { $weatherIconClass = 'lordcros-rain'; }
		elseif ( '10d' == $weatherIcon ) { $weatherIconClass = 'lordcros-rain-sun'; } elseif ( '10n' == $weatherIcon ) { $weatherIconClass = 'lordcros-rain-night'; }
		elseif ( '11d' == $weatherIcon ) { $weatherIconClass = 'lordcros-thunderstorm'; } elseif ( '11n' == $weatherIcon ) { $weatherIconClass = 'lordcros-thunderstorm'; }
		elseif ( '13d' == $weatherIcon ) { $weatherIconClass = 'lordcros-snow'; } elseif ( '13n' == $weatherIcon ) { $weatherIconClass = 'lordcros-snow'; }
		elseif ( '50d' == $weatherIcon ) { $weatherIconClass = 'lordcros-fog'; } elseif ( '50n' == $weatherIcon ) { $weatherIconClass = 'lordcros-fog'; }
		else { $weatherIconClass = 'unavailable-icon'; }

		ob_start();
		?>
			<div class="lordcros-weather-data">
				<i class="lordcros <?php echo esc_attr( $weatherIconClass ); ?>"></i>
				<span class="weather-description"><?php echo '' . $weatherDesc; ?></span> , <span class="weather-temp"><?php echo '' . $celsiusTemp; ?> &deg;C / <?php echo '' . $fahrenheitTemp; ?> &deg;F</span>
			</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Account Sign In/Out */
if ( ! function_exists( 'lordcros_header_block_account' ) ) {
	function lordcros_header_block_account() {
		$header_sign_links = lordcros_get_opt( 'header_sign_links' );

		if ( ! $header_sign_links ) {
			return;
		}
		ob_start();
		?>
			<div class="account-form-wrap">
				<div class="lordcros-account-sign-in">
					<a href="<?php echo lordcros_login_url(); ?>"><?php echo esc_html__( 'Sign In', 'lordcros' ); ?></a>
				</div>

				<div class="lordcros-account-register">
					<a href="<?php echo lordcros_login_url(); ?>"><?php echo esc_html__( 'Register', 'lordcros' ); ?></a>
				</div>
			</div>
		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Hamburger Menu Button */
if ( ! function_exists( 'lordcros_hamburger_menu_btn' ) ) {
	function lordcros_hamburger_menu_btn( $class = '' ) {
		?>
			<div class="lordcros-burger-wrapper <?php echo esc_attr( $class ); ?>">
				<div class="hamburger">
					<span></span>
					<span></span>
					<span></span>
				</div>
			</div>
		<?php
	}
}

/* Hamburger Navigation */
if ( ! function_exists( 'lordcros_header_block_hamburg_nav_menu' ) ) {
	function lordcros_header_block_hamburg_nav_menu() {
		if ( ! has_nav_menu( 'main-navigation' ) ) {
			return;
		}

		ob_start();
		?>

		<nav class="hamburger-navigation site-nav lordcros-navigation">
			<?php
				$walker = new LordCrosWalker;

				wp_nav_menu( array(
					'theme_location'	=>	'main-navigation',
					'fallback_cb'		=>	false,
					'container'			=>	false,
					'items_wrap'		=>	'<ul id="%1$s" class="hamburger-menu-navigation lordcros-menu-nav">%3$s</ul>',
					'walker'			=>	$walker
				) );
			?>
		</nav>

		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Header Explain Text */
if ( ! function_exists( 'lordcros_header_block_explain_txt' ) ) {
	function lordcros_header_block_explain_txt() {
		$explain_txt_content = lordcros_get_opt( 'explain_txt_content' );

		if ( ! $explain_txt_content ) {
			return;
		}
		ob_start();
		?>

		<div class="header-explain-txt-wrap">
			<?php echo do_shortcode( $explain_txt_content ); ?>
		</div>

		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Mobile Nav */
if ( ! function_exists( 'lordcros_mobile_navigation' ) ) {
	function lordcros_mobile_navigation() {
		$menu_locations = get_nav_menu_locations();
		$mobile_location = 'main-navigation';
		?>

		<div class="mobile-nav">
			<div class="close-btn">
				<a href="#" class="close-btn-link">
					<span></span>
					<span></span>
				</a>
			</div>

			<?php

			if ( isset( $menu_locations['mobile-side-navigation'] ) && $menu_locations['mobile-side-navigation'] != 0 ) {
				$mobile_location = 'mobile-side-navigation';
			}

			if ( has_nav_menu( $mobile_location ) ) {
				echo '<div class="mobile-navigation-content">';

				$walker = new LordCrosWalker;
				wp_nav_menu( array(
						'theme_location'	=>	$mobile_location,
						'menu_class'		=>	'lordcros-mobile-nav',
						'container'			=>	false,
						'walker'			=>	$walker
				) );

				echo '</div>';
			}

			?>
		</div>

		<?php
	}
}
