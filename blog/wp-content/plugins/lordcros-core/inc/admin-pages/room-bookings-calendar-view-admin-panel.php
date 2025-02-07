<?php
/*
 * Room booking calendar view page
 */

defined( 'ABSPATH' ) || exit;

/* Booking calendar view admin panel main actions */
if ( ! function_exists( 'lordcros_core_room_booking_calendar_view_render_pages' ) ) {
	function lordcros_core_room_booking_calendar_view_render_pages() {
		global $wpdb;
		if ( empty( $_REQUEST['date_from'] ) ) {
			$_REQUEST['date_from'] = date( 'Y-m-d', strtotime( '-15 days' ) );
		}
		if ( empty( $_REQUEST['date_to'] ) ) {
			$_REQUEST['date_to'] = date( 'Y-m-d', strtotime( '+15 days' ) );
		}
		?>
		<div class="wrap">

			<h2><?php echo esc_html__( 'Room Bookings Calendar View', 'lordcros-core' ); ?></h2>

			<form method="get">
				<input type="hidden" name="page" value="room_bookings_calendar_view" />
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
			</form>

			<div class="booking-status-color">
				<div class="status-new">
					<span class="color-val"></span>
					<span class="status-title"><?php echo esc_html__( 'New', 'lordcros-core' ); ?></span>
				</div>

				<div class="status-pending">
					<span class="color-val"></span>
					<span class="status-title"><?php echo esc_html__( 'Pending', 'lordcros-core' ); ?></span>
				</div>

				<div class="status-confirmed">
					<span class="color-val"></span>
					<span class="status-title"><?php echo esc_html__( 'Confirmed', 'lordcros-core' ); ?></span>
				</div>

				<div class="status-canceled">
					<span class="color-val"></span>
					<span class="status-title"><?php echo esc_html__( 'Canceled', 'lordcros-core' ); ?></span>
				</div>
			</div>
		</div>
		<?php
		$booking_data = lordcros_core_get_calendar_view_booking_data( $_REQUEST );
		$date_from_obj = new DateTime( $_REQUEST['date_from'] );
		$date_to_obj = new DateTime( $_REQUEST['date_to'] );
		$date_to_obj = $date_to_obj->modify( '+1 day' ); 
		$date_interval = DateInterval::createFromDateString( '1 day' );
		$period = new DatePeriod( $date_from_obj, $date_interval, $date_to_obj );
		
		?>
		<div class="wrapper booking-calendar-wrapper">
			<table class="calendar_view">
				<thead>
					<th><?php echo esc_html__( 'Room', 'lordcros-core' ); ?></th>
					<?php foreach ( $period as $dt ) : ?>
						<th>
							<span class="month"><?php echo $dt->format( "M" ); ?></span>
							<span class="day"><?php echo $dt->format( "d" ); ?></span>
						</th>
					<?php endforeach; ?>
				</thead>
				
				<?php
				foreach ( $booking_data as $d ) {
					if ( $d['status'] == 'new' ) {
						$class = "td-new";
					} elseif ( $d['status'] == 'pending' ) {
						$class = "td-pending";
					} elseif ( $d['status'] == 'confirmed' ) {
						$class = "td-confirmed";
					} else {
						$class = "td-canceled";
					}
					$td_content = '<a href="' . admin_url( 'admin.php?page=room_bookings&action=edit&booking_id=' . $d['id'] ) . '">' . $d['first_name'] . ' ' . $d['last_name'] . '</a>';
					?>
					
					<tr>
						<td><?php echo get_the_title( $d['post_id'] ); ?></td>
						
						<?php
						$count = 0;
						foreach ( $period as $dt ) {
							
							if ( strtotime( $d['date_from'] ) <= strtotime( $dt->format( "Y-m-d" ) ) && strtotime( $d['date_to'] ) >= strtotime( $dt->format( "Y-m-d" ) ) ) {
								if ( strtotime( $d['date_from'] ) == strtotime( $dt->format( "Y-m-d" ) ) ) {
									$class .= " started";
								}

								if ( strtotime( $d['date_to'] ) == strtotime( $dt->format( "Y-m-d" ) ) ) {
									$class .= " ended";
								}

								$count++;
								continue;
							}
							
							if ( $count == 0 ) {
								?>
								<td></td>
								<?php
							} else {
								?>
								<td class="<?php echo esc_attr( $class ); ?>" <?php echo ( ! empty( $count ) ) ? 'colspan="' . $count . '"' : ''; ?> >
									<?php echo $td_content; ?>
								</td>
								<?php
								$count = 0;
							}
						}

						if ( $count == 0 ) {
							?>
							<td></td>
							<?php
						} else {
							?>
							<td class="<?php echo esc_attr( $class ); ?>" <?php echo ( ! empty( $count ) ) ? 'colspan="' . $count . '"' : ''; ?> >
								<?php echo $td_content; ?>
							</td>
							<?php
							$count = 0;
						}
						?>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
		<?php
		
	}
}