<?php
/**
 * Header Layout 3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get Header Values
$sticky_header_setting = lordcros_get_opt( 'sticky_header_setting' );
$container_class = 'container-fluid';

?>

<!-- Header -->
<header <?php lordcros_header_classes( 'header-layout-3' ); ?>>
	<div class="header-wrapper <?php echo esc_attr( $container_class ); ?>">
		<div class="main-header-wrap">
			<div class="header-left-part">
				<?php
					echo lordcros_weather_info(); // Current location weather info
					echo lordcros_contact_phone_num(); // Contact phone number					
				?>
			</div>

			<div class="header-logo">
				<?php echo lordcros_header_block_logo(); // Site logo ?>
			</div>
			
			<div class="header-right-part">
				<?php
					echo lordcros_multi_language_switcher_arrange(); // Multi-language Switcher
					echo lordcros_room_header_search_form(false); // Check Room Form
					echo lordcros_hamburger_menu_btn();  // Hamburger Menu
				?>

				<div class="header-mobile-nav">
					<?php echo lordcros_hamburger_menu_btn( 'mobile-burger' ); // Mobile Hamburger Menu ?>
				</div>
			</div>
		</div>
	</div>
</header>
<!-- End Header -->

<!-- Sticky Header -->
<?php if ( $sticky_header_setting ) : ?>
	<div <?php lordcros_sticky_header_classes( 'header-layout-3' ); ?>>
		<?php echo lordcros_hamburger_menu_btn(); // Hamburger Menu ?>

		<div class="header-mobile-nav">
			<?php echo lordcros_hamburger_menu_btn( 'mobile-burger' ); // Mobile Hamburger Menu ?>
		</div>
	</div>
<?php endif; ?>
<!-- End Sticky Header -->