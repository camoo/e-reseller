var DomainWhois=(function($){
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

			$('#domainwhois').on('submit', function(evt){
				showSpinner();
				me.whois();
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
		whois: function() {
			showSpinner();
			var url = '/domain-whois';
			var token = $('#domainwhois').find('input[name=__csrf_Token]').val();
				var jsonData = {'domain' : $('#domain').val(), '__csrf_Token' : token};
			$.ajax({
				url : url,
				type  : 'POST',
				dataType : 'JSON',
				cache: false,
				async:true,
				data : jsonData,
				success : function (data) {
					if ( data.status === true ) {
						console.log(data);
						//me.openInNewTab(data.sso_link);
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
	if($('#domainwhois').length > 0) {
		DomainWhois.initialize();
	}
});
