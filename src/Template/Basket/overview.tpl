{% extends "Layouts/pages.tpl" %}
{% block content %}
<div class="shopping-cart">
    <div class="container">
  <!-- Title -->
  <div class="title">
    Vous avez <strong>{{ basket.count() }}</strong> élément{% if basket.count() > 1 %}s{% endif %} dans le panier
  </div>
   {% for key,item in basket %}
  <div class="item">
    <div class="buttons">
      <span class="delete-btn"></span>
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
{% endfor %}
  </div>
</div>
{% endblock %}
