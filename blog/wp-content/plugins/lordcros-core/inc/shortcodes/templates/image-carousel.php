<?php
/**
 *	Image Carousel Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// [lc_image_carousel]
function lordcros_core_shortcode_image_carousel( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'title'				=>	'',
		'short_description'	=>	'',
		'carousel_images'	=>	'',
		'carousel_size'		=>	'',
		'carousel_column'	=>	3,
		'nav_speed'			=>	1000,
		'slider_nav'		=>	1,
		'nav_position'		=>	'default_pos', // default_pos, center_pos
		'dots_nav'			=>	0, //1, 0
		'dots_position'		=>	'bottom', // bottom, right
		'center_mode'		=>	0, //1, 0
		'slide_page'		=>	'',
		'slider_loop'		=>	'',
		'slider_auto'		=>	'',
		'item_gap'			=>	10,
		'nav_clr_scheme'	=>	'dark', // dark, light
		'animation'			=>	'',
		'animation_delay'	=>	1,
		'extra_class'		=>	''
	), $atts, 'lordcros_image_carousel' ) );

	$id = rand( 100, 9999 );
	$shortcode_carousel_id = uniqid( 'lordcros-image-carousel-' . $id );

	$carousel_classes = $html = $styles = '';

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	if ( ! empty( $animation_classes ) ) {
		$carousel_classes .= ' ' . $animation_classes;
	}
	if ( ! empty( $extra_class ) ) {
		$carousel_classes .= ' ' . $extra_class;
	}
	if ( ! empty( $nav_position ) ) {
		$carousel_classes .= ' nav-' . $nav_position;
	}
	if ( ! empty( $dots_nav ) && ! empty( $dots_position ) ) {
		$carousel_classes .= ' dots-position-' . $dots_position;
	}	
	if ( ! empty( $center_mode ) ) {
		$carousel_classes .= ' center-mode';
	}
	if ( ! empty( $nav_clr_scheme ) ) {
		$carousel_classes .= ' nav-color-scheme-' . $nav_clr_scheme;
	}

	$item_gap = intval( $item_gap );
	if ( $item_gap > 30 ) {
		$item_gap = 30;
	}

	if ( $nav_position == 'center_pos' ) {
		$item_gap = 100;	
	}

	$html .= '<div id="' . esc_attr( $shortcode_carousel_id ) . '" class="lordcros-shortcode-element lordcros-shortcode-img-carousel ' . esc_attr( $carousel_classes ) . '" style="animation-delay: ' . $animation_delay . 's;">';
		if ( ! empty( $title ) ) {
			$html .= '<h2 class="shortcode-title">' . esc_html( $title ) . '</h2>';
		}

		if ( ! empty( $short_description ) ) {
			$html .= '<p class="shortcode-description">' . $short_description . '</p>';
		}

		$html .= '<div class="image-carousel-inner owl-carousel">';
			if ( ! empty ( $carousel_images ) ) {
				$carousel_images_lists = explode( ',', $carousel_images );

				foreach ( $carousel_images_lists as $carousel_images_list ) {
					$html .= lordcros_core_get_image( $carousel_images_list, $carousel_size );
				}
			}
		$html .= '</div>';
	$html .= '</div>';

	$html .= lordcros_core_carousel_layout( $shortcode_carousel_id, $carousel_column, $nav_speed, $slider_nav, $dots_nav, $slide_page, $slider_auto, $slider_loop, $center_mode, 0, $item_gap );

	return $html;
}

add_shortcode( 'lc_image_carousel', 'lordcros_core_shortcode_image_carousel' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_image_carousel() {

	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Image Carousel', 'lordcros-core' ),
		'base'			=>	'lc_image_carousel',
		'icon'			=>	'lordcros-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'	=>	esc_html__( 'Animated Carousel with Images.', 'lordcros-core' ),
		'params'		=>	array(
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Title', 'lordcros-core' ),
				'param_name'	=> 'title',
				'save_always'	=> true,
				'admin_label'	=> false,
			),
			array(
				'type'			=> 'textarea',
				'heading'		=> esc_html__( 'Description', 'lordcros-core' ),
				'param_name'	=> 'short_description',
				'save_always'	=> false,
			),
			array(
				'type'			=>	'attach_images',
				'heading'		=>	esc_html__( 'Images', 'lordcros-core' ),
				'param_name'	=>	'carousel_images',
				'class'			=>	'hide_in_vc_editor',
			),
			array(
				'type'				=>	'textfield',
				'heading'			=>	esc_html__( 'Carousel Size', 'lordcros-core' ),
				'param_name'		=>	'carousel_size',
				'admin_label'		=>	false,
				'description'		=>	esc_html__( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Example: 200x100 (Width x Height)).', 'lordcros-core' ),
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Carousel Per View', 'lordcros-core' ),
				'param_name'	=>	'carousel_column',
				'value'			=>	array(
					1, 2, 3, 4, 5, 6
				),
				'std'			=>	3
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Navigation Speed', 'lordcros-core' ),
				'param_name'	=>	'nav_speed',
				'std'			=>	1000
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Center Mode', 'lordcros-core' ),
				'param_name'	=>	'center_mode',
				'value'			=>	array(
					esc_html__( 'Yes', 'lordcros-core' )	=>	1,
					esc_html__( 'No', 'lordcros-core' )		=>	0
				),
				'std'			=>	0
			),
			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Slide By', 'lordcros-core' ),
				'param_name'	=>	'slide_page',
				'description'	=>	esc_html__( 'Navigation slide by per page items.', 'lordcros-core' ),
				'value'			=>	array(
					esc_html__( 'Yes, please', 'lordcros-core' )	=>	'yes'
				)
			),
			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Slider Loop', 'lordcros-core' ),
				'param_name'	=>	'slider_loop',
				'description'	=>	esc_html__( 'Enable loop mode.', 'lordcros-core' ),
				'value'			=>	array(
					esc_html__( 'Yes, please', 'lordcros-core' )	=>	'yes'
				)
			),
			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Slider AutoPlay', 'lordcros-core' ),
				'param_name'	=>	'slider_auto',
				'description'	=>	esc_html__( 'Enable autoplay mode.', 'lordcros-core' ),
				'value'			=>	array(
					esc_html__( 'Yes, please', 'lordcros-core' )	=>	'yes'
				)
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Image Item Gap', 'lordcros-core' ),
				'param_name'	=>	'item_gap',
				'description'	=>	esc_html__( 'Unit: px, Max value: 30', 'lordcros-core' ),
				'std'			=>	'10'
			),
			$animation_style,
			$animation_delay,
			$extra_class,
			array(
				'type'				=>	'dropdown',
				'heading'			=>	esc_html__( 'Slider Navigation', 'lordcros-core' ),
				'param_name'		=>	'slider_nav',
				'value'				=>	array(
					esc_html__( 'Show', 'lordcros-core' )	=>	1,
					esc_html__( 'Hide', 'lordcros-core' )	=>	0
				),
				'std'				=>	1,
				'edit_field_class'	=> 'vc_col-sm-7 vc_column',
				'group'				=>	esc_html__( 'Navigation Settings', 'lordcros-core' )
			),
			array(
				'type'				=>	'dropdown',
				'heading'			=>	esc_html__( 'Arrow Nav Position', 'lordcros-core' ),
				'param_name'		=>	'nav_position',
				'value'				=>	array(
					esc_html__( 'Default Position', 'lordcros-core' )	=>	'default_pos',
					esc_html__( 'Center Position', 'lordcros-core' )	=>	'center_pos'
				),
				'dependency'		=>	array(
					'element'		=>	'carousel_column',
					'value'			=>	array( '2', '4', '6' )
				),
				'std'				=>	'default_pos',
				'edit_field_class'	=> 'vc_col-sm-7 vc_column',
				'group'				=>	esc_html__( 'Navigation Settings', 'lordcros-core' )
			),
			array(
				'type'				=>	'dropdown',
				'heading'			=>	esc_html__( 'Dots Navigation', 'lordcros-core' ),
				'param_name'		=>	'dots_nav',
				'value'				=>	array(
					esc_html__( 'Show', 'lordcros-core' )	=>	1,
					esc_html__( 'Hide', 'lordcros-core' )	=>	0
				),
				'std'				=>	0,
				'edit_field_class'	=> 'vc_col-sm-7 vc_column',
				'group'				=>	esc_html__( 'Navigation Settings', 'lordcros-core' )
			),
			array(
				'type'				=>	'dropdown',
				'heading'			=>	esc_html__( 'Dots Position', 'lordcros-core' ),
				'param_name'		=>	'dots_position',
				'value'				=>	array(
					esc_html__( 'Bottom', 'lordcros-core' )	=>	'bottom',
					esc_html__( 'Right', 'lordcros-core' )	=>	'right',
				),
				'dependency'		=> array(
					'element'		=> 'dots_nav',
					'value'			=> array( '1' ),
				),
				'std'				=>	'bottom',
				'edit_field_class'	=> 'vc_col-sm-7 vc_column',
				'group'				=>	esc_html__( 'Navigation Settings', 'lordcros-core' )
			),
			array(
				'type'				=>	'dropdown',
				'heading'			=>	esc_html__( 'Navigation Color Scheme', 'lordcros-core' ),
				'param_name'		=>	'nav_clr_scheme',
				'value'				=>	array(
					esc_html__( 'Dark', 'lordcros-core' )	=>	'dark',
					esc_html__( 'Light', 'lordcros-core' )	=>	'light',
				),
				'std'				=>	'dark',
				'edit_field_class'	=> 'vc_col-sm-7 vc_column',
				'group'				=>	esc_html__( 'Navigation Settings', 'lordcros-core' )
			),
		),
	) );
}

lordcros_core_vc_shortcode_image_carousel();