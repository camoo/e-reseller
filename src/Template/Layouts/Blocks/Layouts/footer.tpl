{% block footer %}
    <!-- footer -->
    <footer class="footer">
	<div class="camoo-loading invisible"></div>
        <div class="footer_top">
            <div class="container">
                <div class="row">
                    <div class="col-xl-3 col-md-6 col-lg-3">
                        <div class="footer_widget">
                            <div class="footer_logo">
                                <a href="#">
                                    <img src="img/logo.png" alt="">
                                </a>
                            </div>
                            <p class="footer_text doanar"> <a class="first" href="#">{{ siteConfig.company_tel }}
                                </a> <br>
                                <a href="#">{{ siteConfig.contact_email }}</a></p>
                            <div class="socail_links">
                                <ul>
                                    <li>
                                        <a href="#">
                                            <i class="fa fa-facebook-square"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <i class="fa fa-twitter"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <i class="fa fa-instagram"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-lg-3">
                        <div class="footer_widget">
                            <h3 class="footer_title">
                                service
                            </h3>
                            <ul>
                                <li><a href="#">Hosting</a></li>
                                <li><a href="#">Domain</a></li>
                                <li><a href="#">Wordpress</a></li>
                                <li><a href="#">Shared Hosting</a></li>
                            </ul>

                        </div>
                    </div>
                    <div class="col-xl-2 col-md-6 col-lg-2">
                        <div class="footer_widget">
                            <h3 class="footer_title">
                                Navigation
                            </h3>
                            <ul>
                                <li><a href="#">Home</a></li>
                                <li><a href="#">Rooms</a></li>
                                <li><a href="#">About</a></li>
                                <li><a href="#">News</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6 col-lg-4">
                        <div class="footer_widget">
                            <h3 class="footer_title">
                                Newsletter
                            </h3>
                            <form action="#" class="newsletter_form">
                                <input type="text" placeholder="Votre E-mail">
                                <button type="submit">S'abonner</button>
                            </form>
                            <p class="newsletter_text">Abonnez-vous à notre newsletter pour ne pas rater nos dernières publications</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copy-right_text">
            <div class="container">
                <div class="footer_border"></div>
                <div class="row">
                    <div class="col-xl-12">
                        <p class="copy_right text-center">
                &copy; Copyright {{ "now"|date('Y', "Africa/Douala") }} by {{ siteConfig.company_name }}. <a class="right" href="https://colorlib.com" target="_blank">Colorlib</a></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- footer -->
    <!-- link that opens popup -->

	{% if is_loggedin() == false %}
    <!-- form itself end-->
    <section id="test-form" class="white-popup-block mfp-hide">
        <div class="popup_box ">
            <div class="popup_inner">
                <div class="logo text-center">
                    <a href="#">
                        <img src="/img/logo.png" alt="">
                    </a>
                </div>
                <h3>Connexion au compte</h3>
				{{ form_start('login', {'url':'/login', 'class' : 'camoo-form-spining'})|raw}}
                    <div class="row">
                        <div class="col-xl-12 col-md-12">
							{{ form_input('username', {'type': 'email', 'placeholder' : 'Votre E-Mail'})|raw }}

                        </div>
                        <div class="col-xl-12 col-md-12">
							{{ form_input('passwd', {'type' : 'password', 'placeholder' : 'Mot de passe'})|raw }}
                        </div>
                        <div class="col-xl-12">
							{{ form_input('submit', {'type' : 'submit', 'class' : 'boxed_btn_green', 'value' : 'Se connecter'}) |raw}}
                        </div>
                    </div>
				{{ form_end() |raw}}
                <p class="doen_have_acc">Pas encore un compte? <a class="dont-hav-acc" href="#test-form2">S'enregistrer</a>
                </p>
            </div>
        </div>
    </section>
    <!-- form itself end -->
	{% endif %}

	{% if is_loggedin() == false %}
    <!-- form itself end-->
    <section id="test-form2" class="white-popup-block mfp-hide">
        <div class="popup_box ">
            <div class="popup_inner">
                <h3>Enregistrez-vous</h3>
				{{ form_start('join', {'url':'/join', 'class': 'camoo-form-spining'})|raw}}
                    <div class="row">
<div class="col-md-6 col-sm-6 col-xs-12">                     
  <div class="div_form_add">
    <div class="input text required">
	  {{ form_input('name', {'placeholder' : 'Votre Nom', 'required' : 'required'})|raw }}
    </div>  
    <div class="input password required">
	  {{ form_input('password', {'placeholder' : 'Mot de passe', 'required' : 'required'})|raw }}
    </div>   
      <div class="input email required">
	  {{ form_input('email', {'placeholder' : 'Votre E-mail', 'required' : 'required'})|raw }}
    </div>
    <div class="input text required">
	  {{ form_input('city', {'placeholder' : 'Votre ville', 'required' : 'required'})|raw }}
    </div> 
  </div>
</div>

<div class="col-md-6 col-sm-6 col-xs-12">                     
  <div class="div_form_add">
    <div class="input text required">
	  {{ form_input('firstname', {'placeholder' : 'Votre Prénom', 'required' : 'required'})|raw }}
    </div>  
    <div class="input password">
	  {{ form_input('password_confirm', {'type': 'password', 'placeholder' : 'Confirmer votre Mot de passe', 'required' : 'required'})|raw }}
    </div>   
      <div class="input phone required">
	  {{ form_input('phone', {'type':'number', 'placeholder' : 'Votre téléphone', 'required' : 'required'})|raw }}
    </div>
    <div class="input text required">
	  {{ form_input('address', {'placeholder' : 'Votre adresse (Rue/Quartier)', 'required' : 'required'})|raw }}
    </div> 
  </div>
</div>
                        <div class="col-xl-12">
                            <button type="submit" class="boxed_btn_green">Créer votre compte</button>
                        </div>
                    </div>
				{{ form_end() |raw}}
            </div>
        </div>
    </section>
    <!-- form itself end -->
	{% endif %}
{% endblock %}
