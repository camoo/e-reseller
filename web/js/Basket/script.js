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
				me.removeItem(sku);
				evt.preventDefault();
			});

		},

		addItem: function()
		{
		},

		/**
		* @param {string} sku
		*/
		removeItem: function(sku)
		{
			showSpinner();
			var url = '/basket/delete';
				var jsonData = {'sku' : sku};
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
