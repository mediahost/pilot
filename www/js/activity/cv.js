$(document).ready(function() {

    // přenastavení aktivity pro ajaxový seznam odkazů
    $("ul.reactivate li a").livequery("click", function() {
        var active = "active";
        $(this).parent().parent().children().removeClass(active);
        $(this).parent().addClass(active);
    });

    initAjaxTabs();

    initRangeSliders();

    var previewSettings = {
        name: "#cv .preview",
        next: "#cv .tips .subscript .next",
        prev: "#cv .tips .subscript .prev",
        paginator: "#cv .tips .subscript .paginator span",
        navigator: "#cv .navigator ul li a",
        attr: {
            page: "preview-page",
            pages: "preview-pages",
            step: "preview-step"
        }
    };
    initPreview(previewSettings);

    initGroupsCv();

});

function initAjaxTabs() {

    $("#cv .tabs .tab .show .controls a").livequery("click", function(event) {
        event.preventDefault();
        var root = $(this).parent().parent().parent();
        root.find(".show").hide();
        root.find(".inputs").show();
        root.find(".inputs input").focus();
    });

    $("#cv .tabs .tab .inputs input").livequery(function() {
        $(this).blur(function() {
            var root = $(this).parent().parent().parent().parent().parent().parent().parent();
            root.find(".show").show();
            root.find(".inputs").hide();
        });
    });

}


function initRangeSliders() {

    $(".range").livequery(function() {
        $(this).attr('readonly', true);
    });

    if (!$("#rangeSlider").length) {
        var rangeTo = $(".range.to");//;
        rangeTo.livequery(function() {
            $(this).parent().parent().parent().append("<tr><th></th><td><div id='rangeSlider'></div></td></tr>");
        });
    }

    $("#rangeSlider").livequery(function() {
        $(this).slider({
            range: true,
            min: 3000,
            max: 200000,
            step: 1000,
            values: [$(".range.from").val(), $(".range.to").val()],
            slide: function(event, ui) {
                $(".range.from").val(ui.values[0]);
                $(".range.to").val(ui.values[1]);
            },
            stop: function() {
                $(".range.from").change();
                //    $(".range.to").change();
            }
        });
    });
    $(".range.from").livequery(function() {
        $(this).val($("#rangeSlider").slider("values", 0));
    });
    $(".range.to").livequery(function() {
        $(this).val($("#rangeSlider").slider("values", 1));
    });

}


function initGroupsCv() {
    $("#cv .box .contain .group h3").livequery("click", function() {
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


function initPreview(preview) {

    $(preview.name).livequery(function() {
        var previewEl = $(preview.name);
        var previewPrev = $(preview.prev);
        var previewNext = $(preview.next);
        var previewPaginator = $(preview.paginator);
        var previewNavigator = $(preview.navigator);
        var previewAttr = preview.attr;

        initPreviewBox(previewEl, previewPrev, previewNext, previewPaginator, previewNavigator, previewAttr);
    });

    $(preview.prev).livequery("click", function(event) {
        event.preventDefault();
        if (!$(this).hasClass("inactive")) {
            previewPage($(preview.name), $(preview.prev), $(preview.next), $(preview.paginator), preview.attr, "prev");
        }
    });

    $(preview.next).livequery("click", function(event) {
        event.preventDefault();
        if (!$(this).hasClass("inactive")) {
            previewPage($(preview.name), $(preview.prev), $(preview.next), $(preview.paginator), preview.attr, "next");
        }
    });

}
