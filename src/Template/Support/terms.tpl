{% extends "Layouts/pages.tpl" %}
{% block content %}

{{ siteConfig.terms| raw }}

{% if siteConfig.terms |trim is empty %}
<!-- core_features_start -->
<div class="core_features2 faq_area">
    <div class="container">
        <p>Terms content is missing !</p>
    </div>
</div>
<!-- core_features_end -->
{% endif %}

<!-- have_question_statr -->
<div id="have-question">{% include 'Layouts/Blocks/Layouts/havequestion.tpl' %}</div>
<!-- have_question_end -->
{% endblock %}
