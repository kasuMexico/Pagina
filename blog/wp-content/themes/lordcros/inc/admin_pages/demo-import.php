<?php
/**
 * Demo Import Admin Page
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

$demos = array();
$i = 1;

$demo_names = array(
	'demo1'		=>	array( 'alt' => 'LordCros Demo 1', 'url' => 'https://lordcros.c-themes.com/demo1' ),
	'demo2'		=>	array( 'alt' => 'LordCros Demo 2', 'url' => 'https://lordcros.c-themes.com/demo2' ),
	'demo3'		=>	array( 'alt' => 'LordCros Demo 3', 'url' => 'https://lordcros.c-themes.com/demo3' ),
	'demo4'		=>	array( 'alt' => 'LordCros Demo 4', 'url' => 'https://lordcros.c-themes.com/demo4' ),
	'demo5'		=>	array( 'alt' => 'LordCros Demo 5', 'url' => 'https://lordcros.c-themes.com/demo5' ),
	'demo6'		=>	array( 'alt' => 'LordCros Demo 6', 'url' => 'https://lordcros.c-themes.com/demo6' ),
	'demo7'		=>	array( 'alt' => 'LordCros Demo 7', 'url' => 'https://lordcros.c-themes.com/demo7' ),
	'demo8'		=>	array( 'alt' => 'LordCros Demo 8', 'url' => 'https://lordcros.c-themes.com/demo8' ),
	'demo9'		=>	array( 'alt' => 'LordCros Demo 9', 'url' => 'https://lordcros.c-themes.com/demo9' ),
	'demo10'	=>	array( 'alt' => 'LordCros Demo 10', 'url' => 'https://lordcros.c-themes.com/demo10' ),
);

foreach ( $demo_names as $key => $demo_name ) {
	$demos[$key] = array(
		'title'	=>	$demo_name['alt'],
		'url'	=>	$demo_name['url'],
		'img'	=>	LORDCROS_URI . '/images/demos/demo' . $i . '.jpg'
	);

	$i++;
}

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
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=system_status' ) ); ?>" class="nav-tab"><?php echo esc_html__( 'System Status', 'lordcros' ); ?></a>
		<a href="#" class="nav-tab nav-tab-active"><?php echo esc_html__( 'Demo Import', 'lordcros' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=theme_options' ) ); ?>" class="nav-tab"><?php echo esc_html__( 'Theme Options', 'lordcros' ); ?></a>
	</div>

	<div class="lordcros-demo-import">
		<?php
			$import_ready = true;
			$plugins_required = true;

			$memory_limit = intval( substr( ini_get( 'memory_limit' ), 0, -1 ) );
			if ( $memory_limit < 256 ) {
				$import_ready = false;
			}

			$execution_time = intval( ini_get( 'max_execution_time' ) );
			if ( $execution_time < 30 ) {
				$import_ready = false;	
			}

			$upload_size = intval( substr( size_format( wp_max_upload_size() ), 0, -1 ) );
			$size_unit = preg_replace( '/[0-9]+/', '', substr( size_format( wp_max_upload_size() ), 0, -1) );
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

			foreach ( $plugins as $plugin ) {
				if ( ! class_exists( $plugin['check_str'] ) && ! function_exists( $plugin['check_str'] ) ) {
					$plugins_required = false;
					break;
				}
			}
		?>

		<div class="theme-browser">
			<?php

			if ( $plugins_required && $import_ready ) {
				?>
				<div class="lordcros-admin-alert alert-success">
					<?php echo esc_html__( 'You are ready to import base demo content. Please select one demo, and import dummy content.', 'lordcros' ); ?>
				</div>
				<?php
			} else {
				?>
				<div class="lordcros-admin-alert alert-error">
					<?php
						echo esc_html__( 'You are not ready to import base demo content. Please check', 'lordcros' ) . ' <a href="' . esc_url( admin_url( 'admin.php?page=system_status' ) ) . '">' . esc_html__( 'System Status', 'lordcros' ) . '</a> ' . esc_html__( 'tab. You need to activate all "required plugins" and "Server Environment" tables.', 'lordcros' );
					?>
				</div>
				<?php
			}

			?>

			<div id="lordcros-install-options">
				<h3 class="title"><?php echo esc_html__( 'Install Options', 'lordcros' ); ?></h3>

				<label for="lordcros-import-options"><input type="checkbox" id="lordcros-import-options" value="1" checked="checked"/> <?php echo esc_html__( 'Import theme options', 'lordcros' ); ?></label>
				<input type="hidden" id="lordcros-install-demo-type" value="landing"/>
				<label for="lordcros-reset-menus"><input type="checkbox" id="lordcros-reset-menus" value="1" checked="checked"/> <?php echo esc_html__( 'Reset menus', 'lordcros' ); ?></label>
				<label for="lordcros-reset-widgets"><input type="checkbox" id="lordcros-reset-widgets" value="1" checked="checked"/> <?php echo esc_html__( 'Reset widgets', 'lordcros' ); ?></label>
				<label for="lordcros-import-dummy"><input type="checkbox" id="lordcros-import-dummy" value="1" checked="checked"/> <?php echo esc_html__( 'Import dummy content', 'lordcros' ); ?></label>
				<label for="lordcros-import-widgets"><input type="checkbox" id="lordcros-import-widgets" value="1" checked="checked"/> <?php echo esc_html__( 'Import widgets', 'lordcros' ); ?></label>

				<p><?php echo esc_html__( 'Do you want to install demo? It can also take a minute to complete.', 'lordcros' ); ?></p>

				<button class="button button-primary" id="lordcros-import-yes"><?php echo esc_html__( 'Yes', 'lordcros' ); ?></button>
				<button class="button" id="lordcros-import-no"><?php echo esc_html__( 'No', 'lordcros' ); ?></button>
			</div>

			<div id="import-status"></div>

			<div class="import-success importer-notice">
				<p>
					<?php 
					echo esc_html__( 'The demo content has been imported successfully.', 'lordcros' ) . '<a href="' . site_url() . '" target="_blank">' . esc_html__('View Site', 'lordcros' ) . '</a>'; 
					?> 
				</p>
			</div>

			<div class="import-demo-area">
				<div class="demo-list-inner">
					<?php foreach ( $demos as $demo => $demo_details ) : ?>
						<div class="demo-screenshot">
							<div class="screenshot-inner">
								<img src="<?php echo esc_url( $demo_details['img'] ); ?>" alt="<?php echo esc_attr( $demo ); ?>">
								<div id="<?php echo esc_attr( $demo ); ?>" class="demo-info">
									<?php echo esc_html( $demo_details['title'] ); ?>
								</div>
								<div class="demo-actions">
									<a href="<?php echo esc_url( $demo_details['url'] ) ?>" class="demo-preview-link" target="_blank">
										<?php echo esc_html__( 'Preview', 'lordcros' ); ?>
									</a>

									<?php 
										$disabled = '';

										if ( ! $plugins_required || ! $import_ready ) {
											$disabled = 'disabled=disabled';
										}

										printf( '<a href="#" class="button button-primary lordcros-install-demo-button" data-demo-id="%s" %s>%s</a>', strtolower( $demo ), esc_attr( $disabled ), esc_html__( 'Install Demo', 'lordcros' ) ); 
									?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</div>