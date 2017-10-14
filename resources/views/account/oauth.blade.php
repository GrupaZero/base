@extends('gzero-base::layouts.master')

@section('title')
    @lang('common.account')
@stop

@component('gzero-base::account.menu')@endcomponent

@component('gzero-base::sections.content', ['class' => 'col-sm-8'])
    <h1 class="page-header">@lang('user.oauth')</h1>

    <passport-clients></passport-clients>
    <passport-authorized-clients></passport-authorized-clients>
    <passport-personal-access-tokens></passport-personal-access-tokens>
@endcomponent
