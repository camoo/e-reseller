{% block slider %}
    <!-- slider_area_start -->
    <div class="slider_area">
        <div class="single_slider d-flex align-items-center justify-content-center slider_bg_1 overlay2">
            <div class="container">
			   {{ show_flash()|raw }}
                <div class="row align-items-center justify-content-center">
                    <div class="col-xl-9">
                        <div class="slider_text text-center">
                            <p>Nous sommes le meilleur fournisseur de nom de domaine et Hébergeur Web au Cameroun</p>
                            <h3>Trouvez votre domaine</h3>
                            <div class="find_dowmain">
							{{ form_start('join', {'id':'domainwhois', 'class': 'find_dowmain_form'})|raw }}
	                        {{ form_input('domain', {'placeholder' : 'Rechercher votre nom de domaine', 'required' : 'required'})|raw }}
							{{ form_input('submit', {'type' : 'submit', 'value' : 'Lancer', 'id' : 'finddomain'}) |raw}}
							{{ form_end() |raw}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- slider_area_end -->
{% endblock %}
