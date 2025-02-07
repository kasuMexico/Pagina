<?php
/**
 * The default template for displaying post content
 */

global $post;

$blog_style = lordcros_get_opt( 'blog_style', 'layout-1' );

lordcros_get_template_part( 'blog/blog', $blog_style );