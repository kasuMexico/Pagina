<?php
/**
 * Single Room Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

?>

<div class="main-content">

<?php
	if ( have_posts() ) {
		while ( have_posts() ) : the_post();

			// add to user recent activity
			lordcros_update_user_recent_activity( get_the_ID() );

			$layout = lordcros_page_layout();

			lordcros_get_template_part( 'room/room', $layout );
					
		endwhile;
	}
?>

</div>

<?php

get_footer();