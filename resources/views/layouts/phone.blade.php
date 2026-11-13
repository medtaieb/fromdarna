@extends('layouts.base')
@section('body', 'phone-body')
@section('content')
    <div class="phone">
        <div class="phone-scroll reveal">@yield('screen')</div>
        @php($tab = trim($__env->yieldContent('tab')))
        @if ($tab)
            @include('partials.tabbar', ['active' => $tab])
        @endif
    </div>
@endsection
