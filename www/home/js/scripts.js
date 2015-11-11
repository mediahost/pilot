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

	// Close mobile menu once link is clicked

	$('.menu li a').click(function(){
		if($('nav').hasClass('nav-open')){
			$('nav').removeClass('nav-open');
		}
	});


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

    });

    if(!$('nav').hasClass('fixed') && !$('nav').hasClass('overlay')){

        // Compensate the height of parallax element for inline nav

        if($(window).width() > 768){
            $('.parallax:first-child .background-image-holder').css('top', -($('nav').outerHeight(true)));
        }

        // Adjust fullscreen elements
        if($(window).width() > 768 && ($('section.parallax:first-child, header.parallax:first-child').outerHeight() == $(window).height()) ){
            $('section.parallax:first-child, header.parallax:first-child').css('height', ($(window).height() - $('nav').outerHeight(true)));
        }
    }

    // Mobile nav

    $('.mobile-toggle').click(function() {
        $(this).closest('nav').toggleClass('nav-open');
        if ($(this).closest('nav').hasClass('nav-3')) {
            $(this).toggleClass('active');
        }
    });

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

    // Sort tabs into 2 ul's

    $('.tabbed-content').each(function(){
    	$(this).append('<ul class="content"></ul>');
    });

    $('.tabs li').each(function(){
    	var originalTab = $(this), activeClass = "";
    	if(originalTab.is('.tabs li:first-child')){
    		activeClass = ' class="active"';
    	}
    	var tabContent = originalTab.find('.tab-content').detach().wrap('<li'+activeClass+'></li>').parent();
    	originalTab.closest('.tabbed-content').find('.content').append(tabContent);
    });

    $('.tabs li').click(function(){
    	$(this).closest('.tabs').find('li').removeClass('active');
    	$(this).addClass('active');
    	var liIndex = $(this).index() + 1;
    	$(this).closest('.tabbed-content').find('.content li').removeClass('active');
    	$(this).closest('.tabbed-content').find('.content li:nth-child('+liIndex+')').addClass('active');
    });


    // Contact form code

    var validateEmail = function (email) {
        var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
        return re.test(email);
    };

    var addError = function ($form, error) {
        var $error = $form.find('.form-error');
        if (!$error.length) {
            $error = $('<div class="form-error" />').hide().appendTo($form);
        }
        $error.text(error).fadeIn();
        setTimeout(function(){
            $error.fadeOut();
        }, 2000);
    };

    $('form.form-email input[name=email]').on('change', function(){
        var $form = $(this).closest('form');
        var $email = $(this);
        var email = $(this).val();
        if (!validateEmail(email)) {
            addError($form, $(this).data('invalid'));
        } else if (!$form.hasClass('no-reg')) {
            var url = 'https://source-code.com/ajax/validate-mail?mail=' + encodeURIComponent(email);
            $.get(url, function (data) {
                if (!data.valid) {
                    addError($form, $email.data('already-registered'));
                }
            }, 'json');
        }
    });

    $('form.form-email').submit(function(e) {
        var $password = $(this).find('input[name=password]');
        var $email = $(this).find('input[name=email]');
        var $form = $(this);


        if ($form.hasClass('no-reg')) {

            var error = false;
            var $name = $(this).find('input[name=name]');
            var $message = $(this).find('textarea[name=message]');
            if ($message.val() == '') {
                addError($form, $message.data('required'));
                error = true;
            }
            if ($email.val() == '') {
                addError($form, $email.data('required'));
                error = true;
            } else if (!validateEmail($email.val())) {
                addError($form, $email.data('invalid'));
                error = true;
            }
            if ($name.val() == '') {
                addError($form, $name.data('required'));
                error = true;
            }
            if (!error) {
                $form.addClass('submit');
                $form.submit();
            }
        } else {
            if ($form.hasClass('submit')) {
                return;
            }
            console.log('test');
            var url = 'https://source-code.com/ajax/validate-mail?mail=' + encodeURIComponent($email.val());
            $.get(url, function (data) {
                var error = false;
                if (!data.valid) {
                    addError($form, $email.data('already-registered'));
                    error = true;
                }
                if ($email.val() == '') {
                    addError($form, $email.data('required'));
                    error = true;
                } else if (!validateEmail($email.val())) {
                    addError($form, $email.data('invalid'));
                    error = true;
                };
                if ($password.val() == '') {
                    addError($form, $password.data('required'));
                    error = true;
                }
                if (!error) {
                    $form.addClass('submit');
                    $form.submit();
                }
            }, 'json');
        }

        if (e.preventDefault) e.preventDefault();
        else e.returnValue = false;
        return false;
    });
    // End Contact Form Code

    // Get referrer from URL string
    if (getURLParameter("ref")) {
        $('form.form-email').append('<input type="text" name="referrer" class="hidden" value="' + getURLParameter("ref") + '"/>');
    }

    function getURLParameter(name) {
        return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null;
    }



    $('.validate-required, .validate-email').on('blur change', function() {
        validateFields($(this).closest('form'));
    });

    $('form').each(function() {
        if ($(this).find('.form-error').length) {
            $(this).attr('original-error', $(this).find('.form-error').text());
        }
    });

    function validateFields(form) {
        var name, error, originalErrorMessage;

        $(form).find('.validate-required[type="checkbox"]').each(function() {
            if (!$('[name="' + $(this).attr('name') + '"]:checked').length) {
                error = 1;
                name = $(this).attr('name').replace('[]', '');
                form.find('.form-error').text('Please tick at least one ' + name + ' box.');
            }
        });

        $(form).find('.validate-required').each(function() {
            if ($(this).val() === '') {
                $(this).addClass('field-error');
                error = 1;
            } else {
                $(this).removeClass('field-error');
            }
        });

        $(form).find('.validate-email').each(function() {
            if (!(/(.+)@(.+){2,}\.(.+){2,}/.test($(this).val()))) {
                $(this).addClass('field-error');
                error = 1;
            } else {
                $(this).removeClass('field-error');
            }
        });

        if (!form.find('.field-error').length) {
            form.find('.form-error').fadeOut(1000);
        }

        return error;
    }

    // Remove screen when user clicks on the map, then add it again when they scroll

    $('.screen').click(function(){
    	$(this).removeClass('screen');
    });

    $(window).scroll(function(){
    	$('.contact-2 .map-holder').addClass('screen');
    });

});

$(window).load(function() {

	"use strict";

	// Sticky nav

    if (!$('nav').hasClass('overlay')) {
    	$('.nav-container').css('min-height', $('.navbar').height());
    }

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