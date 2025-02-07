<?php
/*
 * Html Block post type
 */

defined( 'ABSPATH' ) || exit;

/* Register Html Block Post Type */
if ( ! function_exists( 'lordcros_core_register_html_block_post_type' ) ) {
	function lordcros_core_register_html_block_post_type() {
		$labels = array(
			'name'					=> _x( 'HTML Blocks', 'Post Type General Name', 'lordcros-core' ),
			'singular_name'			=> _x( 'HTML Block', 'Post Type Singular Name', 'lordcros-core' ),
			'menu_name'				=> __( 'HTML Blocks', 'lordcros-core' ),
			'all_items'				=> __( 'All Items', 'lordcros-core' ),
			'view_item'				=> __( 'View Item', 'lordcros-core' ),
			'add_new_item'			=> __( 'Add New Item', 'lordcros-core' ),
			'add_new'				=> __( 'New Item', 'lordcros-core' ),
			'edit_item'				=> __( 'Edit Item', 'lordcros-core' ),
			'update_item'			=> __( 'Update Item', 'lordcros-core' ),
			'search_items'			=> __( 'Search Items', 'lordcros-core' ),
			'not_found'				=> __( 'No HTML Block found', 'lordcros-core' ),
			'not_found_in_trash'	=> __( 'No HTML Block found in Trash', 'lordcros-core' )
		);
		$args = array(
			'label'					=> __( 'HTML Block', 'lordcros-core' ),
			'description'			=> __( 'HTML Block information pages', 'lordcros-core' ),
			'labels'				=> $labels,
			'supports'				=> array( 'title', 'editor', 'thumbnail', 'author' ),
			'hierarchical'			=> false,
			'show_ui'				=> true,
			'public'				=> true,
			'show_in_menu'			=> true,
			'show_in_admin_bar'		=> true,
			'show_in_nav_menus'		=> true,
			'menu_position'			=> 29,
			'menu_icon'				=> 'dashicons-editor-table',
			'can_export'			=> true,
			'has_archive'			=> false,
			'publicly_queryable'	=> false,
			'exclude_from_search'	=> true,
			'rewrite'				=> false
		);

		register_post_type( 'html_block', $args );
	}
}

add_action( 'init', 'lordcros_core_register_html_block_post_type', 2 );

/* Edit html_block post type columns */
if ( ! function_exists( 'lordcros_core_admin_edit_html_blocks_columns' ) ) {
	function lordcros_core_admin_edit_html_blocks_columns( $columns ) {
		$columns = array(
			'cb' 		=> '<input type="checkbox" />',
			'title' 	=> __( 'Title', 'lordcros-core' ),
			'shortcode' => __( 'Shortcode', 'lordcros-core' ),	   
			'date' 		=> __( 'Date', 'lordcros-core' ),
		);

		return $columns;
	}
}

add_filter( 'manage_edit-html_block_columns', 'lordcros_core_admin_edit_html_blocks_columns' );

if ( ! function_exists( 'lordcros_core_shortcode_html_blocks_columns' ) ) {
	function lordcros_core_shortcode_html_blocks_columns( $column, $post_id ) {
		switch( $column ) {
			case 'shortcode':
				echo '<strong>[html_block block_id="' . $post_id . '"]</strong>';
			break;
		}
	}
}

add_action( 'manage_html_block_posts_custom_column', 'lordcros_core_shortcode_html_blocks_columns', 10, 2 );