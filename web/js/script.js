$(function(){
	$('.camoo-form-spining').on('submit', function (e) {
		showSpinner();
		// DISABLE SUBMIT BUTTON
		var oButtonSubmit = $(this).find('button[type=submit]');
		if (oButtonSubmit.length > 0){
			oButtonSubmit.prop('disabled', true);
		}
	});

	$(document).on('submit', '.newsletter_form, #newsletter', function (e) {
		e.preventDefault();
		var $form = $(this);
		var $submitBtn = $form.find('button[type="submit"]');
		var $emailInput = $form.find('input[name="email"]');
		var $feedback = $form.parent().find('.newsletter_feedback');

		if ($feedback.length === 0) {
			$feedback = $('<div class="newsletter_feedback" id="newsletter-feedback" role="alert" aria-live="polite"></div>');
			$form.after($feedback);
		}

		var email = $.trim($emailInput.val());
		if (!email) {
			return;
		}

		var originalBtnText = $submitBtn.html();
		$submitBtn.prop('disabled', true);
		$feedback.removeClass('is-success is-error').hide().empty();

		$.ajax({
			url: $form.attr('action') || '/newsletter/subscribe',
			type: 'POST',
			data: $form.serialize(),
			dataType: 'json',
			headers: {
				'Accept': 'application/json',
				'X-Requested-With': 'XMLHttpRequest'
			}
		}).done(function (response) {
			if (response && (response.status === true || response.success === true)) {
				$feedback.addClass('is-success')
					.text(response.message || 'Merci ! Votre adresse e-mail est maintenant inscrite à notre newsletter.')
					.slideDown(200);
				$emailInput.val('');
			} else {
				$feedback.addClass('is-error')
					.text((response && response.message) || 'Une erreur est survenue. Veuillez réessayer.')
					.slideDown(200);
			}
		}).fail(function (xhr) {
			var errorMsg = 'Votre inscription à la newsletter n’a pas pu être enregistrée. Veuillez réessayer plus tard.';
			if (xhr.responseJSON && xhr.responseJSON.message) {
				errorMsg = xhr.responseJSON.message;
			}
			$feedback.addClass('is-error').text(errorMsg).slideDown(200);
		}).always(function () {
			$submitBtn.prop('disabled', false).html(originalBtnText);
		});
	});
});

/**
 * @return {bool}
 */
var supports_local_storage=function () {
	try {
		return 'localStorage' in window && window['localStorage'] !== null;
	} catch (e) {
		return false;
	}
};

/**
 * @return {bool}
 */
var supports_session_storage=function () {
	try {
		return 'sessionStorage' in window && window['sessionStorage'] !== null;
	} catch (e) {
		return false;
	}
};

/**
 * @param {string}
 * @return {void}
 */
var redirect_blank=function(url) {
	window.location = url;
	return;
};
/**
 * @param {string} name
 * @param {string} url
 * @return {string}
 */
var getParameterByName=function(name, url) {
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, "\\$&");
	var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
		results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, " "));
}
/**
 * @return {void}
 */
var showSpinner=function(){
	$('.camoo-loading').removeClass('invisible');
	$('body').addClass('loading');
};

/**
 * @return {void}
 */
var hideSpinner=function(){
	$('.camoo-loading').addClass('invisible');
	$('body').removeClass('loading');
};
