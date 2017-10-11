<!DOCTYPE html>
<html lang="{{ isset($lang) ? $lang->code : app()->getLocale() }}">
<head>
    @include('gzero-base::includes.head')
</head>
<body class="@yield('bodyClass')">
<div id="root" class="page">
    <div class="wrapper">
        <header>
            {{--@include('gzero-base::includes.navbar')--}}
        </header>
        @yield('breadcrumbs')
        <div id="main-container" class="container">
            <div class="row">
                <div id="content" class="col-sm-12 mh-column">
                    @yield('content')
                </div>
                <!-- end #content -->
            </div>
        </div>
        <!-- end #main-container -->
    </div>
    <footer id="footer" class="clearfix">
        @include('gzero-base::includes.footer')
    </footer>
</div>
<!-- end #root -->
@stack('footerScripts')
</body>
</html>
