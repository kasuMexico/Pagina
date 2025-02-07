<?php
/*
 * Room Related Elements Define Functions
 */

defined( 'ABSPATH' ) || exit;

/* Room Search Form */
if ( ! function_exists( 'lordcros_core_room_search_form' ) ) {
	function lordcros_core_room_search_form() {
		
		$default_args = array(
			'date_from'			=> date( 'm/d/Y' ),
			'date_to'			=> date( 'm/d/Y', strtotime( ' +1 day' ) ),
			'adults'			=> 1,
			'kids'				=> 0,
			'max_price'			=> '',
			'min_price'			=> 0,
			'def_service'		=> array(),
			'extra_service'		=> array(),
		);
		extract( wp_parse_args( $_REQUEST, $default_args ) );

		$date_from_day = date( 'd', strtotime( $date_from ) );
		$date_from_month = date( 'M', strtotime( $date_from ) );
		$date_from_year = date( 'Y', strtotime( $date_from ) );
		$date_to_day = date( 'd', strtotime( $date_to ) );
		$date_to_month = date( 'M', strtotime( $date_to ) );
		$date_to_year = date( 'Y', strtotime( $date_to ) );

		$room_search_page = lordcros_get_opt( 'room_search_page' );

		ob_start();
		?>

		<div class="room-search-form-wrap">
			<div class="room-search-form">
				<?php if( empty( $room_search_page ) ) : ?>
					<p class="alert alert-warning"><?php echo esc_html__( 'Please config Room Search Page in theme options panel.', 'lordcros-core' ); ?></p>
				<?php else : ?>
					<form action="<?php echo esc_url( get_permalink( $room_search_page ) ); ?>" method="get">
						<div class="basic-fields-wrapper">
							<div class="form-input-area">
								<div id="form-check-in" class="search-calendar-show">
									<div class="check-in-section-wrap">
										<span class="section-title"><?php echo esc_html__( 'Check-In', 'lordcros-core' ); ?></span>
										<div class="section-content">
											<div class="selected-day">
												<div class="day-val"><?php echo esc_html( $date_from_day ); ?></div>
												<span class="month-val"><?php echo esc_html( $date_from_month ); ?></span>
												<span class="year-val"><?php echo esc_html( $date_from_year ); ?></span>
											</div>
											<i class="fas fa-chevron-down"></i>
										</div>
									</div>

									<input type="hidden" id="lc-booking-date-month-from" class="lc-booking-date-month-from" value="<?php echo esc_attr( $date_from_month ); ?>">
									<input type="hidden" id="lc-booking-date-day-from" class="lc-booking-date-day-from" value="<?php echo esc_attr( $date_from_day ); ?>">
									<input type="text" name="date_from" class="lc-booking-date-range-from sidebar-form" placeholder="Check In" value="<?php echo esc_attr( $date_from ); ?>">
								</div>
								<div id="form-check-out" class="search-calendar-show">
									<div class="check-out-section-wrap">
										<span class="section-title"><?php echo esc_html__( 'Check-Out', 'lordcros-core' ); ?></span>
										<div class="section-content">
											<div class="selected-day">
												<div class="day-val"><?php echo esc_html( $date_to_day ); ?></div>
												<span class="month-val"><?php echo esc_html( $date_to_month ); ?></span>
												<span class="year-val"><?php echo esc_html( $date_to_year ); ?></span>
											</div>
											<i class="fas fa-chevron-down"></i>
										</div>
									</div>

									<input type="hidden" id="lc-booking-date-month-to" class="lc-booking-date-month-to" value="<?php echo esc_attr( $date_to_month ); ?>">
									<input type="hidden" id="lc-booking-date-day-to" class="lc-booking-date-day-to" value="<?php echo esc_attr( $date_to_day ); ?>">
									<input type="text" name="date_to" class="lc-booking-date-range-to sidebar-form" placeholder="Check Out" value="<?php echo esc_attr( $date_to ); ?>">
								</div>
								<div id="form-guests-num" class="search-guest-count">
									<div class="guest-section-wrap">
										<span class="section-title"><?php echo esc_html__( 'Guests', 'lordcros-core' ); ?></span>
										<div class="section-content">
											<div class="guest-val"><?php echo esc_html( $adults ); ?></div>
											<div class="leftside-inner">
												<i class="fas fa-chevron-up"></i>
												<i class="fas fa-chevron-down"></i>
											</div>
										</div>
									</div>

									<input type="number" name="adults" id="lc-booking-form-guests" class="lc-booking-form-guests" placeholder="Guest" min="1" value="<?php echo esc_attr( $adults ); ?>">
								</div>
							</div>

							<div class="form-submit-wrap">
								<button type="submit" class="room-search-submit"><i class="lordcros lordcros-bell"></i><?php echo esc_html__( 'Check Rooms', 'lordcros-core' ); ?></button>
							</div>
						</div>
					</form>
				<?php endif; ?>
			</div>
		</div>
		
		<?php
		$output = ob_get_clean();

		ob_start();
		?>
		"use strict";
		$ = jQuery.noConflict();
		$(document).ready( function() {	
			$('.room-search-form-wrap .lc-booking-date-range-from').datepicker( 'setDate', '<?php echo esc_attr( $date_from ); ?>' );
			$('.room-search-form-wrap .lc-booking-date-range-to').datepicker( 'setDate', '<?php echo esc_attr( $date_to ); ?>' );
		});
		<?php
		$script = ob_get_clean();
		
		wp_add_inline_script( 'lordcros-theme-scripts', $script );

		return $output;
	}
}

