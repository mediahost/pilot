var Homepage = function() {

    var handleVideoPlay = function() {
        $(".videoFrame .preview").click(function() {
            $(this).hide();
            $(".videoFrame .video").show();
        });
    };

    var sleep = function(millis, callback, arg1) {
        setTimeout(function() {
            callback(arg1);
        }, millis);
    };

    var timelineShow1 = function(time) {
        $("#timeline .first span").fadeIn(time);
        $("#timeline .line").removeClass("null").addClass("first");
    };

    var timelineShow2 = function(time) {
        $("#timeline .second span").fadeIn(time);
        $("#timeline .line").removeClass("first").addClass("second");
    };

    var timelineShow3 = function(time) {
        $("#timeline .third span").fadeIn(time);
        $("#timeline .line").removeClass("second").addClass("third");
    };

    var timelineShow4 = function(time) {
        $("#timeline .fourth span").fadeIn(time);
        $("#timeline .line").removeClass("third").addClass("fourth");
    };

    var timelineShow5 = function(time) {
        $("#timeline .fifth span").fadeIn(time);
        $("#timeline .line").removeClass("fourth").addClass("fifth");
    };

    var timelineRun = function(sleepTime, fadeTime) {
        if ($("#timeline .line").hasClass("null")) {
            sleep(sleepTime, timelineShow1, fadeTime);
            sleep(sleepTime * 2, timelineShow2, fadeTime);
            sleep(sleepTime * 3, timelineShow3, fadeTime);
            sleep(sleepTime * 4, timelineShow4, fadeTime);
            sleep(sleepTime * 5, timelineShow5, fadeTime);
        }
    };

    var handleTimeline = function(sleepTime, fadeTime) {

        var timelineY = $("#timeline .line").offset().top + 100;
        var scrolled = window.innerHeight;
        
        if (timelineY < scrolled) {
            timelineRun(sleepTime, fadeTime);
        } else {
            $(window).scroll(function() {
                scrolled += window.scrollY;
                if (timelineY < scrolled) {
                    timelineRun(sleepTime, fadeTime);
                }
            });
        }    

    };

    return {
        //main function to initiate the module
        init: function() {
            handleVideoPlay();
            handleTimeline(1000, 500);
        }

    };

}();