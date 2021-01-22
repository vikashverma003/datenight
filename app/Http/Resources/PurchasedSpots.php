<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchasedSpots extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'user_id'=>$this->user_id,
            'plan_spot_id'=>$this->plan_spot_id,
            'start'=>$this->start,
            'end'=>$this->end,
            'is_expired'=>$this->is_expired,
            'slot'=>new PlanSpot($this->planSpot)
        ];
    }
}
