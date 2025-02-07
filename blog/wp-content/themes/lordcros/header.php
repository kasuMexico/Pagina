<?php
/**
 * Main Theme Header
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!DOCTYPE html>

<html <?php language_attributes(); ?> class="supports-fontface">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<link rel="profile" href="//gmpg.org/xfn/11">

	<!-- WordPress wp_head() -->
	<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

	<!-- Start Page Wrapper -->
	<div id="page-wrapper" <?php lordcros_page_classes(); ?>>

		<?php
			// Seleted header layout
			$header_layout = apply_filters( 'lordcros_header_layout', lordcros_get_opt( 'header_layout', 'header-layout-1' ) );

			// Include Header Layout
			lordcros_get_template_part( 'header/' . $header_layout );		
		?>