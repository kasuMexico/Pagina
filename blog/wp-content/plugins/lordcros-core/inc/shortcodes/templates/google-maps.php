<?php
/**
 *	Google Maps Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lc_google_map]
function lordcros_core_shortcode_google_map( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'map_width' 				=>	'100%',
		'map_height' 				=>	'340px',
		'map_type'   				=>	'ROADMAP',
		'map_center_posi' 			=>	'',
		'map_zoom' 					=>	'12',
		'map_type_control' 			=>	'true',
		'map_navigation_control'	=>	'true',
		'map_street_view_control' 	=>	'true',
		'map_custom_icon' 			=>	'',
		'map_draggale_control' 		=>	'true',
		'map_styled' 				=>	'default',
		'text_enable'				=>	'false',
		'extra_class'				=>	'',
		'animation'					=>	'',
		'animation_delay'			=>	''
	), $atts, 'lordcros_google_maps' ) );

	$id = rand( 100, 9999 );
	$shortcode_google_map_id = uniqid( 'lordcros-google-map-' . $id );

	$html = $custom_marker = $styles = '';

	$map_center = '41.850033, -87.650052';
	if ( ! empty( $map_center_posi ) ) {
		$map_center = $map_center_posi;
	}

	$map_types = array( 'ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN' );
	$map_type = strtoupper( $map_type );
	if ( empty( $map_type) || ! in_array( $map_type, $map_types ) ) $map_type = 'ROADMAP';

	if ( is_numeric( $map_custom_icon ) ) {
		$icon_src = wp_get_attachment_image_src( $map_custom_icon, 'lordcros-map-marker' );
		$custom_marker = 'icon: "' . $icon_src[0] . '"';
	}

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	global $google_default, $smooth_dark, $ultra_light, $subtle_gray, $shades_grey, $blue_water, $blue_dark_sea, $midnight_commander;

	$map_seleted_style = 'default';
    if ( $map_styled == 'default' ) {
        $map_seleted_style = $google_default;
    } elseif ( $map_styled == 'ultra_light' ) {
      	$map_seleted_style = $ultra_light;
    } elseif ( $map_styled == 'subtle_gray' ) {
      	$map_seleted_style = $subtle_gray;
    } elseif ( $map_styled == 'shades_grey' ) {
      	$map_seleted_style = $shades_grey;
    } elseif ( $map_styled == 'blue_water' ) {
      	$map_seleted_style = $blue_water;
    } elseif ( $map_styled == 'blue_dark_sea' ) {
      	$map_seleted_style = $blue_dark_sea;
    } elseif ( $map_styled == 'midnight_commander' ) {
    	$map_seleted_style = $midnight_commander;
    } elseif ( $map_styled == 'smooth_dark' ) {
    	$map_seleted_style = $smooth_dark;
    }

	$html .= '<div class="shortcode-google-maps ' . esc_attr( $extra_class ) . '">';
		if ( ! empty( $text_enable ) && 'true' == $text_enable ) {
			$html .= '<div class="google-map-text">' . do_shortcode( $content ) . '</div>';
		}

		$html .= '<div id="' . esc_attr( $shortcode_google_map_id ) . '" class="google-map-inner ' . $animation_classes . '" style="animation-delay: ' . $animation_delay . 's; width: ' . $map_width . '; height: ' . $map_height . ';"></div>';
	$html .= '</div>';

	$html .= lordcros_core_google_map( $shortcode_google_map_id, $map_seleted_style, $custom_marker, $map_type, $map_center, $map_zoom, $map_type_control, $map_navigation_control, $map_street_view_control, $map_draggale_control );

	return $html;
}

add_shortcode( 'lc_google_map', 'lordcros_core_shortcode_google_map' );

// WPBakery
function lordcros_core_vc_shortcode_google_map() {
	$animation_style = lordcros_core_animation_style_field();

	$animation_delay = lordcros_core_animation_delay_field();

	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Google Map', 'lordcros-core' ),
		'base'			=>	'lc_google_map',
		'icon'			=>	'lordcros-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'	=>	esc_html__( 'Add Google Map on currecnt page.', 'lordcros-core' ),
		'params'      	=> array(
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Width (in %)', 'lordcros-core' ),
				'value'     	=>	'100%',
				'param_name'	=>	'map_width',
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Height (in px)', 'lordcros-core' ),
				'value'			=>	'340px',
				'param_name'	=>	'map_height',
			),
			array(
				'type'       	=>	'dropdown',
				'heading'    	=>	esc_html__( 'Map Type', 'lordcros-core' ),
				'param_name' 	=>	'map_type',
				'admin_label'	=>	false,
				'value'      	=>	array(
					esc_html__( 'Roadmap', 'lordcros-core' )	=> 'ROADMAP',
					esc_html__( 'Satellite', 'lordcros-core' )	=> 'SATELLITE',
					esc_html__( 'Hybrid', 'lordcros-core' )		=> 'HYBRID',
					esc_html__( 'Terrain', 'lordcros-core' )	=> 'TERRAIN',
				),
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Map Center Position', 'lordcros-core' ),
				'description'	=>	esc_html__( 'input Latitude & Longitude like this: 41.850033, -87.650052', 'lordcros-core' ),
				'param_name'	=>	'map_center_posi',
				'admin_label'	=>	false,
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Map Zoom', 'lordcros-core' ),
				'value'			=>	12,
				'save_always'	=>	true,
				'min'			=>	1,
				'max'			=>	20,
				'param_name'  	=>	'map_zoom',
				'admin_label'	=>	false,
				'description' 	=>	esc_html__( 'Max:20 Min:1', 'lordcros-core' ),
			),
			array(
				'type'       	=>	'dropdown',
				'heading'    	=>	esc_html__( 'Map Type Control', 'lordcros-core' ),
				'param_name' 	=>	'map_type_control',
				'admin_label'	=>	false,
				'value'      	=> 	array(
					esc_html__( 'Enable', 'lordcros-core' ) 	=> 'true',
					esc_html__( 'Disable', 'lordcros-core' )	=> 'false',
				),
				'group' 		=> esc_html__( 'Advanced', 'lordcros-core' ),
			),
			array(
				'type'       	=> 	'dropdown',
				'heading'    	=> 	esc_html__( 'Map Navigation Control', 'lordcros-core' ),
				'param_name' 	=> 	'map_navigation_control',
				'admin_label'	=>	false,
				'value'      	=> 	array(
					esc_html__( 'Enable', 'lordcros-core' ) 	=> 'true',
					esc_html__( 'Disable', 'lordcros-core' ) 	=> 'false',
				),
				'group' 		=> esc_html__( 'Advanced', 'lordcros-core' ),
			),
			array(
				'type'       	=> 	'dropdown',
				'heading'    	=> 	esc_html__( 'Map Street View Control', 'lordcros-core' ),
				'admin_label'	=>	false,
				'param_name' 	=> 	'map_street_view_control',
				'value'      	=> 	array(
					esc_html__( 'Enable', 'lordcros-core' ) 	=> 'true',
					esc_html__( 'Disable', 'lordcros-core' ) 	=> 'false',
				),
				'group' 		=> esc_html__( 'Advanced', 'lordcros-core' ),
			),
			array(
				'type'       	=> 	'dropdown',
				'heading'    	=> 	esc_html__( 'Map Draggable Control', 'lordcros-core' ),
				'param_name' 	=> 	'map_draggale_control',
				'admin_label'	=>	false,
				'value'      	=> 	array(
					esc_html__( 'Enable', 'lordcros-core' )		=> 'true',
					esc_html__( 'Disable', 'lordcros-core' ) 	=> 'false',
				),
				'group' 		=> esc_html__( 'Advanced', 'lordcros-core' ),
			),
			array(
				'type'       	=> 	'dropdown',
				'heading'    	=> 	esc_html__( 'Marker/Point icon', 'lordcros-core' ),
				'param_name' 	=> 	'map_marker_icon',
				'admin_label'	=>	false,
				'value'      	=> 	array(
					esc_html__( 'Use Google Default', 'lordcros-core' ) => 'default',
					esc_html__( 'Upload Custom Icon', 'lordcros-core' ) => 'custom_icon',
				),
				'group' 		=> esc_html__( 'Style', 'lordcros-core' ),
			),
			array(
				'type'  		=> 	'attach_image',
				'heading' 		=> 	esc_html__( 'Upload Image Icon:', 'lordcros-core' ),
				'param_name' 	=> 	'map_custom_icon',
				'admin_label'	=>	false,
				'description' 	=> 	esc_html__( 'Upload the custom image icon.', 'lordcros-core' ),
				'dependency' 	=> 	array( 
					'element' => 'map_marker_icon',
					'value'   => 'custom_icon'
				),
				'group' 		=> esc_html__( 'Style', 'lordcros-core' ),
			),
			array(
				'type'        	=> 	'lordcros_image_selection',
				'heading'     	=> 	esc_html__( 'Google Styled Map', 'lordcros-core' ),
				'param_name'  	=> 	'map_styled',
				'admin_label'	=>	false,
				'value'      	=>	array(
					esc_html__( 'Google Default Style', 'lordcros-core' ) 		=> 'default',
					esc_html__( 'Smooth Dark', 'lordcros-core' ) 				=> 'smooth_dark',
					esc_html__( 'Ultra Light with Labels', 'lordcros-core' )	=> 'ultra_light',
					esc_html__( 'Subtle Grayscale', 'lordcros-core' ) 			=> 'subtle_gray',
					esc_html__( 'Shades of Grey', 'lordcros-core' ) 			=> 'shades_grey',
					esc_html__( 'Blue Water', 'lordcros-core' ) 				=> 'blue_water',
					esc_html__( 'Blue Dark Sea', 'lordcros-core' ) 				=> 'blue_dark_sea',
					esc_html__( 'Midnight Commander', 'lordcros-core' ) 		=> 'midnight_commander',
				),
				'image_value'	=> array(
					'default'				=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/default-google-style.jpg',
					'smooth_dark'			=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/smooth-dark.jpg',
					'ultra_light'			=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/ultra-light.jpg',
					'subtle_gray'			=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/subtle-gray.jpg',
					'shades_grey'			=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/shades-grey.jpg',
					'blue_water'			=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/blue-water.jpg',
					'blue_dark_sea'			=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/blue-dark-sea.jpg',
					'midnight_commander'	=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/midnight-commander.jpg'
				),
				'std'			=>	'default',
				'group' 		=>	esc_html__( 'Style', 'lordcros-core' ),
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Map Text Content', 'lordcros-core' ),
				'param_name'	=>	'text_enable',
				'value'			=>	array(
					esc_html__( 'Enable', 'lordcros-core' ) 	=> 'true',
					esc_html__( 'Disable', 'lordcros-core' ) 	=> 'false',
				),
				'std'			=>	'false',
				'group'			=>	esc_html__( 'Text', 'lordcros-core' )
			),
			array(
				'type'			=>	'textarea_html',
				'param_name'	=>	'content',
				'dependency'	=>	array(
					'element'	=>	'text_enable',
					'value'		=>	'true'
				),
				'description'	=>	esc_html__( 'Add Html content to show text on google map.', 'lordcros-core' ),
				'group'			=>	esc_html__( 'Text', 'lordcros-core' )
			),
			$animation_style,
			$animation_delay,
			$extra_class
		),
	) );
}

lordcros_core_vc_shortcode_google_map();