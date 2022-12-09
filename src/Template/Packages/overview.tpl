{% extends "Layouts/pages.tpl" %}
{% block content %}

<div class="package_prsing_area">
    <div class="container">
        <div class="row">
            {{ package_plans()|raw }}
        </div>
    </div>
</div>

<div id="rs-features">{% include 'Layouts/Blocks/Layouts/features.tpl' %}</div>
<div id="rs-support">{% include 'Layouts/Blocks/Layouts/support.tpl' %}</div>

<!-- have_question_statr -->
<div id="have-question">{% include 'Layouts/Blocks/Layouts/havequestion.tpl' %}</div>
<!-- have_question_end -->

{% endblock %}
