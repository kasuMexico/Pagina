<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LordCros Core Rooms Search Form Widget
 */

if ( ! class_exists( 'LordCros_Core_Room_Search_Form_Widget' ) ) {
	class LordCros_Core_Room_Search_Form_Widget extends WP_Widget {

		function __construct() {
			
			$args = array( 
				'label'			=>	esc_html__( 'LordCros Room Search Form', 'lordcros-core' ),
				'description'	=>	esc_html__( 'Display room search form.', 'lordcros-core' ),
			);
		
			parent::__construct( 'lordcros-room-search-form-widget', esc_html__( 'LordCros Room Search Form', 'lordcros-core' ), $args );
		}

		// Output function
		function widget( $args, $instance )	{
			$widget_title = empty( $instance['title'] ) ? '' : $instance['title'];
			$widget_title = apply_filters( 'widget_title', $widget_title, $instance );

			echo '' . $args['before_widget'];

			if ( ! empty( $widget_title ) ) { 
				echo '' . $args['before_title'] . $widget_title . $args['after_title'];
			}

			echo '<div class="lordcros-room-search-widget"><div class="widget-search-form-wrap">';
			echo lordcros_core_room_search_form();
			echo '</div></div>';
			echo '' . $args['after_widget'];
		}

		function form( $instance ) {
			// Output admin widget options form
			$defaults = array( 'title' => '' );
			$instance = wp_parse_args( (array) $instance, $defaults );
			?>
			<p class="title-field-wrapper">
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html__( 'Title', 'lordcros-core' ); ?>:</label>
				<input type="text" class="widefat title-field room-widget-field" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>						
			<?php 
		}
	
	} // class
}