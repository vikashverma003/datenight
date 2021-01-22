<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\BusinessEvent as BusinessEventResource;
use App\Http\Resources\BusinessEventCollection;

class BusinessEvent extends Model
{
    use \App\Traits\CommonUtil;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_id','event_name', 'event_type', 'date','start_time','end_time','description','is_regular_basis','recurring_type','month_day','week_day_name'
    ];
    
    public function assets()
    {
        return $this->hasMany('App\Models\BusinessEventAsset','event_id');
    }
    
    public function createEvent($data){
        $createdBusinessEvent= self::create(
            [
                'business_id'  =>  $data['business_id'],
                'event_name'    =>$data['event_name'],
                'event_type' =>  $data['event_type'],
                'date'  =>  $data['date'],
                'start_time'      =>  $data['start_time'],
                'end_time'    => $data['end_time'],
                'description'  =>$data['description'],
                'is_regular_basis'  =>  $data['is_regular_basis'],
                'recurring_type'=>$data['recurring_type']??null,
                'month_day'=>$data['month_day']??null,
                'week_day_name'=>$data['week_day']??null,

            ]
        );
       return $this->event_resource($createdBusinessEvent);

    }

    public function event_resource($event){
        return new BusinessEventResource($event);
    }
    public function event_collection($events){
        return  new BusinessEventCollection($events);
    }
    public function event_resource_collection($event){
        return  BusinessEventResource::Collection($event);
    }

    public function getEventByBusinessId($id,$status=null,$timezone='Asia/Kolkata'){
       $businessEvent= self::where('business_id',$id);
        $datetime=$this->getCurrentTime($timezone);
        $currentDate=$datetime->format('Y-m-d');
        $currentTime=$datetime->format('H:i');
       switch($status){
        case config("constants.business_event_type.ONGOING"):
            $businessEvent->where(function ($q) use($currentDate,$currentTime,$id){
                $q->where('date',$currentDate)->where('start_time','<',$currentTime)->where('business_id',$id);
            })->orWhere(function ($q) use($id){
                $q->where('is_regular_basis',1)->where('business_id',$id);
            })->where('is_delete',0);
            break;

        case config("constants.business_event_type.UPCOMMING"):
            $businessEvent->where(function($q) use($currentDate,$currentTime){
                $q->orWhere(function($qq) use($currentDate,$currentTime){
                    $qq->where('date',$currentDate)->where('start_time','>',$currentTime);
                })->orWhere('date','>',$currentDate);
               })->orWhere('is_delete',1);
            break;
        
        case config("constants.business_event_type.PAST"):
            $businessEvent->where('date','<',$currentDate)->where('is_delete',0);
            break;

        default:   

       }
       
       $businessEvent= $businessEvent->with(['assets'])->get();
       return $businessEvent;
    }

    public function updateEvent($event_id,$data){
      self::where('id',$event_id)->update(
            [
                'business_id'  =>  $data['business_id'],
                'event_name'    =>$data['event_name'],
                'event_type' =>  $data['event_type'],
                'date'  =>  $data['date'],
                'start_time'      =>  $data['start_time'],
                'end_time'    => $data['end_time'],
                'description'  =>$data['description'],
                'is_regular_basis'  =>  $data['is_regular_basis'],
                'recurring_type'=>$data['recurring_type']??null,
                'month_day'=>$data['month_day']??null,
                'week_day_name'=>$data['week_day']??null,

            ]
        );
        $createdBusinessEvent=self::where('id',$event_id)->first();
       return $this->event_resource($createdBusinessEvent);
    }

    public function deleteEvent($event_id){
       return self::where('id',$event_id)->delete();
    }

    public function getEventById($id){
       $event= self::where('id',$id)->with(['assets'])->first();
        return $this->event_resource($event);
    }

    public function deleteEventForNow($id){
        $enddate = strtotime("+7 day");
        self::where('id',$id)->update([
            'is_delete'=>1,
            'delete_till_date'=>date('Y-m-d H:i:s', $enddate)]);
    }
}
