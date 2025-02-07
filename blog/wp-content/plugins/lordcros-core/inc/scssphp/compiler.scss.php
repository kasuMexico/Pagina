<?php
/**
 *	SCSS Compiler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;	// exit if accessed directly
}

// Compile SCSS files
function lordcros_compile_plugin_css() {

	if ( ! class_exists('scssc') ) {
		return false;
	}

	$scss = new scssc();
	$scss->setImportPaths( LORDCROS_DIR . '/scss' );

	if ( WP_DEBUG ) { 
		$scss->setFormatter('scss_formatter');
	} else { 
		$scss->setFormatter('scss_formatter_crunched');
	}

	// bootstrap compile
	ob_start();
	echo $scss->compile('@import "plugins.scss"');
	$_config_css = ob_get_clean();

	$file_name = LORDCROS_DIR . '/css/plugins.css';

	if ( false == is_writable( dirname( $file_name ) ) ) {
		@chmod( dirname( $file_name ), 0755 );
	}

	if ( file_exists( $file_name ) ) {
		if ( false == is_writable( $file_name ) ) {
			@chmod( $file_name, 0755 );
		}
		@unlink( $file_name );
	}

	$redux = ReduxFrameworkInstances::get_instance( 'lordcros_theme_options' );
	$redux->filesystem->execute( 'put_contents', $file_name, array( 'content' => $_config_css ) );
}

// Compile SCSS files
function lordcros_compile_theme_css() {

	if ( !class_exists('scssc') ) {
		return false;
	}

	// variable file compile
	ob_start();
	require( LORDCROS_LIB . '/theme_options/variable_scss_theme.php' );
	
	$_config_css = ob_get_clean();

	$file_name = LORDCROS_DIR . '/scss/_variable_theme.scss';

	if ( false == is_writable( dirname( $file_name ) ) ) {
		@chmod( dirname( $file_name ), 0755 );
	}

	if ( file_exists( $file_name ) ) {
		if ( false == is_writable( $file_name ) ) {
			@chmod( $file_name, 0755 );
		}
		@unlink( $file_name );
	}

	$redux = ReduxFrameworkInstances::get_instance( 'lordcros_theme_options' );
	$redux->filesystem->execute( 'put_contents', $file_name, array( 'content' => $_config_css ) );

	$scss = new scssc();
	$scss->setImportPaths( LORDCROS_DIR . '/scss' );

	if ( WP_DEBUG ) { 
		$scss->setFormatter('scss_formatter');
	} else { 
		$scss->setFormatter('scss_formatter_crunched');
	}

	try {
		// bootstrap compile
		ob_start();
		echo $scss->compile('@import "theme.scss"');
		$_config_css = ob_get_clean();

		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			$file_name = LORDCROS_DIR . '/css/theme' . $blog_id . '.css';
		} else {
			$file_name = LORDCROS_DIR . '/css/theme.css';
		}

		if ( false == is_writable( dirname( $file_name ) ) ) {
			@chmod( dirname( $file_name ), 0755 );
		}

		if ( file_exists( $file_name ) ) {
			if ( false == is_writable( $file_name ) ) {
				@chmod( $file_name, 0755 );
			}
			@unlink( $file_name );
		}

		$redux = ReduxFrameworkInstances::get_instance( 'lordcros_theme_options' );
		$redux->filesystem->execute( 'put_contents', $file_name, array( 'content' => $_config_css ) );
	} catch (Exception $e) {
	}
}