<?php
/*
 * functions releted rooms
 */

defined( 'ABSPATH' ) || exit;

/* Search available rooms */
if ( ! function_exists( 'lordcros_core_get_available_rooms' ) ) {
	function lordcros_core_get_available_rooms( $args ) {
		//$date_from = '', $date_to = '', $adults = 1, $kids = 0, $order_by = 'size', $order = 'ASC', $max_price = '', $def_service = array(), $extra_service = array()
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
		);
		extract( wp_parse_args( $args, $default_args ) );

		$date_from = date( 'Y-m-d', strtotime( $date_from ) );
		$date_to = date( 'Y-m-d', strtotime( $date_to ) );

		if ( ! is_array( $def_service ) ) {
			$def_service = array( $def_service );
		}
		if ( ! is_array( $extra_service ) ) {
			$extra_service = array( $extra_service );
		}

		if ( empty( $size_order_by ) || $size_order_by != "ASC" ) {
			$size_order_by = "DESC";
		}
		if ( empty( $price_order_by ) || $price_order_by != "DESC" ) {
			$price_order_by = "ASC";
		}

		global $wpdb, $language_count;

		$date_from_obj = new DateTime( $date_from );
		$date_to_obj = new DateTime( $date_to );

		$tbl_posts = esc_sql( $wpdb->posts );
		$tbl_postmeta = esc_sql( $wpdb->postmeta );
		$tbl_terms = esc_sql( $wpdb->prefix . 'terms' );
		$tbl_term_taxonomy = esc_sql( $wpdb->prefix . 'term_taxonomy' );
		$tbl_term_relationships = esc_sql( $wpdb->prefix . 'term_relationships' );
		$tbl_icl_translations = esc_sql( $wpdb->prefix . 'icl_translations' );

		$adults = esc_sql( $adults );
		$kids = esc_sql( $kids );

		$sql = " SELECT * FROM {$tbl_posts} WHERE post_type = 'room' AND post_status = 'publish' ";
		$select = "SELECT DISTINCT room.ID AS room_id FROM ( {$sql} ) as room ";
		$join = " ";
		$where = " WHERE 1=1 ";
		
		// search with minimum stay field
		$stay_dates_obj = $date_to_obj->diff( $date_from_obj );
		$stay_dates = $stay_dates_obj->format( '%a' );
		
		$join .= " LEFT JOIN {$tbl_postmeta} AS meta_min_stay ON ( room.ID = meta_min_stay.post_id ) AND ( meta_min_stay.meta_key = 'lordcros_room_min_stay' ) ";
		$where .= " AND ( ISNULL( meta_min_stay.meta_value ) OR ( meta_min_stay.meta_value <= {$stay_dates} ) ) ";

		// search with adults and kis
		$join .= " LEFT JOIN {$tbl_postmeta} AS meta_adults ON ( room.ID = meta_adults.post_id ) AND ( meta_adults.meta_key = 'lordcros_room_adults' ) ";
		$join .= " LEFT JOIN {$tbl_postmeta} AS meta_children ON ( room.ID = meta_children.post_id ) AND ( meta_children.meta_key = 'lordcros_room_children' ) ";
		$where .= " AND ( meta_adults.meta_value >= {$adults} ) AND ( meta_adults.meta_value + IFNULL( meta_children.meta_value, 0 ) >= {$adults} + {$kids} ) ";
		
		$sql = $select . $join . $where;

		// if wpml is enabled do search by default language post
		if ( defined( 'ICL_LANGUAGE_CODE' ) && ( $language_count > 1 ) ) {
			$sql = " SELECT DISTINCT it2.element_id AS room_id FROM ( {$sql} ) AS t0
						INNER JOIN {$tbl_icl_translations} it1 ON ( it1.element_type = 'post_room' ) AND it1.element_id = t0.room_id
						INNER JOIN {$tbl_icl_translations} it2 ON ( it2.element_type = 'post_room' ) AND it2.language_code = '" . lordcros_get_default_language() . "' AND it2.trid = it1.trid ";
		}

		// get speciality post sql
		$speciality_post_sql = " SELECT speciality_posts.ID
							FROM {$tbl_posts} speciality_posts WHERE speciality_posts.post_type = 'speciality' AND post_status = 'publish' ";

		// get date block range sql
		$date_block_sql = " SELECT date_block_posts.ID AS date_block_post_id, meta_speciality_date_from.meta_value AS date_from, meta_speciality_date_to.meta_value AS date_to 
							FROM ( {$speciality_post_sql} ) AS date_block_posts 
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_type ON ( date_block_posts.ID = meta_speciality_type.post_id ) AND ( meta_speciality_type.meta_key = 'lordcros_speciality_type' )
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_date_from ON ( date_block_posts.ID = meta_speciality_date_from.post_id ) AND ( meta_speciality_date_from.meta_key = 'lordcros_speciality_date_from' )
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_date_to ON ( date_block_posts.ID = meta_speciality_date_to.post_id ) AND ( meta_speciality_date_to.meta_key = 'lordcros_speciality_date_to' ) 
							WHERE meta_speciality_type.meta_value = 'date_block' ";

		// get price special sql
		$price_special_sql = " SELECT price_speciality_posts.ID AS price_speciality_post_id, meta_speciality_date_from.meta_value AS date_from, meta_speciality_date_to.meta_value AS date_to, meta_speciality_price.meta_value AS special_price 
							FROM ( {$speciality_post_sql} ) AS price_speciality_posts 
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_type ON ( price_speciality_posts.ID = meta_speciality_type.post_id ) AND ( meta_speciality_type.meta_key = 'lordcros_speciality_type' )
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_date_from ON ( price_speciality_posts.ID = meta_speciality_date_from.post_id ) AND ( meta_speciality_date_from.meta_key = 'lordcros_speciality_date_from' )
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_date_to ON ( price_speciality_posts.ID = meta_speciality_date_to.post_id ) AND ( meta_speciality_date_to.meta_key = 'lordcros_speciality_date_to' ) 
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_price ON ( price_speciality_posts.ID = meta_speciality_price.post_id ) AND ( meta_speciality_price.meta_key = 'lordcros_speciality_price' ) 
							WHERE meta_speciality_type.meta_value = 'price_variation' ";
		
		// check room availability and every night price with booking data and specialities
		$date_interval = DateInterval::createFromDateString( '1 day' );
		$period = new DatePeriod( $date_from_obj, $date_interval, $date_to_obj );
		$sql_check_date_parts = array();
		
		foreach ( $period as $dt ) {
			$check_date = $dt->format( "Y-m-d" );
			$sql_check_date_parts[] = " SELECT '{$check_date}' AS check_date ";
		}
		$sql_check_date = implode( ' UNION ', $sql_check_date_parts );
		$sql = " SELECT t1.room_id, check_dates.check_date, IFNULL( meta_qty.meta_value, 0 ) AS room_qty
					FROM ( {$sql} ) AS t1
					JOIN ( {$sql_check_date} ) AS check_dates
					LEFT JOIN {$tbl_postmeta} AS meta_qty ON ( t1.room_id = meta_qty.post_id ) AND ( meta_qty.meta_key = 'lordcros_room_qty' ) 
					GROUP BY t1.room_id, check_dates.check_date ";
		$sql2 = "SELECT post_id, check_date, COUNT(*) AS room_booked_qty
					FROM " . LORDCROS_ROOM_BOOKINGS_TABLE . " AS bookings
					JOIN ( {$sql_check_date} ) AS check_dates
					WHERE ( bookings.status != 'canceled' ) AND ( bookings.date_from <= check_dates.check_date ) AND ( bookings.date_to > check_dates.check_date ) 
					GROUP BY post_id, check_date";
		$sql = " SELECT ttt1.room_id, ttt1.check_date, ( ttt1.room_qty - IFNULL( ttt2.room_booked_qty, 0 ) ) AS available_room_qty
					FROM ( {$sql} ) AS ttt1
					LEFT JOIN ( {$sql2} ) AS ttt2
					ON ttt1.room_id = ttt2.post_id AND ttt1.check_date = ttt2.check_date";

		// combine date block range sql
		$sql = " SELECT t2.room_id, t2.check_date, IF( MAX( t3.date_block_post_id ) > 0, 0, t2.available_room_qty ) AS available_room_qty 
					FROM ( {$sql} ) AS t2
					LEFT JOIN {$tbl_postmeta} AS meta_room_date_block ON ( t2.room_id = meta_room_date_block.post_id ) AND ( meta_room_date_block.meta_key = 'lordcros_room_date_block' ) 
					LEFT JOIN ( {$date_block_sql} ) AS t3 ON ( meta_room_date_block.meta_value = t3.date_block_post_id AND t2.check_date <= t3.date_to AND t2.check_date >= t3.date_from ) 
					GROUP BY t2.room_id, t2.check_date ";
		$sql = " SELECT * FROM ( {$sql} ) AS tt1 WHERE tt1.room_id NOT IN ( SELECT DISTINCT tt2.room_id FROM ( {$sql} ) AS tt2 WHERE tt2.available_room_qty <= 0 ) ";

		// combine date special price
		$sql = " SELECT t4.room_id, t4.check_date, t4.available_room_qty, MAX( t5.special_price ) AS special_price
					FROM ( {$sql} ) AS t4
					LEFT JOIN {$tbl_postmeta} AS meta_room_date_price ON ( t4.room_id = meta_room_date_price.post_id ) AND ( meta_room_date_price.meta_key = 'lordcros_room_price_variation' ) 
					LEFT JOIN ( {$price_special_sql} ) AS t5 ON ( meta_room_date_price.meta_value = t5.price_speciality_post_id AND t4.check_date <= t5.date_to AND t4.check_date >= t5.date_from ) 
					GROUP BY t4.room_id, t4.check_date ";

		// get price based on special price and week day price and regular price
		$sql = " SELECT t6.room_id, t6.check_date, t6.available_room_qty, 
							CASE WHEN ! ( t6.special_price IS NULL OR t6.special_price = '' ) THEN t6.special_price
								WHEN WEEKDAY( t6.check_date ) = 0 AND ! ( meta_room_price_mon.meta_value IS NULL OR meta_room_price_mon.meta_value = '' ) THEN meta_room_price_mon.meta_value 
								WHEN WEEKDAY( t6.check_date ) = 1 AND ! ( meta_room_price_tue.meta_value IS NULL OR meta_room_price_tue.meta_value = '' ) THEN meta_room_price_tue.meta_value
								WHEN WEEKDAY( t6.check_date ) = 2 AND ! ( meta_room_price_wed.meta_value IS NULL OR meta_room_price_wed.meta_value = '' ) THEN meta_room_price_wed.meta_value
								WHEN WEEKDAY( t6.check_date ) = 3 AND ! ( meta_room_price_thu.meta_value IS NULL OR meta_room_price_thu.meta_value = '' ) THEN meta_room_price_thu.meta_value
								WHEN WEEKDAY( t6.check_date ) = 4 AND ! ( meta_room_price_fri.meta_value IS NULL OR meta_room_price_fri.meta_value = '' ) THEN meta_room_price_fri.meta_value
								WHEN WEEKDAY( t6.check_date ) = 5 AND ! ( meta_room_price_sat.meta_value IS NULL OR meta_room_price_sat.meta_value = '' ) THEN meta_room_price_sat.meta_value
								WHEN WEEKDAY( t6.check_date ) = 6 AND ! ( meta_room_price_sun.meta_value IS NULL OR meta_room_price_sun.meta_value = '' ) THEN meta_room_price_sun.meta_value
								ELSE meta_room_price.meta_value
							END AS price
					FROM ( {$sql} ) AS t6
					LEFT JOIN {$tbl_postmeta} AS meta_room_price ON ( t6.room_id = meta_room_price.post_id ) AND ( meta_room_price.meta_key = 'lordcros_room_price' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_mon ON ( t6.room_id = meta_room_price_mon.post_id ) AND ( meta_room_price_mon.meta_key = 'lordcros_room_price_mon' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_tue ON ( t6.room_id = meta_room_price_tue.post_id ) AND ( meta_room_price_tue.meta_key = 'lordcros_room_price_tue' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_wed ON ( t6.room_id = meta_room_price_wed.post_id ) AND ( meta_room_price_wed.meta_key = 'lordcros_room_price_wed' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_thu ON ( t6.room_id = meta_room_price_thu.post_id ) AND ( meta_room_price_thu.meta_key = 'lordcros_room_price_thu' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_fri ON ( t6.room_id = meta_room_price_fri.post_id ) AND ( meta_room_price_fri.meta_key = 'lordcros_room_price_fri' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_sat ON ( t6.room_id = meta_room_price_sat.post_id ) AND ( meta_room_price_sat.meta_key = 'lordcros_room_price_sat' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_sun ON ( t6.room_id = meta_room_price_sun.post_id ) AND ( meta_room_price_sun.meta_key = 'lordcros_room_price_sun' ) ";

		// if wpml is enabled return current language posts
		if ( defined( 'ICL_LANGUAGE_CODE' ) && ( $language_count > 1 ) && ( lordcros_get_default_language() != ICL_LANGUAGE_CODE ) ) {
			$sql = "SELECT it4.element_id AS room_id, t7.check_date, t7.available_room_qty FROM ( {$sql} ) AS t7
					INNER JOIN {$tbl_icl_translations} it3 ON ( it3.element_type = 'post_room' ) AND it3.element_id = t7.room_id
					INNER JOIN {$tbl_icl_translations} it4 ON ( it4.element_type = 'post_room' ) AND it4.language_code='" . ICL_LANGUAGE_CODE . "' AND it4.trid = it3.trid";
		}

		// for max price
		$max_price_sql = " SELECT room_id, SUM( price ) AS price FROM ( {$sql} ) AS t8 GROUP BY room_id ";
		$max_price_sql = "SELECT MAX( t9.price ) AS max_price FROM ( {$max_price_sql} ) AS t9 ";
		$max_price_val = $wpdb->get_var( $max_price_sql );

		$sql = " SELECT room_id, meta_room_size.meta_value AS size, MIN( available_room_qty ) AS qty, SUM( price ) AS price 
				FROM ( {$sql} ) AS t8 
				LEFT JOIN {$tbl_postmeta} AS meta_room_size ON ( t8.room_id = meta_room_size.post_id ) AND ( meta_room_size.meta_key = 'lordcros_room_size' ) 
				GROUP BY room_id ";

		// search with default service
		$def_service_where_clause = "";
		if ( ! empty( $def_service ) ) {
			$def_service_string = implode( ",", $def_service );
			$def_service_count = count( $def_service );
			$sub_query = " SELECT post_id 
							FROM {$tbl_postmeta} AS meta_def_service 
							WHERE meta_def_service.meta_value IN ( {$def_service_string} ) 
							AND meta_def_service.meta_key = 'lordcros_room_def_service' 
							GROUP BY post_id 
							HAVING COUNT( * ) = {$def_service_count} ";

			$def_service_where_clause = " AND ( room_id IN ( {$sub_query} ) ) ";
		}
		
		// search with extra service
		$extra_service_where_clause = "";
		if ( ! empty( $extra_service ) ) {
			$extra_service_string = implode( ",", $extra_service );
			$extra_service_count = count( $extra_service );
			$sub_query = " SELECT post_id 
							FROM {$tbl_postmeta} AS meta_extra_service 
							WHERE meta_extra_service.meta_value IN ( {$extra_service_string} ) 
							AND meta_extra_service.meta_key = 'lordcros_room_extra_service' 
							GROUP BY post_id 
							HAVING COUNT( * ) = {$extra_service_count} ";

			$extra_service_where_clause = " AND ( room_id IN ( {$sub_query} ) )";
		}

		$sql = "SELECT room_id, size, qty, price FROM ( {$sql} ) AS t9 WHERE 1 = 1 {$def_service_where_clause} {$extra_service_where_clause} AND price >= {$min_price} ";

		if ( ! empty( $max_price) ) {
			$sql .= " AND price <= {$max_price} ";
		}

		$sql .=	" ORDER BY price {$price_order_by}, size {$size_order_by}";

		$results = $wpdb->get_results( $sql, ARRAY_A );
		
		if ( ! empty( $results ) ) {
			return array( array_slice( $results, ( $page_num - 1 ) * $per_page, $per_page ), count( $results ), $max_price_val );
		}
		return array( false, 0, $max_price_val );
	}
}

/* Get booking default values */
if ( ! function_exists( 'lordcros_core_default_booking_data' ) ) {
	function lordcros_core_default_booking_data( $type = 'new' ) {
		$default_booking_data = array( 
			'first_name'			=> '',
			'last_name'				=> '',
			'email'					=> '',
			'phone'					=> '',
			'address1'				=> '',
			'address2'				=> '',
			'city'					=> '',
			'state'					=> '',
			'zip'					=> '',
			'country'				=> '',
			'arrival'				=> '',
			'special_requirements'	=> '',
			'post_id'				=> '',
			'date_from'				=> '',
			'date_to'				=> '',
			'adults'				=> 0,
			'kids'					=> 0,
			'extra_service'			=> '',
			'total_price'			=> '',
			'room_price'			=> 0,
			'service_price'			=> 0,
			'coupon_code'			=> '',
			'discounted_price'		=> 0,
			'payment'				=> '',
			'transaction_id'		=> '',
			'deposit_price'			=> 0,
			'deposit_paid'			=> 0,			
			'status'				=> 'new',
			'updated'				=> date( 'Y-m-d H:i:s' ),
		);

		if ( $type == 'new' ) {
			$temp = array( 
				'created'	=> date( 'Y-m-d H:i:s' ),
				'mail_sent'	=> '',
				'other'		=> '',
				'id'		=> '' 
			);

			if ( is_user_logged_in() ) {
				$temp['user_id'] = get_current_user_id();
			}

			$default_booking_data = array_merge( $default_booking_data, $temp );
		}

		return $default_booking_data;
	}
}

/* Get booking data for calendar view */
if ( ! function_exists( 'lordcros_core_get_calendar_view_booking_data' ) ) {
	function lordcros_core_get_calendar_view_booking_data( $args ) {
		global $wpdb;
		
		$post_table_name  = esc_sql( $wpdb->prefix . 'posts' );

		$where = "1=1";
		if ( ! empty( $args['post_id'] ) ) {
			$where .= " AND lordcros_booking.post_id = '" . esc_sql( lordcros_core_room_org_id( $args['post_id'] ) ) . "'";
		}
		if ( ! empty( $args['date_from'] ) &&  ! empty( $args['date_to'] ) ) {
			$where .= " AND ( ( lordcros_booking.date_from >= '" . esc_sql( $args['date_from'] ) . "' AND lordcros_booking.date_from <= '" . esc_sql( $args['date_to'] ) . "' ) OR ( lordcros_booking.date_to >= '" . esc_sql( $args['date_from'] ) . "' AND lordcros_booking.date_to <= '" . esc_sql( $args['date_to'] ) . "' ) ) ";
		}		
		if ( ! empty( $args['status'] ) ) {
			$where .= " AND lordcros_booking.status = '" . esc_sql( $args['status'] ) . "'";
		}

		$sql = $wpdb->prepare( 'SELECT lordcros_booking.* FROM %1$s as lordcros_booking
			WHERE ' . $where . ' ORDER BY lordcros_booking.post_id ', LORDCROS_ROOM_BOOKINGS_TABLE );

		return $wpdb->get_results( $sql, ARRAY_A );
	}
}

/* Check room availability */
if ( ! function_exists( 'lordcros_core_check_room_availability' ) ) {
	function lordcros_core_check_room_availability( $room_id, $date_from, $date_to, $adults = 1, $kids = 0 ) {
		global $wpdb;

		$room_id = lordcros_core_room_org_id( $room_id );
		$date_from = date( 'Y-m-d', strtotime( $date_from ) );
		$date_to = date( 'Y-m-d', strtotime( $date_to ) );

		$room_adults = rwmb_meta( 'lordcros_room_adults', '', $room_id );
		$room_kids = rwmb_meta( 'lordcros_room_children', '', $room_id );

		// check person availability
		if ( $adults > $room_adults || ( $adults + $kids ) > ( $room_adults + $room_kids ) ) {
			return esc_html__( 'Exceed maximum people.', 'lordcros-core' );
		}

		// check minimum stay
		$min_stay = rwmb_meta( 'lordcros_room_min_stay', '', $room_id );		
		$min_stay = intval( $min_stay );

		if ( ! strtotime( $date_from ) || ! strtotime( $date_to ) || ( strtotime( $date_from ) >= strtotime( $date_to ) ) || ( strtotime( $date_from .' + ' . $min_stay . ' days' ) > strtotime( $date_to) ) ) {
			return esc_html__( 'Wrong Booking Date. Please check again.', 'lordcros-core' );
		}

		$tbl_posts = esc_sql( $wpdb->posts );
		$tbl_postmeta = esc_sql( $wpdb->postmeta );
		$tbl_terms = esc_sql( $wpdb->prefix . 'terms' );
		$tbl_term_taxonomy = esc_sql( $wpdb->prefix . 'term_taxonomy' );
		$tbl_term_relationships = esc_sql( $wpdb->prefix . 'term_relationships' );

		$adults = esc_sql( $adults );
		$kids = esc_sql( $kids );
		$room_id = esc_sql( $room_id );

		$date_from_obj = new DateTime( $date_from );
		$date_to_obj = new DateTime( $date_to );
		$date_interval = DateInterval::createFromDateString( '1 day' );
		$period = new DatePeriod( $date_from_obj, $date_interval, $date_to_obj );
		$sql_check_date_parts = array();

		// get speciality post sql
		$speciality_post_sql = " SELECT speciality_posts.ID
							FROM {$tbl_posts} speciality_posts WHERE speciality_posts.post_type = 'speciality' AND post_status = 'publish' ";

		// get date block range sql
		$date_block_sql = " SELECT date_block_posts.ID AS date_block_post_id, meta_speciality_date_from.meta_value AS date_from, meta_speciality_date_to.meta_value AS date_to 
							FROM ( {$speciality_post_sql} ) AS date_block_posts 
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_type ON ( date_block_posts.ID = meta_speciality_type.post_id ) AND ( meta_speciality_type.meta_key = 'lordcros_speciality_type' )
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_date_from ON ( date_block_posts.ID = meta_speciality_date_from.post_id ) AND ( meta_speciality_date_from.meta_key = 'lordcros_speciality_date_from' )
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_date_to ON ( date_block_posts.ID = meta_speciality_date_to.post_id ) AND ( meta_speciality_date_to.meta_key = 'lordcros_speciality_date_to' ) 
							WHERE meta_speciality_type.meta_value = 'date_block' ";

		// get price special sql
		$price_special_sql = " SELECT price_speciality_posts.ID AS price_speciality_post_id, meta_speciality_date_from.meta_value AS date_from, meta_speciality_date_to.meta_value AS date_to, meta_speciality_price.meta_value AS special_price 
							FROM ( {$speciality_post_sql} ) AS price_speciality_posts 
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_type ON ( price_speciality_posts.ID = meta_speciality_type.post_id ) AND ( meta_speciality_type.meta_key = 'lordcros_speciality_type' )
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_date_from ON ( price_speciality_posts.ID = meta_speciality_date_from.post_id ) AND ( meta_speciality_date_from.meta_key = 'lordcros_speciality_date_from' )
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_date_to ON ( price_speciality_posts.ID = meta_speciality_date_to.post_id ) AND ( meta_speciality_date_to.meta_key = 'lordcros_speciality_date_to' ) 
							LEFT JOIN {$tbl_postmeta} AS meta_speciality_price ON ( price_speciality_posts.ID = meta_speciality_price.post_id ) AND ( meta_speciality_price.meta_key = 'lordcros_speciality_price' ) 
							WHERE meta_speciality_type.meta_value = 'price_variation' ";
		
		$sql = " SELECT {$room_id} AS room_id ";
		
		// check room availability and every night price with booking data and specialities
		$date_interval = DateInterval::createFromDateString( '1 day' );
		$period = new DatePeriod( $date_from_obj, $date_interval, $date_to_obj );
		$sql_check_date_parts = array();
		
		foreach ( $period as $dt ) {
			$check_date = $dt->format( "Y-m-d" );
			$sql_check_date_parts[] = " SELECT '{$check_date}' AS check_date ";
		}
		$sql_check_date = implode( ' UNION ', $sql_check_date_parts );
		
		$sql = " SELECT t1.room_id, check_dates.check_date, IFNULL( meta_qty.meta_value, 0 ) AS room_qty
					FROM ( {$sql} ) AS t1
					JOIN ( {$sql_check_date} ) AS check_dates
					LEFT JOIN {$tbl_postmeta} AS meta_qty ON ( t1.room_id = meta_qty.post_id ) AND ( meta_qty.meta_key = 'lordcros_room_qty' ) 
					GROUP BY t1.room_id, check_dates.check_date ";
		
		$sql2 = "SELECT post_id, check_date, COUNT(*) AS room_booked_qty
					FROM " . LORDCROS_ROOM_BOOKINGS_TABLE . " AS bookings
					JOIN ( {$sql_check_date} ) AS check_dates
					WHERE ( bookings.status != 'canceled' ) AND ( bookings.date_from <= check_dates.check_date ) AND ( bookings.date_to > check_dates.check_date ) 
					GROUP BY post_id, check_date";
		$sql = " SELECT ttt1.room_id, ttt1.check_date, ( ttt1.room_qty - IFNULL( ttt2.room_booked_qty, 0 ) ) AS available_room_qty
					FROM ( {$sql} ) AS ttt1
					LEFT JOIN ( {$sql2} ) AS ttt2
					ON ttt1.room_id = ttt2.post_id AND ttt1.check_date = ttt2.check_date";

		// combine date block range sql
		$sql = " SELECT t2.room_id, t2.check_date, IF( MAX( t3.date_block_post_id ) > 0, 0, t2.available_room_qty ) AS available_room_qty 
					FROM ( {$sql} ) AS t2
					LEFT JOIN {$tbl_postmeta} AS meta_room_date_block ON ( t2.room_id = meta_room_date_block.post_id ) AND ( meta_room_date_block.meta_key = 'lordcros_room_date_block' ) 
					LEFT JOIN ( {$date_block_sql} ) AS t3 ON ( meta_room_date_block.meta_value = t3.date_block_post_id AND t2.check_date <= t3.date_to AND t2.check_date >= t3.date_from ) 
					GROUP BY t2.room_id, t2.check_date ";
		$sql = " SELECT * FROM ( {$sql} ) AS tt1 WHERE tt1.room_id NOT IN ( SELECT DISTINCT tt2.room_id FROM ( {$sql} ) AS tt2 WHERE tt2.available_room_qty <= 0 ) ";

		// combine date special price
		$sql = " SELECT t4.room_id, t4.check_date, t4.available_room_qty, MAX( t5.special_price ) AS special_price
					FROM ( {$sql} ) AS t4
					LEFT JOIN {$tbl_postmeta} AS meta_room_date_price ON ( t4.room_id = meta_room_date_price.post_id ) AND ( meta_room_date_price.meta_key = 'lordcros_room_price_variation' ) 
					LEFT JOIN ( {$price_special_sql} ) AS t5 ON ( meta_room_date_price.meta_value = t5.price_speciality_post_id AND t4.check_date <= t5.date_to AND t4.check_date >= t5.date_from ) 
					GROUP BY t4.room_id, t4.check_date ";

		// get price based on special price and week day price and regular price
		$sql = " SELECT t6.room_id, t6.check_date, t6.available_room_qty, 
							CASE WHEN ! ( t6.special_price IS NULL OR t6.special_price = '' ) THEN t6.special_price
								WHEN WEEKDAY( t6.check_date ) = 0 AND ! ( meta_room_price_mon.meta_value IS NULL OR meta_room_price_mon.meta_value = '' ) THEN meta_room_price_mon.meta_value 
								WHEN WEEKDAY( t6.check_date ) = 1 AND ! ( meta_room_price_tue.meta_value IS NULL OR meta_room_price_tue.meta_value = '' ) THEN meta_room_price_tue.meta_value
								WHEN WEEKDAY( t6.check_date ) = 2 AND ! ( meta_room_price_wed.meta_value IS NULL OR meta_room_price_wed.meta_value = '' ) THEN meta_room_price_wed.meta_value
								WHEN WEEKDAY( t6.check_date ) = 3 AND ! ( meta_room_price_thu.meta_value IS NULL OR meta_room_price_thu.meta_value = '' ) THEN meta_room_price_thu.meta_value
								WHEN WEEKDAY( t6.check_date ) = 4 AND ! ( meta_room_price_fri.meta_value IS NULL OR meta_room_price_fri.meta_value = '' ) THEN meta_room_price_fri.meta_value
								WHEN WEEKDAY( t6.check_date ) = 5 AND ! ( meta_room_price_sat.meta_value IS NULL OR meta_room_price_sat.meta_value = '' ) THEN meta_room_price_sat.meta_value
								WHEN WEEKDAY( t6.check_date ) = 6 AND ! ( meta_room_price_sun.meta_value IS NULL OR meta_room_price_sun.meta_value = '' ) THEN meta_room_price_sun.meta_value
								ELSE meta_room_price.meta_value
							END AS price
					FROM ( {$sql} ) AS t6
					LEFT JOIN {$tbl_postmeta} AS meta_room_price ON ( t6.room_id = meta_room_price.post_id ) AND ( meta_room_price.meta_key = 'lordcros_room_price' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_mon ON ( t6.room_id = meta_room_price_mon.post_id ) AND ( meta_room_price_mon.meta_key = 'lordcros_room_price_mon' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_tue ON ( t6.room_id = meta_room_price_tue.post_id ) AND ( meta_room_price_tue.meta_key = 'lordcros_room_price_tue' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_wed ON ( t6.room_id = meta_room_price_wed.post_id ) AND ( meta_room_price_wed.meta_key = 'lordcros_room_price_wed' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_thu ON ( t6.room_id = meta_room_price_thu.post_id ) AND ( meta_room_price_thu.meta_key = 'lordcros_room_price_thu' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_fri ON ( t6.room_id = meta_room_price_fri.post_id ) AND ( meta_room_price_fri.meta_key = 'lordcros_room_price_fri' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_sat ON ( t6.room_id = meta_room_price_sat.post_id ) AND ( meta_room_price_sat.meta_key = 'lordcros_room_price_sat' ) 
					LEFT JOIN {$tbl_postmeta} AS meta_room_price_sun ON ( t6.room_id = meta_room_price_sun.post_id ) AND ( meta_room_price_sun.meta_key = 'lordcros_room_price_sun' ) ";

		$sql = " SELECT room_id, MIN( available_room_qty ) AS qty, SUM( price ) AS price FROM ( {$sql} ) AS t7 GROUP BY room_id ";

		$results = $wpdb->get_row( $sql, ARRAY_A );

		if ( ! empty( $results ) ) {
			return array( 'total_price' => $results['price'] );
		}

		return esc_html__( 'Another person booked this room.', 'lordcros-core' );
	}
}

/* Check room availability ajax function */
if ( ! function_exists( 'lordcros_core_ajax_room_check_availability' ) ) {
	function lordcros_core_ajax_room_check_availability() {
		check_ajax_referer( 'lordcros-ajax', 'security' );

		$room_id = $_POST['room_id'];
		if ( empty( $room_id ) ) {
			wp_send_json( array( 
				'success'	=> 0, 
				'message'	=> esc_html__( 'Please set room.', 'lordcros-core' )
			) );
		}

		$date_from = $_POST['date_from'];
		$date_to = $_POST['date_to'];
		$adults = $_POST['adults'];
		$kids = isset( $_POST['kids'] ) ? $_POST['kids'] : 0;

		$result = lordcros_core_check_room_availability( $room_id, $date_from, $date_to, $adults, $kids );

		if ( ! is_array( $result ) ) {
			wp_send_json( array( 
				'success'	=> 0, 
				'message'	=> $result,
			) );
		}

		wp_send_json( array( 
			'success'	=> 1, 
			'message'	=> $result,
		) );
	}
}

add_action( 'wp_ajax_room_check_availability', 'lordcros_core_ajax_room_check_availability' );
add_action( 'wp_ajax_nopriv_room_check_availability', 'lordcros_core_ajax_room_check_availability' );

/* Add room information to session */
if ( ! function_exists( 'lordcros_core_room_add_cart' ) ) {
	function lordcros_core_room_add_cart() {

		LordCros_Core_Room_Checkout_Info::set( array(
													'room_id'		=> $_POST['room_id'],
													'date_from'		=> $_POST['date_from'],
													'date_to'		=> $_POST['date_to'],
													'adults'		=> $_POST['adults'],
													'kids'			=> ( ! empty( $_POST['kids'] ) ) ? intval( $_POST['kids'] ) : 0,
													'room_price'	=> $_POST['room_price']
												) );
	}
}

add_action( 'lordcros_room_add_cart', 'lordcros_core_room_add_cart' );

/* Add checkout information to session */
if ( ! function_exists( 'lordcros_core_room_checkout' ) ) {
	function lordcros_core_room_checkout() {

		$booking_data = LordCros_Core_Room_Checkout_Info::get();

		$room_id = $booking_data['room_id'];
		$date_from = $booking_data['date_from'];
		$date_to = $booking_data['date_to'];
		$adults = $booking_data['adults'];
		$kids = $booking_data['kids'];

		$date_diff = date_diff( date_create( $date_to ), date_create( $date_from ) );
		$nights = $date_diff->format( '%a' );
		// Calculate service price
		$total_service_price = 0;

		if ( ! empty( $_POST['extra_service'] ) ) {
			foreach ( $_POST['extra_service'] as $e_service_id ) {
			
				$price_type1 = rwmb_meta( 'lordcros_room_service_price_type_1', '', $e_service_id );
				$price_type2 = rwmb_meta( 'lordcros_room_service_price_type_2', '', $e_service_id );
				$service_price = floatval( rwmb_meta( 'lordcros_room_service_price', '', $e_service_id ) );
				
				$calculated_service_price = $service_price;
				
				if ( $price_type1 == 'per_person' ) {
					$calculated_service_price *= $adults;
				}

				if ( $price_type2 == 'per_day' ) {
					$calculated_service_price *= $nights;
				}

				$total_service_price += $calculated_service_price;
			}
		}

		$total_price = LordCros_Core_Room_Checkout_Info::get_field( 'room_price' ) + $total_service_price;

		$discounted_price = $total_price;

		if ( ! empty( $_POST['coupon_code'] ) ) {
			$room_coupon_code = $_POST['coupon_code'];
			$room_coupon = get_page_by_title( $room_coupon_code, OBJECT, 'room_coupon' );

			if ( ! empty( $room_coupon ) ) {
				$room_coupon_id = $room_coupon->ID;
				
				$coupon_type = rwmb_meta( 'lordcros_room_coupon_type', '', $room_coupon_id );
				$coupon_amount = floatval( rwmb_meta( 'lordcros_room_coupon_amount', '', $room_coupon_id ) );

				if ( ! empty( $coupon_type ) && $coupon_type == 'percent' ) {
					$discounted_price *= ( 100 - $coupon_amount ) / 100;
				} else {
					$discounted_price -= $coupon_amount;
				}
			}
		}
		
		LordCros_Core_Room_Checkout_Info::set( array(
													'extra_service'			=> ( ! empty( $_POST['extra_service'] ) ) ? $_POST['extra_service'] : array(),
													'first_name'			=> $_POST['first_name'],
													'last_name'				=> $_POST['last_name'],
													'email'					=> $_POST['email'],
													'phone'					=> $_POST['phone'],
													'address'				=> $_POST['address'],
													'country'				=> $_POST['country'],
													'city'					=> $_POST['city'],
													'zip'					=> $_POST['zip'],
													'arrival'				=> $_POST['arrival'],
													'special_requirements'	=> $_POST['special_requirements'],
													'coupon_code'			=> $_POST['coupon_code'],
													'total_price'			=> $total_price,
													'service_price'			=> $total_service_price,
													'discounted_price'		=> $discounted_price,
												) );

	}
}

add_action( 'lordcros_room_checkout', 'lordcros_core_room_checkout' );

/* Get all countries */
if ( ! function_exists( 'lordcros_core_get_all_countries' ) ) {
	function lordcros_core_get_all_countries() {
		$countries = array(
			array( "code" => "US", "name" => "United States", "d_code" => "+1" ),
			array( "code" => "GB", "name" => "United Kingdom", "d_code" => "+44" ),
			array( "code" => "CA", "name" => "Canada", "d_code" => "+1" ),
			array( "code" => "AF", "name" => "Afghanistan", "d_code" => "+93" ),
			array( "code" => "AL", "name" => "Albania", "d_code" => "+355" ),
			array( "code" => "DZ", "name" => "Algeria", "d_code" => "+213" ),
			array( "code" => "AS", "name" => "American Samoa", "d_code" => "+1" ),
			array( "code" => "AD", "name" => "Andorra", "d_code" => "+376" ),
			array( "code" => "AO", "name" => "Angola", "d_code" => "+244" ),
			array( "code" => "AI", "name" => "Anguilla", "d_code" => "+1" ),
			array( "code" => "AG", "name" => "Antigua", "d_code" => "+1" ),
			array( "code" => "AR", "name" => "Argentina", "d_code" => "+54" ),
			array( "code" => "AM", "name" => "Armenia", "d_code" => "+374" ),
			array( "code" => "AW", "name" => "Aruba", "d_code" => "+297" ),
			array( "code" => "AU", "name" => "Australia", "d_code" => "+61" ),
			array( "code" => "AT", "name" => "Austria", "d_code" => "+43" ),
			array( "code" => "AZ", "name" => "Azerbaijan", "d_code" => "+994" ),
			array( "code" => "BH", "name" => "Bahrain", "d_code" => "+973" ),
			array( "code" => "BD", "name" => "Bangladesh", "d_code" => "+880" ),
			array( "code" => "BB", "name" => "Barbados", "d_code" => "+1" ),
			array( "code" => "BY", "name" => "Belarus", "d_code" => "+375" ),
			array( "code" => "BE", "name" => "Belgium", "d_code" => "+32" ),
			array( "code" => "BZ", "name" => "Belize", "d_code" => "+501" ),
			array( "code" => "BJ", "name" => "Benin", "d_code" => "+229" ),
			array( "code" => "BM", "name" => "Bermuda", "d_code" => "+1" ),
			array( "code" => "BT", "name" => "Bhutan", "d_code" => "+975" ),
			array( "code" => "BO", "name" => "Bolivia", "d_code" => "+591" ),
			array( "code" => "BA", "name" => "Bosnia and Herzegovina", "d_code" => "+387" ),
			array( "code" => "BW", "name" => "Botswana", "d_code" => "+267" ),
			array( "code" => "BR", "name" => "Brazil", "d_code" => "+55" ),
			array( "code" => "IO", "name" => "British Indian Ocean Territory", "d_code" => "+246" ),
			array( "code" => "VG", "name" => "British Virgin Islands", "d_code" => "+1" ),
			array( "code" => "BN", "name" => "Brunei", "d_code" => "+673" ),
			array( "code" => "BG", "name" => "Bulgaria", "d_code" => "+359" ),
			array( "code" => "BF", "name" => "Burkina Faso", "d_code" => "+226" ),
			array( "code" => "MM", "name" => "Burma Myanmar" ,"d_code" => "+95" ),
			array( "code" => "BI", "name" => "Burundi", "d_code" => "+257" ),
			array( "code" => "KH", "name" => "Cambodia", "d_code" => "+855" ),
			array( "code" => "CM", "name" => "Cameroon", "d_code" => "+237" ),
			array( "code" => "CV", "name" => "Cape Verde", "d_code" => "+238" ),
			array( "code" => "KY", "name" => "Cayman Islands", "d_code" => "+1" ),
			array( "code" => "CF", "name" => "Central African Republic", "d_code" => "+236" ),
			array( "code" => "TD", "name" => "Chad", "d_code" => "+235" ),
			array( "code" => "CL", "name" => "Chile", "d_code" => "+56" ),
			array( "code" => "CN", "name" => "China", "d_code" => "+86" ),
			array( "code" => "CO", "name" => "Colombia", "d_code" => "+57" ),
			array( "code" => "KM", "name" => "Comoros", "d_code" => "+269" ),
			array( "code" => "CK", "name" => "Cook Islands", "d_code" => "+682" ),
			array( "code" => "CR", "name" => "Costa Rica", "d_code" => "+506" ),
			array( "code" => "CI", "name" => "Cote d'Ivoire" ,"d_code" => "+225" ),
			array( "code" => "HR", "name" => "Croatia", "d_code" => "+385" ),
			array( "code" => "CU", "name" => "Cuba", "d_code" => "+53" ),
			array( "code" => "CY", "name" => "Cyprus", "d_code" => "+357" ),
			array( "code" => "CZ", "name" => "Czech Republic", "d_code" => "+420" ),
			array( "code" => "CD", "name" => "Democratic Republic of Congo", "d_code" => "+243" ),
			array( "code" => "DK", "name" => "Denmark", "d_code" => "+45" ),
			array( "code" => "DJ", "name" => "Djibouti", "d_code" => "+253" ),
			array( "code" => "DM", "name" => "Dominica", "d_code" => "+1" ),
			array( "code" => "DO", "name" => "Dominican Republic", "d_code" => "+1" ),
			array( "code" => "EC", "name" => "Ecuador", "d_code" => "+593" ),
			array( "code" => "EG", "name" => "Egypt", "d_code" => "+20" ),
			array( "code" => "SV", "name" => "El Salvador", "d_code" => "+503" ),
			array( "code" => "GQ", "name" => "Equatorial Guinea", "d_code" => "+240" ),
			array( "code" => "ER", "name" => "Eritrea", "d_code" => "+291" ),
			array( "code" => "EE", "name" => "Estonia", "d_code" => "+372" ),
			array( "code" => "ET", "name" => "Ethiopia", "d_code" => "+251" ),
			array( "code" => "FK", "name" => "Falkland Islands", "d_code" => "+500" ),
			array( "code" => "FO", "name" => "Faroe Islands", "d_code" => "+298" ),
			array( "code" => "FM", "name" => "Federated States of Micronesia", "d_code" => "+691" ),
			array( "code" => "FJ", "name" => "Fiji", "d_code" => "+679" ),
			array( "code" => "FI", "name" => "Finland", "d_code" => "+358" ),
			array( "code" => "FR", "name" => "France", "d_code" => "+33" ),
			array( "code" => "GF", "name" => "French Guiana", "d_code" => "+594" ),
			array( "code" => "PF", "name" => "French Polynesia", "d_code" => "+689" ),
			array( "code" => "GA", "name" => "Gabon", "d_code" => "+241" ),
			array( "code" => "GE", "name" => "Georgia", "d_code" => "+995" ),
			array( "code" => "DE", "name" => "Germany", "d_code" => "+49" ),
			array( "code" => "GH", "name" => "Ghana", "d_code" => "+233" ),
			array( "code" => "GI", "name" => "Gibraltar", "d_code" => "+350" ),
			array( "code" => "GR", "name" => "Greece", "d_code" => "+30" ),
			array( "code" => "GL", "name" => "Greenland", "d_code" => "+299" ),
			array( "code" => "GD", "name" => "Grenada", "d_code" => "+1" ),
			array( "code" => "GP", "name" => "Guadeloupe", "d_code" => "+590" ),
			array( "code" => "GU", "name" => "Guam", "d_code" => "+1" ),
			array( "code" => "GT", "name" => "Guatemala", "d_code" => "+502" ),
			array( "code" => "GN", "name" => "Guinea", "d_code" => "+224" ),
			array( "code" => "GW", "name" => "Guinea-Bissau", "d_code" => "+245" ),
			array( "code" => "GY", "name" => "Guyana", "d_code" => "+592" ),
			array( "code" => "HT", "name" => "Haiti", "d_code" => "+509" ),
			array( "code" => "HN", "name" => "Honduras", "d_code" => "+504" ),
			array( "code" => "HK", "name" => "Hong Kong", "d_code" => "+852" ),
			array( "code" => "HU", "name" => "Hungary", "d_code" => "+36" ),
			array( "code" => "IS", "name" => "Iceland", "d_code" => "+354" ),
			array( "code" => "IN", "name" => "India", "d_code" => "+91" ),
			array( "code" => "ID", "name" => "Indonesia", "d_code" => "+62" ),
			array( "code" => "IR", "name" => "Iran", "d_code" => "+98" ),
			array( "code" => "IQ", "name" => "Iraq", "d_code" => "+964" ),
			array( "code" => "IE", "name" => "Ireland", "d_code" => "+353" ),
			array( "code" => "IL", "name" => "Israel", "d_code" => "+972" ),
			array( "code" => "IT", "name" => "Italy", "d_code" => "+39" ),
			array( "code" => "JM", "name" => "Jamaica", "d_code" => "+1" ),
			array( "code" => "JP", "name" => "Japan", "d_code" => "+81" ),
			array( "code" => "JO", "name" => "Jordan", "d_code" => "+962" ),
			array( "code" => "KZ", "name" => "Kazakhstan", "d_code" => "+7" ),
			array( "code" => "KE", "name" => "Kenya", "d_code" => "+254" ),
			array( "code" => "KI", "name" => "Kiribati", "d_code" => "+686" ),
			array( "code" => "XK", "name" => "Kosovo", "d_code" => "+381" ),
			array( "code" => "KW", "name" => "Kuwait", "d_code" => "+965" ),
			array( "code" => "KG", "name" => "Kyrgyzstan", "d_code" => "+996" ),
			array( "code" => "LA", "name" => "Laos", "d_code" => "+856" ),
			array( "code" => "LV", "name" => "Latvia", "d_code" => "+371" ),
			array( "code" => "LB", "name" => "Lebanon", "d_code" => "+961" ),
			array( "code" => "LS", "name" => "Lesotho", "d_code" => "+266" ),
			array( "code" => "LR", "name" => "Liberia", "d_code" => "+231" ),
			array( "code" => "LY", "name" => "Libya", "d_code" => "+218" ),
			array( "code" => "LI", "name" => "Liechtenstein", "d_code" => "+423" ),
			array( "code" => "LT", "name" => "Lithuania", "d_code" => "+370" ),
			array( "code" => "LU", "name" => "Luxembourg", "d_code" => "+352" ),
			array( "code" => "MO", "name" => "Macau", "d_code" => "+853" ),
			array( "code" => "MK", "name" => "Macedonia", "d_code" => "+389" ),
			array( "code" => "MG", "name" => "Madagascar", "d_code" => "+261" ),
			array( "code" => "MW", "name" => "Malawi", "d_code" => "+265" ),
			array( "code" => "MY", "name" => "Malaysia", "d_code" => "+60" ),
			array( "code" => "MV", "name" => "Maldives", "d_code" => "+960" ),
			array( "code" => "ML", "name" => "Mali", "d_code" => "+223" ),
			array( "code" => "MT", "name" => "Malta", "d_code" => "+356" ),
			array( "code" => "MH", "name" => "Marshall Islands", "d_code" => "+692" ),
			array( "code" => "MQ", "name" => "Martinique", "d_code" => "+596" ),
			array( "code" => "MR", "name" => "Mauritania", "d_code" => "+222" ),
			array( "code" => "MU", "name" => "Mauritius", "d_code" => "+230" ),
			array( "code" => "YT", "name" => "Mayotte", "d_code" => "+262" ),
			array( "code" => "MX", "name" => "Mexico", "d_code" => "+52" ),
			array( "code" => "MD", "name" => "Moldova", "d_code" => "+373" ),
			array( "code" => "MC", "name" => "Monaco", "d_code" => "+377" ),
			array( "code" => "MN", "name" => "Mongolia", "d_code" => "+976" ),
			array( "code" => "ME", "name" => "Montenegro", "d_code" => "+382" ),
			array( "code" => "MS", "name" => "Montserrat", "d_code" => "+1" ),
			array( "code" => "MA", "name" => "Morocco", "d_code" => "+212" ),
			array( "code" => "MZ", "name" => "Mozambique", "d_code" => "+258" ),
			array( "code" => "NA", "name" => "Namibia", "d_code" => "+264" ),
			array( "code" => "NR", "name" => "Nauru", "d_code" => "+674" ),
			array( "code" => "NP", "name" => "Nepal", "d_code" => "+977" ),
			array( "code" => "NL", "name" => "Netherlands", "d_code" => "+31" ),
			array( "code" => "AN", "name" => "Netherlands Antilles", "d_code" => "+599" ),
			array( "code" => "NC", "name" => "New Caledonia", "d_code" => "+687" ),
			array( "code" => "NZ", "name" => "New Zealand", "d_code" => "+64" ),
			array( "code" => "NI", "name" => "Nicaragua", "d_code" => "+505" ),
			array( "code" => "NE", "name" => "Niger", "d_code" => "+227" ),
			array( "code" => "NG", "name" => "Nigeria", "d_code" => "+234" ),
			array( "code" => "NU", "name" => "Niue", "d_code" => "+683" ),
			array( "code" => "NF", "name" => "Norfolk Island", "d_code" => "+672" ),
			array( "code" => "KP", "name" => "North Korea", "d_code" => "+850" ),
			array( "code" => "MP", "name" => "Northern Mariana Islands", "d_code" => "+1" ),
			array( "code" => "NO", "name" => "Norway", "d_code" => "+47" ),
			array( "code" => "OM", "name" => "Oman", "d_code" => "+968" ),
			array( "code" => "PK", "name" => "Pakistan", "d_code" => "+92" ),
			array( "code" => "PW", "name" => "Palau", "d_code" => "+680" ),
			array( "code" => "PS", "name" => "Palestine", "d_code" => "+970" ),
			array( "code" => "PA", "name" => "Panama", "d_code" => "+507" ),
			array( "code" => "PG", "name" => "Papua New Guinea", "d_code" => "+675" ),
			array( "code" => "PY", "name" => "Paraguay", "d_code" => "+595" ),
			array( "code" => "PE", "name" => "Peru", "d_code" => "+51" ),
			array( "code" => "PH", "name" => "Philippines", "d_code" => "+63" ),
			array( "code" => "PL", "name" => "Poland", "d_code" => "+48" ),
			array( "code" => "PT", "name" => "Portugal", "d_code" => "+351" ),
			array( "code" => "PR", "name" => "Puerto Rico", "d_code" => "+1" ),
			array( "code" => "QA", "name" => "Qatar", "d_code" => "+974" ),
			array( "code" => "CG", "name" => "Republic of the Congo", "d_code" => "+242" ),
			array( "code" => "RE", "name" => "Reunion" ,"d_code" => "+262" ),
			array( "code" => "RO", "name" => "Romania", "d_code" => "+40" ),
			array( "code" => "RU", "name" => "Russia", "d_code" => "+7" ),
			array( "code" => "RW", "name" => "Rwanda", "d_code" => "+250" ),
			array( "code" => "BL", "name" => "Saint Barthelemy" ,"d_code" => "+590" ),
			array( "code" => "SH", "name" => "Saint Helena", "d_code" => "+290" ),
			array( "code" => "KN", "name" => "Saint Kitts and Nevis", "d_code" => "+1" ),
			array( "code" => "MF", "name" => "Saint Martin", "d_code" => "+590" ),
			array( "code" => "PM", "name" => "Saint Pierre and Miquelon", "d_code" => "+508" ),
			array( "code" => "VC", "name" => "Saint Vincent and the Grenadines", "d_code" => "+1" ),
			array( "code" => "WS", "name" => "Samoa", "d_code" => "+685" ),
			array( "code" => "SM", "name" => "San Marino", "d_code" => "+378" ),
			array( "code" => "ST", "name" => "Sao Tome and Principe" ,"d_code" => "+239" ),
			array( "code" => "SA", "name" => "Saudi Arabia", "d_code" => "+966" ),
			array( "code" => "SN", "name" => "Senegal", "d_code" => "+221" ),
			array( "code" => "RS", "name" => "Serbia", "d_code" => "+381" ),
			array( "code" => "SC", "name" => "Seychelles", "d_code" => "+248" ),
			array( "code" => "SL", "name" => "Sierra Leone", "d_code" => "+232" ),
			array( "code" => "SG", "name" => "Singapore", "d_code" => "+65" ),
			array( "code" => "SK", "name" => "Slovakia", "d_code" => "+421" ),
			array( "code" => "SI", "name" => "Slovenia", "d_code" => "+386" ),
			array( "code" => "SB", "name" => "Solomon Islands", "d_code" => "+677" ),
			array( "code" => "SO", "name" => "Somalia", "d_code" => "+252" ),
			array( "code" => "ZA", "name" => "South Africa", "d_code" => "+27" ),
			array( "code" => "KR", "name" => "South Korea", "d_code" => "+82" ),
			array( "code" => "ES", "name" => "Spain", "d_code" => "+34" ),
			array( "code" => "LK", "name" => "Sri Lanka", "d_code" => "+94" ),
			array( "code" => "LC", "name" => "St. Lucia", "d_code" => "+1" ),
			array( "code" => "SD", "name" => "Sudan", "d_code" => "+249" ),
			array( "code" => "SR", "name" => "Suriname", "d_code" => "+597" ),
			array( "code" => "SZ", "name" => "Swaziland", "d_code" => "+268" ),
			array( "code" => "SE", "name" => "Sweden", "d_code" => "+46" ),
			array( "code" => "CH", "name" => "Switzerland", "d_code" => "+41" ),
			array( "code" => "SY", "name" => "Syria", "d_code" => "+963" ),
			array( "code" => "TW", "name" => "Taiwan", "d_code" => "+886" ),
			array( "code" => "TJ", "name" => "Tajikistan", "d_code" => "+992" ),
			array( "code" => "TZ", "name" => "Tanzania", "d_code" => "+255" ),
			array( "code" => "TH", "name" => "Thailand", "d_code" => "+66" ),
			array( "code" => "BS", "name" => "The Bahamas", "d_code" => "+1" ),
			array( "code" => "GM", "name" => "The Gambia", "d_code" => "+220" ),
			array( "code" => "TL", "name" => "Timor-Leste", "d_code" => "+670" ),
			array( "code" => "TG", "name" => "Togo", "d_code" => "+228" ),
			array( "code" => "TK", "name" => "Tokelau", "d_code" => "+690" ),
			array( "code" => "TO", "name" => "Tonga", "d_code" => "+676" ),
			array( "code" => "TT", "name" => "Trinidad and Tobago", "d_code" => "+1" ),
			array( "code" => "TN", "name" => "Tunisia", "d_code" => "+216" ),
			array( "code" => "TR", "name" => "Turkey", "d_code" => "+90" ),
			array( "code" => "TM", "name" => "Turkmenistan", "d_code" => "+993" ),
			array( "code" => "TC", "name" => "Turks and Caicos Islands", "d_code" => "+1" ),
			array( "code" => "TV", "name" => "Tuvalu", "d_code" => "+688" ),
			array( "code" => "UG", "name" => "Uganda", "d_code" => "+256" ),
			array( "code" => "UA", "name" => "Ukraine", "d_code" => "+380" ),
			array( "code" => "AE", "name" => "United Arab Emirates", "d_code" => "+971" ),
			array( "code" => "UY", "name" => "Uruguay", "d_code" => "+598" ),
			array( "code" => "VI", "name" => "US Virgin Islands", "d_code" => "+1" ),
			array( "code" => "UZ", "name" => "Uzbekistan", "d_code" => "+998" ),
			array( "code" => "VU", "name" => "Vanuatu", "d_code" => "+678" ),
			array( "code" => "VA", "name" => "Vatican City", "d_code" => "+39" ),
			array( "code" => "VE", "name" => "Venezuela", "d_code" => "+58" ),
			array( "code" => "VN", "name" => "Vietnam", "d_code" => "+84" ),
			array( "code" => "WF", "name" => "Wallis and Futuna", "d_code" => "+681" ),
			array( "code" => "YE", "name" => "Yemen", "d_code" => "+967" ),
			array( "code" => "ZM", "name" => "Zambia", "d_code" => "+260" ),
			array( "code" => "ZW", "name" => "Zimbabwe", "d_code" => "+263" ),
		);
		return $countries;
	}
}

/* Check room coupon code ajax function */
if ( ! function_exists( 'lordcros_core_ajax_check_room_coupon_code' ) ) {
	function lordcros_core_ajax_check_room_coupon_code() {
		check_ajax_referer( 'lordcros-ajax', 'security' );

		$room_coupon_code = $_POST['coupon_code'];
		if ( empty( $room_coupon_code ) ) {
			wp_send_json( array( 
				'success'	=> 0, 
				'message'	=> esc_html__( 'Please set coupon code.', 'lordcros-core' ),
			) );
		}

		$result = lordcros_core_check_room_coupon_code_availability( $room_coupon_code, $_SESSION['lordcros_booking_room_data']['room_id'] );

		if ( $result ) {
			wp_send_json( array( 
				'success'	=> 1, 
				'message'	=> $result,
			) );
		}

		wp_send_json( array( 
			'success'	=> 0, 
			'message'	=> esc_html__( 'Coupon code is not valid.', 'lordcros-core' )
		) );
	}
}
add_action( 'wp_ajax_check_room_coupon_code', 'lordcros_core_ajax_check_room_coupon_code' );
add_action( 'wp_ajax_nopriv_check_room_coupon_code', 'lordcros_core_ajax_check_room_coupon_code' );

if ( ! function_exists( 'lordcros_core_check_room_coupon_code_availability' ) ) {
	function lordcros_core_check_room_coupon_code_availability( $room_coupon_code, $room_id ) {
		
		$room_coupon = get_page_by_title( $room_coupon_code, OBJECT, 'room_coupon' );

		if ( empty( $room_coupon ) ) {
			return false;
		}
		if ( $room_coupon->post_status != 'publish' ) {
			return false;
		}

		$room_coupon_id = $room_coupon->ID;
		$expiry_date = rwmb_meta( 'lordcros_room_coupon_expiry_date', '', $room_coupon_id );
		if ( strtotime( $expiry_date ) < strtotime( "now" ) ) {
			return false;
		}

		$room_restriction = rwmb_meta( 'lordcros_room_coupon_room_restriction', '', $room_coupon_id );
		if ( ! empty( $room_restriction ) && ! in_array( $room_id, $room_restriction ) ) {
			return false;
		}

		//check coupon code usage
		$usage_limit = rwmb_meta( 'lordcros_room_coupon_usage_limit', '', $room_coupon_id );
		if ( isset( $usage_limit ) && $usage_limit != "" ) {
			global $wpdb;

			$sql = " SELECT count(*) AS count FROM " . LORDCROS_ROOM_BOOKINGS_TABLE . " WHERE coupon_code = %s AND status != 'canceled'";
			$sql = $wpdb->prepare( $sql, $room_coupon_code );
			$count = $wpdb->get_var( $sql );
			
			if ( intval( $usage_limit ) <= intval( $count ) ) {
				return false;
			}
		}

		return true;
	}
}

/* Add booking function */
if ( ! function_exists( 'lordcros_core_room_add_booking' ) ) {
	function lordcros_core_room_add_booking() {

		global $wpdb;

		$default_booking_data = lordcros_core_default_booking_data( 'new' );
		
		$booking_data = array();
		foreach ( $default_booking_data as $table_field => $def_value ) {
			$value = LordCros_Core_Room_Checkout_Info::get_field( $table_field );
			if ( $value ) {
				if ( ! is_array( $value ) ) {
					$booking_data[ $table_field ] = sanitize_text_field( $value );
				} else {
					$booking_data[ $table_field ] = serialize( $value );
				}
			} elseif ( $table_field == 'address1' && LordCros_Core_Room_Checkout_Info::get_field( 'address' ) ) {
				$booking_data[ $table_field ] = LordCros_Core_Room_Checkout_Info::get_field( 'address' );
			} elseif ( $table_field == 'post_id' && LordCros_Core_Room_Checkout_Info::get_field( 'room_id' ) ) {
				$booking_data[ $table_field ] = LordCros_Core_Room_Checkout_Info::get_field( 'room_id' );
			} else {
				$booking_data[ $table_field ] = $def_value;
			}

			if ( $table_field == 'date_from' || $table_field == 'date_to' ) {
				$booking_data[ $table_field ] = date( 'Y-m-d', strtotime( $booking_data[ $table_field ] ) );
			}
		}

		if ( $wpdb->insert( LORDCROS_ROOM_BOOKINGS_TABLE, $booking_data ) ) {
			$order_id = $wpdb->insert_id;
			do_action( 'lordcros_room_send_confirmation_email', $order_id );
			LordCros_Core_Room_Checkout_Info::_unset();
		}
	}

	add_action( 'lordcros_room_add_booking', 'lordcros_core_room_add_booking' );
}

if ( ! function_exists( 'lordcros_core_room_send_confirmation_email' ) ) {

	function lordcros_core_room_send_confirmation_email( $order_id ) {
		global $wpdb;

		$order = new LordCros_Core_Room_Booking( $order_id );
		$order_data = $order->get_booking_info();
		
		if ( empty( $order_data ) ) {
			return false;
		}

		// server variables
		$admin_email = get_option( 'admin_email' );
		$home_url = esc_url( home_url( '/' ) );
		$site_name = filter_input( INPUT_SERVER, 'SERVER_NAME' );

		$logo_uploaded = lordcros_get_opt( 'alternative_logo' );

		if ( isset( $logo_uploaded['url'] ) ) {
			$logo_url = $logo_uploaded['url'];
		} else {
			$logo_url = '';
		}

		$order_data['room_id'] = lordcros_core_room_clang_id( $order_data['post_id'] );

		// room info
		$room_name = get_the_title( $order_data['room_id'] );
		$room_url = esc_url( lordcros_core_get_permalink_clang( $order_data['room_id'] ) );
		$room_thumbnail = get_the_post_thumbnail( $order_data['room_id'], 'medium' );
		$address = lordcros_get_opt( 'address' );
		$phone = lordcros_get_opt( 'phone_num_val' );
		$email = lordcros_get_opt( 'email_address' );
		
		// booking info
		$date_from = new DateTime( $order_data['date_from'] );
		$date_to = new DateTime( $order_data['date_to'] );
		$number1 = $date_from->format( 'U' );
		$number2 = $date_to->format( 'U' );
		$booking_nights = ( $number2 - $number1 ) / ( 3600 * 24 );
		$from_date = date_i18n( 'j F Y', strtotime( $order_data['date_from'] ) );
		$to_date = date_i18n( 'j F Y', strtotime( $order_data['date_to'] ) );
		$adults = $order_data['adults'];
		$kids = $order_data['kids'];
		$total_price = lordcros_price( $order_data['total_price'] );
		$discounted_price = lordcros_price( $order_data['discounted_price'] );
		$room_price = lordcros_price( $order_data['room_price'] );
		$service_price = lordcros_price( $order_data['service_price'] );
		$coupon_code = $order_data['coupon_code'];
		$booking_payment_type = $order_data['payment'];
		$statuses = array( 'inquiry' => esc_html__( 'Just Request', 'lordcros-core' ), 'paypal' => esc_html__( 'Paypal', 'lordcros-core' ), 'stripe' => esc_html__( 'Stripe', 'lordcros-core' ) );
		if ( isset( $statuses[$order_data['payment']] ) ) {
			$booking_payment_type = $statuses[$order_data['payment']];
		}
		$transaction_id = $order_data['transaction_id'];
		$booking_status = $order_data['status'];
		
		$extra_service = '';
		if ( ! empty( $order_data['extra_service'] ) ) {
			$booked_extra_services = unserialize( $order_data['extra_service'] );
			$args = array(
					'posts_per_page'	=> -1,
					'post_type'			=> 'room_service',
					'post_status'		=> 'publish',
					'post__in'			=> $booked_extra_services
				);
			$extra_services = get_posts( $args );
			
			$booked_extra_services = array();			
			if ( ! empty( $extra_services ) ) {
				foreach ( $extra_services as $e_service ) {
					$booked_extra_services[] = $e_service->post_title;
				}
			}
			$extra_service = implode( ', ', $booked_extra_services );
		}	

		// customer info
		$customer_first_name = $order_data['first_name'];
		$customer_last_name = $order_data['last_name'];
		$customer_email = $order_data['email'];
		$customer_country_code = $order_data['country'];
		$customer_phone = $order_data['phone'];
		$customer_address1 = $order_data['address1'];
		$customer_address2 = $order_data['address2'];
		$customer_city = $order_data['city'];
		$customer_zip = $order_data['zip'];
		$customer_country = $order_data['country'];
		$arrival = $order_data['arrival'];
		$customer_special_requirements = $order_data['special_requirements'];

		$variables = array( 
			'home_url',
			'site_name',
			'logo_url',
			'room_name',
			'room_url',
			'room_thumbnail',
			'address',
			'email',
			'phone',
			'from_date',
			'to_date',
			'booking_nights',
			'adults',
			'kids',
			'total_price',
			'discounted_price',
			'room_price',
			'service_price',
			'coupon_code',
			'booking_payment_type',
			'transaction_id',
			'extra_service',
			'booking_status',
			'customer_first_name',
			'customer_last_name',
			'customer_email',
			'customer_country_code',
			'customer_phone',
			'customer_address1',
			'customer_address2',
			'customer_city',
			'customer_zip',
			'customer_country',
			'customer_special_requirements',
		);

		/* mailing function to customer */
		if ( ! empty( lordcros_get_opt( 'room_booked_notify_customer' ) ) ) {
			if ( empty( $subject ) ) {
				$subject = empty( lordcros_get_opt( 'room_confirm_email_subject' ) ) ? 'Booking Confirmation Email Subject' : lordcros_get_opt( 'room_confirm_email_subject' );
			}

			if ( empty( $description ) ) {
				$description = empty( lordcros_get_opt( 'room_confirm_email_description' ) ) ? 'Booking Confirmation Email Description' : lordcros_get_opt( 'room_confirm_email_description' );
			}

			foreach ( $variables as $variable ) {
				$subject = str_replace( "[" . $variable . "]", $$variable, $subject );
				$description = str_replace( "[" . $variable . "]", $$variable, $description );
			}

			$mail_sent = lordcros_core_send_mail( $site_name, $admin_email, $customer_email, $subject, $description );
		}

		/* mailing function to admin */
		if ( ! empty( lordcros_get_opt( 'room_booked_notify_admin' ) ) ) {
			$subject = empty( lordcros_get_opt( 'room_admin_email_subject' ) ) ? 'You received a booking' : lordcros_get_opt( 'room_admin_email_subject' );
			$description = empty( lordcros_get_opt( 'room_admin_email_description' ) ) ? 'Booking Details' : lordcros_get_opt( 'room_admin_email_description' );

			foreach ( $variables as $variable ) {
				$subject = str_replace( "[" . $variable . "]", $$variable, $subject );
				$description = str_replace( "[" . $variable . "]", $$variable, $description );
			}

			lordcros_core_send_mail( $site_name, $admin_email, $admin_email, $subject, $description );
		}

		return true;
	}

	add_action( 'lordcros_room_send_confirmation_email', 'lordcros_core_room_send_confirmation_email' );	
}

/* send mail functions */
if ( ! function_exists('lordcros_core_send_mail') ) {
	function lordcros_core_send_mail( $from_name, $from_address, $to_address, $subject, $description ) {
		//Create Email Headers
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= "From: ".$from_name." <".$from_address.">\n";
		$headers .= "Reply-To: ".$from_name." <".$from_address.">\n";
		$message = "<html>\n";
		$message .= "<body>\n";
		$message .= $description;
		$message .= "</body>\n";
		$message .= "</html>\n";
		$mailsent = wp_mail( $to_address, $subject, $message, $headers );
		return ( $mailsent ) ? true : false;
	}
}

/* Get rooms from ids */
if ( ! function_exists( 'lordcros_core_get_rooms_from_id' ) ) {
	function lordcros_core_get_rooms_from_id( $ids ) {
		if ( ! is_array( $ids ) ) {
			return false;
		}

		$args = array( 'post__in' => $ids, 'post_type' => 'room', 'posts_per_page' => -1 );
		$results = get_posts( $args );
		return $results;
	}
}

/* Get special( latest, featured ) rooms */
if ( ! function_exists( 'lordcros_core_get_special_rooms' ) ) {
	function lordcros_core_get_special_rooms( $type = 'latest', $count = 3 ) {
		$args = array(
				'post_type'			=> 'room',
				'suppress_filters'	=> 0,
				'posts_per_page'	=> $count,
				'post_status'		=> 'publish',
			);

		if ( $type == 'featured'  ) {
			$args = array_merge( $args, array(
				'orderby'		=> 'rand',
				'meta_key'		=> 'lordcros_room_featured',
				'meta_value'	=> '1',
			) );
		} else {
			$args = array_merge( $args, array(
				'orderby'	=> 'post_date',
				'order'		=> 'DESC',
			) );
		}

		return get_posts( $args );
		
	}
}

/* Add/Update speciality */
if ( ! function_exists( 'lordcros_core_add_speciality' ) ) {
	function lordcros_core_add_speciality( $price, $date_from, $date_to, $available ) {
		
		$speciality_id = 0;
		$speciality_title = $available . '|' . $date_from . '|' . $date_to . '|' . $price;
		$speciality = get_page_by_title( $speciality_title, ARRAY_A, 'speciality' );
				
		if ( ! empty( $speciality ) ) {
			$speciality_id = $speciality['ID'];
		}

		$speciality_type = 'date_block';

		if ( $available == 'available' ) {
			$speciality_type = 'price_variation';
		}
		
		$speciality_arr = array(
								'ID'			=> $speciality_id,
								'post_title'	=> $speciality_title,
								'post_status'	=> 'publish',
								'post_type'		=> 'speciality',
								'meta_input'	=> array(
														'lordcros_speciality_type'		=> $speciality_type,
														'lordcros_speciality_date_from'	=> $date_from,
														'lordcros_speciality_date_to'	=> $date_to,
														'lordcros_speciality_price'		=> $price,
													),
							);
		$speciality_id = wp_insert_post( $speciality_arr );
		
		return array( $speciality_id, $speciality_title);
	}
}

/* Get booking count */
if ( ! function_exists( 'lordcros_core_get_booking_count' ) ) {
	function lordcros_core_get_booking_count( $user_id = 0, $status = '' ) {

		global $wpdb;

		$where = ' WHERE 1=1';
		
		if ( ! empty( $user_id ) ) {
			$where .= ' AND user_id=' . esc_sql( $user_id );
		}

		if ( ! empty( $status ) ) {
			$where .= ' AND status="' . esc_sql( $status ) . '"';
		}

		$sql = "SELECT COUNT(*) FROM " . LORDCROS_ROOM_BOOKINGS_TABLE . $where;
		$result = $wpdb->get_var( $sql );

		return $result;
	}
}

/* Get user booking list function */
if ( ! function_exists( 'lordcros_core_get_user_booking_list' ) ) {
	function lordcros_core_get_user_booking_list( $user_id, $status = '', $sortby = 'created', $order = 'desc' ) {

		global $wpdb;

		$order = ( $order == 'desc' ) ? 'desc' : 'asc';
		$order_by = ' ORDER BY ' . esc_sql( $sortby ) . ' ' . $order;
		$where = ' WHERE 1=1';
		$where .= ' AND user_id=' . esc_sql( $user_id );
		if ( ! empty( $status ) ) {
			$where .= ' AND status= "' . esc_sql( $status ) . '"';
		}
		
		$sql = "SELECT id, total_price, created, status, post_id, date_from, date_to, adults FROM " . LORDCROS_ROOM_BOOKINGS_TABLE . $where;
		$sql .= $order_by;
		//return $sql;

		$booking_list = $wpdb->get_results( $sql );

		if ( empty( $booking_list ) ) {
			return '<span class="empty-list">' . esc_html__( 'You don\'t have any booked trips yet.', 'lordcros-core' ) . '</span>'; // if empty return false
		}

		$html = '';
		foreach ( $booking_list as $booking_data ) {
			$class = '';
			$label = esc_html__( 'UPCOMING', 'lordcros-core' );
			if ( $booking_data->status == 'canceled' ) {
				$class = ' canceled';
				$label = esc_html__( 'CANCELED', 'lordcros-core' );
			}
			if ( $booking_data->status == 'confirmed' ) {
				$class = ' completed';
				$label = esc_html__( 'COMPLETED', 'lordcros-core' );
			}
			
			$html .= '<div class="booking-info-list' . $class . '">';
			$html .= '<div class="date">
							<span class="month">' . date( 'M', strtotime( $booking_data->date_from ) ) . '</span>
							<span class="date">' . date( 'd', strtotime( $booking_data->date_from ) ) . '</span>
							<span class="day">' . date( 'D', strtotime( $booking_data->date_from ) ) . '</span>
						</div>';
			$html .= '<h4 class="box-title">';
			$html .= '<a href="' . get_permalink( $booking_data->post_id ) . '">' . get_the_title( $booking_data->post_id ) . '</a>';
			$html .= $booking_data->adults . ' ' . esc_html__( 'adults', 'lordcros-core' );
			$html .= '</h4>';
			$html .= '<div class="book-date-info">';
			$html .= '<span class="info-label">' . esc_html__( 'Booked on', 'lordcros-core') . '</span>';
			$html .= '<span class="info-val">' . date( 'l, M, j, Y', strtotime( $booking_data->created ) ) . '</span>';
			$html .= '</div>';
			$html .= '<button class="booking-status-btn">' . esc_html( $label ) . '</button>';
			$html .= '</div>';
		}
		return $html;
	}

	add_filter( 'lordcros_user_booking_list', 'lordcros_core_get_user_booking_list', 10, 3 );
}

/* Handle booking list filter and sorting action. */
if ( ! function_exists( 'lordcros_core_ajax_update_booking_list' ) ) {
	function lordcros_core_ajax_update_booking_list() {
		$result_json = array();
		$user_id = get_current_user_id();
		$status = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : "";
		$sortby = isset( $_POST['sort_by'] ) ? sanitize_text_field( $_POST['sort_by'] ) : 'created';
		$order = isset( $_POST['order'] ) ? sanitize_text_field( $_POST['order'] ) : 'desc';
		$booking_list = lordcros_core_get_user_booking_list( $user_id, $status, $sortby, $order );
		if ( ! empty( $booking_list ) ) {
			$result_json['success'] = 1;
			$result_json['result'] = $booking_list;
			wp_send_json( $result_json );
		} else {
			$result_json['success'] = 0;
			$result_json['result'] = esc_html__( 'empty', 'lordcros-core' );
			wp_send_json( $result_json );
		}
	}

	add_action( 'wp_ajax_update_booking_list', 'lordcros_core_ajax_update_booking_list' );
	add_action( 'wp_ajax_nopriv_update_booking_list', 'lordcros_core_ajax_update_booking_list' );
}
