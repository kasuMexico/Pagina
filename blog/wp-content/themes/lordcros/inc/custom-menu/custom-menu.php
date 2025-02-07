<?php

class LordCrosCustomMenu {

	function __construct() {
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'edit_custom_walker'), 10, 2 );
	} // end constructor
	
	
	function add_custom_nav_fields( $menu_item ) {
		$menu_item->menu_style = get_post_meta( $menu_item->ID, '_menu_item_style', true );

		return $menu_item;
	}
	
	function update_custom_nav_fields( $menu_id, $menu_item_db_id, $args ) {
		if ( isset( $_REQUEST['menu-item-style'][$menu_item_db_id] ) ) {
			$menu_item_style = $_REQUEST['menu-item-style'][$menu_item_db_id];
			$header_layout = lordcros_get_opt( 'header_layout', 'header-layout-1' );

			if ( in_array( $header_layout, array( 'header-layout-3', 'header-layout-4', 'header-layout-6', 'header-layout-10' ) ) ) {
				$menu_item_style = 'default-menu';
			}

			update_post_meta( $menu_item_db_id, '_menu_item_style', $menu_item_style );
		} else {
			delete_post_meta( $menu_item_db_id, '_menu_item_style' );
		}
	}
	
	function edit_custom_walker( $walker, $menu_id ) {
		return 'LordCrosMenuEditCustom';
	}

}

$GLOBALS['sweet_custom_menu'] = new LordCrosCustomMenu();

include_once( 'edit-custom-walker.php' );
include_once( 'custom-walker.php' );