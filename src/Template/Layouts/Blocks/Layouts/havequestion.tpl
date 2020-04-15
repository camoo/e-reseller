{% block have_question %}
		<div class="have_question">
        <div class="container">
            <div class="row">
                <div class="col-xl-10 offset-xl-1">
                    <div class="single_border">
                        <div class="row align-items-center">
                            <div class="col-xl-6 col-md-4 col-lg-6">
                                <h3>Avez-vous des questions?</h3>
                            </div>
                            <div class="col-xl-6 col-md-8 col-lg-6">
                                <div class="chat">
                                    <a class="boxed_btn_green" href="#">
                                        <i class="flaticon-chat"></i>
                                        <span>{{ siteConfig.company_whatsapp }}</span>
                                    </a>
                                    <a class="boxed_btn_green2" href="/contact">Contact</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
