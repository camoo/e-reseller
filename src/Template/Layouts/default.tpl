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
            <link href="/favicon.ico" type="image/x-icon" rel="icon"/><link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
            <meta name="copyright" content="Camoo Hosting https://www.camoo.hosting"/>
            <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
            <meta name="description" content="{{ description }}">
            <meta name="keywords" content="{{ keywords }}">
            <meta name="Security" content="Public">
            <meta name="Abstract" content="Camoo Sarl">
            <meta name="Author" content="Camoo Sarl">
            <meta name="Robots" content="Noindex">
            <meta name="generator" content="CAMOO SARL"/>
			<!-- meta http-equiv="Content-Security-Policy" content="default-src 'self'" -->
            <!-- link rel="stylesheet" type="text/css" href="/css/style.css" / -->
			{% include 'Layouts/Blocks/Layouts/css.tpl' %}
        {% endblock %}
    </head>
    <body>
        <div id="header">
			{% include 'Layouts/Blocks/Layouts/header.tpl' %}
        </div>
		<div id="rs-slider">{% include 'Layouts/Blocks/Layouts/slider.tpl' %}</div>
		<div id="rs-prising">{% include 'Layouts/Blocks/Layouts/prising.tpl' %}</div>
		<div id="rs-features">{% include 'Layouts/Blocks/Layouts/features.tpl' %}</div>
		<div id="rs-support">{% include 'Layouts/Blocks/Layouts/support.tpl' %}</div>
        <div id="content">{% block content %}{% endblock %}</div>
		<div id="rs-footer">{% include 'Layouts/Blocks/Layouts/footer.tpl' %}</div>
	{% include 'Layouts/Blocks/Layouts/js.tpl' %}
    </body>
</html>
