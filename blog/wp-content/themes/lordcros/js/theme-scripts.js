/**
 * LordCros Theme Scripts
 */
window.theme = {};

// Theme Configuration
(function(theme, $) {
	"use strict";

	theme = theme || {};

	$.extend(theme, {
		ajax_nonce : js_lordcros_vars.ajax_nonce,
		ajaxurl : js_lordcros_vars.ajax_url,
		timer_days : js_lordcros_vars.timer_days,
		timer_hours : js_lordcros_vars.timer_hours,
		timer_mins : js_lordcros_vars.timer_mins,
		timer_sec : js_lordcros_vars.timer_sec,
		stripe_publishable_key : js_lordcros_vars.stripe_publishable_key,
		month_Jan : js_lordcros_vars.month_Jan,
		month_Feb : js_lordcros_vars.month_Feb,
		month_Mar : js_lordcros_vars.month_Mar,
		month_Apr : js_lordcros_vars.month_Apr,
		month_May : js_lordcros_vars.month_May,
		month_Jun : js_lordcros_vars.month_Jun,
		month_Jul : js_lordcros_vars.month_Jul,
		month_Aug : js_lordcros_vars.month_Aug,
		month_Sep : js_lordcros_vars.month_Sep,
		month_Oct : js_lordcros_vars.month_Oct,
		month_Nov : js_lordcros_vars.month_Nov,
		month_Dec : js_lordcros_vars.month_Dec,
		month_short_Jan : js_lordcros_vars.month_short_Jan,
		month_short_Feb : js_lordcros_vars.month_short_Feb,
		month_short_Mar : js_lordcros_vars.month_short_Mar,
		month_short_Apr : js_lordcros_vars.month_short_Apr,
		month_short_May : js_lordcros_vars.month_short_May,
		month_short_Jun : js_lordcros_vars.month_short_Jun,
		month_short_Jul : js_lordcros_vars.month_short_Jul,
		month_short_Aug : js_lordcros_vars.month_short_Aug,
		month_short_Sep : js_lordcros_vars.month_short_Sep,
		month_short_Oct : js_lordcros_vars.month_short_Oct,
		month_short_Nov : js_lordcros_vars.month_short_Nov,
		month_short_Dec : js_lordcros_vars.month_short_Dec,
		week_Sun : js_lordcros_vars.week_Sun,
		week_Mon : js_lordcros_vars.week_Mon,
		week_Tue : js_lordcros_vars.week_Tue,
		week_Wed : js_lordcros_vars.week_Wed,
		week_Thu : js_lordcros_vars.week_Thu,
		week_Fri : js_lordcros_vars.week_Fri,
		week_Sat : js_lordcros_vars.week_Sat,
		coupon_code_error_msg : js_lordcros_vars.coupon_code_error_msg
	});
}).apply(this, [window.theme, jQuery]);

