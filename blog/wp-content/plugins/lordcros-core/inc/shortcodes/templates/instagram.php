<?php
/**
 * Button Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lordcros_core_instagram]
function lordcros_core_shortcode_instagram( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'username'		=>	'wood.interior',
		'view_style'	=>	'grid',
		'number'		=>	10,
		'img_size'		=>	'thumbnail',
		'open_target'	=>	'_self',
		'columns'		=>	5,
		'gap'			=>	'',
		'hide_icons'	=>	'',
		'user_text'		=>	'',
		'extra_class'	=>	''
	), $atts, 'lordcros-core-instagram' ) );
	
	$id = rand( 100, 9999 );
	$shortcode_instagram_id = uniqid( 'lordcros-instagram-' . $id );

	$instagram_class = array();
	$html = $carousel_class = '';

	if ( empty( $username ) ) {
		return;
	}

	$instagram_class[] = 'shortcode-instagram instagram-widget';

	if ( '' != $view_style ) {
		$instagram_class[] = 'instagram-' . $view_style;
	}

	$instagram_class[] = 'instagram-per-row-' . $columns;

	if ( 'yes' == $gap ) {
		$instagram_class[] = 'instagram-photo-gap';
	}

	if ( ! empty( $extra_class ) ) {
		$instagram_class[] = $extra_class;
	}

	if ( 'carousel' == $view_style ) {
		$carousel_class = 'owl-carousel';
	}

	ob_start();

	?>

	<div id="<?php echo esc_attr( $shortcode_instagram_id ); ?>" class="<?php echo esc_attr( implode( ' ', $instagram_class ) ); ?>">
		<?php
			$instagram_items = lordcros_core_scrape_instagram( $username, $number );
			
			if ( is_wp_error( $instagram_items ) )	{
				echo esc_html__( $instagram_items->get_error_message() );
			} else {
				?>

				<div class="instagram-photos <?php echo esc_attr( $carousel_class ); ?>">
					<?php

					foreach ( $instagram_items as $item ) :
						$image = $item[$img_size];
						?>
							<div class="instagram-picture">
								<div class="wrapp-picture">
									<a href="<?php echo esc_url( $item['link'] ) ?>" target="<?php echo esc_attr( $open_target ); ?>"></a>
									<img src="<?php echo esc_url( $image ) ?>">

									<?php if ( 'yes' != $hide_icons ) : ?>
										<div class="hover-mask shortcode-mask-inner">
											<span class="instagram-comments"><span><?php echo lordcros_core_number_beautifier( $item['comments'] ) ?></span></span>
											<span class="instagram-likes"><span><?php echo lordcros_core_number_beautifier( $item['likes'] ) ?></span></span>
											<span class="instagram-description"><?php echo $item['description']; ?></span>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php
					endforeach;

					?>
				</div>

				<?php
			}

			if ( ! empty( $content ) ) {
				?>

				<div class="instagram-text-wrap">
					<div class="text-inner">
						<?php echo do_shortcode( $content ); ?>
					</div>
				</div>

				<?php
			}

			if ( ! empty( $user_text ) ) {
				?>

				<div class="user-link-text">
					<a href="//instagram.com/<?php echo trim( $username ) ?>" rel="me" target="<?php echo esc_attr( $open_target ) ?>"><?php echo esc_html( $user_text ); ?></a>
				</div>

				<?php
			}

			if ( 'carousel' == $view_style ) {
				lordcros_core_carousel_layout( $shortcode_instagram_id, $columns, 600 );
			}
		?>
	</div>

	<?php

	$html .= ob_get_clean();

	return $html;
}

add_shortcode( 'lc_instagram', 'lordcros_core_shortcode_instagram' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_instagram() {
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Instagram', 'lordcros-core' ),
		'base'			=>	'lc_instagram',
		'icon'			=>	'lordcros-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'	=>	esc_html__( 'Show instagram photos.', 'lordcros-core' ),
		'params'		=>	array(
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Username', 'lordcros-core' ),
				'param_name'	=>	'username',
				'admin_label'	=>	true
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'View Style', 'lordcros-core' ),
				'param_name'	=>	'view_style',
				'value'			=>	array(
					esc_html__( 'Grid', 'lordcros-core' )		=>	'grid',
					esc_html__( 'Carousel', 'lordcros-core' )	=>	'carousel'
				),
				'std'			=>	'grid'
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Photo Number to Show', 'lordcros-core' ),
				'param_name'	=>	'number'
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Image Size', 'lordcros-core' ),
				'param_name'	=>	'img_size',
				'value'			=>	array(
					esc_html__( 'Thumbnail', 'lordcros-core' )	=>	'thumbnail',
					esc_html__( 'Medium', 'lordcros-core' )		=>	'medium',
					esc_html__( 'Large', 'lordcros-core' )		=>	'large'
				)
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Open Photo Target', 'lordcros-core' ),
				'param_name'	=>	'open_target',
				'value'			=>	array(
					esc_html__( 'Self', 'lordcros-core' )	=>	'_self',
					esc_html__( 'Blank', 'lordcros-core' )	=>	'_blank'
				)
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Photo Columns', 'lordcros-core' ),
				'description'	=>	esc_html__( 'Set photo columns for grid or carousel layout.', 'lordcros-core' ),
				'param_name'	=>	'columns',
				'value'			=>	array(
					1, 2, 3, 4, 5, 6, 7, 8, 10
				),
				'std'			=>	5
			),
			array(
				'type'			=>	'textarea_html',
				'heading'		=>	esc_html__( 'Instagram Text', 'lordcros-core' ),
				'description'	=>	esc_html__( 'Add text content to show your instagram section.', 'lordcros-core' ),
				'param_name'	=>	'content',				
			),

			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Add Gap Between Photos', 'lordcros-core' ),
				'param_name'	=>	'gap',
				'value'			=>	array(
					esc_html__( 'Yes', 'lordcros-core' )	=>	'yes'
				)
			),
			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Hide Wishlist And Comment Icons', 'lordcros-core' ),
				'param_name'	=>	'hide_icons',
				'value'			=>	array(
					esc_html__( 'Yes', 'lordcros-core' )	=>	'yes'	
				)
			),
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Instagram User Link Text', 'lordcros-core' ),
				'param_name'	=>	'user_text'
			),
			$extra_class
		)
	) );
}

lordcros_core_vc_shortcode_instagram();

/**
 * Get images & links from instagram account
 */
