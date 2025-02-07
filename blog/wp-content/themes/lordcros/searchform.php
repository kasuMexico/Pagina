<?php
/**
 * Template for displaying search forms in LordCros
 */
?>

<form role="search" method="get" id="searchform" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<input type="hidden" name="post_type[]" value="post">
	<input type="hidden" name="post_type[]" value="room">
	<input type="hidden" name="post_type[]" value="service">
	<span class="screen-reader-text"><?php echo _x( 'Search for:', 'label', 'lordcros' ); ?></span>
	<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Buscar &hellip;', 'placeholder', 'lordcros' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
	<button type="submit" id="searchsubmit" class="searchsubmit"><i class="lordcros lordcros-search"></i></button>
</form>
