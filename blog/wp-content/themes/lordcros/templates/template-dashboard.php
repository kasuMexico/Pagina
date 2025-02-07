<?php
/*
 Template Name: Dashboard Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_user_logged_in() ) {
	wp_redirect( lordcros_login_url() );
	exit;
}

get_header();

// Get Page Classes
$page_classes = array();
$page_classes[] = 'container-fluid';
$page_classes[] = 'dashboard-page-wrap';

$page_classes = implode( ' ', $page_classes );
$content_class = '';

lordcros_page_heading();
?>

<div class="main-content <?php echo esc_attr( $page_classes ); ?>">
	<?php do_action( 'lordcros_before_dashboard' ); ?>

	<ul id="pills-tab" class="nav nav-pills tab-head" role="tablist">
		<li class="nav-item">
			<a id="pills-dashboard-tab" class="nav-link active" data-toggle="pill" role="tab" aria-controls="pills-dashboard" href="#pills-dashboard" aria-selected="true"><i class="lordcros lordcros-puzzle"></i><?php echo esc_html__( 'Dashboard', 'lordcros' ); ?></a>
		</li>
		<li class="nav-item">
			<a id="pills-profile-tab" class="nav-link" data-toggle="pill" role="tab" aria-controls="pills-profile" href="#pills-profile" aria-selected="false"><i class="lordcros lordcros-two-users"></i><?php echo esc_html__( 'Profile', 'lordcros' ); ?></a>
		</li>
		<li class="nav-item">
			<a id="pills-booking-tab" class="nav-link" data-toggle="pill" role="tab" aria-controls="pills-booking" href="#pills-booking" aria-selected="false"><i class="lordcros lordcros-shopping-bag"></i><?php echo esc_html__( 'Booking', 'lordcros' ); ?></a>
		</li>
		<li class="nav-item">
			<a id="pills-logout-tab" class="nav-link" href="<?php echo wp_logout_url(); ?>"><i class="lordcros lordcros-logout"></i><?php echo esc_html__( 'Logout', 'lordcros' ); ?></a>
		</li>
	</ul>

	<div id="pills-tabContent" class="tab-content">
		<div id="pills-dashboard" class="tab-pane fade show active" role="tabpanel" aria-labelledby="pills-dashboard-tab">
			<?php lordcros_get_template_part( 'user/dashboard' ); ?>
		</div>
		
		<div id="pills-profile" class="tab-pane fade" role="tabpanel" aria-labelledby="pills-profile-tab">
			<?php lordcros_get_template_part( 'user/profile' ); ?>
		</div>

		<div id="pills-booking" class="tab-pane fade" role="tabpanel" aria-labelledby="pills-booking-tab">
			<?php lordcros_get_template_part( 'user/booking-history' ); ?>
		</div>
	</div>
</div>

<?php get_footer(); ?>