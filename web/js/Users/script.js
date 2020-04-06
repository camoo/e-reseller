var Users=(function($){
	"use strict";
	var me = {
		initialized: false,

		/**
		* @return {void}
		*/
		initialize: function (){

			if (me.initialized === true) {
				return;
			}

			me.registerEvents();
			me.initialized = true;
		},

		/**
		 * @return {void}
		 */
		registerEvents: function() {

			$('#camoo-dashbord').on('click', function(evt){
				console.log(this);
				showSpinner();
				me.getSSO();
				evt.preventDefault();
			});
		},

		/**
		 * @param {string} url
		 * @return {void}
		 */
		openInNewTab: function(url) {
			window.location = url;
		},

		/**
		 * @return {void}
		 */
		getSSO: function() {
			showSpinner();
			var url = '/sso';
			$.ajax({
				url : url,
				type  : 'get',
				dataType : 'JSON',
				cache: false,
				async:true,
				data : {},
				success : function (data) {
					if ( data.status === true ) {
						me.openInNewTab(data.sso_link);
					} else {
						//location.reload();
					}
				},
				error: function ( jqXHR, textStatus,  errorThrown ) {
					hideSpinner();
					console.log("ERROR");
					console.log(textStatus);
					console.log(jqXHR.responseText)
					console.log(errorThrown)
				},
				complete:function (jqXHR) {
					hideSpinner();
				}
			});
		}
	};
	return {
		'initialize' : me.initialize,
	};
})(jQuery);

$(function(){
	if($('#camoo-dashbord').length > 0) {
		Users.initialize();
	}
});
