$(document).ready(function() { 

	"use strict";

	// Variables
	
	var triggerVid;
	var launchkit_hoverGallery;


    // Disable default click on a with blank href

    $('a').click(function() {
        if ($(this).attr('href') === '#') {
            return false;
        }
    });
    
    // Smooth scroll to inner links
	
	$('.inner-link').smoothScroll({
		offset: -59,
		speed: 800
	});

    // TweenMAX Scrolling override on Windows for a smoother experience

    if (navigator.appVersion.indexOf("Win") != -1) {
        if (navigator.userAgent.toLowerCase().indexOf('chrome') > -1) {
            $(function() {

                var $window = $(window);
                var scrollTime = 0.4;
                var scrollDistance = 350;

                $window.on("mousewheel DOMMouseScroll", function(event) {

                    event.preventDefault();

                    var delta = event.originalEvent.wheelDelta / 120 || -event.originalEvent.detail / 3;
                    var scrollTop = $window.scrollTop();
                    var finalScroll = scrollTop - parseInt(delta * scrollDistance);

                    TweenMax.to($window, scrollTime, {
                        scrollTo: {
                            y: finalScroll,
                            autoKill: true
                        },
                        ease: Power1.easeOut,
                        overwrite: 5
                    });

                });
            });
        }
    }

    // Sticky nav

    if (!$('nav').hasClass('overlay')) {
        $('.nav-container').css('min-height', $('nav').outerHeight());
    }
    
    // Set bg of nav container if dark skin
    
    if($('nav').hasClass('dark')){
    	$('.nav-container').addClass('dark');
    	$('.main-container').find('section:nth-of-type(1)').css('outline', '40px solid #222');
    }

    $(window).scroll(function() {
        if ($(window).scrollTop() > 0) {
            $('nav').addClass('fixed');
        } else {
            $('nav').removeClass('fixed');
        }

        if ($(window).scrollTop() > $('nav').outerHeight()) {
            $('nav').addClass('shrink');
        } else {
            $('nav').removeClass('shrink');
        }
    });

    // Mobile nav

    $('.mobile-toggle').click(function() {
        $(this).closest('nav').toggleClass('nav-open');
        if ($(this).closest('nav').hasClass('nav-3')) {
            $(this).toggleClass('active');
        }
    });

    // Fixed header scrolling for desktop browsers

	parallaxBackground();
	$(window).scroll(function(){
		requestAnimationFrame(parallaxBackground);
	});

    if (!(/Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/i).test(navigator.userAgent || navigator.vendor || window.opera)) {
        $(window).scroll(function() {
            requestAnimationFrame(fixedHeader);
            
        });
    }

    // Initialize sliders

    $('.hero-slider').flexslider({
        directionNav: false
    });
    $('.slider').flexslider({
        directionNav: false
    });

    // Append .background-image-holder <img>'s as CSS backgrounds

    $('.background-image-holder').each(function() {
        var imgSrc = $(this).children('img').attr('src');
        $(this).css('background', 'url("' + imgSrc + '")');
        $(this).children('img').hide();
        $(this).css('background-position', '50% 50%');
    });
    
    // Fade in background images
	
	setTimeout(function(){
		$('.background-image-holder').each(function() {
			$(this).addClass('fadeIn');
		});
    },200);


    // Hook up video controls on local video

    $('.local-video-container .play-button').click(function() {
        $(this).toggleClass('video-playing');
        $(this).closest('.local-video-container').find('.background-image-holder').toggleClass('fadeout');
        var video = $(this).closest('.local-video-container').find('video');
        if (video.get(0).paused === true) {
            video.get(0).play();
        } else {
            video.get(0).pause();
        }
    });

    $('video').bind("pause", function() {
        var that = this;
        triggerVid = setTimeout(function() {
            $(that).closest('section').find('.play-button').toggleClass('video-playing');
            $(that).closest('.local-video-container').find('.background-image-holder').toggleClass('fadeout');
            $(that).closest('.modal-video-container').find('.modal-video').toggleClass('reveal-modal');
        }, 100);
    });

    $('video').on('play', function() {
        if (typeof triggerVid === 'number') {
            clearTimeout(triggerVid);
        }
    });

    $('video').on('seeking', function() {
        if (typeof triggerVid === 'number') {
            clearTimeout(triggerVid);
        }
    });

    // Video controls for modals

    $('.modal-video-container .play-button').click(function() {
        $(this).toggleClass('video-playing');
        $(this).closest('.modal-video-container').find('.modal-video').toggleClass('reveal-modal');
        $(this).closest('.modal-video-container').find('video').get(0).play();
    });

    $('.modal-video-container .modal-video').click(function(event) {
        var culprit = event.target;
        if ($(culprit).hasClass('modal-video')) {
            $(this).find('video').get(0).pause();
        }
    });

    // Hover gallery
    $('.hover-gallery').each(function(){
    	var that = $(this);
    	var timerId = setInterval(function(){scrollHoverGallery(that);}, $(this).closest('.hover-gallery').attr('speed'));
		$(this).closest('.hover-gallery').attr('timerId', timerId );
		
		$(this).find('li').bind('hover, mouseover, mouseenter, click', function(e){
			e.stopPropagation();
			clearInterval(timerId);
		});
	
	});
	

    $('.hover-gallery li').mouseenter(function() {
        clearInterval($(this).closest('.hover-gallery[timerId]').attr('timerId'));
        $(this).parent().find('li.active').removeClass('active');
        $(this).addClass('active');
    });
    
    // Pricing table remove emphasis on hover

    $('.pricing-option').mouseenter(function() {
        $(this).closest('.pricing').find('.pricing-option').removeClass('active');
        $(this).addClass('active');
    });

    // Map overlay switch

    $('.map-toggle .switch').click(function() {
        $(this).closest('.contact').toggleClass('toggle-active');
        $(this).toggleClass('toggle-active');
    });

    // Twitter Feed

    $('.tweets-feed').each(function(index) {
        $(this).attr('id', 'tweets-' + index);
    }).each(function(index) {

        function handleTweets(tweets) {
            var x = tweets.length;
            var n = 0;
            var element = document.getElementById('tweets-' + index);
            var html = '<ul class="slides">';
            while (n < x) {
                html += '<li>' + tweets[n] + '</li>';
                n++;
            }
            html += '</ul>';
            element.innerHTML = html;
            return html;
        }

        twitterFetcher.fetch($('#tweets-' + index).attr('data-widget-id'), '', 5, true, true, true, '', false, handleTweets);

    });

    // Instagram Feed

    jQuery.fn.spectragram.accessData = {
        accessToken: '1406933036.fedaafa.feec3d50f5194ce5b705a1f11a107e0b',
        clientID: 'fedaafacf224447e8aef74872d3820a1'
    };

    $('.instafeed').each(function() {
        $(this).children('ul').spectragram('getUserFeed', {
            query: $(this).attr('data-user-name')
        });
    });

    $('#login').click(function (e) {
		e.preventDefault();
		$('#login-popup').toggle();
	});
    $('#toSign').click(function (e) {
		$('#login-popup').hide();
	});
    // Remove screen when user clicks on the map, then add it again when they scroll
    
    $('.screen').click(function(){
    	$(this).removeClass('screen');
    });
    
    $(window).scroll(function(){
    	$('.contact-2 .map-holder').addClass('screen');
    });
	
	$('input.mail').each(function () {
		var rules = $(this).attr('data-nette-rules');
		if (rules) {
			rules += ",{op:':serverMail',msg:'This mail is already registered.'}";
			$(this).attr('data-nette-rules', rules);
		}
	});

}); 