/* Get Grid View Html for Room */
if ( ! function_exists( 'lordcros_core_room_get_grid_view_html' ) ) {
	function lordcros_core_room_get_grid_view_html( $room_id, $total_price = '', $date_from = '', $date_to = '', $adults = 1, $kids = 0, $title_tag = 'h3' ) {
		$title = get_the_title( $room_id );
		$permalink = get_permalink( $room_id );
		$query_args = array(
			'date_from'	=> $date_from,
			'date_to'	=> $date_to,
			'adults'	=> $adults,
			'kids'		=> $kids
		);

		$permalink = add_query_arg( $query_args, $permalink );
		$room_adults = rwmb_meta( 'lordcros_room_adults', '', $room_id );
		$room_kids = rwmb_meta( 'lordcros_room_children', '', $room_id );
		$size = rwmb_meta( 'lordcros_room_size', '', $room_id );
		$price_per_night = rwmb_meta( 'lordcros_room_price', '', $room_id );
		$def_services = rwmb_meta( 'lordcros_room_def_service', '', $room_id );
		$room_images = rwmb_meta( 'lordcros_room_image', '', $room_id );
		$ext_link = rwmb_meta( 'lordcros_room_ext_link', '', $room_id );

		$brief = apply_filters( 'the_content', get_post_field( 'post_content', $room_id ) );
		$brief = wp_trim_words( $brief, 11, '...' );

		$booking_page = lordcros_get_opt( 'room_booking_page' );

		ob_start();
		?>
	
		<div class="room-content-view-wrap">		
			<div class="room-grid-view">
				<div class="room-thumbs">
					<?php if ( ! empty( $room_images ) ) : ?>
						<ul class="owl-carousel room-gallery">
							<li class="gallery-item">
								<a href="<?php echo esc_url( $permalink ); ?>">
									<?php echo get_the_post_thumbnail( $room_id, 'lordcros-room-grid' ); ?>
								</a>
							</li>
							<?php foreach ( $room_images as $image ) : ?>
								<li class="gallery-item">
									<a href="<?php echo esc_url( $permalink ); ?>">
										<?php echo wp_get_attachment_image( $image['ID'], 'lordcros-room-grid' ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<a href="<?php echo esc_url( $permalink ); ?>" class="room-featured-img">
							<?php echo get_the_post_thumbnail( $room_id, 'lordcros-room-grid' ); ?>
						</a>
					<?php endif; ?>
					<span class="price-section">
						<?php
						if ( ! empty( $total_price ) ) {
							echo esc_html( lordcros_price( $total_price ) );
							echo '<span class="val-unit">' . esc_html__( '/trip', 'lordcros-core' ) . '</span>';
						} else {
							echo esc_html( lordcros_price( $price_per_night ) );
							echo '<span class="val-unit">' . esc_html__( '/night', 'lordcros-core' ) . '</span>';
						}
						?>
					</span>
				</div>
				<div class="room-infobox">
					<<?php echo esc_attr( $title_tag ); ?> class="room-title">
						<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
					</<?php echo esc_attr( $title_tag ); ?>>
					<div class="room-info">
						<div class="adults-info">
							<i class="lordcros lordcros-user"></i> 
							<span class="info-val"><?php echo esc_html( $room_adults ); ?> <?php echo esc_html__( 'GUESTS', 'lordcros-core' ); ?></span>
						</div>
						<div class="size-info">
							<i class="lordcros lordcros-plans"></i>
							<span class="info-val"><?php echo esc_html( $size ); ?> <?php echo lordcros_get_opt( 'size_unit', 'Ft' ); ?><sup>2</sup></span>
						</div>
					</div>
					<div class="brief">
						<?php echo esc_html( $brief ); ?>
					</div>
					<div class="default-services-wrap">
						<ul class="default-services">
							<?php
							if ( ! empty( $def_services ) ) {
								foreach ( $def_services as $def_service ) {
									$service_name = get_the_title( $def_service );
									$service_icon = rwmb_meta( 'lordcros_room_service_icon_class', '', $def_service );
									if ( empty( $service_icon ) ) {
										$service_icon = '';
									}
									
									$icon_images = rwmb_meta( 'lordcros_room_service_icon_image', array( 'limit' => 1 ), $def_service );
									if ( ! empty( $icon_images ) ) {
										$image = reset( $icon_images );
										?>
										<li class="service-icon">
											<a href="#" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr( $service_name ); ?>">
												<img src="<?php echo esc_attr( $image['url'] ); ?>">
											</a>
										</li>
										<?php	
									} else {
									?>
										<li class="service-icon">
											<a href="#" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr( $service_name ); ?>">
												<i class="<?php echo esc_attr( $service_icon ); ?>"></i>
											</a>
										</li>
									<?php
									}
								}
							}
							?>
						</ul>
					</div>

					<?php if ( empty( $total_price ) ) : ?>
						<?php if ( ! empty( $ext_link ) ) : ?>
							<a href="<?php echo esc_url( $ext_link ); ?>" class="room-book-btn archive-rooms-btn">
								<?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i>
							</a>
						<?php else : ?>
							<a href="<?php echo esc_url( $permalink ); ?>" class="room-book-btn archive-rooms-btn">
								<?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i>
							</a>
						<?php endif; ?>
					<?php else : ?>

						<?php if ( ! empty( $ext_link ) ) : ?>
							<form action="<?php echo esc_url( $ext_link ); ?>" method="get" class="available-room-book">
								<button type="submit"><?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i></button>
							</form>
						
						<?php elseif ( ! empty( $booking_page ) ) : ?>
							<form action="<?php echo esc_url( get_permalink( $booking_page ) ); ?>" method="post" class="available-room-book">
								<?php wp_nonce_field( 'room_add_cart', 'room_add_cart_wpnonce' . $room_id ); ?>
								<input type="hidden" name="room_id" value="<?php echo esc_attr( $room_id ); ?>">
								<input type="hidden" name="date_from" value="<?php echo esc_attr( $date_from ); ?>">
								<input type="hidden" name="date_to" value="<?php echo esc_attr( $date_to ); ?>">
								<input type="hidden" name="adults" value="<?php echo esc_attr( $adults ); ?>">
								<input type="hidden" name="kids" value="<?php echo esc_attr( $kids ); ?>">
								<input type="hidden" name="room_price" value="<?php echo esc_attr( $total_price ); ?>">
								<button type="submit"><?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i></button>
							</form>
						<?php else : ?>
							<a href="javascript:alert('<?php echo esc_html__( 'Please config Room Booking Page in theme options panel.', 'lordcros-core' ); ?>');" class="room-book-btn config-page-alert">
								<?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i>
							</a>
						<?php endif; ?>

					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php
		$html = ob_get_clean();
		return $html;
	}
}

/* Get List View Html for Room */
if ( ! function_exists( 'lordcros_core_room_get_list_view_html' ) ) {
	function lordcros_core_room_get_list_view_html( $room_id, $total_price = '', $date_from = '', $date_to = '', $adults = 1, $kids = 0, $title_tag = 'h3' ) {
		$title = get_the_title( $room_id );
		$permalink = get_permalink( $room_id );
		$query_args = array(
			'date_from'	=> $date_from,
			'date_to'	=> $date_to,
			'adults'	=> $adults,
			'kids'		=> $kids
		);

		$permalink = add_query_arg( $query_args, $permalink );
		$room_adults = rwmb_meta( 'lordcros_room_adults', '', $room_id );
		$room_kids = rwmb_meta( 'lordcros_room_children', '', $room_id );
		$size = rwmb_meta( 'lordcros_room_size', '', $room_id );
		$price_per_night = rwmb_meta( 'lordcros_room_price', '', $room_id );
		$def_services = rwmb_meta( 'lordcros_room_def_service', '', $room_id );
		$room_images = rwmb_meta( 'lordcros_room_image', '', $room_id );
		$ext_link = rwmb_meta( 'lordcros_room_ext_link', '', $room_id );

		$brief = apply_filters( 'the_content', get_post_field( 'post_content', $room_id ) );
		$brief = wp_trim_words( $brief, 11, '...' );

		$booking_page = lordcros_get_opt( 'room_booking_page' );

		ob_start();
		?>
		
		<div class="room-content-view-wrap">		
			<div class="room-list-view">
				<div class="room-thumbs">
					<?php if ( ! empty( $room_images ) ) : ?>
						<ul class="owl-carousel room-gallery">
							<li class="gallery-item">
								<a href="<?php echo esc_url( $permalink ); ?>">
									<?php echo get_the_post_thumbnail( $room_id, 'lordcros-room-list' ); ?>
								</a>
							</li>
							<?php foreach ( $room_images as $image ) : ?>
								<li class="gallery-item">
									<a href="<?php echo esc_url( $permalink ); ?>">
										<?php echo wp_get_attachment_image( $image['ID'], 'lordcros-room-list' ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<a href="<?php echo esc_url( $permalink ); ?>" class="room-featured-img">
							<?php echo get_the_post_thumbnail( $room_id, 'lordcros-room-list' ); ?>
						</a>
					<?php endif; ?>
					<span class="price-section">
						<?php
						if ( ! empty( $total_price ) ) {
							echo esc_html( lordcros_price( $total_price ) );
							echo '<span class="val-unit">' . esc_html__( '/trip', 'lordcros-core' ) . '</span>';
						} else {
							echo esc_html( lordcros_price( $price_per_night ) );
							echo '<span class="val-unit">' . esc_html__( '/night', 'lordcros-core' ) . '</span>';
						}
						?>
					</span>
				</div>
				<div class="room-infobox">
					<<?php echo esc_attr( $title_tag ); ?> class="room-title">
						<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
					</<?php echo esc_attr( $title_tag ); ?>>
					<div class="room-info">
						<div class="adults-info">
							<i class="lordcros lordcros-user"></i> 
							<span class="info-val"><?php echo esc_html( $room_adults ); ?> <?php echo esc_html__( 'GUESTS', 'lordcros-core' ); ?></span>
						</div>
						<div class="size-info">
							<i class="lordcros lordcros-plans"></i>
							<span class="info-val"><?php echo esc_html( $size ); ?> <?php echo lordcros_get_opt( 'size_unit', 'Ft' ); ?><sup>2</sup></span>
						</div>
					</div>
					<div class="brief">
						<?php echo esc_html( $brief ); ?>
					</div>
					<div class="service-book-btn-section">
						<div class="default-services-wrap">
							<ul class="default-services">
								<?php
								if ( ! empty( $def_services ) ) {
									foreach ( $def_services as $def_service ) {
										$service_name = get_the_title( $def_service );
										$service_icon = rwmb_meta( 'lordcros_room_service_icon_class', '', $def_service );
										if ( empty( $service_icon ) ) {
											$service_icon = '';
										}
										
										$icon_images = rwmb_meta( 'lordcros_room_service_icon_image', array( 'limit' => 1 ), $def_service );
										if ( ! empty( $icon_images ) ) {
											$image = reset( $icon_images );
											?>
											<li class="service-icon">
												<a href="#" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr( $service_name ); ?>">
													<img src="<?php echo esc_attr( $image['url'] ); ?>">
												</a>
											</li>
											<?php	
										} else {
										?>
											<li class="service-icon">
												<a href="#" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo esc_attr( $service_name ); ?>">
													<i class="<?php echo esc_attr( $service_icon ); ?>"></i>
												</a>
											</li>
										<?php
										}
									}
								}
								?>
							</ul>
						</div>

						<?php if ( empty( $total_price ) ) : ?>
							<?php if ( ! empty( $ext_link ) ) : ?>
								<a href="<?php echo esc_url( $ext_link ); ?>" class="room-book-btn archive-rooms-btn">
									<?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i>
								</a>
							<?php else : ?>
								<a href="<?php echo esc_url( $permalink ); ?>" class="room-book-btn archive-rooms-btn">
									<?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i>
								</a>
							<?php endif; ?>
						<?php else : ?>
							<?php if ( ! empty( $ext_link ) ) : ?>
								<form action="<?php echo esc_url( $ext_link ); ?>" method="get" class="available-room-book">
									<button type="submit"><?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i></button>
								</form>							
							<?php elseif ( ! empty( $booking_page ) ) : ?>
								<form action="<?php echo esc_url( get_permalink( $booking_page ) ); ?>" method="post" class="available-room-book">
									<?php wp_nonce_field( 'room_add_cart', 'room_add_cart_wpnonce' . $room_id ); ?>
									<input type="hidden" name="room_id" value="<?php echo esc_attr( $room_id ); ?>">
									<input type="hidden" name="date_from" value="<?php echo esc_attr( $date_from ); ?>">
									<input type="hidden" name="date_to" value="<?php echo esc_attr( $date_to ); ?>">
									<input type="hidden" name="adults" value="<?php echo esc_attr( $adults ); ?>">
									<input type="hidden" name="kids" value="<?php echo esc_attr( $kids ); ?>">
									<input type="hidden" name="room_price" value="<?php echo esc_attr( $total_price ); ?>">
									<button type="submit"><?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i></button>
								</form>
							<?php else : ?>
								<a href="javascript:alert('<?php echo esc_html__( 'Please config Room Booking Page in theme options panel.', 'lordcros-core' ); ?>');" class="room-book-btn config-page-alert"">
									<?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i>
								</a>
							<?php endif; ?>

						<?php endif; ?>
					</div>
				</div>
			</div>		
		</div>
		
		<?php
		$html = ob_get_clean();
		return $html;
	}
}

/* Get Block View Html for Room */
if ( ! function_exists( 'lordcros_core_room_get_block_view_html' ) ) {
	function lordcros_core_room_get_block_view_html( $room_id, $total_price = '', $date_from = '', $date_to = '', $adults = 1, $kids = 0 ) {
		$title = get_the_title( $room_id );
		$permalink = get_permalink( $room_id );
		$query_args = array(
			'date_from'	=> $date_from,
			'date_to'	=> $date_to,
			'adults'	=> $adults,
			'kids'		=> $kids
		);

		$permalink = add_query_arg( $query_args, $permalink );
		$price_per_night = rwmb_meta( 'lordcros_room_price', '', $room_id );

		$booking_page = lordcros_get_opt( 'room_booking_page' );

		ob_start();
		?>

		<div class="room-content-view-wrap">		
			<div class="room-block-view">
				<div class="room-thumbs">
					<a href="<?php echo esc_url( $permalink ); ?>" class="block-room-featured-img">
						<?php echo get_the_post_thumbnail( $room_id, 'lordcros-room-block' ); ?>
					</a>
					<span class="price-section">
						<?php
						if ( ! empty( $total_price ) ) {
							echo esc_html( lordcros_price( $total_price ) ) . esc_html__( '/trip', 'lordcros-core' );
						} else {
							echo esc_html( lordcros_price( $price_per_night ) ) . esc_html__( '/night', 'lordcros-core' );
						}
						?>
					</span>
					<h2 class="room-title">
						<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
					</h2>
					<div class="room-book-btn-section">
						<?php if ( empty( $total_price ) ) : ?>
							<a href="<?php echo esc_url( $permalink ); ?>" class="room-book-btn archive-rooms-btn">
								<?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i>
							</a>
						<?php else : ?>
							
							<?php if ( ! empty( $booking_page ) ) : ?>
								<form action="<?php echo esc_url( get_permalink( $booking_page ) ); ?>" method="post" class="available-room-book">
									<?php wp_nonce_field( 'room_add_cart', 'room_add_cart_wpnonce' . $room_id ); ?>
									<input type="hidden" name="room_id" value="<?php echo esc_attr( $room_id ); ?>">
									<input type="hidden" name="date_from" value="<?php echo esc_attr( $date_from ); ?>">
									<input type="hidden" name="date_to" value="<?php echo esc_attr( $date_to ); ?>">
									<input type="hidden" name="adults" value="<?php echo esc_attr( $adults ); ?>">
									<input type="hidden" name="kids" value="<?php echo esc_attr( $kids ); ?>">
									<input type="hidden" name="room_price" value="<?php echo esc_attr( $total_price ); ?>">
									<button type="submit"><?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i></button>
								</form>
							<?php else : ?>
								<a href="javascript:alert('<?php echo esc_html__( 'Please config Room Booking Page in theme options panel.', 'lordcros-core' ); ?>');" class="room-book-btn config-page-alert">
									<?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i>
								</a>
							<?php endif; ?>

						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<?php
		$html = ob_get_clean();
		return $html;
	}
}

/* Room Check Availability Form */
if ( ! function_exists( 'lordcros_core_room_check_availability_form' ) ) {
	function lordcros_core_room_check_availability_form( $room_id, $date_from = '', $date_to = '', $adults = 1, $kids = 0 ) {
		if ( empty( $room_id ) || ! intval( $room_id ) ) {
			return;
		}

		if ( empty( $date_from ) ) {
			$date_from = date( 'm/d/Y' );
		}
		if ( empty( $date_to ) ) {
			$date_to = date( 'm/d/Y', strtotime( ' +1 day' ) );
		}

		$date_from_day = date( 'd', strtotime( $date_from ) );
		$date_from_month = date( 'M', strtotime( $date_from ) );
		$date_from_year = date( 'Y', strtotime( $date_from ) );
		$date_to_day = date( 'd', strtotime( $date_to ) );
		$date_to_month = date( 'M', strtotime( $date_to ) );
		$date_to_year = date( 'Y', strtotime( $date_to ) );

		$booking_page = lordcros_get_opt( 'room_booking_page' );

		ob_start();
		?>		
			<div class="room-search-form room-check-form">
				
				<?php if ( ! empty( $booking_page ) ) : ?>
					
					<form id="room-check-form" action="<?php echo esc_url( get_permalink( $booking_page ) ); ?>" method="post">
						<input type="hidden" name="room_id" value="<?php echo esc_attr( $room_id ); ?>">
						<input type="hidden" name="action" value="room_check_availability">
						<input type="hidden" name="room_price" value="">
						<?php wp_nonce_field( 'room_add_cart', 'room_add_cart_wpnonce' . $room_id ); ?>

						<div class="basic-fields-wrapper">
							<div class="form-input-area">
								<div id="form-check-in" class="search-calendar-show">
									<div class="check-in-section-wrap">
										<span class="section-title"><?php echo esc_html__( 'Check-In', 'lordcros-core' ); ?></span>
										<div class="section-content">
											<div class="selected-day">
												<span class="day-val"><?php echo esc_html( $date_from_day ); ?></span>
												<span class="month-val"><?php echo esc_html( $date_from_month ); ?></span>
												<span class="year-val"><?php echo esc_html( $date_from_year ); ?></span>
											</div>
											<i class="fas fa-chevron-down"></i>
										</div>
									</div>

									<input type="hidden" id="lc-booking-date-month-from" class="lc-booking-date-month-from" value="<?php echo esc_attr( $date_from_month ); ?>">
									<input type="hidden" id="lc-booking-date-day-from" class="lc-booking-date-day-from" value="<?php echo esc_attr( $date_from_day ); ?>">
									<input type="text" name="date_from" class="lc-booking-date-range-from" placeholder="Check In" value="<?php echo esc_attr( $date_from ); ?>">
								</div>
								<div id="form-check-out" class="search-calendar-show">
									<div class="check-out-section-wrap">
										<span class="section-title"><?php echo esc_html__( 'Check-Out', 'lordcros-core' ); ?></span>
										<div class="section-content">
											<div class="selected-day">
												<span class="day-val"><?php echo esc_html( $date_to_day ); ?></span>
												<span class="month-val"><?php echo esc_html( $date_to_month ); ?></span>
												<span class="year-val"><?php echo esc_html( $date_to_year ); ?></span>
											</div>
											<i class="fas fa-chevron-down"></i>
										</div>
									</div>

									<input type="hidden" id="lc-booking-date-month-to" class="lc-booking-date-month-to" value="<?php echo esc_attr( $date_to_month ); ?>">
									<input type="hidden" id="lc-booking-date-day-to" class="lc-booking-date-day-to" value="<?php echo esc_attr( $date_to_day ); ?>">
									<input type="text" name="date_to" class="lc-booking-date-range-to" placeholder="Check Out" value="<?php echo esc_attr( $date_to ); ?>">
								</div>
								<div id="form-guests-num" class="search-guest-count">
									<div class="guest-section-wrap">
										<span class="section-title"><?php echo esc_html__( 'Guests', 'lordcros-core' ); ?></span>
										<div class="section-content">
											<div class="guest-val"><?php echo esc_html( $adults ); ?></div>
											<div class="leftside-inner">
												<i class="fas fa-chevron-up"></i>
												<i class="fas fa-chevron-down"></i>
											</div>
										</div>
									</div>

									<input type="number" name="adults" id="lc-booking-form-guests" class="lc-booking-form-guests" placeholder="Guest" min="1" value="<?php echo esc_attr( $adults ); ?>">
								</div>
							</div>

							<div class="form-submit-wrap">
								<button type="submit" class="room-search-submit"><i class="lordcros lordcros-bell"></i><?php echo esc_html__( 'Check Availability', 'lordcros-core' ); ?></button>
							</div>
						</div>
					</form>
				
				<?php else : ?>

					<p class="alert alert-warning"><?php echo esc_html__( 'Please config Room Booking Page in theme options panel.', 'lordcros-core' ); ?></p>

				<?php endif; ?>

			</div>
		<?php
		$output = ob_get_clean();

		ob_start();
		?>
		"use strict";
		$ = jQuery.noConflict();
		$(document).ready( function() {	
			$('.room-check-form .lc-booking-date-range-from').datepicker( 'setDate', '<?php echo esc_attr( $date_from ); ?>' );
			$('.room-check-form .lc-booking-date-range-to').datepicker( 'setDate', '<?php echo esc_attr( $date_to ); ?>' );
		});
		<?php
		$script = ob_get_clean();
		
		wp_add_inline_script( 'lordcros-theme-scripts', $script );

		return $output;
	}
}