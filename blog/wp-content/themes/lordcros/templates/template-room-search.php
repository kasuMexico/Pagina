<?php
/*
 Template Name: Room Search Page Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

lordcros_page_heading(); // Template Page Banner Heading

// Search from default values
$default_args = array(
				'date_from'			=> date( 'm/d/Y' ),
				'date_to'			=> date( 'm/d/Y', strtotime( ' +1 day' ) ),
				'adults'			=> 1,
				'kids'				=> 0,
				'size_order_by'		=> 'DESC',
				'price_order_by'	=> 'ASC',
				'max_price'			=> '',
				'min_price'			=> 0,
				'def_service'		=> array(),
				'extra_service'		=> array(),
				'page_num'			=> 1,
				'per_page'			=> intval( lordcros_get_opt( 'rooms_per_page', '4' ) ),
				'view'				=> lordcros_get_opt( 'default_view', 'grid' ),
			);
extract( wp_parse_args( $_REQUEST, $default_args ) );

// Extra room services
$args = array(
			'posts_per_page'	=> -1,
			'post_type'			=> 'room_service',
			'post_status'		=> 'publish',
			'meta_query'		=> array(
				array(
					'key'		=> 'lordcros_room_service_type',
					'value'		=> 'extra_service',
				),
			),
		);
$extra_services = get_posts( $args );

// Default room services
$args = array(
			'posts_per_page'	=> -1,
			'post_type'			=> 'room_service',
			'post_status'		=> 'publish',
			'meta_query'		=> array(
				array(
					'key'		=> 'lordcros_room_service_type',
					'value'		=> 'def_service',
				),
			),
		);
$def_services = get_posts( $args );

list( $available_rooms, $count, $limit_max_price_val ) = lordcros_core_get_available_rooms( $_REQUEST );

// Default Max Value
if( empty( $limit_max_price_val ) ) {
	$limit_max_price_val = 800;
}

if ( empty( $max_price ) ) {
	$max_price = $limit_max_price_val;
}

// Get Room Currency Symbol
$currency_symbol = lordcros_get_currency_symbol();

if ( have_posts() ) {
	while ( have_posts() ) : the_post();
		$post_id = get_the_ID();
		?>

		<div class="room-book-stepline">
			<div class="container">
				<div id="select-step" class="step-item active">
					<span class="step-icon"></span>
					<span class="step-title"><?php echo esc_html__( 'Select Room', 'lordcros' ); ?></span>
				</div>
				<div id="booking-step" class="step-item">
					<span class="step-icon"></span>
					<span class="step-title"><?php echo esc_html__( 'Booking', 'lordcros' ); ?></span>
				</div>
				<div id="checkout-step" class="step-item">
					<span class="step-icon"></span>
					<span class="step-title"><?php echo esc_html__( 'Checkout', 'lordcros' ); ?></span>
				</div>
				<div id="confirm-step" class="step-item">
					<span class="step-icon"></span>
					<span class="step-title"><?php echo esc_html__( 'Thank you', 'lordcros' ); ?></span>
				</div>
			</div>
		</div>

		<div class="main-content search-available-rooms">
			<div class="container">
				<div class="row">
					<aside class="col-lg-4">
						<div class="mobile-sidebar-header">
							<h2 class="title"><?php echo esc_html__( 'Sidebar', 'lordcros' ); ?></h2>
							<a href="#" class="close-btn"><?php echo esc_html__( 'Close', 'lordcros' ); ?></a>
						</div>

						<div class="search-form-wrapper">
							<?php echo lordcros_core_room_search_form(); ?>

							<div class="extra-fields-wrap">
								<div id="form_price_filter" class="price-filter">
									<div class="service-filter-wrap">
										<h2 class="filter-title"><?php echo esc_html__( 'Trip Price', 'lordcros' ); ?></h2>

										<div id="price-filter-slider" data-min-price="<?php echo esc_attr( $min_price ); ?>" data-max-price="<?php echo esc_attr( $max_price ); ?>" data-url="<?php echo esc_url( add_query_arg( array( 'page_num' => 1, 'max_price' => false, 'min_price' => false ) ) ); ?>"></div>

										<div class="price-filter-amount" data-list-max-val="<?php echo esc_attr( $limit_max_price_val ); ?>">
											<div class="show-price-values">
												<div class="min-price">
													<span class="price-currency"><?php echo esc_html( $currency_symbol ); ?></span>
													<span class="price-val"><?php echo esc_html( $min_price ); ?></span>
												</div>
												<span class="price-dash"> - </span>
												<div class="max-price">
													<span class="price-currency"><?php echo esc_html( $currency_symbol ); ?></span>
													<span class="price-val"><?php echo esc_html( $max_price ); ?></span>
												</div>
											</div>

											<div class="price-hidden-values">
												<input type="text" name="min_price" value="<?php echo esc_attr( $min_price ); ?>">
												<input type="text" name="max_price" value="<?php echo esc_attr( $max_price ); ?>">
											</div>
										</div>
									</div>
								</div>
								<div id="form_default_service_filter" class="service-filter" data-url="<?php echo esc_url( add_query_arg( array( 'def_service' => false, 'page_num' => 1 ) ) ); ?>">
									<div class="service-filter-wrap">
										<h2 class="filter-title"><?php echo esc_html__( 'Default Services', 'lordcros' ); ?></h2>

										<div class="service-filter-section">
											<?php if ( ! empty( $def_services ) )	: ?>
												<?php foreach ( $def_services as $d_service ) : ?>
													<div class="form-group def-service-field-wrap">
														<input type="checkbox" class="form-control" id="def_service_<?php echo esc_attr( $d_service->ID ); ?>" name="def_service[]" value="<?php echo esc_attr( $d_service->ID ); ?>" <?php echo ( in_array( $d_service->ID, $def_service ) ) ? 'checked' : ''; ?> >
														<label for="def_service_<?php echo esc_attr( $d_service->ID ); ?>"><?php echo esc_html( $d_service->post_title ); ?></label>
													</div>
												<?php endforeach; ?>
											<?php endif; ?>
										</div>
									</div>
								</div>
								<div id="form_extra_service_filter" class="service-filter" data-url="<?php echo esc_url( add_query_arg( array( 'extra_service' => false, 'page_num' => 1 ) ) ); ?>">
									<div class="service-filter-wrap">
										<h2 class="filter-title"><?php echo esc_html__( 'Extra Services', 'lordcros' ); ?></h2>
										
										<div class="service-filter-section">
											<?php if ( ! empty( $extra_services ) )	: ?>
												<?php foreach ( $extra_services as $e_service ) : ?>
													<div class="form-group extra-service-field-wrap">
														<input type="checkbox" class="form-control" id="extra_service_<?php echo esc_attr( $e_service->ID ); ?>" name="extra_service[]" value="<?php echo esc_attr( $e_service->ID ); ?>" <?php echo ( in_array( $e_service->ID, $extra_service ) ) ? 'checked' : ''; ?> >
														<label for="extra_service_<?php echo esc_attr( $e_service->ID ); ?>"><?php echo esc_html( $e_service->post_title ); ?></label>
													</div>
												<?php endforeach; ?>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</aside>

					<div class="mobile-sidebar-toggle-btn">
						<span class="btn-inner"><i class="lordcros lordcros-angle-right"></i></span>
						<span class="dot-wave"></span>
					</div>

					<div class="col-lg-8">
						<div class="order-view-filter-wrap">
							<div class="order-filter-section">
								<div class="price-order-by-wrap">
									<select id="price-order-by" name="price_order_by" class="price-order-by">
										<option value="ASC" <?php selected( $price_order_by, 'ASC' ); ?>><?php echo esc_html__( 'Price: Low To High', 'lordcros' ); ?></option>
										<option value="DESC" <?php selected( $price_order_by, 'DESC' ); ?>><?php echo esc_html__( 'Price: High To Low', 'lordcros' ); ?></option>
									</select>
								</div>
								<div class="size-order-by-wrap">
									<select id="size-order-by" name="size_order_by" class="size-order-by">
										<option value="ASC" <?php selected( $size_order_by, 'ASC' ); ?>><?php echo esc_html__( 'Size: Small To Large', 'lordcros' ); ?></option>
										<option value="DESC" <?php selected( $size_order_by, 'DESC' ); ?>><?php echo esc_html__( 'Size: Large To Small', 'lordcros' ); ?></option>
									</select>
								</div>
							</div>
							<div class="view-mode-section">
								<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'block' ) ) ); ?>" class="<?php echo ( 'block' == $view ) ? 'active' : ''; ?>"><i class="fas fa-th"></i></a>
								<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'grid' ) ) ); ?>" class="<?php echo ( 'grid' == $view ) ? 'active' : ''; ?>"><i class="fas fa-th-large"></i></a>
								<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'list' ) ) ); ?>" class="<?php echo ( 'list' == $view ) ? 'active' : ''; ?>"><i class="fas fa-list"></i></a>
							</div>
						</div>

						<?php if ( ! empty( $available_rooms ) ) : ?>

							<div class="available-rooms-wrap row" data-col="<?php echo ( 'list' == $view ) ? '1' : '2'; ?>">
							
								<?php
								foreach ( $available_rooms as $room ) {
									if ( $view == 'grid' ) {
										echo lordcros_core_room_get_grid_view_html( $room['room_id'], $room['price'], $date_from, $date_to, $adults, $kids, 'h2' );
									} elseif ( $view == 'block' ) {
										echo lordcros_core_room_get_block_view_html( $room['room_id'], $room['price'], $date_from, $date_to, $adults, $kids );
									} else {
										echo lordcros_core_room_get_list_view_html( $room['room_id'], $room['price'], $date_from, $date_to, $adults, $kids, 'h2' );
									}
								}
								?>
							
							</div>

							<div class="lordcros-pagination">
								<?php
									unset( $_GET['page_num'] );
								
									$pagenum_link = strtok( filter_input( INPUT_SERVER, 'REQUEST_URI' ), '?' ) . '%_%';
									$total = ceil( $count / $per_page );
									$args = array(
										'base'		=> $pagenum_link,
										'total'		=> $total,
										'format'	=> '?page_num=%#%',
										'current'	=> $page_num,
										'show_all'	=> false,
										'prev_next'	=> true,
										'prev_text'	=> esc_html__( 'Previous', 'lordcros' ),
										'next_text'	=> esc_html__( 'Next', 'lordcros' ),
										'end_size'	=> 1,
										'mid_size'	=> 2,
										'type'		=> 'list',
										'add_args'	=> $_GET,
									);
									echo paginate_links( $args );
								?>
							</div>

						<?php else : ?>
							
							<div class="lordcros-msg warning-msg-wrap">
								<i class="fas fa-exclamation-triangle"></i>
								<p class="warning-description"><?php echo esc_html__( 'There is no available rooms.', 'lordcros' ); ?></p>
							</div>

						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<?php 
	endwhile;
}

get_footer();