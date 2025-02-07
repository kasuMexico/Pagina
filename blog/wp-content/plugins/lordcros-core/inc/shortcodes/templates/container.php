<?php
/**
 * Container Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lc_container]
function lordcros_core_shortcode_container( $atts, $content = null ) {

	extract( shortcode_atts( array(
			'extra_class'		=> '',
			'animation'			=> '',
			'animation_delay'	=> 1,
			'css'				=>	''
		), $atts, 'lordcros_contaier' ) );
	
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	$content_class = 'container';
	if ( ! empty( $animation_classes ) ) {
		$content_class .= ' ' . $animation_classes;
	}

	if ( ! empty( $extra_class ) ) {
		$content_class .= ' ' . $extra_class;
	}

	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= ' ' . vc_shortcode_custom_css_class( $css );
	}
	
	$html = '<div class="lordcros-shortcode-container ' . esc_attr( $content_class ) . '" style="animation-delay: ' . esc_attr( $animation_delay ) . 's;">';
	$html .= do_shortcode( $content );
	$html .= '</div>';
	
	return $html;
}

add_shortcode( 'lc_container', 'lordcros_core_shortcode_container' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_container() {

	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'				=> esc_html__( 'Theme Container', 'lordcros-core' ),
		'base'				=> 'lc_container',
		'icon'				=> 'lordcros-js-composer',
    	'category'			=> esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'		=> esc_html__( 'Add a Container on your page.', 'lordcros-core' ),
		'is_container'		=> true,
		"js_view"			=> 'VcColumnView',
		'params'			=> array(
			$animation_style,
			$animation_delay,
			$extra_class,
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Custom CSS', 'lordcros-core' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'lordcros-core' )
			),
		)
	) );

	if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
		class WPBakeryShortCode_Lc_Container extends WPBakeryShortCodesContainer {}
	}

}

lordcros_core_vc_shortcode_container();