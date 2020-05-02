{% block header %}
    <!-- header-start -->
    <header>
        <div class="header-area ">
            <div id="sticky-header" class="main-header-area">
                <div class="container-fluid p-0">
                    <div class="row align-items-center no-gutters">
                        <div class="col-xl-2 col-lg-2">
                            <div class="logo-img">
                                <a href="/">
                                    <img src="/img/logo.png" alt="">
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-7 col-lg-7">
                            <div class="main-menu  d-none d-lg-block">
                                <nav>
                                    <ul id="navigation">
                                        <li><a class="{{ pages_active }}" href="/">Accueil</a></li>
                                        <li><a href="package.html">Package</a></li>
                                        <li><a href="#">blog <i class="ti-angle-down"></i></a>
                                            <ul class="submenu">
                                                <li><a href="blog.html">blog</a></li>
                                                <li><a href="single-blog.html">single-blog</a></li>
                                            </ul>
                                        </li>
                                        <li><a href="#">pages <i class="ti-angle-down"></i></a>
                                            <ul class="submenu">
                                                <li><a href="elements.html">elements</a></li>
                                            </ul>
                                        </li>
                                        <li><a href="Support.html">Support</a></li>
                                        <li><a href="about.html">About</a></li>
                                        <li><a class="{{ contact_active }}" href="/contact">Contact</a></li>
										{% if basket_counter() > 0%}
										<li> 
										<div class="icon-cart" style="float: left">
<div class="cart-line-1" style="background-color: #C5BFB6"></div>
<div class="cart-line-2" style="background-color: #C5BFB6"></div>
<div class="cart-line-3" style="background-color: #C5BFB6"></div>
<div class="cart-wheel" style="background-color: #C5BFB6"></div>
<span style="margin-left: 24px;" id="cart-count" class="w3-badge w3-red">{{ basket_counter() }}</span>
</div>
										</li>
										{% endif %}
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 d-none d-lg-block">
                            <div class="log_chat_area d-flex align-items-center">
								{% if is_loggedin() %}
                                <a href="/logout" id="camoo-logout" class="login">
                                    <i class="flaticon-lock"></i>
                                    <span id="logout">Déconnexion</span>
                                </a>
                                <div class="live_chat_btn">
                                    <a class="boxed_btn_green" href="#" id="camoo-dashbord">
                                        <i class="flaticon-browser"></i>
                                        <span>cPanel</span>
                                    </a>
                                </div>
								{% else %}
                                <a href="#test-form" id="camoo-login" class="login popup-with-form">
                                    <i class="flaticon-user"></i>
                                    <span id="login">Connexion</span>
                                </a>
								{% endif %}
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mobile_menu d-block d-lg-none"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- header-end -->
{% endblock %}
