<?php
/*
 * CountDown Timer Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// [lc_countdown_timer]
function lordcros_core_shortcode_countdown_timer( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'start_time'		=>	'',
		'end_time'			=>	'',
		'text_clr'			=>	'#fff',
		'countdown_size'	=>	'default',
		'extra_class'		=>	'',
		'animation'			=>	'',
		'animation_delay'	=>	'',
		'css'				=>	''
	), $atts, 'lordcros_countdown_timer' ) );

	$rand_id = rand( 100, 9999 );
	$countdown_timer_id = uniqid( 'lordcros-countdown-timer-' . $rand_id );

	if ( empty( $start_time ) || empty( $end_time ) ) {
		return;	
	}

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	$classes = $html = $timer_classes = $styles = '';

	if ( ! empty( $animation_classes ) ) {
		$classes .= ' ' . $animation_classes;
	}

	if ( ! empty( $extra_class ) ) {
		$classes .= ' ' . $extra_class;
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$classes = ' ' . vc_shortcode_custom_css_class( $css );
	}

	$start_time_str = strtotime( $start_time );
	$end_time_str = strtotime( $end_time );
	$current_date = strtotime( date( 'Y-m-d H:i:s' ) );

	if ( $start_time_str > $current_date || $end_time_str < $current_date ) {
		return;
	}

	$default_timezone = 'UTC';
	$timezone = '';

	$timezone = get_option( 'timezone_string' );
	if ( empty( $timezone ) ) {
		$timezone = $default_timezone;
	}

	if ( ! empty( $countdown_size ) ) {
		$timer_classes .= ' countdown-size-' . $countdown_size;
	}	

	$html .= '<div id="' . esc_attr( $countdown_timer_id ) . '" class="lordcros-shortcode-element lordcros-shortcode-countdown-timer countdown-wrapper '  . esc_attr( $classes ) . '" style="animation-delay: ' . $animation_delay . 's;">';
		$html .= '<div class="countdown-timer ' . esc_attr( $timer_classes ) . '" data-date-to="' . esc_attr( $end_time ) . '" data-timezone="' . esc_attr( $timezone ) . '" style="color: ' . $text_clr . ';"></div>';
	$html .= '</div>';

	return $html;
}

add_shortcode( 'lc_countdown_timer', 'lordcros_core_shortcode_countdown_timer' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_countdown_timer() {

	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'CountDown Timer', 'lordcros-core' ),
		'base'			=>	'lc_countdown_timer',
		'icon'			=>	'lordcros-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'	=>	esc_html__( 'Show countdown timer effect.', 'lordcros-core' ),
		'params'		=>	array(
			array(
				'type'				=>	'lordcros_datetimepicker',
				'heading'			=>	esc_html__( 'Start Time', 'lordcros-core' ),
				'param_name'		=>	'start_time',
			),
			array(
				'type'				=>	'lordcros_datetimepicker',
				'heading'			=>	esc_html__( 'End Time', 'lordcros-core' ),
				'param_name'		=>	'end_time',
			),
			array(
				'type'				=>	'colorpicker',
				'heading'			=>	esc_html__( 'Text Color', 'lordcros-core' ),
				'param_name'		=>	'text_clr',
				'std'				=>	'#fff'
			),
			array(
				'type'				=>	'dropdown',
				'heading'			=>	esc_html__( 'CountDown Size', 'lordcros-core' ),
				'param_name'		=>	'countdown_size',
				'value'				=>	array(
					esc_html__( 'Default Size', 'lordcros-core' )		=>	'default',
					esc_html__( 'Large Size', 'lordcros-core' )			=>	'large',
					esc_html__( 'Extra Large Size', 'lordcros-core' )	=>	'extra-large'
				),
				'std'				=>	'default'
			),
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Custom CSS', 'lordcros-core' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'lordcros-core' )
			),
			$animation_style,
			$animation_delay,
			$extra_class,			
		)
	) );
}

lordcros_core_vc_shortcode_countdown_timer();