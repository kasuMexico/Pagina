<?php
/*
 * Single Post Page Template
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

						while ( have_posts() ) : the_post();

							// add to user recent activity
        					lordcros_update_user_recent_activity( get_the_ID() );

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
										<div class="post-date">
											<span class="year"><?php echo get_the_time( 'Y', $post->ID ); ?></span>
											<span class="month"><?php echo get_the_time( 'M', $post->ID ); ?></span>
										</div>
									</div>

									<div class="post-main-content">
										<div class="post-title">
											<h2><?php the_title(); ?></h2>
										</div>

										<div class="post-meta-description">
											<div class="author-info">
												<i class="lordcros lordcros-profile"></i>
												<a href="<?php echo get_author_posts_url( $post->post_author ); ?>">
													<span class="post-author"><?php echo get_the_author_meta( 'display_name', $post->post_author ); ?></span>
												</a>
											</div>
											<div class="comment-count">
												<i class="lordcros lordcros-consulting-message"></i>
												<a href="#comments">
													<?php comments_number( esc_html__( 'Sin comentarios', 'lordcros' ), esc_html__( '1 Comment', 'lordcros' ), '% ' . esc_html__( 'Comments', 'lordcros' ) ); ?>
												</a>
											</div>
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
											echo lordcros_post_tags();
											echo lordcros_post_share_buttons();
										?>
									</div>

									<?php
										echo lordcros_post_pagination();
										echo lordcros_post_comments_field();
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
