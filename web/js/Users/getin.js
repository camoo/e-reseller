const GetIn = (function ($) {
	"use strict";
	const me = {
		initialized: false,
		hash: null,

		/**
		 * @return {void}
		 */
		initialize: function () {

			if (me.initialized === true) {
				return;
			}

			me.initialized = true;
			me.hash = window.location.hash;
			me.registerEvents();
		},

		/**
		 * @return {void}
		 */
		registerEvents: function () {

			if (me.hash === '#login') {
				me.showLogin();
			}

			if (me.hash === '#join') {
				me.showRegister();
			}


			if (me.hash === '#show-momo') {
				me.showMoMoForm();
			}
		},

		/**
		 * @return {void}
		 */
		showLogin: function () {
			$('span#login').click();
		},

		/**
		 * @return {void}
		 */
		showRegister: function () {
			$('a.dont-hav-acc').click();
		},

		showMoMoForm: function (){
			$('span#show-momo').click();
		}

	};
	return {
		'initialize': me.initialize,
	};
})(jQuery);

$(function(){
	if($('#camoo-login').length > 0) {
		GetIn.initialize();
	}
});