if ( ! function_exists( 'lordcros_core_scrape_instagram' ) ) {
	function lordcros_core_scrape_instagram( $username, $slice = 9 ) {
		$username       = strtolower( $username );
		$by_hashtag     = ( substr( $username, 0, 1 ) == '#' );
		$transient_name = 'instagram-media-new-' . sanitize_title_with_dashes( $username );
		$instagram      = get_transient( $transient_name );
		
		if ( false === $instagram ) {
			$request_param = ( $by_hashtag ) ? 'explore/tags/' . substr( $username, 1 ) : trim( $username );
			$remote        = wp_remote_get( 'https://instagram.com/'. $request_param );
			
			if ( is_wp_error( $remote ) ) {
				return new WP_Error( 'site_down', esc_html__( 'Unable to communicate with Instagram.', 'lordcros-core' ) );
			}

			if ( 200 != wp_remote_retrieve_response_code( $remote ) ) {
				return new WP_Error( 'invalid_response', esc_html__( 'Instagram did not return a 200.', 'lordcros-core' ) );
			}
				
			$shards      = explode( 'window._sharedData = ', $remote['body'] );
			$insta_json  = explode( ';</script>', $shards[1] );
			$insta_array = json_decode( $insta_json[0], TRUE );

			if ( ! $insta_array ){
				return new WP_Error( 'bad_json', esc_html__( 'Instagram has returned invalid data.', 'lordcros-core' ) );
			}
				
			if ( isset( $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] ) ) {
				$images = $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
			} elseif( $by_hashtag && isset( $insta_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'] ) ) {
		        $images = $insta_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'];
		    } else {
				return new WP_Error( 'bad_json_2', esc_html__( 'Instagram has returned invalid data.', 'lordcros-core' ) );
			}

			if ( ! is_array( $images ) ) {
				return new WP_Error( 'bad_array', esc_html__( 'Instagram has returned invalid data.', 'lordcros-core' ) );
			}
				
			$instagram = array();
			
			foreach ( $images as $image ) {
				$image = $image['node'];
				$caption = esc_html__( 'Instagram Image', 'lordcros-core' );

				if ( ! empty( $image['edge_media_to_caption']['edges'][0]['node']['text'] ) ) {
					$caption = $image['edge_media_to_caption']['edges'][0]['node']['text'];
				}

				$image['thumbnail_src'] = preg_replace( "/^https:/i", "", $image['thumbnail_src'] );
				$image['thumbnail']     = preg_replace( "/^https:/i", "", $image['thumbnail_resources'][0]['src'] );
				$image['medium']        = preg_replace( "/^https:/i", "", $image['thumbnail_resources'][2]['src'] );
				$image['large']         = $image['thumbnail_src'];
				
				$type = ( $image['is_video'] ) ? 'video' : 'image';
				
				$instagram[] = array(
					'description'   => $caption,
					'link'		  	=> '//instagram.com/p/' . $image['shortcode'],
					'comments'	  	=> $image['edge_media_to_comment']['count'],
					'likes'		 	=> $image['edge_liked_by']['count'],
					'thumbnail'	 	=> $image['thumbnail'],
					'medium'		=> $image['medium'],
					'large'			=> $image['large'],
					'type'		  	=> $type
				);
			}
		
			// do not set an empty transient - should help catch private or empty accounts
			if ( ! empty( $instagram ) ) {
				$instagram = base64_encode( maybe_serialize( $instagram ) );
				set_transient( $transient_name, $instagram, apply_filters( 'null_instagram_cache_time', HOUR_IN_SECONDS * 2 ) );
			}
		}

		if ( ! empty( $instagram ) ) {
			$instagram = maybe_unserialize( base64_decode( $instagram ) );
			
			return array_slice( $instagram, 0, $slice );
		} else {
			return new WP_Error( 'no_images', esc_html__( 'Instagram did not return any images.', 'lordcros-core' ) );
		}
	}
}

/**
 * Number Reduce Beautifier
 */
if ( ! function_exists( 'lordcros_core_number_beautifier' ) ) {
	function lordcros_core_number_beautifier( $num = 0 ) {
		$num = (int) $num;

		if ( $num > 1000000 ) {
			return floor( $num / 1000000 ) . 'M';
		}

		if ( $num > 10000 ) {
			return floor( $num / 1000 ) . 'k';
		}

		return $num;
	}
}