// Basic Functions
(function(theme, $) {
	"use strict";

	theme = theme || {};

	$.extend(theme, {
		basicFunctions: function() {
			// SubMenu Shift Pos
			$('header .sub-menu-dropdown, .lordcros-sticky-header .sub-menu-dropdown').each(function(e) {
				var subMenuWidth = $(this).outerWidth(),
					SubMenuLeftPos = $(this).offset().left;
				
				if ( $(window).width() < subMenuWidth + SubMenuLeftPos ) {
					$(this).addClass('submenu-shift');
				}
			});

			// Header Sticky Setting
			var headerHeight = $('header.lordcros-header').outerHeight();

			if ( $('.lordcros-sticky-header').length > 0 ) {
				$(window).scroll(function(e) {
					if ( $(this).scrollTop() > headerHeight ) {
						$('.lordcros-sticky-header').addClass('page-scroll');
					} else {
						$('.lordcros-sticky-header').removeClass('page-scroll');
					}
				});
			}

			// Append Header Nav Drop
			$('.leftside-header.header-style-6 .menu-item-has-children').append('<div class="drop-nav"><i class="fas fa-angle-down"></i></div>');

			$('.leftside-header.header-style-6').on('click', '.drop-nav', function(e) {
				e.stopPropagation();
				
				$(this).parent().toggleClass('active-submenu').children('.sub-menu-dropdown').toggleClass('opened');

				if ( $(this).parent().children('.sub-menu-dropdown').hasClass('opened') ) {
					$(this).parent().children('.sub-menu-dropdown').slideDown(300);
				} else {
					$(this).parent().children('.sub-menu-dropdown').slideUp(300);
				}
			});

			// Tooltips
			$('[data-toggle="tooltip"]').tooltip();

			// Tab Initialize
			$('body .tab-wrapper').each(function(e) {
				$(this).find('.nav-tabs .tab-pane-title:first-child a').addClass('active show');
				$(this).find('.tab-content .tab-pane:first-child').addClass('active show');
			});

			// Element Margin Space
			$('.lordcros-shortcode-rooms-wrapper').each(function(e) {
				var $this = $(this),
					marginVal = $this.data('margin-val'),
					marginHalfVal = marginVal / 2;

				if ( $this.hasClass('owl-carousel') ) {
					return;
				}

				$this.find('.room-content-view-wrap').css({ 'padding-left': marginHalfVal + 'px', 'padding-right': marginHalfVal + 'px' });
				$this.find('.room-block-view, .room-grid-view').css( 'margin-bottom', marginVal + 'px' );
			});

			var elementMarginGap = function( $wrapper, $item ) {
				var $this = $wrapper,
					marginVal = $this.data('margin-val'),
					marginHalfVal = marginVal / 2;
				
				if ( 'undefined' == typeof marginVal ) {
					return;
				}

				$this.find($item).css({ 'padding-left': marginHalfVal + 'px', 'padding-right': marginHalfVal + 'px' });
				$this.find($item).css( 'margin-bottom', marginVal + 'px' );
			}

			$('.lordcros-shortcode-services-wrapper').each(function(e) {
				elementMarginGap( $(this), '.service-item' );
			});

			$('.image-gallery-inner').each(function(e) {
				elementMarginGap( $(this), '.lordcros-image-wrap' );
				$(this).addClass('added-gap').trigger('classChanged');
			});

			$('.lordcros-shortcode-posts-inner').each(function(e) {
				if ( $(this).hasClass('owl-carousel') ) {
					return;
				}

				elementMarginGap( $(this), '.blog-post-item' );
			});

			// Archive Room Image Carousel
			$('.available-rooms-wrap .room-gallery').owlCarousel({
				loop: true,
				nav: true,
				dots: false,
				items: 1,
				onRefreshed: function() {
					$(window).resize();
				}
			});

			// Hamburger Navigation
			$('.lordcros-burger-wrapper').on('click', function(e) {
				$(this).find('.hamburger').toggleClass('active');

				if ( ! $(this).hasClass('mobile-burger') ) {
					$('.lordcros-hamburger-menu-wrap').toggleClass('open');
					$('html').toggleClass('noScroll hamburger-menu-opened');
				}
			});

			// Mobile Navigation Settings
			$('.header-mobile-nav').off('click').on('click', function(e) {
				e.preventDefault();
				$('body').toggleClass('mobile-nav-active');

				return false;
			});

			$(document).on('click', 'body', function(e) {
				if ( $('.mobile-nav-active .mobile-nav').length > 0 && ! $(e.target).is('.mobile-nav-active .mobile-nav, .mobile-nav-active .mobile-nav *') ) {
					$('body').removeClass('mobile-nav-active');
					$('.lordcros-burger-wrapper.mobile-burger .hamburger').removeClass('active');
				}
			});

			$('.mobile-nav .close-btn .close-btn-link').on('click', function(e) {
				e.preventDefault();
				$('body').removeClass('mobile-nav-active');
				$('.lordcros-burger-wrapper.mobile-burger .hamburger').removeClass('active');
			});

			$('.lordcros-mobile-nav .menu-item-has-children').append('<div class="drop-nav"><i class="lordcros lordcros-angle-down"></i></div>');

			$('.lordcros-mobile-nav').on('click', '.drop-nav', function(e) {
				e.stopPropagation();
				
				$(this).parent().toggleClass('active-submenu').children('.sub-menu-dropdown').toggleClass('opened');

				if ( $(this).parent().children('.sub-menu-dropdown').hasClass('opened') ) {
					$(this).parent().children('.sub-menu-dropdown').slideDown(300);
				} else {
					$(this).parent().children('.sub-menu-dropdown').slideUp(300);
				}
			});

			// Full Screen Search Popup
			$('.search-button .full-screen-search').each(function() {
				$(this).on('click', function(e) {
					e.preventDefault();

					$('#lordcros-full-screen-search').removeClass('closed').addClass('opened');
				});
			});

			$('#lordcros-full-screen-search .form-close').on('click', function(e) {
				$('#lordcros-full-screen-search').removeClass('opened').addClass('closed');
			});
		},

		cookiesPopup: function() { 

			if ( typeof $.cookie === "undefined" ) { 
				return;
			}

			var cookies_law_version = theme.cookies_law_version;

			if ( $.cookie( 'lordcros_cookies_' + cookies_law_version ) == 'accepted' ) {
				return;
			}

			var popup = $( '.lordcros-cookies-popup' );

			setTimeout( function() {
				popup.addClass( 'popup-display' );
				popup.on( 'click', '.accept-cookie-btn', function(e) {
					e.preventDefault();
					acceptCookies();
				} )
			}, 1000 );

			var acceptCookies = function() {
				popup.removeClass('popup-display').addClass('popup-hide');
				$.cookie( 'lordcros_cookies_' + cookies_law_version, 'accepted', { 
					expires: 60, 
					path: '/' 
				} );
			};
		},

		// CountDown
		CountDown : {
			initialize: function() {
				this.events();
				return this;
			},

			events: function() {
				$('.countdown-timer').each(function() {
					var endTime = moment.tz( $(this).data('date-to'), $(this).data('timezone') );
										
					$(this).countdown( endTime.toDate(), function(event) {
						$(this).html( event.strftime( ''
							+ '<span class="time countdown-days">%-D <span>' + theme.timer_days + '</span></span>'
							+ '<span class="time countdown-hours">%H <span>' + theme.timer_hours + '</span></span>'
							+ '<span class="time countdown-min">%M <span>' + theme.timer_mins + '</span></span>'
							+ '<span class="time countdown-sec">%S <span>' + theme.timer_sec + '</span></span>'
						) );
					} );
					
				});
			}
		},

		backToTop: function() {
			$(window).scroll(function() {
				if ( $(this).scrollTop() > 400 && ! $('.back-to-top').hasClass('is-visible') ) {
					$('.back-to-top').addClass('is-visible');
				}

				if ( $(this).scrollTop() < 400 && $('.back-to-top').hasClass('is-visible') ) {
					$('.back-to-top').removeClass('is-visible');
				}
			});

			$('body').off('click', '.back-to-top').on('click', '.back-to-top', function(e) {
				e.preventDefault();

				$('html, body').animate({scrollTop: 0}, 'slow');
				return false;
			});
		},

		initialize: function() {
			this.basicFunctions();
			this.cookiesPopup();
			this.CountDown.initialize();
			this.backToTop();
		}
	});
}).apply(this, [window.theme, jQuery]);

