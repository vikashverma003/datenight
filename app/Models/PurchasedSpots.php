<?php

namespace App\Models;
use App\Http\Resources\PurchasedSpots as PurchasedSpotsResource;

use Illuminate\Database\Eloquent\Model;

class PurchasedSpots extends Model
{
    protected $fillable=['user_id','plan_spot_id','start','end','is_expired'];


    public function createPurchasedPlan($data){
        return self::create([
            'user_id'=>$data['user_id'],
            'plan_spot_id'=>$data['plan_spot_id'],
            'start' =>$data['start'],
            'end'   =>$data['end'],
            'is_expired'=>0
        ]);
    }

    public function userPurchasedSlot($user_id){
      $plans=self::where('user_id',$user_id)->orderBy('end')->get();
      return  $this->purchased_spots_collection($plans);
    }

    public function purchased_spots_resource($plan){
        return new PurchasedSpotsResource($plan);
    }

    public function purchased_spots_collection($plans){
        return PurchasedSpotsResource::Collection($plans);
    }

    public function planSpot(){
        return $this->belongsTo('App\Models\PlanSpot','plan_spot_id','id');
    }
    public function isSlotPurchased($user_id){
       return self::where('user_id',$user_id)->where('end','>',date('Y-m-d H:i:s'))->first();
    }
}
