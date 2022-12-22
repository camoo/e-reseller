{% extends "Layouts/pages.tpl" %}
{% block content %}
<div class="shopping-cart">
    <div class="container">
        <!-- Title -->
        <div class="title">
            Vous avez <strong>{{ basket.count() }}</strong> élément{% if basket.count() > 1 %}s{% endif %} dans le
            panier
        </div>
        {% for key,item in basket %}
        {% if key == "hosting" %}
        {% for idex,hash in item %}
        <div class="item" id="{{hash.id}}">
            <div class="buttons">
                <span title="Supprimer" data-id="{{hash.id}}" data-type="hosting" data-sku="{{hash.sku}}"
                      class="delete-btn delete-cart-item"></span>
            </div>

            <div class="image">
                <span style="font-size:50px;"><i
                            class="{% if  hash.basket_icon %}{{ hash.basket_icon }}{% else %}flaticon-servers{% endif %}"></i></span>
            </div>

            <div class="description">
                <span>{{ hash.human_name }}</span>
                <span>{{ hash.description }}</span>
            </div>

            <div class="quantity"></div>
            <div class="total-price">XAF {{ hash.price }}</div>
        </div>
        {% endfor %}

        {% else %}
        <div class="item">
            <div class="buttons">
                <span title="Supprimer" data-sku="{{key}}" class="delete-btn delete-cart-item"></span>
            </div>

            <div class="image">
                <span style="font-size:50px;"><i
                            class="{% if  item.basket_icon %}{{ item.basket_icon }}{% else %}flaticon-servers{% endif %}"></i></span>
            </div>

            <div class="description">
                <span>{{ key }}</span>
                <span>{{ item.description }}</span>
            </div>

            <div class="quantity"></div>
            <div class="total-price">XAF {{ item.price }}</div>
        </div>
        {% endif %}
        {% endfor %}

        {% if not is_loggedin() %}
        <div class="col-xl-12 cart-goto-login">
            <a id="user-join" href="/#login" class="boxed_btn_green">Se connecter ou créer un compte</a>
        </div>
        {% endif %}
    </div>
</div>

{% if is_loggedin() and basket.count() > 0 %}
<div id="payment-options">
    <div class="container">
        <div class="row">
            <h2 class="payment-method-heading">Choisissez un mode de paiement pour finaliser votre achat</h2>
            <div class="CartSection pay-sepr" id="CartSection_paymentOptions">
                <div class="paymentOption rfloat opt-2" id="paymentOption_offline">
                    <h3 class="ui-subheading">Options de paiement</h3>
                    <p class="txt-info">Ajoutez votre commande maintenant ou payez plus tard par chèque/espèces ou
                        dépôt.</p>
                    <p class="italic_font">En cliquant sur "Payer hors ligne" ou "Payer par Mobile Money", vous acceptez les<br><a target="_blank"
                                                                                                       href="/support/terms">Terms
                            &amp; Conditions</a> + <a href="/support/privacy" target="_blank">associated agreements</a>
                        concernant les produits ci-dessus.</p>
                    <p>
                        <button id="pay-offline-button" class="uiButton btn-space" type="button"><span><span>Payer hors ligne</span></span>
                        </button>
                        {% if siteConfig.can_mobile_money %}
                        |
                        <a href="#momo-form" id="momo-payment" class="uiButton btn-space popup-with-form">
                            <i class="flaticon-security"></i>
                            <span id="show-momo">Payer par Mobile Money</span>
                        </a>
                        {% endif %}
                    </p>
                </div>
            </div>

            <div id="zeroValueCart" class="CartSection invisible">
                <p id="payment-response" style="padding-bottom: 10px;"></p>
            </div>
            <div class="paymentOption" id="visa_only_payment_online" style="display: none;">
                <h3>VISA Only Payment Gateway</h3>
                <p>This is offer is applicable only to VISA card holders</p>
                <div class="clear"></div>
                <p>
                    <button id="visa_gateway_button" class="ui-button"><span><span>Pay Now</span></span></button>
                </p>
            </div>

        </div>
    </div>

</div>

{% endif %}
{% endblock %}
