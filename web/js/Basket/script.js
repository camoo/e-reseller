const Cart = (function ($) {
    "use strict";
    const me = {
        initialized: false,
        isValidDomain: false,
        READY: 'already',
        NEW: 'new',
        request: {},
        'decisionBtn': '#domain-decision',

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
                const sku = $(this).data('sku');

                me.removeItem(this);
                evt.preventDefault();
            });


            $('.add2cart').on('click', function (evt) {
                evt.preventDefault();
                me.addItem(this);
            });

            $('#btn_new_domain').on('click', function () {
                $(me.decisionBtn).attr('placeholder', $('#hosting-domain').data('currentplaceholder'));
                $(me.decisionBtn).html('Recherchez et ajoutez un domaine');
                $(me.decisionBtn).attr('data-active', 'new');
            });

            $('#btn_already_domain').on('click', function () {
                $('#hosting-domain').attr('placeholder', 'Renseignez votre nom de domaine');
                $(me.decisionBtn).html('Continuez >>');
                $(me.decisionBtn).attr('data-active', 'already');

            });

            $(me.decisionBtn).on('click', function (ev) {

                const val = $('#hosting-domain').val();
                if (val.replace(/^\s*|\s*$/g, '') === '') {
                    alert('Renseignez votre nom de domaine');
                    return;
                }

                if (me.getDomainDecision() === me.READY) {
                    me.validateDomain(val);
                    // ADD Domain To Hosting
                    console.log('Domain added to hosting');
                }

                if (me.getDomainDecision() === me.NEW) {
                    $('#domain').val(val);
                    console.log('lookup');
                    DomainWhois.lookup();
                }

            });
        },

        getDomainDecision: function () {
            return $(me.decisionBtn).attr('data-active');
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
                        if (data.status === false) {
                            alert('Invalid domain name');
                            return false;
                        }
                        const hid = me.request.getQuery('kid');
                        const $hosting = $('li#' + hid + ' span[data-id=' + hid + ']');
                        console.log($hosting, hid)

                        me.addDomainToHosting($hosting, domain);

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
            const type = $(src).data('type');

            if (typeof type === 'undefined' || type !== 'hosting') {
                return;
            }
            showSpinner();
            const sku = $(src).data('sku');
            const belongsTo = $(src).data('belongs') || domain;
            const url = '/basket/add';
            const jsonData = {'sku': sku, 'key': belongsTo, 'type': type, 'action_key': 'domain_hosting'};

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                async: true,
                data: jsonData,
                success: function (data) {

                    console.log('addDomainToHosting', data);
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
            const type = $(src).data('type');
            if (typeof type !== 'undefined' && type === 'hosting') {
                showSpinner();
                const sku = $(src).data('sku');
                const belongsTo = $(src).data('belongs');
                const url = '/basket/add';
                const jsonData = {'sku': sku, 'key': belongsTo, 'type': type};
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
            const sku = $(src).data('sku');
            const url = '/basket/delete';
            const jsonData = {'sku': sku};
            const type = $(src).data('type');
            if (typeof type !== 'undefined' && type === 'hosting') {
                const id = $(src).data('id');
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
