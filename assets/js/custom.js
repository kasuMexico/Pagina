//ESte javascript realiza los efectos visuales de la pagina
(function ($) {
	"use strict";
	// Window Resize Mobile Menu Fix
	mobileNav();
	// Scroll animation init
	window.sr = new scrollReveal();
	// Menu Dropdown Toggle
	if($('.menu-trigger').length){
		$(".menu-trigger").on('click', function() {
			$(this).toggleClass('active');
			$('.header-area .nav').slideToggle(200);
			var expanded = $(this).attr('aria-expanded') === 'true';
			$(this).attr('aria-expanded', String(!expanded));
			$('#primary-nav').attr('aria-hidden', String(expanded));
		});
	}
	// Menu elevator animation
	$('a[href*=\\#]:not([href=\\#])').on('click', function() {
		if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
			var target = $(this.hash);
			target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
			if (target.length) {
				var width = $(window).width();
				if(width < 991) {
					$('.menu-trigger').removeClass('active');
					$('.header-area .nav').slideUp(200);
					$('.menu-trigger').attr('aria-expanded', 'false');
					$('#primary-nav').attr('aria-hidden', 'true');
				}
				$('html,body').animate({
					scrollTop: (target.offset().top) - 130
				}, 700);
				return false;
			}
		}
	});
	$(document).ready(function () {
	    $(document).on("scroll", onScroll);

	    //smoothscroll
	    $('a[href^="#"]').on('click', function (e) {
	        e.preventDefault();
	        $(document).off("scroll");

	        $('.header-area .nav a').each(function () {
	            $(this).removeClass('active');
	        });
	        $(this).addClass('active');

	        var target = this.hash,
	        menu = target;
	       	var target = $(this.hash);
	        $('html, body').stop().animate({
	            scrollTop: (target.offset().top) - 130
	        }, 500, 'swing', function () {
	            window.location.hash = target;
	            $(document).on("scroll", onScroll);
	        });
	    });
	});
	function onScroll(event){
	    var scrollPos = $(document).scrollTop();
	    $('.header-area .nav a').each(function () {
	        var currLink = $(this);
	        var href = currLink.attr("href") || "";
	        if (href.charAt(0) !== '#') {
	            return;
	        }
	        var refElement = $(href);
	        if (!refElement.length) {
	            return;
	        }
	        if (refElement.position().top <= scrollPos && refElement.position().top + refElement.height() > scrollPos) {
	            $('.header-area .nav a').removeClass("active");
	            currLink.addClass("active");
	        } else {
	            currLink.removeClass("active");
	        }
	    });
	}
	// Home seperator
	if($('.home-seperator').length) {
		$('.home-seperator .left-item, .home-seperator .right-item').imgfix();
	}
	// Home number counterup
	if($('.count-item').length){
		$('.count-item strong').counterUp({
			delay: 10,
			time: 1000
		});
	}
	// Page loading animation
	$(window).on('load', function() {
		if($('.cover').length){
			$('.cover').parallax({
				imageSrc: $('.cover').data('image'),
				zIndex: '1'
			});
		}
		$("#preloader").animate({
			'opacity': '0'
		}, 600, function(){
			setTimeout(function(){
				$("#preloader").css("visibility", "hidden").fadeOut();
			}, 300);
		});
	});
	// Window Resize Mobile Menu Fix
	$(window).on('resize', function() {
		mobileNav();
	});
	// Window Resize Mobile Menu Fix
	function mobileNav() {
		var width = $(window).width();
		if (width < 992) {
			var isActive = $('.menu-trigger').hasClass('active');
			$('#primary-nav').attr('aria-hidden', String(!isActive));
		} else {
			$('#primary-nav').attr('aria-hidden', 'false');
			$('.menu-trigger').attr('aria-expanded', 'false').removeClass('active');
			$('.header-area .nav').removeAttr('style');
		}
		$('.submenu').on('click', function() {
			if(width < 992) {
				$('.submenu ul').removeClass('active');
				$(this).find('ul').toggleClass('active');
			}
		});
	}
})(window.jQuery);
