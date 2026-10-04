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

			me.scrollToResults();
			setTimeout(function() {
				me.scrollToResults();
			}, 250);
		},

		/**
		 * Smoothly scroll to results section if present on page
		 * @return {void}
		 */
		scrollToResults: function() {
			var $results = $('#domain-whois-results');
			if ($results.length > 0 && $results.find('.single_search').length > 0) {
				var $container = $results.closest('.search_area');
				var targetTop = $container.length > 0 ? $container.offset().top - 30 : $results.offset().top - 30;
				$('html, body').stop().animate({
					scrollTop: Math.max(0, targetTop)
				}, 450);
			}
		},

		/**
		 * @return {void}
		 */
		registerEvents: function() {

			$(document).on('submit', '#domainwhois', function(evt){
				evt.preventDefault();
				me.whois();
			});

			$(document).on('click', '#domain-whois-results .disable', function(evt){
				evt.preventDefault();
			});

			$(document).on('click', '#domain-whois-results .add-to-basket', function(evt){
				if ($(this).hasClass('disable')) {
					return false;
				}
				me.addToBasket(this);
				evt.preventDefault();
			});

			$(document).on('click', '[data-tld]', function(evt){
				var tld = $(this).data('tld');
				if (tld && tld.charAt(0) === '.') {
					var input = $('#domainwhois').find('input[name=domain]');
					if (input.length > 0) {
						var current = input.val().trim();
						if (current === '' || current.indexOf('.') === -1) {
							var baseName = current !== '' ? current : 'monentreprise';
							input.val(baseName + tld);
						} else {
							var nameWithoutExt = current.split('.')[0];
							input.val(nameWithoutExt + tld);
						}
						$('html, body').animate({
							scrollTop: $('#domainwhois').offset().top - 120
						}, 350);
						input.focus();
						evt.preventDefault();
					}
				}
			});
		},

		addToBasket: function(src)
		{
			var domain = $(src).data('domain');
			showSpinner();
			var url = '/domain-add-to-basket';
			var token = $('#domainwhois').find('input[name=__csrf_Token]').val() || $('input[name=__csrf_Token]').first().val();
			var jsonData = {'domain' : domain, '__csrf_Token' : token};
			$.ajax({
				url : url,
				type  : 'POST',
				dataType : 'JSON',
				cache: false,
				async: true,
				data : jsonData,
				success : function (data) {
					if ( data.status === true ) {
						// Show basket
						me.updateBasket(true);
						var $div = $(src).closest('div');
						$($div).find('.trigger-domain').addClass('disable').html('Dans le panier');
					} else {
						hideSpinner();
						alert('Impossible d’ajouter ce domaine au panier. Veuillez réessayer.');
					}
				},
				error: function ( jqXHR, textStatus,  errorThrown ) {
					hideSpinner();
					console.log("ERROR", textStatus, jqXHR.responseText, errorThrown);
					alert('Une erreur est survenue lors de l’ajout au panier.');
				},
				complete: function () {
					hideSpinner();
				}
			});
		},

		removeFromBasket: function(src)
		{
			var domain = $(src).data('domain');
			showSpinner();
			var url = '/domain-remove-basket';
			var token = $('#domainwhois').find('input[name=__csrf_Token]').val() || $('input[name=__csrf_Token]').first().val();
			var jsonData = {'domain' : domain, '__csrf_Token' : token};
			$.ajax({
				url : url,
				type  : 'POST',
				dataType : 'JSON',
				cache: false,
				async: true,
				data : jsonData,
				success : function (data) {
					if ( data.status === true ) {
						me.updateBasket(false);
					}
				},
				error: function ( jqXHR, textStatus,  errorThrown ) {
					hideSpinner();
					console.log("ERROR", textStatus, jqXHR.responseText, errorThrown);
				},
				complete: function () {
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
				$('#line-cart').addClass('invisible');
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
			var $form = $('#domainwhois');
			var $input = $form.find('input[name=domain]');
			var domainVal = $.trim($input.length > 0 ? $input.val() : $('#domain').val());

			if (!domainVal) {
				return;
			}

			showSpinner();
			var url = '/domain-whois';
			var token = $form.find('input[name=__csrf_Token]').val() || $('input[name=__csrf_Token]').first().val();
			var jsonData = {'domain' : domainVal, '__csrf_Token' : token};

			$.ajax({
				url : url,
				type  : 'POST',
				dataType : 'JSON',
				cache: false,
				async: true,
				data : jsonData,
				success : function (data) {
					if ( data.status === true ) {
						var targetUrl = '/domain?d=' + encodeURIComponent(data.domain) + '#domain-whois-results';
						if (window.location.pathname === '/domain') {
							var currentD = (new URLSearchParams(window.location.search)).get('d');
							if (currentD === data.domain) {
								window.location.reload();
								return;
							}
						}
						window.location.href = targetUrl;
					} else {
						hideSpinner();
						var msg = 'Nom de domaine invalide ou indisponible.';
						if (data.result && typeof data.result === 'object') {
							var errors = [];
							for (var k in data.result) {
								if (data.result.hasOwnProperty(k)) {
									var errVal = data.result[k];
									if (typeof errVal === 'string') {
										errors.push(errVal);
									} else if (typeof errVal === 'object' && errVal !== null) {
										errors.push(Object.values(errVal).join(', '));
									}
								}
							}
							if (errors.length > 0) {
								msg = errors.join("\n");
							}
						}
						alert(msg);
					}
				},
				error: function ( jqXHR, textStatus,  errorThrown ) {
					hideSpinner();
					console.log("ERROR", textStatus, jqXHR.responseText, errorThrown);
					alert('Une erreur est survenue lors de la vérification du domaine. Veuillez réessayer.');
				}
			});
		}
	};
	return {
		'initialize' : me.initialize,
		'lookup' : me.whois,
		'scrollToResults': me.scrollToResults,
	};
})(jQuery);

$(function(){
	if($('#domainwhois').length > 0 || $('#domain-whois-results').length > 0) {
		DomainWhois.initialize();
	}
});
