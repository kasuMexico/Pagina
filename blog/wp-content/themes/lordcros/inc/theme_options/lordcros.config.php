<?php
/**
 * ReduxFramework Sample Config File
 * For full documentation, please visit: http://docs.reduxframework.com/
 */
if ( ! class_exists( 'Redux' ) ) {
	return;
}

//LordCros theme option name. In there, all of the Redux data is stored
$opt_name = "lordcros_theme_options";

// This line is only for altering the demo. Can be easily removed.
$opt_name = apply_filters( 'redux_demo/opt_name', $opt_name );

$theme = wp_get_theme(); // For use with some settings. Not necessary.

$show_admin = true;
$allow_submenu = true;
$page_priority = 58;
$parent_page = '';
$menu_title = esc_html__( 'Theme Options', 'lordcros' );
$menu_type = 'menu';
$display_name = $theme->get( 'Name' );

/*************  START ARGUMENTS  **************/

$args = array(
	'opt_name'             => $opt_name,
	'display_name'         => $display_name,
	'display_version'      => $theme->get( 'Version' ),
	'menu_type'            => $menu_type,
	'allow_sub_menu'       => $allow_submenu,
	'menu_title'           => $menu_title,
	'page_title'           => esc_html__( 'Theme Options', 'lordcros' ),
	'google_api_key'       => '',
	'google_update_weekly' => true,
	'async_typography'     => true,
	'admin_bar'            => $show_admin,
	'admin_bar_icon'       => 'dashicons-admin-settings',
	'admin_bar_priority'   => 90,
	'global_variable'      => '',
	'dev_mode'             => false,
	'update_notice'        => false,
	'customizer'           => false,
	'page_priority'        => $page_priority,
	'page_parent'          => $parent_page,
	'page_permissions'     => 'manage_options',
	'menu_icon'            => '',
	'last_tab'             => '',
	'page_icon'            => 'icon-themes',
	'page_slug'            => 'theme_options',
	'save_defaults'        => true,
	'default_show'         => true,
	'default_mark'         => '',
	'show_import_export'   => true,
	'transient_time'       => 60 * MINUTE_IN_SECONDS,
	'output'               => true,
	'output_tag'           => true,
	'database'             => '',
	'system_info'          => false,

	// HINTS
	'hints'                => array(
		'icon'          => 'el el-question-sign',
		'icon_position' => 'right',
		'icon_color'    => 'lightgray',
		'icon_size'     => 'normal',
		'tip_style'     => array(
			'color'   => 'red',
			'shadow'  => true,
			'rounded' => false,
			'style'   => '',
		),
		'tip_position'  => array(
			'my' => 'top left',
			'at' => 'bottom right',
		),
		'tip_effect'    => array(
			'show' => array(
				'effect'   => 'slide',
				'duration' => '500',
				'event'    => 'mouseover',
			),
			'hide' => array(
				'effect'   => 'slide',
				'duration' => '500',
				'event'    => 'click mouseleave',
			),
		),
	)
);

Redux::setArgs( $opt_name, $args );

/**************  END ARGUMENTS ***************/


