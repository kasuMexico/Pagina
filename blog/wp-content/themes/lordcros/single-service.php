<?php
/*
 * Single Service Page Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

lordcros_page_heading();

// Get content container width
$content_class = lordcros_get_content_class();
$wrapper_class = ' container';
?>

<div class="single-post-content">
	<div class="main-content <?php echo esc_attr( $wrapper_class ) ?>">
		<div id="post-content" class="row content-layout-wrapper">
			<div class="post-inner-content <?php echo esc_attr( $content_class ); ?>">
				<?php
					if ( have_posts() ) :

						while ( have_posts() ) : the_post(); $service_id = get_the_ID();

							// add to user recent activity
							lordcros_update_user_recent_activity( $service_id );

							?>

								<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>				
									<div class="post-header">
										<div class="post-image">
											<?php
												if ( has_post_thumbnail() ) {
													?>
													<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'lordcros-room-large-gallery' ); ?></a>
													<?php		
												} else {
													?>
													<a href="<?php the_permalink(); ?>" class="placeholder-img"></a>
													<?php
												}
											?>
										</div>
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
									</div>

									<div class="post-main-content">
										<div class="post-title">
											<h2><?php the_title(); ?></h2>
										</div>

										<div class="post-content">
											<?php 
												the_content(); 

												wp_link_pages( array(
													'before'		=> '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'lordcros' ) . '</span>',
													'after'			=> '</div>',
													'link_before'	=> '<span>',
													'link_after'	=> '</span>',
													'pagelink'		=> '<span class="wp-link-page-txt">' . esc_html__( 'Page', 'lordcros' ) . ' </span>%',
													'separator'		=> '<span class="wp-link-page-txt">, </span>',
												) );
											?>
										</div>

										<?php
											echo lordcros_post_share_buttons();
										?>
									</div>

									<?php
										echo lordcros_post_pagination();
									?>
								</article>
							<?php
						endwhile;

					endif;
				?>
			</div>
			
			<?php get_sidebar(); ?>

		</div>
	</div>
</div>

<?php
get_footer();