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

			$('#domain-whois-results .disable').on('click', function(evt){
				evt.preventDefault();
			});


			$('#domain-whois-results .add-to-basket').on('click', function(evt){
				if ($(this).hasClass('disable')) {
					return false;
				}
				me.addToBasket(this);
				evt.preventDefault();
			});

		},

		addToBasket: function(src)
		{
			var domain = $(src).data('domain');
			showSpinner();
			var url = '/domain-add-to-basket';
			var token = $('#domainwhois').find('input[name=__csrf_Token]').val();
				var jsonData = {'domain' : domain, '__csrf_Token' : token};
			$.ajax({
				url : url,
				type  : 'POST',
				dataType : 'JSON',
				cache: false,
				async:true,
				data : jsonData,
				success : function (data) {
					if ( data.status === true ) {
						// Show basket
						me.updateBasket(true);
						var $div = $(src).closest('div');
						$($div).find('.trigger-domain').addClass('disable').html('Dans le panier');
					//	me.openInNewTab('/domain?d='+ data.domain + '#domain-whois-results');
					} else {
					///	location.reload();
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
		},

		removeFromBasket: function(src)
		{
			var domain = $(src).data('domain');
			showSpinner();
			var url = '/domain-remove-basket';
			var token = $('#domainwhois').find('input[name=__csrf_Token]').val();
				var jsonData = {'domain' : domain, '__csrf_Token' : token};
			$.ajax({
				url : url,
				type  : 'POST',
				dataType : 'JSON',
				cache: false,
				async:true,
				data : jsonData,
				success : function (data) {
					if ( data.status === true ) {
						me.updateBasket(false);
					//	me.openInNewTab('/domain?d='+ data.domain + '#domain-whois-results');
					} else {
					///	location.reload();
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
		},

		updateBasket: function(bIncrement)
		{
			var xCount = $('span#cart-count').html();
			var iCount = xCount.replace(/^\s*|\s*$/g, '') === ''? 0 : parseInt(xCount);

			if (bIncrement) {
				$('#line-cart').removeClass('invisible');
				iCount++;
			}else {
				iCount--;
			}
			if ( iCount < 1 ) {
				$('span#cart-count').html(0);
				$('line-cart').addClass('invisible');
			} else{
				$('span#cart-count').html(iCount);
			}
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
						me.openInNewTab('/domain?d='+ data.domain + '#domain-whois-results');
					} else {
						location.reload();
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
		'lookup' : me.whois,
	};
})(jQuery);

$(function(){
	if($('#domainwhois').length > 0) {
		DomainWhois.initialize();
	}
});