// Check Availity Form
(function(theme, $) {
	"use strict";
	
	$('.lc-booking-date-range-from').datepicker({
		defaultDate: '+1w',
		minDate: 0,
		altFormat: 'M',
		altField: '.lc-booking-date-month-from',
		firstDay: 0,
		dateFormat: 'mm/dd/yy',
		monthNames: [theme.month_Jan, theme.month_Feb, theme.month_Mar, theme.month_Apr, theme.month_May, theme.month_Jun, theme.month_Jul, theme.month_Aug, theme.month_Sep, theme.month_Oct, theme.month_Nov, theme.month_Dec],
		monthNamesShort: [theme.month_short_Jan, theme.month_short_Feb, theme.month_short_Mar, theme.month_short_Apr, theme.month_short_May, theme.month_short_Jun, theme.month_short_Jul, theme.month_short_Aug, theme.month_short_Sep, theme.month_short_Oct, theme.month_short_Nov, theme.month_short_Dec],
		dayNamesMin: [theme.week_Sun, theme.week_Mon, theme.week_Tue, theme.week_Wed, theme.week_Thu, theme.week_Fri, theme.week_Sat],
		changeMonth: false,
		numberOfMonths: 1,
		beforeShow: function(input, inst) {
			var instHeight = 0;

			if ( input.classList.contains('sidebar-form') ) {
				$('#ui-datepicker-div').removeClass('sidebar-form-datepicker search-form-datepicker').addClass('sidebar-form-datepicker');
			} else {
				$('#ui-datepicker-div').removeClass('sidebar-form-datepicker search-form-datepicker').addClass('search-form-datepicker');
			}

			setTimeout( function () {
				instHeight = inst.dpDiv.height();
				var bottomSpace = $(window).height() - input.getBoundingClientRect().top;				

				if( bottomSpace > instHeight ) {
					inst.dpDiv.removeClass('ui-top').addClass('ui-bottom');
				} else {
					inst.dpDiv.removeClass('ui-bottom').addClass('ui-top');
				}
			},0 );
		},
		onClose: function() {
			var form_wrapper = $(this).closest('.room-search-form');
			var minDate = $(this).datepicker("getDate");
			var newMin = new Date(minDate.setDate(minDate.getDate() + 1));
			form_wrapper.find( ".lc-booking-date-range-to" ).datepicker( "option", "minDate", newMin );

			var lc_booking_input_date_from = $(this).val();
			var lc_booking_date_day_from = lc_booking_input_date_from.substring(3, 5);
			var lc_booking_date_year_from = lc_booking_input_date_from.substring(6, 10);
			form_wrapper.find( ".lc-booking-date-day-from" ).val(lc_booking_date_day_from);

			var lc_booking_input_date_to = form_wrapper.find( ".lc-booking-date-range-to" ).val();
			var lc_booking_date_day_to = lc_booking_input_date_to.substring(3, 5);
			var lc_booking_date_year_to = lc_booking_input_date_to.substring(6, 10);
			form_wrapper.find( ".lc-booking-date-day-to" ).val(lc_booking_date_day_to);

			form_wrapper.find( "#form-check-in .day-val" ).text(lc_booking_date_day_from);
			form_wrapper.find( "#form-check-in .year-val" ).text(lc_booking_date_year_from);
			var lc_booking_date_month_from = form_wrapper.find( ".lc-booking-date-month-from" ).val();
			form_wrapper.find( "#form-check-in .month-val" ).text(lc_booking_date_month_from);

			form_wrapper.find( "#form-check-out .day-val" ).text(lc_booking_date_day_to);
			form_wrapper.find( "#form-check-out .year-val" ).text(lc_booking_date_year_to);
			var lc_booking_date_month_to = form_wrapper.find( ".lc-booking-date-month-to" ).val();
			form_wrapper.find( "#form-check-out .month-val" ).text(lc_booking_date_month_to);
		}
	});

	$('.lc-booking-date-range-to').datepicker({
		defaultDate: '+1w',
		minDate: '+1d',
		altFormat: 'M',
		altField: '.lc-booking-date-month-to',
		firstDay: 0,
		dateFormat: 'mm/dd/yy',
		monthNames: [theme.month_Jan, theme.month_Feb, theme.month_Mar, theme.month_Apr, theme.month_May, theme.month_Jun, theme.month_Jul, theme.month_Aug, theme.month_Sep, theme.month_Oct, theme.month_Nov, theme.month_Dec],
		monthNamesShort: [theme.month_short_Jan, theme.month_short_Feb, theme.month_short_Mar, theme.month_short_Apr, theme.month_short_May, theme.month_short_Jun, theme.month_short_Jul, theme.month_short_Aug, theme.month_short_Sep, theme.month_short_Oct, theme.month_short_Nov, theme.month_short_Dec],
		dayNamesMin: [theme.week_Sun, theme.week_Mon, theme.week_Tue, theme.week_Wed, theme.week_Thu, theme.week_Fri, theme.week_Sat],
		changeMonth: false,
		numberOfMonths: 1,
		beforeShow: function(input, inst) {
			var instHeight = 0;

			if ( input.classList.contains('sidebar-form') ) {
				$('#ui-datepicker-div').removeClass('sidebar-form-datepicker search-form-datepicker').addClass('sidebar-form-datepicker');
			} else {
				$('#ui-datepicker-div').removeClass('sidebar-form-datepicker search-form-datepicker').addClass('search-form-datepicker');
			}

			setTimeout( function () {
				instHeight = inst.dpDiv.height();
				var bottomSpace = $(window).height() - input.getBoundingClientRect().top;				

				if( bottomSpace > instHeight ) {
					inst.dpDiv.removeClass('ui-top').addClass('ui-bottom');
				} else {
					inst.dpDiv.removeClass('ui-bottom').addClass('ui-top');
				}
			},0 );
		},
		onClose: function() {
			var form_wrapper = $(this).closest('.room-search-form');

			var lc_booking_input_date_from = form_wrapper.find( ".lc-booking-date-range-from" ).val();
			var lc_booking_date_day_from = lc_booking_input_date_from.substring(3, 5);
			var lc_booking_date_year_from = lc_booking_input_date_from.substring(6, 10);
			form_wrapper.find( ".lc-booking-date-day-from" ).val(lc_booking_date_day_from);

			var lc_booking_input_date_to = $(this).val();
			var lc_booking_date_day_to = lc_booking_input_date_to.substring(3, 5);
			var lc_booking_date_year_to = lc_booking_input_date_to.substring(6, 10);
			form_wrapper.find( ".lc-booking-date-day-to" ).val(lc_booking_date_day_to);

			form_wrapper.find( "#form-check-in .day-val" ).text(lc_booking_date_day_from);
			form_wrapper.find( "#form-check-in .year-val" ).text(lc_booking_date_year_from);
			var lc_booking_date_month_from = form_wrapper.find( ".lc-booking-date-month-from" ).val();
			form_wrapper.find( "#form-check-in .month-val" ).text(lc_booking_date_month_from);

			form_wrapper.find( "#form-check-out .day-val" ).text(lc_booking_date_day_to);
			form_wrapper.find( "#form-check-out .year-val" ).text(lc_booking_date_year_to);
			var lc_booking_date_month_to = form_wrapper.find( ".lc-booking-date-month-to" ).val();
			form_wrapper.find( "#form-check-out .month-val" ).text(lc_booking_date_month_to);
		}
	});

	$('.lc-booking-date-range-from').datepicker("setDate", "+0");
	$('.lc-booking-date-range-to').datepicker("setDate", "+1");

	$('.room-search-form #form-check-in').on('click', function(e) {
		$(this).find('.lc-booking-date-range-from').datepicker('show');
	});

	$('.room-search-form #form-check-out').on('click', function(e) {
		$(this).find('.lc-booking-date-range-to').datepicker('show');
	});

	// Guest count
	$('.search-guest-count .fa-chevron-up').on('click', function(e) {
		var search_guest_count_div = $(this).closest('.search-guest-count');
		var currentVal = search_guest_count_div.find('.guest-val').text();
		currentVal++;

		search_guest_count_div.find('.guest-val').text(currentVal);
		search_guest_count_div.find('.lc-booking-form-guests').val(currentVal);
	});

	$('.search-guest-count .fa-chevron-down').on('click', function(e) {
		var search_guest_count_div = $(this).closest('.search-guest-count');
		var currentVal = search_guest_count_div.find('.guest-val').text();

		if ( 1 < currentVal ) {
			currentVal--;

			search_guest_count_div.find('.guest-val').text(currentVal);
			search_guest_count_div.find('.lc-booking-form-guests').val(currentVal);
		}
	});

	$('#room-check-form [type="submit"]').on('click', function(e) {
		e.preventDefault();

		var room_check_form = $('#room-check-form');

		$.ajax({
			url: theme.ajaxurl,
			type: "POST",
			data: room_check_form.serialize()+'&security='+theme.ajax_nonce,
			success: function(response){
				if (response.success == 1) {
					room_check_form.find("[name='room_price']").val(response.message.total_price);
					room_check_form.submit();
				} else {
					alert(response.message);
				}
			}
		});

		return false;
	});

}).apply(this, [window.theme, jQuery]);

