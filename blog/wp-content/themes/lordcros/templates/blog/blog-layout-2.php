<?php
/**
 * Loop Blog Layout 2 Template
 */

global $post;

$blog_excerpt = lordcros_get_opt( 'blog_excerpt', 'excerpt' );

$classes = array();
$classes[] = 'blog-post-item';
$classes[] = 'blog-post-loop';

if ( '' == get_the_title() ){
	$classes[] = 'post-no-title';
}

$post_content = '';
$post_content = get_the_content();
$post_content = lordcros_strip_tags( apply_filters( 'the_content', $post_content ) );

if ( 'full' != $blog_excerpt ) { 
	$blog_excerpt_length_by = lordcros_get_opt( 'blog_excerpt_length_by', 'letter' );
	$blog_excerpt_length = intval( lordcros_get_opt( 'blog_excerpt_length', '145' ) );

	if ( 'word' == $blog_excerpt_length_by ) { 
		$post_content = explode( ' ', $post_content, $blog_excerpt_length );

		if ( count( $post_content ) >= $blog_excerpt_length ) {
			array_pop( $post_content );
			$post_content = implode( " ", $post_content ) . '... ';
		} else {
			$post_content = implode( " ", $post_content );
		}
	} else { 
		$post_content = substr( $post_content, 0, $blog_excerpt_length ) . '... ';
	}
}

$author_id = $post->post_author;
$post_link = get_permalink( $post->ID );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>
	<div class="blog-post-item-inner">
		<div class="post-header">
			<div class="post-featured-image">
				<?php
					if ( has_post_thumbnail( $post->ID ) ) {
						?>
						
						<a href="<?php echo get_permalink( $post->ID ); ?>">
							<?php echo get_the_post_thumbnail( $post->ID, 'lordcros-blog-second' ); ?>
						</a>

						<?php
					} else {
						?>
						
						<a href="<?php echo get_permalink( $post->ID ); ?>" class="placeholder-img"></a>

						<?php
					}
				?>
			</div>
			<div class="post-date">
				<span class="year"><?php echo get_the_time( 'Y', $post->ID ); ?></span>
				<span class="month"><?php echo get_the_time( 'M', $post->ID ); ?></span>
			</div>		
		</div>

		<div class="post-content">
			<?php if ( is_sticky( $post->ID ) ) : ?>
				<span class="sticky-post"><?php echo esc_html__( 'Featured', 'lordcros' ); ?></span>
			<?php endif; ?>
		
			<h2 class="post-title">
				<a href="<?php echo get_permalink( $post->ID ); ?>"><?php echo esc_html( $post->post_title ); ?></a>
			</h2>

			<div class="post-meta-description">
				<div class="author-info">
					<i class="lordcros lordcros-profile"></i>
					<a href="<?php echo get_author_posts_url( $author_id ); ?>" class="post-author">
						<?php echo get_the_author_meta( 'display_name', $author_id ); ?>
					</a>
				</div>

				<div class="comment-count">
					<i class="lordcros lordcros-consulting-message"></i>
					<?php comments_number( esc_html__( 'No Comments', 'lordcros' ), esc_html__( '1 Comment', 'lordcros' ), '% ' . esc_html__( 'Comments', 'lordcros' ) ); ?>
				</div>
			</div>

			<div class="post-summary">
				<p class="summary-content"><?php echo '' . $post_content; ?></p>
			</div>

			<div class="post-read-more">
				<a href="<?php echo get_permalink( $post->ID ); ?>">
					<?php echo esc_html__( 'Read More', 'lordcros' ); ?>
					<i class="lordcros lordcros-arrow-right"></i>
				</a>				
			</div>
		</div>
	</div>
</article>