/*************  START SECTIONS  **************/
Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'General Settings', 'lordcros' ),
	'id'			=>	'general-settings',
	'icon'			=>	'el el-cogs',
	'fields'		=>	array(
		array(
			'id'			=>	'primary_color',
			'type'			=>	'color',
			'title'			=>	esc_html__( 'Theme Primary Color', 'lordcros' ),
			'default'		=>	'#ff6d5e',
			'transparent'	=>	false
		),

		array(
			'id'			=>	'secondary_color',
			'type'			=>	'color',
			'title'			=>	esc_html__( 'Theme Secondary Color', 'lordcros' ),
			'default'		=>	'#252c41',
			'transparent'	=>	false
		),

		array(
			'id'		=>	'favicon',
			'title'		=>	esc_html__( 'Favicon image', 'lordcros' ),
			'type'		=>	'media',
			'desc'		=>	esc_html__( 'Upload Favicon image (png, ico)', 'lordcros' ),
			'url'		=>	false,
			'default'	=>	array(
				'url'	=>	LORDCROS_URI . '/favicon.png'
			)
		),

		array(
			'id'		=>	'favicon_retina',
			'title'		=>	esc_html__( 'Favicon Retina image', 'lordcros' ),
			'type'		=>	'media',
			'desc'		=>	esc_html__( 'Upload Retina Favicon image (png, ico)', 'lordcros' ),
			'url'		=>	false,
			'default'	=>	array(
				'url'	=>	LORDCROS_URI . '/favicon.png'
			)
		),

		array(
			'id'		=>	'phone_num_val',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Telephone Number', 'lordcros' ),
			'default'	=>	'+1 888 123 4567',
		),

		array(
			'id'		=>	'email_address',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Email Address', 'lordcros' ),
			'default'	=>	'info@lordcros.com',
		),

		array(
			'id'		=>	'address',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Hotel Address', 'lordcros' ),
			'desc'		=>	esc_html__( 'It will be shown in Notification Email, etc.', 'lordcros' ),
			'default'	=>	'',
		),

		array(
			'id'		=>	'google_map_api_key',
			'title'		=>	esc_html__( 'Google Map API Key', 'lordcros' ),
			'type'		=>	'text',
			'subtitle'	=>	wp_kses( __( 'Obtain API key <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">here</a> to use our Google Map VC element.', 'lordcros' ), array(
					'a' => array(
						'href' => array(),
						'target' => array()
					)
			) )
		),

		array(
			'id'		=>	'openweather_map_api_key',
			'title'		=>	esc_html__( 'OpenWeatherMap API Key', 'lordcros' ),
			'type'		=>	'text',
			'subtitle'	=>	wp_kses( __( 'Obtain API key <a href="https://openweathermap.org/price" target="_blank">here</a> to get weather data of your current location.', 'lordcros' ), array(
					'a' => array(
						'href' => array(),
						'target' => array()
					)
			) )
		),

		array(
			'id'		=>	'custom_js',
			'title'		=>	esc_html__( 'Custom JS', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Here is the place to paste your Google Analytics code or any other JS code you might want to add to be loaded in the footer of your website.', 'lordcros' ),
			'type'		=>	'ace_editor',
			'mode'		=>	'javascript',
			'default'	=>	'jQuery(document).ready(function(){});'
		)
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Typography', 'lordcros' ),
	'id'			=>	'typography-setting',
	'icon'			=>	'el el-fontsize',
	'fields'		=>	array(
		array(
			'id'			=> 'typography-body',
			'type'			=> 'typography',
			'title'			=> esc_html__( 'Body Font', 'lordcros' ),
			'subtitle'		=> esc_html__( 'Set typography options for body text font.', 'lordcros' ),
			'google'		=> true,
			'font-backup'	=> false,
			'text-align'	=> false,
			'all_styles'	=> true,
			'default'		=> array(
				'color'			=> '#70778b',
				'font-size'		=> '15px',
				'font-family'	=> 'Roboto',
				'font-weight'	=> '400',
				'line-height'	=> '24px'
			),
		),

		array(
			'id'			=> 'typography-nav',
			'type'			=> 'typography',
			'title'			=> esc_html__( 'Navigation Font', 'lordcros' ),
			'subtitle'		=> esc_html__( 'Set all navigation menu typography.', 'lordcros' ),
			'google'		=> true,
			'font-backup'	=> false,
			'text-align'	=> false,
			'line-height'	=> false,
			'all_styles'	=> true,
			'default'		=> array(
				'color'			=> '#262626',
				'font-weight'	=> '400',
				'font-family'	=> 'Roboto Slab',
				'font-size'		=> '15px'
			),
		),

		array(
			'id'			=> 'typography-h1',
			'type'			=> 'typography',
			'title'			=> esc_html__( 'H1 Font Style', 'lordcros' ),
			'subtitle'		=> esc_html__( 'Set HTML h1 tag typography.', 'lordcros' ),
			'google'		=> true,
			'font-backup'	=> false,
			'text-align'	=> false,
			'all_styles'	=> true,
			'default'		=> array(
				'font-family'	=> 'Roboto Slab',
				'font-size'		=> '24px',
				'font-weight'	=> '400',
				'line-height'	=> '34px',
				'color'			=> '#252c41'
			),
		),

		array(
			'id'			=> 'typography-h2',
			'type'			=> 'typography',
			'title'			=> esc_html__( 'H2 Font Style', 'lordcros' ),
			'subtitle'		=> esc_html__( 'Set HTML h2 tag typography.', 'lordcros' ),
			'google'		=> true,
			'font-backup'	=> false,
			'text-align'	=> false,
			'all_styles'	=> true,
			'default'		=> array(
				'font-family'	=> 'Roboto Slab',
				'font-size'		=> '20px',
				'font-weight'	=> '400',
				'line-height'	=> '28px',
				'color'			=> '#252c41'
			),
		),

		array(
			'id'			=> 'typography-h3',
			'type'			=> 'typography',
			'title'			=> esc_html__( 'H3 Font Style', 'lordcros' ),
			'subtitle'		=> esc_html__( 'Set HTML h3 tag typography.', 'lordcros' ),
			'google'		=> true,
			'font-backup'	=> false,
			'text-align'	=> false,
			'all_styles'	=> true,
			'default'		=> array(
				'font-family'	=> 'Roboto Slab',
				'font-size'		=> '18px',
				'font-weight'	=> '400',
				'line-height'	=> '25px',
				'color'			=> '#252c41'
			),
		),

		array(
			'id'			=> 'typography-h4',
			'type'			=> 'typography',
			'title'			=> esc_html__( 'H4 Font Style', 'lordcros' ),
			'subtitle'		=> esc_html__( 'Set HTML h4 tag typography.', 'lordcros' ),
			'google'		=> true,
			'font-backup'	=> false,
			'text-align'	=> false,
			'all_styles'	=> true,
			'default'		=> array(
				'font-family'	=> 'Roboto Slab',
				'font-size'		=> '16px',
				'font-weight'	=> '400',
				'line-height'	=> '23px',
				'color'			=> '#252c41'
			),
		),

		array(
			'id'			=> 'typography-h5',
			'type'			=> 'typography',
			'title'			=> esc_html__( 'H5 Font Style', 'lordcros' ),
			'subtitle'		=> esc_html__( 'Set HTML h5 tag typography.', 'lordcros' ),
			'google'		=> true,
			'font-backup'	=> false,
			'text-align'	=> false,
			'all_styles'	=> true,
			'default'		=> array(
				'font-family'	=> 'Roboto Slab',
				'font-size'		=> '13px',
				'font-weight'	=> '400',
				'line-height'	=> '18px',
				'color'			=> '#252c41'
			),
		),

		array(
			'id'			=> 'typography-h6',
			'type'			=> 'typography',
			'title'			=> esc_html__( 'H6 Font Style', 'lordcros' ),
			'subtitle'		=> esc_html__( 'Set HTML h6 tag typography.', 'lordcros' ),
			'google'		=> true,
			'font-backup'	=> false,
			'text-align'	=> false,
			'all_styles'	=> true,
			'default'		=> array(
				'font-family'	=> 'Roboto Slab',
				'font-size'		=> '11px',
				'font-weight'	=> '400',
				'line-height'	=> '15px',
				'color'			=> '#252c41'
			),
		)
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Header', 'lordcros' ),
	'id'			=>	'header',
	'icon'			=>	'el el-home',
	'fields'		=>	array(
		array(
			'id'		=>	'header_layout',
			'type'		=>	'image_select',
			'title'		=>	esc_html__( 'Header Layout', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Choose your header layout', 'lordcros' ),
			'options'	=>	array(
				'header-layout-1'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/header-layout1.jpg',
					'alt'			=>	'Header Layout 1'
				),
				'header-layout-2'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/header-layout2.jpg',
					'alt'			=>	'Header Layout 2'
				),
				'header-layout-3'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/header-layout3.jpg',
					'alt'			=>	'Header Layout 3'
				),
				'header-layout-4'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/header-layout4.jpg',
					'alt'			=>	'Header Layout 4'
				),
				'header-layout-5'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/header-layout5.jpg',
					'alt'			=>	'Header Layout 5'
				),
				'header-layout-6'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/header-layout6.jpg',
					'alt'			=>	'Header Layout 6'
				),
				'header-layout-7'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/header-layout7.jpg',
					'alt'			=>	'Header Layout 7'
				),
				'header-layout-8'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/header-layout8.jpg',
					'alt'			=>	'Header Layout 8'
				),
				'header-layout-9'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/header-layout9.jpg',
					'alt'			=>	'Header Layout 9'
				),
				'header-layout-10'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/header-layout10.jpg',
					'alt'			=>	'Header Layout 10'
				)
			),
			'default'	=>	'header-layout-1'
		),

		array(
			'id'		=>	'header_overlap',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Header above the content', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Overlap page content with this header.', 'lordcros' ),
			'default'	=>	false,
			'required'	=>	array(
				'header_layout', 'not', 'header-layout-10'
			)
		),

		array(
			'id'		=>	'mobile_screen_size',
			'type'		=>	'slider',
			'title'		=>	esc_html__( 'Mobile Header Enable Width', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Set width where mobile layout is available.', 'lordcros' ),
			'min'		=>	767,
			'step'		=>	1,
			'max'		=>	1600,
			'default'	=>	960,
			'display'	=>	'text',
			'required'	=>	array(
				'header_layout', 'not', 'header-layout-6'
			)
		),

		array(
			'id'		=>	'topbar_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-sliders-h',
			'raw'		=>	'<h3>' . esc_html__( 'Top Bar Basic Settings', 'lordcros' ) . '</h3>',
			'required'	=>	array(
				'header_layout', 'equals', array( 'header-layout-1', 'header-layout-2', 'header-layout-5', 'header-layout-7', 'header-layout-8', 'header-layout-9', 'header-layout-10' )
			)
		),

		array(
			'id'		=>	'topbar_enable',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable/Disable Top Bar', 'lordcros' ),
			'default'	=>	false,
			'required'	=>	array(
				'header_layout', 'equals', array( 'header-layout-1', 'header-layout-2', 'header-layout-5', 'header-layout-7', 'header-layout-8', 'header-layout-9', 'header-layout-10' )
			)
		),

		array(
			'id'		=>	'topbar_bg_color',
			'type'		=>	'color',
			'title'		=>	esc_html__( 'Top Bar Background Color', 'lordcros' ),
			'default'	=>	'transparent',
			'validate'	=>	'color',
			'required'	=>	array(
				'header_layout', 'equals', array( 'header-layout-1', 'header-layout-2', 'header-layout-5', 'header-layout-7', 'header-layout-8', 'header-layout-9', 'header-layout-10' )
			)
		),

		array(
			'id'			=>	'topbar_txt_color',
			'type'			=>	'color',
			'title'			=>	esc_html__( 'Top Bar Text Color', 'lordcros' ),
			'default'		=>	'#878787',
			'validate'		=>	'color',
			'transparent'	=>	false,
			'required'		=>	array(
				'header_layout', 'equals', array( 'header-layout-1', 'header-layout-2', 'header-layout-5', 'header-layout-7', 'header-layout-8', 'header-layout-9', 'header-layout-10' )
			)
		),

		array(
			'id'		=>	'header_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-sliders-h',
			'raw'		=>	'<h3>' . esc_html__( 'Main Header Settings', 'lordcros' ) . '</h3>'
		),

		array(
			'id'		=>	'header_bg_color',
			'type'		=>	'color',
			'title'		=>	esc_html__( 'Main Header Background Color', 'lordcros' ),
			'default'	=>	'#151b2e',
			'validate'	=>	'color'
		),

		array(
			'id'			=>	'header_txt_color',
			'type'			=>	'color',
			'title'			=>	esc_html__( 'Header Text Color', 'lordcros' ),
			'default'		=>	'#fff',
			'validate'		=>	'color',
			'transparent'	=>	false
		),

		array(
			'id'			=>	'submenu_bg_clr',
			'type'			=>	'color',
			'title'			=>	esc_html__( 'Navigation Submenu Background Color', 'lordcros' ),
			'default'		=>	'#fff',
			'validate'		=>	'color',
			'transparent'	=>	false,
			'required'		=>	array(
				'header_layout', 'equals', array( 'header-layout-1', 'header-layout-2', 'header-layout-5', 'header-layout-7', 'header-layout-8', 'header-layout-9' )
			)
		)
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Logo', 'lordcros' ),
	'id'			=>	'header_logo',
	'subsection'	=>	true,
	'icon'			=>	'fas fa-chevron-right',
	'fields'		=>	array(
		array(
			'id'		=>	'logo',
			'title'		=>	esc_html__( 'Logo Image', 'lordcros' ),
			'desc'		=>	esc_html__( 'Upload your logo image (png, jpg)', 'lordcros' ),
			'type'		=>	'media',
			'url'		=>	false,
			'default'	=>	array(
				'url'	=>	LORDCROS_URI . '/images/logo.png'
			)
		),

		array(
			'id'		=>	'alternative_logo',
			'title'		=>	esc_html__( 'Alternative Sticky Logo', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Used This Logo on the Sticky Header', 'lordcros' ),
			'type'		=>	'media',
			'url'		=>	false,
			'desc'		=>	esc_html__( 'Upload your logo image (png, jpg)', 'lordcros' ),
			'default'	=>	array(
				'url'	=>	LORDCROS_URI . '/images/sticky-logo.png'
			)
		),

		array(
			'id'		=>	'logo_height',
			'title'		=>	esc_html__( 'Logo Height', 'lordcros' ),
			'type'		=>	'slider',
			'default'	=>	69,
			'min'		=>	0,
			'max'		=>	200,
			'step'		=>	1,
			'display'	=>	'text'
		),

		array(
			'id'		=>	'sticky_logo_height',
			'title'		=>	esc_html__( 'Sticky Logo Height', 'lordcros' ),
			'type'		=>	'slider',
			'default'	=>	50,
			'min'		=>	0,
			'max'		=>	200,
			'step'		=>	1,
			'display'	=>	'text'
		)
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Sticky Header', 'lordcros' ),
	'id'			=>	'sticky_header',
	'subsection'	=>	true,
	'icon'			=>	'fas fa-chevron-right',
	'fields'		=>	array(
		array(
			'id'			=>	'sticky_header_setting',
			'title'			=>	esc_html__( 'Sticky Header', 'lordcros' ),
			'type'			=>	'switch',
			'on'			=>	esc_html__( 'Enable', 'lordcros' ),
			'off'			=>	esc_html__( 'Disable', 'lordcros' ),
			'default'		=>	false
		),

		array(
			'id'			=>	'sitcky_header_bg_clr',
			'title'			=>	esc_html__( 'Sticky Header Background Color', 'lordcros' ),
			'type'			=>	'color',
			'default'		=>	'#fff',
			'transparent'	=>	false,
			'validate'		=>	'color'
		),

		array(
			'id'			=>	'sticky_header_menu_clr',
			'title'			=>	esc_html__( 'Sticky Header Menu Color', 'lordcros' ),
			'type'			=>	'color',
			'default'		=>	'#252c41',
			'transparent'	=>	false,
			'validate'		=>	'color'
		),
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Elements', 'lordcros' ),
	'id'			=>	'header_elements',
	'subsection'	=>	true,
	'icon'			=>	'fas fa-chevron-right',
	'fields'		=>	array(
		array(
			'id'		=>	'room_search_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-concierge-bell',
			'raw'		=>	'<h3>' . esc_html__( 'Header Room Search Form', 'lordcros' ) . '</h3>',
			'required'	=>	array(
				array( 'header_layout', 'not', 'header-layout-4' ),
				array( 'header_layout', 'not', 'header-layout-5' )
			)
		),

		array(
			'id'		=>	'header_room_search',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable/Disable Form', 'lordcros' ),
			'default'	=>	1,
			'required'	=>	array(
				array( 'header_layout', 'not', 'header-layout-4' ),
				array( 'header_layout', 'not', 'header-layout-5' )
			)
		),

		array(
			'id'		=>	'header_txt_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-align-left',
			'raw'		=>	'<h3>' . esc_html__( 'Header Text Field', 'lordcros' ) . '</h3>',
			'required'	=>	array(
				array( 'header_layout', 'equals', 'header-layout-10' )
			)
		),

		array(
			'id'		=>	'header_explain_txt',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable/Disable Field', 'lordcros' ),
			'default'	=>	1,
			'required'	=>	array(
				array( 'header_layout', 'equals', 'header-layout-10' )
			)
		),

		array(
			'id'		=>	'explain_txt_content',
			'type'		=>	'editor',
			'title'		=>	esc_html__( 'Text Field Content', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'You can also use shortcodes here. Ex: [html_block block_id="1452"]', 'lordcros' ),
			'required'	=>	array(
				array( 'header_explain_txt', 'equals', 1 ),
				array( 'header_layout', 'equals', 'header-layout-10' )
			)
		),

		array(
			'id'		=>	'searchicon_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-search',
			'raw'		=>	'<h3>' . esc_html__( 'Header Search Icon', 'lordcros' ) . '</h3>',
			'required'	=>	array(
				'header_layout', 'equals', array( 'header-layout-1', 'header-layout-2', 'header-layout-5', 'header-layout-6', 'header-layout-7', 'header-layout-8', 'header-layout-9' )
			)
		),

		array(
			'id'		=>	'header_search_icon',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable/Disable Icon', 'lordcros' ),
			'default'	=>	1,
			'required'	=>	array(
				'header_layout', 'equals', array( 'header-layout-1', 'header-layout-2', 'header-layout-5', 'header-layout-6', 'header-layout-7', 'header-layout-8', 'header-layout-9' )
			)
		),

		array(
			'id'		=>	'header_html_block_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-table',
			'raw'		=>	'<h3>' . esc_html__( 'Header HTML Block Content', 'lordcros' ) . '</h3>',
			'required'	=>	array(
				'header_layout', 'equals', array( 'header-layout-3', 'header-layout-4', 'header-layout-10' )
			)
		),

		array(
			'id'		=>	'header_html_block_status',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable/Disable Block', 'lordcros' ),
			'default'	=>	1,
			'required'	=>	array(
				'header_layout', 'equals', array( 'header-layout-3', 'header-layout-4', 'header-layout-10' )
			)
		),

		array(
			'id'		=>	'header_html_block_content',
			'type'		=>	'select',
			'title'		=>	esc_html__( 'Header HTML Block Content', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Select "HTML Block" to show on Hamburger header wrap.', 'lordcros' ),
			'data'		=>	'posts',
			'args'		=>	array(
				'post_type'			=> 'html_block',
				'posts_per_page'	=> -1,
				'orderby'			=> 'id',
				'order'				=> 'ASC',
			),
			'required'	=>	array(
				array( 'header_html_block_status', 'equals', 1 ),
				array( 'header_layout', 'equals', array( 'header-layout-3', 'header-layout-4', 'header-layout-10' ) )
			)
		),

		array(
			'id'		=>	'language_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-language',
			'raw'		=>	'<h3>' . esc_html__( 'Multi Language Switcher', 'lordcros' ) . '</h3>'
		),

		array(
			'id'		=>	'header_lang_switch',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable/Disable Switcher', 'lordcros' ),
			'default'	=>	false
		),

		array(
			'id'		=>	'phone_num_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-phone',
			'raw'		=>	'<h3>' . esc_html__( 'Header Phone Number', 'lordcros' ) . '</h3>',
		),

		array(
			'id'		=>	'header_phone_num',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Show/Hide Phone Number', 'lordcros' ),
			'default'	=>	false
		),

		array(
			'id'		=>	'contact_email_info',
			'type'		=>	'info',
			'icon'		=>	'far fa-envelope',
			'raw'		=>	'<h3>' . esc_html__( 'Header Contact Email', 'lordcros' ) . '</h3>',
			'required'	=>	array(
				array( 'header_layout', 'equals', 'header-layout-4' )
			)
		),

		array(
			'id'		=>	'header_email',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Show/Hide Email', 'lordcros' ),
			'default'	=>	1,
			'required'	=>	array(
				array( 'header_layout', 'equals', 'header-layout-4' )
			)
		),

		array(
			'id'		=>	'weather_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-cloud-sun',
			'raw'		=>	'<h3>' . esc_html__( 'Weather Info', 'lordcros' ) . '</h3>',
			'required'	=>	array(
				array( 'header_layout', 'not', 'header-layout-5' ),
				array( 'header_layout', 'not', 'header-layout-6' )
			)
		),

		array(
			'id'		=>	'header_weather',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable/Disable Info', 'lordcros' ),
			'default'	=>	1,
			'required'	=>	array(
				array( 'header_layout', 'not', 'header-layout-5' ),
				array( 'header_layout', 'not', 'header-layout-6' )
			)
		),

		array(
			'id'			=>	'weahter_city_name_id',
			'type'			=>	'text',
			'title'			=>	esc_html__( 'Hotel City Name(or ID)', 'lordcros' ),
			'subtitle'		=>	wp_kses( __( 'Add correct city name or id. You can get city id info from <a href="http://bulk.openweathermap.org/sample/city.list.json.gz" target="_blank">here</a>.', 'lordcros' ), array(
					'a'		=>	array(
						'href'		=>	array(),
						'target'	=>	array()
					)
			) ),
			'description'	=>	esc_html__( 'If there is a space in city name, please add this correctly. EX: "New York". If you add name "NewYork", it will not work.', 'lordcros' ),
			'default'		=>	'Melbourne',
			'required'		=>	array(
				array( 'header_weather', 'equals', 1 ),
				array( 'header_layout', 'not', 'header-layout-5' ),
				array( 'header_layout', 'not', 'header-layout-6' )
			)
		),

		array(
			'id'		=>	'social_icon_lists',
			'type'		=>	'info',
			'icon'		=>	'fab fa-twitter',
			'raw'		=>	'<h3>' . esc_html__( 'Header Social Icons', 'lordcros' ) . '</h3>',
			'required'	=>	array(
				array( 'header_layout', 'not', 'header-layout-3' ),
				array( 'header_layout', 'not', 'header-layout-6' ),
				array( 'header_layout', 'not', 'header-layout-9' )
			)
		),

		array(
			'id'		=>	'header_social_icons',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable/Disable Icons', 'lordcros' ),
			'default'	=>	1,
			'required'	=>	array(
				array( 'header_layout', 'not', 'header-layout-3' ),
				array( 'header_layout', 'not', 'header-layout-6' ),
				array( 'header_layout', 'not', 'header-layout-9' )
			)
		),

		array(
			'id'		=>	'header_sign_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-sign-in-alt',
			'raw'		=>	'<h3>' . esc_html__( 'User Sign In/Up Links', 'lordcros' ) . '</h3>',
			'required'	=>	array(
				array( 'header_layout', 'equals', array( 'header-layout-5', 'header-layout-10' ) )
			)
		),

		array(
			'id'		=>	'header_sign_links',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable/Disable Links', 'lordcros' ),
			'default'	=>	1,
			'required'	=>	array(
				array( 'header_layout', 'equals', array( 'header-layout-5', 'header-layout-10' ) )
			)
		),

		array(
			'id'		=>	'header_copyright_info',
			'type'		=>	'info',
			'icon'		=>	'far fa-copyright',
			'raw'		=>	'<h3>' . esc_html__( 'Header Copyright Text', 'lordcros' ) . '</h3>',
			'required'	=>	array(
				array( 'header_layout', 'equals', array( 'header-layout-6' ) )
			)
		),

		array(
			'id'		=>	'header_copyright',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable/Disable text', 'lordcros' ),
			'default'	=>	1,
			'required'	=>	array(
				array( 'header_layout', 'equals', array( 'header-layout-6' ) )
			)
		),

		array(
			'id'		=>	'header_copyright_txt',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Copyright Text', 'lordcros' ),
			'default'	=>	'© 2019 LordCros All rights reserved',
			'required'	=>	array(
				'header_copyright', 'equals', 1
			)
		)
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Footer', 'lordcros' ),
	'id'			=>	'footer',
	'icon'			=>	'el el-website',
	'fields'		=>	array(
		array(
			'id'		=>	'footer_layout',
			'type'		=>	'image_select',
			'title'		=>	esc_html__( 'Footer Layout', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Choose your footer layout', 'lordcros' ),
			'options'	=>	array(
				'footer-layout-1'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/footer-layout-1.jpg',
					'alt'			=>	'Footer Layout 1'
				),

				'footer-layout-2'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/footer-layout-2.jpg',
					'alt'			=>	'Footer Layout 2'
				)
			),
			'default'	=>	'footer-layout-1'
		),

		array(
			'id'		=>	'footer-logo',
			'title'		=>	esc_html__( 'Footer Logo', 'lordcros' ),
			'desc'		=>	esc_html__( 'Upload your logo image (png, jpg)', 'lordcros' ),
			'type'		=>	'media',
			'url'		=>	false,
			'default'	=>	array(
				'url'	=>	LORDCROS_URI . '/images/logo.png'
			)
		),

		array(
			'id'		=>	'enable_footer_top',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable Footer Top Content', 'lordcros' ),
			'default'	=>	true,
			'required'	=>	array( 'footer_layout', '=', 'footer-layout-1' ),
		),

		array(
			'id'		=>	'main_footer_option',
			'icon'		=>	'fas fa-tachometer-alt',
			'type'		=>	'info',
			'raw'		=>	'<h3>' . esc_html__( 'Main Footer Settings', 'lordcros' ) . '</h3>'
		),

		array(
			'id'		=>	'footer_main_columns',
			'type'		=>	'image_select',
			'title'		=>	esc_html__( 'Main Footer Columns', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Choose your main footer area column.', 'lordcros' ),
			'options'	=>	array(
				'footer-column-1'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/footer-one-column.png',
					'alt'			=>	'Main Footer Column 1'
				),

				'footer-column-2'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/footer-two-column.png',
					'alt'			=>	'Main Footer Column 2'
				),

				'footer-column-3'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/footer-three-column.png',
					'alt'			=>	'Main Footer Column 3'
				),

				'footer-column-4'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/footer-four-column.png',
					'alt'			=>	'Main Footer Column 4'
				),

				'footer-narrow-column-4'	=>	array(
					'img'					=>	LORDCROS_URI . '/images/theme_options/footer-four-narrow-column.png',
					'alt'					=>	'Main Footer Narrow Column 4'
				),

				'footer-column-5'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/footer-five-column.png',
					'alt'			=>	'Main Footer Column 5'
				),

				'footer-column-6'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/footer-six-column.png',
					'alt'			=>	'Main Footer Column 6'
				)
			),
			'default'	=>	'footer-narrow-column-4'
		),

		array(
			'id'			=>	'footer_bg_clr',
			'title'			=>	esc_html__( 'Footer Background Color', 'lordcros' ),
			'type'			=>	'color',
			'default'		=>	'#151b2e',
			'transparent'	=>	false,
			'validate'		=>	'color'
		),

		array(
			'id'			=>	'footer_widget_title_clr',
			'title'			=>	esc_html__( 'Footer Widget Title Color', 'lordcros' ),
			'type'			=>	'color',
			'default'		=>	'#fff',
			'transparent'	=>	false,
			'validate'		=>	'color'
		),

		array(
			'id'			=>	'footer_txt_clr',
			'title'			=>	esc_html__( 'Footer Text Color', 'lordcros' ),
			'type'			=>	'color',
			'default'		=>	'#787d8b',
			'transparent'	=>	false,
			'validate'		=>	'color'
		),

		array(
			'id'			=>	'footer_bottom_option',
			'icon'			=>	'fas fa-tachometer-alt',
			'type'			=>	'info',
			'raw'			=>	'<h3>' . esc_html__( 'Footer Bottom Settings', 'lordcros' ) . '</h3>'
		),

		array(
			'id'			=>	'footer_bottom_bg_clr',
			'title'			=>	esc_html__( 'Footer Bottom Background Color', 'lordcros' ),
			'type'			=>	'color',
			'default'		=>	'#0e1222',
			'transparent'	=>	false,
			'validate'		=>	'color'
		),

		array(
			'id'			=>	'footer_bottom_txt_clr',
			'title'			=>	esc_html__( 'Footer Bottom Text Color', 'lordcros' ),
			'type'			=>	'color',
			'default'		=>	'#787d8b',
			'transparent'	=>	false,
			'validate'		=>	'color'
		),

		array(
			'id'			=>	'copyright_text',
			'type'			=>	'text',
			'title'			=>	esc_html__( 'Copyright Text', 'lordcros' ),
			'default'		=>	esc_html__( '© 2019 Lord Cros. All Rights Reserved. Design by C-Themes', 'lordcros' )
		)
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Page & Post Setting', 'lordcros' ),
	'id'			=>	'page_setting',
	'icon'			=>	'el el-edit',
	'fields'		=>	array(

		array(
			'id'		=>	'show_breadcrumbs',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Show Breadcrumbs', 'lordcros' ),
			'default'	=>	true,
			'desc'		=> esc_html__( 'This field can be overridden in special page edit.', 'lordcros' ),
		),

		array(
			'id'		=>	'page_sidebar',
			'type'		=>	'image_select',
			'title'		=>	esc_html__( 'Page Sidebar Layout', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Select sidebar layout for page.', 'lordcros' ),
			'options'	=>	array(
				'full-width'	=>	array(
					'alt'	=>	esc_html__( 'No Sidebar', 'lordcros' ),
					'img'	=>	ReduxFramework::$_url.'assets/img/1col.png'
				),
				'sidebar-left'	=>	array(
					'alt'	=>	esc_html__( 'Left Sidebar', 'lordcros' ),
					'img'	=>	ReduxFramework::$_url.'assets/img/2cl.png'
				),
				'sidebar-right'	=>	array(
					'alt'	=>	esc_html__( 'Right Sidebar', 'lordcros' ),
					'img'	=>	ReduxFramework::$_url.'assets/img/2cr.png'
				),
			),
			'default'	=>	'full-width',
			'desc'		=> esc_html__( 'This field can be overridden in special page edit.', 'lordcros' ),
		),

		array(
			'id'		=>	'page_comments_enable',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Show comments form on page', 'lordcros' ),
			'default'	=>	true
		),

		array(
			'id'		=>	'post_comments_enable',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Show comments form on post', 'lordcros' ),
			'default'	=>	true
		),

		array(
			'id'		=>	'login_page',
			'type'		=>	'select',
			'title'		=>	esc_html__( 'Login/Register Page', 'lordcros' ),
			'data'		=>	'posts',
			'args'		=> array(
				'post_type'			=>	'page',
				'posts_per_page'	=>	-1,
				'orderby'			=>	'id',
				'order'				=>	'ASC',
				'meta_key'			=>	'_wp_page_template',
				'meta_value'		=>	'templates/template-login.php'
			),
		),

		array(
			'id'		=>	'redirect_page',
			'type'		=>	'select',
			'title'		=>	esc_html__( 'Page After logged in', 'lordcros' ),
			'data'		=>	'posts',
			'args'		=> array(
				'post_type'			=>	'page',
				'posts_per_page'	=>	-1,
				'orderby'			=>	'id',
				'order'				=>	'ASC',
			),
		),
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Blog', 'lordcros' ),
	'id'			=>	'blog',
	'icon'			=>	'el el-pencil',
	'fields'		=>	array(
		array(
			'id'		=>	'blog_layout',
			'type'		=>	'image_select',
			'title'		=>	esc_html__( 'Blog Layout', 'lordcros' ),
			'options'	=>	array(
				'full-width'	=>	array(
					'alt'	=>	esc_html__( 'Full Width', 'lordcros' ),
					'img'	=>	ReduxFramework::$_url . 'assets/img/1col.png'
				),
				'sidebar-left'	=>	array(
					'alt'	=>	esc_html__( '2 Column Left', 'lordcros' ),
					'img'	=>	ReduxFramework::$_url . 'assets/img/2cl.png'
				),
				'sidebar-right'	=>	array(
					'alt'	=>	esc_html__( '2 Column Right', 'lordcros' ),
					'img'	=>	ReduxFramework::$_url . 'assets/img/2cr.png'
				),
			),
			'default'	=>	'full-width'
		),

		array(
			'id'		=>	'blog_style',
			'type'		=>	'image_select',
			'title'		=>	esc_html__( 'Blog Style', 'lordcros' ),
			'options'	=>	array(
				'layout-1'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/blog-layout-1.jpg',
					'alt'			=>	'Layout 1'
				),

				'layout-2'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/blog-layout-2.jpg',
					'alt'			=>	'Layout 2'
				),

				'layout-3'	=>	array(
					'img'			=>	LORDCROS_URI . '/images/theme_options/blog-layout-3.jpg',
					'alt'			=>	'Layout 3'
				),
			),
			'default'	=>	'layout-1',
		),

		array(
			'id'		=>	'blog_grid_columns',
			'type'		=>	'button_set',
			'title'		=>	esc_html__( 'Blog item columns', 'lordcros' ),
			'options'	=>	array(
				2	=>	'2',
				3	=>	'3',
				4	=>	'4',
			),
			'default'	=>	2,
			'required'	=>	array(
				array( 'blog_style', 'equals', array( 'layout-2', 'layout-3' ) ),
			),
		),

		array(
			'id'		=>	'blog_excerpt',
			'type'		=>	'button_set',
			'title'		=>	esc_html__( 'Posts excerpt', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'If you will set this option to "Excerpt" then you are able to set custom excerpt for each post or it will be cutted from the post content. If you choose "Full content" then all content will be shown, or you can also add "Read more button" while editing the post and by doing this cut your excerpt length as you need.', 'lordcros' ),
			'options'	=>	array(
				'excerpt'	=>	esc_html__( 'Excerpt', 'lordcros' ),
				'full'		=>	esc_html__( 'Full content', 'lordcros' )
			),
			'default'	=>	'excerpt'
		),

		array(
			'id'		=>	'blog_excerpt_length_by',
			'type'		=>	'button_set',
			'title'		=>	esc_html__( 'Excerpt length by words or letters', 'lordcros' ),
			'options'	=>	array(
				'word'		=>	esc_html__( 'Words', 'lordcros' ),
				'letter'	=>	esc_html__( 'Letters', 'lordcros' )
			),
			'default'	=>	'letter',
			'required'	=>	array(
				array( 'blog_excerpt', 'equals', 'excerpt' ),
			)
		),

		array(
			'id'		=>	'blog_excerpt_length',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Excerpt length', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Number of words or letters that will be displayed for each post if you use "Excerpt" mode and don\'t set custom excerpt for each post.', 'lordcros' ),
			'default'	=>	145,
			'required'	=>	array(
				array( 'blog_excerpt', 'equals', 'excerpt' ),
			)
		),
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Social Setting', 'lordcros' ),
	'id'			=>	'social_setting',
	'icon'			=>	'fas fa-share-square'
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Social Links', 'lordcros' ),
	'id'			=>	'social-follow',
	'subsection'	=>	true,
	'icon'			=>	'fas fa-chevron-right',
	'fields'		=>	array(
		array(
			'id'		=>	'follow_info',
			'type'		=>	'info',
			'desc'		=>	esc_html__( 'Configure [lc_social_buttons] shortcode. If you leave empty field, that particular link will be removed. There are two types of social buttons. [lc_social_buttons type="follow"]: It is shown simple social links. [lc_social_buttons type="share"]: It is shown social icons that share your page in social media. You can use both types.', 'lordcros' )
		),

		array(
			'id'		=>	'facebook_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Facebook link', 'lordcros' ),
			'default'	=>	'#'
		),

		array(
			'id'		=>	'twitter_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Twitter link', 'lordcros' ),
			'default'	=>	'#'
		),

		array(
			'id'		=>	'google_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Google+ link', 'lordcros' ),
			'default'	=>	'#'
		),

		array(
			'id'		=>	'instagram_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Instagram link', 'lordcros' ),
			'default'	=>	'#'
		),

		array(
			'id'		=>	'pinterest_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Pinterest link', 'lordcros' ),
			'default'	=>	'#'
		),

		array(
			'id'		=>	'youtube_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Youtube link', 'lordcros' ),
			'default'	=>	'#'
		),

		array(
			'id'		=>	'linkedin_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'LinkedIn link', 'lordcros' ),
			'default'	=>	'#'
		),

		array(
			'id'		=>	'vimeo_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Vimeo link', 'lordcros' ),
			'default'	=>	''
		),

		array(
			'id'		=>	'tumblr_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Tumblr link', 'lordcros' ),
			'default'	=>	''
		),

		array(
			'id'		=>	'flickr_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Flickr link', 'lordcros' ),
			'default'	=>	''
		),

		array(
			'id'		=>	'github_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Github link', 'lordcros' ),
			'default'	=>	''
		),

		array(
			'id'		=>	'vk_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'VK link', 'lordcros' ),
			'default'	=>	''
		),

		array(
			'id'		=>	'dribbble_link',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Dribbble link', 'lordcros' ),
			'default'	=>	''
		),

		array(
			'id'		=>	'social_email',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Email for Social link', 'lordcros' ),
			'default'	=>	true
		)
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Social Share', 'lordcros' ),
	'id'			=>	'social-share',
	'subsection'	=>	true,
	'icon'			=>	'fas fa-chevron-right',
	'fields'		=>	array(
		array(
			'id'		=>	'share_info',
			'type'		=>	'info',
			'desc'		=>	esc_html__( 'Configure [lc_social_buttons] shortcode. If you leave empty field, that particular link will be removed. There are two types of social buttons. [lc_social_buttons type="follow"]: It is shown simple social links. [lc_social_buttons type="share"]: It is shown social icons that share your page in social media. You can use both types.', 'lordcros' )
		),

		array(
			'id'		=>	'facebook_share',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Facebook Share', 'lordcros' ),
			'default'	=>	true
		),

		array(
			'id'		=>	'twitter_share',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Twitter Share', 'lordcros' ),
			'default'	=>	true
		),

		array(
			'id'		=>	'google_share',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Google Plus Share', 'lordcros' ),
			'default'	=>	true
		),

		array(
			'id'		=>	'pinterest_share',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Pinterest Share', 'lordcros' ),
			'default'	=>	true
		),

		array(
			'id'		=>	'linkedin_share',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'LinkedIn Share', 'lordcros' ),
			'default'	=>	true
		),

		array(
			'id'		=>	'vk_share',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'VK Share', 'lordcros' ),
			'default'	=>	false
		),

		array(
			'id'		=>	'email_share',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Email for Share links', 'lordcros' ),
			'default'	=>	true
		),
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Currency Settings', 'lordcros' ),
	'id'			=>	'currency-settings',
	'icon'			=>	'el el-usd',
	'fields'		=>	array(

		array(
			'id'			=>	'currency_code',
			'type'			=>	'text',
			'title'			=>	esc_html__( 'Currency Code', 'lordcros' ),
			'default'		=>	'USD',
			'desc'			=>	esc_html__( 'Currency code should be acceptable in payment gateways such as USD, EUR, etc.', 'lordcros' ),
		),

		array(
			'id'			=>	'currency_symbol',
			'type'	 		=>	'text',
			'title'			=>	esc_html__( 'Currency Symbol', 'lordcros' ),
			'desc'	 		=>	esc_html__( 'Currency Symbol that shows in front end', 'lordcros' ),
			'default'		=>	'$'
		),

		array(
			'id'			=>	'cs_pos',
			'type'	 		=>	'button_set',
			'title'			=>	esc_html__( 'Currency Symbol Position', 'lordcros' ),
			'subtitle' 		=>	esc_html__( "Select a Curency Symbol Position for Frontend", 'lordcros' ),
			'desc'	 		=>	'',
			'options'		=>	array(
				'left' 			=>	esc_html__( 'Left ($99.99)', 'lordcros' ),
				'right' 		=>	esc_html__( 'Right (99.99$)', 'lordcros' ),
				'left_space' 	=>	esc_html__( 'Left with space ($ 99.99)', 'lordcros' ),
				'right_space' 	=>	esc_html__( 'Right with space (99.99 $)', 'lordcros' )
			),
			'default'		=>	'left'
		),

		array(
			'id'			=>	'decimal_prec',
			'type'	 		=>	'select',
			'title'			=>	esc_html__( 'Decimal Precision', 'lordcros' ),
			'subtitle' 		=>	esc_html__( 'Please choose desimal precision', 'lordcros' ),
			'desc'	 		=>	'',
			'options'		=>	array(
				'0'				=>	'0',
				'1'				=>	'1',
				'2'				=>	'2',
				'3'				=>	'3',
			),
			'default'		=>	'2'
		),

		array(
			'id'			=>	'thousands_sep',
			'type'	 		=>	'text',
			'title'			=>	esc_html__( 'Thousand Separate', 'lordcros' ),
			'subtitle' 		=>	esc_html__( 'This sets the thousand separator of displayed prices.', 'lordcros' ),
			'default'		=>	',',
		),

		array(
			'id'			=>	'decimal_sep',
			'type'	 		=>	'text',
			'title'			=>	esc_html__( 'Decimal Separate', 'lordcros' ),
			'subtitle' 		=>	esc_html__( 'This sets the decimal separator of displayed prices.', 'lordcros' ),
			'default'		=>	'.',
		),
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Room Settings', 'lordcros' ),
	'id'			=>	'room-settings',
	'icon'			=>	'fas fa-concierge-bell',
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Rooms General Settings', 'lordcros' ),
	'id'			=>	'room-general-settings',
	'subsection'	=>	true,
	'icon'			=>	'fas fa-chevron-right',
	'fields'		=>	array(

		array(
			'id'		=>	'room_page_layout',
			'type'		=>	'image_select',
			'title'		=>	esc_html__( 'Room Page Layout', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'This field can be overridden in special page edit.', 'lordcros' ),
			'options'	=>	array(
				'layout-1'	=>	array(
					'title'			=>	esc_html__( 'Room Slider In Content', 'lordcros' ),
					'img'			=>	LORDCROS_URI . '/images/theme_options/room-single-layout-1.jpg',
					'alt'			=>	'Slider in content'
				),

				'layout-2'	=>	array(
					'title'			=>	esc_html__( 'Room Slider In Header', 'lordcros' ),
					'img'			=>	LORDCROS_URI . '/images/theme_options/room-single-layout-2.jpg',
					'alt'			=>	'Slider in header'
				),

				'layout-3'	=>	array(
					'title'			=>	esc_html__( 'Room Slider Full Width', 'lordcros' ),
					'img'			=>	LORDCROS_URI . '/images/theme_options/room-single-layout-3.jpg',
					'alt'			=>	'Slider in bottom'
				),
			),
			'default'	=>	'layout-1'
		),

		array(
			'id'			=>	'size_unit',
			'type'	 		=>	'text',
			'title'			=>	esc_html__( 'Size Unit', 'lordcros' ),
			'desc'	 		=>	esc_html__( 'Add Size Unit. For example, Ft, m, etc.', 'lordcros' ) . ' ' . '<sup>2</sup>' . esc_html__( ' will be added at the end of unit like', 'lordcros' ) . ' Ft<sup>2</sup>' ,
			'default'		=>	'Ft'
		),
	),
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Room Search Page Settings', 'lordcros' ),
	'id'			=>	'room-search-page-settings',
	'subsection'	=>	true,
	'icon'			=>	'fas fa-chevron-right',
	'fields'		=>	array(

		array(
			'id'		=>	'room_search_page',
			'type'		=>	'select',
			'title'		=>	esc_html__( 'Room Search Page', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Room Search Page should be "Room Search Page Template"', 'lordcros' ),
			'data'		=>	'posts',
			'args'		=> array(
				'post_type'			=>	'page',
				'posts_per_page'	=>	-1,
				'orderby'			=>	'id',
				'order'				=>	'ASC',
				'meta_key'			=>	'_wp_page_template',
				'meta_value'		=>	'templates/template-room-search.php'
			),
		),

		array(
			'id'			=>	'room_list_default_view',
			'type'	 		=>	'button_set',
			'title'			=>	esc_html__( 'Room List Default View', 'lordcros' ),
			'options'		=>	array(
				'list' 			=>	esc_html__( 'List View', 'lordcros' ),
				'grid' 			=>	esc_html__( 'Grid View', 'lordcros' ),
				'block' 		=>	esc_html__( 'Block View', 'lordcros' ),
			),
			'default'		=>	'grid',
		),

		array(
			'id'			=>	'rooms_per_page',
			'type'	 		=>	'text',
			'title'			=>	esc_html__( 'Rooms Per Page', 'lordcros' ),
			'subtitle'		=>	esc_html__( 'Select a number of rooms to show on room search page.', 'lordcros' ),
			'default'		=>	'4',
		),

	),
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Room Booking Page Settings', 'lordcros' ),
	'id'			=>	'room-booking-page-settings',
	'subsection'	=>	true,
	'icon'			=>	'fas fa-chevron-right',
	'fields'		=>	array(

		array(
			'id'		=>	'room_booking_page',
			'type'		=>	'select',
			'title'		=>	esc_html__( 'Room Booking Page', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Room Booking Page should be "Booking Page Template"', 'lordcros' ),
			'data'		=>	'posts',
			'args'		=> array(
				'post_type'			=>	'page',
				'posts_per_page'	=>	-1,
				'orderby'			=>	'id',
				'order'				=>	'ASC',
				'meta_key'			=>	'_wp_page_template',
				'meta_value'		=>	'templates/template-booking.php'
			),
		),

		array(
			'id'		=>	'room_checkout_page',
			'type'		=>	'select',
			'title'		=>	esc_html__( 'Room Checkout Page', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Room Checkout Page should be "Checkout Page Template"', 'lordcros' ),
			'data'		=>	'posts',
			'args'		=> array(
				'post_type'			=>	'page',
				'posts_per_page'	=>	-1,
				'orderby'			=>	'id',
				'order'				=>	'ASC',
				'meta_key'			=>	'_wp_page_template',
				'meta_value'		=>	'templates/template-checkout.php'
			),
		),

		array(
			'id'		=>	'room_thankyou_page',
			'type'		=>	'select',
			'title'		=>	esc_html__( 'Room Thankyou Page', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Room Thankyou Page should be "Thankyou Page Template"', 'lordcros' ),
			'data'		=>	'posts',
			'args'		=> array(
				'post_type'			=>	'page',
				'posts_per_page'	=>	-1,
				'orderby'			=>	'id',
				'order'				=>	'ASC',
				'meta_key'			=>	'_wp_page_template',
				'meta_value'		=>	'templates/template-thankyou.php'
			),
		),
	)
) );

ob_start();
include	LORDCROS_LIB .'/theme_options/email_templates/room_confirm_email_description.htm';
$room_confirm_email_description = ob_get_contents();
ob_end_clean();

ob_start();
include	LORDCROS_LIB .'/theme_options/email_templates/room_admin_email_description.htm';
$room_admin_email_description = ob_get_contents();
ob_end_clean();

Redux::setSection( $opt_name, array(
	'title'			=> esc_html__( 'Room Booking Email Settings', 'lordcros' ),
	'id'			=> 'room-email-settings',
	'icon'			=> 'fas fa-chevron-right',
	'subsection' 	=> true,
	'fields'	 	=> array(

		array(
			'id'		=>	'customer_notification_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-sliders-h',
			'raw'		=>	'<h3>' . esc_html__( 'Customer Notification Settings', 'lordcros' ) . '</h3>',
		),

		array(
			'title'	 	=> esc_html__( 'Enable/Disable Customer Notification', 'lordcros' ),
			'subtitle'	=> esc_html__( 'Enable individual booked email notification to Customer.', 'lordcros' ),
			'id'		=> 'room_booked_notify_customer',
			'default'	=> true,
			'type'		=> 'switch'
		),

		array(
			'title'	 	=> esc_html__( 'Booking Confirmation Email Subject', 'lordcros' ),
			'subtitle'	=> esc_html__( 'Room booking confirmation email subject.', 'lordcros' ),
			'id'		=> 'room_confirm_email_subject',
			'default'	=> 'Your booking at [room_name]',
			'type'		=> 'text',
			'required'	=> array( 'room_booked_notify_customer', '=', '1' ),
		),

		array(
			'title'	 	=> esc_html__( 'Booking Confirmation Email Description', 'lordcros' ),
			'subtitle'	=> esc_html__( 'Room booking confirmation email description.', 'lordcros' ),
			'id'		=> 'room_confirm_email_description',
			'default'	=> $room_confirm_email_description,
			'type'		=> 'editor',
			'required'	=> array( 'room_booked_notify_customer', '=', '1' ),
		),

		array(
			'id'		=>	'admin_notification_info',
			'type'		=>	'info',
			'icon'		=>	'fas fa-sliders-h',
			'raw'		=>	'<h3>' . esc_html__( 'Administrator Notification Settings', 'lordcros' ) . '</h3>',
		),

		array(
			'title'	 	=> esc_html__( 'Enable/Disable Administrator Notification', 'lordcros' ),
			'subtitle'	=> esc_html__( 'Enable individual booked email notification to site administrator.', 'lordcros' ),
			'id'		=> 'room_booked_notify_admin',
			'default'	=> true,
			'type'		=> 'switch'
		),

		array(
			'title'	 	=> esc_html__( 'Administrator Booking Notification Email Subject', 'lordcros' ),
			'subtitle'	=> esc_html__( 'Administrator Notification Email Subject for Room Booking.', 'lordcros' ),
			'id'		=> 'room_admin_email_subject',
			'default'	=> 'Received a booking at [room_name]',
			'required'	=> array( 'room_booked_notify_admin', '=', '1' ),
			'type'		=> 'text'
		),

		array(
			'title'	 	=> esc_html__( 'Administrator Booking Notification Email Description', 'lordcros' ),
			'subtitle'	=> esc_html__( 'Administrator Notification Email Description for Room Booking.', 'lordcros' ),
			'id'		=> 'room_admin_email_description',
			'default'	=> $room_admin_email_description,
			'required'	=> array( 'room_booked_notify_admin', '=', '1' ),
			'type'		=> 'editor'
		),

	),
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Booking Payment Settings', 'lordcros' ),
	'id'			=>	'payment-settings',
	'icon'			=>	'far fa-credit-card',
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Paypal Settings', 'lordcros' ),
	'id'			=>	'paypal-settings',
	'icon'			=>	'fas fa-chevron-right',
	'subsection'	=>	true,
	'fields'		=>	array(

		array(
			'id'		=>	'paypal_payment',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable paypal payment in booking.', 'lordcros' ),
			'default'	=>	false,
		),

		array(
			'id'		=>	'paypal_sandbox',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Sandbox Mode', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Enable PayPal sandbox for testing.', 'lordcros' ),
			'default'	=>	false,
			'required'	=>	array( 'paypal_payment', '=', '1' ),
		),

		array(
			'id'		=>	'paypal_api_username',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'PayPal API Username', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Your PayPal Account API Username.', 'lordcros' ),
			'default'	=>	'',
			'required'	=>	array( 'paypal_payment', '=', '1' ),
		),

		array(
			'id'		=>	'paypal_api_password',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'PayPal API Password', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Your PayPal Account API Password.', 'lordcros' ),
			'default'	=>	'',
			'required'	=>	array( 'paypal_payment', '=', '1' ),
		),

		array(
			'id'		=>	'paypal_api_signature',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'PayPal API Signature', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Your PayPal Account API Signature.', 'lordcros' ),
			'default'	=>	'',
			'required'	=>	array( 'paypal_payment', '=', '1' ),
		),

	),

) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Stripe Settings', 'lordcros' ),
	'id'			=>	'stripe-settings',
	'icon'			=>	'fas fa-chevron-right',
	'subsection'	=>	true,
	'fields'		=>	array(

		array(
			'id'		=>	'stripe_payment',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable stripe payment in booking.', 'lordcros' ),
			'default'	=>	false,
		),

		array(
			'id'		=>	'stripe_publishable_key',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Publishable Key', 'lordcros' ),
			'required'	=>	array( 'stripe_payment', '=', '1' ),
		),

		array(
			'id'		=>	'stripe_secret_key',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Secret Key', 'lordcros' ),
			'required'	=>	array( 'stripe_payment', '=', '1' ),
		),

	),

) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Bank Transfer Settings', 'lordcros' ),
	'id'			=>	'bank-stransfer-settings',
	'icon'			=>	'fas fa-chevron-right',
	'subsection'	=>	true,
	'fields'		=>	array(

		array(
			'id'		=>	'bank_transfer_payment',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Enable bank transfer payment in booking.', 'lordcros' ),
			'default'	=>	false,
		),

		array(
			'id'		=>	'bank_name',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Bank Name', 'lordcros' ),
			'required'	=>	array( 'bank_transfer_payment', '=', '1' ),
		),

		array(
			'id'		=>	'account_name',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Hoder Name', 'lordcros' ),
			'required'	=>	array( 'bank_transfer_payment', '=', '1' ),
		),

		array(
			'id'		=>	'swift',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Swift/BIC', 'lordcros' ),
			'required'	=>	array( 'bank_transfer_payment', '=', '1' ),
		),

		array(
			'id'		=>	'bank_address',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Bank Address', 'lordcros' ),
			'required'	=>	array( 'bank_transfer_payment', '=', '1' ),
		),

		array(
			'id'		=>	'sort_code',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Sort Code', 'lordcros' ),
			'required'	=>	array( 'bank_transfer_payment', '=', '1' ),
		),

	),

) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Cookies Law', 'lordcros' ),
	'id'			=>	'cookie-setting',
	'icon'			=>	'fas fa-user-secret',
	'fields'		=>	array(
		array(
			'id'		=>	'show_cookie',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Show Cookies Notification', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Under EU privacy regulations, websites must make it clear to visitors what information about them is being stored. This specifically includes cookies. Turn on this option and user will see info box at the bottom of the page that your web-site is using cookies.', 'lordcros' ),
			'default'	=>	false
		),

		array(
			'id'		=>	'cookies_text',
			'type'		=>	'editor',
			'title'		=>	esc_html__( 'Notification text', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Place here some information about cookies usage that will be shown in the popup.', 'lordcros' ),
			'default'	=>	esc_html__( 'We use cookies to improve your experience on our website. By browsing this website, you agree to our use of cookies.', 'lordcros' )
		),

		array(
			'id'		=>	'cookies_policy_page',
			'type'		=>	'select',
			'title'		=>	esc_html__( 'Page with details', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Choose page that will contain detailed information about your Privacy Policy', 'lordcros' ),
			'data'		=>	'pages'
		),

		array(
			'id'		=>	'cookies_version',
			'type'		=>	'text',
			'title'		=>	esc_html__( 'Cookies version', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'If you change your cookie policy information you can increase their version to show the popup to all visitors again.', 'lordcros' ),
			'default'	=>	1
		)
	)
) );

