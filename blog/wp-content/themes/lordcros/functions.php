<?php
/**
 * LordCros Main Theme Functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! session_id() ) {
	session_start();
}

/* Define Values */
define( 'LORDCROS_DIR', 	get_template_directory() );
define( 'LORDCROS_URI', 	get_template_directory_uri() );
define( 'LORDCROS_LIB', 	LORDCROS_DIR . '/inc' );
define( 'LORDCROS_ADMIN',	LORDCROS_LIB . '/admin_pages' );
define( 'LORDCROS_TEMP', 	LORDCROS_DIR . '/templates' );

/* Include PHP files */
if ( class_exists( 'ReduxFramework' ) && ! isset( $redux_demo )  && file_exists( LORDCROS_LIB . '/theme_options/lordcros.config.php' ) ) {
	require_once( LORDCROS_LIB . '/theme_options/lordcros.config.php' );
}

require_once( LORDCROS_LIB . '/class-tgm-plugin-activation.php' );
require_once( LORDCROS_LIB . '/functions.php' );

// Include Admin Theme Page
if ( is_admin() ) {
	include_once( LORDCROS_ADMIN . '/admin_page.php' );
}

/* LordCros theme setup */
if ( ! function_exists( 'lordcros_theme_setup' ) ) {
	function lordcros_theme_setup() {
		require_once( LORDCROS_LIB . '/helper.php' );
		require_once( LORDCROS_LIB . '/multiple-sidebars.php' );

		if ( is_multisite() ) {
			update_site_option( 'fileupload_maxk', 1024 * 32 );
		}

		add_theme_support( 'title-tag' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'custom-header' );
		add_theme_support( 'custom-background' );

		// load theme text domain - multi-language
		load_theme_textdomain( 'lordcros', LORDCROS_DIR . '/languages' );

		// Register additional image sizes
		add_image_size( 'lordcros-page-banner', 1920, 500, true );
		add_image_size( 'lordcros-room-grid', 545, 380, true );
		add_image_size( 'lordcros-room-list', 545, 545, true );
		add_image_size( 'lordcros-room-block', 545, 610, true );
		add_image_size( 'lordcros-booked-room', 360, 220, true );
		add_image_size( 'lordcros-shortcode-room-block', 960, 950, true );
		add_image_size( 'lordcros-service-list', 570, 450, true );
		add_image_size( 'lordcros-service-large-gallery', 750, 550, true );
		add_image_size( 'lordcros-service-extra-large-gallery', 945, 667, true );
		add_image_size( 'lordcros-service-thumb', 540, 350, true );
		add_image_size( 'lordcros-post-gallery', 560, 700, true );
		add_image_size( 'lordcros-post-grid', 560, 360, true );
		add_image_size( 'lordcros-map-marker', 70, 70, true );
		add_image_size( 'lordcros-restaurant-menu-list', 120, 90, true );
		add_image_size( 'lordcros-room-gallery', 200, 122, true );
		add_image_size( 'lordcros-room-large-gallery', 750, 450, true );
		add_image_size( 'lordcros-room-extra-large-gallery', 1200, 720, true );
		add_image_size( 'lordcros-room-full-screen-gallery', 1920, 960, true );
		add_image_size( 'lordcros-blog-default', 650, 400, true );
		add_image_size( 'lordcros-blog-second', 545, 400, true );

		if ( ! isset( $content_width ) ) $content_width = 1140;
	}
}
add_action( 'after_setup_theme', 'lordcros_theme_setup' );

/* Init lordcros theme */
if ( ! function_exists( 'lordcros_init' ) ) {
	function lordcros_init() {
		require_once( LORDCROS_LIB . '/custom-menu/custom-menu.php' );
		require_once( LORDCROS_TEMP . '/header/element-functions.php' );

		// Scss compile actions
		add_action( 'redux/options/lordcros_theme_options/saved', 'lordcros_compile_plugin_css', 10 );
		add_action( 'redux/options/lordcros_theme_options/saved', 'lordcros_compile_theme_css', 10 );
		add_action( 'redux/options/lordcros_theme_options/import', 'lordcros_compile_plugin_css', 10 );
		add_action( 'redux/options/lordcros_theme_options/import', 'lordcros_compile_theme_css', 10 );
		add_action( 'redux/options/lordcros_theme_options/reset', 'lordcros_compile_plugin_css', 10 );
		add_action( 'redux/options/lordcros_theme_options/reset', 'lordcros_compile_theme_css', 10 );
		add_action( 'redux/options/lordcros_theme_options/section/reset', 'lordcros_compile_plugin_css', 10 );
		add_action( 'redux/options/lordcros_theme_options/section/reset', 'lordcros_compile_theme_css', 10 );

		// Register menu
		register_nav_menus( array(
			'main-navigation'			=>	esc_html__( 'Main Navigation', 'lordcros' ),
			'mobile-side-navigation'	=>	esc_html__( 'Mobile Side Navigation', 'lordcros' )
		) );
	}
}
add_action( 'init', 'lordcros_init' );

