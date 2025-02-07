<?php
/*
 * Speciality post type metabox
 */

defined( 'ABSPATH' ) || exit;

/* Register meta fields in speciality post type */
if ( ! function_exists( 'lordcros_core_register_speciality_meta_boxes' ) ) {
	function lordcros_core_register_speciality_meta_boxes( $meta_boxes ) {
		$prefix = 'lordcros_speciality_';
		global $speciality_type_values;

		$meta_boxes[] = array(
			'id'			=> 'speciality-setting',
			'title'			=> esc_html__( 'Speciality Settings', 'lordcros-core' ),
			'post_types'	=> array( 'speciality' ),
			'context'		=> 'advanced',
			'priority'		=> 'default',
			'autosave'		=> 'false',
			'tab_style'		=> 'left',
			'tab_wrapper'	=> true,
			'fields'		=> array(
				array(
					'id'		=> $prefix . 'type',
					'name'		=> esc_html__( 'Speciality Type', 'lordcros-core' ),
					'type'		=> 'select',
					'options'	=> $speciality_type_values,
					'std'		=> 'date_block',
				),
				array(
					'id'		=> $prefix . 'date_from',
					'name'		=> esc_html__( 'Date From', 'lordcros-core' ),
					'type'		=> 'date',
				),
				array(
					'id'		=> $prefix . 'date_to',
					'name'		=> esc_html__( 'Date To', 'lordcros-core' ),
					'type'		=> 'date',
				),
				array(
					'name'		=> esc_html__( 'Speciality Price', 'lordcros-core' ),
					'id'		=> $prefix . 'price',
					'type'		=> 'text',
					'desc'		=> esc_html__( 'Only put numbers. Don\'t put currency symbol, etc', 'lordcros-core' ),
					'visible'	=> array( $prefix . 'type', '=', 'price_variation' ),
				),
			),
		);
		return $meta_boxes;
	}
}

add_filter( 'rwmb_meta_boxes', 'lordcros_core_register_speciality_meta_boxes' );
