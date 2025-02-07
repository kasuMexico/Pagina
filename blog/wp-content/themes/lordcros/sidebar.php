<?php
/* LordCros Sidebar Template */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sidebar_class = lordcros_get_sidebar_class();
$sidebar_name = lordcros_get_sidebar_name();

if( strstr( $sidebar_class, 'col-lg-0' ) ) {
	return;
}

?>

<aside class="sidebar-container <?php echo esc_attr( $sidebar_class ) ?> area-<?php echo esc_attr( $sidebar_name ) ?>" role="complementary">
	<div class="mobile-sidebar-header">
		<h2 class="title"><?php echo esc_html__( '', 'lordcros' ); ?></h2>
		<a href="#" class="close-btn"><?php echo esc_html__( '', 'lordcros' ); ?></a>
	</div>

	<div class="lordcros-sidebar-section lordcros-widget-area">
		<?php
			do_action( 'lordcros_before_sidebar_area' );

			dynamic_sidebar( $sidebar_name );

			do_action( 'lordcros_after_sidebar_area' );
		?>
	</div>
</aside>

<div class="mobile-sidebar-toggle-btn">
	<span class="btn-inner"><i class="lordcros lordcros-angle-right"></i></span>
	<span class="dot-wave"></span>
</div>
