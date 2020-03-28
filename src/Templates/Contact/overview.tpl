{% extends "Layouts/pages.tpl" %}
{% block content %}
	    <!-- ================ contact section start ================= -->
    <section class="contact-section">
            <div class="container">
                <div class="d-none d-sm-block mb-5 pb-4">
                </div>
    
                <div class="row">
                    <div class="col-12">
                        <h2 class="contact-title">Contactez-nous</h2>
                    </div>
                    <div class="col-lg-8">
                        <form class="form-contact contact_form" method="post" id="contactForm" novalidate="novalidate">
						<input type='hidden' name='__csrf_Token' value="{{ __csrf_Token }}" /> 
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <textarea class="form-control w-100" name="message" id="message" cols="30" rows="9" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter Message'" placeholder=" Name"></textarea>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input class="form-control valid" name="name" id="name" type="text" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter your name'" placeholder="Enter your name">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <input class="form-control valid" name="email" id="email" type="email" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter email address'" placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <input class="form-control" name="subject" id="subject" type="text" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter Subject'" placeholder="Enter Subject">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                <button type="submit" class="button button-contactForm boxed-btn">Envoyez</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-3 offset-lg-1">
                        <div class="media contact-info">
                            <span class="contact-info__icon"><i class="ti-home"></i></span>
                            <div class="media-body">
                                <h3>{{ siteConfig.brand_address }}, {{ siteConfig.brand_city }}</h3>
                                <p>{{ siteConfig.brand_pobox }} {{ siteConfig.brand_country }}</p>
                            </div>
                        </div>
                        <div class="media contact-info">
                            <span class="contact-info__icon"><i class="ti-tablet"></i></span>
                            <div class="media-body">
                                <h3>{{ siteConfig.company_tel }}</h3>
                                <p>7j/7</p>
                            </div>
                        </div>
                        <div class="media contact-info">
                            <span class="contact-info__icon"><i class="ti-email"></i></span>
                            <div class="media-body">
                                <h3>{{ siteConfig.contact_email }}</h3>
                                <p>Contactez-nous à nous moment!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <!-- ================ contact section end ================= -->
{% endblock %}
