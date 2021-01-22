<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertiseEventSlotPuruchase extends Model
{
    protected $fillable=['user_id','business_id','start','end','is_expired'];

    public function createAdvertise($data){
        return self::create([
            'user_id'=>$data['user_id'],
            'business_id'=>$data['business_id'],
            'start'=>$data['start'],
            'end'   =>$data['end'],
            'is_expired'=>0
        ]);
    }

    public function isPurchased($user_id){
        return self::where('user_id',$user_id)->where('end','>',date('Y-m-d H:i:s'))->count();
    }
    public function purchasedPlanDetail($user_id){
        return self::where('user_id',$user_id)->where('end','>',date('Y-m-d H:i:s'))->first();
    }
}
