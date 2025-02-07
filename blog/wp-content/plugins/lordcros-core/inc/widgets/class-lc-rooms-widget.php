<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LordCros Core Rooms Widget
 */

if ( ! class_exists( 'LordCros_Core_Rooms_Widget' ) ) {
	class LordCros_Core_Rooms_Widget extends WP_Widget {

		function __construct() {
			
			$args = array( 
				'label'			=>	esc_html__( 'LordCros Rooms', 'lordcros-core' ),
				'description'	=>	esc_html__( 'Display rooms.', 'lordcros-core' ),
			);
		
			parent::__construct( 'lordcros-room-widget', esc_html__( 'LordCros Rooms', 'lordcros-core' ), $args );
		}

		// Output function
		function widget( $args, $instance )	{
			$widget_title = empty( $instance['title'] ) ? '' : $instance['title'];
			$type = $instance['type'];
			$count = $instance['count'];
			$post_ids = empty( $instance['post_ids'] ) ? array() : $instance['post_ids'];

			$widget_title = apply_filters( 'widget_title', $widget_title, $instance );

			echo '' . $args['before_widget'];

			if ( ! empty( $widget_title ) ) { 
				echo '' . $args['before_title'] . $widget_title . $args['after_title'];
			}

			$rooms = array();
			if ( $type == 'selected' ) {
				$rooms = lordcros_core_get_rooms_from_id( $post_ids );
			} else {
				$rooms = lordcros_core_get_special_rooms( $type, $count );
			}

			if ( ! empty( $rooms ) ) {
			?>
				<div class="lordcros-widget-content lordcros-rooms-widget-content">
					<?php
					foreach( $rooms as $room ) {
						$room_id = $room->ID;
						$title = get_the_title( $room_id );
						$permalink = get_permalink( $room_id );
						$price_per_night = rwmb_meta( 'lordcros_room_price', '', $room_id );
						?>
						<div class="room">
							<div class="room-thumbs">
								<a href="<?php echo esc_url( $permalink ); ?>" class="room-featured-img">
									<?php echo get_the_post_thumbnail( $room_id, 'lordcros-room-grid' ); ?>
								</a>
							</div>
							<div class="room-info">
								<span class="room-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></span>
								<span class="price-section">
									<?php echo esc_html__( 'From', 'lordcros-core' ) . ' ' . lordcros_price( $price_per_night ) . ' ' . esc_html__( 'per night', 'lordcros-core' ); ?>
								</span>
								<a href="<?php echo esc_url( $permalink ); ?>" class="room-book-btn">
									<?php echo esc_html__( 'Book Now', 'lordcros-core' ); ?> <i class="lordcros lordcros-arrow-right"></i>
								</a>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			<?php
			}
			echo '' . $args['after_widget'];
		}

		function form( $instance ) {
			// Output admin widget options form
			$defaults = array( 'title' => '', 'type' => 'latest', 'post_ids' => array(), 'count' => 3 );
			$instance = wp_parse_args( (array) $instance, $defaults );
			$args = array( 'post_type' => 'room', 'posts_per_page' => -1 );
			$rooms = get_posts( $args );
			?>
			<p class="title-field-wrapper">
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html__( 'Title', 'lordcros-core' ); ?>:</label>
				<input type="text" class="widefat title-field room-widget-field" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>
			<p class="type-field-wrapper">
				<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php echo esc_html__( 'Type', 'lordcros-core' ); ?>:</label>
				<select class="widefat type-field room-widget-field" id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>">
					<option value="latest" <?php selected( 'latest', esc_attr( $instance['type'] ) ); ?> ><?php echo esc_html__( 'Latest', 'lordcros-core' ); ?></option>
					<option value="featured" <?php selected( 'featured', esc_attr( $instance['type'] ) ); ?> ><?php echo esc_html__( 'Featured', 'lordcros-core' ); ?></option>
					<option value="selected" <?php selected( 'selected', esc_attr( $instance['type'] ) ); ?> ><?php echo esc_html__( 'Selected', 'lordcros-core' ); ?></option>
				</select>
			</p>			
			<p class="room-ids-field-wrapper">
				<label for="<?php echo esc_attr( $this->get_field_id( 'post_ids' ) ); ?>"><?php echo esc_html__( 'Rooms', 'lordcros-core' ); ?>:</label>
				<select class="widefat room-ids-field room-widget-field" id="<?php echo esc_attr( $this->get_field_id( 'post_ids' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_ids' ) ); ?>[]" multiple="multiple">
					<?php foreach ( $rooms as $room ) : ?>
						<option value="<?php echo esc_attr( $room->ID ); ?>" <?php echo in_array( $room->ID, $instance['post_ids'] ) ? 'selected' : ''; ?> ><?php echo esc_html( $room->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p class="count-field-wrapper">
				<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php echo esc_html__( 'Count', 'lordcros-core' ); ?>:</label>
				<input type="text" class="widefat count-field room-widget-field" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" value="<?php echo esc_attr( $instance['count'] ); ?>" />
			</p>			
			<?php 
		}
	
	} // class
}