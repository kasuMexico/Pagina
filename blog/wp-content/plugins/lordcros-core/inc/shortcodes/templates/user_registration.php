<?php
/**
 * Button Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lc_user_registration]
function lordcros_core_shortcode_user_registration() {
	ob_start();
	lordcros_core_user_registration();

	return ob_get_clean();
}

add_shortcode( 'lc_user_registration', 'lordcros_core_shortcode_user_registration' );

function lordcros_core_user_registration() {
	// sanitize user form input
	global $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio;
	
	if ( isset( $_POST['submit'] ) ) {
		lordcros_core_registration_validation(
									$_POST['username'],
									$_POST['password'],
									$_POST['email'],
									$_POST['website'],
									$_POST['fname'],
									$_POST['lname'],
									$_POST['nickname'],
									$_POST['bio']
								);
		
		$username = sanitize_user( $_POST['username'] );
		$password = esc_attr( $_POST['password'] );
		$email = sanitize_email( $_POST['email'] );
		$website = esc_url( $_POST['website'] );
		$first_name = sanitize_text_field( $_POST['fname'] );
		$last_name = sanitize_text_field( $_POST['lname'] );
		$nickname = sanitize_text_field( $_POST['nickname'] );
		$bio = esc_textarea( $_POST['bio'] );

		// call @function lordcros_core_complete_registration to create the user
		// only when no WP_error is found
		lordcros_core_complete_registration(
								$username,
								$password,
								$email,
								$website,
								$first_name,
								$last_name,
								$nickname,
								$bio
							);
	}
 
	lordcros_core_registration_form(
						$username,
						$password,
						$email,
						$website,
						$first_name,
						$last_name,
						$nickname,
						$bio
					);
}

function lordcros_core_complete_registration() {
	global $reg_errors, $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio;
	if ( 1 > count( $reg_errors->get_error_messages() ) ) {
		$userdata = array(
							'user_login'	=> $username,
							'user_email'	=> $email,
							'user_pass'		=> $password,
							'user_url'		=> $website,
							'first_name'	=> $first_name,
							'last_name'		=> $last_name,
							'nickname'		=> $nickname,
							'description'	=> $bio,
						);
		$user = wp_insert_user( $userdata );
		echo esc_html__( 'Registration complete. Please login', 'lordcros-core' );   
	}
}

function lordcros_core_registration_form( $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio ) {
	?>
		<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<div class="form-group username-field">
				<label for="username"><?php echo esc_html__( 'Username', 'lordcros-core' ); ?> <strong>*</strong></label>
				<input type="text" name="username" value="<?php echo isset( $_POST['username'] ) ? $username : null; ?>">
			</div>
		 
			<div class="form-group password-field">
				<label for="password"><?php echo esc_html__( 'Password', 'lordcros-core' ); ?> <strong>*</strong></label>
				<input type="password" name="password" value="<?php echo isset( $_POST['password'] ) ? $password : null; ?>">
			</div>
		 
			<div class="form-group email-field">
				<label for="email"><?php echo esc_html__( 'Email', 'lordcros-core' ); ?> <strong>*</strong></label>
				<input type="text" name="email" value="<?php echo isset( $_POST['email'] ) ? $email : null; ?>">
			</div>
		 
			<div class="form-group website-field">
				<label for="website"><?php echo esc_html__( 'Website', 'lordcros-core' ); ?></label>
				<input type="text" name="website" value="<?php echo isset( $_POST['website'] ) ? $website : null; ?>">
			</div>
		 
			<div class="form-group firstname-field">
				<label for="firstname"><?php echo esc_html__( 'First Name', 'lordcros-core' ); ?></label>
				<input type="text" name="fname" value="<?php echo isset( $_POST['fname'] ) ? $first_name : null; ?>">
			</div>
		 
			<div class="form-group lastname-field">
				<label for="website"><?php echo esc_html__( 'Last Name', 'lordcros-core' ); ?></label>
				<input type="text" name="lname" value="<?php echo isset( $_POST['lname'] ) ? $last_name : null; ?>">
			</div>
		 
			<div class="form-group nickname-field">
				<label for="nickname"><?php echo esc_html__( 'Nickname', 'lordcros-core' ); ?></label>
				<input type="text" name="nickname" value="<?php echo isset( $_POST['nickname'] ) ? $nickname : null; ?>">
			</div>
		 
			<div class="form-group description-field">
				<label for="bio"><?php echo esc_html__( 'About', 'lordcros-core' ); ?></label>
				<textarea name="bio"><?php echo isset( $_POST['bio']) ? $bio : null; ?></textarea>
			</div>

			<!-- <input type="submit" name="submit" value="<?php echo esc_attr__( 'Register', 'lordcros-core' ); ?>"/> -->
			<button type="submit" name="submit" class="lordcros-btn secondary-clr rectangle-style medium">
				<span><?php esc_html_e( 'Register', 'lordcros'); ?></span>
				<i class="lordcros lordcros-arrow-right"></i>
			</button>
		</form>
	<?php
}

function lordcros_core_registration_validation( $username, $password, $email, $website, $first_name, $last_name, $nickname, $bio ) {
	global $reg_errors;
	$reg_errors = new WP_Error;

	if ( empty( $username ) || empty( $password ) || empty( $email ) ) {
		$reg_errors->add( 'field', esc_html__( 'Required form field is missing', 'lordcros-core' ) );
	}

	if ( 4 > strlen( $username ) ) {
		$reg_errors->add( 'username_length', esc_html__( 'Username too short. At least 4 characters is required', 'lordcros-core' ) );
	}

	if ( username_exists( $username ) ) {
		$reg_errors->add( 'user_name', esc_html__( 'Sorry, that username already exists!', 'lordcros-core' ) );
	}

	if ( ! validate_username( $username ) ) {
		$reg_errors->add( 'username_invalid', esc_html__( 'Sorry, the username you entered is not valid', 'lordcros-core' ) );
	}

	if ( 5 > strlen( $password ) ) {
		$reg_errors->add( 'password', esc_html__( 'Password length must be greater than 5', 'lordcros-core' ) );
	}

	if ( ! is_email( $email ) ) {
		$reg_errors->add( 'email_invalid', esc_html__( 'Email is not valid', 'lordcros-core' ) );
	}

	if ( email_exists( $email ) ) {
		$reg_errors->add( 'email', esc_html__( 'Email Already in use', 'lordcros-core' ) );
	}

	if ( ! empty( $website ) ) {
		if ( ! filter_var( $website, FILTER_VALIDATE_URL ) ) {
			$reg_errors->add( 'website', esc_html__( 'Website is not a valid URL', 'lordcros-core' ) );
		}
	}

	if ( is_wp_error( $reg_errors ) ) {
	    foreach ( $reg_errors->get_error_messages() as $error ) {
	        echo '<p class="form-description error">';
	        echo '<strong>' . esc_html__( 'ERROR', 'lordcros-core' ) . '</strong>:';
	        echo $error;
	        echo '</p>';
	    }
	}
}