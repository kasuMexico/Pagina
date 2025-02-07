<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LordCros Core HTML Block Widget
 */

if ( ! class_exists( 'LordCros_Core_HTML_Block_Widget' ) ) {
	class LordCros_Core_HTML_Block_Widget extends WP_Widget {

		function __construct() {
			
			$args = array( 
				'label'			=>	esc_html__( 'LordCros HTML Block', 'lordcros-core' ),
				'description'	=>	esc_html__( 'Display the selected HTML Block.', 'lordcros-core' ),
			);
		
			parent::__construct( 'lordcros-html-block-widget', esc_html__( 'LordCros HTML Block', 'lordcros-core' ), $args );
		}

		// Output function
		function widget( $args, $instance )	{
			$widget_title = empty( $instance['title'] ) ? '' : $instance['title'];
			$block_id     = $instance['html_block'];
			$title_option = empty( $instance['title_option'] )? 'widget_title' : $instance['title_option'];

			if ( ! $block_id ) { 
				return; 
			}

			if ( 'block_title' == $title_option ) { 
				$widget_title = get_the_title( $block_id );
			}

			$widget_title = apply_filters( 'widget_title', $widget_title, $instance );

			echo '' . $args['before_widget'];

			if ( ! empty( $widget_title ) ) { 
				echo '' . $args['before_title'] . $widget_title . $args['after_title'];
			}

			echo '<div class="lordcros-widget-content">';

			echo lordcros_core_shortcode_html_block( array( 
					'block_id' => $block_id 
				) );

			echo '</div>';

			echo '' . $args['after_widget'];
		}

		function form( $instance ) {
			// Output admin widget options form
			$defaults = array( 'title' => '', 'html_block' => '', 'title_option' => 'widget_title' );
			$instance = wp_parse_args( (array) $instance, $defaults );
			$args = array( 'post_type' => 'html_block', 'posts_per_page' => -1 );
			$html_blocks = get_posts( $args );
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php echo esc_html__( 'Title', 'lordcros-core' ); ?>:</label>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'html_block' ) ); ?>"><?php echo esc_html__( 'HTML Block', 'lordcros-core' ); ?>:</label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'html_block' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'html_block' ) ); ?>">
					<?php foreach ( $html_blocks as $html_block ) : ?>
						<option value="<?php echo esc_attr( $html_block->ID ); ?>" <?php selected( $html_block->ID, $instance['html_block'] ); ?> ><?php echo esc_html( $html_block->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>			
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title_option' ) ); ?>"><?php echo esc_html__( 'Widget Title Option', 'lordcros-core' ); ?>:</label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title_option' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title_option' ) ); ?>">
					<option value="widget_title" <?php selected( 'widget_title', $instance['title_option'] ); ?> ><?php echo esc_html__( 'Use Custom Title', 'lordcros-core' ); ?></option>
					<option value="block_title" <?php selected( 'block_title', $instance['title_option'] ); ?> ><?php echo esc_html__( 'Use Original Block Title', 'lordcros-core' ); ?></option>
				</select>
			</p>			
			<?php 
		}
	
	} // class
}