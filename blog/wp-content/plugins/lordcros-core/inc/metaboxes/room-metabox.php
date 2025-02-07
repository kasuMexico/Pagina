<?php
/*
 * Room post type metabox
 */

defined( 'ABSPATH' ) || exit;

/* Register meta fields in room post type */
if ( ! function_exists( 'lordcros_core_register_room_meta_boxes' ) ) {
	function lordcros_core_register_room_meta_boxes( $meta_boxes ) {
		$prefix = 'lordcros_room_';

		$meta_boxes[] = array(
			'id'			=> 'room-setting',
			'title'			=> esc_html__( 'Room Settings', 'lordcros-core' ),
			'post_types'	=> array( 'room' ),
			'context'		=> 'advanced',
			'priority'		=> 'default',
			'autosave'		=> 'false',
			'tab_style'		=> 'left',
			'tab_wrapper'	=> true,
			'tabs'			=> array(
				'general_tab'		=> array(
					'label'				=> esc_html__( 'General Settings', 'lordcros-core' ),
					'icon'				=> 'fas fa-cogs',
				),
				'price_tab'			=> array(
					'label'				=> esc_html__( 'Price Settings', 'lordcros-core' ),
					'icon'				=> 'fas fa-money-check-alt',
				),
				'service_tab'		=> array(
					'label'				=> esc_html__( 'Service Settings', 'lordcros-core' ),
					'icon'				=> 'fas fa-concierge-bell',
				),
				'speciality_tab'	=> array(
					'label'				=> esc_html__( 'Speciality Settings', 'lordcros-core' ),
					'icon'				=> 'fas fa-thumbs-up',
				),
			),
			'fields'		=> array(
				array(
					'id'				=> $prefix . 'image',
					'name'				=> esc_html__( 'Images', 'lordcros-core' ),
					'type'				=> 'image_advanced',
					'max_file_uploads'	=> 50,
					'tab'				=> 'general_tab',
				),
				array(
					'id'			=> $prefix . 'adults',
					'name'			=> esc_html__( 'Adults Number', 'lordcros-core' ),
					'type'			=> 'number',
					'desc'			=> esc_html__( 'Put adults number of the room.', 'lordcros-core' ),
					'std'			=> 1,
					'tab'			=> 'general_tab',
				),
				// array(
				// 	'id'			=> $prefix . 'children',
				// 	'name'			=> esc_html__( 'Children Number', 'lordcros-core' ),
				// 	'type'			=> 'number',
				// 	'desc'			=> esc_html__( 'Put children number of the room.', 'lordcros-core' ),
				// 	'std'			=> 0,
				// 	'tab'			=> 'general_tab',
				// ),
				array(
					'id'			=> $prefix . 'size',
					'name'			=> esc_html__( 'Room Size', 'lordcros-core' ),
					'type'			=> 'text',
					'desc'			=> esc_html__( 'Put room size with only number. Measure unit can be set in theme options panel.', 'lordcros-core' ),
					'tab'			=> 'general_tab',
				),
				array(
					'id'			=> $prefix . 'qty',
					'name'			=> esc_html__( 'Available Room Quantity', 'lordcros-core' ),
					'type'			=> 'number',
					'desc'			=> esc_html__( 'Put available quantity for the room.', 'lordcros-core' ),
					'std'			=> 1,
					'tab'			=> 'general_tab',
				),
				array(
					'id'			=> $prefix . 'min_stay',
					'name'			=> esc_html__( 'Minimum Stay Date', 'lordcros-core' ),
					'type'			=> 'number',
					'desc'			=> esc_html__( 'Put minimum state dates for the room.', 'lordcros-core' ),
					'std'			=> 1,
					'tab'			=> 'general_tab',
				),
				array(
					'id'			=> $prefix . 'ext_link',
					'name'			=> esc_html__( 'Affiliate URL/External Link', 'lordcros-core' ),
					'type'			=> 'text',
					'desc'			=> esc_html__( 'If you want to use Affiliate URL, please put in it.', 'lordcros-core' ),
					'std'			=> '',
					'tab'			=> 'general_tab',
				),
				array(
					'id'			=> $prefix . 'def_service',
					'name'			=> esc_html__( 'Default Services', 'lordcros-core' ),
					'type'			=> 'post',
					'post_type'		=> 'room_service',
					'field_type'	=> 'select_advanced',
					'multiple'		=> true,
					'query_args'	=> array(
						'meta_query'	=> array(
							array(
								'key'		=> 'lordcros_room_service_type',
								'value'		=> 'def_service',
							),
						),
					),
					'placeholder'	=> esc_html__( 'Select Services', 'lordcros-core' ),
					'tab'			=> 'service_tab',
				),
				array(
					'id'			=> $prefix . 'extra_service',
					'name'			=> esc_html__( 'Extra Services', 'lordcros-core' ),
					'type'			=> 'post',
					'post_type'		=> 'room_service',
					'field_type'	=> 'select_advanced',
					'multiple'		=> true,
					'query_args'	=> array(
						'meta_query'	=> array(
							array(
								'key'		=> 'lordcros_room_service_type',
								'value'		=> 'extra_service',
							),
						),
					),
					'placeholder'	=> esc_html__( 'Select Services', 'lordcros-core' ),
					'tab'			=> 'service_tab',
				),
				array(
					'id'			=> $prefix . 'price',
					'name'			=> esc_html__( 'Regular Price', 'lordcros-core' ),
					'type'			=> 'text',
					'desc'			=> esc_html__( 'Put number only. Don\'t put currency symbol etc', 'lordcros-core' ),
					'tab'			=> 'price_tab',
				),				
				array(
					'id'			=> $prefix . 'heading_1',
					'type'			=> 'heading',
					'name'			=> esc_html__( 'Week Day Price', 'lordcros-core' ),
					'desc'			=> esc_html__( 'Regular price will be used if you set day price empty.', 'lordcros-core' ),
					'tab'			=> 'price_tab',
				),
				array(
					'id'			=> $prefix . 'price_mon',
					'name'			=> esc_html__( 'Monday Price', 'lordcros-core' ),
					'type'			=> 'text',
					'tab'			=> 'price_tab',
				),
				array(
					'id'			=> $prefix . 'price_tue',
					'name'			=> esc_html__( 'Tuesday Price', 'lordcros-core' ),
					'type'			=> 'text',
					'tab'			=> 'price_tab',
				),
				array(
					'id'			=> $prefix . 'price_wed',
					'name'			=> esc_html__( 'Wednes Price', 'lordcros-core' ),
					'type'			=> 'text',
					'tab'			=> 'price_tab',
				),
				array(
					'id'			=> $prefix . 'price_thu',
					'name'			=> esc_html__( 'Thursday Price', 'lordcros-core' ),
					'type'			=> 'text',
					'tab'			=> 'price_tab',
				),
				array(
					'id'			=> $prefix . 'price_fri',
					'name'			=> esc_html__( 'Friday Price', 'lordcros-core' ),
					'type'			=> 'text',
					'tab'			=> 'price_tab',
				),
				array(
					'id'			=> $prefix . 'price_sat',
					'name'			=> esc_html__( 'Saturday Price', 'lordcros-core' ),
					'type'			=> 'text',
					'tab'			=> 'price_tab',
				),
				array(
					'id'			=> $prefix . 'price_sun',
					'name'			=> esc_html__( 'Sunday Price', 'lordcros-core' ),
					'type'			=> 'text',
					'tab'			=> 'price_tab',
				),
				array(
					'id'			=> $prefix . 'price_variation',
					'name'			=> esc_html__( 'Price Variation', 'lordcros-core' ),
					'type'			=> 'post',
					'post_type'		=> 'speciality',
					'field_type'	=> 'select_advanced',
					'multiple'		=> true,
					'query_args'	=> array(
						'meta_query'	=> array(
							array(
								'key'		=> 'lordcros_speciality_type',
								'value'		=> 'price_variation',
							),
						),
					),
					'placeholder'	=> esc_html__( 'Select Specialities', 'lordcros-core' ),
					'desc'			=> esc_html__( 'Select Price Variation Specialities', 'lordcros-core' ),
					'tab'			=> 'speciality_tab',
				),
				array(
					'id'			=> $prefix . 'date_block',
					'name'			=> esc_html__( 'Block Dates', 'lordcros-core' ),
					'type'			=> 'post',
					'post_type'		=> 'speciality',
					'field_type'	=> 'select_advanced',
					'multiple'		=> true,
					'query_args'	=> array(
						'meta_query'	=> array(
							array(
								'key'		=> 'lordcros_speciality_type',
								'value'		=> 'date_block',
							),
						),
					),
					'placeholder'	=> esc_html__( 'Select Specialities', 'lordcros-core' ),
					'desc'			=> esc_html__( 'Select Block Dates Specialities', 'lordcros-core' ),
					'tab'			=> 'speciality_tab',
				),
				array(
					'id'			=> $prefix . 'ical_url',
					'name'			=> esc_html__( 'iCalendar Import', 'lordcros-core' ),
					'type'			=> 'ical',
					'placeholder'	=> esc_html__( 'Put iCalendar URL', 'lordcros-core' ),
					'desc'			=> esc_html__( 'Put iCalendar URL and click "Import iCalendar" button to import price data from iCalendar.', 'lordcros-core' ),
					'size'			=> 100,
					'tab'			=> 'speciality_tab',
				),
			),
		);

		$meta_boxes[] = array(
			'id'			=> 'other-settings',
			'title'			=> esc_html__( 'Other Settings', 'lordcros-core' ),
			'post_types'	=> array( 'room' ),
			'context'		=> 'advanced',
			'priority'		=> 'default',
			'autosave'		=> 'false',
			'fields'		=> array(
				array(
					'id'			=> $prefix . 'similar_rooms',
					'name'			=> esc_html__( 'Similar Rooms', 'lordcros-core' ),
					'type'			=> 'post',
					'post_type'		=> 'room',
					'field_type'	=> 'select_advanced',
					'multiple'		=> true,
				),
				array(
					'id'			=> $prefix . 'hotel_services',
					'name'			=> esc_html__( 'Hotel Services', 'lordcros-core' ),
					'type'			=> 'post',
					'post_type'		=> 'service',
					'field_type'	=> 'select_advanced',
					'multiple'		=> true,
				),
			),
		);

		$meta_boxes[] = array(
			'id'		=> 'room_settings',
			'title'		=> esc_html__( 'Room Settings', 'lordcros-core' ),
			'pages'		=> array( 'room' ),
			'context'	=> 'side',
			'priority'	=> 'default',
			'fields'	=> array(
				array(
					'name'	=> __( 'Feature This Room', 'lordcros-core' ),
					'id'	=> $prefix . 'featured',
					'desc'	=> __( 'Add this room to featured list.', 'lordcros-core' ),
					'type'	=> 'checkbox',
					'std'	=> array(),
				),
			)
		);

		return $meta_boxes;
	}
}

