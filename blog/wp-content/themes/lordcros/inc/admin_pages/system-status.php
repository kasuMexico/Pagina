<?php
/**
 * System Status Admin Page
 */

$theme = wp_get_theme();

if ( $theme->parent_theme ) {

	if ( function_exists( 'get_parent_theme_file_path' ) ) {
		$template_dir = basename( get_parent_theme_file_path() );
	} else {
		$template_dir = basename( get_template_directory() );
	}

	$theme = wp_get_theme( $template_dir );

}

$tgmpa = TGM_Plugin_Activation::$instance;
$plugins = TGM_Plugin_Activation::$instance->plugins;

?>

<div class="wrap about-wrap lordcros-welcome">
	<h1><?php echo esc_html__( 'Welcome to LordCros', 'lordcros' ); ?></h1>

	<div class="about-text">
		<?php echo esc_html__( 'Thank you for purchasing our LordCros - Hotel Booking WordPress Theme. Theme is now installed and ready to use! Please install required plugins, and import our dummy content. We hope you enjoy it!', 'lordcros' ); ?>

		<div class="lordcros-thumb">
			<img src="<?php echo LORDCROS_URI; ?>/images/thumbnail.png" alt="lordcros-thumbnail" />
			<span class="theme-version">
				<?php echo number_format( (int)lordcros_theme_version(), 1 ); ?>
			</span>
		</div>
	</div>

	<div class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=lordcros_theme' ) ); ?>" class="nav-tab"><?php echo esc_html__( 'Required Plugins', 'lordcros' ); ?></a>
		<a href="#" class="nav-tab nav-tab-active"><?php echo esc_html__( 'System Status', 'lordcros' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=demo_import' ) ); ?>" class="nav-tab"><?php echo esc_html__( 'Demo Import', 'lordcros' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=theme_options' ) ); ?>" class="nav-tab"><?php echo esc_html__( 'Theme Options', 'lordcros' ); ?></a>
	</div>

	<div class="lordcros-system-status">

		<?php
			$import_ready = true;

			$memory_limit = intval( substr( ini_get( 'memory_limit' ), 0, -1 ) );
			if ( $memory_limit < 256 ) {
				$import_ready = false;
			}

			$execution_time = intval( ini_get( 'max_execution_time' ) );
			if ( $execution_time < 30 ) {
				$import_ready = false;	
			}

			$upload_size = intval( substr( size_format( wp_max_upload_size() ), 0, -1 ) );
			$size_unit = preg_replace( '/[0-9]+/', '', substr( size_format( wp_max_upload_size() ), 0, -1 ) );
			$upload_size_unit = str_replace( ' ', '', $size_unit );

			if ( 'M' == $upload_size_unit ) {
				if ( $upload_size < 12 ) {
					$import_ready = false;	
				}
			}

			if ( ! ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ) ) {
				$import_ready = false;
				$fsockopen = true;
			}

			$posting['gzip']['name'] = 'GZip';
			if ( ! is_callable( 'gzopen' ) ) {
				$import_ready = false;
			}
		?>

		<table class="system-status-table" cellspacing="0">
			<thead>
				<tr>
					<td><?php echo esc_html__( 'Required Plugins', 'lordcros' ); ?></td>
					<td></td>
				</tr>
			</thead>

			<tbody>
				<?php

				foreach( $plugins as $plugin ) {

					$file_path = $plugin['file_path'];
					$active = false;

					if ( class_exists( $plugin['check_str'] ) || function_exists( $plugin['check_str'] ) ) {
						$active = true;
					}

					?>

					<tr>
						<td><?php echo esc_html( $plugin['name'] ); ?></td>
						<td>
							<?php
							if ( $active ):
								echo '<mark class="activated"><span class="dashicons dashicons-yes"></span> ';
								echo '<span>' . esc_html__( 'Activated', 'lordcros' ) . '</span>';
								echo '</mark>';
							else:
								echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ';
								echo '<span>' . esc_html__( 'Not installed/activated.', 'lordcros' ) . '</span>';
								echo '</mark>';  
							endif;
							?>
						</td>
					</tr>
				<?php

				} ?>
			</tbody>
		</table>

		<table class="system-status-table" cellspacing="0">
			<thead>
				<tr>
					<td><?php echo esc_html__( 'Server Environment', 'lordcros' ); ?></td>
					<td></td>
				</tr>
			</thead>

			<tbody>
				<?php if ( function_exists( 'ini_get' ) ) : ?>
					<tr>
						<td data-export-label="Server Memory Limit">
							<?php echo esc_html__( 'Server Memory Limit', 'lordcros' ); ?>:
						</td>
						<td>
							<?php
								$mark = $memory_limit >= 256 ? 'activated' : 'error';
							?>

							<?php if ( $mark == 'activated' ) : ?>
								<mark class="<?php echo esc_attr( $mark ); ?>">
									<span class="dashicons dashicons-yes"></span><?php echo ini_get( 'memory_limit' ); ?>
								</mark>
							<?php else: ?>
								<mark class="<?php echo esc_attr( $mark ); ?>">
									<span class="dashicons dashicons-warning"></span>  <span><?php echo ini_get( 'memory_limit' ); ?></span>. 
									<?php echo esc_html__( 'The recommended value is 256M.', 'lordcros' ); ?>
								</mark>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<td data-export-label="PHP Time Limit"><?php echo esc_html__( 'PHP Time Limit', 'lordcros' ); ?>:</td>
						<td>
							<?php
								$mark = $execution_time >= 30 ? 'activated' : 'error'; 
							?>

							<?php if ($mark == 'activated'): ?>
								<mark class="<?php echo esc_attr( $mark ); ?>">
									<span class="dashicons dashicons-yes"></span><?php echo ini_get( 'max_execution_time' ); ?>
								</mark>
							<?php else: ?>
								<mark class="<?php echo esc_attr( $mark ); ?>">
									<span class="dashicons dashicons-warning"></span>  <span><?php echo ini_get( 'max_execution_time' ); ?></span>. 
									<?php echo esc_html__( 'The recommended value is 30.', 'lordcros' ); ?>
								</mark>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>

				<tr>
					<td data-export-label="Max Upload Size"><?php echo esc_html__( 'Max Upload Size', 'lordcros' ); ?>:</td>
					<td>
						<?php
							if ( 'M' == $upload_size_unit ) {
								$mark = $upload_size >= 12 ? 'activated' : 'error'; 
							} else {
								$mark = 'activated';
							}
						?>

						<?php if ($mark == 'activated'): ?>
							<mark class="<?php echo esc_attr( $mark ); ?>">
								<span class="dashicons dashicons-yes"></span><?php echo size_format( wp_max_upload_size() ); ?>
							</mark>
						<?php else: ?>
							<mark class="<?php echo esc_attr( $mark ); ?>">
								<span class="dashicons dashicons-warning"></span>  <span><?php echo size_format( wp_max_upload_size() ); ?></span>. 
								<?php echo esc_html__( 'The recommended value is 12M.', 'lordcros' ); ?>
							</mark>
						<?php endif; ?>
					</td>
				</tr>
				
				<?php
					$posting = array();

					// fsockopen/cURL
					$posting['fsockopen_curl']['name'] = 'fsockopen/cURL';
					$posting['fsockopen_curl']['help'] = '<a href="#" class="help_tip" data-tip="' . esc_attr__( 'Payment gateways can use cURL to communicate with remote servers to authorize payments, other plugins may also use it when communicating with remote services.', 'lordcros' ) . '">[?]</a>';

					if (  ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ) ) {
						$posting['fsockopen_curl']['success'] = true;
					} else {
						$posting['fsockopen_curl']['success'] = false;
						$posting['fsockopen_curl']['note']    = esc_html__( 'Disabled', 'lordcros' );
					}

					// GZIP
					$posting['gzip']['name'] = 'GZip';
					
					if ( is_callable( 'gzopen' ) ) {
						$posting['gzip']['success'] = true;
					} else {
						$posting['gzip']['success'] = false;
						$posting['gzip']['note']    = sprintf( __( 'Your server does not support the <a href="%s">gzopen</a> function - this is required to use the GeoIP database from MaxMind. The API fallback will be used instead for geolocation.', 'lordcros' ), 'http://php.net/manual/en/zlib.installation.php' );
					}

					// allow url fopen
					$posting['fopen']['name'] = esc_html__( 'Remote file open', 'lordcros' );

					if ( ini_get( 'allow_url_fopen' ) ) {
						$posting['fopen']['success'] = true;
					} else {
						$posting['fopen']['note']    = esc_html__( 'Disabled', 'lordcros' );
						$posting['fopen']['success'] = false;
					}

					foreach ( $posting as $post ) {
						$mark = ! empty( $post['success'] ) ? 'activated' : 'error';
						?>
						<tr>
							<td data-export-label="<?php echo esc_html( $post['name'] ); ?>"><?php echo esc_html( $post['name'] ); ?>:</td>
							<td>
								<mark class="<?php echo esc_attr( $mark ); ?>">
									<?php echo ! empty( $post['success'] ) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-warning"></span>'; ?> 
									<span><?php echo ! empty( $post['note'] )? $post['note'] : ''; ?></span>
								</mark>
							</td>
						</tr>
						<?php
					}
				?>
			</tbody>
		</table>
	</div>
</div>