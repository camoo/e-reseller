{% extends "Layouts/pages.tpl" %}
{% block content %}

<!-- core_features_start -->
<div class="core_features2 faq_area">
    <div class="container">
        {{ siteConfig.terms| raw }}
        {% if siteConfig.terms |trim is empty %}
        <p>Terms content is missing !</p>
        {% endif %}
    </div>
</div>
<!-- core_features_end -->


<!-- have_question_statr -->
<div id="have-question">{% include 'Layouts/Blocks/Layouts/havequestion.tpl' %}</div>
<!-- have_question_end -->
{% endblock %}
