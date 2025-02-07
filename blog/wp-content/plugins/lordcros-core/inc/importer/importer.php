<?php

defined( 'ABSPATH' ) || exit;

// add import ajax actions
add_action( 'wp_ajax_lordcros_reset_menus', 'lordcros_core_reset_menus' );
add_action( 'wp_ajax_lordcros_reset_widgets', 'lordcros_core_reset_widgets' );
add_action( 'wp_ajax_lordcros_import_dummy', 'lordcros_core_import_dummy' );
add_action( 'wp_ajax_lordcros_import_widgets', 'lordcros_core_import_widgets' );
add_action( 'wp_ajax_lordcros_import_options', 'lordcros_core_import_options' );
add_filter( 'widget_data_export', 'lordcros_core_modify_widget_data_export', 30, 2 );

function lordcros_core_modify_widget_data_export( $widget_val, $widget_type ) { 
	$new_widget_val = $widget_val;

	if ( 'nav_menu' == $widget_type ) { 
		foreach ( $widget_val as $widget_key => $widget_value ) {
			if ( is_int( $widget_key ) && array_key_exists( 'nav_menu', $widget_value ) ) { 
				$menu_object = wp_get_nav_menu_object( $widget_value['nav_menu'] );

				if ( $menu_object ) { 
					$new_widget_val[$widget_key]['slug'] = $menu_object->slug;
				}
			}
		}
	}

	return $new_widget_val;
}

function lordcros_core_reset_menus() {
	if ( current_user_can( 'manage_options' ) ) {
		$menus = get_terms( 'nav_menu' );
		
		foreach ( $menus as $menu ) {
			wp_delete_nav_menu( $menu );
		}

		echo esc_html__( 'Successfully reset menus!', 'lordcros-core' );
	}

	die;
}

function lordcros_core_reset_widgets() {
	if ( current_user_can( 'manage_options' ) ) {
		ob_start();

		$sidebars_widgets = retrieve_widgets();

		foreach ( $sidebars_widgets as $area => $widgets ) {
			foreach ( $widgets as $key => $widget_id ) {
				$pieces = explode( '-', $widget_id );
				$multi_number = array_pop( $pieces );
				$id_base = implode( '-', $pieces );
				$widget = get_option( 'widget_' . $id_base );

				unset( $widget[$multi_number] );

				update_option( 'widget_' . $id_base, $widget );

				unset( $sidebars_widgets[$area][$key] );
			}
		}

		wp_set_sidebars_widgets( $sidebars_widgets );

		ob_clean();
		ob_end_clean();

		echo esc_html__( 'Successfully reset widgets!', 'lordcros-core' );
	}

	die;
}

