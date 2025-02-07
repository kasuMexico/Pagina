<?php
/**
 *	Testimonial Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// [lc_testimonials]
function lordcros_core_shortcode_testimonials( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'title'				=>	'',
		'short_description'	=>	'',
		'nav_speed'			=>	1000,
		'slider_nav'		=>	1,
		'dots_nav'			=>	0,
		'slider_loop'		=>	'',
		'slider_auto'		=>	'',
		'animation'			=>	'',
		'animation_delay'	=>	'',
		'extra_class'		=>	''
	), $atts, 'lordcros_testimonials' ) );

	$id = rand( 100, 9999 );
	$shortcode_testimonial_id = uniqid( 'lordcros-testimonial-' . $id );

	$testimonial_classes = $html = $styles = '';

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	if ( ! empty( $animation_classes ) ) {
		$testimonial_classes .= ' ' . $animation_classes;
	}

	if ( ! empty( $extra_class ) ) {
		$testimonial_classes .= ' ' . $extra_class;
	}

	if ( $slider_nav == 0 ) {
		$testimonial_classes .= ' slider-nav-disabled';
	}

	if ( $dots_nav == 0 ) {
		$testimonial_classes .= ' slider-dots-disabled';
	}
	
	$html .= '<div id="' . esc_attr( $shortcode_testimonial_id ) . '" class="lordcros-shortcode-element lordcros-shortcode-testimonial ' . esc_attr( $testimonial_classes ) . '" style="animation-delay: ' . $animation_delay . 's;">';

		if ( ! empty( $title ) ) {
			$html .= '<h2 class="shortcode-title">' . esc_html( $title ) . '</h2>';
		}

		if ( ! empty( $short_description ) ) {
			$html .= '<p class="shortcode-description">' . $short_description . '</p>';
		}

		$html .= '<div class="testimonial-inner owl-carousel">';
			$html .= do_shortcode( $content );
		$html .= '</div>';
	$html .= '</div>';

	$html .= lordcros_core_carousel_layout( $shortcode_testimonial_id, 1, $nav_speed, 1, 1, false, $slider_auto, $slider_loop, 1, 0, 10 );

	return $html;
}

add_shortcode( 'lc_testimonials', 'lordcros_core_shortcode_testimonials' );

// [lc_testimonial]
function lordcros_core_shortcode_testimonial( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'author_name'			=>	'',
		'author_photo'			=>	'',
		'author_job'			=>	'',
		'author_link'			=>	'',
		'extra_class'			=>	''
	), $atts, 'lordcros_core_testimonial' ) );

	$author_link = ( '||' === $author_link ) ? '' : $author_link;
	$author_link = vc_build_link( $author_link );
	$a_href = '#'; $a_target = '_self';

	if ( strlen( $author_link['url'] ) > 0 ) {
		$a_href = $author_link['url'];
		$a_target = strlen( $author_link['target'] ) > 0 ? $author_link['target'] : '_self';
	}

	$html = '';

	$html .= '<div class="testimonial-inside ' . esc_attr( $extra_class ) . '">';
		$html .= '<div class="testimonial-author-info">';
			if ( ! empty( $author_photo ) && is_numeric( $author_photo ) ) {
				$html .= '<div class="author-photo">';
					$html .= lordcros_core_get_image( $author_photo, '90x90' );
				$html .= '</div>';
			}
			$html .= '<div class="author-name-job">';
				$html .= '<a class="author-link" href="' . esc_attr( $a_href ) . '" target="' . esc_attr( $a_target ) . '">';
					$html .= '<span class="author-name">' . esc_html( $author_name ) . '</span>';
				$html .= '</a>';
				$html .= '<p class="author-job">' . esc_html( $author_job ) . '</p>';
			$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="testimonial-blockquote">';
			$html .= '<span class="quote-icon"></span>';
			$html .= do_shortcode( $content );
		$html .= '</div>';

	$html .= '</div>';

	return $html;
}

add_shortcode( 'lc_testimonial', 'lordcros_core_shortcode_testimonial' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_testimonials() {
	
	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'				=>	esc_html__( 'Testimonials', 'lordcros-core' ),
		'base'				=>	'lc_testimonials',
		'icon'				=>	'lordcros-js-composer',
		'category'			=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'		=>	esc_html__( 'Show testimonials with carousel slider effect.', 'lordcros-core' ),
		'as_parent'			=>	array( 'only' => 'lc_testimonial' ),
		'params'			=>	array(
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
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Navigation Speed', 'lordcros-core' ),
				'param_name'	=>	'nav_speed',
				'std'			=>	1000
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Slider Navigation', 'lordcros-core' ),
				'param_name'	=>	'slider_nav',
				'value'			=>	array(
					esc_html__( 'Show', 'lordcros-core' )	=>	1,
					esc_html__( 'Hide', 'lordcros-core' )	=>	0
				),
				'std'			=>	1
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Dots Navigation', 'lordcros-core' ),
				'param_name'	=>	'dots_nav',
				'value'			=>	array(
					esc_html__( 'Show', 'lordcros-core' )	=>	1,
					esc_html__( 'Hide', 'lordcros-core' )	=>	0
				),
				'std'			=>	0
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
			$animation_style,
			$animation_delay,
			$extra_class
		),
		'js_view'			=>	'VcColumnView',
		'default_content'	=>	'[lc_testimonial][/lc_testimonial]'
	) );

	vc_map( array(
		'name'				=>	esc_html__( 'Testimonial', 'lordcros-core' ),
		'base'				=>	'lc_testimonial',
		'icon'				=>	'lordcros-js-composer',
		'category'			=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'as_child'			=>	array( 'only' => 'lc_testimonials' ),
		'params'			=>	array(
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Author Name', 'lordcros-core' ),
				'param_name'	=>	'author_name',
				'admin_label'	=>	true
			),			
			array(
				'type'			=>	'vc_link',
				'heading'		=>	esc_html__( 'Author Link', 'lordcros-core' ),
				'param_name'	=>	'author_link',
				'description'	=>	esc_html__( 'Enter URL if you want to add a link.', 'lordcros-core' ),
				'holder'		=>	'div',
				'class'			=>	'hide_in_vc_editor',
			),
			array(
				'type'			=>	'checkbox',
				'param_name'	=>	'enable_author_photo',
				'value'			=>	array(
					esc_html__( 'Enable Author Photo', 'lordcros-core' ) => 'yes'
				),
				'std'			=>	'yes',
				'admin_label'	=>	false
			),
			array(
				'type'			=>	'attach_image',
				'heading'		=>	esc_html__( 'Author Photo', 'lordcros-core' ),
				'param_name'	=>	'author_photo',
				'class'			=>	'hide_in_vc_editor',
				'dependency'	=>	array(
					'element'	=>	'enable_author_photo',
					'value'		=>	'yes'
				),
				'admin_label'	=>	false
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Author Job', 'lordcros-core' ),
				'param_name'	=>	'author_job',
				'admin_label'	=>	true
			),
			array(
				'type'			=>	'textarea_html',
				'heading'		=>	esc_html__( 'Testimonial Blockquote', 'lordcros-core' ),
				'param_name'	=>	'content',
				'admin_label'	=>	false
			),
			$extra_class
		)
	) );

	if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
	    class WPBakeryShortCode_Lc_Testimonials extends WPBakeryShortCodesContainer {
	    }
	}

	if ( class_exists( 'WPBakeryShortCode' ) ) {
	    class WPBakeryShortCode_Lc_Testimonial extends WPBakeryShortCode {
	    }
	}
}

lordcros_core_vc_shortcode_testimonials();