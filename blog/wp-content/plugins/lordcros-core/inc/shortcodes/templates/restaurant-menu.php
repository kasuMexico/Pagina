<?php
/**
 *	Restaurant Menu Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// [lc_restaurant_menus]
function lordcros_core_shortcode_restaurant_menus( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'title'				=>	'',
		'short_description'	=>	'',
		'css'				=>	'',
		'extra_class'		=>	'',
		'animation'			=>	'',
		'animation_delay'	=>	''
	), $atts, 'lordcros_restaurant_menus' ) );

	$rand_id = rand( 100, 9999 );
	$shortcode_restaurant_menus_id = uniqid( 'lordcros-restaurant-menus-' . $rand_id );

	$restaurant_menus_classes = $content_class = $html = $styles = '';

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	if ( ! empty( $animation_classes ) ) {
		$restaurant_menus_classes .= ' ' . $animation_classes;
	}

	if ( ! empty( $extra_class ) ) {
		$restaurant_menus_classes .= ' ' . $extra_class;
	}

	ob_start();
	?>
	<div id="<?php echo esc_attr( $shortcode_restaurant_menus_id ); ?>" class="lordcros-shortcode-restaurant-menus <?php echo esc_attr( $restaurant_menus_classes ); ?>" style="animation-delay: <?php echo esc_attr( $animation_delay ); ?>s;">
		<?php if ( ! empty( $title ) ) : ?>
			<h3 class="shortcode-title"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<?php if ( ! empty( $short_description ) ) : ?>
			<p class="shortcode-description"><?php echo esc_html( $short_description ); ?></p>
		<?php endif; ?>
		
		<div class="restaurant-menus-innner">
			<?php echo do_shortcode( $content ); ?>
		</div>
	</div>

	<?php

	$html = ob_get_clean();

	return $html;
	
}

add_shortcode( 'lc_restaurant_menus', 'lordcros_core_shortcode_restaurant_menus' );

// [lc_restaurant_menu]
function lordcros_core_shortcode_restaurant_menu( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'title'				=>	'',
		'subtitle'			=>	'',
		'image_id'			=>	'',
		'price'				=>	'',
		'extra_class'		=>	'',
		'animation'			=>	'',
		'animation_delay'	=>	''
	), $atts, 'lordcros_restaurant_menu' ) );

	$rand_id = rand( 100, 9999 );
	$shortcode_restaurant_menu_id = uniqid( 'lordcros-restaurant-menu-' . $rand_id );

	$restaurant_menu_classes = $content_class = $html = $styles = '';

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	if ( ! empty( $animation_classes ) ) {
		$restaurant_menu_classes .= ' ' . $animation_classes;
	}

	if ( ! empty( $extra_class ) ) {
		$restaurant_menu_classes .= ' ' . $extra_class;
	}

	ob_start();
	?>
	<div id="<?php echo esc_attr( $shortcode_restaurant_menu_id ); ?>" class="shortcode-restaurant-menu <?php echo esc_attr( $restaurant_menu_classes ); ?>">
		<div class="restaurant-menu-image">
			<?php echo lordcros_core_get_image( $image_id, 'lordcros-restaurant-menu-list' ); ?>
		</div>
		<div class="restaurant-menu-detail">
			<span class="menu-title"><?php echo esc_html( $title ); ?></span>
			<span class="menu-subtitle"><?php echo esc_html( $subtitle ); ?></span>
		</div>
		<div class="restaurant-menu-price">
			<span class="menu-price"><?php echo esc_html( $price ); ?></span>
		</div>
	</div>

	<?php

	$html = ob_get_clean();

	$styles .= '#' . $shortcode_restaurant_menu_id . '{';
	$styles .= 'animation-delay: ' . $animation_delay . 's;';
	$styles .= '}';

	wp_register_style( 'lordcros-core-inline-styles', false );
	wp_enqueue_style( 'lordcros-core-inline-styles' );
	wp_add_inline_style( 'lordcros-core-inline-styles', $styles );

	return $html;
	
}

add_shortcode( 'lc_restaurant_menu', 'lordcros_core_shortcode_restaurant_menu' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_restaurant_menus() {
	
	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'				=>	esc_html__( 'Restaurant Menus', 'lordcros-core' ),
		'base'				=>	'lc_restaurant_menus',
		'icon'				=>	'lordcros-js-composer',
		'category'			=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'		=>	esc_html__( 'Show restaurant menus', 'lordcros-core' ),
		'as_parent'			=>	array( 'only' => 'lc_restaurant_menu' ),
		'params'			=>	array(
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Title', 'lordcros-core' ),
				'param_name'	=> 'title',
				'save_always'	=> true,
				'admin_label'	=> true,
			),
			array(
				'type'			=> 'textarea',
				'heading'		=> esc_html__( 'Description', 'lordcros-core' ),
				'param_name'	=> 'short_description',
				'save_always'	=> true,
			),
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Custom CSS', 'lordcros-core' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'lordcros-core' )
			),
			$animation_style,
			$animation_delay,
			$extra_class
		),
		'js_view'			=>	'VcColumnView',
		'default_content'	=>	'[lc_restaurant_menu][/lc_restaurant_menu]'
	) );

	vc_map( array(
		'name'				=>	esc_html__( 'Restaurant Menu', 'lordcros-core' ),
		'base'				=>	'lc_restaurant_menu',
		'icon'				=>	'lordcros-js-composer',
		'category'			=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'as_child'			=>	array( 'only' => 'lc_restaurant_menus' ),
		'params'			=>	array(
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Menu Name', 'lordcros-core' ),
				'param_name'	=>	'title',
				'admin_label'	=>	true
			),			
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Menu Description', 'lordcros-core' ),
				'param_name'	=>	'subtitle',
			),
			array(
				'type'			=>	'attach_image',
				'heading'		=>	esc_html__( 'Menu Image', 'lordcros-core' ),
				'param_name'	=>	'image_id',
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Price', 'lordcros-core' ),
				'param_name'	=>	'price',
				'admin_label'	=>	true
			),
			$animation_style,
			$animation_delay,
			$extra_class
		)
	) );

	if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
	    class WPBakeryShortCode_Lc_Restaurant_Menus extends WPBakeryShortCodesContainer {
	    }
	}

	if ( class_exists( 'WPBakeryShortCode' ) ) {
	    class WPBakeryShortCode_Lc_Restaurant_Menu extends WPBakeryShortCode {
	    }
	}
}

lordcros_core_vc_shortcode_restaurant_menus();