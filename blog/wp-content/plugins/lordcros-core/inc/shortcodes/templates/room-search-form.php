<?php
/**
 * Room Search Form Shortcode
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// [lc_room_search_form]
function lordcros_core_shortcode_room_search_form( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'number_color'			=>	'#262626',
		'label_color'			=>	'#878787',
		'font_size'				=>	'default',	//default, large
		'date_format'			=>	'style1',	//style1, style2, style3
		'separator'				=>	'no',		//no, yes
		'separator_color'		=>	'#e9e9e9',
		'show_icon'				=>	'no',		//no, yes
		'button_color'			=>	'#fff',
		'button_bg_color'		=>	'#ff6d5e',
		'button_border'			=>	'no',		//no, yes
		'button_border_color'	=>	'#fff',
		'button_size'			=>	'default',	//default, large
		'button_text'			=>	'BOOK NOW',
		'extra_class'			=>	'',
		'animation'				=>	'',
		'animation_delay'		=>	'',
		'box_shadow'			=>	'no',		//no, yes
		'css'					=>	''
	), $atts, 'lordcros_room_search_form' ) );

	$id = rand( 100, 9999 );
	$shortcode_id = uniqid( 'lordcros-room-search-form-' . $id );

	$content_class = $html = $styles = '';
	
	if ( ! empty( $font_size ) ) {
		$content_class .= ' font-size-' . $font_size;
	}
	if ( ! empty( $date_format ) ) {
		$content_class .= ' date-format-' . $date_format;
	}
	if ( ! empty( $border ) && $border == 'yes' ) {
		$content_class .= ' bordered';
	}
	if ( ! empty( $separator ) && $separator == 'yes' ) {
		$content_class .= ' separator';
	}
	if ( ! empty( $show_icon ) && $show_icon == 'yes' ) {
		$content_class .= ' show-btn-icon';
	}
	if ( ! empty( $box_shadow ) && $box_shadow == 'yes' ) {
		$content_class .= ' show-box-shadow';
	}
	if ( ! empty( $button_border ) && $button_border == 'yes' ) {
		$content_class .= ' button_bordered';
	}
	if ( ! empty( $button_size ) ) {
		$content_class .= ' button-size-' . $button_size;
	}

	// Build the animation classes
	$animation_classes = lordcros_core_getCSSAnimation( $animation );

	if ( ! empty( $animation_classes ) ) {
		$content_class .= ' ' . $animation_classes;
	}

	if ( ! empty( $extra_class ) ) {
		$content_class .= ' ' . $extra_class;
	}

	if ( ! empty( $css ) && function_exists( 'vc_shortcode_custom_css_class' ) ) {
		$content_class .= ' ' . vc_shortcode_custom_css_class( $css );
	}

	$room_search_page = lordcros_get_opt( 'room_search_page' );

	$current_day = date( 'd' );
	$next_day = date( 'd', strtotime( '+1 day' ) );
	$current_month = date( 'M' );
	$month_next_day = date( 'M', strtotime( '+1 day' ) );
	$current_year = date( 'Y' );
	$year_next_day = date( 'Y', strtotime( '+1 day' ) );
	
	ob_start();
	?>		
		<div id="<?php echo esc_attr( $shortcode_id ); ?>" class="lordcros-shortcode-element lordcros-room-search-shortcode room-search-form <?php echo esc_attr( $content_class ); ?>">
			<?php if( empty( $room_search_page ) ) : ?>
				<p class="alert alert-warning"><?php echo esc_html__( 'Please config Room Search Page in theme options panel.', 'lordcros-core' ); ?></p>
			<?php else : ?>
				<form action="<?php echo esc_url( get_permalink( $room_search_page ) ); ?>" method="get">			
					<div class="form-input-area">
						<div id="form-check-in" class="search-calendar-show">
							<div class="check-in-section-wrap">
								<?php

								if ( $date_format == 'style1' ) {
									?>
									<div class="section-content">
										<span class="section-title"><?php echo esc_html__( 'Check-In', 'lordcros-core' ); ?></span>
										<div class="day-val"><?php echo esc_html( $current_day ); ?></div>
										<div class="bottomside-inner">
											<span class="month-val"><?php echo esc_html( $current_month ); ?></span>
											<span class="year-val"><?php echo esc_html( $current_year ); ?></span>
										</div>
									</div>
									<div class="leftside-inner">
										<i class="fas fa-chevron-down"></i>
									</div>
									<?php
								} elseif ( $date_format == 'style2' ) {
									?>
									<span class="section-title"><?php echo esc_html__( 'Check-In', 'lordcros-core' ); ?></span>
									<div class="section-content">
										<div class="day-val"><?php echo esc_html( $current_day ); ?></div>
										<div class="leftside-inner">
											<span class="month-val"><?php echo esc_html( $current_month ); ?></span>
											<span class="year-val"><?php echo esc_html( $current_year ); ?></span>
											<i class="fas fa-chevron-down"></i>
										</div>
									</div>
									<?php
								} else {
									?>
									<span class="section-title"><?php echo esc_html__( 'Check-In', 'lordcros-core' ); ?></span>
									<div class="section-content">
										<div class="day-val"><?php echo esc_html( $current_day ); ?></div>
										<div class="leftside-inner">
											<div class="year-month-wrap">
												<span class="month-val"><?php echo esc_html( $current_month ); ?></span>
												<span class="year-val"><?php echo esc_html( $current_year ); ?></span>
											</div>
											<i class="fas fa-chevron-down"></i>
										</div>
									</div>
									<?php
								}

								?>
							</div>

							<input type="hidden" id="lc-booking-date-month-from" class="lc-booking-date-month-from">
							<input type="hidden" id="lc-booking-date-day-from" class="lc-booking-date-day-from">
							<input type="text" name="date_from" class="lc-booking-date-range-from" placeholder="Check In">
						</div>
						<div id="form-check-out" class="search-calendar-show">
							<div class="check-out-section-wrap">
								<?php

								if ( $date_format == 'style1' ) {
									?>
									<div class="section-content">
										<span class="section-title"><?php echo esc_html__( 'Check-Out', 'lordcros-core' ); ?></span>
										<div class="day-val"><?php echo esc_html( $next_day ); ?></div>
										<div class="bottomside-inner">
											<span class="month-val"><?php echo esc_html( $month_next_day ); ?></span>
											<span class="year-val"><?php echo esc_html( $year_next_day ); ?></span>
										</div>
									</div>
									<div class="leftside-inner">
										<i class="fas fa-chevron-down"></i>
									</div>
									<?php
								} elseif ( $date_format == 'style2' ) {
									?>
									<span class="section-title"><?php echo esc_html__( 'Check-Out', 'lordcros-core' ); ?></span>
									<div class="section-content">
										<div class="day-val"><?php echo esc_html( $next_day ); ?></div>
										<div class="leftside-inner">
											<span class="month-val"><?php echo esc_html( $month_next_day ); ?></span>
											<span class="year-val"><?php echo esc_html( $year_next_day ); ?></span>
											<i class="fas fa-chevron-down"></i>
										</div>
									</div>
									<?php
								} else {
									?>
									<span class="section-title"><?php echo esc_html__( 'Check-Out', 'lordcros-core' ); ?></span>
									<div class="section-content">
										<div class="day-val"><?php echo esc_html( $next_day ); ?></div>
										<div class="leftside-inner">
											<div class="year-month-wrap">
												<span class="month-val"><?php echo esc_html( $month_next_day ); ?></span>
												<span class="year-val"><?php echo esc_html( $year_next_day ); ?></span>
											</div>
											<i class="fas fa-chevron-down"></i>
										</div>
									</div>
									<?php
								}

								?>
							</div>

							<input type="hidden" id="lc-booking-date-month-to" class="lc-booking-date-month-to">
							<input type="hidden" id="lc-booking-date-day-to" class="lc-booking-date-day-to">
							<input type="text" name="date_to" class="lc-booking-date-range-to" placeholder="Check Out">
						</div>
						<div id="form-guests-num" class="search-guest-count">
							<div class="guest-section-wrap">
								<span class="section-title"><?php echo esc_html__( 'Guests', 'lordcros-core' ); ?></span>
								<div class="section-content">
									<div class="guest-val">1</div>
									<div class="leftside-inner">
										<i class="fas fa-chevron-up"></i>
										<i class="fas fa-chevron-down"></i>
									</div>
								</div>
							</div>

							<input type="number" name="adults" id="lc-booking-form-guests" class="lc-booking-form-guests" placeholder="Guest" min="1" value="1">
						</div>
					</div>

					<div class="form-submit-wrap">
						<button type="submit" class="room-search-submit"><i class="lordcros lordcros-bell"></i><?php echo "" . $button_text; ?></button>
					</div>
				</form>
			<?php endif; ?>
		</div>
	<?php

	$html = ob_get_clean();

	$styles .= '#' . $shortcode_id . '{';
	$styles .= 'animation-delay: ' . $animation_delay . 's;';
	$styles .= '}';
	$styles .= '#' . $shortcode_id . ' .form-input-area .day-val,';
	$styles .= '#' . $shortcode_id . ' .form-input-area .guest-val {';
	$styles .= 'color: ' . $number_color . ';';
	$styles .= '}';
	$styles .= '#' . $shortcode_id . ' .form-input-area .section-title,';
	$styles .= '#' . $shortcode_id . ' .form-input-area .month-val,';
	$styles .= '#' . $shortcode_id . ' .form-input-area .year-val,';
	$styles .= '#' . $shortcode_id . ' .form-input-area i {';
	$styles .= 'color: ' . $label_color . ';';
	$styles .= '}';
	$styles .= '#' . $shortcode_id . ' .form-input-area #form-check-out {';
	$styles .= 'color: ' . $separator_color . ';';
	$styles .= '}';
	$styles .= '#' . $shortcode_id . ' .room-search-submit {';
	$styles .= 'background-color: ' . $button_bg_color . ';';
	$styles .= 'border-color: ' . $button_border_color . ';';
	$styles .= 'color: ' . $button_color . ';';
	$styles .= '}';

	ob_start();
	?>

	<script>
		var css = '<?php echo $styles; ?>',
		head = document.head || document.getElementsByTagName('head')[0],
		style = document.createElement('style');

		head.appendChild(style);

		style.type = 'text/css';

		if (style.styleSheet){
			// This is required for IE8 and below.
			style.styleSheet.cssText = css;
		} else {
			style.appendChild(document.createTextNode(css));
		}
	</script>

	<?php
	$custom_css = ob_get_clean();

	$html .= $custom_css;

	return $html;
}

add_shortcode( 'lc_room_search_form', 'lordcros_core_shortcode_room_search_form' );

/**
 * WPBakery
 */
