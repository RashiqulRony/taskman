<section class="sidebar">
    <div id="menu" role="navigation">
        <ul class="navigation" id="navigation">
            @if (Spark::canBillCustomers())
                <li class="menu-dropdown">
                    <a data-toggle="collapse" href="#collapseBilling">
                        <i class="menu-icon fa-list"></i>
                        <span>Billing</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul id="collapseBilling" class="collapse" data-parent="#navigation">
                    @if (Spark::hasPaidPlans())
                        <!-- Subscription Link -->
                        <li>
                            <a href="/settings#/subscription" aria-controls="subscription">
                                <i class="fa fa-fw fa-btn fa-shopping-bag"></i>Subscription
                            </a>
                        </li>
                    @endif
                    <!-- Payment Method Link -->
                        <li>
                            <a href="/settings#/payment-method" aria-controls="payment-method">
                                <i class="fa fa-fw fa-btn fa-credit-card"></i>Payment Method
                            </a>
                        </li>
                        <!-- Invoices Link -->
                        <li>
                            <a href="/settings#/invoices" aria-controls="invoices">
                                <i class="fa fa-fw fa-btn fa-history"></i>Invoices
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            <li class="menu-dropdown">
                <a data-toggle="collapse" href="#collapseProjects">
                    <i class="fa fa-fw fa-tasks"></i>
                    <span>Projects</span>
                    <span class="fa arrow"></span>
                </a>

                <ul id="collapseProjects" class="collapse" data-parent="#navigation"
                    style="overflow:auto;max-height: 200px;">
                </ul>
            </li>

            @if (Spark::developer(Auth::user()->email))
                <li class="menu-dropdown">
                    <a data-toggle="collapse" href="#collapseKiosk">
                        <i class="menu-icon fa fa-user"></i>
                        <span>kiosk</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul id="collapseKiosk" class="collapse" data-parent="#navigation">
                        <!-- Announcements Link -->
                        <li>
                            <a href="#">{{--/spark/kiosk#/announcements--}}
                                <i class="fa fa-fw fa-btn fa-bullhorn"></i> Announcements
                            </a>
                        </li>

                        <!-- Metrics Link -->
                        <li>
                            <a href="/spark/kiosk#/metrics">
                                <i class="fa fa-fw fa-btn fa-bar-chart"></i> Metrics
                            </a>
                        </li>
                        <!-- Users Link -->
                        <li>
                            <a href="/spark/kiosk#/users">
                                <i class="fa fa-fw fa-btn fa-user"></i> Users
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            @if (Spark::usesTeams())
                <li {!! (Request::is('settings/'.str_plural(Spark::teamsPrefix()).'/*') ? 'class="menu-dropdown active"' : 'class="menu-dropdown"') !!}>
                    <a data-toggle="collapse" href="#collapseTeam">
                        <i class="menu-icon fa fa-users"></i>
                        <span> {{ ucfirst(str_plural(Spark::teamsPrefix())) }} Settings</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul {!! (Request::is('settings/'.str_plural(Spark::teamsPrefix()).'/*') ? 'class="collapse show active"' : 'class="collapse"') !!} id="collapseTeam"
                        data-parent="#navigation">
                        <li v-for="team in teams"
                            :class="{active:team.id=={{Request::is('settings/'.str_plural(Spark::teamsPrefix()).'/*')?substr(Request::path(),strlen('settings/'.str_plural(Spark::teamsPrefix()).'/')):0}}}">
                            <a :href="'/settings/{{str_plural(Spark::teamsPrefix())}}/'+team.id">
                                <span v-if="user.current_team_id == team.id">
                                    <i class="fa fa-fw text-left fa-btn fa-check text-success"></i> @{{ team.name }}
                                </span>

                                <span v-else>
                                    <img :src="team.photo_url" class="spark-profile-photo-xs" alt="{{__('Team Photo')}}" />
                                    <i class="fa fa-btn"></i> @{{ team.name }}
                                </span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            <li class="menu-dropdown">
                <a data-toggle="collapse" href="#collapseAccounts">
                    <i class="menu-icon fa fa-cog"></i>
                    <span>Settings</span>
                    <span class="fa arrow"></span>
                </a>
                <ul id="collapseAccounts" class="collapse" data-parent="#navigation">
                    <!-- Profile Link -->
                    <li>
                        <a href="/profile">
                            <i class="menu-icon fa fa-user"></i> Profile
                        </a>
                    </li>
                    <li>
                        <a href="/notification">
                            <i class="menu-icon fa fa-bell-o"></i> Email & Notification
                        </a>
                    </li>
                    @if (Spark::usesTeams())
                        <li>
                            <a href="/settings#/{{str_plural(Spark::teamsPrefix())}}" aria-controls="teams">
                                <i class="fa fa-fw fa-btn fa-users"></i> {{ ucfirst(str_plural(Spark::teamsPrefix())) }}
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="/settings#/security">
                            <i class="fa fa-fw fa-btn fa-lock"></i> Update Password
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
        <!-- / .navigation -->
    </div>
    <!-- menu -->
</section>