$(window).load(function() { 

	"use strict";

    // Initialize twitter feed

    var setUpTweets = setInterval(function() {
        if ($('.tweets-slider').find('li.flex-active-slide').length) {
            clearInterval(setUpTweets);
            return;
        } else {
            if ($('.tweets-slider').length) {
                $('.tweets-slider').flexslider({
                    directionNav: false,
                    controlNav: false
                });
            }
        }
    }, 500);

    // Append Instagram BGs

    var setUpInstagram = setInterval(function() {
        if ($('.instafeed li').hasClass('bg-added')) {
            clearInterval(setUpInstagram);
            return;
        } else {
            $('.instafeed li').each(function() {

                // Append background-image <img>'s as li item CSS background for better responsive performance
                var imgSrc = $(this).find('img').attr('src');
                $(this).css('background', 'url("' + imgSrc + '")');
                $(this).find('img').css('opacity', 0);
                $(this).css('background-position', '50% 0%');
                // Check if the slider has a color scheme attached, if so, apply it to the slider nav
                $(this).addClass('bg-added');
            });
            $('.instafeed').addClass('fadeIn');
        }
    }, 500);

}); 

function scrollHoverGallery(gallery){
	var nextActiveSlide = $(gallery).find('li.active').next();

	if (nextActiveSlide.length === 0) {
		nextActiveSlide = $(gallery).find('li:first-child');
	}

	$(gallery).find('li.active').removeClass('active');
	nextActiveSlide.addClass('active');
}

function fixedHeader() {
    if ($(window).scrollTop() < $('.fixed-header').outerHeight()) {
        var scroll = $(window).scrollTop();
        if (scroll < 0) {
            $('.fixed-header').css({
                transform: 'translateY(0px)'
            });
        } else {
            $('.fixed-header').css({
                transform: 'translateY(' + scroll / 1.2 + 'px)'
            });
        }
    }
}

function parallaxBackground(){
	$('.parallax').each(function(){
		var element = $(this).closest('section');
		var scrollTop = $(window).scrollTop();
    	var scrollBottom = scrollTop + $(window).height();
    	var elemTop = element.offset().top;
    	var elemBottom = elemTop + element.outerHeight();

		if((scrollBottom > elemTop) && (scrollTop < elemBottom)){
			var value = ((scrollBottom - elemTop)/5);
			$(element).find('.parallax').css({
                transform: 'translateY(' + value + 'px)'
            });
            
		}
	});	
}