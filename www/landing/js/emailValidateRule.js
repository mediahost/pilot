Nette.validators.serverMail = function (elem, arg, val, rule) {
	console.log(val);
	var url = basePath + '/ajax/validate-mail?mail=' + encodeURIComponent(val);
	$.ajax({
		url: url,
		dataType: "json",
		success: function (result) {
			$.data(elem, 'valid', result.valid);
			$.data(elem, 'validMsg', result.msg);
			$.data(elem, 'validFor', val);
			if (!result.valid) {
				LiveForm.addError(elem, result.msg);
			} else {
				LiveForm.removeError(elem);
			}
		}
	});
	var valid = $.data(elem, 'valid');
	if (typeof valid !== "undefined" && $.data(elem, 'validFor') === val) {
		return valid;
	}
	return true;
};