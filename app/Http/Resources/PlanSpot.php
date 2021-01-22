<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanSpot extends JsonResource
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
            'id' => $this->id,
            'spot_no'  => $this->spot_no,
            'price' =>  $this->price,
            'is_spot_available'=>$this->is_spot_available,
            'package'=>new Plan($this->package)
        ];
    }
}
