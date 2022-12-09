var Cart = (function ($) {
    "use strict";
    var me = {
        initialized: false,
        isValidDomain: false,
        READY: 'already',
        NEW: 'new',
        request: {},

        Request: class Request {
            constructor() {
                this.request = new URL(location.href);
            }

            /**
             * @param {string} key
             * @return {string|IterableIterator<[string, string]>}
             */
            getQuery(key) {
                if (typeof key === 'undefined') {
                    return this.request.searchParams.entries();
                }
                return this.request.searchParams.get(key);
            }
        },

        /**
         * @return {void}
         */
        initialize: function () {

            me.request = new me.Request();
            if (me.initialized === true) {
                return;
            }

            me.registerEvents();
            me.initialized = true;
        },

        /**
         * @return {void}
         */
        registerEvents: function () {

            $('.delete-cart-item').on('click', function (evt) {
                showSpinner();
                var sku = $(this).data('sku');

                me.removeItem(this);
                evt.preventDefault();
            });


            $('.add2cart').on('click', function (evt) {
                evt.preventDefault();
                me.addItem(this);
            });

            $('#btn_new_domain').click(function () {
                $('#hosting-domain').attr('placeholder', $('#hosting-domain').data('currentplaceholder'));
                $('#domain-decision').html('Recherchez et ajoutez un domaine');
                $('#domain-decision').attr('data-active', 'new');
            });

            $('#btn_already_domain').click(function () {
                $('#hosting-domain').attr('placeholder', 'Renseignez votre nom de domaine');
                $('#domain-decision').html('Continuez >>');
                $('#domain-decision').attr('data-active', 'already');

            });

            $('#domain-decision').click(function () {
                var active = $('#domain-decision').attr('data-active');
                var val = $('#hosting-domain').val();
                if (val.replace(/^\s*|\s*$/g, '') === '') {
                    alert('Renseignez votre nom de domaine');
                    return;
                }

                //	console.log(me.getDomainDecision());
                //var domainValidation = me.validateDomain(val);
                if (me.getDomainDecision() === me.READY && me.validateDomain(val) === true) {
                    // ADD Domain To Hosting
                    console.log('Epie');
                }

            });
        },

        getDomainDecision: function () {
            return $('#domain-decision').attr('data-active');
        },

        /**
         * @param {string} domain
         * @return {void}
         */
        validateDomain: function (domain) {
            showSpinner();
            $.ajax({
                url: '/domains/is-valid',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {'domain': domain},
                success: function (data) {
                    if (me.getDomainDecision() === me.READY) {
                        // ADD Domain To Hosting
                        console.log(data);
                        if (data.status === false) {
                            alert('Invalid domain name');
                            return;
                        }
                        var hid = me.request.getQuery('kid');
                        var $hosting = $('li#' + hid + ' span[data-id=' + hid + ']');

                        console.log(me.request);
                        console.log('li#' + hid + ' span[data-id=' + hid + ']');
                        me.addDomainToHosting($hosting, domain);

                    } else {
                        $('#domain').val(domain);
                        DomainWhois.lookup();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    hideSpinner();
                    console.log("ERROR");
                    console.log(textStatus);
                    console.log(jqXHR.responseText)
                    console.log(errorThrown)
                },
                complete: function (jqXHR) {
                    hideSpinner();
                }
            });
        },

        addDomainToHosting: function (src, domain) {
            domain = (typeof domain === 'undefined') ? null : domain;
            var type = $(src).data('type');

            if (typeof type === 'undefined' || type !== 'hosting') {
                return;
            }

            showSpinner();
            var sku = $(src).data('sku');
            var belongsTo = $(src).data('belongs') || domain;
            var url = '/basket/add';
            var jsonData = {'sku': sku, 'key': belongsTo, 'type': type};
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                async: true,
                data: jsonData,
                success: function (data) {

                    if (data.status === true && typeof (data.id) !== 'undefined') {

                        window.location = '/basket';
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    hideSpinner();
                    console.log("ERROR");
                    console.log(textStatus);
                    console.log(jqXHR.responseText)
                    console.log(errorThrown)
                },
                complete: function (jqXHR) {
                    hideSpinner();
                }
            });


        },


        addItem: function (src) {
            var type = $(src).data('type');
            if (typeof type !== 'undefined' && type === 'hosting') {
                showSpinner();
                var sku = $(src).data('sku');
                var belongsTo = $(src).data('belongs');
                var url = '/basket/add';
                var jsonData = {'sku': sku, 'key': belongsTo, 'type': type};
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'JSON',
                    cache: false,
                    async: true,
                    data: jsonData,
                    success: function (data) {
                        if (data.status === true && typeof (data.id) !== 'undefined') {
                            // ADD Domain if not yet
                            window.location = '/domains/decision/?kid=' + data.id;
                        }
                    },
                    error: function (jqXHR) {
                        hideSpinner();
                        console.log("ERROR");
                        console.log(jqXHR.responseText)
                    },
                    complete: function (jqXHR) {
                        hideSpinner();
                    }
                });
            }

        },

        /**
         * @param {any} src
         */
        removeItem: function (src) {
            showSpinner();
            var sku = $(src).data('sku');
            var url = '/basket/delete';
            var jsonData = {'sku': sku};
            var type = $(src).data('type');
            if (typeof type !== 'undefined' && type === 'hosting') {
                var id = $(src).data('id');
                if (typeof id !== 'undefined') {
                    if (id) {
                        jsonData.type = 'hosting';
                        jsonData.id = id;
                    }
                }
            }

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                async: true,
                data: jsonData,
                success: function (data) {
                    if (data.status === true) {
                        location.reload();
                    }
                },
                error: function (jqXHR) {
                    hideSpinner();
                    console.log("ERROR");
                    console.log(jqXHR.responseText)
                },
                complete: function (jqXHR) {
                    hideSpinner();
                }
            });

        },

        refreshItem: function (bIncrement) {
        }
    };
    return {
        'initialize': me.initialize,
    };
})(jQuery);

$(function () {
    Cart.initialize();
});
