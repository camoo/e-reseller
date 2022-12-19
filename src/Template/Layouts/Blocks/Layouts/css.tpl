{% block css %}
<!-- link rel="stylesheet" href="/css/bootstrap.min.css" -->
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<link rel="stylesheet" href="/css/owl.carousel.min.css">
<link rel="stylesheet" href="/css/magnific-popup.css">
<link rel="stylesheet" href="/css/font-awesome.min.css">
<link rel="stylesheet" href="/css/themify-icons.css">
<link rel="stylesheet" href="/css/nice-select.css">
<link rel="stylesheet" href="/css/flaticon.css">
<link rel="stylesheet" href="/css/gijgo.css">
<link rel="stylesheet" href="/css/animate.css">
<link rel="stylesheet" href="/css/slicknav.css">
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/css/ers.css?{{ date().timestamp }}">
{% if add_custom_css() %}
    <link rel="stylesheet" href="/css/custom.css?{{ date().timestamp }}">
{% endif %}
<link rel="stylesheet" href="/css/responsive.css">
{{ html_fetch('css') }}
{% endblock %}
