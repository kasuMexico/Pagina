<?php
/*
 * Meta Box Custom Fields
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lordcros_core_register_custom_fields' ) ) {
	function lordcros_core_register_custom_fields() {
	    require 'fields/ical_field.php';
	}
}

add_action( 'init', 'lordcros_core_register_custom_fields' );
