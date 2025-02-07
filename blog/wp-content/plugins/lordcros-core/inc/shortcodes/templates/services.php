<?php
/**
 * Services Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lc_services]
function lordcros_core_shortcode_services( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'title'					=>	'',
		'short_description'		=>	'',
		'style'					=>	'style1', //style1, style2, style3, style4, style5, style6, style7
		'type'					=>	'latest', //latest, featured, selected
		'post_ids'				=>	'',
		'count'					=>	3,
		'columns'				=>	1,
		'margin'				=>	10,
		'title_color_scheme'	=>	'dark', //dark, light
		'extra_class'			=>	'',
		'animation'				=>	'',
		'animation_delay'		=>	1,
		'css'					=>	'',
	), $atts, 'lordcros-services' ) );

	$id = rand( 100, 9999 );
	$shortcode_services_id = uniqid( 'lordcros-services-' . $id );

	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	$types = array( 'latest', 'featured', 'selected' );
		
	if ( ! in_array( $type, $types ) ) {
		$type = 'latest';
	}
	$post_ids = explode( ',', $post_ids );
	if ( empty( $count ) || ( ! is_numeric( $count ) ) ) {
		$count = 3;	
	}
	if ( empty( $columns ) || ( ! is_numeric( $columns ) ) ) {
		$columns = 1;
	}
	$margin = intval( $margin );
	if ( $margin > 30 ) {
		$margin = 30;
	}

	$service_ids = array();
	if ( $type == 'selected' ) {
		$service_ids = lordcros_core_get_services_from_id( $post_ids );
	} else {
		$service_ids = lordcros_core_get_special_services( $type, $count );
	}

	$wrapper_class = "lordcros-shortcode-element lordcros-shortcode-services " . esc_attr( $style ) . ' ' . esc_attr( $extra_class );
	if ( ! empty( $title_color_scheme ) ) {
		$wrapper_class .= " color-scheme-" . $title_color_scheme;
	}
	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$wrapper_class .= ' ' .vc_shortcode_custom_css_class( $css );
	}
	$html = '';
	
	if ( ! empty( $service_ids ) ) {
		ob_start();
		?>
		<div id="<?php echo esc_attr( $shortcode_services_id ); ?>" class="<?php echo esc_attr( $wrapper_class ) . ' ' . esc_attr( $animation_classes ); ?>" style="animation-delay: <?php echo esc_attr( $animation_delay ); ?>s;">
					
			<?php if ( $style == 'style1' ) : ?>
				
				<?php if ( ! empty( $title ) ) : ?>
					<h2 class="shortcode-title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $short_description ) ) : ?>
					<p class="shortcode-description"><?php echo '' . $short_description; ?></p>
				<?php endif; ?>

				<div class="lordcros-shortcode-services-wrapper" data-col="<?php echo esc_attr( $columns ); ?>" data-margin-val="<?php echo esc_attr( $margin ); ?>">
					<?php
					foreach ( $service_ids as $service_id ) {
				
						$title = get_the_title( $service_id );
						$permalink = get_permalink( $service_id );
						$brief = apply_filters( 'the_content', get_post_field( 'post_content', $service_id ) );
						$brief = wp_trim_words( $brief, 17, '...' );

						?>
						<div class="service-item">
							<div class="service-item-wrap">
								<div class="service-thumbs">
									<a href="<?php echo esc_url( $permalink ); ?>" class="service-featured-img">
										<?php echo get_the_post_thumbnail( $service_id, 'lordcros-service-list' ); ?>

										<span class="featured-icon">
											<?php
											$service_icon = rwmb_meta( 'lordcros_service_icon_class', '', $service_id );
											if ( empty( $service_icon ) ) {
												$service_icon = '';
											}
											$icon_images = rwmb_meta( 'lordcros_service_icon_image', array( 'limit' => 1 ), $service_id );
											if ( ! empty( $icon_images ) ) {
												$image = reset( $icon_images );
												?>
												<img src="<?php echo esc_attr( $image['url'] ); ?>">
												<?php
											} else {
											?>
												<i class="<?php echo esc_attr( $service_icon ); ?>"></i>
											<?php
											}
											?>
										</span>
									</a>					
								</div>
								<div class="service-infobox">
									<h3 class="service-title">
										<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
									</h3>
								
									<div class="brief">
										<?php echo esc_html( $brief ); ?>
									</div>

									<a href="<?php echo esc_url( $permalink ); ?>" class="service-read-more"><?php echo esc_html__( 'Read More', 'lordcros-core' ); ?></a>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>

			<?php elseif ( $style == 'style2' ) : ?>
				
				<?php if ( ! empty( $title ) ) : ?>
					<h2 class="shortcode-title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $short_description ) ) : ?>
					<p class="shortcode-description"><?php echo '' . $short_description; ?></p>
				<?php endif; ?>

				<div class="lordcros-shortcode-services-wrapper">
					<?php
					foreach ( $service_ids as $service_id ) {
				
						$title = get_the_title( $service_id );
						$permalink = get_permalink( $service_id );
						$brief = apply_filters( 'the_content', get_post_field( 'post_content', $service_id ) );
						$brief = wp_trim_words( $brief, 17, '...' );

						?>
						<div class="service-item">
							<div class="service-thumbs">
								<a href="<?php echo esc_url( $permalink ); ?>" class="service-featured-img">
									<?php echo get_the_post_thumbnail( $service_id, 'lordcros-service-list' ); ?>
								</a>						
							</div>
							<div class="service-infobox">
								<div class="service-infobox-wrap">
									<span class="featured-icon">
										<?php
										$service_icon = rwmb_meta( 'lordcros_service_icon_class', '', $service_id );
										if ( empty( $service_icon ) ) {
											$service_icon = '';
										}
										$icon_images = rwmb_meta( 'lordcros_service_icon_image', array( 'limit' => 1 ), $service_id );
										if ( ! empty( $icon_images ) ) {
											$image = reset( $icon_images );
											?>
											<img src="<?php echo esc_attr( $image['url'] ); ?>">
											<?php
										} else {
										?>
											<i class="<?php echo esc_attr( $service_icon ); ?>"></i>
										<?php
										}
										?>
									</span>

									<h3 class="service-title">
										<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
									</h3>
									
									<div class="brief">
										<?php echo esc_html( $brief ); ?>
									</div>
									<a href="<?php echo esc_url( $permalink ); ?>" class="service-read-more"><?php echo esc_html__( 'Read More', 'lordcros-core' ); ?><i class="lordcros lordcros-arrow-right"></i></a>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>

			<?php elseif ( $style == 'style3' ) : ?>

				<?php if ( ! empty( $title ) ) : ?>
					<h2 class="shortcode-title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $short_description ) ) : ?>
					<p class="shortcode-description"><?php echo '' . $short_description; ?></p>
				<?php endif; ?>

				<div class="lordcros-shortcode-services-wrapper">
					<div class="service-wrap-bg"></div>

					<div class="services-wrap-slide owl-carousel">
						<?php
							$count = count( $service_ids );
							$index = 0;
							foreach ( $service_ids as $service_id ) {
								$index++;
								$title = get_the_title( $service_id );
								$permalink = get_permalink( $service_id );
								$brief = apply_filters( 'the_content', get_post_field( 'post_content', $service_id ) );
								$brief = wp_trim_words( $brief, 17, '...' );

								?>
								<div class="service-item">
									<div class="service-thumbs">
										<a href="<?php echo esc_url( $permalink ); ?>" class="service-featured-img">
											<?php echo get_the_post_thumbnail( $service_id, 'lordcros-service-list' ); ?>
										</a>						
									</div>
									<div class="service-infobox">
										<div class="service-infobox-wrap">
											<div class="service-index">
												<span class="service-number"><?php echo sprintf( "%02d", $index ); ?></span>
												<span class="separator">/</span>
												<span class="total-count"><?php echo sprintf( "%02d", $count ); ?></span>
											</div>
											<h3 class="service-title">
												<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
											</h3>
											<div class="brief">
												<?php echo esc_html( $brief ); ?>
											</div>
											<a href="<?php echo esc_url( $permalink ); ?>" class="service-read-more"><?php echo esc_html__( 'Read More', 'lordcros-core' ); ?><i class="lordcros lordcros-arrow-right"></i></a>
										</div>
									</div>
								</div>
								<?php
							}
						?>
					</div>
				</div>

			<?php elseif ( $style == 'style4' ) : ?>

				<?php if ( ! empty( $title ) ) : ?>
					<h2 class="shortcode-title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $short_description ) ) : ?>
					<p class="shortcode-description"><?php echo '' . $short_description; ?></p>
				<?php endif; ?>

				<div class="lordcros-shortcode-services-wrapper">
					<div class="services-thumbs-wrap">
						<div class="thumbs-slider-inner owl-carousel">
							<?php
								foreach ( $service_ids as $service_id ) {
									$permalink = get_permalink( $service_id );
									?>
										<div class="service-thumbs">
											<a href="<?php echo esc_url( $permalink ); ?>" class="service-featured-img">
												<?php echo get_the_post_thumbnail( $service_id, 'lordcros-service-large-gallery' ); ?>
											</a>						
										</div>
									<?php
								}
							?>
						</div>

						<div class="service-slider-nav">
							<button class="slider-prev"><i class="lordcros lordcros-arrow-left"></i></button>
							<button class="slider-next"><i class="lordcros lordcros-arrow-right"></i></button>
						</div>
					</div>

					<div class="services-infoboxes-wrap owl-carousel">
						<?php
							foreach ( $service_ids as $service_id ) {
								$title = get_the_title( $service_id );
								$permalink = get_permalink( $service_id );
								$brief = apply_filters( 'the_content', get_post_field( 'post_content', $service_id ) );
								$brief = wp_trim_words( $brief, 14, '...' );
								?>
									<div class="service-infobox">
										<div class="service-infobox-wrap">
											<span class="featured-icon">
												<?php
												$service_icon = rwmb_meta( 'lordcros_service_icon_class', '', $service_id );
												if ( empty( $service_icon ) ) {
													$service_icon = '';
												}
												$icon_images = rwmb_meta( 'lordcros_service_icon_image', array( 'limit' => 1 ), $service_id );
												if ( ! empty( $icon_images ) ) {
													$image = reset( $icon_images );
													?>
													<img src="<?php echo esc_attr( $image['url'] ); ?>">
													<?php
												} else {
												?>
													<i class="<?php echo esc_attr( $service_icon ); ?>"></i>
												<?php
												}
												?>
											</span>

											<h3 class="service-title">
												<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
											</h3>
											
											<div class="brief">
												<?php echo esc_html( $brief ); ?>
											</div>
											
											<a href="<?php echo esc_url( $permalink ); ?>" class="service-read-more"><?php echo esc_html__( 'Read More', 'lordcros-core' ); ?></a>
										</div>
									</div>
								<?php
							}
						?>
					</div>
				</div>

			<?php elseif ( $style == 'style5' ) : ?>
				<div class="lordcros-shortcode-services-wrapper tab-wrapper">
					<div class="tab-content">
						<?php foreach ( $service_ids as $service_id ) : ?>
							<div id="img-<?php echo esc_attr( $service_id ); ?>" class="service-thumbs tab-pane fade">
								<a href="<?php echo esc_url( get_permalink( $service_id ) ); ?>"  class="service-featured-img">
									<?php echo get_the_post_thumbnail( $service_id, 'lordcros-service-extra-large-gallery' ); ?>
								</a>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="service-infobox">
						<div class="service-infobox-wrap">
							<?php if ( ! empty( $title ) ) : ?>
								<h2 class="shortcode-title"><?php echo esc_html( $title ); ?></h2>
							<?php endif; ?>

							<?php if ( ! empty( $short_description ) ) : ?>
								<p class="shortcode-description"><?php echo '' . $short_description; ?></p>
							<?php endif; ?>

							<ul class="nav nav-tabs service-titles">
								<?php foreach ( $service_ids as $service_id ) : ?>
									<li class="tab-pane-title">
										<a data-toggle="tab" href="#img-<?php echo esc_attr( $service_id ); ?>" id="title-<?php echo esc_attr( $service_id ); ?>">
											<?php echo get_the_title( $service_id ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				</div>

			<?php elseif ( $style == 'style6' ) : ?>

				<?php if ( ! empty( $title ) ) : ?>
					<h2 class="shortcode-title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $short_description ) ) : ?>
					<p class="shortcode-description"><?php echo '' . $short_description; ?></p>
				<?php endif; ?>

				<div class="lordcros-shortcode-services-wrapper" data-col="<?php echo esc_attr( $columns ); ?>" data-margin-val="<?php echo esc_attr( $margin ); ?>">
					<?php
					foreach ( $service_ids as $service_id ) {
				
						$title = get_the_title( $service_id );
						$permalink = get_permalink( $service_id );
						
						?>
						<div class="service-item">
							<div class="service-thumbs">
								<a href="<?php echo esc_url( $permalink ); ?>" class="service-featured-img">
									<?php echo get_the_post_thumbnail( $service_id, 'lordcros-post-gallery' ); ?>
								</a>

								<div class="service-title">							
									<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>

			<?php else : ?>

				<?php if ( ! empty( $title ) ) : ?>
					<h2 class="shortcode-title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $short_description ) ) : ?>
					<p class="shortcode-description"><?php echo '' . $short_description; ?></p>
				<?php endif; ?>

				<div class="lordcros-shortcode-services-wrapper" data-col="<?php echo esc_attr( $columns ); ?>" data-margin-val="<?php echo esc_attr( $margin ); ?>">
					<?php
					foreach ( $service_ids as $service_id ) {
						$title = get_the_title( $service_id );
						$permalink = get_permalink( $service_id );
						
						?>
						<div class="service-item">
							<div class="service-thumbs">
								<a href="<?php echo esc_url( $permalink ); ?>" class="service-featured-img">
									<?php echo get_the_post_thumbnail( $service_id, 'lordcros-service-thumb' ); ?>
								</a>						
							</div>
							<div class="service-infobox">
								<span class="featured-icon">
									<?php
									$service_icon = rwmb_meta( 'lordcros_service_icon_class', '', $service_id );
									if ( empty( $service_icon ) ) {
										$service_icon = '';
									}
									$icon_images = rwmb_meta( 'lordcros_service_icon_image', array( 'limit' => 1 ), $service_id );
									if ( ! empty( $icon_images ) ) {
										$image = reset( $icon_images );
										?>
										<img src="<?php echo esc_attr( $image['url'] ); ?>">
										<?php
									} else {
									?>
										<i class="<?php echo esc_attr( $service_icon ); ?>"></i>
									<?php
									}
									?>
								</span>

								<h3 class="service-title">
									<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
								</h3>
							</div>
						</div>
						<?php
					}
					?>
				</div>

			<?php endif; ?>

		</div>
		<?php
		$html = ob_get_clean();
	}

	return $html;
}

add_shortcode( 'lc_services', 'lordcros_core_shortcode_services' );

/* WPBakery */
function lordcros_core_vc_shortcode_services() {

	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'				=> esc_html__( 'Services', 'lordcros-core' ),
		'base'				=> 'lc_services',
		'icon'				=> 'lordcros-js-composer',
		'category'			=> esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'		=> esc_html__( 'Add Services on your page.', 'lordcros-core' ),
		'params'			=> array(
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Title', 'lordcros-core' ),
				'param_name'	=> 'title',
				'save_always'	=> true,
				'admin_label'	=> true,
			),
			array(
				'type'			=> 'textarea',
				'heading'		=> esc_html__( 'Description', 'lordcros-core' ),
				'param_name'	=> 'short_description',
				'save_always'	=> true,
			),
			array(
				'type'			=> 'lordcros_image_selection',
				'heading'		=> esc_html__( 'Service Style', 'lordcros-core' ),
				'param_name'	=> 'style',
				'value'			=> array(
					__( 'Style 1', 'lordcros-core' )	=> 'style1',
					__( 'Style 2', 'lordcros-core' )	=> 'style2',
					__( 'Style 3', 'lordcros-core' )	=> 'style3',
					__( 'Style 4', 'lordcros-core' )	=> 'style4',
					__( 'Style 5', 'lordcros-core' )	=> 'style5',
					__( 'Style 6', 'lordcros-core' )	=> 'style6',
					__( 'Style 7', 'lordcros-core' )	=> 'style7',
				),
				'image_value'	=> array(
					'style1'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/service-style-1.jpg',
					'style2'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/service-style-2.jpg',
					'style3'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/service-style-3.jpg',
					'style4'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/service-style-4.jpg',
					'style5'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/service-style-5.jpg',
					'style6'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/service-style-6.jpg',
					'style7'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/service-style-7.jpg',
				),
				'std'			=> 'style1',
				'save_always'	=> true,
				'admin_label'	=> true,
			),
			array(
				"type"			=> 'dropdown',
				"heading"		=> esc_html__( 'Type', 'lordcros-core' ),
				"param_name"	=> 'type',
				"value"			=> array(
					__( 'Latest Services', 'lordcros-core' )	=> 'latest',
					__( 'Featured Services', 'lordcros-core' )	=> 'featured',
					__( 'Selected Services', 'lordcros-core' )	=> 'selected',
				),
				"std"			=> 'latest',
				'admin_label'	=> true
			),
			array(
				'type'			=> 'autocomplete',
				'heading'		=> esc_html__( 'Service IDs', 'lordcros-core' ),
				'param_name'	=> 'post_ids',
				'settings'		=> array(
					'multiple'		=> true,
					'sortable'		=> true,
				),
				'save_always'	=> true,
				'admin_label'	=> true,
				'dependency'	=> array(
					'element'		=> 'type',
					'value'			=> array( 'selected' )
				),
			),
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Count', 'lordcros-core' ),
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
				'dependency'	=> array(
					'element'		=> 'style',
					'value'			=> array( 'style1', 'style6', 'style7' )
				),
			),
			array(
				'type'			=> 'textfield',
				'heading'		=> esc_html__( 'Item Margin', 'lordcros-core' ),
				'param_name'	=> 'margin',
				'save_always'	=> true,
				'std'			=> '10',
				'description'	=> esc_html__( 'Unit: px, Max value: 30', 'lordcros-core' ),
				'dependency'	=> array(
					'element'		=> 'style',
					'value'			=> array( 'style1', 'style6', 'style7' )
				),
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

lordcros_core_vc_shortcode_services();