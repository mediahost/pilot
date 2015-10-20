var LoginPopup = function() {

    var showLoginPopup = function() {
        $(".login-popup").show().addClass("active");
        $("a.login").addClass("active");
    };

    var hideLoginPopup = function() {
        $(".login-popup").hide().removeClass("active");
        $("a.login").removeClass("active");
    };

    var toggleLoginPopup = function() {
        if ($(".login-popup").hasClass("active")) {
            hideLoginPopup();
        } else {
            showLoginPopup();
        }
    };

    var handleLoginPopup = function() {

        $("a.login").click(function(e) {
            e.stopPropagation();
            e.preventDefault();
            toggleLoginPopup();
        });
        $("a.login-remote").click(function(e) {
            e.stopPropagation();
            toggleLoginPopup();
        });
        // Prevent events from getting pass .popup
        $(".login-popup").click(function(e) {
            e.stopPropagation();
        });
        // If an event gets to the body
        $("body").click(function() {
            hideLoginPopup();
        });
        
        if (window.location.hash == '#login') {
            showLoginPopup();
            $(function(){
                console.log('top');
                window.scrollTo(0,0);
            });
        }

    };

    return {
        //main function to initiate the module
        init: function() {
            handleLoginPopup();
        }

    };

}();