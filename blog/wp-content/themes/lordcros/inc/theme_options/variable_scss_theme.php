<?php
/**
 * Theme Option Value Compile
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function lordcros_hex2rgb($hex) {
	$hex = str_replace( "#", "", $hex );
				
	if( strlen( $hex ) == 3 ) {
		$r = hexdec( substr( $hex, 0, 1 ).substr( $hex, 0, 1 ) );
		$g = hexdec( substr( $hex, 1, 1 ).substr( $hex, 1, 1 ) );
		$b = hexdec( substr( $hex, 2, 1 ).substr( $hex, 2, 1 ) );
	} else {
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
	}
	$rgb = array( $r, $g, $b );
	return implode( ",", $rgb );
}

/* Theme Options Values */
$primary_color = lordcros_get_opt( 'primary_color' );
$secondary_color = lordcros_get_opt( 'secondary_color', '#252c41' );
$body_font = lordcros_get_opt( 'typography-body' );
$nav_font = lordcros_get_opt( 'typography-nav' );
$h1_font = lordcros_get_opt( 'typography-h1' );
$h2_font = lordcros_get_opt( 'typography-h2' );
$h3_font = lordcros_get_opt( 'typography-h3' );
$h4_font = lordcros_get_opt( 'typography-h4' );
$h5_font = lordcros_get_opt( 'typography-h5' );
$h6_font = lordcros_get_opt( 'typography-h6' );
$topbar_bg = lordcros_get_opt( 'topbar_bg_color', '#fff' );
$topbar_txt = lordcros_get_opt( 'topbar_txt_color', '#878787' );
$header_bg = lordcros_get_opt( 'header_bg_color', '#fff' );
$header_txt = lordcros_get_opt( 'header_txt_color', '#262626' );
$submenu_bg = lordcros_get_opt( 'submenu_bg_clr', '#fff' );
$header_logo_height = lordcros_get_opt( 'logo_height', 69 );
$sticky_header_logo_height = lordcros_get_opt( 'sticky_logo_height', 50 );
$sticky_header_bg = lordcros_get_opt( 'sitcky_header_bg_clr', '#fff' );
$sticky_header_txt = lordcros_get_opt( 'sticky_header_menu_clr', '#252c41' );
$footer_bg = lordcros_get_opt( 'footer_bg_clr', '#151b2e' );
$footer_widget_title = lordcros_get_opt( 'footer_widget_title_clr', '#fff' );
$footer_txt = lordcros_get_opt( 'footer_txt_clr', '#787d8b' );
$footer_bottom_bg = lordcros_get_opt( 'footer_bottom_bg_clr', '#0e1222' );
$footer_bottom_txt = lordcros_get_opt( 'footer_bottom_txt_clr', '#787d8b' );
$mobile_screen_size = lordcros_get_opt( 'mobile_screen_size', 960 );
?>
// Site Skin Color
$site_primary_color: <?php if ( isset( $primary_color ) && ( '' != $primary_color ) ) { echo '' . $primary_color; } else { echo '#ff6d5e'; } ?>;
$site_secondary_color: <?php if ( isset( $secondary_color ) && ( '' != $secondary_color ) ) { echo '' . $secondary_color; } else { echo '#252c41'; } ?>;
$site_secondary_color_disabled: <?php echo 'rgba(' . lordcros_hex2rgb( $secondary_color ) . ', .7)'; ?>;

// Typography Settings
$body_font_family: <?php if ( isset( $body_font['font-family'] ) && '' != $body_font['font-family'] ) { echo '' . $body_font['font-family']; } else { echo 'inherit'; } ?>;
$body_font_weight: <?php if ( isset( $body_font['font-weight'] ) && '' != $body_font['font-weight'] ) { echo '' . $body_font['font-weight']; }
	else { echo 'initial'; } ?>;
$body_font_size: <?php if ( isset( $body_font['font-size'] ) && '' != $body_font['font-size'] ) { echo '' . $body_font['font-size']; }
	else { echo '15px'; } ?>;
$body_font_line_height: <?php if ( isset( $body_font['line-height'] ) && '' != $body_font['line-height'] ) { echo '' . $body_font['line-height']; } else { echo 'inherit'; } ?>;
$body_font_color: <?php if ( isset( $body_font['color'] ) && '' != $body_font['color'] ) { echo '' . $body_font['color']; } else { echo 'inherit'; } ?>;

