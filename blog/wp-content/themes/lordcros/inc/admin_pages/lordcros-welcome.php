<?php
/**
 * Welcome Admin Page
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
$view_totals = array(
	'all'       =>  array(),
	'install'   =>  array(),
	'update'    =>  array(),
	'activate'  =>  array(),
);

foreach ( $plugins as $slug => $plugin ) {
	if ( $tgmpa->is_required_plugin_activated( $slug ) && false === $tgmpa->does_plugin_have_update( $slug ) ) {
		continue;
	} else {
		$view_totals['all'][ $slug ] = $plugin;

		if ( ! $tgmpa->is_plugin_installed( $slug ) ) {
			$view_totals['install'][ $slug ] = $plugin;
		} else {
			if ( false !== $tgmpa->does_plugin_have_update( $slug ) ) {
				$view_totals['update'][ $slug ] = $plugin;
			}

			if ( $tgmpa->can_plugin_activate( $slug ) ) {
				$view_totals['activate'][ $slug ] = $plugin;
			}
		}
	}
}

$all_index = $install_index = $update_index = $activate_index = 0;

foreach ( $view_totals as $type => $count ) {
	$size = sizeof($count);

	if ( $size < 1 ) {
		continue;
	}
	switch ( $type ) {
		case 'all':
			$all_index = $size;
			break;
		case 'install':
			$install_index = $size;
			break;
		case 'update':
			$update_index = $size;
			break;
		case 'activate':
			$activate_index = $size;
			break;
		default:
			break;
	}
}

$installed_plugins = get_plugins();

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
		<a href="#" class="nav-tab nav-tab-active"><?php echo esc_html__( 'Required Plugins', 'lordcros' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=system_status' ) ); ?>" class="nav-tab"><?php echo esc_html__( 'System Status', 'lordcros' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=demo_import' ) ); ?>" class="nav-tab"><?php echo esc_html__( 'Demo Import', 'lordcros' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=theme_options' ) ); ?>" class="nav-tab"><?php echo esc_html__( 'Theme Options', 'lordcros' ); ?></a>
	</div>

	<div class="lordcros-required-plugins">
		<div class="theme-browser">
			<div class="lordcros-admin-alert alert-success">
				<?php echo esc_html__( 'To import base demo content successfully, you need to install all required plugins. After install and activate required plugins, please check "System Status" and import demo in "Demo Import" Tab.', 'lordcros' ); ?>
			</div>

			<?php if ( $install_index > 0 || $update_index > 0 || $activate_index > 0 ) : ?>
				<p class="about-description">
					<?php
					if ($install_index > 0)
						printf( '<a href="%s" class="button-primary">%s</a>', admin_url( 'themes.php?page=install-required-plugins&plugin_status=install' ), esc_html__( "Click here to install plugins all together.", 'lordcros' ) );
					?>

					<?php
					if ($activate_index > 0)
						printf( '<a href="%s" class="button-primary">%s</a>', admin_url( 'themes.php?page=install-required-plugins&plugin_status=activate' ), esc_html__( "Click here to activate plugins all together.", 'lordcros' ) );
					?>

					<?php
					if ($update_index > 0)
						printf( '<a href="%s" class="button-primary">%s</a>', admin_url( 'themes.php?page=install-required-plugins&plugin_status=update' ), esc_html__( "Click here to update plugins all together.", 'lordcros' ) );
					?>
				</p>
			<?php endif; ?>

			<div class="plugin-features feature-section">
				<?php
				
				foreach ( $plugins as $plugin ) {
					$class = '';
					$plugin_status = '';
					$file_path = $plugin['file_path'];
					$plugin_action = $this->plugin_link( $plugin );

					if ( $plugin['required'] ) { 
						$required_plugins[] = $plugin;
					}

					if ( class_exists( $plugin['check_str'] ) || function_exists( $plugin['check_str'] ) ) {
						$plugin_status = 'active';
						$class = 'active';
					}
					?>

					<div class="theme <?php echo esc_attr( $class ); ?>">
						<div class="theme-wrapper">
							<div class="theme-screenshot">
								<img src="<?php echo esc_url( $plugin['image_url'] ); ?>" alt="plugin image" />

								<div class="plugin-info">
									<?php if ( isset( $installed_plugins[ $plugin['file_path'] ] ) ) : ?>
										<?php printf( esc_html__( 'Version: %1s', 'lordcros' ), $installed_plugins[ $plugin['file_path'] ]['Version'] ); ?>
									<?php elseif ( 'bundled' == $plugin['source_type'] ) : ?>
										<?php printf( esc_attr__( 'Available Version: %s', 'lordcros' ), $plugin['version'] ); ?>
									<?php endif; ?>
								</div>
							</div>

							<h3 class="theme-name">
								<?php if ( 'active' == $plugin_status ) : ?>
									<span><?php printf( esc_html__( '%s', 'lordcros' ), $plugin['name'] ); ?></span>
								<?php else : ?>
									<?php echo esc_html( $plugin['name'] ); ?>
								<?php endif; ?>
							</h3>

							<div class="theme-actions">
								<?php foreach ( $plugin_action as $action ) { echo '' . $action; } ?>
							</div>

							<?php if ( isset( $plugin_action['update'] ) && $plugin_action['update'] ) : ?>
								<div class="theme-update">
									<span class="dashicons dashicons-update"></span> <?php printf( esc_html__( 'Update Available: Version %s', 'lordcros' ), $plugin['version'] ); ?>
								</div>
							<?php endif; ?>
							
							<?php if ( isset( $plugin['required'] ) && $plugin['required'] ) : ?>
								<div class="plugin-required">
									<?php echo esc_html__( 'Required', 'lordcros' ); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<?php
				}

				?>
			</div>
		</div>
	</div>
</div>