{% block footer %}
    <!-- footer -->
    <footer class="footer">
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

    <!-- form itself end-->
    <form id="test-form" class="white-popup-block mfp-hide">
        <div class="popup_box ">
            <div class="popup_inner">
                <div class="logo text-center">
                    <a href="#">
                        <img src="/img/logo.png" alt="">
                    </a>
                </div>
                <h3>Sign in</h3>
                <form action="#">
                    <div class="row">
                        <div class="col-xl-12 col-md-12">
                            <input type="email" placeholder="Enter email">
                        </div>
                        <div class="col-xl-12 col-md-12">
                            <input type="password" placeholder="Password">
                        </div>
                        <div class="col-xl-12">
                            <button type="submit" class="boxed_btn_green">Sign in</button>
                        </div>
                    </div>
                </form>
                <p class="doen_have_acc">Don’t have an account? <a class="dont-hav-acc" href="#test-form2">Sign Up</a>
                </p>
            </div>
        </div>
    </form>
    <!-- form itself end -->

    <!-- form itself end-->
    <form id="test-form2" class="white-popup-block mfp-hide">
        <div class="popup_box ">
            <div class="popup_inner">
                <h3>Enreigistrez-vous</h3>
                <form action="#">
                    <div class="row">
<div class="col-md-6 col-sm-6 col-xs-12">                     
  <div class="div_form_add">
    <div class="input text required">
      <input type="text" name="name" placeholder="Votre nom" required="required" id="name">
    </div>  
    <div class="input password required">
      <input type="password" name="password" placeholder="Mot de passe" id="password">
    </div>   
      <div class="input email required">
      <input type="email" name="email" required="required" placeholder="Email *" id="email">
    </div>
    <div class="input text required">
      <input type="text" name="city" placeholder="Votre ville" required="required" id="city">
    </div> 
  </div>
</div>

<div class="col-md-6 col-sm-6 col-xs-12">                     
  <div class="div_form_add">
    <div class="input text required">
      <input type="text" name="fistname" placeholder="Votre présom" required="required" id="firstname">
    </div>  
    <div class="input text">
      <input type="password" name="password_confirm" placeholder="Confirmez votre mot de passe" id="password_confirm">
    </div>   
      <div class="input phone required">
      <input type="number" name="phone" required="required" placeholder="Votre téléphone" id="phone">
    </div>
    <div class="input text required">
      <input type="text" name="address" placeholder="Votre adresse" required="required" id="address">
    </div> 
  </div>
</div>
                        <div class="col-xl-12">
                            <button type="submit" class="boxed_btn_green">Créer votre compte</button>
                        </div>


                    </div>
                </form>
            </div>
        </div>
    </form>
    <!-- form itself end -->
{% endblock %}
