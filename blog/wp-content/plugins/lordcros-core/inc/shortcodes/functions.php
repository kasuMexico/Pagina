<?php
/**
 *	Shortcode Functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* Get image by size */
function lordcros_core_get_image( $attach_id, $img_size ) {
	if ( function_exists( 'wpb_getImageBySize' ) ) {
		$image = wpb_getImageBySize( array(
					'attach_id'		=>	$attach_id,
					'thumb_size'	=>	$img_size
				) );
		$image = $image['thumbnail'];
	} else {
		$image = wp_get_attachment_image( $attach_id, $img_size );
	}

	return $image;
}

/* Carousel Slider */
if ( ! function_exists( 'lordcros_core_carousel_layout' ) ) {
	function lordcros_core_carousel_layout( $id, $columns = 4, $speed = 1000, $nav = 1, $dots_nav = 0, $slide_by = 'no', $slider_auto = 'no', $slider_loop = 'no', $center_mode = 0, $rtl = 0, $margin = 0 ) {
		$carousel_function = function() use( $id, $columns, $speed, $nav, $dots_nav, $slide_by, $slider_auto, $slider_loop, $center_mode, $rtl, $margin ) {

			if ( is_rtl() ) {
				$rtl = 1;
			}

			ob_start();
			?>

				jQuery(document).ready(function($) {

					var owl_content = $("#<?php echo esc_js( $id ); ?> > .owl-carousel");
					var owl_center = <?php echo ( empty( $center_mode ) ) ? 'false' : 'true'; ?>;

					$(window).bind( "vc_js", function() {
						owl_content.trigger('refresh.owl.carousel');
					} );

					var options = {
						rtl:		<?php echo ( 1 == $rtl ) ? 'true' : 'false'; ?>,
						loop:		<?php echo ( 'yes' == $slider_loop ) ? 'true' : 'false'; ?>,
						center:		owl_center,
						nav:		<?php echo ( 1 == $nav ) ? 'true' : 'false'; ?>,
						dots:		<?php echo ( 1 == $dots_nav ) ? 'true' : 'false'; ?>,
						autoplay:	<?php echo ( 'yes' == $slider_auto ) ? 'true' : 'false'; ?>,
						slideBy: 	<?php echo ( 'yes' == $slide_by ) ? '\'page\'' : 1; ?>,
						autoHeight:	true,
						navText:	false,
						navSpeed:	<?php echo esc_js( $speed ); ?>,
						items:		<?php echo esc_js( $columns ); ?>,
						margin:		<?php echo esc_js( $margin ); ?>,
						<?php if ( 1 < $columns ) : ?>
						responsive: {
							1200: {
								items: <?php echo esc_js( $columns ); ?>,
								margin: <?php echo esc_js( $margin ); ?>
							},
							992: {
								<?php
								if ( $columns == 1 ) {
									?> items: 1, <?php
								} else {
									?> items: 2, <?php
								}
								?>
								margin: <?php echo esc_js( $margin ); ?>
							},
							768: {
								<?php
								if ( $columns == 1 ) {
									?> items: 1, <?php
								} else {
									?> items: 2, <?php
								}
								?>
								margin: <?php echo esc_js( $margin ); ?>
							},
							576: {
								<?php
								if ( $columns == 1 ) {
									?> items: 1, <?php
								} else {
									?> items: 2, <?php
								}
								?>
								<?php
								if ( $margin == 100 ) {
									?> margin: 50 <?php
								} else {
									?> margin: <?php echo esc_js( $margin ); ?> <?php
								}
								?>
							},
							0: {
								items: 1,
								<?php
								if ( $margin == 100 ) {
									?> margin: 50 <?php
								} else {
									?> margin: <?php echo esc_js( $margin ); ?> <?php
								}
								?>
							},
						},
						<?php endif; ?>
						onRefreshed: function() {
							$(window).resize();
						}
					};

					owl_content.owlCarousel(options);

				});

			<?php

			return ob_get_clean();
		};

		echo '<script type="text/javascript">' . $carousel_function() . '</script>';

	}
}

/* Masonry Layout Init */
if ( ! function_exists( 'lordcros_core_masonry_layout' ) ) {
	function lordcros_core_masonry_layout( $id, $content ) {
		$masonry_function = function() use( $id, $content ) {

			ob_start();
			?>			
				jQuery(document).ready(function($) {
					var masonry_content = $("#<?php echo esc_js( $id ); ?> .<?php echo esc_js( $content ); ?>");

					masonry_content.on('classChanged', function(e) {
						masonry_content.isotope({}).isotope( 'layout' );
					});
				});			
			<?php
			return ob_get_clean();
		};

		echo '<script type="text/javascript">' . $masonry_function() . '</script>';
	}
}

