<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DateNightContact extends Model
{
    protected $fillable = [
        'date_night_id', 'contact_no','status','name'
    ];

    public function addContact($data){
        return self::create([
            'date_night_id'=>$data['date_night_id'],
            'contact_no'  => $data['contact_no'],
            'status'      => config('constants.datenight_action.PENDING'),
            'name'        => $data['name']
        ]);
    }
    public function businesses()
    {
        return $this->belongsToMany('App\Models\Business','date_night_contact_businesses','date_night_contact_id','business_id')->withTimestamps();
    }
}
