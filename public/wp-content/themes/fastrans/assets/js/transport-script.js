/* -----------------------------------------------------------------------------



File:           JS Core
Version:        1.0
Last change:    00/00/00 
-------------------------------------------------------------------------------- */
(function($) {

	"use strict";

	$(window).on("scroll", function(){
		var ScrollBarPos = $(this).scrollTop(); 
		if(ScrollBarPos > 150 ) {
			$(".main-header").addClass("fixed-header"); 
		} else {
			$(".main-header").removeClass("fixed-header");
		}
	}); 

	//Submenu Dropdown Toggle
	if($('.main-header .navigation li.menu-item-has-children').length){
		$('.main-header .navigation li.menu-item-has-children').append('<div class="dropdown-btn"><span class="fa fa-angle-down"></span></div>');
		
		//Dropdown Button
		$('.main-header li.menu-item-has-children .dropdown-btn').on('click', function() {
			$(this).prev('ul').slideToggle(500);
		});
		
		//Megamenu Toggle
		$('.main-header .main-menu li.menu-item-has-children .dropdown-btn').on('click', function() {
			$(this).prev('.mega-menu').slideToggle(500);
		});
		
		//Disable dropdown parent link
		$('.main-header .navigation li.menu-item-has-children > a,.hidden-bar .side-menu li.menu-item-has-children > a').on('click', function(e) {
			e.preventDefault();
		});
	}

	
	
	//Mobile Nav Hide Show
	if($('.mobile-menu').length){
		
		$('.mobile-menu .menu-box').mCustomScrollbar();
		
		var mobileMenuContent = $('.main-header .nav-outer .main-menu').html();
		$('.mobile-menu .menu-box .menu-outer').append(mobileMenuContent);
		$('.sticky-header .main-menu').append(mobileMenuContent);
		
		//Dropdown Button
		$('.mobile-menu li .dropdown-btn').on('click', function() {
			$(this).toggleClass('open');
			$(this).prev('ul').slideToggle(500);
		});
		//Menu Toggle Btn
		$('.mobile-nav-toggler').on('click', function() {
			$('body').addClass('mobile-menu-visible');
		});

		//Menu Toggle Btn
		$('.mobile-menu .menu-backdrop, .mobile-menu .close-btn').on('click', function() {
			$('body').removeClass('mobile-menu-visible');
		});
		
	}

	function updateDisplayedRatings() {
		var reviewRegex = /(\b4[.,]9\s+von\s+5[.,]0\s+aus\s+)(\d+)(\s+Bewertungen\b)/i;

		$('body').find('*').contents().filter(function() {
			return this.nodeType === 3 && (
				this.nodeValue.indexOf('Bewertungen') !== -1 ||
				this.nodeValue.indexOf('Ausgezeichnet') !== -1
			);
		}).each(function() {
			if (reviewRegex.test(this.nodeValue)) {
				this.nodeValue = this.nodeValue.replace(reviewRegex, '$1147$3');
			}

			if (/^\s*50\s+Bewertungen\s*$/i.test(this.nodeValue)) {
				this.nodeValue = this.nodeValue.replace(/50/i, '147');
			}
		});
	}

	$(window).on('load', updateDisplayedRatings);
	$(document).ready(updateDisplayedRatings);

	if (typeof MutationObserver !== 'undefined') {
		var ratingObserver = new MutationObserver(function() {
			updateDisplayedRatings();
		});

		$(window).on('load', function() {
			if (document.body) {
				ratingObserver.observe(document.body, {
					childList: true,
					subtree: true
				});
			}
		});
	}

})(window.jQuery);
