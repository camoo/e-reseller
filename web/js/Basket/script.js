var Cart=(function($){
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

			$('.delete-cart-item').on('click', function(evt){
				showSpinner();
				var sku = $(this).data('sku');
				console.log(this);
				me.removeItem(this);
				evt.preventDefault();
			});


			$('.add2cart').on('click', function(evt){
				evt.preventDefault();
				me.addItem(this);
			});

			$('#btn_new_domain').click(function(){
				$('#hosting-domain').attr('placeholder', $('#hosting-domain').data('currentplaceholder'));
				$('#domain-decision').html('Recherchez et ajoutez un domaine');
				$('#domain-decision').attr('data-active', 'new');
			});

			$('#btn_already_domain').click(function(){
				$('#hosting-domain').attr('placeholder', 'Renseignez votre nom de domaine');
				$('#domain-decision').html('Continuez >>');
				$('#domain-decision').attr('data-active', 'already');

			});

			$('#domain-decision').click(function(){
				var active = $('#domain-decision').attr('data-active');
				var val = $('#hosting-domain').val();
				if (val.replace(/^\s*|\s*$/g, '') === '') {
					alert('Renseignez votre nom de domaine');
					return;
				}
			console.log(me.isValidDomain(val));	

			});

		},

		isValidDomain : function(domain)
		{
			var isValid = false;
			$.ajax({
				url : '/domains/is-valid',
				type  : 'POST',
				dataType : 'JSON',
				cache: false,
				async:true,
				data : {'domain' : domain},
				success : function (data) {
					isValid = data.status;
						console.log(data);
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

			return isValid;

		},

		addItem: function(src)
		{
			var type = $(src).data('type');
			if ( typeof type !== 'undefined' && type === 'hosting') {
				showSpinner();
				var sku = $(src).data('sku');
				var belongsTo = $(src).data('belongs');
				var url = '/basket/add';
				var jsonData = {'sku' : sku, 'key' : belongsTo, 'type' : type};
				$.ajax({
					url : url,
					type  : 'POST',
					dataType : 'JSON',
					cache: false,
					async:true,
					data : jsonData,
					success : function (data) {
						if ( data.status === true ) {
							// ADD Domain if not yet
						}
						console.log(data);
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

		},

		/**
		 * @param {string} sku
		 */
		removeItem: function(src)
		{
			showSpinner();
			var sku = $(src).data('sku');
			var url = '/basket/delete';
			var jsonData = {'sku' : sku};
			var type = $(src).data('type');
			if ( typeof type !== 'undefined' && type === 'hosting') {
				var id = $(src).data('id');
				if ( typeof id !== 'undefined') {
					if(id) {
						jsonData.type = 'hosting';
						jsonData.id = id;
					}
				}
			}

			$.ajax({
				url : url,
				type  : 'POST',
				dataType : 'JSON',
				cache: false,
				async:true,
				data : jsonData,
				success : function (data) {
					if ( data.status === true ) {
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

		},

		refreshItem: function(bIncrement)
		{
		}
	};
	return {
		'initialize' : me.initialize,
	};
})(jQuery);

$(function(){
	Cart.initialize();
});
