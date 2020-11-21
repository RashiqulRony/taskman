@extends('spark::layouts.layout_2')
@section("title")
    Register
@endsection
@section("styles")
    <link href="/css/themify-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/login.css">
@endsection
@section('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript" src="https://static.leaddyno.com/js"></script>
    <script>
        LeadDyno.key = "fef1a127077c71b9a48d42a9103fadce6b79eadd";
        LeadDyno.recordVisit();
        LeadDyno.autoWatch();
    </script>

    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-156350210-3"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-156350210-3');
    </script>
    <!-- Begin Inspectlet Asynchronous Code -->
    <script type="text/javascript">
        (function() {
            window.__insp = window.__insp || [];
            __insp.push(['wid', 1012972900]);
            var ldinsp = function(){
                if(typeof window.__inspld != "undefined") return; window.__inspld = 1; var insp = document.createElement('script'); insp.type = 'text/javascript'; insp.async = true; insp.id = "inspsync"; insp.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://cdn.inspectlet.com/inspectlet.js?wid=1012972900&r=' + Math.floor(new Date().getTime()/3600000); var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(insp, x); };
            setTimeout(ldinsp, 0);
        })();
    </script>
    <!-- End Inspectlet Asynchronous Code -->
    <script>
        var shareasaleSSCID=shareasaleGetParameterByName("sscid");function shareasaleSetCookie(e,a,r,s,t){if(e&&a){var o,n=s?"; path="+s:"",i=t?"; domain="+t:"",l="";r&&((o=new Date).setTime(o.getTime()+r),l="; expires="+o.toUTCString()),document.cookie=e+"="+a+l+n+i}}function shareasaleGetParameterByName(e,a){a||(a=window.location.href),e=e.replace(/[\[\]]/g,"\\$&");var r=new RegExp("[?&]"+e+"(=([^&#]*)|&|#|$)").exec(a);return r?r[2]?decodeURIComponent(r[2].replace(/\+/g," ")):"":null}shareasaleSSCID&&shareasaleSetCookie("shareasaleSSCID",shareasaleSSCID,94670778e4,"/");
    </script>
@endsection

@section('content')
<spark-register-stripe inline-template>
    <div class="layout-centered bg-img">
        <div class="spark-screen container ">
            <!-- Common Register Form Contents -->
            @include('spark::auth.register-common')

            <!-- Billing Information -->
            <div class="row justify-content-center" v-if="selectedPlan && selectedPlan.price > 0">
                <div class="col-lg-8">
                    <div class="card card-default">
                        <div class="card-header">{{__('Billing Information')}}</div>

                        <div class="card-body">
                            <!-- Generic 500 Level Error Message / Stripe Threw Exception -->
                            <div class="alert alert-danger" v-if="registerForm.errors.has('form')">
                                {{__('We had trouble validating your card. It\'s possible your card provider is preventing us from charging the card. Please contact your card provider or customer support.')}}
                            </div>

                            <form role="form">
                                <!-- Cardholder's Name -->
                                <div class="form-group row">
                                    <label for="name" class="col-md-4 col-form-label text-md-right">{{__('Cardholder\'s Name')}}</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="name" v-model="cardForm.name">
                                    </div>
                                </div>

                                <!-- Card Details -->
                                <div class="form-group row">
                                    <label for="name" class="col-md-4 col-form-label text-md-right">{{__('Card')}}</label>

                                    <div class="col-md-6">
                                        <div id="card-element"></div>
                                        <span class="invalid-feedback" v-show="cardForm.errors.has('card')">
                                            @{{ cardForm.errors.get('card') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Billing Address Fields -->
                                @if (Spark::collectsBillingAddress())
                                    @include('spark::auth.register-address')
                                @endif

                                <!-- ZIP Code -->
                                <div class="form-group row" v-if=" ! spark.collectsBillingAddress">
                                    <label class="col-md-4 col-form-label text-md-right">{{__('ZIP / Postal Code')}}</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="zip" v-model="registerForm.zip" :class="{'is-invalid': registerForm.errors.has('zip')}">

                                        <span class="invalid-feedback" v-show="registerForm.errors.has('zip')">
                                            @{{ registerForm.errors.get('zip') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Coupon Code -->
                                <div class="form-group row" v-if="query.coupon">
                                    <label class="col-md-4 col-form-label text-md-right">{{__('Coupon Code')}}</label>

                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="coupon" v-model="registerForm.coupon" :class="{'is-invalid': registerForm.errors.has('coupon')}">

                                        <span class="invalid-feedback" v-show="registerForm.errors.has('coupon')">
                                            @{{ registerForm.errors.get('coupon') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Terms And Conditions -->
                                <div class="form-group row">
                                    <div class="col-md-6 offset-md-4">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" v-model="registerForm.terms">
                                                {!! __('I Accept :linkOpen The Terms Of Service :linkClose', ['linkOpen' => '<a href="/terms" target="_blank">', 'linkClose' => '</a>']) !!}
                                            </label>
                                            <span class="invalid-feedback" v-show="registerForm.errors.has('terms')">
                                                <strong>@{{ registerForm.errors.get('terms') }}</strong>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tax / Price Information -->
                                <div class="form-group row" v-if="spark.collectsEuropeanVat && countryCollectsVat && selectedPlan">
                                    <label class="col-md-4 col-form-label text-md-right">&nbsp;</label>

                                    <div class="col-md-6">
                                        <div class="alert alert-info" style="margin: 0;">
                                            <strong>{{__('Tax')}}:</strong> @{{ taxAmount(selectedPlan) | currency }}
                                            <br><br>
                                            <strong>{{__('Total Price Including Tax')}}:</strong>
                                            @{{ priceWithTax(selectedPlan) | currency }}
                                            @{{ selectedPlan.type == 'user' && spark.chargesUsersPerSeat ? '/ '+ spark.seatName : '' }}
                                            @{{ selectedPlan.type == 'user' && spark.chargesUsersPerTeam ? '/ '+ __('teams.team') : '' }}
                                            @{{ selectedPlan.type == 'team' && spark.chargesTeamsPerSeat ? '/ '+ spark.teamSeatName : '' }}
                                            @{{ selectedPlan.type == 'team' && spark.chargesTeamsPerMember ? '/ '+ __('teams.member') : '' }}
                                            / @{{ __(selectedPlan.interval) | capitalize }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Register Button -->
                                <div class="form-group row mb-0">
                                    <div class="col-md-6 offset-md-4">
                                        <button type="submit" class="btn btn-primary" @click.prevent="register()" :disabled="registerForm.busy">
                                            <span v-if="registerForm.busy">
                                                <i class="fa fa-btn fa-spinner fa-spin"></i> {{__('Registering')}}
                                            </span>

                                            <span v-else>
                                                <i class="fa fa-btn fa-check-circle"></i> {{__('Register')}}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan Features Modal -->
        @include('spark::modals.plan-details')
    </div>
</spark-register-stripe>
@endsection
