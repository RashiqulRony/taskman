@extends('spark::layouts.app') @section("title") Home @endsection
@section('breadcrumb')
@endsection @section('content')

{{--    <projects></projects>--}}
<div id="project">
    <navbarcommon v-if="is_nav_" ></navbarcommon>
    <router-view></router-view>
</div>
@endsection
