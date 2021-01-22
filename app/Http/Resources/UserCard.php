<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserCard extends JsonResource
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
            'id'=>$this->id,
            'user_id'=>$this->user_id,
            'customer_id'=>$this->customer_id,
            'brand'=>$this->brand,
            'last4' =>$this->last4,
            'name' =>$this->name,
            'exp_month' =>$this->exp_month,
            'exp_year'=>$this->exp_year,
            'card_id'=>$this->card_id,
            'is_default'=>(int) $this->is_default
        ];
    }

  
}