$nav_font_family: <?php if ( isset( $nav_font['font-family'] ) && '' != $nav_font['font-family'] ) { echo '' . $nav_font['font-family']; } else { echo 'inherit'; } ?>;
$nav_font_weight: <?php if ( isset( $nav_font['font-weight'] ) && '' != $nav_font['font-weight'] ) { echo '' . $nav_font['font-weight']; }
	else { echo 'initial'; } ?>;
$nav_font_size: <?php if ( isset( $nav_font['font-size'] ) && '' != $nav_font['font-size'] ) { echo '' . $nav_font['font-size']; }
	else { echo '15px'; } ?>;
$nav_font_color: <?php if ( isset( $nav_font['color'] ) && '' != $nav_font['color'] ) { echo '' . $nav_font['color']; } else { echo 'inherit'; } ?>;
$nav_menu_border_clr: <?php if ( isset( $nav_font['color'] ) && ( '' != $nav_font['color'] ) ) { echo 'rgba(' . lordcros_hex2rgb( $nav_font['color'] ) . ', .2)'; } else { echo 'rgba(38, 38, 38, .2)'; } ?>;

$h1_font_family: <?php if ( isset( $h1_font['font-family'] ) && '' != $h1_font['font-family'] ) { echo '' . $h1_font['font-family']; } else { echo 'inherit'; } ?>;
$h1_font_weight: <?php if ( isset( $h1_font['font-weight'] ) && '' != $h1_font['font-weight'] ) { echo '' . $h1_font['font-weight']; }
	else { echo 'initial'; } ?>;
$h1_font_size: <?php if ( isset( $h1_font['font-size'] ) && '' != $h1_font['font-size'] ) { echo '' . $h1_font['font-size']; }
	else { echo '15px'; } ?>;
$h1_font_line_height: <?php if ( isset( $h1_font['line-height'] ) && '' != $h1_font['line-height'] ) { echo '' . $h1_font['line-height']; } else { echo 'inherit'; } ?>;
$h1_font_color: <?php if ( isset( $h1_font['color'] ) && '' != $h1_font['color'] ) { echo '' . $h1_font['color']; } else { echo 'inherit'; } ?>;

$h2_font_family: <?php if ( isset( $h2_font['font-family'] ) && '' != $h2_font['font-family'] ) { echo '' . $h2_font['font-family']; } else { echo 'inherit'; } ?>;
$h2_font_weight: <?php if ( isset( $h2_font['font-weight'] ) && '' != $h2_font['font-weight'] ) { echo '' . $h2_font['font-weight']; }
	else { echo 'initial'; } ?>;
$h2_font_size: <?php if ( isset( $h2_font['font-size'] ) && '' != $h2_font['font-size'] ) { echo '' . $h2_font['font-size']; }
	else { echo '15px'; } ?>;
$h2_font_line_height: <?php if ( isset( $h2_font['line-height'] ) && '' != $h2_font['line-height'] ) { echo '' . $h2_font['line-height']; } else { echo 'inherit'; } ?>;
$h2_font_color: <?php if ( isset( $h2_font['color'] ) && '' != $h2_font['color'] ) { echo '' . $h2_font['color']; } else { echo 'inherit'; } ?>;

$h3_font_family: <?php if ( isset( $h3_font['font-family'] ) && '' != $h3_font['font-family'] ) { echo '' . $h3_font['font-family']; } else { echo 'inherit'; } ?>;
$h3_font_weight: <?php if ( isset( $h3_font['font-weight'] ) && '' != $h3_font['font-weight'] ) { echo '' . $h3_font['font-weight']; }
	else { echo 'initial'; } ?>;
$h3_font_size: <?php if ( isset( $h3_font['font-size'] ) && '' != $h3_font['font-size'] ) { echo '' . $h3_font['font-size']; }
	else { echo '15px'; } ?>;
$h3_font_line_height: <?php if ( isset( $h3_font['line-height'] ) && '' != $h3_font['line-height'] ) { echo '' . $h3_font['line-height']; } else { echo 'inherit'; } ?>;
$h3_font_color: <?php if ( isset( $h3_font['color'] ) && '' != $h3_font['color'] ) { echo '' . $h3_font['color']; } else { echo 'inherit'; } ?>;

$h4_font_family: <?php if ( isset( $h4_font['font-family'] ) && '' != $h4_font['font-family'] ) { echo '' . $h4_font['font-family']; } else { echo 'inherit'; } ?>;
$h4_font_weight: <?php if ( isset( $h4_font['font-weight'] ) && '' != $h4_font['font-weight'] ) { echo '' . $h4_font['font-weight']; }
	else { echo 'initial'; } ?>;
