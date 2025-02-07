<?php
/**
 * The default template for content none
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<article class="post no-results not-found">
	<div class="lordcros-msg warning-msg-wrap">
		<i class="fas fa-exclamation-triangle"></i>
		<p class="warning-description"><?php echo esc_html__( 'Not Founding', 'lordcros' ); ?></p>
	</div>
</article>