/* Room Block View */
if ( ! function_exists( 'lordcros_core_room_get_shortcode_block_view_html' ) ) {
	function lordcros_core_room_get_shortcode_block_view_html( $room_id, $layout_height = '560px' ) {
		$title = get_the_title( $room_id );
		$permalink = get_permalink( $room_id );
		$price_per_night = rwmb_meta( 'lordcros_room_price', '', $room_id );
		$item_bg_url = get_the_post_thumbnail_url( $room_id, 'lordcros-shortcode-room-block' );

		ob_start();
		?>

		<div class="room-content-view-wrap">		
			<div class="room-block-view">
				<div class="room-thumbs">
					<span class="room-item-bg" style="background-image: url(<?php echo esc_attr( $item_bg_url ); ?>);"></span>
						
					<div class="room-item-wrap" style="height: <?php echo esc_attr( $layout_height ); ?>;">
						<span class="price-section">
							<?php
							if ( ! empty( $total_price ) ) {
								echo esc_html( lordcros_price( $total_price ) ) . esc_html__( '/trip', 'lordcros-core' );
							} else {
								echo esc_html( lordcros_price( $price_per_night ) ) . esc_html__( '/night', 'lordcros-core' );
							}
							?>
						</span>
						<h3 class="room-title">
							<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
						</h3>
						<div class="room-book-btn-section">
							<a href="<?php echo esc_url( $permalink ); ?>" class="room-book-btn archive-rooms-btn">
								<?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
		$output = ob_get_clean();
		return $output;
	}
}

/* Get posts from ids */
if ( ! function_exists( 'lordcros_core_get_posts_from_id' ) ) {
	function lordcros_core_get_posts_from_id( $ids ) {
		if ( ! is_array( $ids ) ) {
			return false;
		}

		$args = array( 'post__in' => $ids, 'post_type' => 'post' );
		$results = get_posts( $args );
		return $results;
	}
}

/* Get posts */
if ( ! function_exists( 'lordcros_core_get_recent_posts' ) ) {
	function lordcros_core_get_recent_posts( $count = 3 ) {
		$args = array(
				'post_type'			=> 'post',
				'suppress_filters'	=> 0,
				'posts_per_page'	=> $count,
				'post_status'		=> 'publish',
				'orderby'			=> 'post_date',
				'order'				=> 'DESC',
			);

		return get_posts( $args );
	}
}

/* Google Map Init */
if ( ! function_exists( 'lordcros_core_google_map' ) ) {

	function lordcros_core_google_map( $id, $style, $marker, $map_type, $center, $zoom = 10, $type_control = true, $nav_control = true, $street_view = true, $drag = true ) {
		$map_function = function() use( $id, $style, $marker, $map_type, $center, $zoom, $type_control, $nav_control, $street_view, $drag ) {
			
			ob_start();
			?>

				jQuery(document).ready(function($) {
					var map = $("#<?php echo esc_js( $id ); ?>");

					map.gmap3({
						center: [<?php echo esc_js( $center ); ?>],
						zoom: <?php echo esc_js( $zoom ); ?>,
						mapTypeId: "lordcrosMapStyle",
						mapTypeControl: <?php echo esc_js( $type_control ); ?>,
						mapTypeControlOptions: {
							mapTypeIds: [google.maps.MapTypeId.<?php echo esc_js( $map_type ); ?>, "lordcrosMapStyle"]
						},
						navigationControl: <?php echo esc_js( $nav_control ); ?>,
						streetViewControl: <?php echo esc_js( $street_view ); ?>,
						draggable: <?php echo esc_js( $drag ); ?>,
					})
					.marker(function(map) {
						return {
							position: map.getCenter(),
							<?php echo '' . $marker; ?>
						}
					})
					.styledmaptype(
						"lordcrosMapStyle",
						<?php echo '' . $style; ?>
					  );
				});

			<?php
			return ob_get_clean();
		};

		echo '<script type="text/javascript">' . $map_function() . '</script>';
	}
}

/* Blog Social Buttons */
if ( ! function_exists( 'lordcros_core_blog_social_buttons' ) ) {
	function lordcros_core_blog_social_buttons( $link ) {
		?>
			<div class="social-share-buttons">
				<div class="lordcros-social-button facebook-icon">
					<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $link ); ?>">
						<i class="fab fa-facebook-f"></i>
					</a>
				</div>
				
				<div class="lordcros-social-button twitter-icon">
					<a href="https://twitter.com/share?url=<?php echo esc_attr( $link ); ?>">
						<i class="fab fa-twitter"></i>
					</a>
				</div>
			
				<div class="lordcros-social-button google-icon">
					<a href="https://plus.google.com/share?url=<?php echo esc_attr( $link ); ?>">
						<i class="fab fa-google-plus-g"></i>
					</a>
				</div>
			</div>
		<?php
	}
}