<?php
/*
 * Room post type
 */

defined( 'ABSPATH' ) || exit;

/* Register Room Post Type */
if ( ! function_exists( 'lordcros_core_register_room_post_type' ) ) {
	function lordcros_core_register_room_post_type() {
		$labels = array(
			'name'					=> _x( 'Rooms', 'Post Type General Name', 'lordcros-core' ),
			'singular_name'			=> _x( 'Room', 'Post Type Singular Name', 'lordcros-core' ),
			'menu_name'				=> __( 'Rooms', 'lordcros-core' ),
			'all_items'				=> __( 'All Rooms', 'lordcros-core' ),
			'view_item'				=> __( 'View Room', 'lordcros-core' ),
			'add_new_item'			=> __( 'Add New Room', 'lordcros-core' ),
			'add_new'				=> __( 'New Room', 'lordcros-core' ),
			'edit_item'				=> __( 'Edit Room', 'lordcros-core' ),
			'update_item'			=> __( 'Update Room', 'lordcros-core' ),
			'search_items'			=> __( 'Search Rooms', 'lordcros-core' ),
			'not_found'				=> __( 'No Rooms found', 'lordcros-core' ),
			'not_found_in_trash'	=> __( 'No Rooms found in Trash', 'lordcros-core' ),
		);
		$args = array(
			'label'					=> __( 'Room', 'lordcros-core' ),
			'description'			=> __( 'Room information pages', 'lordcros-core' ),
			'labels'				=> $labels,
			'supports'				=> array( 'title', 'editor', 'thumbnail', 'author' ),
			'taxonomies'			=> array(),
			'hierarchical'			=> false,
			'menu_position'			=> 29,
			'menu_icon'				=> plugins_url( 'images/room-key.png', dirname(__FILE__) ),
			'public'				=> true,
			'can_export'			=> true,
			'has_archive'			=> true,
			'exclude_from_search'	=> false,
			'publicly_queryable'	=> true,
		);

		register_post_type( 'room', $args );
	}
}

add_action( 'init', 'lordcros_core_register_room_post_type', 2 );
