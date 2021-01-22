<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Http\Resources\Plan as PlanResource;

class Plan extends Model
{
    public function  getAll(){
        $plans= self::all();
       return  $this->plan_collection($plans);
    }
    public function  getById($id){
        $plan= self::where('id',$id)->with('spots')->first();
        $today = date("F"); 
        $a='plan';
        $plan['name']=$today.' '.$a;

       return  $this->plan_resource($plan);
    }

    public function plan_resource($plan){
        return new PlanResource($plan);
    }
    public function plan_collection($plans){
        return PlanResource::Collection($plans);
    }

    public function spots(){
        return $this->hasMany('App\Models\PlanSpot','plan_id');
    }
}
