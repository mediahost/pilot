function initMaxlength() {
    $("textarea[maxlength]").livequery(function() {
        $(this).maxlength({
            max: $(this).attr("maxlength")
        });
    });
}

function initTooltips() {
    $('.cke *[title]').livequery(function() {
        $(this).powerTip({placement: 's'});
    });
    $('*[title]').livequery(function() {
        var options = {};
        if ($(this).hasClass("tip-n")) {
            options.placement = 'n';
        } else if ($(this).hasClass("tip-nw")) {
            options.placement = 'nw';
        } else if ($(this).hasClass("tip-w")) {
            options.placement = 'w';
        } else if ($(this).hasClass("tip-sw")) {
            options.placement = 'sw';
        } else if ($(this).hasClass("tip-s")) {
            options.placement = 's';
        } else if ($(this).hasClass("tip-se")) {
            options.placement = 'se';
        } else if ($(this).hasClass("tip-e")) {
            options.placement = 'e';
        } else if ($(this).hasClass("tip-ne")) {
            options.placement = 'ne';
        } else {
            options.followMouse = true;
        }
        $(this).powerTip(options);
    });
}

function initColorBox() {
    // ajax content
    $(".innerPage").colorbox();

    $(".iframeCollorBox").colorbox({
        iframe: true,
        width: 745,
        height: 670
    });
    
    $(".imagesGroup").colorbox({rel:'imagesGroup'});
}


/**
 * Inicializování flash zpráviček
 */
function hiddingFlashMessages() {
    initFlashHiding();
    initFlashClose();
}

var flashShowTime = 8000;
var flashHidingTime = 1500;

function initFlashHiding() {
    $("div.flash").livequery(function() {
        var el = $(this);
        setTimeout(function() {
            el.animate({
                "opacity": 0
            }, flashHidingTime);
            el.slideUp();
        }, flashShowTime);
    });
}
function initFlashClose() {
    $("div.flash .close").live('click', function(e) {
        e.preventDefault();
        var el = $(this).parent();
        el.animate({
            "opacity": 0
        }, flashHidingTime);
        el.slideUp();
        return false;
    });
}

/**
 * Inicializace ajaxových okazů
 */
function initAjaxLinks() {

    // ajaxové odkazy
    $("a.ajax").livequery("click", function(event) {
        event.preventDefault();

        // ROLL PAGE EFFECT
        var snippet = ".snippetRoll";
        $(snippet).parent().css({
            'transform': 'rotateX(180deg)'
        })
                .transition({
                    perspective: '500px',
                    rotateX: '0deg'
                }, 500);
        $(snippet).transition({
            opacity: 0.01
        });

        // SPINNER
        snippet = ".snippetSpinner";
        var spinnerClass = "ajaxSpinner";
        $(snippet).transition({
            opacity: 0.01
        });
        if ($(snippet).length) {
            $('<div class="' + spinnerClass + '"></div>').appendTo($(snippet).parent())
                    .css({
                        left: $("." + spinnerClass).parent().position().left + 1,
                        width: $("." + spinnerClass).parent().width(),
                        top: $("." + spinnerClass).parent().position().top + 1,
                        height: $("." + spinnerClass).parent().height(),
                        opacity: 0
                    }).transition({
                opacity: 1
            });
        }

        $.get(this.href);
    });

    // ajaxové bez efektů odkazy
    $("a.ajax-silent").livequery("click", function(event) {
        event.preventDefault();
        $.get(this.href);
    });

}

function initAjaxForms() {

    $("form.ajax").livequery('change submit', function(e) {
        if (!$(e.target).hasClass("novalidate")) {
            e.preventDefault();
            $(this).ajaxSubmit();
        }
    });
    $("form.ajax :submit").livequery('click', function(e) {
        e.preventDefault();
        $(this).ajaxSubmit();
    });

}

