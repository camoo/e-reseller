$(function(){
	$('.camoo-form-spining').on('submit', function (e) {
		showSpinner();
		// DISABLE SUBMIT BUTTON
		var oButtonSubmit = $(this).find('button[type=submit]');
		if (oButtonSubmit.length > 0){
			oButtonSubmit.prop('disabled', true);
		}
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
