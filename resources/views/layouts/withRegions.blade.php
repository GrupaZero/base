@extends('gzero-base::layouts.master')

@isset($blocks)
    @component('gzero-base::sections.sidebarLeft', ['blocks' => $blocks])
        @yield('sidebarLeft')
    @endcomponent
@endisset

@component('gzero-base::sections.content', ['class'=> isset($blocks) ? 'col-sm-4' : 'col-sm-12'])
    @yield('content')
@endcomponent

@isset($blocks)
    @component('gzero-base::sections.sidebarRight')
        @yield('sidebarRight')
    @endcomponent
@endisset
