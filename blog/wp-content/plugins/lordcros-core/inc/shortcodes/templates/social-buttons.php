<?php
/**
 * Social Buttons Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lc_social_buttons]
function lordcros_core_shortcode_social_buttons( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'type'				=>	'share',
		'btn_size'			=>	'medium',
		'btn_shape'			=>	'square',
		'btn_style'			=>	'default',
		'icon_clr'			=>	'#787d8b',
		'icon_bg_clr'		=>	'#dbdbdb',
		'extra_class'		=>	''
	), $atts, 'lordcros-social-buttons' ) );

	$id = rand( 100, 9999 );
	$social_btn_id = uniqid( 'lordcros-social-btn-' . $id );

	$target = '_blank';
	$styles = '';
	$social_btn_classes = 'lordcros-social-buttons';
	$social_btn_classes .= ' button-size-' . $btn_size;
	$social_btn_classes .= ' button-shape-' . $btn_shape;
	$social_btn_classes .= ' button-style-' . $btn_style;

	if ( ! empty( $extra_class ) ) {
		$social_btn_classes .= ' ' . $extra_class;
	}

	if ( 'hover_colored' == $btn_style ) {
		$icon_bg_clr = $icon_bg_clr;
	} else {
		$icon_bg_clr = 'transparent';
	}

	$page_link = get_the_permalink();

	if ( is_home() && ! is_front_page() ) {
		$page_link = get_permalink( get_option( 'page_for_posts' ) );	
	}

	$styles .= '#' . $social_btn_id . ' .lordcros-social-button a {';
	$styles .= 'background-color: ' . $icon_bg_clr . ';';
	$styles .= 'color: ' . $icon_clr . ';';
	$styles .= '}';

	ob_start();

	?>
		<div id="<?php echo esc_attr( $social_btn_id ); ?>" class="<?php echo esc_attr( $social_btn_classes ); ?>">
			<?php if ( ( 'follow' == $type && lordcros_get_opt( 'facebook_link' ) ) || ( 'share' == $type && lordcros_get_opt( 'facebook_share' ) ) ) : ?>
				<div class="lordcros-social-button facebook-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'facebook_link' ) ) : 'https://www.facebook.com/sharer/sharer.php?u=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-facebook-f"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Facebook', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ( 'follow' == $type && lordcros_get_opt( 'twitter_link' ) ) || ( 'share' == $type && lordcros_get_opt( 'twitter_share' ) ) ) : ?>
				<div class="lordcros-social-button twitter-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'twitter_link' ) ) : 'https://twitter.com/share?url=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-twitter"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Twitter', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ( 'follow' == $type && lordcros_get_opt( 'google_link' ) ) || ( 'share' == $type && lordcros_get_opt( 'google_share' ) ) ) : ?>
				<div class="lordcros-social-button google-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'google_link' ) ) : 'https://plus.google.com/share?url=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-google-plus-g"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Google+', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( 'follow' == $type && lordcros_get_opt( 'instagram_link' ) ) : ?>
				<div class="lordcros-social-button instagram-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'instagram_link' ) ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-instagram"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Instagram', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ( 'follow' == $type && lordcros_get_opt( 'pinterest_link' ) ) || ( 'share' == $type && lordcros_get_opt( 'pinterest_share' ) ) ) : ?>
				<div class="lordcros-social-button pinterest-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'pinterest_link' ) ) : 'https://pinterest.com/pin/create/button/?url=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-pinterest-p"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Pinterest', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( 'follow' == $type && lordcros_get_opt( 'youtube_link' ) ) : ?>
				<div class="lordcros-social-button youtube-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'youtube_link' ) ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-youtube"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Youtube', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ( 'follow' == $type && lordcros_get_opt( 'linkedin_link' ) ) || ( 'share' == $type && lordcros_get_opt( 'linkedin_share' ) ) ) : ?>
				<div class="lordcros-social-button linkedin-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'linkedin_link' ) ) : 'https://www.linkedin.com/shareArticle?mini=true&amp;url=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-linkedin-in"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'LinkedIn', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( 'follow' == $type && lordcros_get_opt( 'vimeo_link' ) ) : ?>
				<div class="lordcros-social-button vimeo-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'vimeo_link' ) ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-vimeo-v"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Vimeo', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( 'follow' == $type && lordcros_get_opt( 'tumblr_link' ) ) : ?>
				<div class="lordcros-social-button tumblr-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'tumblr_link' ) ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-tumblr"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Tumblr', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( 'follow' == $type && lordcros_get_opt( 'flickr_link' ) ) : ?>
				<div class="lordcros-social-button flickr-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'flickr_link' ) ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-flickr"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Flickr', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( 'follow' == $type && lordcros_get_opt( 'github_link' ) ) : ?>
				<div class="lordcros-social-button github-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'github_link' ) ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-github"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'GitHub', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ( 'follow' == $type && lordcros_get_opt( 'vk_link' ) ) || ( 'share' == $type && lordcros_get_opt( 'vk_share' ) ) ) : ?>
				<div class="lordcros-social-button vk-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'vk_link' ) ) : 'https://vk.com/share.php?url=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-vk"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'VK', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( 'follow' == $type && lordcros_get_opt( 'dribbble_link' ) ) : ?>
				<div class="lordcros-social-button dribbble-icon">
					<a href="<?php echo ( 'follow' == $type ) ? esc_url( lordcros_get_opt( 'dribbble_link' ) ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="fab fa-dribbble"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Dribbble', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( ( 'follow' == $type && lordcros_get_opt( 'social_email' ) ) || ( 'share' == $type && lordcros_get_opt( 'email_share' ) ) ) : ?>
				<div class="lordcros-social-button email-icon">
					<a href="mailto:<?php echo '?subject=' . esc_html__( 'Check this ', 'lordcros-core' ) . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>">
						<i class="far fa-envelope"></i>
						<span class="lordcros-social-icon-name"><?php echo esc_html__( 'Email', 'lordcros-core' ); ?></span>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<script>
			var css = '<?php echo $styles; ?>',
			head = document.head || document.getElementsByTagName('head')[0],
			style = document.createElement('style');

			head.appendChild(style);

			style.type = 'text/css';

			if (style.styleSheet){
				// This is required for IE8 and below.
				style.styleSheet.cssText = css;
			} else {
				style.appendChild(document.createTextNode(css));
			}
		</script>
	<?php

	$html = ob_get_clean();

	return $html;
}

add_shortcode( 'lc_social_buttons', 'lordcros_core_shortcode_social_buttons' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_social_buttons() {
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Social Buttons', 'lordcros-core' ),
		'base'			=>	'lc_social_buttons',
		'icon'			=>	'lordcros-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'	=>	esc_html__( 'Show follow or share social buttons.', 'lordcros-core' ),
		'params'		=>	array(
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Social Button Type', 'lordcros-core' ),
				'param_name'	=>	'type',
				'value'			=>	array(
					esc_html__( 'Share', 'lordcros-core' )	=>	'share',
					esc_html__( 'Follow', 'lordcros-core' )	=>	'follow'
				),
				'std'			=>	'share'
			),

			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Button Size', 'lordcros-core' ),
				'param_name'	=>	'btn_size',
				'value'			=>	array(
					esc_html__( 'Small', 'lordcros-core' )	=>	'small',
					esc_html__( 'Medium', 'lordcros-core' )	=>	'medium',
					esc_html__( 'Large', 'lordcros-core' )	=>	'large'
				),
				'std'			=>	'medium'
			),

			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Button Shape', 'lordcros-core' ),
				'param_name'	=>	'btn_shape',
				'value'			=>	array(
					esc_html__( 'Circle', 'lordcros-core' )	=>	'circle',
					esc_html__( 'Square', 'lordcros-core' )	=>	'square'
				),
				'std'			=>	'square'
			),

			array(
				'type'			=>	'lordcros_image_selection',
				'heading'		=>	esc_html__( 'Button Style', 'lordcros-core' ),
				'param_name'	=>	'btn_style',
				'value'			=>	array(
					esc_html__( 'Default', 'lordcros-core' )		=>	'default',
					esc_html__( 'Hover Colored', 'lordcros-core' )	=>	'hover_colored',
					esc_html__( 'Hover Bordered', 'lordcros-core' )	=>	'hover_bordered'
				),
				'image_value'	=>	array(
					'default'			=>	LORDCROS_CORE_PLUGIN_URL . '/inc/images/social-button-1.jpg',
					'hover_colored'		=>	LORDCROS_CORE_PLUGIN_URL . '/inc/images/social-button-2.jpg',
					'hover_bordered'	=>	LORDCROS_CORE_PLUGIN_URL . '/inc/images/social-button-3.jpg'
				),
				'std'			=>	'default'
			),

			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Icon Color', 'lordcros-core' ),
				'param_name'	=>	'icon_clr',
				'std'			=>	'#787d8b'
			),

			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Icon Background Color', 'lordcros-core' ),
				'param_name'	=>	'icon_bg_clr',
				'description'	=>	esc_html__( 'This field works only in Hover Colored button style.', 'lordcros-core' ),
				'std'			=>	'#dbdbdb'
			),

			$extra_class
		)
	) );
}

lordcros_core_vc_shortcode_social_buttons();