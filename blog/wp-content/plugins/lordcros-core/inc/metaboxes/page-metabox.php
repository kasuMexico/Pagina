<?php
/*
 * Page metabox
 */

defined( 'ABSPATH' ) || exit;

/* Register meta fields in page */
if ( ! function_exists( 'lordcros_core_register_page_meta_boxes' ) ) {
	function lordcros_core_register_page_meta_boxes( $meta_boxes ) {

		$prefix = 'lordcros_';

		global $wp_registered_sidebars;

		foreach ( $wp_registered_sidebars as $sidebar ) {
			$sidebars[$sidebar['id']] = $sidebar['name'];
		}
		$sidebars['default'] = esc_html__( 'Default', 'lordcros-core' );

		$meta_boxes[] = array(
			'id'			=> 'page-layout-settings',
			'title'			=> esc_html__( 'Page Layout Settings', 'lordcros-core' ),
			'post_types'	=> array( 'page' ),
			'context'		=> 'normal',
			'priority'		=> 'low',
			'autosave'		=> 'false',
			'fields'		=> array(
				array(
					'id'		=> $prefix . 'page_width',
					'name'		=> esc_html__( 'Page Width', 'lordcros-core' ),
					'type'		=> 'select',
					'options'	=> array(
						'full'		=> esc_html__( 'Full Width', 'lordcros-core' ),
						'custom'	=> esc_html__( 'Boxed Width', 'lordcros-core' ),
					),
					'std'		=> 'custom',
					'hidden'	=> array( 'post_ID', get_option( 'page_for_posts' ) ),
				),
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
					'id'		=> $prefix . 'show_page_heading',
					'name'		=> esc_html__( 'Page Heading', 'lordcros-core' ),
					'type'		=> 'select',
					'options'	=> array(
						'show'		=> esc_html__( 'Show', 'lordcros-core' ),
						'hide'		=> esc_html__( 'Hide', 'lordcros-core' ),
					),
					'std'		=> 'show',
				),
				array(
					'id'				=> $prefix . 'header_image',
					'name'				=> esc_html__( 'Header Image', 'lordcros-core' ),					
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
					'visible'			=> array( $prefix . 'show_page_heading', '=', 'show' ),
				),
			),
		);

		return $meta_boxes;
	}
}

add_filter( 'rwmb_meta_boxes', 'lordcros_core_register_page_meta_boxes' );