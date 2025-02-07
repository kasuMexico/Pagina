<?php  
/**
 *	Add & Custom Js Composer Element
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

// Removing unwanted shortcodes
vc_remove_element( 'vc_progress_bar' );
vc_remove_element( 'vc_pie' );
vc_remove_element( 'vc_round_chart' );
vc_remove_element( 'vc_line_chart' );
vc_remove_element( 'product' );
vc_remove_element( 'products' );

// Extra Param to "Row" Element
vc_add_param( 'vc_row', array(
	'type'			=>	'dropdown',
	'heading'		=>	esc_html__( 'Wrap as a Container', 'lordcros-core' ),
	'param_name'	=>	'wrap_container',
	'value'			=>	array(
		__( 'Enable', 'lordcros-core' ) => 'true',
		__( 'Disable', 'lordcros-core' ) => 'false',
	),
	'std'			=>	'false',
	'group'			=>	esc_html__( 'Container Option', 'lordcros-core' ),
	'admin_label'	=>	true,
) );

vc_add_param( 'vc_row_inner', array(
	'type'			=>	'dropdown',
	'heading'		=>	esc_html__( 'Wrap as a Container', 'lordcros-core' ),
	'param_name'	=>	'wrap_container',
	'value'			=>	array(
		__( 'Enable', 'lordcros-core' ) => 'true',
		__( 'Disable', 'lordcros-core' ) => 'false',
	),
	'std'			=>	'false',
	'group'			=>	esc_html__( 'Container Option', 'lordcros-core' ),
	'admin_label'	=>	true,
) );

// Extra Options to "Video" Element
vc_add_params( 'vc_video', array(
	array(
		'type'			=>	'checkbox',
		'heading'		=>	esc_html__( 'Add placeholder to video', 'lordcros-core' ),
		'param_name'	=>	'image_placeholder_switch',
		'group'			=>	esc_html__( 'Extra Options', 'lordcros-core' ),
		'value'			=>	array(
			esc_html__( 'Yes, please', 'lordcros-core' ) => 'yes'
		)
	),

	array(
		'type'			=>	'attach_image',
		'heading'		=>	esc_html__( 'Placeholder Image', 'lordcros-core' ),
		'param_name'	=>	'placeholder_img',
		'description'	=>	esc_html__( 'Select image from media library.', 'lordcros-core' ),
		'group'			=>	esc_html__( 'Extra Options', 'lordcros-core' ),
		'dependency'	=>	array(
			'element'	=>	'image_placeholder_switch',
			'value'		=>	array( 'yes' )
		)
	),

	array(
		'type'			=>	'checkbox',
		'heading'		=>	esc_html__( 'Ratio Reverse', 'lordcros-core' ),
		'param_name'	=>	'ratio_reverse',
		'value'			=>	array(
			esc_html__( 'Yes', 'lordcros-core' )	=>	'yes'
		),
		'group'			=>	esc_html__( 'Extra Options', 'lordcros-core' )
	),
) );

// vc_video placeholder image mask
if ( ! function_exists( 'lordcros_video_placeholder' ) ) {
	function lordcros_video_placeholder( $output, $obj, $attr ) {

		if ( ! empty( $attr['image_placeholder_switch'] ) ) {
			$img_id = $attr['placeholder_img'];
			$image = wp_get_attachment_url( $img_id );

			$output = preg_replace_callback('/wpb_video_wrapper.*?>/',
				function ( $matches ) use( $image ) {
				   return strtolower( $matches[0] . '<div class="lordcros-video-placeholder-wrapper"><div class="lordcros-video-placeholder" style="background-image:url(' . esc_url( $image ) . ')";></div><div class="button-play"><i class="lordcros lordcros-play"></i></div></div>' );
				}, $output );
		}

		return $output;

	}
}

add_filter( 'vc_shortcode_output', 'lordcros_video_placeholder', 10, 3 );


// Add a custom icon set from icomoon to visual composer vc_icon shortcode
function lordcros_core_icon_array() {
	return array(
		array( 'lordcros lordcros-cancel'				=>	'cancel' ),
		array( 'lordcros lordcros-sun'					=>	'sun' ),
		array( 'lordcros lordcros-arrow-right'			=>	'arrow-right' ),
		array( 'lordcros lordcros-search'				=>	'search' ),
		array( 'lordcros lordcros-bell'					=>	'bell' ),
		array( 'lordcros lordcros-arrow-left'			=>	'arrow-left' ),
		array( 'lordcros lordcros-play'					=>	'play' ),
		array( 'lordcros lordcros-arrow-top'			=>	'arrow-top' ),
		array( 'lordcros lordcros-arrow-down'			=>	'arrow-down' ),
		array( 'lordcros lordcros-coffee-cup'			=>	'coffee-cup' ),
		array( 'lordcros lordcros-leaf'					=>	'leaf' ),
		array( 'lordcros lordcros-send'					=>	'send' ),
		array( 'lordcros lordcros-angle-down'			=>	'angle-down' ),
		array( 'lordcros lordcros-angle-up'				=>	'angle-up' ),
		array( 'lordcros lordcros-angle-left'			=>	'angle-left' ),
		array( 'lordcros lordcros-angle-right'			=>	'angle-right' ),
		array( 'lordcros lordcros-swim'					=>	'swim' ),
		array( 'lordcros lordcros-placeholder'			=>	'placeholder' ),
		array( 'lordcros lordcros-call'					=>	'call' ),
		array( 'lordcros lordcros-clock'				=>	'clock' ),
		array( 'lordcros lordcros-user'					=>	'user' ),
		array( 'lordcros lordcros-plans'				=>	'plans' ),
		array( 'lordcros lordcros-phone'				=>	'phone' ),
		array( 'lordcros lordcros-mouse'				=>	'mouse' ),
		array( 'lordcros lordcros-wifi'					=>	'wifi' ),
		array( 'lordcros lordcros-airplane'				=>	'airplane' ),
		array( 'lordcros lordcros-parking'				=>	'parking' ),
		array( 'lordcros lordcros-zoom-in'				=>	'zoom-in' ),
		array( 'lordcros lordcros-bed'					=>	'bed' ),
		array( 'lordcros lordcros-users'				=>	'users' ),
		array( 'lordcros lordcros-sunset'				=>	'sunset' ),
		array( 'lordcros lordcros-consulting-message'	=>	'consulting-message' ),
		array( 'lordcros lordcros-profile'				=>	'profile' ),
		array( 'lordcros lordcros-location'				=>	'location' ),
		array( 'lordcros lordcros-phone-call'			=>	'phone-call' ),
		array( 'lordcros lordcros-fax'					=>	'fax' ),
		array( 'lordcros lordcros-mail'					=>	'mail' ),
		array( 'lordcros lordcros-check'				=>	'check' ),
		array( 'lordcros lordcros-television'			=>	'television' ),
		array( 'lordcros lordcros-half-moon'			=>	'half-moon' ),
		array( 'lordcros lordcros-clound-sun'			=>	'clound-sun' ),
		array( 'lordcros lordcros-cloudy-night'			=>	'cloudy-night' ),
		array( 'lordcros lordcros-cloud'				=>	'cloud' ),
		array( 'lordcros lordcros-cloudy'				=>	'cloudy' ),
		array( 'lordcros lordcros-rain'					=>	'rain' ),
		array( 'lordcros lordcros-rain-sun'				=>	'rain-sun' ),
		array( 'lordcros lordcros-rain-night'			=>	'rain-night' ),
		array( 'lordcros lordcros-thunderstorm'			=>	'thunderstorm' ),
		array( 'lordcros lordcros-snow'					=>	'snow' ),
		array( 'lordcros lordcros-fog'					=>	'fog' ),
		array( 'lordcros lordcros-long-arrow-left'		=>	'long-arrow-left' ),
		array( 'lordcros lordcros-long-arrow-right'		=>	'long-arrow-right' ),
		array( 'lordcros lordcros-price-tag'			=>	'price-tag' ),
		array( 'lordcros lordcros-pause-lines'			=>	'pause-lines' ),
		array( 'lordcros lordcros-play-button'			=>	'play-button' ),

		array( 'lordcros lordcros-puzzle'				=>	'puzzle' ),
		array( 'lordcros lordcros-settings'				=>	'settings' ),
		array( 'lordcros lordcros-logout'				=>	'logout' ),
		array( 'lordcros lordcros-shopping-bag'			=>	'shopping-bag' ),
		array( 'lordcros lordcros-two-users'			=>	'two-users' ),
		array( 'lordcros lordcros-hotel-bell'			=>	'hotel-bell' ),
		array( 'lordcros lordcros-calendar'				=>	'calendar' )
	);
}
add_filter( 'vc_iconpicker-type-lordcrosicons', 'lordcros_core_icon_array' );

add_action( 'vc_base_register_front_css', 'lordcros_core_vc_iconpicker_base_register_css' );
add_action( 'vc_base_register_admin_css', 'lordcros_core_vc_iconpicker_base_register_css' );
function lordcros_core_vc_iconpicker_base_register_css(){
    wp_register_style( 'lordcros-vc-custom-font', LORDCROS_CORE_PLUGIN_URL . '/inc/js_composer/vc_custom_font/font-style.css' );
}

add_action( 'vc_backend_editor_enqueue_js_css', 'lordcros_core_vc_iconpicker_editor_jscss' );
add_action( 'vc_frontend_editor_enqueue_js_css', 'lordcros_core_vc_iconpicker_editor_jscss' );
function lordcros_core_vc_iconpicker_editor_jscss(){
    wp_enqueue_style( 'lordcros-vc-custom-font' );
}

add_action('vc_enqueue_font_icon_element', 'lordcros_enqueue_font_icomoon');
function lordcros_enqueue_font_icomoon( $font ){
    switch ( $font ) {
        case 'lordcrosicons' : wp_enqueue_style( 'lordcros-vc-custom-font' );
    }
}