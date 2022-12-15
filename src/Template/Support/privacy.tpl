{% extends "Layouts/pages.tpl" %}
{% block content %}

{{ siteConfig.privacy | raw }}

{% if siteConfig.privacy |trim is empty %}
<!-- core_features_start -->
<div class="core_features2 faq_area">
    <div class="container">
        <p>Privacy content is missing !</p>
    </div>
</div>
<!-- core_features_end -->
{% endif %}

<!-- have_question_statr -->
<div id="have-question">{% include 'Layouts/Blocks/Layouts/havequestion.tpl' %}</div>
<!-- have_question_end -->
{% endblock %}
