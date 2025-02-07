<?php
/**
 *	Shortcode Init
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* Remove p and br tags before and after shortcodes in content */
function lordcros_core_clean_shortcodes( $content ) {
	$array = array (
	  '<p>['	=> '[',
	  ']</p>'   => ']',
	  ']<br />' => ']',
	);

	$content = strtr( $content, $array );
	$content = preg_replace( "/<br \/>.\[/s", "[", $content );

	return $content;
}

add_filter( 'the_content', 'lordcros_core_clean_shortcodes' );

/* Return default Orderby values */
function lordcros_core_default_orderby_values() { 
	return array( 
		'',
		__( 'Date', 'lordcros-core' )		=> 'date',
		__( 'ID', 'lordcros-core' )			=> 'ID',
		__( 'Slug', 'lordcros-core' )		=> 'slug',
		__( 'Author', 'lordcros-core' )		=> 'author',
		__( 'Title', 'lordcros-core' )		=> 'title',
		__( 'Modified', 'lordcros-core' )	=> 'modified',
		__( 'Random', 'lordcros-core' )		=> 'rand',
		__( 'Menu Order', 'lordcros-core' )	=> 'menu_order'
	);
}

/* Function Add */
function lordcros_core_getCSSAnimation( $css_animation ) {
	$output = '';
	
	if ( '' !== $css_animation && 'none' !== $css_animation ) {
		wp_enqueue_script( 'waypoints' );
		wp_enqueue_style( 'animate-css' );
		$output = ' wpb_animate_when_almost_visible wpb_' . $css_animation . ' ' . $css_animation;
	}

	return $output;
}

/* Return extra class field */
function lordcros_core_extra_class_field() {
	return array(
		'type'			=>	'textfield',
		'heading'		=>	esc_html__( 'Extra Class Name', 'lordcros-core' ),
		'param_name'	=>	'extra_class',
		'description'	=>	esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'lordcros-core' )
	);
}

/* Return animation style field */
function lordcros_core_animation_style_field() {
	return array(
		'type'			=>	'animation_style',
		'heading'		=>	__( 'Animation Style', 'lordcros-core' ),
		'param_name'	=>	'animation',
		'description'	=>	__( 'Choose your animation style', 'lordcros-core' ),
		'admin_label'	=>	false,
		'weight'		=>	0
	);
}

/* Return animation delay field */
function lordcros_core_animation_delay_field() {
	return array(
		'type'			=>	'textfield',
		'heading'		=>	__( 'Animation Delay', 'lordcros-core' ),
		'param_name'	=>	'animation_delay',
		'description'	=>	__( 'Enter the delay second number', 'lordcros-core' ),
		'admin_label'	=>	false,
		'weight'		=>	0,
		'std'			=>	1
	);
}

/* Include Shortcode template files */
function lordcros_core_shortcode_init() {
	// Add Shortcode Functions
	include_once( 'functions.php' );
	include_once( 'map_json.php' );
	
	if ( defined( 'WPB_VC_VERSION' ) ) {

		// Enable VC on Post Types
		$list = array( 'post', 'page' );
		vc_set_default_editor_post_types( $list );

		include_once( 'templates/container.php' );
		include_once( 'templates/button.php' );
		include_once( 'templates/social-buttons.php' );
		include_once( 'templates/html-block.php' );
		include_once( 'templates/rooms.php' );
		include_once( 'templates/services.php' );
		include_once( 'templates/image-carousel.php' );
		include_once( 'templates/testimonial.php' );
		include_once( 'templates/image-gallery.php' );
		include_once( 'templates/posts.php' );
		include_once( 'templates/instagram.php' );
		include_once( 'templates/google-maps.php' );
		include_once( 'templates/icon-box.php' );
		include_once( 'templates/restaurant-menu.php' );
		include_once( 'templates/countdown-timer.php' );
		include_once( 'templates/room-search-form.php' );
		include_once( 'templates/user_registration.php' );
	}
}
add_action( 'init', 'lordcros_core_shortcode_init', 30 );