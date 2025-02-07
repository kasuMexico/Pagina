<?php
/*
 * Hotel Service post type metabox
 */

defined( 'ABSPATH' ) || exit;

/* Register meta fields in hotel service post type */
if ( ! function_exists( 'lordcros_core_register_hotel_service_meta_boxes' ) ) {
	function lordcros_core_register_hotel_service_meta_boxes( $meta_boxes ) {
		$prefix = 'lordcros_service_';

		$meta_boxes[] = array(
			'id'			=> 'general-setting',
			'title'			=> esc_html__( 'General Settings', 'lordcros-core' ),
			'post_types'	=> array( 'service' ),
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
			),
		);

		$meta_boxes[] = array(
			'id'		=> 'service_settings',
			'title'		=> esc_html__( 'Service Settings', 'lordcros-core' ),
			'pages'		=> array( 'service' ),
			'context'	=> 'side',
			'priority'	=> 'default',
			'fields'	=> array(
				array(
					'name'	=> __( 'Feature This Service', 'lordcros-core' ),
					'id'	=> $prefix . 'featured',
					'desc'	=> __( 'Add this service to featured list.', 'lordcros-core' ),
					'type'	=> 'checkbox',
					'std'	=> array(),
				),
			)
		);

		return $meta_boxes;
	}
}

/* Register page style meta fields in service post type */
if ( ! function_exists( 'lordcros_core_register_service_page_style_meta_boxes' ) ) {
	function lordcros_core_register_service_page_style_meta_boxes( $meta_boxes ) {

		$prefix = 'lordcros_';

		global $wp_registered_sidebars;

		foreach ( $wp_registered_sidebars as $sidebar ) {
			$sidebars[$sidebar['id']] = $sidebar['name'];
		}
		$sidebars['default'] = esc_html__( 'Default', 'lordcros-core' );
		
		$meta_boxes[] = array(
			'id'			=> 'page-layout-settings',
			'title'			=> esc_html__( 'Page Layout Settings', 'lordcros-core' ),
			'post_types'	=> array( 'service' ),
			'context'		=> 'normal',
			'priority'		=> 'low',
			'autosave'		=> 'false',
			'fields'		=> array(
				array(
					'id'		=> $prefix . 'page_layout',
					'name'		=> esc_html__( 'Page Layout', 'lordcros-core' ),
					'type'		=> 'select',
					'options'	=> array(
						'inherit'		=> esc_html__( 'Inherit', 'lordcros-core' ),
						'full-width'	=> esc_html__( 'No Sidebar', 'lordcros-core' ),
						'sidebar-left'	=> esc_html__( 'Left Sidebar', 'lordcros-core' ),
						'sidebar-right'	=> esc_html__( 'Right Sidebar', 'lordcros-core' ),						
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


add_filter( 'rwmb_meta_boxes', 'lordcros_core_register_hotel_service_meta_boxes' );
add_filter( 'rwmb_meta_boxes', 'lordcros_core_register_service_page_style_meta_boxes' );