<?php
/**
 * Html Block Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lc_html_block]
function lordcros_core_shortcode_html_block( $atts, $content = null ) {

	extract( shortcode_atts( array(
		'block_id'				=>	'',
		'extra_class'			=>	'',
		'animation'				=>	'',
		'animation_delay'		=>	1
	), $atts, 'lordcros_html_block' ) );
	
	if ( is_numeric( $block_id ) ) {
		$content = get_post_field( 'post_content', $block_id );

		$post_custom_css = get_post_meta( $block_id, '_wpb_post_custom_css', true );
		$shortcode_custom_css = get_post_meta( $block_id, '_wpb_shortcodes_custom_css', true );
		$styles = '';

		if ( ! empty( $post_custom_css ) ) {
			$styles .= $post_custom_css;
		}

		if ( ! empty( $shortcode_custom_css ) ) {
			$styles .= $shortcode_custom_css;
		}

		wp_register_style( 'lordcros-core-inline-styles', false );
		wp_enqueue_style( 'lordcros-core-inline-styles' );
		wp_add_inline_style( 'lordcros-core-inline-styles', $styles );
	}

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );
	
	$html = '';

	$html .= '<div class="lordcros-shortcode lordcros-html-block ' . esc_attr( $extra_class ) . ' ' . $animation_classes . '" style="animation-delay: ' . $animation_delay . 's;">';

	if ( ! empty( $content ) ) {
		$html .= '<div class="html_block_content">' . do_shortcode( $content ) . '</div>';
	}

	$html .= '</div>';

	return $html;

}

add_shortcode( 'lc_html_block', 'lordcros_core_shortcode_html_block' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_html_block() {

	$args = array(
		'post_per_page'	=>	100,
		'post_type'		=>	'html_block'
	);

	$html_block_posts = get_posts( $args );
	$html_block_dropdown = array();

	$html_block_dropdown[ esc_html__( 'Select Block', 'lordcros-core' ) ] = 'select_block';
	
	foreach ( $html_block_posts as $post ) :
		$html_block_dropdown[ $post->post_title ] = $post->ID;
	endforeach;
	
	$animation_style = lordcros_core_animation_style_field();

	$animation_delay = lordcros_core_animation_delay_field();

	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Html Block', 'lordcros-core' ),
		'base'			=>	'lc_html_block',
		'icon'			=>	'lordcros-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'	=>	esc_html__( 'Add Custom Html Block on your page.', 'lordcros-core' ),
		'params'		=>	array(
			array(
				'type' 			=> 'dropdown',
				'holder' 		=> 'div',
				'heading' 		=> esc_html__('Select Html Block', 'lordcros-core'),
				'param_name' 	=> 'block_id',
				'admin_label' 	=> true,
				'save_always'	=> true,
				'value'			=> $html_block_dropdown,
			),

			$animation_style,

			$animation_delay,

			$extra_class
		)
	) );

}

lordcros_core_vc_shortcode_html_block();