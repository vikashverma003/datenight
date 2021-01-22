<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Plan extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
         $today = date("F"); 
         $a='plan';
         $plan_name=$today.' '.$a;

        return [
            'id' => $this->id,
            //'name'  => $this->name,
            'name'  => $plan_name,
            'description' =>  $this->description,
            'image' =>  !is_null($this->image)?env('APP_URL')."/plans/".$this->image:'',
            'price'=> '$20',
            'spots'=>$this->when(
                $this->relationLoaded('spots'),
                function () {
                    return PlanSpot::Collection($this->spots);
                }
            )
        ];
    }
}
