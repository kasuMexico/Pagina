<?php
/*
 Template Name: Login Page Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Get Page Classes
$page_classes = array();
$page_id = lordcros_get_page_id();
$page_width = get_post_meta( $page_id, 'lordcros_page_width', true );

if ( $page_width == 'full' ) {
	$page_classes[] = 'container-fluid';
} else {
	$page_classes[] = 'container';
}

$page_classes = implode( ' ', $page_classes );

lordcros_page_heading();

$login_url = strtok( filter_input( INPUT_SERVER, 'REQUEST_URI' ), '?' );
$redirect_url_on_login = '';
if ( ! empty( lordcros_get_opt( 'redirect_page' ) ) ) {
    $redirect_url_on_login = get_permalink( lordcros_get_opt( 'redirect_page' ) );
}
?>

<div class="main-content <?php echo esc_attr( $page_classes ); ?>">
	<div class="page-content-inner">
		<div class="row entry-content">
			<!-- Loop Setting -->
			<?php 
				while ( have_posts() ) : the_post();

					if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'lostpassword' ) ) :
					?>

						<div id="login" class="sign-form-wrapper col-md-6">
							<h2 class="form-title"><?php echo esc_html__( 'Forgot Password', 'lordcros' ); ?></h2>

							<form name="lostpasswordform" class="login-form" action="<?php echo esc_url( wp_lostpassword_url() ) ?>" method="post">
								<div class="form-group">
									<input type="text" name="user_login"  class="input-text input-large full-width" placeholder="<?php echo esc_html__( 'Username or E-mail:', 'lordcros' ); ?>" value="" size="20"></label>
								</div>
								<button type="submit" class="btn-large full-width sky-blue1"><?php echo esc_html__( 'Get New Password', 'lordcros' ); ?></button>
								<input type="hidden" name="redirect_to" value="<?php echo esc_url( add_query_arg( 'checkemail', 'confirm', $login_url ) ); ?>">
								<p><br />
									<a href="<?php echo esc_url( $login_url );?>" class="underline"><?php echo esc_html__( 'Login', 'lordcros' ); ?></a> | 
									<a href="<?php echo esc_url( $signup_url ); ?>" class="underline"><?php echo esc_html__( "Sign Up", 'lordcros' ) ?></a>
								</p>
							</form>
						</div>

					<?php else : ?>

						<div id="login" class="sign-form-wrapper col-md-6">
							<h2 class="form-title"><?php echo esc_html__( 'Login', 'lordcros' ); ?></h2>

							<form name="loginform" class="login-form" action="<?php echo esc_url( wp_login_url() ); ?>" method="post">
								<?php if ( ! empty( $_GET['login'] ) && ( $_GET['login'] == 'failed' ) ) { ?>
									<p class="form-description error"><?php echo esc_html__( 'Invalid username or password', 'lordcros' ); ?></p>
								<?php } ?>

								<p class="form-description">
									<?php if ( isset( $_GET['checkemail'] ) ) {
										echo esc_html__( 'Check your e-mail for the confirmation link.', 'lordcros' );
									} else {
										echo esc_html__( 'Please login to your account.', 'lordcros' );
									} ?>
								</p>

								<div class="form-group">
									<label for="username"><?php echo esc_html__( 'Username', 'lordcros' ); ?></label>
									<input type="text" name="log" class="form-control" placeholder="<?php echo esc_html__( 'Username', 'lordcros' ); ?>" value="<?php echo empty($_GET['user']) ? '' : esc_attr( $_GET['user'] ) ?>">
								</div>

								<div class="form-group">
									<label for="password"><?php echo esc_html__( 'Password', 'lordcros' ); ?></label>
									<input type="password" name="pwd" class="form-control" placeholder="<?php echo esc_html__( 'Password', 'lordcros' ); ?>">
								</div>

								<div class="form-group">
									<div class="remember-password">
										<input type="checkbox" name="rememberme" tabindex="3" value="forever" id="rememberme">
										<label for="rememberme"><?php echo esc_html__( 'Remember my details', 'lordcros' ); ?></label>
									</div>

									<div class="forgot-password">
										<a href="<?php echo esc_url( add_query_arg( 'action', 'lostpassword', $login_url ) ); ?>"><?php echo esc_html__( 'Forgot password?', 'lordcros' ); ?></a>
									</div>
								</div>

								<button type="submit" class="lordcros-btn primary-clr rectangle-style medium">
									<span><?php echo esc_html__( 'Login', 'lordcros'); ?></span>
									<i class="lordcros lordcros-arrow-right"></i>
								</button>

								<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_url_on_login ) ?>">

							</form>
						</div>

					<?php endif; ?>

					<div id="registration" class="sign-form-wrapper col-md-6">
						<h2 class="form-title"><?php echo esc_html__( 'Register', 'lordcros' ); ?></h2>

						<?php echo do_shortcode( '[lc_user_registration]' ); ?>
					</div>

					<?php
				endwhile;
			?>
			<!-- Resetting the page Loop -->
		</div>
	</div>
</div>

<?php get_footer(); ?>