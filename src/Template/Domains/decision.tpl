{% extends "Layouts/pages.tpl" %}
{% block content %}
<div class="core_features0 dm_area">
            <div class="container">
                <div class="border-bottm">
<div class="row">
<div class="col-md-offset-2 col-md-8 col-xs-12 col-sm-10" id="div_domain_result_form">
                                <div id="start-domaincheck-error" class="invisible"></div>
                <div class="panel panel-default">
					<div class="panel-heading"><h4>Indiquez un nom de domaine pour votre commande</h4></div>
                    <div class="panel-body" id="form-body-search-input">
                            <div class="col-md-12">
                                <p class="p_choice">
                                    <input type="hidden" name="chose_domain" value=""><label for="btn_new_domain"><input type="radio" name="chose_domain" value="0" checked="checked" id="btn_new_domain">Je veux un nouveau nom de domaine</label><label for="btn_already_domain"><input type="radio" name="chose_domain" value="1" style="margin-left:20px" id="btn_already_domain">J’ai déjà un nom de domaine</label>                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                                <div class="input text required"><input type="text" name="rs_domain" aria-invalid="false" aria-required="true" autocapitalize="off" autocorrect="off" class="form-control" placeholder="Entrer le domaine souhaité" data-currentplaceholder="Entrer le domaine souhaité" id="hosting-domain" data-message-invalid-tld="Caractères invalides détectés. Veuillez corriger cette erreur pour continuer" required="required" style="width:63%;height:50px"></div>                            </div>

                        
                        <div id="div-submit-button">
                            <button data-active="new" class="btn btn-primary" id="domain-decision" type="submit">Recherchez et ajoutez un domaine</button>
						</div>
                </div>
            </div>
            </div> <!-- end row -->
			
			
                </div>
            </div>
        </div>
{% endblock %}