function lordcros_core_import_dummy() {
	if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) { 
		define( 'WP_LOAD_IMPORTERS', true ); // We are loading importers
	} 

	if ( ! class_exists( 'LORDCROS_CORE_WP_Import' ) ) { // if WP importer doesn't exist
		$wp_import = LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/importer/wordpress-importer.php';
		include $wp_import;
	}

	if ( current_user_can( 'manage_options' ) && class_exists( 'WP_Importer' ) && class_exists( 'LORDCROS_CORE_WP_Import' ) ) { // check for main import class and wp import class
	
		$process = ( isset( $_POST['process'] ) && $_POST['process'] ) ? $_POST['process'] : 'import_start';
		$demo = ( isset( $_POST['demo'] ) && $_POST['demo'] ) ? $_POST['demo'] : 'demo1';
		$index = ( isset( $_POST['index'] ) && $_POST['index'] ) ? $_POST['index'] : 0;

		$importer = new LORDCROS_CORE_WP_Import();
		$theme_xml = LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/importer/data/' . $demo . '/content.gz';
		$importer->fetch_attachments = true;

		$loop = (int) ( ini_get( 'max_execution_time' ) / 60 );
		if ( $loop < 1 ) $loop = 1;
		if ( $loop > 10 ) $loop = 10;

		$i = 0;

		while ( $i < $loop ) {
			$response = $importer->import( $theme_xml, $process, $index );
			if ( isset( $response['count'] ) && isset( $response['index'] ) && $response['count'] && $response['index'] && $response['index'] < $response['count'] ) {
				$i++;
				$index = $response['index'];
			} else {
				break;
			}
		}

		echo json_encode( $response );

		// Import Revolution Sliders
		if ( 'complete' == $response['process'] && class_exists( 'RevSlider' ) ) { 
			$directory = dirname( __FILE__ ) . '/data/' . $demo . '/sliders';
			$sliders = array_diff( scandir( $directory ), array( '..', '.' ) );

			foreach ( $sliders as $demo_slider ) {
				$demo_file = dirname( __FILE__ ) . '/data/' . $demo . '/sliders/' . $demo_slider;

				if ( file_exists( $demo_file ) ) { 
					$revapi = new RevSlider();

					ob_start();
					$slider_result = $revapi->importSliderFromPost( true, true, $demo_file );
					ob_end_clean();
				}
			}
		}
		
		ob_start();

		if ( 'complete' == $response['process'] ) {
			// Set imported menus to registered theme locations
			$locations = get_theme_mod( 'nav_menu_locations' ); // registered menu locations in theme
			$menus = wp_get_nav_menus(); // registered menus

			if ( $menus ) {
				foreach( $menus as $menu ) { // assign menus to theme locations
					if ( 'Main Navigation' == $menu->name ) {
						$locations['main-navigation'] = $menu->term_id;
					} else if ( 'Mobile Navigation' == $menu->name ) {
						$locations['mobile-side-navigation'] = $menu->term_id;
					}
				}
			}

			set_theme_mod( 'nav_menu_locations', $locations ); // set menus to locations

			// Set reading options
			$homepage = get_page_by_title( 'Home' );
			$posts_page = get_page_by_title( 'Blog' );
		  
			if ( ( $homepage && $homepage->ID ) || ( $posts_page && $posts_page->ID ) ) {
				update_option( 'show_on_front', 'page' );

				if ( $homepage && $homepage->ID ) {
					update_option( 'page_on_front', $homepage->ID ); // Front Page
				}

				if ( $posts_page && $posts_page->ID ) {
					update_option( 'page_for_posts', $posts_page->ID ); // Blog Page
				}
			}

			// MailChimp Form Import Settings
			$args = array( 
				'post_type' => 'mc4wp-form'
			);
			$mailchimp_forms = get_posts( $args );

			if ( is_array( $mailchimp_forms ) ) { 
				$form_id = $mailchimp_forms[0]->ID;

				$default_form_id = (int) get_option( 'mc4wp_default_form_id', 0 );

				if( empty( $default_form_id ) ) {
					update_option( 'mc4wp_default_form_id', $form_id );
				}
			}

			update_option( 'permalink_structure', '/%postname%/' );

			// Flush rules after install
			flush_rewrite_rules();
		}

		ob_end_clean();
	}

	die();
}