// Price Slider in search page
(function(theme, $) {
	"use strict";

	theme = theme || {};

	var limitMaxPriceVal = $('.service-filter-wrap .price-filter-amount').data( 'list-max-val' );
	var min_price = $('.service-filter-wrap #price-filter-slider').data('min-price');
	var max_price = $('.service-filter-wrap #price-filter-slider').data('max-price');

	$('.service-filter-wrap #price-filter-slider').slider({
		range:true,
		min: 0,
		max: limitMaxPriceVal,
		values: [ min_price, max_price ],
		slide: function( event, ui ) {
			$('.service-filter-wrap .price-filter-amount input[name="min_price"]').val( ui.values[0] );
			$('.price-filter-amount .show-price-values .min-price .price-val').text( ui.values[0] );
			$('.service-filter-wrap .price-filter-amount input[name="max_price"]').val( ui.values[1] );
			$('.price-filter-amount .show-price-values .max-price .price-val').text( ui.values[1] );
		},
		change: function( event, ui ) {
			var url = $('.service-filter-wrap #price-filter-slider').data('url').replace(/&amp;/g, '&');
			url += '&max_price=' + ui.values[1] + '&min_price='+ ui.values[0];
			document.location.href = url;			
		}
	});
}).apply(this, [window.theme, jQuery]);

