<?php
/**
 *	Functions related Js Composer
 */

if ( !defined('ABSPATH') ) {
	exit();
}

/* Suggestor for "Room IDs" field */
if ( ! function_exists( 'lordcros_core_room_ids_autocomplete_suggestor' ) ) { 
	function lordcros_core_room_ids_autocomplete_suggestor( $query, $tag, $param_name ) { 
		global $wpdb;

		$room_id = (int) $query;
		$room_infos = $wpdb->get_results( $wpdb->prepare( "SELECT a.ID AS id, a.post_title AS title
			FROM {$wpdb->posts} AS a
			WHERE a.post_type = 'room' AND a.post_status = 'publish' AND ( a.ID = '%d' OR a.post_title LIKE '%%%s%%' )", $room_id > 0 ? $room_id : - 1, stripslashes( $query ) ), ARRAY_A );

		$results = array();
		if ( is_array( $room_infos ) && ! empty( $room_infos ) ) {
			foreach ( $room_infos as $value ) {
				$data = array();

				$data['value'] = $value['id'];
				$data['label'] = esc_html__( 'Id', 'lordcros-core' ) . ': ' . $value['id'] . ( ( strlen( $value['title'] ) > 0 ) ? ' - ' . esc_html__( 'Title', 'lordcros-core' ) . ': ' . $value['title'] : '' );

				$results[] = $data;
			}
		}

		return $results;
	}
}

/*
 * Renderer for "Room IDs" field
 */
if ( ! function_exists( 'lordcros_core_room_ids_autocomplete_render' ) ) { 
	function lordcros_core_room_ids_autocomplete_render( $query ) { 
		$query = trim( $query['value'] ); // get value from requested

		if ( ! empty( $query ) ) {
			// get room
			$room_object = get_post( (int) $query );

			if ( is_object( $room_object ) ) {
				$room_title = $room_object->post_title;
				$room_id = $room_object->ID;

				$room_title_display = '';
				if ( ! empty( $room_title ) ) {
					$room_title_display = ' - ' . esc_html__( 'Title', 'lordcros-core' ) . ': ' . $room_title;
				}

				$room_id_display = esc_html__( 'Id', 'lordcros-core' ) . ': ' . $room_id;

				$data = array();
				$data['value'] = $room_id;
				$data['label'] = $room_id_display . $room_title_display;

				return $data;
			}

			return false;
		}

		return false;
	}
}

// "Room IDs" field in "Rooms" Visual Composer element
add_filter( 'vc_autocomplete_lc_rooms_post_ids_callback', 'lordcros_core_room_ids_autocomplete_suggestor', 10, 3 );
add_filter( 'vc_autocomplete_lc_rooms_post_ids_render', 'lordcros_core_room_ids_autocomplete_render', 10 );

/* LordCros WPBakery Image Selection param type */
if( ! function_exists( 'lordcros_core_image_selection_type' ) && function_exists( 'vc_add_shortcode_param' ) ) {
	function lordcros_core_image_selection_type( $settings, $value ) {
		$settings_value = array_flip( $settings['value'] );
        $uniqid = uniqid();

		ob_start();
		?>
			<input type="hidden" id="input-<?php echo esc_attr( $uniqid ); ?>" class="lordcros-image-selection-input wpb_vc_param_value" name="<?php echo esc_attr( $settings['param_name'] ); ?>" value="<?php echo esc_attr( $value ); ?>">

			<ul class="lordcros-image-selection" id="select-<?php echo esc_attr( $uniqid ); ?>">
				<?php foreach ( $settings['value'] as $key => $value ): ?>
					<li data-value="<?php echo esc_attr( $value ); ?>">
						<img src="<?php echo esc_url( $settings['image_value'][$value] ); ?>">
						<h5><?php echo esc_html( $settings_value[$value] ); ?></h5>
					</li>
				<?php endforeach; ?>
			</ul>
			
			<script type="text/javascript">
				(function( $ ){
					var inputValue = $( '#input-<?php echo esc_js( $uniqid ); ?>' ).attr( 'value' );
					$( '#select-<?php echo esc_js( $uniqid ); ?> li[data-value="'+ inputValue +'"]' ).addClass( 'selected' );
					$( '#select-<?php echo esc_js( $uniqid ); ?> li' ).click( function(){
						var _this = $( this ),
							dataValue = _this.data( 'value' );

						_this.siblings().removeClass( 'selected' );
						_this.addClass( 'selected' );
						$( '#input-<?php echo esc_js( $uniqid ); ?>' ).attr( 'value', dataValue );
					} );
				})(jQuery);
			</script>
		<?php

		return ob_get_clean();
	}
	vc_add_shortcode_param( 'lordcros_image_selection', 'lordcros_core_image_selection_type' );
}

/* LordCros WPBakery datetimepicker param type */
if( ! function_exists( 'lordcros_core_datetimepicker_type' ) && function_exists( 'vc_add_shortcode_param' ) ) {
	function lordcros_core_datetimepicker_type( $settings, $value ) {
		$id = rand(100, 9999);
		$uniqid_id = uniqid( 'lordcros-vc-datetime-' . $id );

		$output = '<div id="' . $uniqid_id . '" class="vc-ui-datetime">'.'<input name="' . esc_attr( $settings['param_name'] ) . '" class="wpb_vc_param_value wpb-textinput ' . esc_attr( $settings['param_name'] ) . ' ' . esc_attr( $settings['type'] ) . '_field" type="text" value="' . esc_attr( $value ) . '" />'.'</div>';
		$output .= '<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery("#' . $uniqid_id . ' input").datepicker({
					dateFormat: "yy-mm-dd"
				});
			});
			</script>';

		return $output;
	}
	vc_add_shortcode_param( 'lordcros_datetimepicker', 'lordcros_core_datetimepicker_type' );
}

