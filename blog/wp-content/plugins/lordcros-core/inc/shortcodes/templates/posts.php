<?php
/**
 * Post Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lc_posts]
function lordcros_core_shortcode_posts( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'title'					=>	'',
		'short_description'		=>	'',
		'type'					=>	'latest', // latest, selected
		'style'					=>	'style1', //style1, style2, style3
		'slider'				=>	'no',	// yes, no
		'post_ids'				=>	'',
		'count'					=>	3,
		'columns'				=>	3,
		'title_color_scheme'	=>	'dark', //dark, light
		'margin'				=>	10,
		'extra_class'			=>	'',
		'animation'				=>	'',
		'animation_delay'		=>	1,
		'css'					=>	'',
	), $atts, 'lordcros_posts' ) );

	$id = rand( 100, 9999 );
	$shortcode_posts_id = uniqid( 'lordcros-posts-' . $id );

	$posts_classes = $html = '';

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	if ( ! empty( $animation_classes ) ) {
		$posts_classes .= ' ' . $animation_classes;
	}

	if ( ! empty( $extra_class ) ) {
		$posts_classes .= ' ' . $extra_class;
	}

	if ( ! empty( $style ) ) {
		$posts_classes .= ' ' . $style ;	
	}

	if ( ! empty( $title_color_scheme ) ) {
		$posts_classes .= " color-scheme-" . $title_color_scheme;
	}

	$slider_class = '';
	if ( $slider == 'yes' ) {
		$slider_class .= " owl-carousel posts-slider";
	}
	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$posts_classes .= ' ' .vc_shortcode_custom_css_class( $css );
	}

	$post_ids = explode( ',', $post_ids );
	$margin = intval( $margin );

	$posts = array();
	if ( $type == 'selected' ) {
		$posts = lordcros_core_get_posts_from_id( $post_ids );
	} else {
		$posts = lordcros_core_get_recent_posts( $count );
	}

	$html = '';
	
	if ( ! empty( $posts ) ) {
		ob_start();
		?>
		<div id="<?php echo esc_attr( $shortcode_posts_id ); ?>" class="lordcros-shortcode-element lordcros-shortcode-posts <?php echo esc_attr( $posts_classes ); ?>" style="animation-delay: <?php echo esc_attr( $animation_delay ); ?>s;">
				
			<?php if ( ! empty( $title ) ) : ?>
				<h2 class="shortcode-title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $short_description ) ) : ?>
				<p class="shortcode-description"><?php echo esc_html( $short_description ); ?></p>
			<?php endif; ?>

			<div class="lordcros-shortcode-posts-inner <?php echo esc_attr( $slider_class ); ?>" data-margin-val="<?php echo esc_attr( $margin ); ?>" data-col="<?php echo esc_attr( $columns ); ?>">
				<?php
					foreach( $posts as $post_id ) {
						$brief = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
						$brief = wp_trim_words( $brief, 17, '...' );
						?>
						<article class="blog-post-item">
							<div class="post-item-wrap">
								<div class="post-featured-image">
									<a href="<?php echo get_permalink( $post_id ); ?>">
										<?php 
											if ( $style != 'style2' ) {
												echo get_the_post_thumbnail( $post_id, 'lordcros-post-grid' );
											} else {
												echo get_the_post_thumbnail( $post_id, 'lordcros-post-gallery' );
											}
										?>
									</a>
								</div>
								<div class="post-content">
									<?php if ( $style == 'style3' ) : ?>
										<div class="post-content-wrapper">
									<?php endif; ?>

									<div class="post-categories">
										<?php echo get_the_category_list( ', ', '', $post_id ); ?>
									</div>

									<h3 class="post-title">
										<a href="<?php echo get_permalink( $post_id ); ?>"><?php echo get_the_title( $post_id ); ?></a>
									</h3>

									<?php if ( $style != 'style3' ) : ?>
										<div class="post-summary">
											<p class="summary-content"><?php echo $brief; ?></p>
										</div>

										<div class="post-read-more">
											<a href="<?php echo get_permalink( $post_id ); ?>" class="read-more-btn">
												<?php echo esc_html__( 'Read More', 'lordcros-core' ); ?>	
											</a>
										</div>
									<?php endif; ?>

									<?php if ( $style == 'style3' ) : ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						</article>							
						<?php
					}
				?>
			</div>

		</div>
		<?php

		if ( $slider == 'yes' ) {
			lordcros_core_carousel_layout( $shortcode_posts_id, $columns, 800, 1, false, 'no', 'no', 'yes', 0, 0, $margin );
		}
		
		$html = ob_get_clean();
	}

	return $html;

}

add_shortcode( 'lc_posts', 'lordcros_core_shortcode_posts' );

/* WPBakery */
function lordcros_core_vc_shortcode_posts() {

	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'				=> esc_html__( 'Posts', 'lordcros-core' ),
		'base'				=> 'lc_posts',
		'icon'				=> 'lordcros-js-composer',
		'category'			=> esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'		=> esc_html__( 'Add Posts on your page.', 'lordcros-core' ),
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
				'type'			=> 'lordcros_image_selection',
				'heading'		=> esc_html__( 'Post Style', 'lordcros-core' ),
				'param_name'	=> 'style',
				'value'			=> array(
					__( 'Style 1', 'lordcros-core' )	=> 'style1',
					__( 'Style 2', 'lordcros-core' )	=> 'style2',
					__( 'Style 3', 'lordcros-core' )	=> 'style3',
				),
				'image_value'	=> array(
					'style1'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/post-style-1.jpg',
					'style2'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/post-style-2.jpg',
					'style3'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/post-style-3.jpg',
				),
				'std'			=> 'style1',
				'save_always'	=> true,
				'admin_label'	=> true,
			),
			array(
				'type'			=> 'dropdown',
				'heading'		=> __( 'Enable Slider?', 'lordcros-core' ),
				'param_name'	=> 'slider',
				'value'			=> array(
					__( 'Yes', 'lordcros-core' )	=> 'yes',
					__( 'No', 'lordcros-core' )		=> 'no',
				),
				'std'			=> 'no',
				'admin_label'	=> false,
			),
			array(
				"type"			=> 'dropdown',
				"heading"		=> esc_html__( 'Type', 'lordcros-core' ),
				"param_name"	=> 'type',
				"value"			=> array(
					__( 'Latest Posts', 'lordcros-core' )	=> 'latest',
					__( 'Selected Posts', 'lordcros-core' )	=> 'selected',
				),
				"std"			=> 'latest',
				'admin_label'	=> true
			),
			array(
				'type'			=> 'autocomplete',
				'heading'		=> esc_html__( 'Post IDs', 'lordcros-core' ),
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
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Post Count', 'lordcros-core' ),
				'param_name'	=> 'count',
				'save_always'	=> true,
				'dependency'	=> array(
					'element'		=> 'type',
					'value'			=> array( 'latest', 'featured' )
				),
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
				'save_always'	=>	true
			),
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Margin Gap', 'lordcros-core' ),
				'param_name'	=> 'margin',
				'save_always'	=> true,
				'std'			=> '10',
				'description'	=> esc_html__( 'Unit: px', 'lordcros-core' ),
			),
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Custom CSS', 'lordcros-core' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'lordcros-core' )
			),
			$animation_style,
			$animation_delay,
			$extra_class
		)
	) );
}

lordcros_core_vc_shortcode_posts();