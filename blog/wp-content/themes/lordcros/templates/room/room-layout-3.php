<?php
/**
 * Room slider bottom template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// variables for booking form
$default_args = array(
	'date_from'			=> date( 'm/d/Y' ),
	'date_to'			=> date( 'm/d/Y', strtotime( ' +1 day' ) ),
	'adults'			=> 1,
	'kids'				=> 0,
);
extract( wp_parse_args( $_REQUEST, $default_args ) );

// variables for room information
$room_id = get_the_ID();
$room_adults = get_post_meta( $room_id, 'lordcros_room_adults', true );
$room_kids = get_post_meta( $room_id, 'lordcros_room_children', true );
$size = get_post_meta( $room_id, 'lordcros_room_size', true );
$price_per_night = get_post_meta( $room_id, 'lordcros_room_price', true );
$min_stay = get_post_meta( $room_id, 'lordcros_room_min_stay', true );
if ( empty( $min_stay ) || ! is_numeric( $min_stay ) ) {
	$min_stay = 1;
}

$featured_img_id = get_post_thumbnail_id( $room_id );
$room_images = get_post_meta( $room_id, 'lordcros_room_image' );
if ( empty( $room_images ) ) {
	$room_images = array();
}
if ( ! empty( $featured_img_id ) ) {
	array_unshift( $room_images, $featured_img_id );
}

$def_services = get_post_meta( $room_id, 'lordcros_room_def_service' );
$hotel_services = get_post_meta( $room_id, 'lordcros_room_hotel_services' );
$similar_rooms = get_post_meta( $room_id, 'lordcros_room_similar_rooms' );

lordcros_page_heading(); // Template Page Banner Heading

?>
<div id="room-<?php echo esc_attr( $room_id ); ?>" class="single-room-page-wrapper room-slider-bottom-layout">
	<div class="single-room-main-section container">
		<div class="row">
			<div class="single-room-page-content-area col-lg-8">
				<div class="room-summery">
					<div class="room-content-section">
						<h3 class="section-title"><?php echo esc_html__( 'Description', 'lordcros' ); ?></h3>

						<div class="content-wrap">
							<?php the_content(); ?>
						</div>
					</div>

					<div class="room-service-section">
						<?php if ( ! empty( $def_services ) ) : ?>
							<div class="room-service-info">
								<h3 class="section-title"><?php echo esc_html__( 'Services', 'lordcros' ); ?></h3>
								
								<ul class="room-def-services">
									<?php foreach ( $def_services as $def_service ) : ?>
										<li class="room-def-service"><?php echo get_the_title( $def_service ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $hotel_services ) ) : ?>
							<div class="hotel-services">
								<h3 class="section-title"><?php echo esc_html__( 'Around The Hotel', 'lordcros' ); ?></h3>
								<?php echo do_shortcode( '[lc_services style="style6" type="selected" columns="3" margin="30" post_ids="' . implode( ', ', $hotel_services ) . '" ]' ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<aside class="single-room-sidebar-area sidebar-container col-lg-4" role="complementary">
				<div class="mobile-sidebar-header">
					<h2 class="title"><?php echo esc_html__( 'Sidebar', 'lordcros' ); ?></h2>
					<a href="#" class="close-btn"><?php echo esc_html__( 'Close', 'lordcros' ); ?></a>
				</div>
				
				<div class="check-form-wrapper">
					<?php echo lordcros_core_room_check_availability_form( $room_id, $date_from, $date_to, $adults, $kids ); ?>
				</div>

				<div class="room-info-wrap">
					<ul class="room-infos">
						<li class="room-info room-person">
							<i class="lordcros lordcros-users"></i>
							<span class="info-text">
								<span class="info-title">
									<?php echo esc_html__( 'Available Persons', 'lordcros' ); ?>
								</span>
								<span class="info-val">
									<?php echo esc_html( $room_adults ) . ' ' . esc_html__( _n( 'Adult', 'Adults', $room_adults, 'lordcros' ) ); ?>
								</span>
							</span>
						</li>
						<li class="room-info room-size">
							<i class="lordcros lordcros-plans"></i>
							<span class="info-text">
								<span class="info-title">
									<?php echo esc_html__( 'Room Size', 'lordcros' ); ?>
								</span>
								<span class="info-val">
									<?php echo esc_html( $size ) . ' ' . lordcros_get_opt( 'size_unit', 'Ft' ); ?><sup>2</sup>
								</span>
							</span>
						</li>
						<li class="room-info room-price">
							<i class="lordcros lordcros-price-tag"></i>
							<span class="info-text">
								<span class="info-title">
									<?php echo esc_html__( 'Price Per Night', 'lordcros' ); ?>
								</span>
								<span class="info-val">
									<?php echo esc_html( lordcros_price( $price_per_night ) ); ?>
								</span>
							</span>
						</li>
						<li class="room-info room-min-stay">
							<i class="lordcros lordcros-bed"></i>
							<span class="info-text">
								<span class="info-title">
									<?php echo esc_html__( 'Minimum Stay', 'lordcros' ); ?>
								</span>
								<span class="info-val">
									<?php echo esc_html( $min_stay ) . ' ' . esc_html__( _n( 'day', 'days', $min_stay, 'lordcros' ) ); ?>		
								</span>
							</span>
						</li>
					</ul>
				</div>
			</aside>

			<div class="mobile-sidebar-toggle-btn">
				<span class="btn-inner"><i class="lordcros lordcros-angle-right"></i></span>
				<span class="dot-wave"></span>
			</div>
		</div>
	</div>

	<div class="single-room-bottom-section">
		<?php if ( ! empty( $room_images ) ) : ?>
			<div class="room-gallery-wrapper">
				<h2 class="section-title"><?php echo esc_html__( 'Gallery', 'lordcros' ); ?></h2>
				<div class="room-image-gallery">
					<?php
					$image_ids = array();
					foreach ( $room_images as $image ) {
						$image_ids[] = $image;
					}
					echo do_shortcode( '[lc_image_carousel carousel_images="' . implode( ', ', $image_ids ) . '" carousel_column="2" center_mode="1" slider_loop="yes" slider_auto="no" nav_position="center_pos" carousel_size="960x600"]' );
					?>
				</div>
			</div>
		<?php endif; ?>

		<?php if( ! empty( $similar_rooms ) ) : ?>
			<div class="similar-rooms-wrapper container">
				<h2 class="section-title"><?php echo esc_html__( 'Similar Rooms', 'lordcros' ); ?></h2>
				<div class="similar-rooms-inner">
					<?php echo do_shortcode( '[lc_rooms style="style1" type="selected" columns="3" margin="30" post_ids="' . implode( ', ', $similar_rooms ) . '" ]' ); ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>