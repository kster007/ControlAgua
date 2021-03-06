@extends('layouts.app')

@push('stylesheets')
<!-- Step -->
<link href="{{ URL::asset('css/plugins/steps/jquery.steps.css') }}" rel="stylesheet">
<!-- Select2 -->
<link href="{{ URL::asset('js/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('css/style.css') }}" rel="stylesheet">
<!-- iCheck -->
<link href="{{ URL::asset('css/plugins/iCheck/custom.css') }}" rel="stylesheet">
<!-- DatePicker -->
<link href="{{ URL::asset('css/plugins/datapicker/datepicker3.css') }}" rel="stylesheet">

@endpush

@section('page-header')
@endsection

@section('content')
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5><i class="fa fa-magic" aria-hidden="true"></i> Generar Recibo de Pago Individual</h5>
                            <div class="ibox-tools">
                                <a class="collapse-link">
                                    <i class="fa fa-chevron-up"></i>
                                </a>
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                    <i class="fa fa-wrench"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-user">
                                    <li><a href="#">Config option 1</a>
                                    </li>
                                    <li><a href="#">Config option 2</a>
                                    </li>
                                </ul>
                                <a class="close-link">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                        </div>
                
                <!-- ibox-content -->
                <div class="ibox-content">
                            
                        @include('partials.errors')

                            <p>Por favor siga las instrucciones paso a paso. <strong>(*) Campos obligatorios.</strong></p>

                            
                            {{ Form::open(array('url' => 'invoices.store_individual', 'id'=>'form'), ['class'=>'wizard-big'])}} 
                            {!! Form::hidden('apply_iva', 'Y', ['id'=>'apply_iva']) !!}  
                                <!-- Step1 -->
                                <h1>Contrato y Fechas</h1>
                                <fieldset>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h2 id="citizen_name"></h2>
                                        <div class="form-group">                            
                                        <label>Nro de Contrato *</label>
                                        {{ Form::select('contract', [], null, ['id'=>'contract', 'class'=>'select2_single form-control', 'tabindex'=>'-1', 'placeholder'=>'', 'required'])}}
                                        </div>
                                        <h2>Fechas del Recibo</h2>
                                            <div class="form-group" id="data_1">
                                                <label>Facturación *</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                    {{ Form::text ('date', null, ['id'=>'date', 'class'=>'form-control', 'type'=>'date', 'placeholder'=>'01/01/2017', 'required']) }}
                                                </div>
                                            </div>
                                            <div class="form-group" id="data_2">
                                                <label>Mes y Año de Consumo *</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                    {{ Form::text ('date_consume', null, ['class'=>'form-control', 'type'=>'date', 'placeholder'=>'Ej. 01/2021', 'required']) }}
                                                </div>
                                            </div>                                        
                                            <div class="form-group" id="data_3">
                                                <label>Vencimiento *</label>
                                                <div class="input-group date">
                                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                    {{ Form::text ('date_limit', null, ['class'=>'form-control', 'type'=>'date', 'placeholder'=>'01/01/2017', 'required']) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <p><strong>Facturación:</strong> Fecha a la que corresponde el recibo de pago. El sistema tomará el mes y año de dicha fecha como período de facturación.</p>
                                            <p><strong>Mes y Año de Consumo:</strong> Período de consumo al que corresponde el recibo. Este período se utilizará para buscar las lecturas del mismo período y efectuar el cálculo en caso de seleccionar la opción <i>Monto según Consumo</i>.</p>
                                            <p><strong>Vencimiento:</strong> Fecha limite de pago. El sistema colocará el ultimo día del mes de facturación. Usted lo puede modificar si lo desea.</p>
                                            <div class="text-center">
                                                <div style="margin-top: 20px">
                                                    <i class="fa fa-sign-in" style="font-size: 50px;color: #e5e5e5 "></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <!-- /Step1 -->
                                
                                <!-- Step2 -->
                                <h1>Tarifas</h1>
                                <fieldset>
                                    <h2>Tarifas a aplicar</h2>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <br/><label>Regla de aplicación</label><br/><br/>
                                                <div class="i-checks"><p> <input type="radio" name="type" value="F" checked> <i></i> Monto Fijo ({{ money_fmt($flat_rate->amount) }} {{ Session::get('coin') }})</p></div>
                                                <div class="i-checks"><p> <input type="radio" name="type" value="C"> <i></i> Monto según Consumo </p></div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <p><strong>Monto Fijo:</strong> El sistema aplicará un monto único (previamente definido) a todos los contratos activos independientemente del consumo que hayan tenido.</p>
                                            <p><strong>Monto según Consumo:</strong> El sistema aplicará un monto calculado en base al tipo de tarifa ({{ Session::get('coin') }}/m3) asignada al contrato y el consumo en m3 que haya tenido en el mes.<br/><br/>El sistema buscará las lecturas del período, en caso de que el contrato no tenga lectura registrada aplicará por defecto el consumo por <i>Monto Fijo</i>.</p>
                                            <div class="text-center">
                                                <div style="margin-top: 20px">
                                                    <i class="fa fa-sign-in" style="font-size: 50px;color: #e5e5e5 "></i>
                                                </div>
                                            </div>                                            
                                        </div>
                                    </div>
                                </fieldset>
                                <!-- /Step2 -->

                                <!-- Step3 -->
                                <h1>Cargos Adicionales</h1>
                                <fieldset>
                                <h2>Cargos Adicionales Comunes</h2>
                                <div class="row">
                                <div class="col-lg-6">                                
                                <!-- Colección de Cargos Adicionales -->                                 
                                @if($charges->count())
                                    <!-- Monto Fijo -->
                                    @if($charges->where('type','M')->count())
                                        <label>Monto Unico</label>                                   
                                        @foreach($charges->where('type','M') as $charge)
                                            <div class="i-checks">
                                                <p>{!! Form::checkbox('charges_m[]', $charge->id,  false, ['id'=>'charges_m']) !!} {{ $charge->description.' ( '.money_fmt($charge->amount).' '.Session::get('coin').' )' }}</p>
                                            </div>
                                        @endforeach
                                    @endif
                                    <!-- /Monto Fijo -->

                                    <!-- Porcentuales -->
                                    @if($charges->where('type','P')->count())
                                        <label>Porcentuales</label>                                   
                                        @foreach($charges->where('type','P') as $charge)
                                            <div class="i-checks">
                                                <p>{!! Form::checkbox('charges_p[]', $charge->id,  false, ['id'=>'charges_p']) !!} {{ $charge->description.' ( '.$charge->percent.'%)' }}</p>
                                            </div>
                                        @endforeach
                                    @endif
                                    <!-- /Porcentuales -->
                                @else
                                    <p>No existen cargos fijos definidos</p>
                                @endif
                                <!-- /Colección de Cargos Adicionales -->

                                <!-- IVA -->
                                <label>Impuesto</label>                                   
                                    <div class="i-checks">
                                        <p>{!! Form::checkbox('iva', $iva->id,  true, ['id'=>'iva', ($iva->status=='A'?'':'disabled')]) !!} {{ $iva->description.' ( '.$iva->percent.'%)' }}</p>
                                    </div>
                                <!-- /IVA -->
                                
    
                                </div>
                                <div class="col-lg-6">
                                    <p><strong>Cargos Adicionales Comunes:</strong> Son todos aquellos cargos adicionales que se cobrán al ciudadano, independiente del nivel de consumo. Por ejemplo: Gastos administrativos, Mantenimiento de Infraestructura, Potabilización, entre otros.</p><br/>
                                    @if($charges->where('type','M')->count())
                                        <p><strong>Monto Unico:</strong> Son todos aquellos cargos adicionales que se cobran a partir de un monto fijo para todos los contratos.</p><br/>
                                    @endif
                                    @if($charges->where('type','P')->count())
                                        <p><strong>Porcentuales:</strong> Son todos aquellos cargos adicionales que se cobran a partir de un porcentaje sobre el monto de consumo. Por Ejemplo si usted tuvo un consumo de 100 {{ Session::get('coin') }} y el porcentaje es del 5%. El monto del cargo es de 5 {{ Session::get('coin') }}.</p>
                                    @endif
                                    <div class="text-center">
                                        <div style="margin-top: 20px">
                                            <i class="fa fa-sign-in" style="font-size: 50px;color: #e5e5e5 "></i>
                                        </div>
                                    </div>                                    
                                </div>
                                </div>
                                </fieldset>
                                <!-- /Step3 -->

                                <!-- Step4 -->
                                <h1>Mensaje</h1>
                                <fieldset>
                                    <div class="col-lg-8">
                                        <h2>Mensaje de interés al ciudadano</h2>
                                        <div class="form-group">
                                            <label>Mensaje</label><small> Máx. 400 caracteres.</small>
                                            <div class="input-group m-b">
                                            <span class="input-group-addon"><i class="fa fa-align-justify" aria-hidden="true"></i></span>
                                            {!! Form::textarea('message', null, ['id'=>'message', 'rows'=>'3', 'class'=>'form-control', 'placeholder'=>'Escriba aqui algún mensaje (opcional)', 'maxlength'=>'400']) !!}
                                            </div>
                                        </div>                                    
                                        <input id="acceptTerms" name="acceptTerms" type="checkbox" class="required i-checks"> <label for="acceptTerms">Los datos suministrados están correctos para la generación de los recibos.</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <p><strong>Mensaje al ciudadano:</strong> Si lo desea, usted puede escribir un mensaje al ciudadano que se reflejará en el recibo de pago.</p>
                                    </div>
                                </fieldset>
                                <!-- /Step4 -->
                    </div>
                    <!-- ibox-content -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')   
<!-- Select2 -->
<script src="{{ URL::asset('js/plugins/select2/dist/js/select2.full.min.js') }}"></script>
<script src="{{ URL::asset('js/plugins/select2/dist/js/i18n/es.js') }}"></script>
<!-- Steps -->
<script src="{{ asset('js/plugins/staps/jquery.steps.js') }}"></script>
<!-- iCheck -->
<script src="{{ URL::asset('js/plugins/iCheck/icheck.min.js') }}"></script>
<!-- DatePicker --> 
<script src="{{ URL::asset('js/plugins/datapicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ URL::asset('js/plugins/datapicker/bootstrap-datepicker.es.min.js') }}"></script>


<!-- Page-Level Scripts -->
<script>
          

    $(document).ready(function() {
                                        
            //Step
            $("#form").steps({
                bodyTag: "fieldset",
                onStepChanging: function (event, currentIndex, newIndex)
                {
                    // Always allow going backward even if the current step contains invalid fields!
                    if (currentIndex > newIndex)
                    {
                        return true;
                    }

                    // Forbid suppressing "Warning" step if the user is to young
                    if (newIndex === 3 && Number($("#age").val()) < 18)
                    {
                        return false;
                    }

                    var form = $(this);

                    // Clean up if user went backward before
                    if (currentIndex < newIndex)
                    {
                        // To remove error styles
                        $(".body:eq(" + newIndex + ") label.error", form).remove();
                        $(".body:eq(" + newIndex + ") .error", form).removeClass("error");
                    }

                    // Disable validation on fields that are disabled or hidden.
                    form.validate().settings.ignore = ":disabled,:hidden";

                    // Start validation; Prevent going forward if false
                    return form.valid();
                },
                onStepChanged: function (event, currentIndex, priorIndex)
                {
                    // Suppress (skip) "Warning" step if the user is old enough.
                    if (currentIndex === 3)
                    {
                        $(this).steps("next");
                    }

                    // Suppress (skip) "Warning" step if the user is old enough and wants to the previous step.
                    if (currentIndex === 3 && priorIndex === 4)
                    {
                        $(this).steps("previous");
                    }
                },
                onFinishing: function (event, currentIndex)
                {
                    var form = $(this);

                    // Disable validation on fields that are disabled.
                    // At this point it's recommended to do an overall check (mean ignoring only disabled fields)
                    form.validate().settings.ignore = ":disabled";

                    // Start validation; Prevent form submission if false
                    return form.valid();
                },
                onFinished: function (event, currentIndex)
                {
                    var form = $(this);
                    // Submit form input
                    form.submit();
                }
            }).validate({
                        errorPlacement: function (error, element)
                        {
                            element.before(error);
                        },
                        rules: {
                            confirm: {
                                equalTo: "#password"
                            }
                        }
                    });
    
        //Datepicker fecha de facturacion
        var date_input_1=$('#data_1 .input-group.date');
        date_input_1.datepicker({
            format: 'dd/mm/yyyy',
            //endDate: '+1d',
            todayHighlight: true,
            autoclose: true,
            language: 'es',
        })
        if($('#data_1 .input-group.date').val() == ''){
          $('#data_1 .input-group.date').datepicker("setDate", new Date());                
        }


        var date_input_2=$('#data_2 .input-group.date');
        date_input_2.datepicker({
            format: 'mm/yyyy',
            endDate: 'm',
            viewMode: 'months', 
            minViewMode: 'months',
            todayHighlight: true,
            autoclose: true,
            language: 'es',
        
        })
    
        
        var date_input_3=$('#data_3 .input-group.date');
        date_input_3.datepicker({
            format: 'dd/mm/yyyy',
            todayHighlight: true,
            autoclose: true,
            language: 'es',
        })
        if($('#data_3 .input-group.date').val() == ''){
          $('#data_3 .input-group.date').datepicker("setDate", '{{ $last_day_month }}');                
        }


    $('#date').change(function(event){
      $('#data_3 .input-group.date').datepicker("setDate", getLastDateOfMonth($('#date').val().substring(6,10), $('#date').val().substring(3,5)));
    });
      

      function getLastDateOfMonth(Year,Month){
        var last_day_month = new Date((new Date(Year, Month,1))-1);
        var day = last_day_month.getDate();
        var month = last_day_month.getMonth()+1;
        if (month < 10){
          month='0'+month;
        }
        var year = last_day_month.getFullYear();
        return(day+'/'+month+'/'+year);
      }

        
    //Ajax para retornar los Contratos   
    $('#contract').select2({
        language: "es",
        placeholder: 'Seleccione un Nro de Contrato',
        width: '100%',
        ajax: {
          url: '/contracts-active-ajax',
          dataType: 'json',
          delay: 250,
            data: function(params) {
                    return {
                        term: params.term
                    }
                },
            processResults: function (data, page) {
                  return {
                    results: data
                  };
                },
            cache: true
        }
    });    

        //Metodo para completar inputs segun datos del ciudadano
        $("#contract").change( event => {
          url = `{{URL::to('get_citizen_by_contract_id/')}}/${event.target.value}`;                    
          $.get(url, function(response){
            $('#citizen_name').html(response.name);
          });
        });        

        // Select2 
        $("#rate").select2({
          language: "es",
          placeholder: "Seleccione una tarifa",
          minimumResultsForSearch: 2,
          allowClear: false,
          width: '100%'
        });
    
        // iCheck
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
    
        $('#iva').on('ifChecked', function(event){ 
            $('#apply_iva').val('Y');
        });       

        $('#iva').on('ifUnchecked', function(event){ 
            $('#apply_iva').val('N');
        });
    
  $('#btn_submit').on("click", function (e) { 
      $('#msj-before').hide();
      $('#msj-after').show();
      $('#btn_submit').prop('disabled', true);
      $('#form').submit();
  });
    

    //Notifications
    setTimeout(function() {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            showMethod: 'slideDown',
            timeOut: 2000
        };
        if('{{ Session::get('notity') }}'=='create' &&  '{{ Session::get('create_notification') }}'=='1'){
            toastr.success('Recibo generado exitosamente', '{{ Session::get('app_name') }}');
        }
    }, 1300);

});
    </script>

@endpush