// Service filter in search page
(function(theme, $) {
	"use strict";

	theme = theme || {};

	$('#form_default_service_filter input[type="checkbox"], #form_extra_service_filter input[type="checkbox"]').on('change', function(){
		var checkbox_name = $(this).attr('name');
		var url = $(this).closest('.service-filter').data('url');
				
		$('input[name="'+checkbox_name+'"]:checked').each(function(index){
			if ( $(this).val() != "" ) {
				url += '&' + checkbox_name + '='+ $(this).val();
			}
		});

		$('input[type="checkbox"').attr( 'disabled', 'disabled' );

		document.location.href = url;
		
	});
}).apply(this, [window.theme, jQuery]);

// Order by in search page
(function(theme, $) {
	"use strict";

	theme = theme || {};

	$('#price-order-by, #size-order-by').on('change', function(){
		
		var url = new URL(location.href);
		var query_string = url.search;
		var search_params = new URLSearchParams(query_string);
		
		search_params.delete('page_num');
		search_params.delete($(this).attr('name'));
		search_params.append($(this).attr('name'), $(this).children('option:selected').val());
		
		url.search = search_params.toString();
		document.location.href = url.toString();
		
	});
}).apply(this, [window.theme, jQuery]);

// Checkout Form
(function(theme, $) {
	"use strict";
	
	$.validator.addMethod( "validate_coupon_code", function(value) {
		
		if ( value ) {
			var room_checkout_form = $('#checkout-form');

			var status = false;
			$.ajax({
				url: theme.ajaxurl,
				type: "POST",
				async: false,
				cache: false,
				timeout: 300,
				data: room_checkout_form.serialize()+'&security='+theme.ajax_nonce,
				success: function(response){
					if (response.success == 1) {
						status = true;
					}
				},
				error: function(){
					return false;
				}
			});
			
			return status;
		}
		
		return true;

	}, theme.coupon_code_error_msg );

	$('#checkout-form').validate({
		onkeyup: false,
		onclick: false,
		onfocusout: false,
		rules: {
			coupon_code: "validate_coupon_code",
		}
	});

}).apply(this, [window.theme, jQuery]);

