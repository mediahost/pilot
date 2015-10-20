function initPreviewBox(previewEl, previewPrev, previewNext, previewPaginator, previewNavigator, previewAttr) {

    var pageHeight = parseInt(previewEl.height());
    var realHeight = parseInt(previewEl.find("#cvcontent").height());
    var pages = Math.ceil(realHeight / pageHeight);

    previewEl.attr(previewAttr.page, 1);
    previewEl.attr(previewAttr.pages, pages);
    previewEl.find("#cvcontent").height(pages * pageHeight);
    previewExec(previewEl, previewPrev, previewNext, previewPaginator, previewAttr, false, false);

    if (previewNavigator != null) {
        previewNavigator.livequery("click", function() {
            var stepNum = previewNavigator.attr("href").match(/step=(\d+)/);
            previewEl.attr(previewAttr.step, parseInt(stepNum[1]));
            previewExec(previewEl, previewPrev, previewNext, previewPaginator, previewAttr, false, false);
        });
    }

}

function previewPage(preview, previewPrev, previewNext, previewPaginator, previewAttr, direct) {
    var page = parseInt(preview.attr(previewAttr.page));
    var pages = parseInt(preview.attr(previewAttr.pages));

    switch (direct) {
        case "prev":
            if (page <= 1)
                page = 1;
            else
                page -= 1;
            break;
        case "next":
        default:
            if (page >= pages)
                page = pages;
            else
                page += 1;
            break;
    }

    preview.attr(previewAttr.page, page);
    previewExec(preview, previewPrev, previewNext, previewPaginator, previewAttr, true, true);
}

function previewExec(main, previewPrev, previewNext, previewPaginator, previewAttr, animate, settedPage) {
    var page = main.attr(previewAttr.page);
    var pages = main.attr(previewAttr.pages);
    var pageHeight = parseInt(main.height());
    var step = main.attr(previewAttr.step);
    if (!settedPage) {
        var anchor = main.find(".step" + parseInt(step));
        if (anchor.length === 1) {
            var anchorPos = parseInt(anchor.position().top) - parseInt(main.position().top);
            page = Math.ceil(anchorPos / pageHeight);
            page = page <= 1 ? 1 : page;
        }
    }

    var move = (page - 1) * pageHeight;

    var header = main.find("div.header");
    var headerMove = 0;
    if (header.length) {
        headerMove = header.height() + parseInt(header.css("padding-top")) + parseInt(header.css("padding-bottom"));
    }

    var footer = main.find("div.footer");
    var footerMove = 0;
    var footerMove2 = 0;
    if (footer.length) {
        footerMove = footer.height() + parseInt(footer.css("padding-top"));
        footerMove2 = footerMove + parseInt(footer.css("padding-bottom"));
    }

    move = move - headerMove - footerMove;

    if (header.length) {
        header.css("top", (move < 0 ? 0 : move));
    }
    if (footer.length) {
        footer.css("top", (move < 0 ? 0 : move) + pageHeight - footerMove2);
    }

    if (animate) {
        main.parent().parent().css({
            'transform': 'rotateY(180deg)'
        })
                .transition({
                    perspective: '500px',
                    rotateY: '0deg'
                }, 500);
    }

    main.scrollTop(move);

    previewPaginator.text(page + " / " + pages);

    if (page <= 1) {
        previewPrev.addClass("inactive");
    } else {
        previewPrev.removeClass("inactive");
    }

    if (page >= pages) {
        previewNext.addClass("inactive");
    } else {
        previewNext.removeClass("inactive");
    }
}