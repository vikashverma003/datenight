<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class Business extends JsonResource
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
            'user_id'  => $this->user_id,
            'username' =>  $this->username,
            'name'      =>  $this->name,
  	     'opening_time' =>  date("h:i A", strtotime($this->opening_time)),
            'closing_time'  =>  date("h:i A", strtotime($this->closing_time)),
            'location_id' => $this->location,
            'is_business' =>1,
            'spot_no'  =>$this->spot_no,
            'location'  =>$this->when(
                $this->relationLoaded('locationd'),
                function () {
                    return new Location($this->locationd);
                }
            ),
            'lng'   => $this->lng,
            'timezone'  => $this->timezone,
            'address'  =>  $this->address,
            'min_age'  =>  $this->min_age,
            'max_age'  =>  $this->max_age,
            'description'=>  $this->description,
            'website_link' =>  $this->website_link,
            'is_favourite' =>$this->is_favourite,
           // 'distance'      => $this->when($this->distance,round($this->distance,2)),
            'user'      =>new BusinessUser($this->whenLoaded('user')),
            'bussiness_images'=>BusinessImage::Collection($this->whenLoaded('images')),
            'business_events' =>BusinessEvent::Collection($this->whenLoaded('businessEvent')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
