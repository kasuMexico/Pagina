<?php
/**
 * Room Archive Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Get Page Classes
$page_classes = 'container';

lordcros_page_heading();

?>
	<div class="main-content <?php echo esc_attr( $page_classes ); ?>">
		<div class="available-rooms-wrap row" data-col="3">
			<?php 
				if ( have_posts() ) : 

					while ( have_posts() ): the_post();
						$room_id = get_the_ID();
						echo lordcros_core_room_get_grid_view_html( $room_id, '', '', '', 1, 0, 'h2' );
					endwhile;

				else :
					?>

					<div class="lordcros-msg warning-msg-wrap">
						<i class="fas fa-exclamation-triangle"></i>
						<p class="warning-description"><?php echo esc_html__( 'No Rooms found', 'lordcros' ); ?></p>
					</div>

					<?php
				endif; 
			?>
		</div>

		<div class="lordcros-pagination">
			<?php echo paginate_links( array(
										'type'		=>	'list',
										'prev_text'	=>	esc_html__( 'Prev', 'lordcros' ),
										'next_text'	=>	esc_html__( 'Next', 'lordcros' ),
									) ); ?>
		</div>
	</div>
<?php
get_footer();