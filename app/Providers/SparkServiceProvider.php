<?php

namespace App\Providers;

use Laravel\Spark\Spark;
use Laravel\Spark\Providers\AppServiceProvider as ServiceProvider;

class SparkServiceProvider extends ServiceProvider
{
    /**
     * Your application and company details.
     *
     * @var array
     */
    protected $details = [
        'vendor' => 'Compltit LLC',
        'product' => 'Compltit - Project Management',
        'street' => 'PO Box 111',
        'location' => 'Your Town, NY 12345',
        'phone' => '332-333-5055',
    ];

    /**
     * The address where customer support e-mails should be sent.
     *
     * @var string
     */
    protected $sendSupportEmailsTo = "cs@compltit.com";

    /**
     * All of the application developer e-mail addresses.
     *
     * @var array
     */
    protected $developers = [
        "eeyonw@gmail.com", "bnatter123@gmail.com"
    ];

    /**
     * Indicates if the application will expose an API.
     *
     * @var bool
     */
    protected $usesApi = true;

    /**
     * Finish configuring Spark for the application.
     *
     * @return void
     */
    public function booted()
    {
        Spark::noCardUpFront()->teamTrialDays(10);

        Spark::collectBillingAddress();

        Spark::freeTeamPlan()
            ->price(0)
            ->maxTeamMembers(1)
            ->features([
                'No Team Features', '1 Team Member', 'Unlimited Projects, Boards and Lists', '1GB Storage'
            ]);

        Spark::teamPlan('(2) Team Members', 'cit-team-2-users')
            ->price(10)
            ->maxTeamMembers(2)
            ->features([
                'All Features', '2 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(3) Team Members', 'cit-team-3-users')
            ->price(20)
            ->maxTeamMembers(3)
            ->features([
                'All Features', '3 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(4) Team Members', 'cit-team-4-users')
            ->price(30)
            ->maxTeamMembers(4)
            ->features([
                'All Features', '4 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(5) Team Members', 'cit-team-5-users')
            ->price(40)
            ->maxTeamMembers(5)
            ->features([
                'All Features', '5 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(6) Team Members', 'cit-team-6-users')
            ->price(50)
            ->maxTeamMembers(6)
            ->features([
                'All Features', '6 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(7) Team Members', 'cit-team-7-users')
            ->price(60)
            ->maxTeamMembers(7)
            ->features([
                'All Features', '7 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(8) Team Members', 'cit-team-8-users')
            ->price(70)
            ->maxTeamMembers(8)
            ->features([
                'All Features', '8 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(9) Team Members', 'cit-team-9-users')
            ->price(80)
            ->maxTeamMembers(9)
            ->features([
                'All Features', '9 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(10) Team Members', 'cit-team-10-users')
            ->price(90)
            ->maxTeamMembers(10)
            ->features([
                'All Features', '10 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(11) Team Members', 'cit-team-11-users')
            ->price(100)
            ->maxTeamMembers(11)
            ->features([
                'All Features', '11 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(12) Team Members', 'cit-team-12-users')
            ->price(110)
            ->maxTeamMembers(12)
            ->features([
                'All Features', '12 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(13) Team Members', 'cit-team-13-users')
            ->price(120)
            ->maxTeamMembers(13)
            ->features([
                'All Features', '13 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(14) Team Members', 'cit-team-14-users')
            ->price(130)
            ->maxTeamMembers(14)
            ->features([
                'All Features', '14 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(15) Team Members', 'cit-team-15-users')
            ->price(140)
            ->maxTeamMembers(15)
            ->features([
                'All Features', '15 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(16) Team Members', 'cit-team-16-users')
            ->price(150)
            ->maxTeamMembers(16)
            ->features([
                'All Features', '16 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(17) Team Members', 'cit-team-17-users')
            ->price(160)
            ->maxTeamMembers(17)
            ->features([
                'All Features', '17 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(18) Team Members', 'cit-team-18-users')
            ->price(170)
            ->maxTeamMembers(18)
            ->features([
                'All Features', '18 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(19) Team Members', 'cit-team-19-users')
            ->price(180)
            ->maxTeamMembers(19)
            ->features([
                'All Features', '19 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(20) Team Members', 'cit-team-20-users')
            ->price(190)
            ->maxTeamMembers(20)
            ->features([
                'All Features', '20 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(21) Team Members', 'cit-team-21-users')
            ->price(200)
            ->maxTeamMembers(21)
            ->features([
                'All Features', '21 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(22) Team Members', 'cit-team-22-users')
            ->price(210)
            ->maxTeamMembers(22)
            ->features([
                'All Features', '22 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(23) Team Members', 'cit-team-23-users')
            ->price(220)
            ->maxTeamMembers(23)
            ->features([
                'All Features', '23 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(24) Team Members', 'cit-team-24-users')
            ->price(230)
            ->maxTeamMembers(24)
            ->features([
                'All Features', '24 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(25) Team Members', 'cit-team-25-users')
            ->price(240)
            ->maxTeamMembers(25)
            ->features([
                'All Features', '25 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(26) Team Members', 'cit-team-26-users')
            ->price(250)
            ->maxTeamMembers(26)
            ->features([
                'All Features', '26 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(27) Team Members', 'cit-team-27-users')
            ->price(260)
            ->maxTeamMembers(27)
            ->features([
                'All Features', '27 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(28) Team Members', 'cit-team-28-users')
            ->price(270)
            ->maxTeamMembers(28)
            ->features([
                'All Features', '28 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(29) Team Members', 'cit-team-29-users')
            ->price(280)
            ->maxTeamMembers(29)
            ->features([
                'All Features', '29 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

        Spark::teamPlan('(30) Team Members', 'cit-team-30-users')
            ->price(290)
            ->maxTeamMembers(30)
            ->features([
                'All Features', '30 Team Members', 'Unlimited Projects, Boards and Lists', '5GB Storage per/User'
            ]);

    }
}
