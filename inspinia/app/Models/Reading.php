<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    protected $table = 'readings';
    protected $dates = ['date'];    
        
    //*** Relations ***        
    public function inspector()
    {        
        return $this->belongsTo('App\Models\Inspector');
    }

    public function invoice()
    {        
        return $this->hasOne('App\Models\Invoice');
    }
    
    public function contract()
    {        
        return $this->belongsTo('App\Models\Contract');
    }

    //*** Methods ***
    public function exist($contract_id, $year, $month){
        $reading = Reading::where('contract_id', $contract_id)
                            ->where('year', $year)
                            ->where('month', $month);
        if($reading->count()>0){
            return true;
        }else{
            return false;
        }
    }
}