function initDatePickers() {

    $("input.date[type=date]").livequery(function() {
        var el = $(this);
        var value = el.val();
        var dateFormat = $.datepicker.W3C;
        var date = (value ? $.datepicker.parseDate(dateFormat, value) : null);

        var minDate = el.attr("min") || null;
        if (minDate)
            minDate = $.datepicker.parseDate(dateFormat, minDate);
        var maxDate = el.attr("max") || null;
        if (maxDate)
            maxDate = $.datepicker.parseDate(dateFormat, maxDate);

        if (el.attr("type") == "date") {
            var tmp = $("<input/>");
            $.each("class,disabled,id,maxlength,name,readonly,required,size,style,tabindex,title,value,data-nette-rules".split(","), function(i, attr) {
                tmp.attr(attr, el.attr(attr));
            });
            tmp.data(el.data());
			tmp.attr("type", "text");
            el.replaceWith(tmp);
            el = tmp;
        }
        el.datepicker({
            minDate: minDate,
            maxDate: maxDate,
            dateFormat: "dd.mm.yy",
            showMonthAfterYear: true
        });        
        el.val($.datepicker.formatDate(el.datepicker("option", "dateFormat"), date));
		Nette.initForm(el.closest('form')[0]);
    });

    $("input.date.birthDate").livequery(function() {
        $(this).datepicker("option", {
            changeMonth: true,
            changeYear: true,
            yearRange: "c-100:c"
        });
    });

}

