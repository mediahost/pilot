$(document).ready(function() {
    
    // init slides on HP
    initSlides();
    
    $(".signIn .comebackConn a.comeback").livequery("click", function (e) {
        e.preventDefault();
        $('.signIn .comebackConn .signInForm').slideToggle();
    });
    
});


/**
 * Inicializace slidů na HP
 * http://slidesjs.com/
 */
function initSlides() {
    
    $('.slides').slidesjs({
        width: 644,
        height: 366,
        navigation: false,
        play: {
            interval: 4500,
            auto: true,
            pauseOnHover: true,
            restartDelay: 5000
        }
    });

}