<?php
/*
 * Meta Box RWMB_ICal_Field
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'RWMB_Text_Field' ) ) {
	class RWMB_ICal_Field extends RWMB_Text_Field {

		public static function admin_enqueue_scripts() {
			wp_enqueue_script( 'lordcros-core-rwmb-field-ical', LORDCROS_CORE_PLUGIN_URL . '/inc/metabox-custom/js/ical.js', '', RWMB_VER );
			$ajax_nonce = wp_create_nonce( "lordcros-ajax" );
			wp_localize_script( 'lordcros-core-rwmb-field-ical', 'js_ical_vars', array(
				'ajax_nonce'	=>	$ajax_nonce,
				'ajax_url'		=>	admin_url( 'admin-ajax.php' ),
				'alert_msg1'	=>	esc_html__( 'Please put iCalendar URL.', 'lordcros-core' ),
				'alert_msg2'	=>	esc_html__( 'Please save Room Before import.', 'lordcros-core' ),
				'post_id'		=> isset( $_GET['post'] ) ? $_GET['post'] : '',
			) );
		}

		public static function html( $meta, $field ) {
			if ( ! empty( $field['id'] ) ) {
				$id_attr = 'id="' . $field['id'] . '-button' . '"';
			} else {
				$id_attr = '';
			}
			return parent::html( $meta, $field ) . '<input type="button" ' . $id_attr . ' class="button button-primary ical_importer" value="' . esc_html__( 'Import iCalendar', 'lordcros-core' ) . '"><span class="spinner"></span>';
		}


		/* Add actions */
		public static function add_actions() {
			add_action( 'wp_ajax_import_ical', array( __CLASS__, 'wp_ajax_import_ical' ) );
		}

		/* Ajax callback */
		public static function wp_ajax_import_ical() {

			$ical_url = $_POST['ical_url'];
			$post_id = $_POST['post_id'];
			$ical_id = $_POST['ical_id'];
			
			if ( empty( $ical_url ) || empty( $post_id ) ) {
				wp_send_json_error();
			} else {
				
				update_post_meta( $post_id, $ical_id, $ical_url );

				$ical = new ICal( $ical_url );
				if ( ! empty( $ical ) ) {
					$events = $ical->events();

					$price_variation = array();
					$date_block = array();

					if ( ! empty( $events ) && is_array( $events ) ) {

						delete_post_meta( $post_id, 'lordcros_room_price_variation' );
						delete_post_meta( $post_id, 'lordcros_room_date_block' );

						foreach ( $events as $key => $event ) {
							$sumary = $event['SUMMARY'];
							$price = 0;
							$available = 'available';
							
							if ( ! is_numeric( $sumary ) ) {
								$available = 'unavailable';
							} else {
								$price = (float) $sumary;
								if ( $price < 0 ) {
									$price = 0;
								}
							}
							
							if ( isset( $event['DTSTART'] ) && isset( $event['DTEND'] ) ) {
								if ( strlen( $event['DTSTART'] ) > 8 ) {
									$event['DTSTART'] = substr( $event['DTSTART'], 0, 8 );
								}
								if ( strlen( $event['DTEND'] ) > 8 ) {
									$event['DTEND'] = substr( $event['DTEND'], 0, 8 );
								}
								$start = DateTime::createFromFormat( 'Ymd', $event['DTSTART'] );
								$start = $start->format( 'Y-m-d' );
								$end = DateTime::createFromFormat( 'Ymd', $event['DTEND'] );
								$end = $end->format( 'Y-m-d' );

								list( $speciality_id, $speciality_title) = lordcros_core_add_speciality( $price, $start, $end, $available );
								if ( ! empty( $speciality_id ) ) {
									if ( $available == 'available' ) {
										add_post_meta( $post_id, 'lordcros_room_price_variation', $speciality_id );
										$price_variation[] = array( 'id' => $speciality_id, 'title' => $speciality_title );
									} else {
										add_post_meta( $post_id, 'lordcros_room_date_block', $speciality_id );
										$date_block[] = array( 'id' => $speciality_id, 'title' => $speciality_title );
									}
								}
							}							
						}
					}
				}
				
				wp_send_json_success( array( 'price_variation' => $price_variation, 'date_block' => $date_block ) );
			}
		}
	}
}