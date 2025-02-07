<?php
/**
 * Header Layout 4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get Header Values
$sticky_header_setting = lordcros_get_opt( 'sticky_header_setting' );
$container_class = 'container';

?>

<!-- Header -->
<header <?php lordcros_header_classes( 'header-layout-4' ); ?>>
	<div class="header-wrapper <?php echo esc_attr( $container_class ); ?>">
		<div class="main-header-wrap">
			<div class="header-left-part">
				<?php echo lordcros_weather_info(); // Current location weather info ?>
			</div>

			<div class="header-logo">
				<?php echo lordcros_header_block_logo(); // Site logo ?>
			</div>
			
			<div class="header-right-part">
				<?php
					echo lordcros_header_social_links(); // Social share/follow links
					echo lordcros_multi_language_switcher(); // Multi-language Switcher
				?>
			</div>
		</div>
	</div>
</header>
<!-- End Header -->

<!-- LeftSide Menu -->
<div class="leftside-header header-style-4">
	<?php echo lordcros_hamburger_menu_btn(); // Hamburger Menu ?>

	<div class="header-mobile-nav">
		<?php echo lordcros_hamburger_menu_btn( 'mobile-burger' ); // Mobile Hamburger Menu ?>
	</div>

	<div class="header-bottom-part">
		<?php
			echo lordcros_contact_phone_num(); // Contact phone number
			echo lordcros_header_info_mail(); // Contact info e-mail address
		?>
	</div>
</div>
<!-- End LeftSide Menu -->