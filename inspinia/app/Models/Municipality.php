<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    protected $table = 'municipalities';
	
    public function state()
    {        
    	return $this->belongsTo('App\Models\State');
    }    
        
    public function citizens()
    {        
        return $this->hasMany('App\Models\Citizen');
    }
    
    public function contracts()
    {        
        return $this->hasMany('App\Models\Contract');
    }
    
    // *** Methods ***
    public static function states($id){
    	return Municipality::where('state_id', $id)->get();
    }	

}