function lordcros_core_import_widgets() {
	if ( current_user_can( 'manage_options' ) ) {
		$demo = ( isset( $_POST['demo'] ) && $_POST['demo'] ) ? $_POST['demo'] : 'demo1';

		// Import widgets
		ob_start();
		include( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/importer/data/' . $demo . '/widget_data.json' );
		$widget_data = ob_get_clean();

		lordcros_core_import_widget_data( $widget_data );

		echo esc_html__( 'Successfully imported widgets!', 'lordcros-core' );
	}

	die();
}

function lordcros_core_import_options() {
	if ( current_user_can( 'manage_options' ) ) {
		$demo = ( isset( $_POST['demo']) && $_POST['demo'] ) ? $_POST['demo'] : 'demo1';
		
		ob_start();
		include( LORDCROS_CORE_PLUGIN_ABSPATH . '/inc/importer/data/' . $demo . '/theme_options.php' );
		$theme_options = ob_get_clean();

		ob_start();
		$options = json_decode( $theme_options, true );
		$redux = ReduxFrameworkInstances::get_instance( 'lordcros_theme_options' );
		$redux->set_options( $options );
		ob_clean();
		ob_end_clean();

		// Compile Theme Option SCSS
		do_action( 'redux/options/lordcros_theme_options/saved' );

		echo esc_html__( 'Successfully imported theme options!', 'lordcros-core' );
	}

	die();
}


// Parsing Widgets Function
// Reference: http://wordpress.org/plugins/widget-settings-importexport/
function lordcros_core_import_widget_data( $widget_data ) {
	$json_data = $widget_data;
	$json_data = json_decode( $json_data, true );

	$sidebar_data = $json_data[0];
	$widget_data = $json_data[1];

	$widgets = array();

	foreach ( $widget_data as $widget_data_title => $widget_data_value ) {
		$widgets[ $widget_data_title ] = array();

		foreach( $widget_data_value as $widget_data_key => $widget_data_array ) {
			if( is_int( $widget_data_key ) ) {
				$widgets[$widget_data_title][$widget_data_key] = 'on';
			}
		}
	}
	unset( $widgets[""] );

	foreach ( $sidebar_data as $title => $sidebar ) {
		$count = count( $sidebar );
		for ( $i = 0; $i < $count; $i++ ) {
			$widget = array( );
			$widget['type'] = trim( substr( $sidebar[$i], 0, strrpos( $sidebar[$i], '-' ) ) );
			$widget['type-index'] = trim( substr( $sidebar[$i], strrpos( $sidebar[$i], '-' ) + 1 ) );
			if ( !isset( $widgets[$widget['type']][$widget['type-index']] ) ) {
				unset( $sidebar_data[$title][$i] );
			}
		}
		$sidebar_data[$title] = array_values( $sidebar_data[$title] );
	}

	foreach ( $widgets as $widget_title => $widget_value ) {
		foreach ( $widget_value as $widget_key => $widget_value ) {
			$widgets[$widget_title][$widget_key] = $widget_data[$widget_title][$widget_key];
		}
	}

	$sidebar_data = array( array_filter( $sidebar_data ), $widgets );

	lordcros_core_parse_import_data( $sidebar_data );
}

function lordcros_core_parse_import_data( $import_array ) {
	global $wp_registered_sidebars;
	
	$sidebars_data = $import_array[0];
	$widget_data = $import_array[1];
	$current_sidebars = get_option( 'sidebars_widgets' );
	$new_widgets = array( );

	foreach ( $sidebars_data as $import_sidebar => $import_widgets ) :

		foreach ( $import_widgets as $import_widget ) :
			//if the sidebar exists
			if ( array_key_exists( $import_sidebar, $wp_registered_sidebars ) ) :
				$title = trim( substr( $import_widget, 0, strrpos( $import_widget, '-' ) ) );
				$index = trim( substr( $import_widget, strrpos( $import_widget, '-' ) + 1 ) );
				$current_widget_data = get_option( 'widget_' . $title );
				$new_widget_name = lordcros_core_get_new_widget_name( $title, $index );
				$new_index = trim( substr( $new_widget_name, strrpos( $new_widget_name, '-' ) + 1 ) );

				if ( !empty( $new_widgets[ $title ] ) && is_array( $new_widgets[$title] ) ) {
					while ( array_key_exists( $new_index, $new_widgets[$title] ) ) {
						$new_index++;
					}
				}

				$current_sidebars[$import_sidebar][] = $title . '-' . $new_index;

				if ( array_key_exists( $title, $new_widgets ) ) {
					$new_widgets[$title][$new_index] = $widget_data[$title][$index];
					$multiwidget = $new_widgets[$title]['_multiwidget'];

					unset( $new_widgets[$title]['_multiwidget'] );

					$new_widgets[$title]['_multiwidget'] = $multiwidget;
				} else {
					$current_widget_data[$new_index] = $widget_data[$title][$index];
					$current_multiwidget = array_key_exists('_multiwidget', $current_widget_data) ? $current_widget_data['_multiwidget'] : false;
					$new_multiwidget = array_key_exists('_multiwidget', $widget_data[$title]) ? $widget_data[$title]['_multiwidget'] : false;
					$multiwidget = ($current_multiwidget != $new_multiwidget) ? $current_multiwidget : 1;

					unset( $current_widget_data['_multiwidget'] );

					$current_widget_data['_multiwidget'] = $multiwidget;
					$new_widgets[$title] = $current_widget_data;
				}

			endif;
		endforeach;
	endforeach;

	if ( isset( $new_widgets ) && isset( $current_sidebars ) ) {
		update_option( 'sidebars_widgets', $current_sidebars );

		foreach ( $new_widgets as $title => $content ) {
			if ( 'nav_menu' == $title ) { 
				foreach ( $content as $widget_key => $widget_value ) {
					if ( is_int( $widget_key ) && array_key_exists( 'slug', $widget_value ) ) { 
						$menu_object = wp_get_nav_menu_object( $widget_value['slug'] );
						$content[$widget_key]['nav_menu'] = $menu_object->term_id;
					}
				}
			}

			update_option( 'widget_' . $title, $content );
		}

		return true;
	}

	return false;
}

function lordcros_core_get_new_widget_name( $widget_name, $widget_index ) {
	$current_sidebars = get_option( 'sidebars_widgets' );
	$all_widget_array = array( );

	foreach ( $current_sidebars as $sidebar => $widgets ) {
		if ( ! empty( $widgets ) && is_array( $widgets ) && $sidebar != 'wp_inactive_widgets' ) {
			foreach ( $widgets as $widget ) {
				$all_widget_array[] = $widget;
			}
		}
	}

	while ( in_array( $widget_name . '-' . $widget_index, $all_widget_array ) ) {
		$widget_index++;
	}

	$new_widget_name = $widget_name . '-' . $widget_index;

	return $new_widget_name;
}