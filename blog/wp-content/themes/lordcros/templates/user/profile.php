<?php
$user_id = get_current_user_id();
$user_info = lordcros_get_current_user_info();
$photo = lordcros_get_avatar( array( 'id' => $user_id, 'email' => $user_info['email'], 'size' => 270 ) );
$_countries = lordcros_core_get_all_countries();
?>
<h2 class="tab-content-title"><?php echo esc_html__( 'Profile', 'lordcros' ); ?></h2>

<div class="profile-block-wrap">
	<div class="block-content-inner view-profile">
		<h3 class="block-title"><?php echo esc_html__( 'View Profile', 'lordcros' ); ?></h3>

		<div class="view-profile-inner">
			<div class="profile-head-part">
				<figure class="profile-photo"><?php echo wp_kses_post( $photo ) ?></figure>

				<div class="head-right-part">
					<h4 class="fullname"><?php echo esc_html( $user_info['display_name'] ); ?></h4>
					<a href="#" class="lordcros-btn primary-clr rectangle-style small edit-profile-btn">
						<?php echo esc_html__( 'EDIT PROFILE', 'lordcros' ); ?>
					</a>
				</div>
			</div>

			<ul class="profile-info-list">
				<?php 
				if ( ! empty( $user_info['login'] ) ) { 
					?>
					<li class="single-list">
						<label class="list-label"><?php echo esc_html__( 'user name', 'lordcros' ) ?>:</label>
						<span class="list-val"><?php echo esc_html( $user_info['login'] ); ?></span>
					</li>
					<?php
				} 
				?>

				<?php 
				if ( ! empty( $user_info['first_name'] ) ) { 
					?>
					<li class="single-list">
						<label class="list-label"><?php echo esc_html__( 'first name', 'lordcros' ) ?>:</label>
						<span class="list-val"><?php echo esc_html( $user_info['first_name'] ); ?></span>
					</li>
					<?php 
				} 
				?>

				<?php 
				if ( ! empty( $user_info['last_name'] ) ) { 
					?>
					<li class="single-list">
						<label class="list-label"><?php echo esc_html__( 'last name', 'lordcros' ) ?>:</label>
						<span class="list-val"><?php echo esc_html( $user_info['last_name'] ); ?></span>
					</li>
					<?php 
				} 
				?>

				<?php 
				if ( ! empty( $user_info['phone'] ) ) { 
					?>
					<li class="single-list">
						<label class="list-label"><?php echo esc_html__( 'phone number', 'lordcros' ) ?>:</label>
						<span class="list-val"><?php echo esc_html( $user_info['phone'] ); ?></span>
					</li>
					<?php 
				} 
				?>

				<?php 
				if ( ! empty( $user_info['birthday'] ) ) { 
					?>
					<li class="single-list">
						<label class="list-label"><?php echo esc_html__( 'Date of birth', 'lordcros' ) ?>:</label>
						<span class="list-val"><?php echo esc_html( $user_info['birthday'] ); ?></span>
					</li>
					<?php 
				} 
				?>

				<?php 
				if ( ! empty( $user_info['address'] ) ) { 
					?>
					<li class="single-list">
						<label class="list-label"><?php echo esc_html__( 'Street Address and number', 'lordcros' ) ?>:</label>
						<span class="list-val"><?php echo esc_html( $user_info['address'] ); ?></span>
					</li>
					<?php 
				} 
				?>

				<?php 
				if ( ! empty( $user_info['city'] ) ) { 
					?>
					<li class="single-list">
						<label class="list-label"><?php echo esc_html__( 'Town / City', 'lordcros' ) ?>:</label>
						<span class="list-val"><?php echo esc_html( $user_info['city'] ); ?></span>
					</li>
					<?php 
				} 
				?>

				<?php 
				if ( ! empty( $user_info['zip'] ) ) { 
					?>
					<li class="single-list">
						<label class="list-label"><?php echo esc_html__( 'ZIP code', 'lordcros' ) ?>:</label>
						<span class="list-val"><?php echo esc_html( $user_info['zip'] ); ?></span>
					</li>
					<?php 
				} 
				?>

				<?php 
				if ( ! empty( $user_info['country'] ) ) { 
					?>
					<li class="single-list">
						<label class="list-label"><?php echo esc_html__( 'Country', 'lordcros' ) ?>:</label>
						<span class="list-val"><?php echo esc_html( $user_info['country'] ); ?></span>
					</li>
					<?php 
				} 
				?>
			</ul>

			<div class="profile-intro">
				<h5 class="profile-intro-title"><?php echo esc_html__( 'About You', 'lordcros' ); ?></h5>

				<p class="intro-txt"><?php echo esc_html( $user_info['description'] ); ?></p>
			</div>
		</div>
	</div>

	<div class="block-content-inner edit-profile">
		<h3 class="block-title"><?php echo esc_html__( 'Edit Profile', 'lordcros' ); ?></h3>

		<div class="edit-profile-inner">
			<form class="edit-profile-form" method="post" enctype='multipart/form-data'>
				<input type="hidden" name="action" value="update_profile">

				<?php wp_nonce_field( 'update_profile' ); ?>

				<div class="form-control-wrap">
					<div class="form-group photo-upload">
						<div id="photo_preview" class="photo-box-wrap"<?php if ( empty( $user_info['photo_url'] ) ) { echo ' style="display:none"'; } ?>>
							<input type="hidden" name="remove_photo">
							<span class="close"><i class="lordcros lordcros-cancel"></i></span>
							<img src="<?php echo esc_url( $user_info['photo_url'] ); ?>" alt="your photo">
						</div>

						<div class="photo-upload-right-part">							
							<div class="fileinput">
								<label for="photo"><?php echo esc_html__( 'Browse Photo', 'lordcros' ); ?></label>
								<input name="photo" type="file" id="photo" class="input-text" data-placeholder="select image/s" accept="image/*">
							</div>

							<a href="#" class="lordcros-btn primary-clr rectangle-style small view-profile-btn">
								<?php echo esc_html__( 'VIEW PROFILE', 'lordcros' ); ?>
							</a>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-sm-6">
							<label><?php echo esc_html__( 'First Name', 'lordcros' ); ?></label>
							<input name="first_name" type="text" class="input-text" placeholder="" value="<?php echo esc_attr( $user_info['first_name'] ); ?>">
						</div>
						<div class="col-sm-6">
							<label><?php echo esc_html__( 'Last Name', 'lordcros' ); ?></label>
							<input name="last_name" type="text" class="input-text" placeholder="" value="<?php echo esc_attr( $user_info['last_name'] ); ?>">
						</div>
					</div>
					<div class="row form-group">
						<div class="col-sm-6">
							<label><?php echo esc_html__( 'Email Address', 'lordcros' ); ?></label>
							<input name="email" type="email" class="input-text" placeholder="" value="<?php echo esc_attr( $user_info['email'] ); ?>">
						</div>
						<div class="col-sm-6">
							<label><?php echo esc_html__( 'Date of Birth', 'lordcros' ); ?></label>
							<div class="datepicker-wrap birthday-datepicker">
								<input name="birthday" type="text" placeholder="" class="input-text" value="<?php echo esc_attr( $user_info['birthday'] );?>">
							</div>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-sm-6">
							<label><?php echo esc_html__( 'Country', 'lordcros' ); ?></label>
							<div class="selector">
								<select name="country" class=">
									<?php foreach ( $_countries as $_country ) {
										$selected = '';
										if ( $_country['name'] == $user_info['country'] ) $selected = "selected"; ?>
											<option <?php echo wp_kses_post( $selected ); ?> value="<?php echo esc_attr( $_country['code'] ); ?>"><?php echo esc_html( $_country['name'] ); ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="col-sm-6">
							<label><?php echo esc_html__( 'Phone Number', 'lordcros' ); ?></label>
							<input name="phone" type="text" class="input-text" placeholder="" value="<?php echo esc_attr( $user_info['phone'] ); ?>">
						</div>
					</div>
					<div class="row form-group">
						<div class="col-sm-6">
							<label><?php echo esc_html__( 'Address', 'lordcros' ); ?></label>
							<input name="address" type="text" class="input-text" value="<?php echo esc_attr( $user_info['address'] ); ?>">
						</div>
						<div class="col-sm-6">
							<label><?php echo esc_html__( 'City', 'lordcros' ); ?></label>
							<input name="city" type="text" class="input-text" value="<?php echo esc_attr( $user_info['city'] ); ?>">
						</div>
					</div>
					<div class="row form-group">
						<div class="col-sm-6">
							<label><?php echo esc_html__( 'ZIP', 'lordcros' ); ?></label>
							<input name="zip" type="text" class="input-text" value="<?php echo esc_attr( $user_info['zip'] ); ?>">
						</div>
						<div class="col-sm-6">
							
						</div>
					</div>
					<div class="form-group">
						<label><?php echo esc_html__( 'Profile Description', 'lordcros' ); ?></label>
						<textarea name="description" rows="5" class="input-text" placeholder="please tell us about you"><?php echo esc_textarea( $user_info['description'] ); ?></textarea>
					</div>
					<div class="from-group">
						<button type="submit" class="lordcros-btn primary-clr rectangle-style medium"><?php echo esc_html__( 'UPDATE SETTINGS', 'lordcros' ); ?></button>
					</div>
				</div>
			</form>
		</div>	
	</div>
</div>