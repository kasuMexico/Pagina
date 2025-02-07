<?php
/*
 * Room Service post type
 */

defined( 'ABSPATH' ) || exit;

global $service_type_values, $service_price_type_1_values, $service_price_type_2_values;
$service_type_values = array( 
							'def_service'	=> esc_html__( 'Default Service', 'lordcros-core' ),
							'extra_service'	=> esc_html__( 'Extra Service', 'lordcros-core' ),
						);
$service_price_type_1_values = array( 
									'per_person'	=> esc_html__( 'Per Person', 'lordcros-core' ),
									'per_room'	=> esc_html__( 'Per Room', 'lordcros-core' ),
								);
$service_price_type_2_values = array( 
									'per_day'	=> esc_html__( 'Per Day', 'lordcros-core' ),
									'per_trip'	=> esc_html__( 'Per Trip', 'lordcros-core' ),
								);

/* Register Service Post Type */
if ( ! function_exists( 'lordcros_core_register_room_service_post_type' ) ) {
	function lordcros_core_register_room_service_post_type() {
		$labels = array(
			'name'					=> _x( 'Room Services', 'Post Type General Name', 'lordcros-core' ),
			'singular_name'			=> _x( 'Room Service', 'Post Type Singular Name', 'lordcros-core' ),
			'menu_name'				=> __( 'Room Services', 'lordcros-core' ),
			'all_items'				=> __( 'All Room Services', 'lordcros-core' ),
			'view_item'				=> __( 'View Room Service', 'lordcros-core' ),
			'add_new_item'			=> __( 'Add New Room Service', 'lordcros-core' ),
			'add_new'				=> __( 'New Room Service', 'lordcros-core' ),
			'edit_item'				=> __( 'Edit Room Service', 'lordcros-core' ),
			'update_item'			=> __( 'Update Room Service', 'lordcros-core' ),
			'search_items'			=> __( 'Search Room Services', 'lordcros-core' ),
			'not_found'				=> __( 'No Room Services found', 'lordcros-core' ),
			'not_found_in_trash'	=> __( 'No Room Services found in Trash', 'lordcros-core' ),
		);
		$args = array(
			'label'					=> __( 'Room Service', 'lordcros-core' ),
			'description'			=> __( 'Room Service information pages', 'lordcros-core' ),
			'labels'				=> $labels,
			'supports'				=> array( 'title' ),
			'hierarchical'			=> false,
			'show_ui'				=> true,
			'public'				=> true,
			'show_in_menu'			=> true,
			'show_in_admin_bar'		=> true,
			'show_in_nav_menus'		=> true,
			'menu_position'			=> 29,
			'menu_icon'				=> plugins_url( 'images/room-service.png', dirname(__FILE__) ),
			'can_export'			=> true,
			'has_archive'			=> false,
			'publicly_queryable'	=> false,
			'exclude_from_search'	=> true,
			'rewrite'				=> false,
		);

		register_post_type( 'room_service', $args );
	}
}

add_action( 'init', 'lordcros_core_register_room_service_post_type', 2 );

/* Edit service post type header columns */
if ( ! function_exists( 'lordcros_core_admin_edit_room_service_columns' ) ) {
	function lordcros_core_admin_edit_room_service_columns( $columns ) {

		$columns = array(
			'cb'			=> '<input type="checkbox" />',
			'title'			=> esc_html__( 'Title', 'findoctor-extension' ),
			'service_type'	=> esc_html__( 'Service Type', 'lordcros-core' ),	
		);

		return $columns;
	}
}

add_filter( 'manage_edit-room_service_columns', 'lordcros_core_admin_edit_room_service_columns' );

/* Edit room_service post type columns */
if ( ! function_exists( 'lordcros_core_room_service_columns' ) ) {
	function lordcros_core_room_service_columns( $column, $post_id ) {
		global $service_type_values;

		switch( $column ) {
			case 'service_type':
				$service_type = rwmb_meta( 'lordcros_room_service_type', '', $post_id );
				if ( empty( $service_type ) ) {
					$service_type = 'def_service';
				}
				echo $service_type_values[$service_type];
				break;
		}

	}
}

add_action( 'manage_room_service_posts_custom_column', 'lordcros_core_room_service_columns', 10, 2 );