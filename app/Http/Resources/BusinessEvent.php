<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessEvent extends JsonResource
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
            'business_id'  => $this->business_id,
            'event_name' =>  $this->event_name,
            'event_type' =>  $this->event_type,
            'date' =>  $this->date,
            'start_time'      =>   date("h:i A", strtotime($this->start_time)),
            'end_time' =>  date("h:i A", strtotime($this->end_time)),
            'description'=> $this->description,
            'is_regular_basis'=>$this->is_regular_basis,
            'recurring_type'  => $this->recurring_type,
            'month_day'  => $this->month_day,
            'week_no'   =>$this->month_day,
            'week_day_name'  => $this->week_day_name,
            'event_assets'  => BusinessEventAsset::Collection($this->assets),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
