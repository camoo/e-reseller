{% extends "Layouts/pages.tpl" %}
{% block content %}
	    <!-- ================ contact section start ================= -->
    <section class="contact-section-1">
            <div class="container">
                <div class="d-none d-sm-block mb-5 pb-4">
                </div>
    
                <div class="row">
                    <div class="col-12">
                        <h2 class="contact-title">Contactez-nous</h2>
                    </div>
                    <div class="col-lg-8">
					{{ form_start() }}
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
									{{ form_input('message', {'type' : 'textarea', 'class' : 'form-control w-100', 'placeholder' : 'Votre Message', 'cols' : 30, 'rows' : 9, 'required' : 'required' })  }}
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
									{{ form_input('name', {'required' : 'required','class' : 'form-control valid', 'placeholder' : 'Votre nom' })  }}
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
									{{ form_input('email', {'required' : 'required','class' : 'form-control valid', 'placeholder' : 'Votre E-mail' })  }}
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
									{{ form_input('subject', {'required' : 'required','class' : 'form-control valid', 'placeholder' : 'L\'object' })  }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                <button type="submit" class="button button-contactForm boxed-btn">Envoyez</button>
                            </div>
						{{ form_end() }}
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
                                <p>Contactez-nous à tout moment!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <!-- ================ contact section end ================= -->
{% endblock %}
