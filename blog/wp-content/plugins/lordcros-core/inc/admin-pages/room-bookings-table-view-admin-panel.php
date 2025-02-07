<?php
/*
 * Room booking table view page
 */

defined( 'ABSPATH' ) || exit;

/* Booking table view admin panel main actions */
if ( ! function_exists( 'lordcros_core_room_booking_table_view_render_pages' ) ) {
	function lordcros_core_room_booking_table_view_render_pages() {
		$default_lang = lordcros_core_get_default_language();
		lordcros_core_switch_language( $default_lang );

		if ( ( ! empty( $_REQUEST['action'] ) ) && ( ( 'add' == $_REQUEST['action'] ) || ( 'edit' == $_REQUEST['action'] ) ) ) {
			lordcros_core_room_booking_table_view_render_manage_page();
		} elseif ( ( ! empty( $_REQUEST['action'] ) ) && ( 'delete' == $_REQUEST['action'] ) ) {
			lordcros_core_room_booking_delete_action();			
		} else {
			lordcros_core_room_booking_table_view_render_list_page();
		}
	}
}

/* Render booking list page */
if ( ! function_exists( 'lordcros_core_room_booking_table_view_render_list_page' ) ) {
	function lordcros_core_room_booking_table_view_render_list_page() {
		global $wpdb;

		$lordcrosBookingTable = new LordCros_Core_Room_Booking_List_Table();
		$lordcrosBookingTable->prepare_items();
		?>

		<div class="wrap">

			<h2><?php echo esc_html__( 'Room Bookings', 'lordcros-core' ); ?><a href="admin.php?page=room_bookings&amp;action=add" class="add-new-h2"><?php echo esc_html__( 'Add New', 'lordcros-core' ); ?></a></h2>

			<?php 
			if ( isset( $_REQUEST['bulk_delete'] ) && isset( $_REQUEST['items'] ) ) {
				echo '<div id="message" class="updated below-h2"><p>' . esc_html( sprintf( esc_html__( '%d bookings deleted', 'lordcros-core' ), $_REQUEST['items'] ) ) . '</p></div>';
			}

			if ( isset( $_REQUEST['bulk_update'] ) && isset( $_REQUEST['items'] ) ) {
				echo '<div id="message" class="updated below-h2"><p>' . esc_html( sprintf( esc_html__( '%d bookings updated', 'lordcros-core' ), $_REQUEST['items'] ) ) . '</p></div>';
			}
			?>

			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
				<select id="room_filter" name="post_id" data-options='{"allowClear":true,"width":"250px","placeholder":"<?php echo esc_attr__( 'Select a Room', 'lordcros-core' ); ?>"}' class="rwmb-post rwmb-select_advanced select2-hidden-accessible" >
					<option></option>
					<?php
					$args = array(
							'post_type'			=> 'room',
							'posts_per_page'	=> -1,
							'bookingby'			=> 'title',
							'booking'			=> 'ASC',
					);
					if ( ! current_user_can( 'manage_options' ) ) {
						$args['author'] = get_current_user_id();
					}
					$room_query = new WP_Query( $args );

					if ( $room_query->have_posts() ) {
						while ( $room_query->have_posts() ) {
							$room_query->the_post();

							$selected = '';
							$id = $room_query->post->ID;
							echo '<option ' . selected( $_REQUEST['post_id'], $id ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
						}
					} else {
						// no posts found
					}
					
					// Restore original Post Data
					wp_reset_postdata();
					?>
				</select>
				<input type="text" id="date_from_filter" data-options='{"timeFormat":"HH:mm", "separator":" ", "dateFormat":"yy-mm-dd", "showButtonPanel":true}' class="rwmb-date" name="date_from" placeholder="<?php echo esc_attr__( 'Date From', 'lordcros-core' ) ?>" value="<?php if ( ! empty( $_REQUEST['date_from'] ) ) { echo esc_attr( $_REQUEST['date_from'] ); } ?>" autocomplete="off">
				<input type="text" id="date_to_filter" data-options='{"timeFormat":"HH:mm", "separator":" ", "dateFormat":"yy-mm-dd", "showButtonPanel":true}' class="rwmb-date" name="date_to" placeholder="<?php echo esc_attr__( 'Date To', 'lordcros-core' ) ?>" value="<?php if ( ! empty( $_REQUEST['date_to'] ) ) { echo esc_attr( $_REQUEST['date_to'] ); } ?>" autocomplete="off">
				<select name="status" id="status_filter">
					<option value=""><?php echo esc_html__( 'Select Status', 'lordcros-core' ) ?></option>
					<?php
						$statuses = array( 
							'new'		=> esc_html__( 'New', 'lordcros-core' ), 
							'confirmed'	=> esc_html__( 'Confirmed', 'lordcros-core' ), 
							'canceled'	=> esc_html__( 'Canceled', 'lordcros-core' ), 
							'pending'	=> esc_html__( 'Pending', 'lordcros-core' ) 
						);

						foreach( $statuses as $key => $status ) { 
							?>
							<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, isset( $_REQUEST['status'] ) ? esc_attr( $_REQUEST['status'] ) : '' ); ?>>
								<?php echo esc_attr( $status ) ?>
							</option>
							<?php 
						}
					?>
				</select>
				<input type="submit" name="booking_filter" id="room-booking-filter" class="button" value="<?php echo esc_attr__( 'Filter', 'lordcros-core' ); ?>">
				<a href="admin.php?page=room_bookings" class="button-secondary"><?php echo esc_html__( 'Show All', 'lordcros-core' ) ?></a>
			</form>

			<form id="rooms-bookings-filter" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
				<?php $lordcrosBookingTable->display() ?>
			</form>
			
		</div>
		<?php
	}
}

