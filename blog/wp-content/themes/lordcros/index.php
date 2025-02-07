<?php
/**
 * Main Blog Post Page Template
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Get Page Classes
$page_classes = 'container';
$content_class = lordcros_get_content_class();

lordcros_page_heading();

?>

	<div class="main-content <?php echo esc_attr( $page_classes ) ?>">

		<div class="row content-layout-wrapper">

			<div class="site-content <?php echo esc_attr( $content_class ) ?>" role="main"> 

				<?php 

				/**
				 * Hook: lordcros_main_loop.
				 *
				 * @hooked lordcros_main_loop - 10
				 */
				do_action( 'lordcros_main_loop' );

				?>

			</div>

			<?php 

			get_sidebar();

			?>

		</div>

	</div>

<?php

get_footer();