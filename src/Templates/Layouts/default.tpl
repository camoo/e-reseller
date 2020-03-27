<!DOCTYPE html>
<html lang="en">
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
            <!-- link rel="stylesheet" type="text/css" href="/css/style.css" / -->
			{% include 'Blocks/Layouts/css.tpl' %}
        {% endblock %}
    </head>
    <body>
        <div id="header">
			{% include 'Blocks/Layouts/header.tpl' %}
        </div>
		<div id="rs-slider">{% include 'Blocks/Layouts/slider.tpl' %}</div>
		<div id="rs-prising">{% include 'Blocks/Layouts/prising.tpl' %}</div>
		<div id="rs-features">{% include 'Blocks/Layouts/features.tpl' %}</div>
		<div id="rs-support">{% include 'Blocks/Layouts/support.tpl' %}</div>
        <div id="content">{% block content %}{% endblock %}</div>
		<div id="rs-footer">{% include 'Blocks/Layouts/footer.tpl' %}</div>
	{% include 'Blocks/Layouts/js.tpl' %}
    </body>
</html>