/* Render booking detail page */
if ( ! function_exists( 'lordcros_core_room_booking_table_view_render_manage_page' ) ) {
	function lordcros_core_room_booking_table_view_render_manage_page() {
		global $wpdb;

		if ( ! empty( $_POST['save'] ) ) {
			lordcros_room_booking_save_action();
			return;
		}

		$booking_data = array();
		$room_data = array();
		$service_data = array();

		if ( 'edit' == $_REQUEST['action'] ) {

			if ( empty( $_REQUEST['booking_id'] ) ) {
				echo "<h2>" . esc_html__( "You attempted to edit an item that doesn't exist. Perhaps it was deleted?" , 'lordcros-core' ) . "</h2>";
				return;
			}

			$booking_id = $_REQUEST['booking_id'];
			$post_table_name = $wpdb->prefix . 'posts';

			$booking = new LordCros_Core_Room_Booking( $booking_id );
			$booking_data = $booking->get_booking_info();

			if ( empty( $booking_data ) ) {
				echo "<h2>" . esc_html__( "You attempted to edit an item that doesn't exist. Perhaps it was deleted?" , 'lordcros-core' ) . "</h2>";
				return;
			}
		}

		$default_booking_data = lordcros_core_default_booking_data();
		$booking_data = array_replace( $default_booking_data , $booking_data );
		$site_currency_symbol = apply_filters( 'currency_symbol', '' );
		?>

		<div class="wrap">
			<?php $page_title = ( 'edit' == $_REQUEST['action'] ) ? esc_html__( 'Edit Room Booking', 'lordcros-core' ) . '<a href="admin.php?page=room_bookings&amp;action=add" class="add-new-h2">' . esc_html__( 'Add New', 'lordcros-core' ) . '</a>' : esc_html__( 'Add New Room Booking', 'lordcros-core' ); ?>
			
			<h2><?php echo wp_kses_post( $page_title ); ?></h2>

			<?php if ( isset( $_REQUEST['updated'] ) ) { echo '<div id="message" class="updated below-h2"><p>' . esc_html__( 'Booking saved', 'lordcros-core' ) . '</p></div>'; } ?>

			<form method="post" id="booking-form" class="room-booking-form" onsubmit="return manage_booking_validateForm();" data-message="<?php echo esc_attr( esc_html__( 'Please select a room', 'lordcros-core' ) ); ?>">

				<input type="hidden" name="id" value="<?php echo esc_attr( $booking_data['id'] ); ?>">

				<div class="row postbox booking-table-view-inner">
					<div class="one-half">
						<h3><?php echo esc_html__( 'Booking Detail', 'lordcros-core' ); ?></h3>

						<table class="lordcros_admin_table lordcros_booking_manage_table">
							<tr>
								<th><?php echo esc_html__( 'Room', 'lordcros-core' ); ?></th>
								<td>
									<select name="post_id" id="post_id">
										<option></option>
										<?php
											$args = array(
													'post_type'			=> 'room',
													'posts_per_page'	=> -1,
													'bookingby'			=> 'title',
													'booking'			=> 'ASC'
											);
											if ( ! current_user_can( 'manage_options' ) ) {
												$args['author'] = get_current_user_id();
											}
											$room_query = new WP_Query( $args );

											if ( $room_query->have_posts() ) {
												while ( $room_query->have_posts() ) {
													$room_query->the_post();
													$selected = '';
													$id = $room_query->post->ID;
													if ( $booking_data['post_id'] == $id ) {
														$selected = ' selected ';
													}
													echo '<option ' . esc_attr( $selected ) . 'value="' . esc_attr( $id ) .'">' . wp_kses_post( get_the_title( $id ) ) . '</option>';
												}
											}
											wp_reset_postdata();
										?>
									</select>
								</td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Date From', 'lordcros-core' ) ?></th>
								<td><input type="text" class="rwmb-date" data-options='{"timeFormat":"HH:mm", "separator":" ", "dateFormat":"yy-mm-dd", "showButtonPanel":true}' name="date_from" id="date_from" value="<?php echo esc_attr( $booking_data['date_from'] ); ?>"  autocomplete="off"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Date To', 'lordcros-core' ) ?></th>
								<td><input type="text" class="rwmb-date" data-options='{"timeFormat":"HH:mm", "separator":" ", "dateFormat":"yy-mm-dd", "showButtonPanel":true}' name="date_to" id="date_to" value="<?php echo esc_attr( $booking_data['date_to'] ); ?>"  autocomplete="off"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Adults', 'lordcros-core' ) ?></th>
								<td><input type="number" name="adults" value="<?php echo esc_attr( $booking_data['adults'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Extra Services', 'lordcros-core' ) ?></th>
								<td>
									<?php
										$booked_extra_services = array();
										$data_selected_str = '';

										if ( ! empty( $booking_data['extra_service'] ) ) {
											$booked_extra_services = unserialize( $booking_data['extra_service'] );
											$data_selected_str = json_encode( array_values( $booked_extra_services ) );
										}
										
										$args = array(
													'posts_per_page'	=> -1,
													'post_type'			=> 'room_service',
													'post_status'		=> 'publish',
													'meta_query'		=> array(
														array(
															'key'		=> 'lordcros_room_service_type',
															'value'		=> 'extra_service',
														),
													),
												);
										$extra_services = get_posts( $args );
									?>
									<select data-options='{"allowClear":true,"width":"300px","placeholder":"<?php echo esc_attr( 'Select Specialities', 'lordcros-core' ); ?>"}' multiple="" class="rwmb-select_advanced select2-hidden-accessible" name="extra_service[]" data-selected='<?php echo esc_attr( $data_selected_str ); ?>' aria-hidden="true">
										<?php if ( ! empty( $extra_services ) )	: ?>
											<?php foreach ( $extra_services as $extra_service ) : ?>
 												<option value="<?php echo esc_attr( $extra_service->ID ); ?>" <?php echo ( in_array( $extra_service->ID, $booked_extra_services ) ) ? 'selected' : ''; ?> ><?php echo esc_html( $extra_service->post_title ); ?></option>
											<?php endforeach; ?>
										<?php endif; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Room Price', 'lordcros-core' ); ?> (<?php echo esc_html( $site_currency_symbol ); ?>)</th>
								<td><input type="text" name="room_price" value="<?php echo esc_attr( $booking_data['room_price'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Service Price', 'lordcros-core' ) ?> (<?php echo esc_html( $site_currency_symbol ); ?>)</th>
								<td><input type="text" name="service_price" value="<?php echo esc_attr( $booking_data['service_price'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Total Price', 'lordcros-core' ) ?> (<?php echo esc_html( $site_currency_symbol ); ?>)</th>
								<td><input type="text" name="total_price" value="<?php echo esc_attr( $booking_data['total_price'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Coupon Code', 'lordcros-core' ) ?></th>
								<td><input type="text" name="coupon_code" value="<?php echo esc_attr( $booking_data['coupon_code'] ); ?>"> <?php echo esc_html( $site_currency_symbol ); ?></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Discounted Price', 'lordcros-core' ) ?></th>
								<td><input type="text" name="discounted_price" value="<?php echo esc_attr( $booking_data['discounted_price'] ); ?>"> <?php echo esc_html( $site_currency_symbol ); ?></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Payment Type', 'lordcros-core' ) ?></th>
								<td>
									<select name="payment">
										<?php $statuses = array(
																'inquiry'		=> esc_html__( 'Just Request', 'lordcros-core' ),
																'paypal'		=> esc_html__( 'Paypal', 'lordcros-core' ),
																'stripe'		=> esc_html__( 'Stripe', 'lordcros-core' ),
																'bank_transfer'	=> esc_html__( 'Bank Transfer', 'lordcros-core' )
															);
											if ( ! isset( $booking_data['status'] ) ) {
												$booking_data['status'] = 'inquiry';
											}
										?>
										<?php foreach ( $statuses as $key => $content) { ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $booking_data['payment'] ); ?>><?php echo esc_html( $content ); ?></option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Payment Transaction ID', 'lordcros-core' ); ?></th>
								<td><input type="text" name="transaction_id" value="<?php echo esc_attr( $booking_data['transaction_id'] ) ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Status', 'lordcros-core' ); ?></th>
								<td>
									<select name="status">
										<?php $statuses = array( 'new' => esc_html__( 'New', 'lordcros-core' ), 'confirmed' => esc_html__( 'Confirmed', 'lordcros-core' ), 'canceled' => esc_html__( 'Canceled', 'lordcros-core' ), 'pending' => esc_html__( 'Pending', 'lordcros-core' ) );
											if ( ! isset( $booking_data['status'] ) ) {
												$booking_data['status'] = 'new';
											}
										?>
										<?php foreach ( $statuses as $key => $content) { ?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $booking_data['status'] ); ?>><?php echo esc_html( $content ); ?></option>
										<?php } ?>
									</select>
								</td>
							</tr>
						</table>
					</div>

					<div class="one-half">
						<h3><?php echo esc_html__( 'Customer Infomation', 'lordcros-core' ); ?></h3>
						<table  class="lordcros_admin_table lordcros_booking_manage_table">
							<tr>
								<th><?php echo esc_html__( 'First Name', 'lordcros-core' ); ?></th>
								<td><input type="text" name="first_name" value="<?php echo esc_attr( $booking_data['first_name'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Last Name', 'lordcros-core' ); ?></th>
								<td><input type="text" name="last_name" value="<?php echo esc_attr( $booking_data['last_name'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Email', 'lordcros-core' ); ?></th>
								<td><input type="email" name="email" value="<?php echo esc_attr( $booking_data['email'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Phone', 'lordcros-core' ); ?></th>
								<td><input type="text" name="phone" value="<?php echo esc_attr( $booking_data['phone'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Street line 1', 'lordcros-core' ); ?></th>
								<td><input type="text" name="address1" value="<?php echo esc_attr( $booking_data['address1'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Street line 2', 'lordcros-core' ); ?></th>
								<td><input type="text" name="address2" value="<?php echo esc_attr( $booking_data['address2'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'City', 'lordcros-core' ); ?></th>
								<td><input type="text" name="city" value="<?php echo esc_attr( $booking_data['city'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'State', 'lordcros-core' ); ?></th>
								<td><input type="text" name="state" value="<?php echo esc_attr( $booking_data['state'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Postal Code', 'lordcros-core' ); ?></th>
								<td><input type="text" name="zip" value="<?php echo esc_attr( $booking_data['zip'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Country', 'lordcros-core' ); ?></th>
								<td><input type="text" name="country" value="<?php echo esc_attr( $booking_data['country'] ); ?>"></td>
							</tr>
							<tr>
								<th><?php echo esc_html__( 'Special Requirements', 'lordcros-core' ); ?></th>
								<td><textarea name="special_requirements"><?php echo esc_textarea( stripslashes( $booking_data['special_requirements'] ) ); ?></textarea></td>
							</tr>
						</table>
					</div>
				</div>

				<input type="submit" class="button-primary button_save_booking" name="save" value="<?php echo esc_attr__( 'Save booking', 'lordcros-core' ); ?>">

				<a href="admin.php?page=room_bookings" class="button-secondary"><?php echo esc_html__( 'Cancel', 'lordcros-core' ); ?></a>
				<?php wp_nonce_field( 'lordcros_manage_bookings','booking_save' ); ?>
			</form>
		</div>

		<?php
	}
}

