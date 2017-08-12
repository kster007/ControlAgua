<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\Contract\ContractRequestStore;
use App\Http\Requests\Contract\ContractRequestUpdate;
use App\Models\Contract;
use App\Models\Citizen;
use App\Models\Company;
use App\Models\Rate;
use App\Models\State;
use App\Models\Administration;
use Illuminate\Support\Facades\Crypt;


class ContractController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Company::first();                  
        $contracts = Contract::all();  
        return view('contracts.index')->with('contracts', $contracts)
                                    ->with('company', $company);  
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function citizen_contracts($citizen_id)
    {
        $company = Company::first();                  
        $citizen = Citizen::find(Crypt::decrypt($citizen_id));
        $contracts = $citizen->contracts()->get();  
        return view('contracts.citizen_contracts')->with('company', $company)
                                            ->with('citizen', $citizen)
                                            ->with('contracts', $contracts);  
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($citizen_id)
    {
        $contract = new Contract();
        $citizen = Citizen::find(Crypt::decrypt($citizen_id));        
        $rates = Rate::orderBy('name')->lists('name','id');
        $administrations = Administration::orderBy('period')->lists('period','id');
        $states = State::orderBy('name')->lists('name','id');
        return view('contracts.save')->with('contract', $contract)
                                    ->with('citizen', $citizen)
                                    ->with('rates', $rates)
                                    ->with('administrations', $administrations)
                                    ->with('states', $states);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ContractRequestStore $request)
    {
        $contract = new Contract();
        $contract->citizen_id= $request->input('hdd_citizen_id');
        $contract->number= $request->input('number');
        $contract->date= (new ToolController)->format_ymd($request->input('date'));        
        $contract->rate_id= $request->input('rate'); 
        $contract->administration_id= $request->input('administration');        
        $contract->state_id= $request->input('state');
        $contract->municipality_id= $request->input('municipality');
        $contract->street= $request->input('street');
        $contract->neighborhood= $request->input('neighborhood');
        $contract->number_ext= $request->input('number_ext');
        $contract->number_int= $request->input('number_int');
        $contract->postal_code= $request->input('postal_code');
        $contract->save();
        return redirect()->route('contracts.citizen_contracts', Crypt::encrypt($contract->citizen_id))->with('notity', 'create');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $contract = Contract::find(Crypt::decrypt($id));
        $citizen = Citizen::find($contract->citizen_id);        
        $rates = Rate::orderBy('name')->lists('name','id');
        $administrations = Administration::orderBy('period')->lists('period','id');
        $states = State::orderBy('name')->lists('name','id');
        return view('contracts.save')->with('contract', $contract)
                                    ->with('citizen', $citizen)
                                    ->with('rates', $rates)
                                    ->with('administrations', $administrations)
                                    ->with('states', $states);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ContractRequestUpdate $request, $id)
    {
        $contract = Contract::find($id);        
        $contract->number= $request->input('number');
        $contract->date= (new ToolController)->format_ymd($request->input('date'));        
        $contract->rate_id= $request->input('rate'); 
        $contract->administration_id= $request->input('administration');        
        $contract->state_id= $request->input('state');
        $contract->municipality_id= $request->input('municipality');
        $contract->street= $request->input('street');
        $contract->neighborhood= $request->input('neighborhood');
        $contract->number_ext= $request->input('number_ext');
        $contract->number_int= $request->input('number_int');
        $contract->postal_code= $request->input('postal_code');
        $contract->save();
        return redirect()->route('contracts.citizen_contracts', Crypt::encrypt($contract->citizen_id))->with('notity', 'update');
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
        * Logica de eliminacion para no generar inconsistencia.
        * Se chequea si hay condominios asociados con el pais
        */        
        $contract = Contract::find($id);
        if (true){
        //if ($contract->invoices->count() == 0){            
            $contract->delete();
            return redirect()->route('contracts.citizen_contracts', Crypt::encrypt($contract->citizen_id))->with('notity', 'delete');        
        }else{            
            return redirect()->route('contracts.citizen_contracts', Crypt::encrypt($contract->citizen_id))->withErrors('No se puede eliminar el Contrato. Existen <strong>'.$contract->invoices->count().'</strong> recibos asociados. Debe primero eliminar los recibos asociados. Gracias...');            
        }
    }

}
