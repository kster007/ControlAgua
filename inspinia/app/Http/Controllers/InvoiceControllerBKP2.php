<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\Invoice\InvoiceRequestStore;
use App\Http\Requests\Invoice\InvoiceRequestUpdate;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\InvoiceSetting;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Reading;
use App\Models\Rate;
use App\Models\Routine;
use App\Models\Charge;
use App\Models\Movement;
use App\Models\Payment;
use App\Models\PaymentDetail;
use Illuminate\Support\Facades\Crypt;
use DB;
use Auth;
use Carbon\Carbon;
use Session;
use PDF;


class InvoiceController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $company = Company::first();          
        
        $from = (new Carbon(Session::get('from')))->format('d/m/Y');
        $to = (new Carbon(Session::get('to')))->format('d/m/Y');

        $invoices = Invoice::whereDate('date', '>=', Session::get('from'))
                            ->whereDate('date', '<=' , Session::get('to'))
                            ->where('total', '>', 0)
                            ->orderBy('date');

        $invoices_count = $invoices->count();
        $invoices_total = $invoices->sum('total');

        $invoices = $invoices->paginate(10);
        
        return view('invoices.index')->with('invoices', $invoices)
                                    ->with('invoices_count', $invoices_count)
                                    ->with('invoices_total', $invoices_total)
                                    ->with('company', $company)
                                    ->with('from', $from)
                                    ->with('to', $to);                                    
    }

    public function change_period(Request $request){
                        
        $from = (new ToolController)->format_ymd($request->input('from'));
        $to = (new ToolController)->format_ymd($request->input('to'));

        Session::put('from', $from);
        Session::put('to', $to);
        return redirect()->route('invoices.index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function print_invoices()
    {
        $routines = Routine::orderBy('year', 'DES')
                            ->orderBy('month', 'DESC')->take(10)->get();
        return view('invoices.print')->with('routines', $routines);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_group()
    {
        $company = Company::first();                  
        $invoices_groups  = DB::table('invoices')
                                ->select('year_consume', 'month_consume', 'year', 'month', DB::raw('count(id) as tot_invoice'), DB::raw('sum(total) as tot_amount'))
                                ->groupby('month')
                                ->groupby('year')
                                ->orderby('year', 'DESC')
                                ->orderby('month', 'DESC')->paginate(10);
        
        return view('invoices.index_group')->with('invoices_groups', $invoices_groups)
                                    ->with('company', $company);     
    }

    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function routines()
    {        
        $routines_generated = Routine::orderBy('created_at', 'DESC')->get();
        $company = Company::first();          
        return view('invoices.routines')->with('routines_generated', $routines_generated)
                                    ->with('company', $company); 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function generate()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $invoice = new Invoice();
        $flat_rate = Rate::find(1);
        $iva = Charge::find(1);        
        $charges = Charge::where('id', '>', 1)->where('status', 'A')->get();
        $last_day_month = date("t/m/Y");
        return view('invoices.generate')->with('charges', $charges)
                                        ->with('flat_rate', $flat_rate)
                                        ->with('iva', $iva)
                                        ->with('last_day_month', $last_day_month)
                                        ->with('invoice', $invoice);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InvoiceRequestStore $request)
    {        
        $flat_rate = Rate::find(1);
        $iva = Charge::find(1);
        $amount_consume =0;
        $i=0;
        $start_invoice = 0;
        $end_invoice = 0;
        //Mes y Año de Consumo
        $month_consume = substr($request->input('date_consume'),0,2);
        $year_consume = substr($request->input('date_consume'),3,4);
        //Mes y Año de Facturación
        $month = substr($request->input('date'),3,2);
        $year = substr($request->input('date'),6,4);
        //Fecha y fecha limite
        $date = (new ToolController)->format_ymd($request->input('date'));
        $date_limit = (new ToolController)->format_ymd($request->input('date_limit'));
        $message = $request->input('message');        
        if(!$this->routine_exist($year, $month)){
            //Paso 1. Se eliminan todos los recibos pendientes de ese mes y año
            $invoices_pending = Invoice::where('year', $year)
                                ->where('month', $month)
                                ->where('status', 'P');

            $invoices_pending->delete();
            //Paso2. Se crea el registro de control, se hace de 1ro para evitar multiples submits
            $routine = new Routine();
            $routine->year = substr($request->input('date'),6,4);
            $routine->month = substr($request->input('date'),3,2);
            $routine->year_consume = $year_consume;
            $routine->month_consume = $month_consume;
            $routine->rate_type = $request->input('type');
            $routine->start = 0;
            $routine->end = 0;
            $routine->created_by = Auth::user()->name;
            $routine->save();            
            //Paso 3. Hacer el ciclo con todos los contratos ACTIVOS
            $contracts = Contract::where('status', 'A')->get();
            foreach($contracts as $contract){            
                //Crea el recibo si no existe uno previo CANCELADO para ese periodo.
                if(!$this->invoice_canceled_exist($contract->id, $year, $month)){
                    $i++;
                    $contract_initial_balance = $contract->balance;
                    //Paso 2. Registrar los datos generales del recibo
                    $invoice = new Invoice();
                    $invoice->date = $date
                    $invoice->date_limit = $date_limit;
                    $invoice->year = $year;
                    $invoice->month = $month;                    
                    $invoice->month_consume = $month_consume;
                    $invoice->year_consume = $year_consume;
                    $invoice->citizen_id = $contract->citizen->id;
                    $invoice->contract_id = $contract->id;
                    $invoice->message = $message;
                    $invoice->status = 'P';
                    $invoice->previous_debt = $invoice->contract->balance;
                    $invoice->save();
                    if($i == 1){
                        $start_invoice = $invoice->id;
                    }
                    //Paso 3. Registrar el detalle del recibo
            
                    //Paso 3.1 Incluir Tarifas
                    $invoice_detail = new InvoiceDetail();
                    $invoice_detail->invoice_id = $invoice->id;              
                    $invoice_detail->movement_type = 'CT';
                    $invoice_detail->type = 'M';             
                    $invoice_detail->description = 'Servicio de Agua '.$month.'/'.$year;            
                    if ($request->input('type')=='F'){
                        $invoice->rate = $flat_rate->amount;
                        $invoice->rate_description = $flat_rate->name;
                        $amount_consume =  $flat_rate->amount;
                        $invoice_detail->sub_total = $amount_consume;
                    }
                    elseif($request->input('type')=='C'){
                        //Si tiene lectura en ese mes calcula sino se coloca la tarifa fija
                        $reading = Reading::where('contract_id', $contract->id)
                                            ->where('year', $year)
                                            ->where('month', $year)->first();
                        if($reading){
                            $invoice->rate = $contract->rate->amount;
                            $invoice->rate_description = $contract->rate->name;
                            $invoice->reading_id = $reading->id;
                            $amount_consume = $reading->consume*$contract->rate->amount; 
                        }else{
                            $invoice->rate = $flat_rate->amount;
                            $invoice->rate_description = $flat_rate->name.' (Sin lectura)';
                            $amount_consume = $flat_rate->amount;                    
                        }
                        $invoice_detail->sub_total = $amount_consume;
                    }
                    $invoice_detail->save();
                    //Paso 3.2 Incluir cargos adicionales monto fijo
                    if($request->input('charges_m')){
                        foreach ($request->input('charges_m') as $charges_m) {
                            $charge = Charge::find($charges_m);
                            $invoice_detail = new InvoiceDetail();
                            $invoice_detail->invoice_id = $invoice->id;              
                            $invoice_detail->movement_type = $charge->movement_type;
                            $invoice_detail->type = 'M';
                            $invoice_detail->description = $charge->description;
                            $invoice_detail->sub_total = $charge->amount;
                            $invoice_detail->save();
                        }
                    }
                    //Paso 3.3 Incluir cargos adicionales porcentuales
                    if($request->input('charges_p')){
                        foreach ($request->input('charges_p') as $charges_p) {
                            $charge = Charge::find($charges_p);
                            $invoice_detail = new InvoiceDetail();
                            $invoice_detail->invoice_id = $invoice->id;              
                            $invoice_detail->movement_type = $charge->movement_type;
                            $invoice_detail->type = $charge->type;
                            $invoice_detail->description = $charge->description;
                            $invoice_detail->percent = $charge->percent;
                            $invoice_detail->sub_total = $amount_consume*($charge->percent/100);
                            $invoice_detail->save();
                        }
                    }
                    //Paso4. Calcular el Impuesto
                    if($request->input('apply_iva')=='Y'){
                            $invoice_detail = new InvoiceDetail();
                            $invoice_detail->invoice_id = $invoice->id;              
                            $invoice_detail->movement_type = $iva->movement_type;
                            $invoice_detail->type = $iva->type;
                            $invoice_detail->description = $iva->description;
                            $invoice_detail->percent = $iva->percent;
                            $invoice_detail->sub_total = $invoice->total_calculated()*($iva->percent/100);
                            $invoice_detail->save();
                    }
                    //Paso5. Actualizar el monto total del recibo en la tabla padre
                    $invoice->total = $invoice->total_calculated();
                    $invoice->save();
                    //Paso6. Registrar el cargo en la tabla movimientos
                    $movement = new Movement();
                    $movement->date = $invoice->date;                    
                    $movement->type = 'C';
                    $movement->movement_type = 'C';
                    $movement->description = 'Servicio de Agua '.$invoice->month.'/'.$invoice->year;
                    $movement->citizen_id = $contract->citizen->id;
                    $movement->contract_id = $contract->id;
                    $movement->invoice_id = $invoice->id;
                    $movement->amount = $invoice->total;
                    $movement->save();
                    //Paso7. Si el contrato tiene saldo a favor suficiente para cancelar el recibo
                    if($contract_initial_balance < 0 && abs($contract_initial_balance) >= $invoice->total){
                        //7.1 Se registra el pago
                        $payment = new Payment();
                        $payment->date= (new ToolController)->format_ymd($request->input('date'));
                        $payment->citizen_id = $contract->citizen->id;
                        $payment->contract_id = $contract->id;
                        $payment->type= 'PA';
                        $payment->description = 'Pago Automatico Servicio de Agua Mes '.$month.'/'.$year;
                        $payment->observation = 'Pago Automatico. Se debita de su saldo a favor.'.abs($contract_initial_balance).' - '.$invoice->total.' Saldo final: '.(abs($contract_initial_balance) - $invoice->total);
                        $payment->amount =$invoice->total;
                        $payment->debt = $contract_initial_balance + $invoice->total;
                        $payment->save();
                        //7.2 Se registra el detalle del pago
                        $payment_detail = new PaymentDetail();
                        $payment_detail->payment_id = $payment->id;
                        $payment_detail->type = 'C';
                        $payment_detail->amount = $invoice->total;
                        $payment_detail->description = 'Pago Automatico Servicio de Agua Mes '.$month.'/'.$year;
                        $payment_detail->save();
                        //7.3. Se registra el movimiento del pago
                        $movement = new Movement();
                        $movement->citizen_id = $contract->citizen->id;
                        $movement->contract_id = $contract->id;
                        $movement->movement_type = 'D';
                        $movement->type = 'P';
                        $movement->payment_id = $payment->id;
                        $movement->date = $payment->date;
                        $movement->amount =0;
                        $movement->description = 'Pago Automatico Servicio de Agua Mes '.$month.'/'.$year. ' Se debita de su saldo a favor.';
                        $movement->save();
                        //7.4 Se cambia el estado del recibo a cancelado y se asocia el pago
                        $invoice->payment_id = $payment->id;
                        $invoice->status = 'C';
                        $invoice->save();                    
                    }

                }//endif invoice exist
                $end_invoice = $invoice->id;
            }//foreach
                
            //Paso5. Actualizar el registro en la tabla control (routines)
            $routine->start = $start_invoice;
            $routine->end = $end_invoice;
            $routine->save();
        
            return redirect()->route('invoices.routines')->with('notity', 'create');;

        }else{
            return redirect()->route('invoices.create')->withErrors(array('global' => "Ya existe una generación de recibos para el período de Facturación <strong>(".month_letter($month, 'lg')." ".$year. ")</strong>. Primero debe reversar la generación anterior para poder realizar una nueva en ese período."));
        }
    }

    
    public function routine_exist($year, $month){
        $routine = Routine::where('year', $year)
                        ->where('month', $month)->first();

        ($routine)?return true: return false;
    }

    public function invoice_canceled_exist($contract_id, $year, $month){

        $invoice = Invoice::where('contract_id', $contract_id)
                            ->where('year', $year)
                            ->where('month', $month)->first();
        
        ($invoice)?return true: return false;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = Invoice::find(Crypt::decrypt($id));
        $company = Company::first();          
        
        return view('invoices.show')->with('invoice', $invoice)
                                        ->with('company', $company);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(InvoiceRequestUpdate $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reverse_routine($year, $month)
    {
        //Paso1. Se eliminan los recibos pendientes (no los cancelados)
        $invoices = Invoice::where('year', Crypt::decrypt($year))
                            ->where('month','=', Crypt::decrypt($month))
                            ->where('status','P');
        $invoices->delete();
        //Pas2. Se elimina el registro de la tabla control (routines)
        $routine = Routine::where('year', Crypt::decrypt($year))
                            ->where('month','=', Crypt::decrypt($month));
        $routine->delete();
        return redirect()->route('invoices.routines')->with('notity', 'delete');   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /**
        * Logica de eliminacion.
        */        
        $invoice = Invoice::find($id);
        $year = $invoice->year;
        $month = $invoice->month;
        
        if ($invoice->status == 'P'){            
            $invoice->delete();
            return redirect()->route('invoices.index', [Crypt::encrypt($year), Crypt::encrypt($month)])->with('notity', 'delete');        
        }else{            
        return redirect()->route('invoices.index', [Crypt::encrypt($year), Crypt::encrypt($month)])->withErrors('No se pueden elminar recibos que ya han sido cancelados.');
        }
    }

    /*
     * Download file from DB  
    */     
    public function report_period(Request $request)
    {
        $company = Company::first();
        $from = (new Carbon(Session::get('from')))->format('d/m/Y');
        $to = (new Carbon(Session::get('to')))->format('d/m/Y');
        
        $invoices = Invoice::whereDate('date', '>=', Session::get('from'))
                            ->whereDate('date', '<=' , Session::get('to'))
                            ->orderBy('date')->get();



        $data=[
            'company' => $company,
            'invoices' => $invoices,
            'logo' => 'data:image/png;base64, '.$company->logo 
        ];
        $pdf = PDF::loadView('reports/invoices_period', $data);
        return $pdf->download('Recibos desde '.$from.' hasta '.$to.'.pdf');

    }

}
