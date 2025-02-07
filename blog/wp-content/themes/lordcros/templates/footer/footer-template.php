<?php
/**
 * Footer Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footer_layout = lordcros_get_opt( 'footer_layout', 'footer-layout-1' );
$footer_main_col = lordcros_get_opt( 'footer_main_columns', 'footer-column-1' );
$footer_layout_config = lordcros_footer_configuration( $footer_main_col );

$logo = LORDCROS_URI . '/images/logo.png';
$logo_uploaded = lordcros_get_opt( 'footer-logo' );

if ( isset( $logo_uploaded['url'] ) && '' != $logo_uploaded['url'] ) {
	if ( is_ssl() ) {
		$logo = str_replace( 'http"//', 'https://', $logo_uploaded['url'] );
	} else {
		$logo = $logo_uploaded['url'];
	}
}

?>

<footer id="lordcros-footer" class="footer-container <?php echo esc_attr( $footer_layout ) ;?>">
	<?php if ( lordcros_get_opt( 'enable_footer_top' ) && ( 'footer-layout-1' == $footer_layout ) ) : ?>
		<div class="footer-top">
			<div class="footer-top-wrapper container">
				<div class="footer-logo">
					<a href="<?php echo esc_url( home_url('/') ); ?>" rel="home">
						<img class="footer-logo-img" src="<?php echo esc_url( $logo ); ?>" title="<?php bloginfo( 'description' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
					</a>
				</div>

				<div class="footer-top-widget-content">
					<?php
						if ( is_active_sidebar( 'lordcros-footer-top-area' ) ) {
							dynamic_sidebar( 'lordcros-footer-top-area' );
						}
					?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( count( $footer_layout_config['column'] ) ) ) : ?>
		<div class="footer-main container">
			<?php if ( 'footer-layout-1' != $footer_layout ) : ?>
				<div class="footer-logo">
					<a href="<?php echo esc_url( home_url('/') ); ?>" rel="home">
						<img class="footer-logo-img" src="<?php echo esc_url( $logo ); ?>" title="<?php bloginfo( 'description' ); ?>" alt="<?php bloginfo( 'name' ); ?>">
					</a>
				</div>
			<?php endif; ?>

			<div class="row footer-widget-content">
				<?php
					foreach ( $footer_layout_config['column'] as $key => $columns ) {
						$column_index = $key + 1;
						?>

						<div class="main-footer-column column-index-<?php echo esc_attr( $column_index ); ?> <?php echo esc_attr( $columns ); ?>">
							<?php
								if ( is_active_sidebar( 'lordcros-footer-widget-' . $column_index ) ) {
									dynamic_sidebar( 'lordcros-footer-widget-' . $column_index );
								}
							?>
						</div>

						<?php
					}
				?>
			</div>
		</div>
	<?php endif; ?>

	<div class="footer-bottom">
		<div class="footer-copyright-txt">
			<?php if ( '' != lordcros_get_opt( 'copyright_text' ) ) { ?>
				<p><?php echo lordcros_get_opt( 'copyright_text' ); ?></p>
			<?php } ?>
		</div>

		<?php if ( 'footer-layout-1' != $footer_layout ) : ?>
			<div class="footer-bottom-widget-content">
				<?php
					if ( is_active_sidebar( 'lordcros-footer-bottom-area' ) ) {
						dynamic_sidebar( 'lordcros-footer-bottom-area' );
					}
				?>
			</div>
		<?php endif; ?>
	</div>
</footer>
