<?php
/**
 * Header Layout 5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get Header Values
$topbar_enable = lordcros_get_opt( 'topbar_enable' );
$sticky_header_setting = lordcros_get_opt( 'sticky_header_setting' );
$container_class = 'container-fluid';

?>

<!-- Header -->
<header <?php lordcros_header_classes( 'header-layout-5' ); ?>>
	<div class="header-wrapper <?php echo esc_attr( $container_class ); ?>">
		<?php if ( $topbar_enable ) : ?>
			<div class="header-topbar-wrap">
				<div class="topbar-left">
					<?php echo lordcros_header_social_links(); // Social share/follow links ?>
				</div>

				<div class="topbar-center">
					<?php echo lordcros_contact_phone_num(); // Contact phone number ?>
				</div>

				<div class="topbar-right">
					<?php echo lordcros_header_block_account(); // Account sign in/out form ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="main-header-wrap">
			<div class="header-left-part">
				<?php echo lordcros_header_block_main_navigation(); // Main Navigation Menu ?>
			</div>

			<?php echo lordcros_header_block_logo(); // Site logo ?>

			<div class="header-right-part">
				<?php
					echo lordcros_header_block_search(); // Search Form
					echo lordcros_room_header_search_form(false); // Check Room Form
					echo lordcros_multi_language_switcher(); // Multi-language Switcher
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
	<div <?php lordcros_sticky_header_classes( 'header-layout-5' ); ?>>
		<div class="header-wrapper <?php echo esc_attr( $container_class ); ?>">
			<div class="main-header-wrap">
				<div class="header-left-part">
					<?php
						echo lordcros_header_block_main_navigation(); // Main Navigation Menu
					?>	
				</div>

				<?php echo lordcros_header_block_sticky_logo(); // Site logo ?>

				<div class="header-right-part">
					<?php
						echo lordcros_header_block_search(); // Search Form
						echo lordcros_room_header_search_form(false); // Check Room Form
						echo lordcros_multi_language_switcher(); // Multi-language Switcher
					?>

					<div class="header-mobile-nav">
						<?php echo lordcros_hamburger_menu_btn( 'mobile-burger' ); // Mobile Hamburger Menu ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
<!-- End Sticky Header -->