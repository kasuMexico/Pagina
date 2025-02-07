<?php
/**
 * Main Theme Footer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$header_layout = lordcros_get_opt( 'header_layout' );
?>

	</div>
	<!-- End Page wrapper -->

	<!-- Footer -->
	<?php lordcros_get_template_part( 'footer/footer', 'template' ); ?>
	<!-- End Footer -->

	<?php
		// Hamburger Navigation
		$hamburger_header_layouts = array( 'header-layout-3', 'header-layout-4', 'header-layout-10' );
		if ( in_array( $header_layout, $hamburger_header_layouts ) ) {
			?>
				<div class="lordcros-hamburger-menu-wrap">
					<div class="burger-menu-inner">
						<?php echo lordcros_header_block_hamburg_nav_menu(); ?>

						<div class="html-block-content">
							<?php
								$burger_html_block_content = lordcros_get_opt( 'header_html_block_content' );
								if ( $burger_html_block_content ) {
									echo do_shortcode( '[lc_html_block block_id="' . $burger_html_block_content . '"]' );
								}
							?>
						</div>
					</div>
				</div>
			<?php
		}

		// Mobile Navigation
		lordcros_mobile_navigation();

		/**
		 * lordcros_after_footer_content hook
		 *
		 * @hooked lordcros_full_screen_searchbox - 10
		 */
		do_action( 'lordcros_after_footer_content' );
	?>

	<!-- Scroll Top Button -->
	<a href="#" class="back-to-top" title="<?php esc_attr_e('Back To Top', 'lordcros'); ?>">
		<i class="lordcros lordcros-arrow-top"></i>
	</a>

	<!-- WordPress wp_footer() -->
	<?php wp_footer(); ?>

</body>
</html>