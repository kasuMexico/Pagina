<?php 
/**
 *	Menu Custom Walker
 */

class LordCrosWalker extends Walker_Nav_Menu {

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_query, $wpdb;

		/* Get Custom Menu Values */
		$header_layout = lordcros_get_opt( 'header_layout', 'header-layout-1' );
		$submenu_style = 'default-menu';
		if ( in_array( $header_layout, array( 'header-layout-3', 'header-layout-4', 'header-layout-6', 'header-layout-10' ) ) ) :
			$submenu_style = 'default-menu';
		endif;
				
		/* End Getting Custom Menu Values */

		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$id_name = $class_names = $extra_classes[] = '';
		$id_name = ' id="lordcros-menu-item-' . esc_attr( $item->ID ) . '"';
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		$extra_classes[] = 'item-level-' . $depth;
		$extra_classes[] = $submenu_style;
		if ( 'default-menu' != $submenu_style ) {
			$extra_classes[] = 'dropdown-mega-menu';
		}
		$extra_classes = implode( ' ', $extra_classes );

		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
		$class_names = ' class="'. esc_attr( $class_names ) . ' ' . esc_attr( $extra_classes ) . '"';

		$output .= $indent . '<li ' . $class_names .'>';

		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_url( $item->url ) .'"' : '';

		$prepend = '';
		$append = '';

		if($depth != 0) {
		   $description = $append = $prepend = "";
		}
		
		// Insert Output Content Start
		$item_output = $args->before;
		$item_output .= '<a' . $attributes . '>';
		$item_output .= '<span class="menu-title">' . $args->link_before .$prepend.apply_filters( 'the_title', $item->title, $item->ID ).$append;
		$item_output .= '</span></a>';

		$item_output .= $args->after;
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

		apply_filters( 'walker_nav_menu_start_lvl', $item_output, $depth );
	}

	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<ul class=\"sub-menu-dropdown sub-menu-level-". $depth ."\">\n";
	}
}

?>