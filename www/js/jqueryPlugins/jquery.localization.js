$(document).ready(function() {

    datepickerLocalization();

    maxlengthLocalization();

});

function maxlengthLocalization() {

    $.maxlength.regional['en'] = {
        feedbackText: '{r} characters remaining ({m} maximum)',
        overflowText: '{o} characters too many ({m} maximum)'
    };

    $.maxlength.regional['cs'] = {
        feedbackText: 'Zbývá {r} znaků (maximálně {m})',
        overflowText: '{o} znaků je příliš (maximálně {m})'
    };

    $.maxlength.regional['sk'] = {
        feedbackText: 'Zostavá {r} znakov (maximálne {m})',
        overflowText: '{o} znakov je príliš (maximálne {m})'
    };

    $.maxlength.setDefaults($.maxlength.regional[ "" ]);
    if ($.maxlength.regional[lang] !== null)
        $.maxlength.setDefaults($.maxlength.regional[lang]);

}

function datepickerLocalization() {

    $.datepicker.regional['en'] = {
        monthNames: ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'],
        monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
        dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        dayNamesMin: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
        dateFormat: 'dd/mm/yyyy',
        firstDay: 1,
        prevText: 'Prev', prevStatus: '',
        prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: '',
        nextText: 'Next', nextStatus: '',
        nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: '',
        currentText: 'Current', currentStatus: '',
        todayText: 'Today', todayStatus: '',
        clearText: 'Clear', clearStatus: '',
        closeText: 'Done', closeStatus: '',
        yearStatus: '', monthStatus: '',
        weekText: 'Wk', weekStatus: '',
        dayStatus: 'DD d MM',
        defaultStatus: '',
        isRTL: false
    };

    $.datepicker.regional['cs'] = {
        monthNames: ['leden', 'únor', 'březen', 'duben', 'květen', 'červen',
            'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec'],
        monthNamesShort: ['led', 'úno', 'bře', 'dub', 'kvě', 'čer',
            'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'],
        dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
        dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
        dayNamesMin: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
        dateFormat: 'dd.mm.yyyy',
        firstDay: 1,
        prevText: 'Dříve', prevStatus: '',
        prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: '',
        nextText: 'Později', nextStatus: '',
        nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: '',
        currentText: 'Nyní', currentStatus: '',
        todayText: 'Nyní', todayStatus: '',
        clearText: '-', clearStatus: '',
        closeText: 'Zavřít', closeStatus: '',
        yearStatus: '', monthStatus: '',
        weekText: 'Týd', weekStatus: '',
        dayStatus: 'DD d MM',
        defaultStatus: '',
        isRTL: false
    };

    $.datepicker.regional['sk'] = {
        monthNames: ['Január', 'Február', 'Marec', 'Apríl', 'Máj', 'Jún',
            'Júl', 'August', 'September', 'Október', 'November', 'December'],
        monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Máj', 'Jún',
            'Júl', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
        dayNames: ['Nedel\'a', 'Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota'],
        dayNamesShort: ['Ned', 'Pon', 'Uto', 'Str', 'Štv', 'Pia', 'Sob'],
        dayNamesMin: ['Ne', 'Po', 'Ut', 'St', 'Št', 'Pia', 'So'],
        dateFormat: 'dd.mm.yyyy',
        firstDay: 0,
        prevText: 'Predchádzajúci', prevStatus: '',
        prevJumpText: '&#x3c;&#x3c;', prevJumpStatus: '',
        nextText: 'Nasledujúci', nextStatus: '',
        nextJumpText: '&#x3e;&#x3e;', nextJumpStatus: '',
        currentText: 'Dnes', currentStatus: '',
        todayText: 'Dnes', todayStatus: '',
        clearText: '-', clearStatus: '',
        closeText: 'Zavrieť', closeStatus: '',
        yearStatus: '', monthStatus: '',
        weekText: 'Ty', weekStatus: '',
        dayStatus: 'DD d MM',
        defaultStatus: '',
        isRTL: false
    };

    $.datepicker.setDefaults($.datepicker.regional[ "" ]);
    if ($.datepicker.regional[lang] !== null)
        $.datepicker.setDefaults($.datepicker.regional[lang]);
}

