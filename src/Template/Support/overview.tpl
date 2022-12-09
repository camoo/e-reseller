{% extends "Layouts/pages.tpl" %}
{% block content %}

{{ siteConfig.support | raw }}

{% if siteConfig.support |trim is empty %}
<!-- core_features_start -->
<div class="core_features2 faq_area">
    <div class="container">
        <div class="border-bottm">
            <div class="row">
                <div class="col-xl-12 col-md-12">
                    <div class="featurest_tabs ">
                        <nav>
                            <div class="nav" id="nav-tab" role="tablist">
                                <a class="nav-item nav-link" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="false">General Ask</a>
                                <a class="nav-item nav-link active show" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="true">Technical Support</a>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div id="accordion">
                            <div class="card">
                                <div class="card-header" id="headingTwo">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                                data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            <i class="flaticon-info"></i> Is WordPress hosting worth it?
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                                     data-parent="#accordion">
                                    <div class="card-body">
                                        Our set he for firmament morning sixth subdue darkness creeping gathered divide our
                                        let god moving. Moving in fourth air night bring upon
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="headingOne">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                                data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                            <i class="flaticon-info"></i> What are the advantages <span>of WordPress hosting
                                                                over shared?</span>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion"
                                     style="">
                                    <div class="card-body">
                                        Our set he for firmament morning sixth subdue darkness creeping gathered divide our
                                        let god moving. Moving in fourth air night bring upon
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="headingThree">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                                data-target="#collapseThree" aria-expanded="false"
                                                aria-controls="collapseThree">
                                            <i class="flaticon-info"></i> Will you transfer my site?
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseThree" class="collapse" aria-labelledby="headingThree"
                                     data-parent="#accordion">
                                    <div class="card-body">
                                        Our set he for firmament morning sixth subdue darkness creeping gathered divide our
                                        let god moving. Moving in fourth air night bring upon
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="heading_4">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                                data-target="#collapse_4" aria-expanded="false" aria-controls="collapse_4">
                                            <i class="flaticon-info"></i> Why should I host with Hostza?
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapse_4" class="collapse" aria-labelledby="heading_4" data-parent="#accordion">
                                    <div class="card-body">
                                        Our set he for firmament morning sixth subdue darkness creeping gathered divide our
                                        let god moving. Moving in fourth air night bring upon
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="heading_5">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                                data-target="#collapse_5" aria-expanded="false" aria-controls="collapse_5">
                                            <i class="flaticon-info"></i> How do I get started <span>with Shared
                                                                Hosting?</span>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapse_5" class="collapse" aria-labelledby="heading_5" data-parent="#accordion">
                                    <div class="card-body">
                                        Our set he for firmament morning sixth subdue darkness creeping gathered divide our
                                        let god moving. Moving in fourth air night bring upon
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade active show" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div id="accordion">
                            <div class="card">
                                <div class="card-header" id="headingTwoo">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                                data-target="#collapseTwoo" aria-expanded="false" aria-controls="collapseTwoo">
                                            <i class="flaticon-info"></i> Is WordPress hosting worth it?
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseTwoo" class="collapse" aria-labelledby="headingTwoo"
                                     data-parent="#accordion">
                                    <div class="card-body">
                                        Our set he for firmament morning sixth subdue darkness creeping gathered divide our
                                        let god moving. Moving in fourth air night bring upon
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="headingOne1">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                                data-target="#collapseOne1" aria-expanded="false" aria-controls="collapseOne1">
                                            <i class="flaticon-info"></i> What are the advantages <span>of WordPress hosting
                                                                over shared?</span>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseOne1" class="collapse" aria-labelledby="headingOne1" data-parent="#accordion1"
                                     style="">
                                    <div class="card-body">
                                        Our set he for firmament morning sixth subdue darkness creeping gathered divide our
                                        let god moving. Moving in fourth air night bring upon
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="headingThreee">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                                data-target="#collapseThreee" aria-expanded="false"
                                                aria-controls="collapseThree">
                                            <i class="flaticon-info"></i> Will you transfer my site?
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapseThreee" class="collapse" aria-labelledby="headingThreee"
                                     data-parent="#accordion">
                                    <div class="card-body">
                                        Our set he for firmament morning sixth subdue darkness creeping gathered divide our
                                        let god moving. Moving in fourth air night bring upon
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="heading_44">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                                data-target="#collapse_44" aria-expanded="false" aria-controls="collapse_44">
                                            <i class="flaticon-info"></i> Why should I host with Hostza?
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapse_44" class="collapse" aria-labelledby="heading_44" data-parent="#accordion">
                                    <div class="card-body">
                                        Our set he for firmament morning sixth subdue darkness creeping gathered divide our
                                        let god moving. Moving in fourth air night bring upon
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="heading_55">
                                    <h5 class="mb-0">
                                        <button class="btn btn-link collapsed" data-toggle="collapse"
                                                data-target="#collapse_55" aria-expanded="false" aria-controls="collapse_55">
                                            <i class="flaticon-info"></i> How do I get started <span>with Shared
                                                                Hosting?</span>
                                        </button>
                                    </h5>
                                </div>
                                <div id="collapse_55" class="collapse" aria-labelledby="heading_55" data-parent="#accordion">
                                    <div class="card-body">
                                        Our set he for firmament morning sixth subdue darkness creeping gathered divide our
                                        let god moving. Moving in fourth air night bring upon
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- core_features_end -->
{% endif %}

<!-- have_question_statr -->
<div id="have-question">{% include 'Layouts/Blocks/Layouts/havequestion.tpl' %}</div>
<!-- have_question_end -->


{% endblock %}
