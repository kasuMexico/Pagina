<?php
/*
 * Hotel Service post type
 */

defined( 'ABSPATH' ) || exit;

/* Register Hotel Service Post Type */
if ( ! function_exists( 'lordcros_core_register_hotel_service_post_type' ) ) {
	function lordcros_core_register_hotel_service_post_type() {
		$labels = array(
			'name'					=> _x( 'Hotel Services', 'Post Type General Name', 'lordcros-core' ),
			'singular_name'			=> _x( 'Hotel Service', 'Post Type Singular Name', 'lordcros-core' ),
			'menu_name'				=> __( 'Hotel Services', 'lordcros-core' ),
			'all_items'				=> __( 'All Hotel Services', 'lordcros-core' ),
			'view_item'				=> __( 'View Hotel Service', 'lordcros-core' ),
			'add_new_item'			=> __( 'Add New Hotel Service', 'lordcros-core' ),
			'add_new'				=> __( 'New Hotel Service', 'lordcros-core' ),
			'edit_item'				=> __( 'Edit Hotel Service', 'lordcros-core' ),
			'update_item'			=> __( 'Update Hotel Service', 'lordcros-core' ),
			'search_items'			=> __( 'Search Hotel Services', 'lordcros-core' ),
			'not_found'				=> __( 'No Hotel Services found', 'lordcros-core' ),
			'not_found_in_trash'	=> __( 'No Hotel Services found in Trash', 'lordcros-core' ),
		);
		$args = array(
			'label'					=> __( 'Hotel Service', 'lordcros-core' ),
			'description'			=> __( 'Hotel Service information pages', 'lordcros-core' ),
			'labels'				=> $labels,
			'supports'				=> array( 'title', 'editor', 'thumbnail', 'author' ),
			'taxonomies'			=> array(),
			'hierarchical'			=> false,
			'menu_position'			=> 29,
			'menu_icon'				=> plugins_url( 'images/reception.png', dirname(__FILE__) ),
			'public'				=> true,
			'can_export'			=> true,
			'has_archive'			=> true,
			'exclude_from_search'	=> false,
			'publicly_queryable'	=> true,
		);

		register_post_type( 'service', $args );
	}
}

add_action( 'init', 'lordcros_core_register_hotel_service_post_type', 2 );
