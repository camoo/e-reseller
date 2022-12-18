{% extends "Layouts/pages.tpl" %}
{% block content %}

<!-- core_features_start -->
<div class="core_features2 faq_area">
    <div class="container">
        {{ siteConfig.privacy | raw }}
        {% if siteConfig.privacy |trim is empty %}
        <p>Privacy content is missing !</p>
        {% endif %}
    </div>
</div>
<!-- core_features_end -->


<!-- have_question_statr -->
<div id="have-question">{% include 'Layouts/Blocks/Layouts/havequestion.tpl' %}</div>
<!-- have_question_end -->
{% endblock %}
