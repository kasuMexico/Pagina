<?php
/*
 * Maintenance Mode - "Coming Soon" Page Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$html_class = 'coming-soon-page';
$coming_soon_content = '';

$bg_image = lordcros_get_opt( 'coming_soon_bg_image' );
$content_block = lordcros_get_opt( 'coming_soon_content' );
$content_pos = lordcros_get_opt( 'coming_soon_content_pos' );

if ( $content_block ) { 
	$coming_soon_content =  lordcros_core_shortcode_html_block( array( 'block_id' => $content_block ) );
}
?>

<!DOCTYPE html>

<html <?php language_attributes(); ?> class="<?php echo esc_attr( $html_class ); ?>">

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
		<div id="page-wrapper" class="page-full-layout">

			<div class="main-content coming_soon_content_wrapper">
				<?php				
				$content_class = '';
				if ( 'left' == $content_pos ) { 
					$content_class = 'col-md-6 offset-md-0 offset-sm-2 col-sm-8';
				} elseif ( 'center' == $content_pos ) { 
					$content_class = 'offset-md-3 col-md-6 offset-sm-2 col-sm-8';
				} elseif ( 'right' == $content_pos ) { 
					$content_class = 'offset-md-6 col-md-6 offset-sm-2 col-sm-8';
				}
				?>

				<div class="row">

					<div class="<?php echo esc_attr( $content_class ) ?>">

						<?php
							// show coming soon page content
							echo '' . $coming_soon_content;
						?>

					</div>

				</div>					
			</div>
		</div>
		<!-- End Page wrapper -->

		<?php 

		$page_inline_style = '.coming_soon_content_wrapper { 
				background-image: url(' . esc_url( $bg_image['background-image'] ) . ');
				background-color: ' . esc_attr( $bg_image['background-color'] ) . ';
			}';

		wp_register_style( 'lordcros-theme-inline-style', false );
		wp_enqueue_style( 'lordcros-theme-inline-style' );
		wp_add_inline_style( 'lordcros-theme-inline-style', $page_inline_style );

		wp_footer(); 

		?>

	</body>

</html>