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
      <div class="col-xl-12">
          <button id="user-join" type="submit" class="boxed_btn_green">Créer votre compte</button>
      </div>
  </div>
</div>
{% endblock %}
