<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sistema de Aguas | Login</title>

    <!-- Bootstrap -->
    <link href="{{ asset("css/bootstrap.min.css") }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{ asset("fonts/css/font-awesome.min.css") }}" rel="stylesheet">
    <!-- Animate -->
    <link href="{{ asset("css/animate.css") }}" rel="stylesheet">
    <!-- Custom Style -->
    <link href="{{ asset("css/style.css") }}" rel="stylesheet">

</head>

<style type="text/css">
  .img-rounded {
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
  }
</style>

<body class="gray-bg">

    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <div>
                <h1 class="logo-name">
                   <span><img style="max-height:160px; max-width:160px; border:2px solid white" class="img-rounded" src="{{ asset("img/logo_company.jpg") }}"/></span>
                </h1>
            </div>
            <br/>
            <h3>Bienvenido</h3>
            <p>Sistema de Gestión de Servicio de Agua.</p>

                    <!-- show erros -->
                    @if (count($errors) > 0)
                      <div class="alert alert-danger fade in">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <i class="fa fa-exclamation-triangle"></i> <strong>Disculpe!</strong>
                        <ul>
                          @foreach ($errors->all() as $error)
                            <li>{!! $error !!}</li>
                          @endforeach
                        </ul>
                      </div>
                    @endif
                    <!-- /show erros -->

            <form class="m-t" id="form" role="form" method="post" action="{{ url('/login') }}">
                {!! csrf_field() !!}
                <div class="form-group">
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Usuario" required="">
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Contraseña" required="">
                </div>
                <button type="submit" id="btn_submit" class="btn btn-primary block full-width m-b">Ingresar</button>

                <!--
                <a href="{{  url('/password/reset') }}"><small>Olvidaste tu contraseña?</small></a>
                <p class="text-muted text-center"><small>No tienes una cuenta?</small></p>
                <a class="btn btn-sm btn-white btn-block" href="{{  url('/register') }}">Crear una cuenta</a>
                -->

            </form>

            <p class="m-t">
                <small>Copyright &copy; 2017 Producto desarrollado por<br/>
                  <a href="http://cubetechnologies.com.mx"><strong><i class="fa fa-cube" aria-hidden="true"></i> Cube Technologies</strong></a>
                </small>
            </p>
        </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset("js/jquery-2.1.1.js") }}"></script>
    <!-- Bootstrap -->
    <script src="{{ asset("js/bootstrap.min.js") }}"></script>
    <!-- Jquery Validate -->
    <script src="{{ URL::asset('js/plugins/jquery-validation-1.16.0/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('js/plugins/jquery-validation-1.16.0/messages_es.js') }}"></script>
    <script>
        $(document).ready(function(){

            // Validation
            $("#form").validate({
                submitHandler: function(form) {
                    $("#btn_submit").attr("disabled",true);
                    form.submit();
                }
            });

        });
    </script>


</body>

</html>
