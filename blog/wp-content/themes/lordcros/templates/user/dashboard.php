<?php
$user_id = get_current_user_id();
$user_info = lordcros_get_current_user_info();

$total_booking_count = lordcros_core_get_booking_count( $user_id );
if ( empty( $total_booking_count ) ) {
	$total_booking_count = 0;
}

$completed_booking_count = lordcros_core_get_booking_count( $user_id, 'confirmed' );
if ( empty( $completed_booking_count ) ) {
	$completed_booking_count = 0;
}

$canceled_booking_count = lordcros_core_get_booking_count( $user_id, 'canceled' );
if ( empty( $canceled_booking_count ) ) {
	$canceled_booking_count = 0;
}

$upcoming_booking_count = lordcros_core_get_booking_count( $user_id, 'new' );
if ( empty( $upcoming_booking_count ) ) {
	$upcoming_booking_count = 0;
}

?>

<h2 class="tab-content-title"><?php echo esc_html__( 'Dashboard', 'lordcros' ); ?></h2>

<div class="booking-information-table">
	<div class="single-information booking-info">
		<div class="information-wrap">
			<div class="text-part">
				<span class="title"><?php echo esc_html__( 'Booking', 'lordcros' ); ?></span>
				<span class="numbers"><?php echo esc_html( $total_booking_count ); ?></span>
			</div>

			<div class="icon-part">
				<i class="lordcros lordcros-calendar"></i>
			</div>
		</div>
	</div>

	<div class="single-information upcoming-info">
		<div class="information-wrap">
			<div class="text-part">
				<span class="title"><?php echo esc_html__( 'Upcoming', 'lordcros' ); ?></span>
				<span class="numbers"><?php echo esc_html( $upcoming_booking_count ); ?></span>
			</div>

			<div class="icon-part">
				<i class="lordcros lordcros-calendar"></i>
			</div>
		</div>
	</div>

	<div class="single-information cancelled-info">
		<div class="information-wrap">
			<div class="text-part">
				<span class="title"><?php echo esc_html__( 'Canceled', 'lordcros' ); ?></span>
				<span class="numbers"><?php echo esc_html( $canceled_booking_count ); ?></span>
			</div>

			<div class="icon-part">
				<i class="lordcros lordcros-calendar"></i>
			</div>
		</div>
	</div>

	<div class="single-information completed-info">
		<div class="information-wrap">
			<div class="text-part">
				<span class="title"><?php echo esc_html__( 'Completed', 'lordcros' ); ?></span>
				<span class="numbers"><?php echo esc_html( $completed_booking_count ); ?></span>
			</div>

			<div class="icon-part">
				<i class="lordcros lordcros-calendar"></i>
			</div>
		</div>
	</div>
</div>

<div class="dashboard-blocks-wrap">
	<div class="block-content">
		<div class="block-content-inner">
			<h3 class="block-title"><?php printf( esc_html__( 'What\'s New On %s' ,'lordcros' ), get_bloginfo( 'name' ) ) ?></h3>
			
			<ul class="block-list-wrap new-list">
				<?php 
					$list_size = 8;
					$available_post_types = array( 'post', 'service', 'room' );

					$args = array(
									'posts_per_page'	=> $list_size,
									'orderby'			=> 'date',
									'order'				=> 'desc',
									'post_status'		=> 'publish',
									'post_type'			=> $available_post_types
								);
					$the_query = new WP_Query( $args );

					if ( $the_query->have_posts() ) {
						while ( $the_query->have_posts() ) {
							$the_query->the_post();
							$post_type = get_post_type( get_the_id() );
							?>		
							<li class="single-list">							
								<a href="<?php the_permalink(); ?>" class="list-link">
									<?php if ( $post_type == 'service' ) { ?>
										<span class="list-icon"><i class="lordcros lordcros-hotel-bell"></i></span>

										<span class="txt-part">
											<p class="list-title"><?php the_title(); ?></p>
											<span class="list-time"><?php echo human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'lordcros' ); ?></span>
										</span>
									<?php } elseif ( $post_type == 'room' ) { ?>
										<span class="list-icon"><i class="lordcros lordcros-hotel-bell"></i></span>

										<span class="txt-part">
											<p class="list-title"><?php the_title(); ?> <?php echo esc_html__( 'in', 'lordcros' ); ?> 
												<span class="price"><?php echo lordcros_price( get_post_meta( get_the_id(), 'lordcros_room_price', true ) ); ?></span>
											</p>
											<span class="list-time"><?php echo human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'lordcros' ); ?></span>
										</span>
									<?php } elseif ( $post_type == 'post' ) { ?>
										<span class="list-icon"><i class="lordcros lordcros-hotel-bell"></i></span>

										<span class="txt-part">
											<p class="list-title"><?php the_title(); ?></p>
											<span class="list-time"><?php echo human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'lordcros' ); ?></span>
										</span>
									<?php } ?>
								</a>
							</li>
							<?php
						}
					} else {
						echo esc_html__( "Nothing New.", "lordcros" );
					}

					wp_reset_postdata();
				?>
			</ul>
		</div>
	</div>
	<div class="block-content">
		<div class="block-content-inner">
			<h3 class="block-title"><?php echo esc_html__( 'Recent Activity', 'lordcros' ) ?></h3>

			<ul class="block-list-wrap recent-activity-list">
				<?php
					$recent_activity = get_user_meta( $user_id, 'recent_activity', true );
					if ( ! empty( $recent_activity ) ) {
						$recent_activity_array = unserialize( $recent_activity );
						foreach ( $recent_activity_array as $post_id ) {

							$post_type = get_post_type( $post_id );
							
							if ( ! in_array( $post_type, $available_post_types ) ) {
								continue;
							}
							
							if ( $post_type == 'room' ) {
								?>
								<li class="single-list">
									<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="list-link">
										<span class="list-icon"><i class="lordcros lordcros-sun"></i></span>
										<p class="list-title"><?php echo get_the_title( $post_id ); ?>
											<span class="price"><?php echo lordcros_price( get_post_meta( $post_id, 'lordcros_room_price', true ) ); ?></span>
										</p>
									</a>
								</li>
							<?php } elseif ( $post_type == 'service' ) {
								?>
								<li class="single-list">
									<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="list-link">
										<span class="list-icon"><i class="lordcros lordcros-sun"></i></span>
										<p class="list-title"><?php echo get_the_title( $post_id ); ?></p>
									</a>
								</li>
							<?php } elseif ( $post_type == 'post' ) {
								?>
								<li class="single-list">
									<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="list-link">
										<span class="list-icon"><i class="lordcros lordcros-sun"></i></span>
										<p class="list-title"><?php echo get_the_title( $post_id ); ?></p>
									</a>
								</li>
							<?php }
						}
					} else {
						echo esc_html__( "You don't have any recent activities.", "lordcros" );
					}
				?>
			</ul>
		</div>
	</div>
</div>