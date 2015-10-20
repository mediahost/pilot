/**
 * Bind all actions
 */
$(document).ready(function() {
    
    // for date pickers
    initDatePickers();
    
    // hiding flash messages
    hiddingFlashMessages();
    
    // maxlength fot textarea
    initMaxlength();
    
    // tooltips
    initTooltips();
    
    // init colorbox
    initColorBox();
    
    // set ajax links
    initAjaxLinks();
    
    // set ajax forms
    initAjaxForms();
    
    // init showing/hiding submenu
    initAdminSubmenu();
    
    // init showing editor
    initAdminEditor();
    
});