<?php
/*
 * include all the files related admin pages
 */

defined( 'ABSPATH' ) || exit;

require_once( 'room-bookings-table-view-admin-panel.php' );
require_once( 'room-bookings-calendar-view-admin-panel.php' );

/* Add admin pages to menu */
if ( ! function_exists( 'lordcros_core_admin_pages' ) ) {
	function lordcros_core_admin_pages() {
		//add room bookings page
		add_menu_page( esc_html__( 'Bookings', 'lordcros-core' ), esc_html__( 'Bookings', 'lordcros-core' ), 'manage_options', 'room_bookings', '', plugins_url( 'images/booking.png', dirname(__FILE__) ), 58 );
		$table_view_page = add_submenu_page( 'room_bookings', esc_html__( 'Table View', 'lordcros-core' ), esc_html__( 'Table View', 'lordcros-core' ), 'manage_options', 'room_bookings', 'lordcros_core_room_booking_table_view_render_pages' );
		$calendar_view_page = add_submenu_page( 'room_bookings', esc_html__( 'Calendar View', 'lordcros-core' ), esc_html__( 'Calendar View', 'lordcros-core' ), 'manage_options', 'room_bookings_calendar_view', 'lordcros_core_room_booking_calendar_view_render_pages' );
		add_action( 'admin_print_scripts-' . $table_view_page, 'lordcros_core_room_booking_admin_enqueue_scripts' );
		add_action( 'admin_print_scripts-' . $calendar_view_page, 'lordcros_core_room_booking_admin_enqueue_scripts' );
	}
}

add_action( 'admin_menu', 'lordcros_core_admin_pages' );

/* Booking admin enqueue script action */
if ( ! function_exists( 'lordcros_core_room_booking_admin_enqueue_scripts' ) ) {
	function lordcros_core_room_booking_admin_enqueue_scripts() {
		// support select2
		
		if ( class_exists( 'RWMB_Date_Field' ) ) {
			RWMB_Date_Field::admin_enqueue_scripts();
		}

		if ( class_exists( 'RWMB_Select_Advanced_Field' ) ) {
			RWMB_Select_Advanced_Field::admin_enqueue_scripts();
		}
		wp_enqueue_style( 'rwmb', RWMB_CSS_URL . 'style.css', array(), RWMB_VER );

		// custom style and js
		wp_enqueue_script( 'lordcros-admin-room-booking-script' , LORDCROS_CORE_PLUGIN_URL . '/js/admin/booking_table.js', array('jquery'), '1.0', true );
		$messages = array(
						'delete_row_confirm_msg'	=> esc_html__( 'It will be deleted permanetly. Do you want to delete it?', 'lordcros-core' ),
					);
		wp_localize_script( 'lordcros-admin-room-booking-script', 'messages', $messages );
	}
}