// Payment Form
(function(theme, $) {
	"use strict";
	
	if ( typeof Stripe != 'undefined' ) {
		Stripe.setPublishableKey( theme.stripe_publishable_key );
	}

	function stripeResponseHandler(status, response) {
		if (response.error) {
			// re-enable the submit button
			$('#stripe-pay-btn').removeAttr("disabled");
			// show the errors on the form
			$(".payment-errors").html(response.error.message);
		} else {
			var form$ = $("#stripe-form");
			// token contains id, last4, and card type
			var token = response['id'];
			// insert the token into the form so it gets submitted to the server
			form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
			// and submit
			form$.get(0).submit();
		}
	}

	$(document).ready(function() {
		//on form submit
		$("#stripe-form").submit(function(event) {
			//disable the submit button to prevent repeated clicks
			$('#stripe-pay-btn').attr("disabled", "disabled");

			//create single-use token to charge the user
			Stripe.createToken({
					number:		$('.card-number').val(),
					cvc:		$('.card-cvc').val(),
					exp_month:	$('.card-expiry-month').val(),
					exp_year:	$('.card-expiry-year').val()
				}, stripeResponseHandler);

			//submit from callback
			return false;
		});
	});

}).apply(this, [window.theme, jQuery]);

// Owl Carousel Navigation Center Mode
(function(theme, $) {
	"use strict";

	$('.lordcros-shortcode-rooms-wrapper.owl-carousel.style2, .lordcros-shortcode-img-carousel.nav-center_pos .image-carousel-inner').each(function(index) {
		var $this = $(this),
			btnLeftPos,
			btnRightPos,
			navPrev,
			navNext,
			containerWidth,
			centerItemWidth;

		var owlNavPos = function() {
			navPrev = $this.find('.owl-prev');
			navNext = $this.find('.owl-next');
			containerWidth = $this.width(),
			centerItemWidth = $this.find('.owl-stage .owl-item.center').width();
			btnLeftPos = ( containerWidth / 2 ) - ( centerItemWidth / 2 ) - 70;
			btnRightPos = btnLeftPos + centerItemWidth + 100;
			navPrev.css( 'left', btnLeftPos ).removeClass('position-set').addClass('position-set');
			navNext.css( 'left', btnRightPos ).removeClass('position-set').addClass('position-set');
		}

		$(this).on('initialized.owl.carousel resized.owl.carousel', function(e) {
			setTimeout(function(e) {
				owlNavPos();
			}, 300);
		});
	});
}).apply(this, [window.theme, jQuery]);

// Services Slider Effects
(function(theme, $) {
	"use strict";

	$('.lordcros-shortcode-services.style3 .services-wrap-slide').each(function(index) {
		var $this = $(this);

		$this.owlCarousel({
			animateOut: 'fadeOutUpLeft',
			animateIn: 'zoomIn',
			loop: true,
			nav: true,
			dots: false,
			items: 1,
			autoHeight: true,
			mouseDrag: false,
			touchDrag: false
		});
	});

	$('.lordcros-shortcode-services.style4 .lordcros-shortcode-services-wrapper').each(function(index) {
		var $this = $(this),
			serviceThumbs = $this.find('.services-thumbs-wrap .thumbs-slider-inner'),
			serviceInfoBoxes = $this.find('.services-infoboxes-wrap'),
			navPrev = $this.find('.service-slider-nav .slider-prev'),
			navNext = $this.find('.service-slider-nav .slider-next');

		serviceThumbs.owlCarousel({
			animateOut: 'fadeOut',
			animateIn: 'fadeInRight',
			loop: true,
			nav: false,
			dots: false,
			items: 1,
			mouseDrag: false,
			touchDrag: false,
			onInitialized: function() {
				navPrev.on('click', function(e) {
					serviceThumbs.find('.owl-nav .owl-prev').click();
				});

				navNext.on('click', function(e) {
					serviceThumbs.find('.owl-nav .owl-next').click();
				});
			}
		});

		serviceInfoBoxes.owlCarousel({
			animateOut: 'fadeOut',
			animateIn: 'fadeInLeft',
			loop: true,
			nav: false,
			dots: false,
			items: 1,
			mouseDrag: false,
			touchDrag: false,
			onInitialized: function() {
				navPrev.on('click', function(e) {
					serviceInfoBoxes.find('.owl-nav .owl-prev').click();
				});

				navNext.on('click', function(e) {
					serviceInfoBoxes.find('.owl-nav .owl-next').click();
				});
			}
		});

		serviceInfoBoxes.trigger('refresh.owl.carousel');
	});
}).apply(this, [window.theme, jQuery]);

