<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Thank You</title>
    <script defer async type="text/javascript" src="https://shareasale-analytics.com/j.js"></script>
    <link href='https://fonts.googleapis.com/css?family=Lato:300,400|Montserrat:700' rel='stylesheet' type='text/css'>
    <style>
        @import url(//cdnjs.cloudflare.com/ajax/libs/normalize/3.0.1/normalize.min.css);
        @import url(//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css);
    </style>
    <link rel="stylesheet" href="https://2-22-4-dot-lead-pages.appspot.com/static/lp918/min/default_thank_you.css">
    <script src="https://2-22-4-dot-lead-pages.appspot.com/static/lp918/min/jquery-1.9.1.min.js"></script>
    <script src="https://2-22-4-dot-lead-pages.appspot.com/static/lp918/min/html5shiv.js"></script>

</head>
<body style="text-align: center">
@php
    $sscid = ! empty( $_COOKIE['shareasaleSSCID'] ) ? $_COOKIE['shareasaleSSCID'] : '';
@endphp
<img src="https://www.shareasale.com/sale.cfm?tracking={{auth()->user()->id}}&amount=0.00&merchantID=94518&transtype=lead&sscidmode=6&sscid={{$sscid}}" width="1" height="1">
<img src="https://spark.compltit.net/img/color-logo.png" alt="" class="img-responsive" style="max-width: 295px;margin-bottom: 15px;margin-top: 80px; margin-bottom: 50px;">
<header class="site-header" id="header" style="padding-top: 0px">
</header>

<div class="main-content">
    <i class="fa fa-check main-content__checkmark" id="checkmark"></i>
    <p class="main-content__body" data-lead-id="main-content-body">We are creating your account. This should only take a few seconds.</p>
</div>

<footer class="site-footer" id="footer">
    <p class="site-footer__fineprint" id="fineprint">Copyright ©2020 | All Rights Reserved</p>
</footer>

</body>
<script>
    setTimeout(function () {
        window.location.href = '/home'
    },5000)
</script>
</html>
