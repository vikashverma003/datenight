<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\SpecialEvent as SpecialEventResource;
use App\Http\Resources\SpecialEventCollection;

class SpecialEvent extends Model
{
    protected $fillable = [
        'business_id','user_id', 'name', 'date','start_time','end_time','description','valid_for_date','website_name','location_id'
    ];
    public function images()
    {
        return $this->hasMany('App\Models\SpecialEventImage','special_event_id');
    }
    
    public function createEvent($data){
      if(!empty($data['start_time'])){
        $start=$data['start_time'];
      }else{
        $start='';
      }

      if(!empty($data['end_time'])){
        $end=$data['end_time'];
      }else{
        $end='';
      }

        $createdSpecialEvent= self::create(
            [
                'business_id'  =>  $data['business_id'],
                'user_id'    =>$data['user_id'],
                'name'       =>  $data['name'],
                'date'  =>  $data['date'],
                'start_time'      =>  $start,
                'end_time'    => $end,
                'description'  =>$data['description']??null,
                'valid_for_date'  =>  $data['valid_for_date']??null,
                'website_name'=>$data['website_name']??null,
                'location_id'=>$data['location_id']??null,

            ]
        );
       return $this->event_resource($createdSpecialEvent);

    }
    public function business(){
    return  $this->belongsTo('App\Models\Business','business_id','id');
    }
    public function event_resource($event){
        return new SpecialEventResource($event);
    }
    public function event_collection($events){
        return  new SpecialEventCollection($events);
    }
    public function event_resource_collection($event){
        return  SpecialEventResource::Collection($event);
    }

    public function deleteEvent($event_id){
        return self::where('id',$event_id)->delete();
     }
     public function updateEvent($event_id,$data){
        self::where('id',$event_id)->update(
              [
               // 'business_id'  =>  $data['business_id'],
              //  'user_id'    =>$data['user_id'],
                'name' =>  $data['name'],
                'date'  =>  $data['date'],
                'start_time'      =>  $data['start_time'],
                'end_time'    => $data['end_time'],
                'description'  =>$data['description']??null,
                'valid_for_date'  =>  $data['valid_for_date']??null,
                'website_name'=>$data['website_name']??null,
                'location_id'=>$data['location_id']??null,
  
              ]
          );
          $createdBusinessEvent=self::where('id',$event_id)->first();
         return $this->event_resource($createdBusinessEvent);
      }

      public function getEvent(){
        $user=\Auth::user();
        $event= self::where('user_id',$user->id)->with(['images'])->get();
         return $this->event_resource_collection($event);
     }

      public function location()
       {
           return $this->belongsTo('App\Models\Location','location_id');
       }

     public function getFiveAdvertisment(){
     
       $avertiseEvent =AdvertiseEventSlotPuruchase::where('end','>',date('Y-m-d H:i:s'))->pluck('user_id')->toArray();
       //dd( self::whereIn('user_id',$avertiseEvent)->with(['images','business','location'])->get());

       
       return $this->event_resource_collection(self::whereIn('user_id',$avertiseEvent)->with(['images','business','location'])->get());
    }
}
