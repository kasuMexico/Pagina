<?php
/**
 * LordCros Admin Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LordCros_Admin_Pages' ) ) {

	class LordCros_Admin_Pages {
		/**
		 * Construct
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'lordcros_theme_admin_menu' ) );
			add_action( 'admin_menu', array( $this, 'lordcros_theme_system_status' ) );
			add_action( 'admin_menu', array( $this, 'lordcros_theme_import_menu' ) );
			add_action( 'after_switch_theme', array( $this, 'activation_redirect' ) );
		}

		public function admin_init() {
			if ( current_user_can( 'edit_theme_options' ) ) {
	            if ( isset( $_GET['lordcros-deactivate'] ) && 'deactivate-plugin' == $_GET['lordcros-deactivate'] ) {
	                check_admin_referer( 'lordcros-deactivate', 'lordcros-deactivate-nonce' );

	                $plugins = TGM_Plugin_Activation::$instance->plugins;

	                foreach ( $plugins as $plugin ) {
	                    if ( $plugin['slug'] == $_GET['plugin'] ) {
	                        deactivate_plugins( $plugin['file_path'] );
	                    }
	                }
				} 

				if ( isset( $_GET['lordcros-activate'] ) && 'activate-plugin' == $_GET['lordcros-activate'] ) {
	                check_admin_referer( 'lordcros-activate', 'lordcros-activate-nonce' );

	                $plugins = TGM_Plugin_Activation::$instance->plugins;

	                foreach ( $plugins as $plugin ) {
	                    if ( isset( $_GET['plugin'] ) && $plugin['slug'] == $_GET['plugin'] ) {
	                        activate_plugin( $plugin['file_path'] );

	                        wp_redirect( admin_url( 'admin.php?page=lordcros_theme' ) );
	                        exit;
	                    }
	                }
	            }

				$php_version = phpversion();
				if ( version_compare( $php_version, '5.4', '<' ) ) { 
					add_action( 'admin_notices', array( $this, 'php_low_version_notice' ) );
				}
			}
		}

		function php_low_version_notice() {
			$html = '<div class="notice notice-error">';
			$html .= '<h2>' . esc_html__( 'LordCros Theme Notification', 'lordcros' ) . '</h2>';
			$html .= '<p>' . esc_html__( 'Current PHP version is ', 'lordcros' ) . phpversion() . '.</p>';
			$html .= '<p><strong>' . esc_html__( 'We recommend a minimum PHP version of 5.4', 'lordcros' ) . '.</strong></p>';
			$html .= '</div>';

			echo '' . $html;
		}

		function activation_redirect() {
			if ( current_user_can( 'edit_theme_options' ) ) {
				header( 'Location:' . admin_url() . 'admin.php?page=lordcros_theme' );
			}
		}

		function lordcros_theme_admin_menu() {
			add_theme_page(
				lordcros_parent_theme_name(),
				lordcros_parent_theme_name(),
				'administrator',
				'lordcros_theme',
				array( $this, 'theme_welcome_page' )
			);
		}

		function theme_welcome_page() {
			require_once( LORDCROS_ADMIN . '/lordcros-welcome.php' );
		}

		function lordcros_theme_system_status() {
			add_theme_page(
				esc_html__( 'System Status', 'lordcros' ),
				esc_html__( 'System Status', 'lordcros' ),
				'administrator',
				'system_status',
				array( $this, 'system_status_page' )
			);
		}

		function system_status_page() {
			require_once( LORDCROS_ADMIN . '/system-status.php' );
		}

		function lordcros_theme_import_menu() {
			add_theme_page(
				esc_html__( 'Demo Import', 'lordcros' ),
				esc_html__( 'Demo Import', 'lordcros' ),
				'administrator',
				'demo_import',
				array( $this, 'lordcros_demo_import' )
			);
		}

		function lordcros_demo_import() {
			require_once( LORDCROS_ADMIN . '/demo-import.php' );
		}

		public function plugin_link( $item ) {
			$installed_plugins = get_plugins();

			$item['sanitized_plugin'] = $item['name'];

			$actions = array();

			// We have a repo plugin
			if ( ! $item['version'] ) {
				$item['version'] = TGM_Plugin_Activation::$instance->does_plugin_have_update( $item['slug'] );
			}

			/** We need to display the 'Install' hover link */
			if ( ! isset( $installed_plugins[$item['file_path']] ) ) {
				$actions = array(
					'install' => sprintf(
						'<a href="%1$s" class="button button-primary" title="' . esc_attr__( 'Install', 'lordcros' ) .' %2$s">' . esc_html__( 'Install', 'lordcros' ) . '</a>',
						esc_url( wp_nonce_url(
							add_query_arg(
								array(
									'page'			=> urlencode( TGM_Plugin_Activation::$instance->menu ),
									'plugin'		=> urlencode( $item['slug'] ),
									'plugin_name'	=> urlencode( $item['sanitized_plugin'] ),
									'plugin_source'	=> urlencode( $item['source'] ),
									'tgmpa-install'	=> 'install-plugin',
									'return_url'	=> 'lordcros_theme',
								),
								TGM_Plugin_Activation::$instance->get_tgmpa_url()
							),
							'tgmpa-install',
							'tgmpa-nonce'
						) ),
						$item['sanitized_plugin']
					),
				);
			}
			/** We need to display the 'Activate' hover link */
			elseif ( is_plugin_inactive( $item['file_path'] ) ) {
				$actions = array(
					'activate' => sprintf(
						'<a href="%1$s" class="button button-primary" title="' . esc_attr__( 'Activate', 'lordcros' ) . ' %2$s">' . esc_html__( 'Activate', 'lordcros' ) . '</a>',
						esc_url( add_query_arg(
							array(
								'plugin'					=> urlencode( $item['slug'] ),
								'plugin_name'				=> urlencode( $item['sanitized_plugin'] ),
								'plugin_source'				=> urlencode( $item['source'] ),
								'lordcros-activate'			=> 'activate-plugin',
								'lordcros-activate-nonce'	=> wp_create_nonce( 'lordcros-activate' ),
							),
							admin_url( 'admin.php?page=lordcros_theme' )
						) ),
						$item['sanitized_plugin']
					),
				);
			}
			/** We need to display the 'Update' hover link */
			elseif ( version_compare( $installed_plugins[$item['file_path']]['Version'], $item['version'], '<' ) ) {
				$actions = array(
					'update' => sprintf(
						'<a href="%1$s" class="button button-primary" title="' . esc_attr__( 'Update', 'lordcros' ) . ' %2$s">' . esc_html__( 'Update', 'lordcros' ) . '</a>',
						wp_nonce_url(
							add_query_arg(
								array(
									'page'			=> urlencode( TGM_Plugin_Activation::$instance->menu ),
									'plugin'		=> urlencode( $item['slug'] ),
									'tgmpa-update'	=> 'update-plugin',
									'plugin_source'	=> urlencode( $item['source'] ),
									'version'		=> urlencode( $item['version'] ),
									'return_url'	=> 'lordcros_theme',
								),
								TGM_Plugin_Activation::$instance->get_tgmpa_url()
							),
							'tgmpa-update',
							'tgmpa-nonce'
						),
						$item['sanitized_plugin']
					),
				);
			} elseif ( class_exists( $item['check_str'] ) || function_exists( $item['check_str'] ) ) {
				$actions = array(
					'deactivate' => sprintf(
						'<a href="%1$s" class="button button-primary" title="' . esc_attr__( 'Deactivate', 'lordcros' ) . ' %2$s">' . esc_html__( 'Deactivate', 'lordcros' ) . '</a>',
						esc_url( add_query_arg(
							array(
								'plugin' 					=> urlencode( $item['slug'] ),
								'plugin_name'				=> urlencode( $item['sanitized_plugin'] ),
								'plugin_source'				=> urlencode( $item['source'] ),
								'lordcros-deactivate'		=> 'deactivate-plugin',
								'lordcros-deactivate-nonce'	=> wp_create_nonce( 'lordcros-deactivate' ),
							),
							admin_url( 'admin.php?page=lordcros_theme' )
						) ),
						$item['sanitized_plugin']
					),
				);
			}

			return $actions;
		}
	}

	new LordCros_Admin_Pages;

}