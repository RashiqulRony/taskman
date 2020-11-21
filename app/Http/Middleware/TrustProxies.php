<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Fideloper\Proxy\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string
     */
    //protected $proxies;
    protected $proxies = [
        '10.0.0.17', //production elb
        '10.0.0.118', //producution elb
        '10.0.0.42', //staging elb
        '10.0.0.74', //staging elb
        '10.0.1.119', //production app LB
        '10.0.0.133' //production app LB
    ];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
