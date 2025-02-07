<?php
/**
 * Header Layout 10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get Header Values
$sticky_header_setting = lordcros_get_opt( 'sticky_header_setting' );
$container_class = 'container-fluid';

?>

<!-- Header -->
<?php
if ( is_front_page() ) :
	?>

	<div class="header-right-corner-part">
		<?php
			echo lordcros_header_block_account(); // Account sign in/out form
			echo lordcros_multi_language_switcher(); // Multi-language Switcher
		?>
	</div>
	
	<?php
endif;
?>
<!-- End Header -->

<!-- LeftSide Menu -->
<div class="leftside-header header-style-10">
	<?php
	if ( is_front_page() ) :
		?>

		<div class="header-topbar-wrap">
			<?php
				echo lordcros_weather_info(); // Current location weather info
				echo lordcros_contact_phone_num(); // Contact phone number
				echo lordcros_header_social_links(); // Social share/follow links
			?>
		</div>
		
		<?php
	endif;
	?>

	<div class="main-header-wrap">
		<div class="logo-part">
			<?php 
				echo lordcros_header_block_logo(); // Site logo
				echo lordcros_hamburger_menu_btn(); // // Hamburger Menu
			?>

			<div class="header-mobile-nav">
				<?php echo lordcros_hamburger_menu_btn( 'mobile-burger' ); // Mobile Hamburger Menu ?>
			</div>		
		</div>
		
		<?php
		if ( is_front_page() ) :

			?>
			<div class="header-html-block-content">
				<?php echo lordcros_header_block_explain_txt(); // Explain Text Area ?>
			</div>
			<?php
			echo lordcros_room_header_search_form(true); // Check Room Form

		endif;
		?>

		<div class="header-bottom-part">
			<?php
				echo lordcros_weather_info(); // Current location weather info
				echo lordcros_contact_phone_num(); // Contact phone number
			?>
		</div>
	</div>
</div>
<!-- End LeftSide Menu -->