$h4_font_size: <?php if ( isset( $h4_font['font-size'] ) && '' != $h4_font['font-size'] ) { echo '' . $h4_font['font-size']; }
	else { echo '15px'; } ?>;
$h4_font_line_height: <?php if ( isset( $h4_font['line-height'] ) && '' != $h4_font['line-height'] ) { echo '' . $h4_font['line-height']; } else { echo 'inherit'; } ?>;
$h4_font_color: <?php if ( isset( $h4_font['color'] ) && '' != $h4_font['color'] ) { echo '' . $h4_font['color']; } else { echo 'inherit'; } ?>;

$h5_font_family: <?php if ( isset( $h5_font['font-family'] ) && '' != $h5_font['font-family'] ) { echo '' . $h5_font['font-family']; } else { echo 'inherit'; } ?>;
$h5_font_weight: <?php if ( isset( $h5_font['font-weight'] ) && '' != $h5_font['font-weight'] ) { echo '' . $h5_font['font-weight']; }
	else { echo 'initial'; } ?>;
$h5_font_size: <?php if ( isset( $h5_font['font-size'] ) && '' != $h5_font['font-size'] ) { echo '' . $h5_font['font-size']; }
	else { echo '15px'; } ?>;
$h5_font_line_height: <?php if ( isset( $h5_font['line-height'] ) && '' != $h5_font['line-height'] ) { echo '' . $h5_font['line-height']; } else { echo 'inherit'; } ?>;
$h5_font_color: <?php if ( isset( $h5_font['color'] ) && '' != $h5_font['color'] ) { echo '' . $h5_font['color']; } else { echo 'inherit'; } ?>;

$h6_font_family: <?php if ( isset( $h6_font['font-family'] ) && '' != $h6_font['font-family'] ) { echo '' . $h6_font['font-family']; } else { echo 'inherit'; } ?>;
$h6_font_weight: <?php if ( isset( $h6_font['font-weight'] ) && '' != $h6_font['font-weight'] ) { echo '' . $h6_font['font-weight']; }
	else { echo 'initial'; } ?>;
$h6_font_size: <?php if ( isset( $h6_font['font-size'] ) && '' != $h6_font['font-size'] ) { echo '' . $h6_font['font-size']; }
	else { echo '15px'; } ?>;
$h6_font_line_height: <?php if ( isset( $h6_font['line-height'] ) && '' != $h6_font['line-height'] ) { echo '' . $h6_font['line-height']; } else { echo 'inherit'; } ?>;
$h6_font_color: <?php if ( isset( $h6_font['color'] ) && '' != $h6_font['color'] ) { echo '' . $h6_font['color']; } else { echo 'inherit'; } ?>;

// Main Header Settings
$topbar_bg_clr: <?php echo '' . $topbar_bg; ?>;
$topbar_txt_clr: <?php echo '' . $topbar_txt; ?>;
$header_bg_clr: <?php echo '' . $header_bg; ?>;
$header_txt_clr: <?php echo '' . $header_txt; ?>;
$header_split_border: <?php echo 'rgba(' . lordcros_hex2rgb( $header_txt ) . ', .2)'; ?>;
$submenu_bg_clr: <?php echo '' . $submenu_bg; ?>;
$header_logo_height_val: <?php echo '' . $header_logo_height . 'px'; ?>;
$sticky_logo_height_val: <?php echo '' . $sticky_header_logo_height . 'px'; ?>;
$sticky_header_bg_clr: <?php echo '' . $sticky_header_bg; ?>;
$sticky_header_txt_clr: <?php echo '' . $sticky_header_txt; ?>;
$sticky_header_split_border: <?php echo 'rgba(' . lordcros_hex2rgb( $sticky_header_txt ) . ', .2)'; ?>;
$footer_bg_clr: <?php echo '' . $footer_bg; ?>;
$footer_widget_title_clr: <?php echo '' . $footer_widget_title; ?>;
$footer_txt_clr: <?php echo '' . $footer_txt; ?>;
$footer_bottom_bg_clr: <?php echo '' . $footer_bottom_bg; ?>;
$footer_bottom_txt_clr: <?php echo '' . $footer_bottom_txt; ?>;
$footer_split_border: <?php echo 'rgba(' . lordcros_hex2rgb( $footer_txt ) . ', .2)'; ?>;
$mobile_enable_width: <?php echo '' . $mobile_screen_size . 'px'; ?>;