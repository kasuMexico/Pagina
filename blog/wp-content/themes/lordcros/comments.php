<?php
/**
 * Comment Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) :
	?>

	<p class="no-comments">
		<?php echo esc_html__('This post is password protected. Enter the password to view comments.', 'lordcros'); ?>
	</p>

	<?php

	return;
endif;


if ( have_comments() ) : ?>
	<div class="post-block post-comments-inner clearfix" id="comments">

		<h2 class="comment-title"><?php
			printf( _nx( 'Comment (1)', 'Comments (%1$s)', get_comments_number(), 'comments title', 'lordcros' ),
				number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
			?>
		</h2>

		<ul class="comments">
			<?php
				// Comments list
				wp_list_comments( array(
					'short_ping'	=> true,
					'avatar_size'	=> 80,
					'callback'		=> 'lordcros_comment'
				) );
			?>
		</ul>

		<?php
		// Are there comments to navigate through?
		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<div class="clearfix">
				<div class="pagination" role="navigation">
					<?php paginate_comments_links() ?>
				</div>
			</div>
		<?php endif; // Check for comment navigation ?>

		<?php if ( ! comments_open() && get_comments_number() ) : ?>
			<p class="no-comments"><?php echo esc_html__( 'Comments are closed.' , 'lordcros' ); ?></p>
		<?php endif; ?>
	</div>
<?php endif; // have_comments() ?>

<?php

$comments_args = array(
	// change the title of the reply section
	'title_reply'   => esc_html__( 'Comentarios', 'lordcros' ),
	// remove "Text or HTML to be displayed after the set of comment fields"
	'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x( 'Tu comentario', 'noun', 'lordcros' ) . '</label><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
);

comment_form($comments_args);
