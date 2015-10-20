/**
 * Bind all actions
 */
$(document).ready(function () {

	var photo = $('.requiredInfo #existingPhoto');
	if (photo.length) {
		var newRow = $('<tr></tr>').append($('<th></th>')).append($('<td></td>').append(photo));
		$('.requiredInfo #frmstep1Form-photo').parent().parent().parent().prepend(newRow);
	}

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

	// range and language sliders
	initSliders();

	// set ajax links
	initAjaxLinks();

	// set ajax forms
	initAjaxForms();

	// init radio to feeling icons
	initRadioFeelings();

	initGroups();

	initChatAjaxSetting();

	initScroolToBottomOfChat();

	initApplyButton();

	initStatusByCompanySelectToggle();

});

function initGroups() {
	$("#front-module-body #dashboard .skills .group h3").livequery("click", function () {
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

function initChatAjaxSetting() {
	$('.chat-settings').on('change', function () {
		var $form = $(this).closest('form');
		var $posting = $.post(
				$form.attr('action'),
				{
					notification: $(this).val()
				}
		);
	});
}

function initScroolToBottomOfChat() {
	$this = $('.chat .items');
	$this.scrollTop($this.prop('scrollHeight'));
}

function initApplyButton() {
	$('#job .apply').on('click', function () {
		var text = $(this).data('text-after-click');
		var $div = $('<div>').addClass('applyed').text(text);
		$(this).replaceWith($div);
	});
}

function initStatusByCompanySelectToggle() {
	$('select.statusByCompanySelect').on('change', function(){
		$text = $(this).closest('form').find('input[name=status_by_company_text]');
		if ($(this).val() == '') {
			$text.show();
		} else {
			$text.hide();
		}
	}).trigger('change');
}