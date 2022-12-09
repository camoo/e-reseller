{% extends "Layouts/pages.tpl" %}
{% block content %}

{{ siteConfig.about_us | raw }}

<!-- have_question_statr -->
<div id="have-question">{% include 'Layouts/Blocks/Layouts/havequestion.tpl' %}</div>
<!-- have_question_end -->

{% endblock %}
