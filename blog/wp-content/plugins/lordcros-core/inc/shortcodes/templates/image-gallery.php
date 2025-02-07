<?php
/**
 *	Image Carousel Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// [lc_image_gallery]
function lordcros_core_shortcode_image_gallery( $atts, $content = null ) {
	extract( shortcode_atts(array(
		'title'						=>	'',
		'short_description'			=>	'',
		'ids'						=>	'',
		'view_style'				=>	'grid', //grid, masonry
		'columns'					=>	4,
		'image_size'				=>	'full',
		'item_gap'					=>	10,
		'extra_class'				=>	'',
		'animation'					=>	'',
		'animation_delay'			=>	''
	), $atts, 'lordcros_image_gallery' ) );

	$rand_id = rand( 100, 9999 );
	$shortcode_gallery_id = uniqid( 'lordcros-image-gallery-' . $rand_id );

	$image_gallery_classes = $html = $styles = '';

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	if ( ! empty( $animation_classes ) ) {
		$image_gallery_classes .= ' ' . $animation_classes;
	}

	if ( ! empty( $extra_class ) ) {
		$image_gallery_classes .= ' ' . $extra_class;
	}

	if ( ! empty( $view_style ) ) {
		$image_gallery_classes .= ' gallery-view-' . $view_style . '-method';	
	}

	if ( isset( $ids ) ) {
		$image_ids = explode( ',', $ids );
		$image_ids = array_map( 'trim', $image_ids );
	} else {
		$image_ids = array();
	}

	$item_gap = intval( $item_gap );
	if ( $item_gap > 30 ) {
		$item_gap = 30;
	}

	$data_attributes = 'data-col="' . esc_attr( $columns ) . '"';
	$data_attributes .= ' data-margin-val="' . esc_attr( $item_gap ) . '"';

	$image_count = count( $image_ids );
	ob_start();
	?>

		<div id="<?php echo esc_attr( $shortcode_gallery_id ); ?>" class="lordcros-shortcode-element lordcros-shortcode-img-gallery <?php echo esc_attr( $image_gallery_classes ); ?>" style="animation-delay: <?php echo esc_attr( $animation_delay ); ?>s;">
			<?php if ( ! empty( $title ) ) : ?>
				<h2 class="shortcode-title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $short_description ) ) : ?>
				<p class="shortcode-description"><?php echo '' . $short_description; ?></p>
			<?php endif; ?>

			<div class="image-gallery-inner" <?php echo $data_attributes; ?>>
				<?php
				$image_counter = 0;

				foreach ( $image_ids as $image_id ) {
					if ( 'masonry' == $view_style ) {
						$image_url = wp_get_attachment_url( $image_id );
						?>
							<div class="lordcros-image-wrap">
								<img src="<?php echo esc_url( $image_url ); ?>" class="<?php echo esc_attr( 'image-' . $image_id ); ?>">
							</div>
						<?php
					} else {
						?>
							<div class="lordcros-image-wrap">
								<?php echo lordcros_core_get_image( $image_id, $image_size ); ?>
							</div>
						<?php
					}
				}
				?>
			</div>
		</div>

	<?php

	if ( 'masonry' == $view_style ) {
		lordcros_core_masonry_layout( $shortcode_gallery_id, 'image-gallery-inner' );
	}

	$html = ob_get_clean();

	return $html;
}

add_shortcode( 'lc_image_gallery', 'lordcros_core_shortcode_image_gallery' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_image_gallery() {

	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Image Gallery', 'lordcros-core' ),
		'base'			=>	'lc_image_gallery',
		'icon'			=>	'lordcros-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'	=>	esc_html__( 'Add Image Gallery', 'lordcros-core' ),
		'params'		=>	array(
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
				'type'			=>	'attach_images',
				'heading'		=>	esc_html__( 'Images', 'lordcros-core' ),
				'param_name'	=>	'ids',
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'View mode', 'lordcros-core' ),
				'param_name'	=>	'view_style',
				'value'			=>	array(
					esc_html__( 'Grid', 'lordcros-core' )		=>	'grid',
					esc_html__( 'Masonry', 'lordcros-core' )	=>	'masonry',
				),
				'std'			=>	'grid'
			),
			array(
				'type'				=>	'textfield',
				'heading'			=>	esc_html__( 'Image Size', 'lordcros-core' ),
				'param_name'		=>	'image_size',
				'admin_label'		=>	false,
				'description'		=>	esc_html__( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Example: 200x100 (Width x Height)).', 'lordcros-core' ),
				'dependency'		=>	array(
					'element'		=>	'view_style',
					'value'			=>	array( 'grid' )
				)
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Columns', 'lordcros-core' ),
				'param_name'	=>	'columns',
				'value'			=>	array(
					1, 2, 3, 4, 5, 6
				),
				'std'			=>	3
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Image Gap', 'lordcros-core' ),
				'param_name'	=>	'item_gap',
				'description'	=>	esc_html__( 'Unit: px, Max value: 30', 'lordcros-core' ),
				'std'			=>	'10'
			),
			$animation_style,
			$animation_delay,
			$extra_class,
		),
	) );
}

lordcros_core_vc_shortcode_image_gallery();