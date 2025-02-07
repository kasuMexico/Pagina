<?php
/*
 * Room Service post type metabox
 */

defined( 'ABSPATH' ) || exit;

/* Register meta fields in room service post type */
if ( ! function_exists( 'lordcros_core_register_room_service_meta_boxes' ) ) {
	function lordcros_core_register_room_service_meta_boxes( $meta_boxes ) {
		$prefix = 'lordcros_room_service_';
		global $service_type_values, $service_price_type_1_values, $service_price_type_2_values;

		$meta_boxes[] = array(
			'id'			=> 'service-setting',
			'title'			=> esc_html__( 'Service Settings', 'lordcros-core' ),
			'post_types'	=> array( 'room_service' ),
			'context'		=> 'advanced',
			'priority'		=> 'default',
			'autosave'		=> 'false',
			'tab_style'		=> 'left',
			'tab_wrapper'	=> true,
			'fields'		=> array(
				array(
					'id'				=> $prefix . 'icon_image',
					'name'				=> esc_html__( 'Icon Image', 'lordcros-core' ),
					'type'				=> 'image_advanced',
					'max_file_uploads'	=> 1,
				),
				array(
					'id'		=> $prefix . 'icon_class',
					'name'		=> esc_html__( 'Icon Class Name', 'lordcros-core' ),
					'type'		=> 'text',
					'desc'		=> esc_html__( 'If you added Icon Image field, this field will not work.', 'lordcros-core' ),
				),
				array(
					'id'		=> $prefix . 'type',
					'name'		=> esc_html__( 'Service Type', 'lordcros-core' ),
					'type'		=> 'select',
					'options'	=> $service_type_values,
					'std'		=> 'def_service',
				),
				array(
					'name'		=> esc_html__( 'Service Price', 'lordcros-core' ),
					'id'		=> $prefix . 'price',
					'type'		=> 'text',
					'desc'		=> esc_html__( 'Only put numbers. Don\'t put currency symbol, etc', 'lordcros-core' ),
					'visible'	=> array( $prefix . 'type', '=', 'extra_service' ),
				),
				array(
					'id'		=> $prefix . 'price_type_1',
					'name'		=> esc_html__( 'Price Type', 'lordcros-core' ),
					'type'		=> 'select',
					'desc'		=> esc_html__( 'Per Person or Per Room', 'lordcros-core' ),
					'options'	=> $service_price_type_1_values,
					'std'		=> 'per_person',
					'visible'	=> array( $prefix . 'type', '=', 'extra_service' ),
				),
				array(
					'id'		=> $prefix . 'price_type_2',
					'name'		=> esc_html__( 'Price Type', 'lordcros-core' ),
					'type'		=> 'select',
					'desc'		=> esc_html__( 'Per Day or Per Trip', 'lordcros-core' ),
					'options'	=> $service_price_type_2_values,
					'std'		=> 'per_day',
					'visible'	=> array( $prefix . 'type', '=', 'extra_service' ),
				),
			),
		);
		return $meta_boxes;
	}
}

add_filter( 'rwmb_meta_boxes', 'lordcros_core_register_room_service_meta_boxes' );
