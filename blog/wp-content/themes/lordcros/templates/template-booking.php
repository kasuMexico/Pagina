<?php
/*
 Template Name: Booking Page Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

lordcros_page_heading(); // Template Page Banner Heading

$room_search_page = lordcros_get_opt( 'room_search_page' );
if ( ! empty( $room_search_page ) ) {
	$room_search_page_url = get_permalink( $room_search_page );
} else {
	$room_search_page_url = "javascript:alert('" . esc_html__( 'Please config Room Search Page in theme options panel.', 'lordcros' ) . "');";
}

$room_checkout_page = lordcros_get_opt( 'room_checkout_page' );
if ( ! empty( $room_checkout_page ) ) {
	$room_checkout_page_url = get_permalink( $room_checkout_page );
} else {
	$room_checkout_page_url = "";
}

$user_info = lordcros_get_current_user_info();

if ( have_posts() ) {
	while ( have_posts() ) : the_post();
		$post_id = get_the_ID();
		?>

		<?php if ( ! isset( $_POST['room_id'] ) ) : ?>
			<div class="container">
				<div class="empty-room-desc">
					<i class="fas fa-exclamation-triangle"></i>
					<h2 class="title"><?php echo esc_html__( 'There are no rooms currently booked.', 'lordcros' ); ?></h2>
					<p class="desc"><?php echo esc_html__( 'You need to book any rooms first. Please search your rooms, and make a reservation.', 'lordcros' ); ?></p>
					<a href="<?php echo esc_url( $room_search_page_url ); ?>" class="empty-search-room button"><?php echo esc_html__( 'Search Rooms', 'lordcros' ); ?></a>
				</div>
			</div>
		<?php else : ?>
			<?php $room_id = $_POST['room_id']; ?>
			<?php if ( ! isset( $_POST['room_add_cart_wpnonce' . $room_id ] ) || ! wp_verify_nonce( $_POST['room_add_cart_wpnonce' . $room_id ], 'room_add_cart' ) ) : ?>
				<div class="container">
					<div class="empty-room-desc">
						<i class="fas fa-exclamation-triangle"></i>
						<h2 class="title"><?php echo esc_html__( 'There are no rooms currently booked.', 'lordcros' ); ?></h2>
						<p class="desc"><?php echo __( 'You need to book any rooms first.', 'lordcros' ) .  '<br/>' . esc_html__( 'Please search your rooms, and make a reservation.', 'lordcros' ); ?></p>
						<a href="<?php echo esc_url( $room_search_page_url ); ?>" class="empty-search-room button"><?php echo esc_html__( 'Search Rooms', 'lordcros' ); ?></a>
					</div>
				</div>
			<?php else : ?>
			
				<?php if ( empty( $room_checkout_page_url ) ) : ?>
					<div class="container">
						<div class="lordcros-msg warning-msg-wrap">
							<i class="fas fa-exclamation-triangle"></i>
							<p class="warning-description"><?php echo esc_html__( 'Please config Room Checkout Page in theme options panel.', 'lordcros' ); ?></p>
						</div>
					</div>
				<?php else : ?>

					<div class="room-book-stepline">
						<div class="container">
							<div id="select-step" class="step-item passed">
								<span class="step-icon"></span>
								<span class="step-title"><?php echo esc_html__( 'Select Room', 'lordcros' ); ?></span>
							</div>
							<div id="booking-step" class="step-item active">
								<span class="step-icon"></span>
								<span class="step-title"><?php echo esc_html__( 'Booking', 'lordcros' ); ?></span>
							</div>
							<div id="checkout-step" class="step-item">
								<span class="step-icon"></span>
								<span class="step-title"><?php echo esc_html__( 'Checkout', 'lordcros' ); ?></span>
							</div>
							<div id="confirm-step" class="step-item">
								<span class="step-icon"></span>
								<span class="step-title"><?php echo esc_html__( 'Thank you', 'lordcros' ); ?></span>
							</div>
						</div>
					</div>

					<?php
						$room_id = $_POST['room_id'];
						$adults = $_POST['adults'];
						if ( ! empty( $_POST['kids'] ) ) {
							$kids = intval( $_POST['kids'] );
						} else {
							$kids = 0;
						}

						$date_from = $_POST['date_from'];
						$date_to = $_POST['date_to'];
						$room_price = $_POST['room_price'];

						$date_from_day = date( 'd', strtotime( $date_from ) );
						$date_from_month = date( 'F', strtotime( $date_from ) );
						$date_from_year = date( 'Y', strtotime( $date_from ) );
						$date_to_day = date( 'd', strtotime( $date_to ) );
						$date_to_month = date( 'F', strtotime( $date_to ) );
						$date_to_year = date( 'Y', strtotime( $date_to ) );
						$date_diff = date_diff( date_create( $date_to ), date_create( $date_from ) );
						$nights = $date_diff->format( '%a' );

						$extra_services = get_post_meta( $room_id, 'lordcros_room_extra_service' );

						$_countries = lordcros_core_get_all_countries();

						//add booking room data to session cart
						do_action( 'lordcros_room_add_cart' );
					?>

					<div class="main-content">
						<div class="container">					
							<form id="checkout-form" method="post" action="<?php echo $room_checkout_page_url; ?>">
								<input type="hidden" name="action" value="check_room_coupon_code">
								<?php wp_nonce_field( 'room_check_out', 'room_checkout_wpnonce' ); ?>
								<div class="row">
									<aside class="col-lg-4 room-booking-details">
										<div class="mobile-sidebar-header">
											<h2 class="title"><?php echo esc_html__( 'Sidebar', 'lordcros' ); ?></h2>
											<a href="#" class="close-btn"><?php echo esc_html__( 'Close', 'lordcros' ); ?></a>
										</div>

										<div class="room-info">
											<div class="room-thumb">
												<?php echo get_the_post_thumbnail( $room_id, 'lordcros-booked-room' ); ?>
											</div>
											<h2 class="room-title"><?php echo get_the_title( $room_id ); ?></h2>
										</div>

										<div class="booking-info">
											<h2 class="info-title"><?php echo esc_html__( 'Your Reservation', 'lordcros' ); ?></h2>

											<div class="basic-fields-wrapper">
												<div class="form-input-area">
													<div id="form-check-in" class="search-calendar-show">
														<div class="check-in-section-wrap">
															<span class="section-title"><?php echo esc_html__( 'Check-In', 'lordcros' ); ?></span>
															<div class="section-content">
																<span class="day-val"><?php echo esc_html( $date_from_day ); ?></span>
																<span class="month-val"><?php echo esc_html( $date_from_month ); ?></span>
																<span class="year-val"><?php echo esc_html( $date_from_year ); ?></span>
															</div>
														</div>
													</div>
													<div id="form-check-out" class="search-calendar-show">
														<div class="check-out-section-wrap">
															<span class="section-title"><?php echo esc_html__( 'Check-Out', 'lordcros' ); ?></span>
															<div class="section-content">
																<span class="day-val"><?php echo esc_html( $date_to_day ); ?></span>
																<span class="month-val"><?php echo esc_html( $date_to_month ); ?></span>
																<span class="year-val"><?php echo esc_html( $date_to_year ); ?></span>
															</div>
														</div>
													</div>
													<div id="form-guests-num" class="search-guest-count">
														<div class="guest-section-wrap">
															<span class="section-title"><?php echo esc_html__( 'Guests', 'lordcros' ); ?></span>
															<div class="section-content">
																<span class="guest-val"><?php echo esc_html( $adults ); ?></span>
															</div>
														</div>
													</div>
													<div class="nights-count">
														<div class="nights-section-wrap">
															<span class="section-title"><?php echo esc_html__( 'Nights', 'lordcros' ); ?></span>
															<div class="section-content">
																<span class="nights-val"><?php echo esc_html( $nights ); ?></span>
															</div>
														</div>
													</div>
												</div>
												<h3 class="total_price">
													<span class="price-val"><?php echo esc_html( lordcros_price( $room_price ) ); ?>/</span><?php echo esc_html__( 'Total', 'lordcros' ); ?>
												</h3>
												<div class="form-submit-wrap">
													<button type="submit" class="room-search-submit"><?php echo esc_html__( 'Checkout', 'lordcros' ); ?></button>
												</div>
											</div>
										</div>
									</aside>

									<div class="mobile-sidebar-toggle-btn">
										<span class="btn-inner"><i class="lordcros lordcros-angle-right"></i></span>
										<span class="dot-wave"></span>
									</div>

									<div class="col-lg-8 checkout-fields-wrapper">
										<?php if ( ! empty( $extra_services ) ) : ?>
											<div class="extra-service-fields-wrap">
												<h2 class="field-title"><?php echo esc_html__( 'Add Extra Services', 'lordcros' ); ?></h2>
												<ul class="extra-service-fields">
													<?php foreach ( $extra_services as $e_service_id ) : ?>
														<?php
															$price_type1 = get_post_meta( $e_service_id, 'lordcros_room_service_price_type_1', true );
															$price_type2 = get_post_meta( $e_service_id, 'lordcros_room_service_price_type_2', true );
															$service_price = floatval( get_post_meta( $e_service_id, 'lordcros_room_service_price', true ) );
															
															$calculated_service_price = $service_price;
															$price_type_str = "";
															if ( $price_type1 == 'per_person' ) {
																$calculated_service_price *= $adults;
																$price_type_str .= esc_html__( 'Guest', 'lordcros' );
															} else {
																$price_type_str .= esc_html__( 'Room', 'lordcros' );
															}

															$price_type_str .= ' ' . esc_html__( '/', 'lordcros' ) . ' ';
															
															if ( $price_type2 == 'per_day' ) {
																$calculated_service_price *= $nights;
																$price_type_str .= esc_html__( 'Day', 'lordcros' );	
															} else {
																$price_type_str .= esc_html__( 'Trip', 'lordcros' );	
															}
														?>
														<li class="service-item">
															<input type="checkbox" class="form-control" id="extra_service_<?php echo esc_attr( $e_service_id ); ?>" name="extra_service[]" value="<?php echo esc_html( $e_service_id ); ?>">
															<label for="extra_service_<?php echo esc_attr( $e_service_id ); ?>">
																<?php echo get_the_title( $e_service_id ); ?> : <?php echo lordcros_price( $service_price ); ?> ( <?php echo esc_html( $price_type_str ); ?> ) = <span class="calculated-price"><?php echo lordcros_price( $calculated_service_price ); ?></span>
															</label>
														</li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>

										<div class="billing-fields-wrapper">
											<h2 class="field-title"><?php echo esc_html__( 'Add Your Information', 'lordcros' ); ?></h2>
											<div class="row">
												
												<div class="col-md-6 form-group first-name-field-wrap">
													<label for="first_name"><?php echo esc_html__( 'First Name', 'lordcros' ); ?> <abbr class="required" title="required">*</abbr></label>
													<input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo esc_html( $user_info['first_name'] ); ?>" required  aria-required="true">
												</div>
												
												<div class="col-md-6 form-group last-name-field-wrap">
													<label for="last_name"><?php echo esc_html__( 'Last Name', 'lordcros' ); ?> <abbr class="required" title="required">*</abbr></label>
													<input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo esc_html( $user_info['last_name'] ); ?>" required  aria-required="true">
												</div>

												<div class="col-md-6 form-group email-field-wrap">
													<label for="email"><?php echo esc_html__( 'Email', 'lordcros' ); ?> <abbr class="required" title="required">*</abbr></label>
													<input type="email" id="email" name="email" class="form-control" value="<?php echo esc_html( $user_info['email'] ); ?>" required  aria-required="true">
												</div>

												<div class="col-md-6 form-group phone-field-wrap">
													<label for="phone"><?php echo esc_html__( 'Phone', 'lordcros' ); ?> <abbr class="required" title="required">*</abbr></label>
													<input type="text" id="phone" name="phone" class="form-control" value="<?php echo esc_html( $user_info['phone'] ); ?>" required  aria-required="true">
												</div>

												<div class="col-md-6 form-group address-field-wrap">
													<label for="address"><?php echo esc_html__( 'Address', 'lordcros' ); ?></label>
													<input type="text" id="address" name="address" class="form-control" value="<?php echo esc_html( $user_info['address'] ); ?>">
												</div>

												<div class="col-md-6 form-group country-field-wrap">
													<label for="country"><?php echo esc_html__( 'Country', 'lordcros' ); ?></label>
													<select id="country" name="country" class="form-control">
														<option value="" <?php selected( $user_info['country'], '' ); ?>><?php echo esc_html__( 'Select your country', 'lordcros' ); ?></option>
														<?php foreach ( $_countries as $_country ) : ?>
															<option value="<?php echo esc_attr( $_country['code'] ); ?>"  <?php selected( $user_info['country'], $_country['code'] ); ?>><?php echo esc_html( $_country['name'] ); ?></option>
														<?php endforeach; ?>
													</select>
												</div>

												<div class="col-md-6 form-group city-field-wrap">
													<label for="city"><?php echo esc_html__( 'City', 'lordcros' ); ?></label>
													<input type="text" id="city" name="city" class="form-control" value="<?php echo esc_html( $user_info['city'] ); ?>">
												</div>

												<div class="col-md-6 form-group zip-field-wrap">
													<label for="zip"><?php echo esc_html__( 'ZIP', 'lordcros' ); ?></label>
													<input type="text" id="zip" name="zip" class="form-control" value="<?php echo esc_html( $user_info['zip'] ); ?>">
												</div>

												<div class="col-md-12 form-group note-field-wrap">
													<label for="special_requirements"><?php echo esc_html__( 'Note', 'lordcros' ); ?></label>
													<textarea id="special_requirements" name="special_requirements" class="form-control"></textarea>
												</div>

												<div class="col-md-6 form-group arrival-field-wrap">
													<label for="arrival"><?php echo esc_html__( 'Arrival', 'lordcros' ); ?></label>
													<select id="arrival" name="arrival" class="form-control">
														<option value="<?php echo esc_attr__( "I do not know", 'lordcros' ); ?>" selected><?php echo esc_html__( "I do not know", 'lordcros' ); ?></option>
														<option value="<?php echo esc_attr__( "Morning", 'lordcros' ); ?>" ><?php echo esc_html__( "Morning", 'lordcros' ); ?></option>
														<option value="<?php echo esc_attr__( "Afternoon", 'lordcros' ); ?>" ><?php echo esc_html__( "Afternoon", 'lordcros' ); ?></option>
													</select>
												</div>

												<div class="col-md-6 form-group coupon-field-wrap">
													<label for="coupon_code"><?php echo esc_html__( 'Coupon', 'lordcros' ); ?></label>
													<input type="text" id="coupon_code" name="coupon_code" class="form-control">
												</div>

												<div class="col-md-12 form-group terms-field-wrap">
													<input type="checkbox" class="form-control" id="terms" name="terms" required aria-required="true" style="display: block; ">
													<label for="terms"><?php echo esc_html__( 'Terms and conditions', 'lordcros' ); ?> <abbr class="required" title="required">*</abbr></label>
												</div>

											</div>
										</div>

										<button type="submit" class="booking-form-submit"><?php echo esc_html__( 'Checkout', 'lordcros' ); ?><i class="lordcros lordcros-arrow-right"></i></button>
									</div>
								</div>
							</form>
						</div>
					</div>
				<?php endif; ?>
			
			<?php endif; ?>

		<?php endif; ?>

		<?php 
	endwhile;
}

get_footer();