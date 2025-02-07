<?php
/*
 * Room Booking Information Class
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LordCros_Core_Room_Booking' ) ) {
	class LordCros_Core_Room_Booking {
		public $booking_id = '';

		public function __construct( $booking_id ) {
			$this->booking_id = $booking_id; 
		}

		public function get_booking_info() {
			global $wpdb;

			if ( empty( $this->booking_id ) ) {
				return false;
			}

			$sql = $wpdb->prepare( 'SELECT lordcros_booking.* FROM ' . LORDCROS_ROOM_BOOKINGS_TABLE . ' AS lordcros_booking WHERE lordcros_booking.id=%s', $this->booking_id );
			$booking_data = $wpdb->get_row( $sql, ARRAY_A );
			if ( empty( $booking_data ) ) {
				return false;
			}

			return $booking_data;
		}		
	}
}