/* Register page style meta fields in room post type */
if ( ! function_exists( 'lordcros_core_register_room_page_style_meta_boxes' ) ) {
	function lordcros_core_register_room_page_style_meta_boxes( $meta_boxes ) {

		$prefix = 'lordcros_';

		global $wp_registered_sidebars;

		foreach ( $wp_registered_sidebars as $sidebar ) {
			$sidebars[$sidebar['id']] = $sidebar['name'];
		}
		$sidebars['default'] = esc_html__( 'Default', 'lordcros-core' );
		
		$meta_boxes[] = array(
			'id'			=> 'page-layout-settings',
			'title'			=> esc_html__( 'Page Layout Settings', 'lordcros-core' ),
			'post_types'	=> array( 'room' ),
			'context'		=> 'normal',
			'priority'		=> 'low',
			'autosave'		=> 'false',
			'fields'		=> array(
				array(
					'id'		=> $prefix . 'page_layout',
					'name'		=> esc_html__( 'Page Layout', 'lordcros-core' ),
					'type'		=> 'select',
					'options'	=> array(
						'inherit'	=> esc_html__( 'Inherit', 'lordcros-core' ),
						'layout-1'	=> esc_html__( 'Slider In Content', 'lordcros-core' ),
						'layout-2'	=> esc_html__( 'Slider In Header', 'lordcros-core' ),
						'layout-3'	=> esc_html__( 'Slider In Bottom', 'lordcros-core' ),						
					),
					'std' => 'inherit',
				),
				array(
					'name'		=> esc_html__( 'Select Sidebar:', 'lordcros-core' ),
					'id'		=> $prefix . 'custom_sidebar',
					'type'		=> 'select',
					'options'	=> $sidebars,
					'std'		=> 'default',
					'hidden'	=> array( $prefix . 'page_layout', '=', 'full-width' ),
				),
				array(
					'name'				=> esc_html__( 'Header Image', 'lordcros-core' ),
					'id'				=> $prefix . 'header_image',
					'type'				=> 'image_advanced',
					'desc'				=> esc_html__( 'Put Image to show in page header.', 'lordcros-core' ),
					'max_file_uploads'	=> 1,
					'visible'			=> array( $prefix . 'show_page_heading', '=', 'show' ),
				),			
				array(
					'id'		=> $prefix . 'show_breadcrumbs',
					'name'		=> esc_html__( 'Show Breadcrumbs', 'lordcros-core' ),
					'type'		=> 'select',
					'desc'		=> esc_html__( 'This field does not work in Slider In Header layout.', 'lordcros-core' ),
					'options'	=> array(
						'inherit'		=> esc_html__( 'Inherit', 'lordcros-core' ),
						'show'			=> esc_html__( 'Show', 'lordcros-core' ),
						'hide'			=> esc_html__( 'Hide', 'lordcros-core' ),
					),
					'std'		=> 'inherit',					
				),
			),
		);

		return $meta_boxes;
	}
}

add_filter( 'rwmb_meta_boxes', 'lordcros_core_register_room_meta_boxes' );
add_filter( 'rwmb_meta_boxes', 'lordcros_core_register_room_page_style_meta_boxes' );