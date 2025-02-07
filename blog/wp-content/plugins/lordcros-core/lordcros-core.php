<?php
/**
Plugin Name: LordCros Core
Plugin URI: http://www.c-themes.com/
Description: A LordCros Core plugin.
Version: 1.1.0
Author: C-Themes
Author URI: http://www.c-themes.com
Text Domain: lordcros-core
Domain Path: /languages/
*/

defined( 'ABSPATH' ) || exit;

global $wpdb;

define( 'LORDCROS_DB_VERSION', '1.1' );
defined( 'LORDCROS_ROOM_BOOKINGS_TABLE' ) or define( 'LORDCROS_ROOM_BOOKINGS_TABLE', $wpdb->prefix . 'lordcros_room_order' );

define( 'LORDCROS_CORE_PLUGIN_ABSPATH', dirname( __FILE__ ) );
define( 'LORDCROS_CORE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

class LordCros_Core { 

	// LordCrosCore version
	public $version = '1.1.0';

	/* Construction */
	function __construct() { 
		// include plugin files
		$this->includes();	

		// load plugin text domain
		add_action( 'plugins_loaded', array( $this, 'loadTextDomain' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'ComposerInit' ) );
		add_action( 'init', array( $this, 'core_init' ) );	
	}

	/* Load plugin textdomain */
	function loadTextDomain() {
		load_plugin_textdomain( 'lordcros-core', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/* Include required core files used in admin and on the frontend */
	private function includes() {
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/scssphp/scss.inc.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/scssphp/compiler.scss.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/importer/importer.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/db.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/wpml.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/widgets/widgets.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/classes/main.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/custom-post-types/main.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/custom-taxonomies/main.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/metabox-custom/main.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/metaboxes/main.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/admin-pages/main.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/room/main.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/service/main.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/payments/main.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/shortcodes/shortcode-init.php' );		
	}

	/* WPBakery Composer Init */
	function ComposerInit() {
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/js_composer/init.php' );
	}

	/* init action */
	function core_init() {
		ob_start();

		add_filter( 'the_content', array( $this, 'disable_wpautop_cpt' ), 0 );
	}

	function disable_wpautop_cpt( $content ) {
		'html_block' === get_post_type() && remove_filter( 'the_content', 'wpautop' );
		return $content;
	}
}

new LordCros_Core();

// call when plugin activate
register_activation_hook( __FILE__, 'lordcros_core_create_extra_tables' );
