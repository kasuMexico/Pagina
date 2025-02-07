<?php
/**
 * Service Archive Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Get Page Classes
$page_classes = 'container';

lordcros_page_heading();

?>
	<div class="main-content <?php echo esc_attr( $page_classes ); ?>">
		<div class="lordcros-shortcode-services style1">
			<div class="services-wrapper lordcros-shortcode-services-wrapper row" data-col="3">
				<?php 
					if ( have_posts() ) : 

						while ( have_posts() ): the_post();
							$service_id = get_the_ID();
							$title = get_the_title( $service_id );
							$permalink = get_permalink( $service_id );
							$brief = apply_filters( 'the_content', get_post_field( 'post_content', $service_id ) );
							$brief = wp_trim_words( $brief, 17, '...' );

							?>
							<div class="service-item">
								<div class="service-item-wrap">
									<div class="service-thumbs">
										<a href="<?php echo esc_url( $permalink ); ?>" class="service-featured-img">
											<?php echo get_the_post_thumbnail( $service_id, 'lordcros-service-list' ); ?>

											<span class="featured-icon">
												<?php
												$service_icon = get_post_meta( $service_id, 'lordcros_service_icon_class', true );
												if ( empty( $service_icon ) ) {
													$service_icon = '';
												}
												$icon_images = get_post_meta( $service_id, 'lordcros_service_icon_image', true );

												if ( ! empty( $icon_images ) ) {
													if ( is_array( $icon_images ) ) {
														$image = reset( $icon_images );	
													} else {
														$image = $icon_images;
													}													
													?>
													<img src="<?php echo wp_get_attachment_url( $image ); ?>">
													<?php
												} else {
												?>
													<i class="<?php echo esc_attr( $service_icon ); ?>"></i>
												<?php
												}
												?>
											</span>
										</a>					
									</div>
									<div class="service-infobox">
										<h3 class="service-title">
											<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
										</h3>
									
										<div class="brief">
											<?php echo esc_html( $brief ); ?>
										</div>

										<a href="<?php echo esc_url( $permalink ); ?>" class="service-read-more"><?php echo esc_html__( 'Read More', 'lordcros' ); ?></a>
									</div>
								</div>
							</div>
							<?php
						endwhile;

					else :
						?>

						<div class="lordcros-msg warning-msg-wrap">
							<i class="fas fa-exclamation-triangle"></i>
							<p class="warning-description"><?php echo esc_html__( 'No Services found', 'lordcros' ); ?></p>
						</div>

						<?php
					endif; 
				?>
			</div>
		</div>

		<div class="lordcros-pagination">
			<?php echo paginate_links( array(
										'type'		=>	'list',
										'prev_text'	=>	esc_html__( 'Prev', 'lordcros' ),
										'next_text'	=>	esc_html__( 'Next', 'lordcros' ),
									) ); ?>
		</div>
	</div>
<?php
get_footer();