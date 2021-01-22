<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSpot extends Model
{
    protected $fillable = [
        'plan_id', 'spot_no','price','active'
    ];

    protected $appends=['is_spot_available'];
    public function getById($id){
        return self::where('id',$id)->first();
    }
    public function isSlotAvaliable($plan_id){
            $isPurchased=PurchasedSpots::where('plan_spot_id',$plan_id)->where('start','<',date('Y-m-d H:i:s'))->where('end','>',date('Y-m-d H:i:s'))->first();
        if(is_null($isPurchased)){
            return 1;
        }
        return 0;
    }
    public function getIsSpotAvailableAttribute(){
        $isPurchased=PurchasedSpots::where('plan_spot_id',$this->id)->where('start','<',date('Y-m-d H:i:s'))->where('end','>',date('Y-m-d H:i:s'))->first();
        if(is_null($isPurchased)){
            return 1;
        }
        return 0;
    }

     public function package(){
        return $this->belongsTo('App\Models\plan','plan_id','id');
    }

    public function purchaseSlot(){
        return $this->hasMany('App\Models\PurchasedSpots','plan_spot_id');
    }
    public function getRemaingSlot(){
        $purchasePlan=PurchasedSpots::where('start','<',date('Y-m-d H:i:s'))->where('end','>',date('Y-m-d H:i:s'))->pluck('plan_spot_id')->toArray();
        return self::whereNotIn('id',$purchasePlan)->count();
    }
}
