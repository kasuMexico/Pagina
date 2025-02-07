<?php
/*
 * Speciality post type
 */

defined( 'ABSPATH' ) || exit;

global $speciality_type_values;
$speciality_type_values = array( 
							'date_block'		=> esc_html__( 'Block Dates', 'lordcros-core' ),
							'price_variation'	=> esc_html__( 'Price Variation', 'lordcros-core' ),
						);

/* Register Speciality Post Type */
if ( ! function_exists( 'lordcros_core_register_speciality_post_type' ) ) {
	function lordcros_core_register_speciality_post_type() {
		$labels = array(
			'name'					=> _x( 'Specialities', 'Post Type General Name', 'lordcros-core' ),
			'singular_name'			=> _x( 'Speciality', 'Post Type Singular Name', 'lordcros-core' ),
			'menu_name'				=> __( 'Specialities', 'lordcros-core' ),
			'all_items'				=> __( 'All Specialities', 'lordcros-core' ),
			'view_item'				=> __( 'View Speciality', 'lordcros-core' ),
			'add_new_item'			=> __( 'Add New Speciality', 'lordcros-core' ),
			'add_new'				=> __( 'New Speciality', 'lordcros-core' ),
			'edit_item'				=> __( 'Edit Speciality', 'lordcros-core' ),
			'update_item'			=> __( 'Update Speciality', 'lordcros-core' ),
			'search_items'			=> __( 'Search Specialities', 'lordcros-core' ),
			'not_found'				=> __( 'No Specialities found', 'lordcros-core' ),
			'not_found_in_trash'	=> __( 'No Specialities found in Trash', 'lordcros-core' ),
		);
		$args = array(
			'label'					=> __( 'Speciality', 'lordcros-core' ),
			'description'			=> __( 'Speciality information pages', 'lordcros-core' ),
			'labels'				=> $labels,
			'supports'				=> array( 'title' ),
			'hierarchical'			=> false,
			'show_ui'				=> true,
			'public'				=> true,
			'show_in_menu'			=> true,
			'show_in_admin_bar'		=> true,
			'show_in_nav_menus'		=> true,
			'menu_position'			=> 29,
			'menu_icon'				=> plugins_url( 'images/speciality.png', dirname(__FILE__) ),
			'can_export'			=> true,
			'has_archive'			=> false,
			'publicly_queryable'	=> false,
			'exclude_from_search'	=> true,
			'rewrite'				=> false,
		);

		register_post_type( 'speciality', $args );
	}
}

add_action( 'init', 'lordcros_core_register_speciality_post_type', 2 );

/* Edit speciality post type header columns */
if ( ! function_exists( 'lordcros_core_admin_edit_speciality_columns' ) ) {
	function lordcros_core_admin_edit_speciality_columns( $columns ) {

		$columns = array(
			'cb'				=> '<input type="checkbox" />',
			'title'				=> esc_html__( 'Title', 'findoctor-extension' ),
			'speciality_type'	=> esc_html__( 'Speciality Type', 'lordcros-core' ),
			'date_from'			=> esc_html__( 'Date From', 'lordcros-core' ),
			'date_to'			=> esc_html__( 'Date To', 'lordcros-core' ),
			'price'				=> esc_html__( 'Special Price', 'lordcros-core' ),
		);

		return $columns;
	}
}

add_filter( 'manage_edit-speciality_columns', 'lordcros_core_admin_edit_speciality_columns' );

/* Edit speciality post type columns */
if ( ! function_exists( 'lordcros_core_speciality_columns' ) ) {
	function lordcros_core_speciality_columns( $column, $post_id ) {
		global $speciality_type_values;

		switch( $column ) {
			case 'speciality_type':
				$speciality_type = rwmb_meta( 'lordcros_speciality_type', '', $post_id );
				if ( empty( $speciality_type ) ) {
					$speciality_type = 'date_block';
				}
				echo $speciality_type_values[$speciality_type];
				break;
			case 'date_from':
				$speciality_date_from = rwmb_meta( 'lordcros_speciality_date_from', '', $post_id );

				if ( ! empty( $speciality_date_from ) ) {
					echo $speciality_date_from;
				} 
				break;
			case 'date_to':
				$speciality_date_to = rwmb_meta( 'lordcros_speciality_date_to', '', $post_id );

				if ( ! empty( $speciality_date_to ) ) {
					echo $speciality_date_to;
				} 
				break;
			case 'price':
				$speciality_price = rwmb_meta( 'lordcros_speciality_price', '', $post_id );

				if ( ! empty( $speciality_price ) ) {
					echo $speciality_price;
				} 
				break;
		}

	}
}

add_action( 'manage_speciality_posts_custom_column', 'lordcros_core_speciality_columns', 10, 2 );