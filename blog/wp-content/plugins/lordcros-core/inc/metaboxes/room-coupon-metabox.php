<?php
/*
 * Room Coupon post type metabox
 */

defined( 'ABSPATH' ) || exit;

/* Register meta fields in room coupon post type */
if ( ! function_exists( 'lordcros_core_register_room_coupon_meta_boxes' ) ) {
	function lordcros_core_register_room_coupon_meta_boxes( $meta_boxes ) {
		$prefix = 'lordcros_room_coupon_';
		global $room_coupon_type_values;

		$meta_boxes[] = array(
			'id'			=> 'room_coupon-setting',
			'title'			=> esc_html__( 'Room Coupon Settings', 'lordcros-core' ),
			'post_types'	=> array( 'room_coupon' ),
			'context'		=> 'advanced',
			'priority'		=> 'default',
			'autosave'		=> 'false',
			'tab_style'		=> 'left',
			'tab_wrapper'	=> true,
			'fields'		=> array(
				array(
					'name'		=> esc_html__( 'Description', 'lordcros-core' ),
					'id'		=> $prefix . 'description',
					'type'		=> 'textarea',
				),
				array(
					'id'		=> $prefix . 'type',
					'name'		=> esc_html__( 'Room Coupon Type', 'lordcros-core' ),
					'type'		=> 'select',
					'options'	=> $room_coupon_type_values,
					'std'		=> 'fixed_cart',
				),
				array(
					'id'		=> $prefix . 'amount',
					'name'		=> esc_html__( 'Coupon Amount', 'lordcros-core' ),
					'type'		=> 'text',
					'std'		=> '0',
				),
				array(
					'id'			=> $prefix . 'room_restriction',
					'name'			=> esc_html__( 'Allowed Rooms', 'lordcros-core' ),
					'type'			=> 'post',
					'post_type'		=> 'room',
					'field_type'	=> 'select_advanced',
					'multiple'		=> true,
					'placeholder'	=> esc_html__( 'Select Rooms', 'lordcros-core' ),
					'desc'			=> esc_html__( 'If you set it as blank, all rooms are acceptable.', 'lordcros-core' ),
				),
				array(
					'id'			=> $prefix . 'usage_limit',
					'name'			=> esc_html__( 'Usage limit per coupon', 'lordcros-core' ),
					'type'			=> 'number',
					'std'			=> '',
					'placeholder'	=> esc_html__( 'No limit', 'lordcros-core' ),
				),
				array(
					'id'		=> $prefix . 'expiry_date',
					'name'		=> esc_html__( 'Expiry Date', 'lordcros-core' ),
					'type'		=> 'date',
				),
			),
		);
		return $meta_boxes;
	}
}

add_filter( 'rwmb_meta_boxes', 'lordcros_core_register_room_coupon_meta_boxes' );