/* Suggestor for "Service IDs" field */
if ( ! function_exists( 'lordcros_core_service_ids_autocomplete_suggestor' ) ) { 
	function lordcros_core_service_ids_autocomplete_suggestor( $query, $tag, $param_name ) { 
		global $wpdb;

		$service_id = (int) $query;
		$service_infos = $wpdb->get_results( $wpdb->prepare( "SELECT a.ID AS id, a.post_title AS title
			FROM {$wpdb->posts} AS a
			WHERE a.post_type = 'service' AND a.post_status = 'publish' AND ( a.ID = '%d' OR a.post_title LIKE '%%%s%%' )", $service_id > 0 ? $service_id : - 1, stripslashes( $query ) ), ARRAY_A );

		$results = array();
		if ( is_array( $service_infos ) && ! empty( $service_infos ) ) {
			foreach ( $service_infos as $value ) {
				$data = array();

				$data['value'] = $value['id'];
				$data['label'] = esc_html__( 'Id', 'lordcros-core' ) . ': ' . $value['id'] . ( ( strlen( $value['title'] ) > 0 ) ? ' - ' . esc_html__( 'Title', 'lordcros-core' ) . ': ' . $value['title'] : '' );

				$results[] = $data;
			}
		}

		return $results;
	}
}

/*
 * Renderer for "Service IDs" field
 */
if ( ! function_exists( 'lordcros_core_service_ids_autocomplete_render' ) ) { 
	function lordcros_core_service_ids_autocomplete_render( $query ) { 
		$query = trim( $query['value'] ); // get value from requested

		if ( ! empty( $query ) ) {
			// get service
			$service_object = get_post( (int) $query );

			if ( is_object( $service_object ) ) {
				$service_title = $service_object->post_title;
				$service_id = $service_object->ID;

				$service_title_display = '';
				if ( ! empty( $service_title ) ) {
					$service_title_display = ' - ' . esc_html__( 'Title', 'lordcros-core' ) . ': ' . $service_title;
				}

				$service_id_display = esc_html__( 'Id', 'lordcros-core' ) . ': ' . $service_id;

				$data = array();
				$data['value'] = $service_id;
				$data['label'] = $service_id_display . $service_title_display;

				return $data;
			}

			return false;
		}

		return false;
	}
}

// "Service IDs" field in "Services" Visual Composer element
add_filter( 'vc_autocomplete_lc_services_post_ids_callback', 'lordcros_core_service_ids_autocomplete_suggestor', 10, 3 );
add_filter( 'vc_autocomplete_lc_services_post_ids_render', 'lordcros_core_service_ids_autocomplete_render', 10 );

/* Suggestor for "Post IDs" field */
if ( ! function_exists( 'lordcros_core_post_ids_autocomplete_suggestor' ) ) { 
	function lordcros_core_post_ids_autocomplete_suggestor( $query, $tag, $param_name ) { 
		global $wpdb;

		$post_id = (int) $query;
		$post_infos = $wpdb->get_results( $wpdb->prepare( "SELECT a.ID AS id, a.post_title AS title
			FROM {$wpdb->posts} AS a
			WHERE a.post_type = 'post' AND a.post_status = 'publish' AND ( a.ID = '%d' OR a.post_title LIKE '%%%s%%' )", $post_id > 0 ? $post_id : - 1, stripslashes( $query ) ), ARRAY_A );

		$results = array();
		if ( is_array( $post_infos ) && ! empty( $post_infos ) ) {
			foreach ( $post_infos as $value ) {
				$data = array();

				$data['value'] = $value['id'];
				$data['label'] = esc_html__( 'Id', 'lordcros-core' ) . ': ' . $value['id'] . ( ( strlen( $value['title'] ) > 0 ) ? ' - ' . esc_html__( 'Title', 'lordcros-core' ) . ': ' . $value['title'] : '' );

				$results[] = $data;
			}
		}

		return $results;
	}
}

/*
 * Renderer for "Post IDs" field
 */
if ( ! function_exists( 'lordcros_core_post_ids_autocomplete_render' ) ) { 
	function lordcros_core_post_ids_autocomplete_render( $query ) { 
		$query = trim( $query['value'] ); // get value from requested

		if ( ! empty( $query ) ) {
			// get post
			$post_object = get_post( (int) $query );

			if ( is_object( $post_object ) ) {
				$post_title = $post_object->post_title;
				$post_id = $post_object->ID;

				$post_title_display = '';
				if ( ! empty( $post_title ) ) {
					$post_title_display = ' - ' . esc_html__( 'Title', 'lordcros-core' ) . ': ' . $post_title;
				}

				$post_id_display = esc_html__( 'Id', 'lordcros-core' ) . ': ' . $post_id;

				$data = array();
				$data['value'] = $post_id;
				$data['label'] = $post_id_display . $post_title_display;

				return $data;
			}

			return false;
		}

		return false;
	}
}

// "Post IDs" field in "Posts" Visual Composer element
add_filter( 'vc_autocomplete_lc_posts_post_ids_callback', 'lordcros_core_post_ids_autocomplete_suggestor', 10, 3 );
add_filter( 'vc_autocomplete_lc_posts_post_ids_render', 'lordcros_core_post_ids_autocomplete_render', 10 );