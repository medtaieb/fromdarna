{{-- Portails Partenaire / Livreur / Ops : @section('role', 'partner|courier|ops') --}}
@extends('layouts.base')

@section('bodyclass', 'app')

@section('body')
<div class="shell" id="shell">
    <button class="burger" type="button" aria-label="Menu" data-menu>☰</button>
    @include('partials.portal-nav', ['role' => trim($__env->yieldContent('role'))])
    <main class="main"><div class="stack">@yield('content')</div></main>
</div>
@endsection
