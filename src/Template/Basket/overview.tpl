{% extends "Layouts/pages.tpl" %}
{% block content %}
<div class="shopping-cart">
    <div class="container">
  <!-- Title -->
  <div class="title">
    Vous avez <strong>{{ basket.count() }}</strong> élément{% if basket.count() > 1 %}s{% endif %} dans le panier
  </div>
   {% for key,item in basket %}
   {% if key == "hosting" %}
     {% for idex,hash in item %}
  <div class="item" id="{{hash.id}}">
    <div class="buttons">
      <span title="Supprimer" data-id="{{hash.id}}" data-type="hosting" data-sku="{{hash.sku}}" class="delete-btn delete-cart-item"></span>
    </div>
 
    <div class="image">
	  <span style="font-size:50px;"><i class="{% if  hash.basket_icon %}{{ hash.basket_icon }}{% else %}flaticon-servers{% endif %}"></i></span>
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
	  <span style="font-size:50px;"><i class="{% if  item.basket_icon %}{{ item.basket_icon }}{% else %}flaticon-servers{% endif %}"></i></span>
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
      <h2 class="payment-method-heading" style="">Choose a payment method to complete your purchase</h2>
      <div class="CartSection pay-sepr" id="CartSection_paymentOptions" style="">
        <div class="paymentOption rfloat opt-2" id="paymentOption_offline">
          <h3 class="ui-subheading">Offline Payment Options</h3>
          <p class="txt-info">Add your order now and pay later using cheque/cash.</p>
          <p class="italic_font" style="">By clicking on "Pay Offline" you agree to the<br><a target="_blank" href="/support/terms">Terms &amp; Conditions</a> + <a href="/support/privacy" target="_blank">associated agreements</a> pertaining to the above products.</p>
          <p><button id="pay_offline_button" class="uiButton btn-space" type="button"><span><span>Pay Offline</span></span></button></p>
        </div>

      </div>


      <div id="zeroValueCart" class="CartSection" style="display: none;">
        <h2 class="HeadingActive">Please Confirm Your Order</h2>
        <p style="padding-bottom: 10px;">Just click below to confirm your order.</p>
        <p><button id="pay_offline_button" class="ui-button" type="button"><span><span>Confirm Order</span></span></button></p>
      </div>
      <div class="paymentOption" id="visa_only_payment_online" style="display: none;">
        <h3>VISA Only Payment Gateway</h3>
        <p>This is offer is applicable only to VISA card holders</p>
        <div class="clear"></div>
        <p><button id="visa_gateway_button" class="ui-button"><span><span>Pay Now</span></span></button></p>
      </div>

    </div>
  </div>

</div>
{% endif %}
{% endblock %}
