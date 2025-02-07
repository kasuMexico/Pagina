<?php
/**
 * 404 Page Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Get Page Classes
$page_classes = array();
$page_classes[] = 'container';

$page_classes = implode( ' ', $page_classes );

lordcros_page_heading();

?>

<div class="main-content <?php echo esc_attr( $page_classes ); ?>">
	<div class="row page-content-inner">
		<div class="page-content-404">
			<span class="title-404">4<span class="middle-num-404">0</span>4</span>

			<p class="description-404">
				<?php echo esc_html__( 'We are sorry, but we can not find the page you were looking for. It is probably some thing we have done wrong but now we konw about it and we will try to fix it', 'lordcros' ); ?>
			</p>

			<div class="search-form-wrapper-404">
				<form role="search" method="get" id="searchform" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Enter any Keyword', 'placeholder', 'lordcros' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
					<button type="submit" id="searchsubmit" class="searchsubmit"><i class="lordcros lordcros-send"></i></button>
				</form>
			</div>

			<a href="<?php echo get_home_url(); ?>" class="back-home"><?php echo esc_html__( 'Back to Homepage', 'lordcros' ); ?></a>			
		</div>
	</div>
</div>

<?php get_footer(); ?>