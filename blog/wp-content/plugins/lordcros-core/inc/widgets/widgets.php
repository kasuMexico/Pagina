<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Register lordcros widget */
if ( ! function_exists( 'lordcros_core_register_widgets' ) ) {
	function lordcros_core_register_widgets() {
		
		// Register Lordcros Widget
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/widgets/class-lc-html-block-widget.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/widgets/class-lc-rooms-widget.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/widgets/class-lc-recent-posts-widget.php' );
		require_once( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/widgets/class-lc-room-search-form-widget.php' );

		register_widget( 'LordCros_Core_HTML_Block_Widget' );
		register_widget( 'LordCros_Core_Rooms_Widget' );
		register_widget( 'LordCros_Core_Recent_Posts_Widget' );
		register_widget( 'LordCros_Core_Room_Search_Form_Widget' );
	}
}

add_action( 'widgets_init', 'lordcros_core_register_widgets' );