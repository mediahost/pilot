/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * @copyright   Copyright (c) 2009 Jan Marek
 * @license     MIT
 * @link        http://nettephp.com/cs/extras/jquery-ajax
 * @version     0.2
 */

jQuery.extend({
    nette: {
        updateSnippet: function (id, html) {
            var el = $("#" + id);
            var spinner = el.parent().find(".ajaxSpinner");
            
            // SPINNER
            if (el.hasClass("snippetSpinner")) {
                
                el.fadeTo("fast", 0.01, function () {
                    spinner.transition({
                        opacity: 0
                    })
                    el.html(html).fadeTo("fast", 1, function() {
                        spinner.remove();
                    });
                });
            } 
            // ROLL-PAGE EFFECT
            else if (el.hasClass("snippetRoll")) {
                el.html(html).fadeTo(500, 1);
            }
            // FADE OUT
            else if (el.hasClass("snippetFadeOut")) {
                el.fadeTo(250, 0.01, function () {
                    el.html(html).fadeTo(250, 1);
                });
            } 
            // OTHER
            else {
                el.html(html);
            }
        },

        success: function (payload) {
            // redirect
            if (payload.redirect) {
                window.location.href = payload.redirect;
                return;
            }

            // snippets
            if (payload.snippets) {
                for (var i in payload.snippets) {
                    jQuery.nette.updateSnippet(i, payload.snippets[i]);
                }
            }
			
			App.afterAjax();
        }
    }
});

jQuery.ajaxSetup({
    success: jQuery.nette.success,
    dataType: "json"
});