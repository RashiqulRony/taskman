<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Information -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400,600' rel='stylesheet' type='text/css'>
    <script src="https://kit.fontawesome.com/1a9af935ca.js" crossorigin="anonymous"></script>
<!-- CSS -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="{{asset('css/light_custom.css')}}" rel="stylesheet">
    <link href="{{asset('css/tree_view.css')}}" rel="stylesheet">
    <link href="{{asset('css/rules.css')}}" rel="stylesheet">
    <link href="{{asset('css/_OverView.css')}}" rel="stylesheet">
    <link href="{{asset('css/board_view.css')}}" rel="stylesheet">
    <link href="{{asset('css/loder.css')}}" rel="stylesheet">
    <link href="{{asset('css/profile.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/all.css')}}">
    <link rel="stylesheet" href="{{asset('css/formelements.css')}}">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.2.0/socket.io.slim.js"></script>

    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-156350210-3"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'UA-156350210-3');
    </script>

{{--yield styles--}}
@yield('styles','')
<!-- Scripts -->
@yield('scripts', '')

<!-- Global Spark Object -->
    <script type="text/javascript" src="https://static.leaddyno.com/js"></script>
    <script>
        LeadDyno.key = "fef1a127077c71b9a48d42a9103fadce6b79eadd";
        LeadDyno.recordVisit();
        LeadDyno.autoWatch();
    </script>

    <script>
        window.Spark = <?php echo json_encode(array_merge(
            Spark::scriptVariables(), []
        )); ?>;
        window.socket_url = <?php echo json_encode(config('app.socket_url')); ?>;

    </script>
</head>
<body class="skin-default" style="-webkit-print-color-adjust:exact;">
<div id="spark-app" v-cloak>
    <!-- Navigation -->

@if (Auth::check())
    @include('spark::nav.user')
@else
    @include('spark::nav.guest')
@endif

<!-- Main Content -->
    <!-- Main Content -->
    <div class="wrapper row-offcanvas row-offcanvas-left">
        @if (Auth::check())
            <aside class="left-side sidebar-offcanvas left-hide">
                <!-- sidebar: style can be found in sidebar-->
            @include('spark::layouts.leftmenu')
            <!-- /.sidebar -->
            </aside>
            <aside class="right-side right-align">
                <!-- Content Header (Page header) -->
                {{--                    <section class="content-header">--}}
                <section>
                    @yield('breadcrumb')
                </section>
                <!-- Main content -->
                <!-- Main Content -->
                <main class="">
                    @yield('content')
                </main>
                <!-- /.content -->
            </aside>
        @else
            <section class="content m-t-75">
                @yield('content')
            </section>
        @endif
    </div>


    <!-- Application Level Modals -->
    @if (Auth::check())
        @include('spark::modals.notifications')
        @include('spark::modals.support')
        @include('spark::modals.session-expired')
    @endif
</div>

<!-- JavaScript -->
<script src="{{ mix('js/app.js') }}"></script>
<script src="{{asset('js/sweetalert.min.js')}}"></script>
<script src="{{asset('js/icheck.js')}}"></script>
<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
<!-- Scripts -->
@yield('custom_scripts', '')
<script>
    var team_id = null;
    $(document).ready(function () {
        $(".navbar .fa-bars").on("click",function () {
            $(".left-side.sidebar-offcanvas").toggleClass("left-hide");
            $(".right-side").toggleClass("right-align")
        });

        $("#menu .navigation a").click(function()
        {
            $("#menu .navigation li.active").removeClass("active");
            $(this).parent().addClass("active");
            $(this).parent().parent(".collapse.show").parent().addClass("active");
        });
        showProjectDashboard();
    });

    function showProjectDashboard() {
        $.ajax({
            type:'GET',
            url:'{{route('all-project')}}',
            data:
                {
                    render : 'project',
                    _token: "{{ csrf_token() }}"
                },
            success: function(data) {
                $('#collapseProjects').html(data);
            }
        });
    }

    <!-- Begin Inspectlet Asynchronous Code -->
    (function() {
        window.__insp = window.__insp || [];
        __insp.push(['wid', 1012972900]);
        var ldinsp = function(){
            if(typeof window.__inspld != "undefined") return; window.__inspld = 1; var insp = document.createElement('script'); insp.type = 'text/javascript'; insp.async = true; insp.id = "inspsync"; insp.src = ('https:' == document.location.protocol ? 'https' : 'http') + '://cdn.inspectlet.com/inspectlet.js?wid=1012972900&r=' + Math.floor(new Date().getTime()/3600000); var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(insp, x); };
        setTimeout(ldinsp, 0);
    })();

    <!-- End Inspectlet Asynchronous Code -->
    window.intercomSettings = {
        app_id: 'ogbm4b5j',
        @guest
        name: 'Guest',
        email: '',
        @endguest
            @auth
        name: '{{ auth()->user()->name}}',
        email: '{{ auth()->user()->email }}',
        created_at: '{{ strtotime(auth()->user()->created_at) }}'
        @endauth
    };

    (function () {
        var w = window;
        var ic = w.Intercom;
        if (typeof ic === "function") {
            ic('reattach_activator');
            ic('update', w.intercomSettings);
        } else {
            var d = document;
            var i = function () {
                i.c(arguments);
            };
            i.q = [];
            i.c = function (args) {
                i.q.push(args);
            };
            w.Intercom = i;
            var l = function () {
                var s = d.createElement('script');
                s.type = 'text/javascript';
                s.async = true;
                s.src = 'https://widget.intercom.io/widget/ogbm4b5j';
                var x = d.getElementsByTagName('script')[0];
                x.parentNode.insertBefore(s, x);
            };
            if (w.attachEvent) {
                w.attachEvent('onload', l);
            } else {
                w.addEventListener('load', l, false);
            }
        }
    });
    setTimeout(() => {
        $(".alert").hide();
    }, 1000);
</script>

</body>
</html>
