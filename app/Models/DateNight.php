<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
class DateNight extends Model
{
    use \App\Traits\CommonUtil;
    protected $fillable = [
        'user_id', 'name', 'date','start_time','location','custom_business_count'
    ];
    protected $appends=['is_perform_action'];
    public function businesses()
    {
        return $this->belongsToMany('App\Models\Business','date_night_business','date_night_id','business_id')->withTimestamps();
    }

    public function contacts(){
        return $this->hasMany('App\Models\DateNightContact','date_night_id','id');
    }
    public function user(){
        return $this->belongsTo('App\User','user_id','id');
    }
    public function locationd()
    {
        return $this->belongsTo('App\Models\Location','location');
    }

    function getIsPerformActionAttribute(){
        $user=Auth::user();    
          $contact=DateNightContact::where('date_night_id',$this->id)->where(function($q) use($user){
            $q->where('contact_no',$user->phone_code.''.$user->phone_number)->orWhere('contact_no',$user->phone_number);
        })->first();
          if(!is_null($contact)){
            return $contact->status>0?1:0;
          }
        return  0;
    }
    public function scopePrevious($query)
    {
        $user=Auth::user();  
        $timeZone=$user->timezone??'Asia/Kolkata';
        $datetime=$this->getCurrentTime($timeZone);
        $currentDate=$datetime->format('Y-m-d');
        $currentTime=$datetime->format('H:i');
        return $query->where('date', '<', $currentDate) ;
    }
    public function scopePresentFuture($query)
    {
        $user=Auth::user();  
        $timeZone=$user->timezone??'Asia/Kolkata';
        $datetime=$this->getCurrentTime($timeZone);
        $currentDate=$datetime->format('Y-m-d');
        $currentTime=$datetime->format('H:i');
        return $query->where('date', '>', $currentDate)->orWhere(function($q) use( $currentDate, $currentTime){
            $q->where('date',$currentDate)->where('start_time','>=',$currentTime);
        });
    }

    public function likebusinesses()
    {
        return $this->belongsToMany('App\Models\Business','date_night_owner_businesses','date_night_id','business_id')->withTimestamps();
    }
  
}