/* Register lordcros widget area */
if ( ! function_exists( 'lordcros_register_sidebar' ) ) {
	function lordcros_register_sidebar() {
		
		// Single room page sidebar
		register_sidebar( array(
			'name'			=>	esc_html__( 'Room Page Sidebar', 'lordcros' ),
			'id'			=>	'lordcros-room-sidebar',
			'description'	=>	esc_html__( 'Widgets in this area will be shown on the room page sidebar.', 'lordcros' ),
			'before_widget'	=>	'<div id="%1$s" class="sidebar-widget %2$s">',
			'after_widget'	=>	'</div>',
			'before_title'	=>	'<h2 class="widget-title">',
			'after_title'	=>	'</h2>',
		) );

		register_sidebar( array(
			'name'			=>	esc_html__( 'Blog Sidebar', 'lordcros' ),
			'id'			=>	'lordcros-blog-sidebar',
			'description'	=>	esc_html__( 'Widgets in this area will be shown on the sidebar of Blog page.', 'lordcros' ),
			'before_widget'	=>	'<div id="%1$s" class="sidebar-widget %2$s">',
			'after_widget'	=>	'</div>',
			'before_title'	=>	'<h2 class="widget-title">',
			'after_title'	=>	'</h2>',
		) );

		register_sidebar( array(
			'name'			=>	esc_html__( 'Post Sidebar', 'lordcros' ),
			'id'			=>	'lordcros-post-sidebar',
			'description'	=>	esc_html__( 'Widgets in this area will be shown on the sidebar of single post page.', 'lordcros' ),
			'before_widget'	=>	'<div id="%1$s" class="sidebar-widget %2$s">',
			'after_widget'	=>	'</div>',
			'before_title'	=>	'<h2 class="widget-title">',
			'after_title'	=>	'</h2>',
		) );

		// Register Widget Areas
		$footer_layout = lordcros_get_opt( 'footer_layout', 'footer-layout-1' );
		$footer_main_col = lordcros_get_opt( 'footer_main_columns', 'footer-column-1' );

		if ( 'footer-layout-1' == $footer_layout ) {
			register_sidebar( array(
				'name'			=>	esc_html__( 'Footer Top Right Area', 'lordcros' ),
				'id'			=>	'lordcros-footer-top-area',
				'description'	=>	esc_html__( 'Widgets in this area will be shown on the footer top right area', 'lordcros' ),
				'before_widget'	=>	'<div id="%1$s" class="footer-widget %2$s">',
				'after_widget'	=>	'</div>',
				'before_title'	=>	'<h2 class="widget-title">',
				'after_title'	=>	'</h2>',
			) );
		} else {
			register_sidebar( array(
				'name'			=>	esc_html__( 'Footer Bottom Right Area', 'lordcros' ),
				'id'			=>	'lordcros-footer-bottom-area',
				'description'	=>	esc_html__( 'Widgets in this area will be shown on the footer bottom right area', 'lordcros' ),
				'before_widget'	=>	'<div id="%1$s" class="footer-widget %2$s">',
				'after_widget'	=>	'</div>',
				'before_title'	=>	'<h2 class="widget-title">',
				'after_title'	=>	'</h2>',
			) );
		}

		$footer_layout_config = lordcros_footer_configuration( $footer_main_col );

		if ( 1 < $footer_layout_config['column'] ) {
			foreach ( $footer_layout_config['column'] as $key => $columns ) {
				$column_index = $key + 1;

				register_sidebar( array(
					'name'			=>	esc_html__( 'Footer Widget Column ' . $column_index, 'lordcros' ),
					'id'			=>	'lordcros-footer-widget-' . $column_index,
					'description'	=>	esc_html__( 'Widgets in this area will be shown on the main footer.', 'lordcros' ),
					'before_widget'	=>	'<div id="%1$s" class="footer-widget %2$s">',
					'after_widget'	=>	'</div>',
					'before_title'	=>	'<h2 class="widget-title">',
					'after_title'	=>	'</h2>',
				) );
			}
		}

	}
}

add_action( 'widgets_init', 'lordcros_register_sidebar' );