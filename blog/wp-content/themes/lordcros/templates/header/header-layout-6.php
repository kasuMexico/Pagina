<?php
/**
 * Header Layout 6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get Header Values
$sticky_header_setting = lordcros_get_opt( 'sticky_header_setting' );
$container_class = 'container-fluid';

?>

<!-- Header -->
<header <?php lordcros_header_classes( 'header-layout-6' ); ?>>
	<div class="header-wrapper <?php echo esc_attr( $container_class ); ?>">
		<div class="main-header-wrap">
			<?php 
				echo lordcros_header_block_logo(); // Site logo
				echo lordcros_room_header_search_form( true ); // Check Room Form 
				echo lordcros_header_block_search(); // Search Form
				echo lordcros_multi_language_switcher(); // Multi-language Switcher
			?>

			<div class="header-mobile-nav">
				<?php echo lordcros_hamburger_menu_btn( 'mobile-burger' ); // Mobile Hamburger Menu ?>
			</div>
		</div>
	</div>
</header>
<!-- End Header -->

<!-- LeftSide Menu -->
<div class="leftside-header header-style-6">
	<?php echo lordcros_header_block_logo(); // Site logo ?>

	<div class="header-nav-part">
		<?php echo lordcros_header_block_main_navigation(); // Main Navigation Menu ?>
	</div>

	<div class="header-bottom-part">
		<?php
			echo lordcros_contact_phone_num(); // Contact phone number
			echo lordcros_header_social_links( 'hover_bordered' ); // Social share/follow links
			echo lordcros_header_block_copyright(); // Copyright text
		?>
	</div>
</div>
<!-- End LeftSide Menu -->

<!-- Mobile Sticky Header -->
<?php if ( $sticky_header_setting ) : ?>
	<div <?php lordcros_sticky_header_classes( 'header-layout-1' ); ?>>
		<div class="header-wrapper <?php echo esc_attr( $container_class ); ?>">
			<div class="main-header-wrap">
				<?php echo lordcros_header_block_sticky_logo(); // Site logo ?>

				<div class="header-mobile-nav">
					<?php echo lordcros_hamburger_menu_btn( 'mobile-burger' ); // Mobile Hamburger Menu ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
<!-- End Mobile Sticky Header -->