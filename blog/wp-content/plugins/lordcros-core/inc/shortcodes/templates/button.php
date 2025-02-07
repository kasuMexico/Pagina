<?php
/**
 * Button Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lc_button]
function lordcros_core_shortcode_button( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'btn_text'			=>	'',
		'btn_link'			=>	'',
		'button_color'		=>	'primary-clr',
		'button_style'		=>	'rectangle-style',
		'btn_size'			=>	'medium',
		'show_arrow'		=>	'',
		'extra_class'		=>	'',
		'animation'			=>	'',
		'animation_delay'	=>	'',
		'css'				=>	''
	), $atts, 'lordcros_button' ) );

	global $shortcode_inline_css;

	$id = rand( 100, 9999 );
	$button_id = uniqid( 'lordcros-button-' . $id );

	$content_class = $html = $styles = '';
	$button_classes = 'lordcros-btn';
	
	if ( empty( $btn_text ) ) {
		return;
	}

	$btn_link = ( '||' === $btn_link ) ? '' : $btn_link;
	$btn_link = vc_build_link( $btn_link );
	$a_href = '#'; $a_target = '_self';

	if ( strlen( $btn_link['url'] ) > 0 ) {
		$a_href = $btn_link['url'];
		$a_target = strlen( $btn_link['target'] ) > 0 ? $btn_link['target'] : '_self';
	}

	if ( ! empty( $button_color ) ) {
		$button_classes .= ' ' . $button_color;
	}

	if ( ! empty( $button_style ) ) {
		$button_classes .= ' ' . $button_style;
	}

	if ( ! empty( $btn_size ) ) {
		$button_classes .= ' ' . $btn_size;
	}

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	if ( ! empty( $animation_classes ) ) {
		$content_class .= ' ' . $animation_classes;
	}

	if ( ! empty( $extra_class ) ) {
		$content_class .= ' ' . $extra_class;
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= ' ' . vc_shortcode_custom_css_class( $css );
	}

	$html .= '<div id="' . esc_attr( $button_id ) . '" class="lordcros-shortcode-button ' . esc_attr( $content_class ) . '" style="animation-delay: ' . $animation_delay . 's;">';
		$html .= '<a href="' . esc_attr( $a_href ) . '" target="' . esc_attr( $a_target ) . '" class="' . esc_attr( $button_classes ) . '">';
			$html .= '<span>' . esc_html( $btn_text ) . '</span>';

			if ( true == $show_arrow || 'yes' === $show_arrow ) {
				$html .= '<i class="lordcros lordcros-arrow-right"></i>';
			}
		$html .= '</a>';
	$html .= '</div>';

	return $html;
}

add_shortcode( 'lc_button', 'lordcros_core_shortcode_button' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_button() {

	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Button', 'lordcros-core' ),
		'base'			=>	'lc_button',
		'icon'			=>	'lordcros-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'	=>	esc_html__( 'Show simple button with different styles.', 'lordcros-core' ),
		'params'		=>	array(
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Button Text', 'lordcros-core' ),
				'param_name'	=>	'btn_text',
				'holder'		=>	'div',
				'class'			=>	'hide_in_vc_editor',
				'admin_label'	=>	true
			),
			array(
				'type'			=>	'vc_link',
				'heading'		=>	esc_html__( 'Button Link', 'lordcros-core' ),
				'param_name'	=>	'btn_link',
				'description'	=>	esc_html__( 'Enter URL if you want to add a link.', 'lordcros-core' )
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Button Color', 'lordcros-core' ),
				'param_name'	=>	'button_color',
				'value'			=>	array(
					esc_html__( 'Primary Color', 'lordcros-core' )		=>	'primary-clr',
					esc_html__( 'Secondary Color', 'lordcros-core' )	=>	'secondary-clr',
					esc_html__( 'Black', 'lordcros-core' )				=>	'black-clr',
					esc_html__( 'White', 'lordcros-core' )				=>	'white-clr',
				),
				'std'			=>	'primary-clr'
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Button Style', 'lordcros-core' ),
				'param_name'	=>	'button_style',
				'value'			=>	array(
					esc_html__( 'Rectangle', 'lordcros-core' )	=>	'rectangle-style',
					esc_html__( 'Bordered', 'lordcros-core' )	=>	'border-style',
					esc_html__( 'Round', 'lordcros-core' )		=>	'round-style'
				),
				'std'			=>	'rectangle-style'
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Button Size', 'lordcros-core' ),
				'param_name'	=>	'btn_size',
				'value'			=>	array(
					esc_html__( 'Small', 'lordcros-core' )		=>	'small',
					esc_html__( 'Medium', 'lordcros-core' )		=>	'medium',
					esc_html__( 'Large', 'lordcros-core' )		=>	'big'
				),
				'std'			=>	'medium'
			),
			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Show Arrow', 'lordcros-core' ),
				'param_name'	=>	'show_arrow',
				'value'			=>	array(
					esc_html__( 'Yes', 'lordcros-core' )	=>	'yes'
				)
			),
			$animation_style,
			$animation_delay,
			$extra_class,
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Custom CSS', 'lordcros-core' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'lordcros-core' )
			)
		)
	) );
}

lordcros_core_vc_shortcode_button();