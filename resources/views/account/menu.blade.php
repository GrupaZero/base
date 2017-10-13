@component('sections.sidebarLeft', ['class' => 'col-sm-3'])
<h3>{{ $user->displayName() }}</h3>
<ul class="nav flex-column" role="navigation">
    <li class="nav-item">
        <a href="{{route('account')}}" class="nav-link">@lang('user.my_account')</a>
    </li>
    <li class="nav-item">
        <a href="{{route('account.oauth')}}" class="nav-link">@lang('user.oauth')</a>
    </li>
    <li class="nav-item">
        <a href="{{route('logout')}}" class="nav-link">@lang('common.logout')</a>
    </li>
</ul>
@endcomponent
