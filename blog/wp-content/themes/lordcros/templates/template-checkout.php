<?php
/*
 Template Name: Checkout Page Template
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

$room_thankyou_page = lordcros_get_opt( 'room_thankyou_page' );
if ( ! empty( $room_thankyou_page ) ) {
	$room_thankyou_page_url = get_permalink( $room_thankyou_page );
} else {
	$room_thankyou_page_url = "";
}

$paypal_payment_enabled = lordcros_get_opt( 'paypal_payment' );
$stripe_payment_enabled = lordcros_get_opt( 'stripe_payment' );
$stripe_publishable_key = lordcros_get_opt( 'stripe_publishable_key' );
$bank_transfer_payment_enabled = lordcros_get_opt( 'bank_transfer_payment' );

if ( have_posts() ) {
	while ( have_posts() ) : the_post();
		$post_id = get_the_ID();
		?>

		<?php if ( ! isset( $_POST['room_checkout_wpnonce'] ) || ! wp_verify_nonce( $_POST['room_checkout_wpnonce'], 'room_check_out' ) ) : ?>
			<div class="container">
				<div class="empty-room-desc">
					<i class="fas fa-exclamation-triangle"></i>
					<h2 class="title"><?php echo esc_html__( 'There are no rooms currently booked.', 'lordcros' ); ?></h2>
					<p class="desc"><?php echo __( 'You need to book any rooms first. <br/>Please search your rooms, and make a reservation.', 'lordcros' ); ?></p>
					<a href="<?php echo esc_url( $room_search_page_url ); ?>" class="empty-search-room button"><?php echo esc_html__( 'Search Room', 'lordcros' ); ?></a>
				</div>
			</div>		
		<?php elseif ( ! class_exists( 'LordCros_Core_Room_Checkout_Info' ) || LordCros_Core_Room_Checkout_Info::get_field( 'room_id' ) == false ) : ?>
			<div class="container">
				<div class="empty-room-desc">
					<i class="fas fa-exclamation-triangle"></i>
					<h2 class="title"><?php echo esc_html__( 'There are no rooms currently booked.', 'lordcros' ); ?></h2>
					<p class="desc"><?php echo __( 'You need to book any rooms first. <br/>Please search your rooms, and make a reservation.', 'lordcros' ); ?></p>
					<a href="<?php echo esc_url( $room_search_page_url ); ?>" class="empty-search-room button"><?php echo esc_html__( 'Search Room', 'lordcros' ); ?></a>
				</div>
			</div>		
		<?php elseif ( empty( $room_thankyou_page_url ) ) : ?>
			<div class="container">
				<div class="lordcros-msg warning-msg-wrap">
					<i class="fas fa-exclamation-triangle"></i>
					<p class="warning-description"><?php echo esc_html__( 'Please config Room Thankyou Page in theme options panel.', 'lordcros' ); ?></p>
				</div>
			</div>
		<?php else : ?>

			<?php
				//add booking information to session cart
				do_action( 'lordcros_room_checkout' );

				$booking_data = LordCros_Core_Room_Checkout_Info::get();

				$room_id = $booking_data['room_id'];
				$date_from = $booking_data['date_from'];
				$date_to = $booking_data['date_to'];
				$adults = $booking_data['adults'];
				$kids = $booking_data['kids'];
				$extra_services = $booking_data['extra_service'];
				$coupon_code = $booking_data['coupon_code'];

				if ( empty( $coupon_code ) ) {
					$coupon_code = esc_html__( 'Not Set', 'lordcros' );
				}

				$date_from_day = date( 'd', strtotime( $date_from ) );
				$date_from_month = date( 'F', strtotime( $date_from ) );
				$date_from_year = date( 'Y', strtotime( $date_from ) );
				$date_to_day = date( 'd', strtotime( $date_to ) );
				$date_to_month = date( 'F', strtotime( $date_to ) );
				$date_to_year = date( 'Y', strtotime( $date_to ) );
				$date_diff = date_diff( date_create( $date_to ), date_create( $date_from ) );
				$nights = $date_diff->format( '%a' );

			?>

			<div class="room-book-stepline">
				<div class="container">
					<div id="select-step" class="step-item passed">
						<span class="step-icon"></span>
						<span class="step-title"><?php echo esc_html__( 'Select Room', 'lordcros' ); ?></span>
					</div>
					<div id="booking-step" class="step-item passed">
						<span class="step-icon"></span>
						<span class="step-title"><?php echo esc_html__( 'Booking', 'lordcros' ); ?></span>
					</div>
					<div id="checkout-step" class="step-item active">
						<span class="step-icon"></span>
						<span class="step-title"><?php echo esc_html__( 'Checkout', 'lordcros' ); ?></span>
					</div>
					<div id="confirm-step" class="step-item">
						<span class="step-icon"></span>
						<span class="step-title"><?php echo esc_html__( 'Thank you', 'lordcros' ); ?></span>
					</div>
				</div>
			</div>

			<div class="main-content checkout-page-container">
				<div class="container">
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
										<span class="price-val"><?php echo esc_html( lordcros_price( $booking_data['total_price'] ) ); ?>/</span><?php echo esc_html__( 'Total', 'lordcros' ); ?>
									</h3>								
								</div>
							</div>
						</aside>

						<div class="mobile-sidebar-toggle-btn">
							<span class="btn-inner"><i class="lordcros lordcros-angle-right"></i></span>
							<span class="dot-wave"></span>
						</div>

						<div class="col-lg-8 checkout-fields-wrapper">
							<div class="checkout-info-wrap">
								<h2 class="field-title"><?php echo esc_html__( 'Your Informations', 'lordcros' ); ?></h2>

								<div class="checkout-info">
									<div class="row">
										<div class="col-lg-4 info-wrap">
											<h3 class="title"><?php echo esc_html__( 'First Name', 'lordcros' ); ?>:</h3>
											<span class="info"><?php echo esc_html( $booking_data['first_name'] ); ?></span>
										</div>
										<div class="col-lg-4 info-wrap">
											<h3 class="title"><?php echo esc_html__( 'Last Name', 'lordcros' ); ?>:</h3>
											<span class="info"><?php echo esc_html( $booking_data['last_name'] ); ?></span>
										</div>
										<div class="col-lg-4 info-wrap">
											<h3 class="title"><?php echo esc_html__( 'Email', 'lordcros' ); ?>:</h3>
											<span class="info"><?php echo esc_html( $booking_data['email'] ); ?></span>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-4 info-wrap">
											<h3 class="title"><?php echo esc_html__( 'Phone', 'lordcros' ); ?>:</h3>
											<span class="info"><?php echo esc_html( $booking_data['phone'] ); ?></span>
										</div>
										<div class="col-lg-4 info-wrap">
											<h3 class="title"><?php echo esc_html__( 'Address', 'lordcros' ); ?>:</h3>
											<span class="info"><?php echo esc_html( $booking_data['address'] ); ?></span>
										</div>
										<div class="col-lg-4 info-wrap">
											<h3 class="title"><?php echo esc_html__( 'City', 'lordcros' ); ?>:</h3>
											<span class="info"><?php echo esc_html( $booking_data['city'] ); ?></span>
										</div>
									</div>

									<div class="row">
										<div class="col-lg-4 info-wrap">
											<h3 class="title"><?php echo esc_html__( 'Country', 'lordcros' ); ?>:</h3>
											<span class="info"><?php echo esc_html( $booking_data['country'] ); ?></span>
										</div>
										<div class="col-lg-4 info-wrap">
											<h3 class="title"><?php echo esc_html__( 'ZIP', 'lordcros' ); ?>:</h3>
											<span class="info"><?php echo esc_html( $booking_data['zip'] ); ?></span>
										</div>
										<div class="col-lg-4 info-wrap">
											<h3 class="title"><?php echo esc_html__( 'Arrival', 'lordcros' ); ?>:</h3>
											<span class="info"><?php echo esc_html( $booking_data['arrival'] ); ?></span>
										</div>
									</div>

								</div>

								<div class="special-requirement-info">
									<h3 class="title"><?php echo esc_html__( 'Spacial Requirement', 'lordcros' ); ?>:</h3>
									<p class="info"><?php echo esc_html( $booking_data['special_requirements'] ); ?></p>
								</div>

								<div class="extra-service-info">
									<h3 class="title"><?php echo esc_html__( 'Extra Services', 'lordcros' ); ?>:</h3>

									<?php if ( ! empty( $extra_services ) ) : ?>
										<ul class="services-list">
											<?php foreach ( $extra_services as $e_service_id ) : ?>
												<li class="service-name"><?php echo get_the_title( $e_service_id ); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
				
								</div>

								<div class="coupon-info">
									<h3 class="title"><?php echo esc_html__( 'Coupon', 'lordcros' ); ?>:</h3>
									<p class="info"><?php echo '' . $coupon_code; ?></p>
								</div>

							</div>

							<div class="payment-form-wrapper">
								<h2 class="field-title"><?php echo esc_html__( 'Payment Options', 'lordcros' ); ?></h2>

								<div class="payment-options-tab tab-wrapper">
									<ul class="nav nav-tabs">
										<?php if ( ! empty( $paypal_payment_enabled ) ) : ?>
											<li class="tab-pane-title">
												<a data-toggle="tab" href="#paypal-form-container"><?php echo esc_html__( 'Paypal', 'lordcros' ); ?></a>
											</li>
										<?php endif; ?>

										<?php if ( ! empty( $stripe_payment_enabled ) ) : ?>
											<li class="tab-pane-title">
												<a data-toggle="tab" href="#credit-card-form-container"><?php echo esc_html__( 'Credit Card', 'lordcros' ); ?></a>
											</li>
										<?php endif; ?>

										<?php if ( ! empty( $bank_transfer_payment_enabled ) ) : ?>
											<li class="tab-pane-title">
												<a data-toggle="tab" href="#bank-transfer-form-container"><?php echo esc_html__( 'Bank Transfer', 'lordcros' ); ?></a>
											</li>
										<?php endif; ?>

										<li class="tab-pane-title">
											<a data-toggle="tab" href="#booking-request"><?php echo esc_html__( 'Only Request Booking', 'lordcros' ); ?></a>
										</li>
										
									</ul>

									<div class="tab-content">
										<?php if ( ! empty( $paypal_payment_enabled ) ) : ?>
											<div id="paypal-form-container" class="tab-pane fade">
												<form method="post" action="<?php echo esc_url( $room_thankyou_page_url ); ?>">
													<input type="hidden" name="payment" value="paypal">
													<button type="submit" id="paypal-submit"><?php echo esc_html__( 'Pay Now', 'lordcros' ); ?></button>
												</form>
											</div>
										<?php endif; ?>

										<?php if ( ! empty( $stripe_payment_enabled ) ) : ?>
											<div id="credit-card-form-container" class="tab-pane fade">
												<span class="payment-errors"></span>
												<form id="stripe-form" method="post" action="<?php echo esc_url( $room_thankyou_page_url ); ?>">
													<input type="hidden" name="payment" value="stripe">
													<noscript>
														<div class="bs-callout bs-callout-danger">
															<h4><?php echo esc_html__( 'JavaScript is not enabled!', 'lordcros' ); ?></h4>
															<p><?php echo esc_html__( 'This payment form requires your browser to have JavaScript enabled. Please activate JavaScript and reload this page.', 'lordcros' ); ?></p>
														</div>
													</noscript>
													
													<div class="row">
														<!-- Card Holder Name -->
														<div class="col-lg-6 form-group">
															<label for="card_num"><?php echo esc_html__( 'Card Number', 'lordcros' ); ?></label>
															<input type="text" name="card_num" id="card_num" autocomplete="off" class="card-number form-control" />
														</div>

														<!-- Card Number -->
														<div class="col-lg-6 form-group">
															<label for="cvc"><?php echo esc_html__( 'CVC', 'lordcros' ); ?></label>
															<input type="text" name="cvc" id="cvc" autocomplete="off" class="card-cvc form-control" />
														</div>

														<!-- Expiry-->
														<div class="col-lg-6 form-group">
															<label for="exp-day"><?php echo esc_html__( 'Expiration (MM/YYYY)', 'lordcros' ); ?></label>
															<div class="expiration-date-wrap">
																<input type="text" name="exp_month" class="card-expiry-month form-control"/>
																<input type="text" name="exp_year" class="card-expiry-year form-control"/>
															</div>
														</div>
													</div>

													<button type="submit" id="stripe-pay-btn"><?php echo esc_html__( 'Submit Payment', 'lordcros' ); ?></button>
												</form>
											</div>
										<?php endif; ?>

										<?php if ( ! empty( $bank_transfer_payment_enabled ) ) : ?>
											<div id="bank-transfer-form-container" class="tab-pane fade">
												<form id="bank-transfer-form" method="post" action="<?php echo esc_url( $room_thankyou_page_url ); ?>">
													<?php if ( ! empty( lordcros_get_opt( 'bank_name', '' ) ) ) : ?>
														<div class="bank-info-wrap">
															<span class="title"><?php echo esc_html__( 'Bank Name', 'lordcros' ); ?>:</span>
															<span class="info"><?php echo lordcros_get_opt( 'bank_name', '' ); ?></span>
														</div>
													<?php endif; ?>

													<?php if ( ! empty( lordcros_get_opt( 'account_name', '' ) ) ) : ?>
														<div class="bank-info-wrap">
															<span class="title"><?php echo esc_html__( 'Hoder Name', 'lordcros' ); ?>:</span>
															<span class="info"><?php echo lordcros_get_opt( 'account_name', '' ); ?></span>
														</div>
													<?php endif; ?>

													<?php if ( ! empty( lordcros_get_opt( 'swift', '' ) ) ) : ?>
														<div class="bank-info-wrap">
															<span class="title"><?php echo esc_html__( 'Swift/BIC', 'lordcros' ); ?>:</span>
															<span class="info"><?php echo lordcros_get_opt( 'swift', '' ); ?></span>
														</div>
													<?php endif; ?>

													<?php if ( ! empty( lordcros_get_opt( 'sort_code', '' ) ) ) : ?>
														<div class="bank-info-wrap">
															<span class="title"><?php echo esc_html__( 'Routing Number', 'lordcros' ); ?>:</span>
															<span class="info"><?php echo lordcros_get_opt( 'sort_code', '' ); ?></span>
														</div>
													<?php endif; ?>

													<?php if ( ! empty( lordcros_get_opt( 'bank_address', '' ) ) ) : ?>
														<div class="bank-info-wrap">
															<span class="title"><?php echo esc_html__( 'Bank Address', 'lordcros' ); ?>:</span>
															<span class="info"><?php echo lordcros_get_opt( 'bank_address', '' ); ?></span>
														</div>
													<?php endif; ?>

													<input type="hidden" name="payment" value="bank_transfer">
													<button type="submit" ><?php echo esc_html__( 'Book Now', 'lordcros' ); ?></button>
												</form>
											</div>
										<?php endif; ?>

										<div id="booking-request" class="tab-pane fade">
											<form id="request-form" method="post" action="<?php echo esc_url( $room_thankyou_page_url ); ?>">
												<input type="hidden" name="payment" value="inquiry">
												<button type="submit" ><?php echo esc_html__( 'Submit Request', 'lordcros' ); ?></button>
											</form>
										</div>
									
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		<?php endif; ?>

		<?php 
	endwhile;
}

get_footer();