$(document).ready(function() {

    initGroupsCandidates();

    initShowCv();

    initPreviews(".candidates .candidate .preview");

    initPlayIcon();

});

function initPlayIcon() {
    $('#dashboard .candidates').livequery(function(){
        wmark.init({
            position: "center",
            opacity: 99,
            className: "video-watermark",
            path: "/images/play-icon.png"
        });
    });
}

function initGroupsCandidates() {
    $("#company-module-body #dashboard .filter .group h3").livequery("click", function() {
        var parent = $(this).parent();
        var items = $(this).next(".items");
        if (parent.hasClass("active")) { // SHOW -> HIDE
            parent.removeClass("active");
            items.animate({
                display: "none",
                opacity: 0,
                height: "toggle"
            }, 1000);
        } else { // HIDE -> SHOW
            parent.addClass("active");
            items.animate({
                display: "block",
                opacity: 1,
                height: "toggle"
            }, 1000);
        }
    });
}

function initShowCv() {
    $(".candidate .data .links .switch").livequery("click", function(event) {
        event.preventDefault();
        if ($(this).hasClass("show")) { // HIDE
            $(this).removeClass("show");
            $(this).addClass("hide");
            $(this).text($(this).attr("data-text-hide"));
            if ($(this).attr("data-linked-cv")) {
                $(".cv-" + $(this).attr("data-linked-cv")).slideUp();
            } else if ($(this).attr("data-linked-chat")) {
                $(".chat-" + $(this).attr("data-linked-chat")).slideUp();
            } else if ($(this).attr("data-linked-doc")) {
                $(".doc-" + $(this).attr("data-linked-doc")).slideUp();
            }
        } else if ($(this).hasClass("hide")) { // SHOW
            $(this).removeClass("hide");
            $(this).addClass("show");
            $(this).text($(this).attr("data-text-show"));
            if ($(this).attr("data-linked-cv")) {
                $(".cv-" + $(this).attr("data-linked-cv")).slideDown();
            } else if($(this).attr("data-linked-doc")) {   
                $(".doc-" + $(this).attr("data-linked-doc")).slideDown();
            } else if($(this).attr("data-linked-chat")) {   
                $(".chat-" + $(this).attr("data-linked-chat")).slideDown();
            }
        }
    });
}

function initPreviews(prevClass) {

    $.when(
        $(prevClass).livequery(function() {

            var previewEl = $(this);
            var parentEl = previewEl.closest(".more");
            var previewPrev = parentEl.find(".previewControls .prev");
            var previewNext = parentEl.find(".previewControls .next");
            var previewPaginator = parentEl.find(".previewControls .paginator");
            var previewNavigator = null;
            var previewAttr = {
                page: "preview-page",
                pages: "preview-pages",
                step: "preview-step"
            };

            initPreviewBox(previewEl, previewPrev, previewNext, previewPaginator, previewNavigator, previewAttr);

            previewPrev.click(function(event) {
                event.preventDefault();
                if (!previewPrev.hasClass("inactive")) {
                    previewPage(previewEl, previewPrev, previewNext, previewPaginator, previewAttr, "prev");
                }
            });

            previewNext.click(function(event) {
                event.preventDefault();
                if (!previewNext.hasClass("inactive")) {
                    previewPage(previewEl, previewPrev, previewNext, previewPaginator, previewAttr, "next");
                }
            });
        }
    )).then(function() {
        $(prevClass).livequery(function() {
            var previewEl = $(prevClass);
            var parentEl = previewEl.closest(".more").parent().find('.more');
            parentEl.hide();
        });
    });
}