// Single Room Slider
(function(theme, $) {
	"use strict";

	var featuredOwlWrap = $('.room-image-gallery .featured-image-slider'),
		thumbsOwlWrap = $('.room-image-gallery .thumbnail-image-slider'),
		roomPageWrap = $('.single-room-page-wrapper');

	var initFeaturedCarousel = function( $animateIn, $animateOut, $loop, $auto, $drag, $margin ) {
		featuredOwlWrap.owlCarousel({
			animateOut: $animateOut,
			animateIn: $animateIn,
			items: 1,
			loop: $loop,
			autoplay: $auto,
			dots: false,
			nav: true,
			navText: false,
			mouseDrag: $drag,
			touchDrag: $drag,
			margin: $margin,
			onInitialized: function() {
				featuredOwlWrap.prev('.room-slider-placeholder').addClass('placeholder-hide');
			},
			onRefreshed: function() {
				$(window).resize();
			}
		});
	}

	var initThumbsCarousel = function() {
		thumbsOwlWrap.owlCarousel({
			items: 4,
			dots: false,
			nav: false,
			nevText: false,
			responsive: {
				479: {
					items: 4,
					margin: 15
				},
				0: {
					items: 3,
					margin: 6
				}
			}
		});

		var thumbsOwl = thumbsOwlWrap.owlCarousel();

		thumbsOwlWrap.on('click', '.owl-item', function(e) {

			var i = $(this).index();
			featuredOwlWrap.trigger('to.owl.carousel', i);
			thumbsOwl.trigger('to.owl.carousel', i);
		});

		featuredOwlWrap.on('changed.owl.carousel', function(e) {
			var i = e.item.index;
			
			thumbsOwl.trigger('to.owl.carousel', i);
			thumbsOwlWrap.find('.current-thumb').removeClass('current-thumb');
			thumbsOwlWrap.find('.gallery-item').eq(i).addClass('current-thumb');
		});

		thumbsOwlWrap.find('.gallery-item').eq(0).addClass('current-thumb');
	}

	var initFeaturedImgPopup = function() {
		$('.featured-image-slider').magnificPopup({
			delegate: 'a',
			type: 'image',
			fixedContentPos: true,
			image: { verticalFit: true },
			gallery: {
				enabled: true,
				navigateByImgClick: true
			},
			removalDelay: 500,
			mainClass: 'mfp-fade'
		});
	}

	if ( roomPageWrap.hasClass('room-slider-content-layout') ) {
		initFeaturedCarousel( false, false, false, false, true, 10 );
		initThumbsCarousel();
		initFeaturedImgPopup();
	}

	if ( roomPageWrap.hasClass('room-slider-header-layout') ) {
		initFeaturedCarousel( 'fadeIn', 'fadeOut', true, true, false, 0 );
	}
}).apply(this, [window.theme, jQuery]);

// Mobile Sidebar Toggle
(function(theme, $) {
	"use strict";

	$('.mobile-sidebar-toggle-btn').on('click', function(e) {
		$('.main-content aside').addClass('mobile-sidebar-open');
		$('body').addClass('mobile-sidebar-active');

		return false;
	});

	$(document).on('click', 'body', function(e) {
		if ( $('.main-content aside.mobile-sidebar-open').length > 0 && ! $(e.target).is('.main-content aside.mobile-sidebar-open, .main-content aside.mobile-sidebar-open *') ) {
			$('body').removeClass('mobile-sidebar-active');
			$('.main-content aside').removeClass('mobile-sidebar-open');
		}
	});

	$('.mobile-sidebar-header .close-btn').on('click', function(e) {
		e.preventDefault();

		$('body').removeClass('mobile-sidebar-active');
		$('.main-content aside').removeClass('mobile-sidebar-open');
	});
}).apply(this, [window.theme, jQuery]);

// VC Video Placeholder
(function(theme, $) {
	"use strict";

	theme = theme || {};

	$('.lordcros-video-placeholder-wrapper').on('click', function() {
		var videoWrapper = $( this ),
			video = videoWrapper.parent().find( 'iframe' ),
			videoScr =  video.attr( 'src' ),
			videoNewSrc = videoScr + '&autoplay=1';

		if  ( videoScr.indexOf( 'vimeo.com' ) + 1 ) {
			videoNewSrc = videoScr + '?autoplay=1';
		}
		
		video.attr( 'src',videoNewSrc );
		videoWrapper.addClass( 'hide-placeholder' );
	});
}).apply(this, [window.theme, jQuery]);

