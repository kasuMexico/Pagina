<?php
/**
 * Rooms Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lc_rooms]
function lordcros_core_shortcode_rooms( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'title'					=>	'',
		'short_description'		=>	'',
		'style'					=>	'grid', //grid, block
		'block_height'			=>	'560px',
		'slider'				=>	'no',	// yes, no
		'slider_effect'			=>	'style1', // style1, style2
		'type'					=>	'latest', //latest, featured, selected
		'post_ids'				=>	'',
		'count'					=>	3,
		'columns'				=>	3,
		'title_color_scheme'	=>	'dark', //dark, light
		'margin'				=>	10,
		'extra_class'			=>	'',
		'animation'				=>	'',
		'animation_delay'		=>	1,
		'css'					=>	'',
	), $atts, 'lordcros-rooms' ) );

	$id = rand( 100, 9999 );
	$uniqid_id = uniqid( 'lordcros-rooms-' . $id );

	$animation_classes = lordcros_core_getCSSAnimation( $animation );
		
	if ( $slider != 'yes' ) {
		$slider = 'no';
	}

	$post_ids = explode( ',', $post_ids );
	if ( empty( $count ) || ( ! is_numeric( $count ) ) ) {
		$count = 3;	
	}
	if ( empty( $columns ) || ( ! is_numeric( $columns ) ) ) {
		$columns = 3;
	}
	$margin = intval( $margin );
	if ( $margin > 30 ) {
		$margin = 30;
	}

	$rooms = array();
	if ( $type == 'selected' ) {
		$rooms = lordcros_core_get_rooms_from_id( $post_ids );
	} else {
		$rooms = lordcros_core_get_special_rooms( $type, $count );
	}

	$shortcode_classes = "";
	if ( ! empty( $title_color_scheme ) ) {
		$shortcode_classes .= "color-scheme-" . $title_color_scheme;
	}
	$shortcode_classes .= " " . $extra_class;

	$wrapper_class = "";
	if ( $slider == 'yes' ) {
		$wrapper_class .= "owl-carousel room-slider " . $slider_effect;

		if ( $slider_effect == 'style2' ) {
			$margin = 100;
		}
	}
	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$wrapper_class .= ' ' . vc_shortcode_custom_css_class( $css );
	}

	$html = '';
	
	if ( ! empty( $rooms ) ) {
		ob_start();
		?>
		<div id="<?php echo esc_attr( $uniqid_id ); ?>" class="lordcros-shortcode-element lordcros-shortcode-rooms <?php echo esc_attr( $animation_classes ) . ' ' . esc_attr( $shortcode_classes ); ?>" style="animation-delay: <?php echo esc_attr( $animation_delay ); ?>s;">
			
			<?php if ( ! empty( $title ) ) : ?>
				<h2 class="shortcode-title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $short_description ) ) : ?>
				<p class="shortcode-description"><?php echo '' . $short_description; ?></p>
			<?php endif; ?>

			<div class="lordcros-shortcode-rooms-wrapper available-rooms-wrap <?php echo esc_attr( $wrapper_class ); ?>" data-col="<?php echo esc_attr( $columns ); ?>" data-margin-val="<?php echo esc_attr( $margin ); ?>">
				<?php
				foreach ( $rooms as $room ) {
					if ( $style == 'block' ) {
						echo lordcros_core_room_get_shortcode_block_view_html( $room->ID, $block_height );
					} else {
						echo lordcros_core_room_get_grid_view_html( $room->ID );
					}
				}
				?>
			</div>
		</div>
		<?php

		if ( $slider == 'yes' ) {
			$center_mode = 0;

			if ( $slider_effect == 'style2' ) {
				$center_mode = 1;				
			}

			lordcros_core_carousel_layout( $uniqid_id, $columns, 800, 1, false, 'no', 'no', 'yes', $center_mode, 0, $margin );
		}

		$html = ob_get_clean();
	}

	return $html;
}

add_shortcode( 'lc_rooms', 'lordcros_core_shortcode_rooms' );

/* WPBakery */
function lordcros_core_vc_shortcode_rooms() {

	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'				=> esc_html__( 'Rooms', 'lordcros-core' ),
		'base'				=> 'lc_rooms',
		'icon'				=> 'lordcros-js-composer',
		'category'			=> esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'		=> esc_html__( 'Add Rooms on your page.', 'lordcros-core' ),
		'params'			=> array(
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Title', 'lordcros-core' ),
				'param_name'	=> 'title',
				'save_always'	=> true,
				'admin_label'	=> false,
			),
			array(
				'type'			=> 'textarea',
				'heading'		=> esc_html__( 'Description', 'lordcros-core' ),
				'param_name'	=> 'short_description',
				'save_always'	=> false,
			),
			array(
				"type"			=> 'dropdown',
				"heading"		=> esc_html__( 'Type', 'lordcros-core' ),
				"param_name"	=> 'type',
				"value"			=> array(
					__( 'Latest Rooms', 'lordcros-core' )	=> 'latest',
					__( 'Featured Rooms', 'lordcros-core' )	=> 'featured',
					__( 'Selected Rooms', 'lordcros-core' )	=> 'selected',
				),
				"std"			=> 'latest',
				'admin_label'	=> true
			),
			array(
				'type'			=> 'autocomplete',
				'heading'		=> esc_html__( 'Room IDs', 'lordcros-core' ),
				'param_name'	=> 'post_ids',
				'settings'		=> array(
					'multiple'		=> true,
					'sortable'		=> true,
				),
				'save_always'	=> true,
				'admin_label'	=> false,
				'dependency'	=> array(
					'element'		=> 'type',
					'value'			=> array( 'selected' )
				),
			),
			$animation_style,
			$animation_delay,
			$extra_class,
			array(
				'type'			=> 'dropdown',
				'heading'		=> esc_html__( 'Room Style', 'lordcros-core' ),
				'param_name'	=> 'style',
				'value'			=> array(
					__( 'Grid', 'lordcros-core' )	=> 'grid',
					__( 'Block', 'lordcros-core' )	=> 'block'
				),
				'std'			=> 'grid',
				'save_always'	=> true,
				'admin_label'	=> false,
				'group'			=>	esc_html__( 'Style', 'lordcros-core' )
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Block Height', 'lordcros-core' ),
				'param_name'	=>	'block_height',
				'description'	=>	esc_html__( 'Add height value with units such as "px", "em", "rem".', 'lordcros-core' ),
				'std'			=>	'560px',
				'dependency'	=> array(
					'element'		=> 'style',
					'value'			=> array( 'block' ),
				),
				'group'			=>	esc_html__( 'Style', 'lordcros-core' )
			),
			array(
				'type'			=> 'dropdown',
				'heading'		=> esc_html__( 'Enable Carousel Slider?', 'lordcros-core' ),
				'param_name'	=> 'slider',
				'value'			=> array(
					__( 'Yes', 'lordcros-core' )	=> 'yes',
					__( 'No', 'lordcros-core' )		=> 'no',
				),
				'dependency'	=> array(
					'element'		=> 'style',
					'value'			=> array( 'block' ),
				),
				'std'			=> 'no',
				'admin_label'	=> false,
				'group'			=>	esc_html__( 'Style', 'lordcros-core' )
			),
			array(
				'type'			=> 'dropdown',
				'heading'		=> esc_html__( 'Carousel Slider Effect', 'lordcros-core' ),
				'param_name'	=> 'slider_effect',
				'value'			=> array(
					__( 'Style1', 'lordcros-core' )	=> 'style1',
					__( 'Style2', 'lordcros-core' )	=> 'style2',
				),
				'dependency'	=> array(
					'element'		=> 'slider',
					'value'			=> array( 'yes' ),
				),
				'save_always'	=> true,
				'group'			=>	esc_html__( 'Style', 'lordcros-core' )
			),
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Room Count', 'lordcros-core' ),
				'param_name'	=> 'count',
				'save_always'	=> true,
				'dependency'	=> array(
					'element'		=> 'type',
					'value'			=> array( 'latest', 'featured' )
				),
				'group'			=>	esc_html__( 'Style', 'lordcros-core' )
			),
			array(
				'type'			=> 'dropdown',
				'heading'		=> esc_html__( 'Columns', 'lordcros-core' ),
				'param_name'	=> 'columns',
				'value'			=> array(
					__( '1', 'lordcros-core' )	=> 1,
					__( '2', 'lordcros-core' )	=> 2,
					__( '3', 'lordcros-core' )	=> 3,
					__( '4', 'lordcros-core' )	=> 4,
				),
				'save_always'	=> true,
				'std'			=> '3',
				'group'			=>	esc_html__( 'Style', 'lordcros-core' )
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Title Color Scheme', 'lordcros-core' ),
				'param_name'	=>	'title_color_scheme',
				'value'			=>	array(
					__( 'Dark', 'lordcros-core' )	=>	'dark',
					__( 'Light', 'lordcros-core' )	=>	'light'
				),
				'std'			=>	'dark',
				'save_always'	=>	true,
				'group'			=>	esc_html__( 'Style', 'lordcros-core' )
			),
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Item Margin', 'lordcros-core' ),
				'param_name'	=> 'margin',
				'save_always'	=> true,
				'std'			=> '10',
				'description'	=> esc_html__( 'Unit: px, Max value: 30', 'lordcros-core' ),
				'group'			=>	esc_html__( 'Style', 'lordcros-core' )
			),
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Custom CSS', 'lordcros-core' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'lordcros-core' )
			)
		)
	) );
}

lordcros_core_vc_shortcode_rooms();