/* Delete booking action */
if ( ! function_exists( 'lordcros_core_room_booking_delete_action' ) ) {
	function lordcros_core_room_booking_delete_action() {
		global $wpdb;
		// data validation
		if ( empty( $_REQUEST['booking_id'] ) ) {
			print esc_html__( 'Sorry, you tried to remove nothing.', 'lordcros-core' );
			exit;
		}

		// nonce check
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'booking_delete' ) ) {
			print esc_html__( 'Sorry, your nonce did not verify.', 'lordcros-core' );
			exit;
		}

		// do action
		$sql = sprintf( 'DELETE FROM %1$s WHERE id = %2$s', LORDCROS_ROOM_BOOKINGS_TABLE, '%d' );
		$wpdb->query( $wpdb->prepare( $sql, $_REQUEST['booking_id'] ) );
		wp_redirect( admin_url( 'admin.php?page=room_bookings' ) );
		exit;
	}
}

/* Save booking action */
if ( ! function_exists( 'lordcros_room_booking_save_action' ) ) {
	function lordcros_room_booking_save_action() {
		//validation
		if ( ! isset( $_POST['booking_save'] ) || ! wp_verify_nonce( $_POST['booking_save'], 'lordcros_manage_bookings' ) ) {
			print esc_html__( 'Sorry, your nonce did not verify.', 'lordcros-core' );
			exit;
		}

		if ( empty( $_POST['post_id'] ) || 'room' != get_post_type( $_POST['post_id'] ) ) {
			print esc_html__( 'Invalide Room ID.', 'lordcros-core' );
			exit;
		}

		global $wpdb;
		$default_booking_data = lordcros_core_default_booking_data( 'update' );
		$booking_data = array();
		foreach ( $default_booking_data as $table_field => $def_value ) {
			if ( isset( $_POST[ $table_field ] ) ) {
				$booking_data[ $table_field ] = $_POST[ $table_field ];
				if ( ! is_array( $_POST[ $table_field ] ) ) {
					$booking_data[ $table_field ] = sanitize_text_field( $booking_data[ $table_field ] );
				} else {
					$booking_data[ $table_field ] = serialize( $booking_data[ $table_field ] );
				}
			}
		}

		$booking_data = array_replace( $default_booking_data, $booking_data );

		$booking_data['post_id'] = lordcros_core_room_org_id( $booking_data['post_id'] );
		if ( empty( $_POST['id'] ) ) {
			//insert
			$booking_data['created'] = date( 'Y-m-d H:i:s' );
			$wpdb->insert( LORDCROS_ROOM_BOOKINGS_TABLE, $booking_data );
			$booking_id = $wpdb->insert_id;
		} else {
			//update
			$wpdb->update( LORDCROS_ROOM_BOOKINGS_TABLE, $booking_data, array( 'id' => sanitize_text_field( $_POST['id'] ) ) );
			$booking_id = sanitize_text_field( $_POST['id'] );
		}

		do_action( 'lordcros_room_send_confirmation_email', $booking_id );
		wp_redirect( admin_url( 'admin.php?page=room_bookings&action=edit&booking_id=' . $booking_id . '&updated=true' ) );
		exit;
	}
}
