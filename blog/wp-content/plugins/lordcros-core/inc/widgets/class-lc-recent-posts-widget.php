<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LordCros Core Recent Posts Widget
 */

if ( ! class_exists( 'LordCros_Core_Recent_Posts_Widget' ) ) {
	class LordCros_Core_Recent_Posts_Widget extends WP_Widget {
	
		function __construct() {	
			// Config widget fields
			$args = array( 
				'label' => esc_html__( 'LordCros Recent Posts', 'lordcros-core' ), 
				'description' => esc_html__( 'An advanced widget that gives you total control over the output of your site’s most recent Posts.', 'lordcros-core' ), 		
			 );

			parent::__construct( 'lordcros-recent-posts-widget', esc_html__( 'LordCros Recent Posts', 'lordcros-core' ), $args );
		}

		
		// Output function
		function widget( $args, $instance )	{

			extract($args);

			echo '' . $before_widget;

			if( ! empty( $instance['title'] ) ) {
				echo '' . $before_title . apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ) . $after_title;
			}

			// Get the recent posts query.
			$offset = isset( $instance['offset'] ) ? $instance['offset'] : 0;
			$posts_per_page = isset( $instance['limit'] ) ? $instance['limit'] : 5;
			$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'date';
			$order = isset( $instance['order'] ) ? $instance['order'] : 'DESC';
			$thumb_height = isset( $instance['thumb_height'] ) ? $instance['thumb_height'] : 100;
			$thumb_width = isset( $instance['thumb_width'] ) ? $instance['thumb_width'] : 130;
			$thumb = isset( $instance['thumb'] ) ? $instance['thumb'] : true;
			$date = isset( $instance['date'] ) ? $instance['date'] : true;

			$query = array(
				'offset'         => $offset,
				'posts_per_page' => $posts_per_page,
				'orderby'        => $orderby,
				'order'          => $order
			);

			$posts = new WP_Query( $query );

			if ( $posts->have_posts() ): 
				?>

				<ul class="lordcros-recent-posts-list">
					<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
						<li>
							<?php if ( $thumb ): ?>
								<?php if ( has_post_thumbnail() ): ?>
									<a class="recent-posts-thumbnail" href="<?php echo esc_url( get_permalink() ); ?>"  rel="bookmark">
										<?php echo lordcros_get_post_thumbnail( array( $thumb_width, $thumb_height ) ); ?>
									</a>
								<?php endif ?>
							<?php endif ?>
							<div class="recent-posts-info">
								<span class="post-title"><a href="<?php echo esc_url( get_permalink() ) ?>" title="<?php echo sprintf( esc_attr__( 'Permalink to %s', 'lordcros-core' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php echo esc_attr( get_the_title() ); ?></a></span>

								<?php if ( $date ): ?>
									<?php $date = get_the_date(); ?>
									<time class="recent-posts-time" datetime="<?php echo esc_html( get_the_date( 'c' ) ); ?>"><?php echo esc_html( $date ); ?></time>
								<?php endif ?>

							</div>
						</li>

					<?php endwhile; ?> 

				</ul>

				<?php 
			endif;

			wp_reset_postdata();

			echo '' . $after_widget;
		}

		public function update( $new_instance, $old_instance ) {

			$instance = $old_instance;
			$instance['title'] = sanitize_text_field( $new_instance['title'] );
			$instance['limit'] = intval( $new_instance['limit'] );
			$instance['offset'] = intval( $new_instance['offset'] );
			$instance['order'] = stripslashes( $new_instance['order'] );
			$instance['orderby'] = stripslashes( $new_instance['orderby'] );
			$instance['date'] = isset( $new_instance['date'] ) ? (bool) $new_instance['date'] : '';
			$instance['thumb'] = isset( $new_instance['thumb'] ) ? (bool) $new_instance['thumb'] : '';
			$instance['thumb_height'] = intval( $new_instance['thumb_height'] );
			$instance['thumb_width'] = intval( $new_instance['thumb_width'] );

			return $instance;
		}

		function form( $instance ) {
			$defaults = array(
				'title'				=> esc_attr__( 'Recent Posts', 'lordcros-core' ),
				'limit'				=> 5,
				'offset'			=> 0,
				'order'				=> 'DESC',
				'orderby'			=> 'date',
				'thumb'				=> true,
				'thumb_height'		=> 100,
				'thumb_width'		=> 130,
				'date'				=> true,
			);

			$instance = wp_parse_args( (array) $instance, $defaults );
			?>

			<p class="title-field-wrapper">
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
					<?php echo esc_html__( 'Title', 'lordcros-core' ); ?>
				</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>

			<p class="order-field-wrapper">
				<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>">
					<?php echo esc_html__( 'Order', 'lordcros-core' ); ?>
				</label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' )); ?>" style="width:100%;">
					<option value="DESC" <?php selected( $instance['order'], 'DESC' ); ?>><?php echo esc_html__( 'Descending', 'lordcros-core' ) ?></option>
					<option value="ASC" <?php selected( $instance['order'], 'ASC' ); ?>><?php echo esc_html__( 'Ascending', 'lordcros-core' ) ?></option>
				</select>
			</p>

			<p class="order-by-field-wrapper">
				<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ) ; ?>">
					<?php echo esc_html__( 'Orderby', 'lordcros-core' ); ?>
				</label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ) ; ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>" style="width:100%;">
					<option value="ID" <?php selected( $instance['orderby'], 'ID' ); ?>><?php echo esc_html__( 'ID', 'lordcros-core' ) ?></option>
					<option value="author" <?php selected( $instance['orderby'], 'author' ); ?>><?php echo esc_html__( 'Author', 'lordcros-core' ) ?></option>
					<option value="title" <?php selected( $instance['orderby'], 'title' ); ?>><?php echo esc_html__( 'Title', 'lordcros-core' ) ?></option>
					<option value="date" <?php selected( $instance['orderby'], 'date' ); ?>><?php echo esc_html__( 'Date', 'lordcros-core' ) ?></option>
					<option value="modified" <?php selected( $instance['orderby'], 'modified' ); ?>><?php echo esc_html__( 'Modified', 'lordcros-core' ) ?></option>
					<option value="rand" <?php selected( $instance['orderby'], 'rand' ); ?>><?php echo esc_html__( 'Random', 'lordcros-core' ) ?></option>
					<option value="comment_count" <?php selected( $instance['orderby'], 'comment_count' ); ?>><?php echo esc_html__( 'Comment Count', 'lordcros-core' ) ?></option>
					<option value="menu_order" <?php selected( $instance['orderby'], 'menu_order' ); ?>><?php echo esc_html__( 'Menu Order', 'lordcros-core' ) ?></option>
				</select>
			</p>

			<p class="limit-field-wrapper">
				<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>">
					<?php echo esc_html__( 'Number of posts to show', 'lordcros-core' ); ?>
				</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' )); ?>" type="number" step="1" min="-1" value="<?php echo esc_attr( (int)$instance['limit'] ); ?>" />
			</p>

			<p class="offset-field-wrapper">
				<label for="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>">
					<?php echo esc_html__( 'Offset', 'lordcros-core' ); ?>
				</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'offset' ) ); ?>" type="number" step="1" min="0" value="<?php echo esc_attr( (int) $instance['offset'] ); ?>" />
				<small><?php echo esc_html__( 'The number of posts to skip', 'lordcros-core' ); ?></small>
			</p>

			<p class="thumb-field-wrapper">
				<input id="<?php echo esc_attr( $this->get_field_id( 'thumb' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb' ) ); ?>" type="checkbox" class="thumb-field recent-posts-widget-field" <?php checked( $instance['thumb'] ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'thumb' ) ); ?>">
					<?php echo esc_html__( 'Display Thumbnail', 'lordcros-core' ); ?>
				</label>
			</p>

			<p class="size-field-wrapper">
				<label style="display: block;" class="lordcros-block" for="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>">
					<?php echo esc_html__( 'Thumbnail (height)', 'lordcros-core' ); ?>
				</label>
				<input style="display: block;" class= "small-input" id="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_height' ) ); ?>" type="number" step="1" min="0" value="<?php echo esc_attr( (int)$instance['thumb_height'] ); ?>" />
				<label style="display: block;" class="lordcros-block" for="<?php echo esc_attr( $this->get_field_id( 'thumb_height' ) ); ?>">
					<?php echo esc_html__( 'Thumbnail (width)', 'lordcros-core' ); ?>
				</label>
				<input style="display: block;" class="small-input" id="<?php echo esc_attr( $this->get_field_id( 'thumb_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'thumb_width' ) ); ?>" type="number" step="1" min="0" value="<?php echo esc_attr( (int)$instance['thumb_width'] ); ?>"/>
			</p>
			
			<p class="date-field-wrapper">
				<input id="<?php echo esc_attr( $this->get_field_id( 'date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'date' ) ); ?>" type="checkbox" <?php checked( $instance['date'] ); ?> />
				<label for="<?php echo esc_attr( $this->get_field_id( 'date' ) ); ?>">
					<?php echo esc_html__( 'Display Date', 'lordcros-core' ); ?>
				</label>
			</p>

			<?php
		}
	}
}