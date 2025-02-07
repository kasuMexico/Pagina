<?php  
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *	Initialize Visual Composer
 */

if ( class_exists('Vc_Manager', false) ) {
	// Disable update
	add_action('vc_before_init', 'lordcros_core_vcSetAsTheme');

	function lordcros_core_vcSetAsTheme() {
		vc_manager()->disableUpdater( true );
		vc_set_as_theme();
	}

	// Modify and remove existing shortcodes from VC
	add_action('vc_before_init', 'lordcros_core_load_js_composer');

	function lordcros_core_load_js_composer() {
		require_once LORDCROS_CORE_PLUGIN_ABSPATH . ( '/inc/js_composer/functions.php' );
		require_once LORDCROS_CORE_PLUGIN_ABSPATH . ( '/inc/js_composer/js_composer.php' );
	}

	// VC Templates
	if ( function_exists( 'vc_set_shortcodes_templates_dir' ) ) {
		vc_set_shortcodes_templates_dir( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/js_composer/vc_templates' );
	}
}