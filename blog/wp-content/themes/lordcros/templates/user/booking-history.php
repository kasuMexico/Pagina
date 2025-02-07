<?php $user_id = get_current_user_id(); ?>
<h2 class="tab-content-title"><?php echo esc_html__( 'Booking', 'lordcros' ); ?></h2>

<div class="booking-block-wrap">
	<div class="block-content-inner">
		<h3 class="block-title"><?php echo esc_html__( 'Trips You have Booked!', 'lordcros' ); ?></h3>

		<div class="filter-section">
			<form class="booking-status-filter">
				<input type="hidden" name="action" value="update_booking_list">

				<div class="form-filter-wrap">
					<div class="radio-filter-part">
						<div class="single-radio">
							<input type="radio" name="status" id="all-type" checked value="" />
							<label for="all-type" class="radio radio-inline"><?php echo esc_html__( 'All Types', 'lordcros' ); ?></label>
						</div>

						<div class="single-radio">
							<input type="radio" name="status" id="upcoming" value="new" />
							<label for="upcoming" class="radio radio-inline"><?php echo esc_html__( 'UPCOMING', 'lordcros' ); ?></label>
						</div>

						<div class="single-radio">
							<input type="radio" name="status" id="canceled" value="canceled" />
							<label for="canceled" class="radio radio-inline"><?php echo esc_html__( 'CANCELED', 'lordcros' ); ?></label>	
						</div>

						<div class="single-radio">
							<input type="radio" name="status" id="completed" value="confirmed"/>
							<label for="completed" class="radio radio-inline"><?php echo esc_html__( 'COMPLETED', 'lordcros' ); ?></label>
						</div>
					</div>

					<div class="sort-filter-part">
						<h6 class="sort-title"><?php echo esc_html__( 'Sort results by' ,'lordcros' ); ?>:</h6>
						<input type="hidden" name="sort_by" value="created">
						<input type="hidden" name="order" value="desc">
						<button class="sort-by-btn active" value="created"><?php echo esc_html__( 'DATE', 'lordcros' ); ?></button>
						<button class="sort-by-btn" value="total_price"><?php echo esc_html__( 'PRICE', 'lordcros' ); ?></button>
					</div>
				</div>
			</form>
		</div>
		<div class="booking-history">
			<?php echo apply_filters( 'lordcros_user_booking_list', $user_id, '', 'created', 'desc' ); ?>
		</div>
	</div>
</div>
