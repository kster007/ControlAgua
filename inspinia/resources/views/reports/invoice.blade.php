
@extends('layouts.blank_report')

@push('stylesheets')
<style>
    @page {
        margin-top: 1.0em;
        margin-right: 5.0em;
        margin-left: 5.0em;
    }
</style>
@endpush

@section('content')

        <!-- Header -->
        <table class="table" width="100%">
            <tbody>
                <tr>
                    <td class="text-right">
                        <h2><strong>{{ $company->name }}</strong></h2>
                        <small>{{ $company->company_phone }}, {{ $company->company_email }}</small>
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- /Header -->
        <!-- Body -->
        <table class="table" width="100%">
                <tbody>
                    <tr>
                        <td class="text-left">                                
                            Para: <strong>{{ $invoice->contract->citizen->name }}</strong><br/>
                            {{ $invoice->contract->citizen->neighborhood }}. {{ $invoice->contract->citizen->street }}.<br/> 
                            # Int {{ $invoice->contract->citizen->number_int }}/ # Ext {{ $invoice->contract->citizen->number_ext }}<br/>
                            {{ $invoice->contract->citizen->municipality->name }}, {{ $invoice->contract->citizen->state->name }}<br/> 
                        </td>
                        <td class="text-right">
                            <h2>RECIBO No. {{ $invoice->id }}</h2>
                            <strong>Fecha:</strong> {{ $invoice->date->format('d/m/Y') }}</span><br/>
                            <strong>Contrato: {{ $invoice->contract->number }}</strong><br/> 
                            Tarifa: {{ $invoice->rate_description}}<br/>
                        </td>
                    </tr>                    
                    <tr class="text-center">
                        <td class="well"><strong>DATOS DE FACTURACION</strong></td>
                        <td class="well"><strong>DETALLE DE FACTURACION MENSUAL</strong></td>
                    </tr>                                            
                    <tr>
                        <td>
                            <strong>Vencimiento:</strong> {{ $invoice->date_limit->format('d/m/Y') }}<br/>
                            <strong>Período de Consumo:</strong> {{ month_letter($invoice->month_consume, 'lg') }} {{ $invoice->year_consume }}
                        </td>
                        <td class="text-right">
                        <!-- Invoice Details -->
                            <table class="table" width="100%">
                                <thead>
                                    <tr>
                                        <th style="text-align:left">Descripción</th>
                                        <th align="right">Total {{ Session::get('coin') }}</th>
                                    </tr>
                                </thead>
                                <!-- foreach -->
                                <tbody>
                                    @php
                                        $tot=0;
                                        $details = $invoice->invoice_details()->where('movement_type', '!=', 'CI')->get();
                                        $iva = $invoice->invoice_details()->where('movement_type', 'CI')->first();
                                    @endphp
                                    @foreach($details as $detail)
                                        <tr>
                                            <td style="text-align:left"><small>{{ $detail->description }}</small></td>
                                            <td align="right">{{ money_fmt($detail->sub_total) }}</td>
                                            @php($tot=$tot+$detail->sub_total)
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table" style="border:none">
                                    @if($iva)                    
                                        <tr>
                                            <td align="right"><strong>Sub Total:</strong></td>
                                            <td align="right">{{ money_fmt($tot) }}</td>
                                        </tr>
                                        <tr>
                                            <td align="right"><strong>IVA ({{ $iva->percent }}%):</strong></td>
                                            <td align="right">{{ money_fmt($iva->sub_total) }}</td>
                                        </tr>                    
                                    @endif
                                    <tr>
                                        <td align="right"><strong>TOTAL MES:</strong></td>
                                        <td align="right"><strong>{{ money_fmt($invoice->total) }}</strong> {{ Session::get('coin') }}</td>
                                    </tr>                    
                                </tfoot>
                                <!-- /foreach -->
                            </table>
                        </div>    
                        <!-- Invoice Details -->
                        </td>
                    </tr>
                    <tr class="text-center">
                        <td class="well"><strong>HISTORIAL DE CONSUMO</strong></td>
                        <td class="well"><strong>INFORMACION DE CONSUMO</strong></td>
                    </tr>
                    <tr class="text-center">
                        <td></td>
                        <td class="text-right">                        
                            <strong>Saldo Anterior {{ Session::get('coin') }}:</strong> {{ money_fmt($invoice->previous_debt) }}<br>
                            <strong>Cargo Mensual {{ Session::get('coin') }}:</strong> {{ money_fmt($invoice->total_calculated()) }}<br/><br/>
                            <span style="font-size:11px"><strong>TOTAL A PAGAR {{ Session::get('coin') }}:</strong> {{ money_fmt($invoice->previous_debt + $invoice->total_calculated()) }}</span>
                        </td>
                    </tr>                                                                                       
                </tbody>
        </table>
        <!-- /Body -->
        <!-- Message -->        
        <div class="well"><strong>Mensaje al ciudadano:</strong><small> {{ $invoice->message }}</small>
        </div>
        <!-- /Message -->
@endsection