function initRangeSlider(type) {

    var min, max, step;
	var $rangeSlider = $("#rangeSlider");
    switch (type) {
        case "jobFinder":
            min = 0;
            step = 10;
            max = 10000;
            break;
        default:
            min = 3000;
            max = 200000;
            step = 1000;
            break;
    }
	if ($rangeSlider.data('max')) {
		max = $rangeSlider.data('max');
	}

    $(".range").livequery(function() {
        $(this).attr('readonly', true);
    });
    $rangeSlider.livequery(function() {
        $(this).slider({
            range: true,
            min: min,
            max: max,
            step: step,
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

function initSliders() {

    $("select.slider").livequery(function() {
        if (!$(this).parent().find(".sliderVal").length) {
            $(this).parent().prepend("<div class='sliderVal'></div>");
            var options = {};
            if ($(this).hasClass("live")) {
                options.sliderOptions = {
                    stop: function(event, ui) {
                        $(event.target).change();
                    }
                };
            }
            $.when($(this).selectToUISlider(options))
                    .done(function() {
                        var slider = $(this).next();
                        slider.find(".ui-slider-scale").hide();
                        slider.find(".screenReaderContext").hide();
                        slider.find(".ui-slider-tooltip").hide();
                        $(this).hide();
                    });
        }
    });
}

function initRadioFeelings() {
    $(".radioFeelings").livequery(function() {
        var radio = $(this);
        var label = $(this).next();
        var title = label.text();
        radio.hide();
        label.html('<div class="radioFeelingsLabel ' + radio.attr("id") + '" title="' + title + '"></div>');
    });
    $(".radioFeelings").livequery("change", function() {
        var active = "active";
        $(this).parent().find("label").removeClass(active)
        var label = $(this).next();
        label.addClass(active);
    });
}

function initAdminSubmenu() {
    $("#menu nav ul > li").livequery(function() {
//        $(this).parent().find(".submenu").hide();
//        $(this).parent().children(".active").find(".submenu").show();

        $(this).hover(function() {
            $(this).parent().find(".submenu").hide();
            $(this).find(".submenu").show();
        }, function() {
            $(this).parent().find(".submenu").hide();
            $(this).parent().children(".active").find(".submenu").show();
        });
    });
}

var timeoutId;

function initAdminEditor() {
    $("#editor:not(.show)").hide();
//    $("#editor").show();
    $(".showEditor").livequery("click", function(event) {
        event.preventDefault();
        $("#editor").show(200);
    });
    $("#editor .close a").livequery("click", function(event) {
        event.preventDefault();
        $("#editor").hide(200);
    });

    // http://editor.texy.info/

    $('textarea.ckeditor').livequery(function() {
        CKEDITOR.replace($(this).attr("id"));
    });

}

function initMyTimeline(type, attrId) {
    google.load("visualization", "1");
    // Set callback to run when API is loaded
    google.setOnLoadCallback(function() {
        $(function() {
            $("#mytimeline").livequery(function() {
                drawVisualization(this, type, attrId);
            });
        });
    });
}

function initGroupTimeline(elClass, type) {
    google.load("visualization", "1");
    // Set callback to run when API is loaded
    google.setOnLoadCallback(function() {
        $(function() {
            $("." + elClass).livequery(function() {
                drawVisualization(this, type, $(this).attr("data-timeline-id"));
            });
        });
    });
}

// Called when the Visualization API is loaded.
function drawVisualization(el, type, attrId) {
    // Create and populate a data table.
    var data = new google.visualization.DataTable();
    data.addColumn('datetime', 'start');
    data.addColumn('datetime', 'end');
    data.addColumn('string', 'content');
    data.addColumn('string', 'className');


    $.get(basePath + '/ajax/timeline-data', {type: type, attrId: attrId}, function(rows) {
        var ids = new Array();
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].length === 5) {
                ids[i] = rows[i].pop();
            }
            for (var j = 0; j < rows[i].length; j++) {
                if (rows[i][j] !== null && (j === 0 || j === 1)) {
                    rows[i][j] = new Date(rows[i][j]);
                }
            }
        }
        data.addRows(rows);


        var length = rows.length,
                element = null,
                start = null,
                end = null,
                min = null,
                max = null;
        for (var i = 0; i < length; i++) {
            element = rows[i];
            start = element[0];
            end = element[1];

            if (min === null || start < min) {
                min = start;
            }
            if (end === undefined) {
                end = start;
            }
            if (max === null || end > max) {
                max = end;
            }
        }
        if (min !== null)
            min = new Date(min.getFullYear() - 1, min.getMonth(), min.getDate(), min.getHours(), min.getMinutes(), min.getSeconds());
        if (max !== null)
            max = new Date(max.getFullYear() + 1, max.getMonth(), max.getDate(), max.getHours(), max.getMinutes(), max.getSeconds());

        var now = new Date();
        var term = 1000 * 60 * 60 * 24 * 365;
        if (min === null && max === null) {
            max = now.valueOf() + term;
            min = now.valueOf() - term;
        }
        if (max < now) {
            max = now.valueOf() + term;
        }

        // specify options
        var options = {
            locale: lang,
            width: "100%",
            showNavigation: true,
            cluster: true,
            style: "box",
            min: min,
            max: max,
            zoomMin: 1000 * 60 * 60 * 24 * 1, // one day in milliseconds
            zoomMax: null
                    //        zoomMax: 1000 * 60 * 60 * 24 * 31 * 12 * 10  // 10 years in milliseconds
        };

        // Instantiate our timeline object.
        timeline = new links.Timeline(el);

        // Draw our timeline with the created data and options
        timeline.draw(data, options);

        links.events.addListener(timeline, 'select', onselect);

        function onselect() {
            var sel = timeline.getSelection();
            if (sel.length) {
                if (sel[0].row != undefined) {
                    var row = sel[0].row;
                    $("#editWork-" + ids[row]).click();
                }
            }
        }
    }).done(function() {
        $(el).removeClass("loading");
    });


}

function initLightEditor() {
    $('.posts .new textarea.ckeditor, .topic.new textarea.ckeditor').livequery(function() {
        CKEDITOR.replace($(this).attr("id"), {
            removePlugins: 'colorbutton,find,flash,font,' +
                    'forms,iframe,image,newpage,removeformat,' +
                    'smiley,specialchar,stylescombo,templates',
            toolbarGroups: [
                {name: 'editing', groups: ['basicstyles', 'links']},
                {name: 'undo'},
                {name: 'clipboard', groups: ['selection', 'clipboard']}
            ],
            bodyClass: 'ck-text',
            width: '848px',
            height: '250px'
        });
    });
}

function initChosen(type) {
    var options = {};
    switch (type) {
        default:
            options.disable_search_threshold = 0;
            options.allow_single_deselect = true;
            break;
    }
    $(".chosen-select").chosen(options);
}

var TM = {};
TM.name = "tagged-multiselect";
TM.childName = TM.name + "-child";
TM.tagClassName = TM.name + "-tag";
TM.tagCloseClassName = TM.name + "-close";

function initTaggedMultiselect() {
    var name = TM.name;
    var childName = TM.childName;

    $("." + name).each(function() {
        var width = $(this).css("width").substr(0, $(this).css("width").length - 2);
        var x = 10; // x is border + padding
        var newDiv = $("<div>").addClass(childName).css("width", (width - x) + "px");
        $(this).after(newDiv);

        TMsetTaggedValues($(this));
    });
    $("." + name).livequery("change", function() {
        TMsetTaggedValues($(this));
    });

    var linkClass = "." + TM.childName + " ." + TM.tagClassName + " ." + TM.tagCloseClassName;
    $(linkClass).livequery("click", function(e) {
        e.preventDefault();
        TMremoveTag($(this));
    });

    $("." + TM.childName).livequery("click", function() {
        $(this).prev().focus();
    });
}

function TMsetTaggedValues(el) {
    var childName = TM.childName;

    var selected = new Array();
    var vals = el.val();
    if (Object.prototype.toString.call(vals) !== '[object Array]') {
        vals = new Array(vals);
    }
    el.find("option").each(function() {
        var val = $(this).val();
        if (jQuery.inArray(val, vals) > -1) {
            selected.push(new Array(val, $(this).html()));
        }
    });

    var child = el.next("." + childName);
    TMinsertSelectedTags(child, selected);
}

function TMinsertSelectedTags(el, values) {
    el.html("");
    var tag, text, link;
    $.each(values, function(index, value) {
        text = $("<span>").text(value[1]);
        link = $("<a>")
                .attr("href", "#")
                .attr("data-id", value[0])
                .addClass(TM.tagCloseClassName);
        tag = $("<div>").append(text)
                .append(link)
                .addClass(TM.tagClassName);
        el.append(tag);
    });
}

function TMremoveTag(el) {
    var link = el.parent();
    var select = el.parent().parent().prev("." + TM.name);
    var vals = select.val();
    var find = vals.indexOf(el.attr("data-id"));

    link.remove();
    if (Object.prototype.toString.call(vals) === '[object Array]') {
        vals.splice(find, 1);
        select.val(vals);
    } else {
        select.val(new Array());
    }
    select.change();
}

var MP = {};
MP.name = "multiselect-plus";
MP.replaceName = TM.name + "-replace";
MP.itemName = TM.name + "-item";

function initMultiselectPlus() {
    var name = MP.name;
    var replaceName = MP.replaceName;

    $("." + name).each(function() {
        $(this).hide();
        var width = $(this).css("width").substr(0, $(this).css("width").length - 2);
        var height = $(this).css("height").substr(0, $(this).css("height").length - 2);
        var padding = $(this).css("padding");
        var size = $(this).css("font-size");
        var color = $(this).css("color");
        var bgcolor = $(this).css("background-color");
        var border = $(this).css("border");
        var x = 12; // x is border + padding
        var y = 12; // y is border + padding

        var newDiv = $("<div>").addClass(replaceName)
                .css("width", (width - x) + "px")
                .css("height", (height - y) + "px")
                .css("padding", padding)
                .css("color", color)
                .css("font-size", size)
                .css("background-color", bgcolor)
                .css("border", border);

        MPappendElements($(this).children(), newDiv);

        $(this).before(newDiv);
        MPselectValues($(this));
    });

    $("." + name).livequery("change", function() {
        MPselectValues($(this));
    });

    $("." + MP.itemName).livequery("click", function() {
        var value = $(this).attr("data-value");
        var parent = null;
        if ($(this).parent().parent("." + replaceName).length) {
            parent = $(this).parent().parent("." + replaceName);
        } else if ($(this).parent().parent().parent().parent("." + replaceName).length) {
            parent = $(this).parent().parent().parent().parent("." + replaceName);
        }
        if (parent !== null) {
            var select = parent.next("." + MP.name);
            if (select.length) {
                var values = select.val();
                if (Object.prototype.toString.call(values) === '[object Array]') {
                    var find = values.indexOf(value);
                    if (find >= 0) {
                        values.splice(find, 1);
                    } else {
                        values.push(value);
                    }
                } else {
                    values = new Array(value);
                }
                select.val(values);
                select.change();
            }
        }
    });

}

function MPappendElements(elements, parent) {

    var list = $("<ul>");
    var item, label;

    elements.each(function() {
        if ($(this).prop("tagName") === 'OPTION') {
            item = $("<li>").text($(this).text())
                    .attr("data-value", $(this).attr("value"))
                    .addClass(MP.itemName);
        } else if ($(this).prop("tagName") === 'OPTGROUP') {
            label = $("<label>").text($(this).attr("label"));
            item = $("<li>").append(label);
            MPappendElements($(this).children(), item);
        }
        list.append(item);
    });

    parent.append(list);
}

function MPselectValues(el) {
    var div = el.parent().find("." + MP.replaceName);
    var find;
    div.find("." + MP.itemName).removeClass("selected");
    if (Object.prototype.toString.call(el.val()) === '[object Array]') {
        $.each(el.val(), function(i, value) {
            find = div.find("." + MP.itemName + "[data-value=" + value + "]");
            find.addClass("selected");
        });
    }
}

function initSlideToggle(controller, controlled, timing, effect) {
    $(controller).livequery("click", function(e) {
        e.preventDefault();
        switch (effect) {
            case undefined:
                effect = "easeInOutCirc";
                break;
        }
        switch (timing) {
            case undefined:
                timing = 500;
                break;
        }
        $(controlled).slideToggle(timing, effect);
    });
}


