<?php
/**
 * LordCros Theme Help Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Get Parent Theme Name */
if ( ! function_exists( 'lordcros_parent_theme_name' ) ) {
	function lordcros_parent_theme_name() {
		$actived_theme = wp_get_theme();

		if ( $actived_theme->parent() ):
			$theme_name = $actived_theme->parent()->get( 'Name' );
		else:
			$theme_name = $actived_theme->get( 'Name' );
		endif;

		return $theme_name;
	}
}

/* Get Theme Version */
if ( ! function_exists( 'lordcros_theme_version' ) ) {
	function lordcros_theme_version() {
		$lordcros_theme = wp_get_theme();
		return $lordcros_theme->get( 'Version' );
	}
}

/* Get Theme Author */
if ( ! function_exists( 'lordcros_theme_author' ) ) {
	function lordcros_theme_author() {
		$lordcros_theme = wp_get_theme();
		return $lordcros_theme->get( 'Author' );
	}
}

/* Get Theme Option Value */
if ( ! function_exists( 'lordcros_get_opt' ) ) {
	function lordcros_get_opt( $slug, $default = false ) {
		global $lordcros_theme_options;

		$lordcros_opt_val = isset( $lordcros_theme_options[ $slug ] ) ? $lordcros_theme_options[ $slug ] : '';

		if ( empty( $lordcros_opt_val ) && ! empty( $default ) ) {
			$lordcros_opt_val = $default;
		}

		return $lordcros_opt_val;
	}
}

/* Get template part (for templates like the shop-loop) */
if ( ! function_exists( 'lordcros_get_template_part' ) ) { 
	function lordcros_get_template_part( $slug, $name = '' ) { 
		$template = '';
		$file_name = '';

		if ( $name ) { 
			$file_name = "{$slug}-{$name}.php";
		} else { 
			$file_name = "{$slug}.php";
		}

		// check if templates/slug-name.php file exists
		$template = locate_template( 'templates/' . $file_name );

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'lordcros_get_template_part', $template, $slug, $name );

		if ( $template ) {
			load_template( $template, false );
		}
	}
}