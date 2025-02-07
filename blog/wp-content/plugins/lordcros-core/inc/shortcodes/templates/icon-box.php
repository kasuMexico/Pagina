<?php
/**
 *	Icon Box Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// [lc_icon_box]
function lordcros_core_shortcode_icon_box( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'icon_library'			=>	'fontawesome',
		'fontawesome_icon'		=>	'',
		'openiconic_icon'		=>	'',
		'typicons_icon'			=>	'',
		'entypo_icon'			=>	'',
		'linecons_icon'			=>	'',
		'monosocial_icon'		=>	'',
		'lordcrosicons_icon'	=>	'',
		'image_icon'			=>	'',
		'img_size'				=>	'',
		'icon_size'				=>	'14',
		'icon_color'			=>	'#222',
		'circle_border'			=>	'',
		'css'					=>	'',
		'box_align'				=>	'center',
		'content_align'			=>	'left',
		'extra_class'			=>	'',
		'animation'				=>	'',
		'animation_delay'		=>	''
	), $atts, 'lordcros_icon_box' ) );

	$rand_id = rand( 100, 9999 );
	$shortcode_icon_box_id = uniqid( 'lordcros-icon-box-' . $rand_id );

	$icon_box_classes = $content_class = $html = $styles = '';

	if ( ! empty( $icon_library ) ) {
		$icon_box_classes .= $icon_library . '-icon-style';
	}

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	if ( ! empty( $animation_classes ) ) {
		$icon_box_classes .= ' ' . $animation_classes;
	}

	if ( ! empty( $extra_class ) ) {
		$icon_box_classes .= ' ' . $extra_class;
	}

	if ( true == $circle_border || 'yes' === $circle_border ) {
		$content_class .= ' icon-circle-border';
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= vc_shortcode_custom_css_class( $css );
	}

	if ( ! empty( $content_align ) ) {
		$content_class .= ' icon-box-content-' . $content_align;
	}

	if ( ! empty( $box_align ) ) {
		$content_class .= ' icon-box-' . $box_align;
	}

	vc_icon_element_fonts_enqueue( $icon_library );

	if ( 'image' == $icon_library ) {
		$icon = '<i class="image-icon">' . lordcros_core_get_image( $image_icon, $img_size ) . '</i>';
	} else {
		$font_icon_class = isset( ${ $icon_library . '_icon' } ) ? esc_attr( ${ $icon_library . '_icon' } ) : 'fa fa-smile-o';
		$icon = '<i class="' . $font_icon_class . '" style="color: ' . $icon_color . '; font-size: ' . $icon_size . 'px;"></i>';
	}

	$html .= '<div id="' . esc_attr( $shortcode_icon_box_id ) . '" class="shortcode-icon-box ' . esc_attr( $icon_box_classes ) . '" style="animation-delay: ' . $animation_delay . 's;">';
		$html .= '<div class="shortcode-icon-box-wrapper ' . esc_attr( $content_class ) . '">';
			$html .= '<div class="icon-wrap" style="border-color: ' . $icon_color . ';">';
				$html .= $icon;
			$html .= '</div>';
			$html .= '<div class="content-inner">' . do_shortcode( $content ) . '</div>';
		$html .= '</div>';
	$html .= '</div>';

	$margin_align = $content_align;

	return $html;
}

add_shortcode( 'lc_icon_box', 'lordcros_core_shortcode_icon_box' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_icon_box() {

	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Icon Box', 'lordcros-core' ),
		'base'			=>	'lc_icon_box',
		'icon'			=>	'lordcros-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'	=>	esc_html__( 'Place Icon Box.', 'lordcros-core' ),
		'params'		=>	array(
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Icon Library', 'lordcros-core' ),
				'param_name'	=>	'icon_library',
				'admin_label'	=>	true,
				'value'			=>	array(
					esc_html__( 'Font Awesome', 'lordcros-core' )	=>	'fontawesome',
					esc_html__( 'Open Iconic', 'lordcros-core' )	=>	'openiconic',
					esc_html__( 'Typicons', 'lordcros-core' )		=>	'typicons',
					esc_html__( 'Entypo', 'lordcros-core' )			=>	'entypo',
					esc_html__( 'Linecons', 'lordcros-core' )		=>	'linecons',
					esc_html__( 'Mono Social', 'lordcros-core' )	=>	'monosocial',
					esc_html__( 'LordCros Icon', 'lordcros-core' )	=>	'lordcrosicons',
					esc_html__( 'Upload Image', 'lordcros-core' )	=>	'image'
				)
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'FontAwesome Icon', 'lordcros-core' ),
				'param_name'	=>	'fontawesome_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'fontawesome'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	true,
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'lordcros-core' )
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Openiconic', 'lordcros-core' ),
				'param_name'	=>	'openiconic_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'openiconic'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	true,
					'type'			=>	'openiconic',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'lordcros-core' )
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Typicons', 'lordcros-core' ),
				'param_name'	=>	'typicons_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'typicons'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	true,
					'type'			=>	'typicons',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'lordcros-core' )	
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Entypo', 'lordcros-core' ),
				'param_name'	=>	'entypo_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'entypo'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	true,
					'type'			=>	'entypo',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'lordcros-core' )
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Linecons', 'lordcros-core' ),
				'param_name'	=>	'linecons_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'linecons'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	true,
					'type'			=>	'linecons',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'lordcros-core' )
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'Mono Social Icon', 'lordcros-core' ),
				'param_name'	=>	'monosocial_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'monosocial'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	false,
					'type'			=>	'monosocial',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'lordcros-core' )
			),
			array(
				'type'			=>	'iconpicker',
				'heading'		=>	esc_html__( 'LordCros Icon', 'lordcros-core' ),
				'param_name'	=>	'lordcrosicons_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'lordcrosicons'
				),
				'settings'		=>	array(
					'emptyIcon'		=>	false,
					'type'			=>	'lordcrosicons',
					'iconsPerPage'	=>	500,
				),
				'description'	=>	esc_html__( 'Select Icon Library', 'lordcros-core' )
			),
			array(
				'type'			=>	'attach_image',
				'heading'		=>	esc_html__( 'Upload Image Icon', 'lordcros-core' ),
				'param_name'	=>	'image_icon',
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'image'
				),
				'description'	=>	esc_html__( 'Select Icon from media library', 'lordcros-core' )
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Image Size', 'lordcros-core' ),
				'param_name'	=>	'img_size',
				'description'	=>	esc_html__( 'Enter the image size. EX(200x100)', 'lordcros-core' ),
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	'image'
				)
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Icon Size', 'lordcros-core' ),
				'param_name'	=>	'icon_size',
				'description'	=>	esc_html__( 'Add icon size instead of pixel unit.', 'lordcros-core' ),
				'dependency'	=>	array(
					'element'	=>	'icon_library',
					'value'		=>	array( 'fontawesome', 'openiconic', 'typicons', 'entypo', 'linecons', 'monosocial', 'lordcrosicons' )
				),
				'std'			=>	14
			),
			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Icon Color', 'lordcros-core' ),
				'param_name'	=>	'icon_color',
				'std'			=>	'#222'
			),
			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Show Icon Circle Border', 'lordcros-core' ),
				'param_name'	=>	'circle_border',
				'value'			=>	array(
					esc_html__( 'Yes', 'lordcros-core' )	=>	'yes'
				)
			),
			array(
				'type'			=>	'textarea_html',
				'heading'		=>	esc_html__( 'Icon Box Content', 'lordcros-core' ),
				'param_name'	=>	'content',
			),
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Icon Box Custom CSS', 'lordcros-core' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'lordcros-core' )
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Alignment', 'lordcros-core' ),
				'param_name'	=>	'box_align',
				'value'			=>	array(
					esc_html__( 'Left', 'lordcros-core' )		=>	'left',
					esc_html__( 'Center', 'lordcros-core' )		=>	'center',
					esc_html__( 'Right', 'lordcros-core' )		=>	'right',
				),
				'std'			=>	'center'
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Content Alignment', 'lordcros-core' ),
				'param_name'	=>	'content_align',
				'value'			=>	array(
					esc_html__( 'Left', 'lordcros-core' )		=>	'left',
					esc_html__( 'Right', 'lordcros-core' )		=>	'right',
					esc_html__( 'Top', 'lordcros-core' )		=>	'top',
					esc_html__( 'Bottom', 'lordcros-core' )		=>	'bottom'
				),
				'std'			=>	'left'
			),
			$animation_style,
			$animation_delay,
			$extra_class,
		)
	) );
}

lordcros_core_vc_shortcode_icon_box();