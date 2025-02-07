<?php
/**
 * The template for displaying all pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Get Page Classes
$page_classes = array();
$page_id = lordcros_get_page_id();
$page_width = get_post_meta( $page_id, 'lordcros_page_width', true );

if ( $page_width == 'full' ) {
	$page_classes[] = 'container-fluid';
} else {
	$page_classes[] = 'container';
}

$page_classes = implode( ' ', $page_classes );
$content_class = lordcros_get_content_class();

lordcros_page_heading();

?>

<div class="main-content <?php echo esc_attr( $page_classes ); ?>">

	<div class="row page-content-inner">
		<div class="entry-content <?php echo esc_attr( $content_class ); ?>">
			<!-- Featured Image -->
			<?php
				if ( has_post_thumbnail( get_queried_object_id() ) ) :
					echo '<div class="page-featured-image">';
					echo get_the_post_thumbnail( get_queried_object_id(), 'full' );
					echo '</div>';
				endif;
			?>
			<!-- End Featured Image -->

			<!-- Loop Setting -->
			<?php 
				while ( have_posts() ) : the_post();

					the_content();

					wp_link_pages( array(
						'before'		=> '<div class="page-links"><span class="page-links-title">' . esc_html__( 'Pages:', 'lordcros' ) . '</span>',
						'after'			=> '</div>',
						'link_before'	=> '<span>',
						'link_after'	=> '</span>',
						'pagelink'		=> '<span class="wp-link-page-txt">' . esc_html__( 'Page', 'lordcros' ) . ' </span>%',
						'separator'		=> '<span class="wp-link-page-txt">, </span>',
					) );

				endwhile;

				lordcros_post_comments_field();
			?>
			<!-- Resetting the page Loop -->
		</div>

		<?php get_sidebar(); ?>
	</div>

</div>

<?php get_footer(); ?>