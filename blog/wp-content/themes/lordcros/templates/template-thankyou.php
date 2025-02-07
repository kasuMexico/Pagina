<?php
/*
 Template Name: Thankyou Page Template
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

if ( have_posts() ) {
	while ( have_posts() ) : the_post();
		$post_id = get_the_ID();
		?>

		<?php if ( ! class_exists( 'LordCros_Core_Room_Checkout_Info' ) || LordCros_Core_Room_Checkout_Info::get_field( 'room_id' ) == false ) : ?>
			<div class="container">
				<div class="empty-room-desc">
					<i class="fas fa-exclamation-triangle"></i>
					<h2 class="title"><?php echo esc_html__( 'There are no rooms currently booked.', 'lordcros' ); ?></h2>
					<p class="desc"><?php echo __( 'You need to book any rooms first. <br/>Please search your rooms, and make a reservation.', 'lordcros' ); ?></p>
					<a href="<?php echo esc_url( $room_search_page_url ); ?>" class="empty-search-room button"><?php echo esc_html__( 'Search Room', 'lordcros' ); ?></a>
				</div>
			</div>
		<?php else : ?>

			<?php
				if ( ! empty( $_POST['payment'] ) ) {
					LordCros_Core_Room_Checkout_Info::set( array( 'payment' => $_POST['payment'] ) );
				}
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

				$payment_status = false;

				if ( ! empty( $booking_data['payment'] ) && $booking_data['payment'] == 'paypal' ) {
					$payment_data = array();
					$payment_data['item_name'] = get_the_title( $room_id );
					$payment_data['item_number'] = $room_id;
					$payment_data['item_desc'] = esc_html__( 'Date From', 'lordcros' ) . ': ' . $booking_data['date_from'] . '  ' . esc_html__( 'Date To', 'lordcros' ) . ': ' . $booking_data['date_to'];
					$payment_data['item_qty'] = 1;
					$payment_data['item_price'] = $booking_data['discounted_price'];
					$payment_data['item_total_price'] = $booking_data['discounted_price'];
					$payment_data['grand_total'] = $booking_data['discounted_price'];
					$payment_data['currency'] = lordcros_get_opt( 'currency_code', 'USD' );
					$payment_data['return_url'] = lordcros_get_current_page_url() . '?payment=success';
					$payment_data['cancel_url'] = lordcros_get_current_page_url() . '?payment=failed';

					$api_info = array(
									'paypal_api_username'	=> lordcros_get_opt( 'paypal_api_username' ),
									'paypal_api_password'	=> lordcros_get_opt( 'paypal_api_password' ),
									'paypal_api_signature'	=> lordcros_get_opt( 'paypal_api_signature' ),
									'paypal_sandbox'		=> lordcros_get_opt( 'paypal_sandbox' ),
								);

					$payment_result = lordcros_core_process_payment( $payment_data, $api_info );

					if ( ! empty( $payment_result['success'] ) ) {
						LordCros_Core_Room_Checkout_Info::set( array( 'transaction_id' => $payment_result['transaction_id'] ) );
						do_action( 'lordcros_room_add_booking' );
						$payment_status = true;
					} else {
						//paypal payment error
					}
				} elseif ( ! empty( $booking_data['payment'] ) && $booking_data['payment'] == 'stripe' ) {
					
					Stripe::setApiKey( lordcros_get_opt( 'stripe_secret_key', '' ) );
					
					try {
						
						if ( ! isset( $_POST['stripeToken'] ) ) {
							throw new Exception( esc_html__( 'The Stripe Token was not generated correctly', 'lordcros' ) );
						}
						
						$charge = Stripe_Charge::create( array(
													'amount'		=> $booking_data['discounted_price'] * 100,
													'currency'		=> lordcros_get_opt( 'currency_code', 'USD' ),
													'card'			=> $_POST['stripeToken'],
													'description'	=> $booking_data['email'],
												) );
						$chargeArray = $charge->__toArray();
						
						LordCros_Core_Room_Checkout_Info::set( array( 'transaction_id' => $chargeArray['id'] ) );
						do_action( 'lordcros_room_add_booking' );
						$payment_status = true;

					} catch (Exception $e) {
						$error = '<div class="alert alert-danger">
						  <strong>Error!</strong> ' . $e->getMessage().'
						  </div>';
						 echo "" . $error;
						 exit;
					}
				} else {
					// just inquiry
					do_action( 'lordcros_room_add_booking' );
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
					<div id="checkout-step" class="step-item passed">
						<span class="step-icon"></span>
						<span class="step-title"><?php echo esc_html__( 'Checkout', 'lordcros' ); ?></span>
					</div>
					<div id="confirm-step" class="step-item passed">
						<span class="step-icon"></span>
						<span class="step-title"><?php echo esc_html__( 'Thank you', 'lordcros' ); ?></span>
					</div>
				</div>
			</div>

			<div class="main-content  thankyou-page-container">			
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
								<h2 class="field-title"><?php echo esc_html__( 'Your Order Details', 'lordcros' ); ?></h2>

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

								<div class="payment-info">
									<h3 class="title"><?php echo esc_html__( 'Payment Options', 'lordcros' ); ?>:</h3>
									<p class="info">
										<?php
										if ( ! empty( $booking_data['payment'] ) && $booking_data['payment'] == 'paypal' ) {
											echo esc_html__( 'Paypal', 'lordcros' );
										} elseif ( ! empty( $booking_data['payment'] ) && $booking_data['payment'] == 'stripe' ) {
											echo esc_html__( 'Stripe', 'lordcros' );
										} elseif ( ! empty( $booking_data['payment'] ) && $booking_data['payment'] == 'bank_transfer' ) {
											echo esc_html__( 'Bank Transfer', 'lordcros' );
										} else {
											echo esc_html__( 'None', 'lordcros');
										} ?>
									</p>
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