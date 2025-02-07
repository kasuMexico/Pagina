<?php
/*
 * Room Checkout Information Class
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LordCros_Core_Room_Checkout_Info' ) ) {
	class LordCros_Core_Room_Checkout_Info {
		
		public function __construct() {
			if ( empty( $_SESSION['lordcros_booking_room_data'] ) ) {
				$_SESSION['lordcros_booking_room_data'] = array();
			}
		}

		public static function set( $data ) {
			foreach( $data as $key => $value ) {
				$_SESSION['lordcros_booking_room_data'][$key] = $value;	
			}			
		}

		public static function get() {
			if ( ! empty( $_SESSION['lordcros_booking_room_data'] ) ) {
				return $_SESSION['lordcros_booking_room_data'];
			}

			return false;
		}

		public static function _unset() {
			if ( ! empty( $_SESSION['lordcros_booking_room_data'] ) ) {
				unset( $_SESSION['lordcros_booking_room_data'] );
			}
		}

		public static function get_field( $field = 'total_price' ) {
			if ( ! empty( $_SESSION['lordcros_booking_room_data'][$field] ) ) {
				return $_SESSION['lordcros_booking_room_data'][$field];
			}

			return false;
		}

	}
}