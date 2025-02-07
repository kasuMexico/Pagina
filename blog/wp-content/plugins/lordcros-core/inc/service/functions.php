<?php
/*
 * functions releted rooms
 */

defined( 'ABSPATH' ) || exit;

/* Get services from ids */
if ( ! function_exists( 'lordcros_core_get_services_from_id' ) ) {
	function lordcros_core_get_services_from_id( $ids ) {
		if ( ! is_array( $ids ) ) {
			return false;
		}

		$args = array( 'post__in' => $ids, 'post_type' => 'service', 'fields' => 'ids' );
		$results = get_posts( $args );
		return $results;
	}
}

/* Get special( latest, featured ) services */
if ( ! function_exists( 'lordcros_core_get_special_services' ) ) {
	function lordcros_core_get_special_services( $type = 'latest', $count = 3 ) {
		$args = array(
				'fields'			=> 'ids',
				'post_type'			=> 'service',
				'suppress_filters'	=> 0,
				'posts_per_page'	=> $count,
				'post_status'		=> 'publish',
			);

		if ( $type == 'featured'  ) {
			$args = array_merge( $args, array(
				'orderby'		=> 'rand',
				'meta_key'		=> 'lordcros_service_featured',
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