function lordcros_core_vc_shortcode_room_search_form() {

	$animation_style = lordcros_core_animation_style_field();
	$animation_delay = lordcros_core_animation_delay_field();
	$extra_class = lordcros_core_extra_class_field();

	vc_map( array(
		'name'			=>	esc_html__( 'Room Search Form', 'lordcros-core' ),
		'base'			=>	'lc_room_search_form',
		'icon'			=>	'lordcros-js-composer',
		'category'		=>	esc_html__( 'by C-Themes', 'lordcros-core' ),
		'description'	=>	esc_html__( 'Show room search form in your page.', 'lordcros-core' ),
		'params'		=>	array(
			array(
				'type'			=> 'lordcros_image_selection',
				'heading'		=>	esc_html__( 'Date Format', 'lordcros-core' ),
				'param_name'	=>	'date_format',
				'value'			=> array(
					__( 'Style 1', 'lordcros-core' )	=> 'style1',
					__( 'Style 2', 'lordcros-core' )	=> 'style2',
					__( 'Style 3', 'lordcros-core' )	=> 'style3',
				),
				'image_value'	=> array(
					'style1'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/date-format-style-1.jpg',
					'style2'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/date-format-style-2.jpg',
					'style3'		=> LORDCROS_CORE_PLUGIN_URL . 'inc/images/date-format-style-3.jpg',
				),
				'std'			=> 'style1',
				'group'			=>	esc_html__( 'Label & Date Settings', 'lordcros-core' ),
			),
			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Number color in Search Form', 'lordcros-core' ),
				'param_name'	=>	'number_color',
				'std'			=>	'#262626',
				'group'			=>	esc_html__( 'Label & Date Settings', 'lordcros-core' ),
			),
			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Label color', 'lordcros-core' ),
				'param_name'	=>	'label_color',
				'std'			=>	'#878787',
				'group'			=>	esc_html__( 'Label & Date Settings', 'lordcros-core' ),
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Font size', 'lordcros-core' ),
				'param_name'	=>	'font_size',
				'value'			=>	array(
					esc_html__( 'Default', 'lordcros-core' )	=>	'default',
					esc_html__( 'Large', 'lordcros-core' )		=>	'large',
				),
				'std'			=>	'default',
				'group'			=>	esc_html__( 'Label & Date Settings', 'lordcros-core' ),
			),
			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Separator', 'lordcros-core' ),
				'param_name'	=>	'separator',
				'value'			=>	array(
					esc_html__( 'Yes', 'lordcros-core' )	=>	'yes'
				),
				'group'			=>	esc_html__( 'Label & Date Settings', 'lordcros-core' ),
			),
			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Separator Color', 'lordcros-core' ),
				'param_name'	=>	'separator_color',
				'dependency'	=> array(
					'element'		=> 'separator',
					'value'			=> array( 'yes' )
				),
				'std'			=>	'#e9e9e9',
				'group'			=>	esc_html__( 'Label & Date Settings', 'lordcros-core' ),
			),			
			array(
				'type'			=>	'textfield',
				'heading'		=>	esc_html__( 'Button Text', 'lordcros-core' ),
				'param_name'	=>	'button_text',
				'std'			=>	'BOOK NOW',
				'group'			=>	esc_html__( 'Button Settings', 'lordcros-core' ),
			),
			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Show Button Icon', 'lordcros-core' ),
				'param_name'	=>	'show_icon',
				'value'			=>	array(
					esc_html__( 'Yes', 'lordcros-core' )	=>	'yes'
				),
				'group'			=>	esc_html__( 'Button Settings', 'lordcros-core' ),
			),
			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Button Color', 'lordcros-core' ),
				'param_name'	=>	'button_color',
				'std'			=>	'#fff',
				'group'			=>	esc_html__( 'Button Settings', 'lordcros-core' ),	
			),
			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Button Background Color', 'lordcros-core' ),
				'param_name'	=>	'button_bg_color',
				'std'			=>	'#ff6d5e',
				'group'			=>	esc_html__( 'Button Settings', 'lordcros-core' ),
			),
			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Button Border', 'lordcros-core' ),
				'param_name'	=>	'button_border',
				'value'			=>	array(
					esc_html__( 'Yes', 'lordcros-core' )	=>	'yes'
				),
				'group'			=>	esc_html__( 'Button Settings', 'lordcros-core' ),	
			),
			array(
				'type'			=>	'colorpicker',
				'heading'		=>	esc_html__( 'Button Border Color', 'lordcros-core' ),
				'param_name'	=>	'button_border_color',
				'dependency'	=> array(
					'element'		=> 'button_border',
					'value'			=> array( 'yes' )
				),
				'group'			=>	esc_html__( 'Button Settings', 'lordcros-core' ),		
			),
			array(
				'type'			=>	'dropdown',
				'heading'		=>	esc_html__( 'Button Size', 'lordcros-core' ),
				'param_name'	=>	'button_size',
				'value'			=>	array(
					esc_html__( 'Default', 'lordcros-core' )	=>	'default',
					esc_html__( 'Large', 'lordcros-core' )		=>	'large',
				),
				'std'			=>	'default',
				'group'			=>	esc_html__( 'Button Settings', 'lordcros-core' ),
			),
			array(
				'type'			=>	'checkbox',
				'heading'		=>	esc_html__( 'Enable Box Shadow', 'lordcros-core' ),
				'param_name'	=>	'box_shadow',
				'value'			=>	array(
					esc_html__( 'Yes', 'lordcros-core' )	=>	'yes'
				)
			),
			$animation_style,
			$animation_delay,
			$extra_class,
			array(
				'type'			=>	'css_editor',
				'heading'		=>	esc_html__( 'Custom CSS', 'lordcros-core' ),
				'param_name'	=>	'css',
				'group'			=>	esc_html__( 'Design For Content', 'lordcros-core' )
			)
		)
	) );
}

lordcros_core_vc_shortcode_room_search_form();