// Dashboard page functions
(function(theme, $) {
	"use strict";

	theme = theme || {};

	$('.dashboard-page-wrap .alert').on('click', '.lordcros-cancel', function(e) {
		$('.dashboard-page-wrap .alert').fadeOut(300);
	});

	$('.dashboard-page-wrap .alert').delay(3000).fadeOut(300);

	$(".dashboard-page-wrap #pills-profile .view-profile").show();
	$(".dashboard-page-wrap #pills-profile .edit-profile").hide();
	
	$(".dashboard-page-wrap #pills-profile .edit-profile-btn").click(function(e) {
		e.preventDefault();
		$(".dashboard-page-wrap #pills-profile .view-profile").fadeOut(0);
		$(".dashboard-page-wrap #pills-profile .edit-profile").fadeIn(250);
	});
	$(".dashboard-page-wrap #pills-profile .view-profile-btn").click(function(e) {
		e.preventDefault();
		$(".dashboard-page-wrap #pills-profile .edit-profile").fadeOut(0);
		$(".dashboard-page-wrap #pills-profile .view-profile").fadeIn(250);
	});

	function readURL(input) {

		if (input.files && input.files[0]) {
			var reader = new FileReader();
			reader.onload = function (e) {
				$('.dashboard-page-wrap #pills-profile #photo_preview img').attr('src', e.target.result);
				$('.dashboard-page-wrap #pills-profile #photo_preview').show();
			}
			reader.readAsDataURL(input.files[0]);
		} else {
			$('.dashboard-page-wrap #pills-profile #photo_preview').hide();
		}
	}

	$('.dashboard-page-wrap #pills-profile .edit-profile input[name="photo"]').change(function(){
		readURL(this);
	});

	var photo_upload = $('.dashboard-page-wrap #pills-profile input[name="photo"]');

	$('.dashboard-page-wrap #pills-profile #photo_preview .close').click(function(){
		photo_upload.replaceWith( photo_upload = photo_upload.clone( true ) );
		$('.dashboard-page-wrap #pills-profile #photo_preview').hide();
		$('.dashboard-page-wrap #pills-profile input[name="remove_photo"').val('1');
	});

	$('.dashboard-page-wrap .booking-status-filter input[name="status"]').change(function(){
		$('.dashboard-page-wrap .booking-history').addClass('ajax-loading');

		update_booking_list();
	});

	$('.dashboard-page-wrap .booking-status-filter button').click(function(e){
		e.preventDefault();

		$('.dashboard-page-wrap .booking-history').addClass('ajax-loading');

		if ( $(this).siblings('input[name="sort_by"]').val() == $(this).val() ) {
			if ( $(this).siblings('input[name="order"]').val() == 'desc' ) {
				$(this).siblings('input[name="order"]').val('asc');
			} else {
				$(this).siblings('input[name="order"]').val('desc');
			}
		} else {
			$(this).siblings('input[name="sort_by"]').val($(this).val());
			$(this).siblings('input[name="order"]').val('desc');
		}

		$('.dashboard-page-wrap .booking-status-filter button').removeClass('active');
		$(this).addClass('active');

		update_booking_list();
		return false;
	});

	function update_booking_list(){
		jQuery.ajax({
			url: theme.ajaxurl,
			type: "POST",
			data: $('.dashboard-page-wrap .booking-status-filter').serialize(),
			success: function(response){
				if ( response.success == 1 ) {
					$('.dashboard-page-wrap .booking-history').html(response.result);
				} else {
					$('.dashboard-page-wrap .booking-history').html(response.result);
				}

				$('.dashboard-page-wrap .booking-history').removeClass('ajax-loading');
			}
		});
	}

	$('.edit-profile-form .datepicker-wrap input').datepicker({
		dateFormat: 'yy-mm-dd',
		monthNames: [theme.month_Jan, theme.month_Feb, theme.month_Mar, theme.month_Apr, theme.month_May, theme.month_Jun, theme.month_Jul, theme.month_Aug, theme.month_Sep, theme.month_Oct, theme.month_Nov, theme.month_Dec],
		monthNamesShort: [theme.month_short_Jan, theme.month_short_Feb, theme.month_short_Mar, theme.month_short_Apr, theme.month_short_May, theme.month_short_Jun, theme.month_short_Jul, theme.month_short_Aug, theme.month_short_Sep, theme.month_short_Oct, theme.month_short_Nov, theme.month_short_Dec],
		dayNamesMin: [theme.week_Sun, theme.week_Mon, theme.week_Tue, theme.week_Wed, theme.week_Thu, theme.week_Fri, theme.week_Sat],
	});
	
}).apply(this, [window.theme, jQuery]);

// LordCros Initialize
(function(theme, $) {
	$(document).ready( function() { 
		theme.initialize();
	} );
}).apply(this, [window.theme, jQuery]);