Redux::setSection( $opt_name, array(
	'title'			=>	esc_html__( 'Maintenance', 'lordcros' ),
	'id'			=>	'maintenance_mode',
	'icon'			=>	'el el-cog',
	'fields'		=>	array(

		array(
			'id'		=>	'coming_soon_mode',
			'type'		=>	'switch',
			'title'		=>	esc_html__( 'Maintenance Mode', 'lordcros' ),
			'on'		=>	esc_html__( 'Enable', 'lordcros' ),
			'off'		=>	esc_html__( 'Disable', 'lordcros' ),
			'default'	=>	0
		),

		array(
			'id'					=>	'coming_soon_bg_image',
			'type'					=>	'background',
			'title'					=>	esc_html__( 'Coming Soon Background Image', 'lordcros' ),
			'default'				=>	array(
				'background-color'	=>	'#fff'
			),
			'transparent'			=>	false,
			'background-repeat'		=>	false,
			'background-size'		=>	false,
			'background-attachment'	=>	false,
			'background-position'	=>	false,
			'required'				=>	array(
				'coming_soon_mode', 'equals', array( '1' )
			)
		),

		array(
			'id'		=>	'coming_soon_content',
			'type'		=>	'select',
			'title'		=>	esc_html__( 'Coming Soon Page Content', 'lordcros' ),
			'subtitle'	=>	esc_html__( 'Select "HTML Block" to show on Coming Soon Page', 'lordcros' ),
			'data'		=>	'posts',
			'args'		=>	array(
				'post_type'			=> 'html_block',
				'posts_per_page'	=> -1,
				'orderby'			=> 'id',
				'order'				=> 'ASC',
			),
			'required'	=>	array(
				'coming_soon_mode', 'equals', array( '1' )
			)
		),

		array(
			'id'		=>	'coming_soon_content_pos',
			'type'		=>	'button_set',
			'title'		=>	esc_html__( 'Coming Soon Content Position', 'lordcros' ),
			'options'	=>	array(
				'left'		=>	esc_html__( 'Left', 'lordcros' ),
				'center'	=>	esc_html__( 'Center', 'lordcros' ),
				'right'		=>	esc_html__( 'Right', 'lordcros' ),
			),
			'default'	=>	'left',
			'required'	=>	array(
				'coming_soon_mode', 'equals', array( '1' )
			)
		),
	)
) );
