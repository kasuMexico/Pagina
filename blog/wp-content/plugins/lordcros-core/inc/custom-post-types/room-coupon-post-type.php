<?php
/*
 * Room Coupon post type
 */

defined( 'ABSPATH' ) || exit;

global $room_coupon_type_values;
$room_coupon_type_values = array(							
							'percent'		=> esc_html__( 'Percentage Discount', 'lordcros-core' ),
							'fixed_cart'	=> esc_html__( 'Fixed Cart Discount', 'lordcros-core' ),
						);

/* Register Room Coupon Post Type */
if ( ! function_exists( 'lordcros_core_register_room_coupon_post_type' ) ) {
	function lordcros_core_register_room_coupon_post_type() {
		$labels = array(
			'name'					=> _x( 'Room Coupons', 'Post Type General Name', 'lordcros-core' ),
			'singular_name'			=> _x( 'Room Coupon', 'Post Type Singular Name', 'lordcros-core' ),
			'menu_name'				=> __( 'Room Coupons', 'lordcros-core' ),
			'all_items'				=> __( 'All Room Coupons', 'lordcros-core' ),
			'view_item'				=> __( 'View Room Coupon', 'lordcros-core' ),
			'add_new_item'			=> __( 'Add New Room Coupon', 'lordcros-core' ),
			'add_new'				=> __( 'New Room Coupon', 'lordcros-core' ),
			'edit_item'				=> __( 'Edit Room Coupon', 'lordcros-core' ),
			'update_item'			=> __( 'Update Room Coupon', 'lordcros-core' ),
			'search_items'			=> __( 'Search Room Coupons', 'lordcros-core' ),
			'not_found'				=> __( 'No Room Coupons found', 'lordcros-core' ),
			'not_found_in_trash'	=> __( 'No Room Coupons found in Trash', 'lordcros-core' ),
		);
		$args = array(
			'label'					=> __( 'Room Coupon', 'lordcros-core' ),
			'description'			=> __( 'Room Coupon information pages', 'lordcros-core' ),
			'labels'				=> $labels,
			'supports'				=> array( 'title' ),
			'hierarchical'			=> false,
			'show_ui'				=> true,
			'public'				=> true,
			'show_in_menu'			=> true,
			'show_in_admin_bar'		=> true,
			'show_in_nav_menus'		=> true,
			'menu_position'			=> 29,
			'menu_icon'				=> plugins_url( 'images/coupon.png', dirname(__FILE__) ),
			'can_export'			=> true,
			'has_archive'			=> false,
			'publicly_queryable'	=> false,
			'exclude_from_search'	=> true,
			'rewrite'				=> false,
		);

		register_post_type( 'room_coupon', $args );
	}
}

add_action( 'init', 'lordcros_core_register_room_coupon_post_type', 2 );

/* Edit Room Coupon post type header columns */
if ( ! function_exists( 'lordcros_core_admin_edit_room_coupon_columns' ) ) {
	function lordcros_core_admin_edit_room_coupon_columns( $columns ) {

		$columns = array(
			'cb'						=> '<input type="checkbox" />',
			'title'						=> esc_html__( 'Code', 'findoctor-extension' ),
			'room_coupon_type'			=> esc_html__( 'Coupon Type', 'lordcros-core' ),
			'room_coupon_amount'		=> esc_html__( 'Coupon Amount', 'lordcros-core' ),
			'room_coupon_description'	=> esc_html__( 'Description', 'lordcros-core' ),
			'usage_limit'				=> esc_html__( 'Usage / Limit', 'lordcros-core' ),
			'expiry_date'				=> esc_html__( 'Expiry Date', 'lordcros-core' ),			
		);

		return $columns;
	}
}

add_filter( 'manage_edit-room_coupon_columns', 'lordcros_core_admin_edit_room_coupon_columns' );

/* Edit room_coupon post type columns */
if ( ! function_exists( 'lordcros_core_room_coupon_columns' ) ) {
	function lordcros_core_room_coupon_columns( $column, $post_id ) {
		global $room_coupon_type_values;

		switch( $column ) {
			case 'room_coupon_type':
				$room_coupon_type = rwmb_meta( 'lordcros_room_coupon_type', '', $post_id );
				if ( empty( $room_coupon_type ) ) {
					$room_coupon_type = 'fixed_cart';
				}
				echo $room_coupon_type_values[$room_coupon_type];
				break;
			case 'room_coupon_amount':
				$room_coupon_amount = rwmb_meta( 'lordcros_room_coupon_amount', '', $post_id );
				if ( empty( $room_coupon_amount ) ) {
					$room_coupon_amount = 0;
				}
				echo $room_coupon_amount;
				break;
			case 'room_coupon_description':
				$room_coupon_description = rwmb_meta( 'lordcros_room_coupon_description', '', $post_id );

				if ( ! empty( $room_coupon_description ) ) {
					echo $room_coupon_description;
				} 
				break;
			case 'expiry_date':
				$room_coupon_expiry_date = rwmb_meta( 'lordcros_room_coupon_expiry_date', '', $post_id );

				if ( ! empty( $room_coupon_expiry_date ) ) {
					echo $room_coupon_expiry_date;
				} 
				break;
			case 'usage_limit':
				$room_coupon_usage_limit = rwmb_meta( 'lordcros_room_coupon_usage_limit', '', $post_id );

				if ( ! empty( $room_coupon_usage_limit ) ) {
					echo $room_coupon_usage_limit;
				} 
				break;
		}

	}
}

add_action( 'manage_room_coupon_posts_custom_column', 'lordcros_core_room_coupon_columns', 10, 2 );