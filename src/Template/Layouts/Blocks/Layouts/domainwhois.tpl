{% block domainwhois %}
    <!-- search_area_start -->
    <div class="search_area">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="search_title">
                        <h3>Résultats</h3>
                    </div>
                </div>
            </div>
            <div class="row mb-20">
                <div class="col-xl-6">
                    <div class="search_result_name">
                        <h4>Nom de domaine</h4>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="search_result_prise text-right">
                        <h4>Prix</h4>
                    </div>
                </div>
            </div>
            <div class="row" id="domain-whois-results">
                <div class="col-xl-12">
				{{domainwhois_results(domain)}}
                </div>
            </div>
        </div>
    </div>
    <!-- search_area_end -->
{% endblock %}
