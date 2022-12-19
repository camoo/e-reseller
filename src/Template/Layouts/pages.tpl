<!DOCTYPE html>
<html lang="en">
			{% if not title %}
			  {% set title=siteConfig.title_for_layout %}
			{% endif %}
			{% if not description %}
			  {% set description=siteConfig.site_desc_long %}
			{% endif %}
			{% if not keywords %}
			  {% set keywords=siteConfig.tags %}
			{% endif %}
    <head itemscope itemtype="http://schema.org/WebSite">
        {% block head %}
            <title>{{ title }}</title>
            <meta charset="utf-8"/>
            <link href="/{{ get_favicon_name() }}" type="image/x-icon" rel="icon"/><link href="/{{ get_favicon_name() }}" type="image/x-icon" rel="shortcut icon"/>
            <meta name="copyright" content="Camoo Hosting https://www.camoo.hosting"/>
            <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
            <meta name="description" content="{{ description }}">
            <meta name="keywords" content="{{ keywords }}">
            <meta name="Security" content="Public">
            <meta name="Abstract" content="Camoo Sarl">
            <meta name="Author" content="Camoo Sarl">
            <meta name="Robots" content="Noindex">
            <meta name="generator" content="CAMOO SARL"/>
            <!-- link rel="stylesheet" type="text/css" href="/css/style.css" / -->
			{% include 'Layouts/Blocks/Layouts/css.tpl' %}
        {% endblock %}
    </head>
    <body>
        <div id="header">
			{% include 'Layouts/Blocks/Layouts/header.tpl' %}
        </div>
		<div id="rs-bradcam">{% include 'Layouts/Blocks/Layouts/bradcam.tpl' %}</div>
		<div class="flash-resp">{{ 'flash'|show_flash }}</div>
        <div id="content">{% block content %}{% endblock %}</div>
		<div id="rs-footer">{% include 'Layouts/Blocks/Layouts/footer.tpl' %}</div>
	{% include 'Layouts/Blocks/Layouts/js.tpl' %}
    </body>
</html>
