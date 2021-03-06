
@extends('layouts.blank_report')

@push('stylesheets')

@endpush

@section('content')

        <!-- Header -->
        <table class="table" width="100%">
            <tbody>
                <tr>
                    <td class="text-center">
                        <img alt="image" style="max-height:110px; max-width:110px;" src="{{ $logo }}"/>
                        <h3><strong>{{ $company->name }}</strong></h3>
                        <h2><strong>Pagos por Contrato</strong></h2>                    
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- /Header -->
        <br/>
        
        <h2>Contrato Nro <strong>{{ $contract->number }}</strong></h2>
        <h3>{{ $contract->citizen->name }}</h3><br/>

        <!-- Body -->
        <table class="table" width="100%">
            <thead>
                <tr>
                    <th class="text-center">Fecha</th>
                    <th class="text-left">Tipo</th>
                    <th class="text-left">Descripción</th>
                    <th class="text-right">Monto {{ Session::get('coin') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td class="text-center">{{ $payment->date->format('d/m/Y') }}</td>
                    <td class="text-left">{{ $payment->type_description }}</td>
                    <td class="text-left"><small>{{ $payment->description }}</small></td>
                    <td class="text-right">{{ money_fmt($payment->amount) }}</td>                
                </tr>
            @endforeach
            </tbody>
        </table>
        <br/>
        <br/>    
@endsection

