<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Sistema de Aguas</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{ asset('fonts/css/font-awesome.min.css') }}" rel="stylesheet">
    <!-- Animate -->
    <link href="{{ asset('css/animate.css') }}" rel="stylesheet">
    <!-- Notifications -->
    <link href="{{ URL::asset('css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">
    <!-- Custom Style -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <!-- Custom Validation -->
    <link href="{{ URL::asset('/js/plugins/jquery-validation-1.16.0/jquery.validate.css') }}" media="all" rel="stylesheet">
    <script src="{{ asset('js/plugins/toastr/toastr.min.js') }}"></script>

    @stack('stylesheets')

</head>

<body>
    <div id="wrapper">

        <!-- sidebar  -->
        @include('partials.sidebar')

        <div id="page-wrapper" class="gray-bg dashbard-1">

            <!-- topbar -->
            @include('partials.topbar')

            <!-- Page Header -->
            @yield('page-header')

            <div class="row">
                <div class="col-lg-12">
                    <!-- dashboard -->
                    @yield('content')
                </div>

                <!-- footer -->
                @include('partials.footer')
            </div>
        </div>

    </div>

    <!-- Mainly scripts -->
    <script src="{{ asset('js/jquery-2.1.1.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
    <script src="{{ asset('js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- Notifications -->
    <script src="{{ asset('js/plugins/toastr/toastr.min.js') }}"></script>
    <!-- Custom and plugin javascript -->
    <script src="{{ asset('js/inspinia.js') }}"></script>

    <!-- Jquery Validate -->
    <script src="{{ URL::asset('js/plugins/jquery-validation-1.16.0/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('js/plugins/jquery-validation-1.16.0/messages_es.js') }}"></script>

    @stack('scripts')
</body>
</html>
