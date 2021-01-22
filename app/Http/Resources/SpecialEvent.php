<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpecialEvent extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if(!empty($this->start_time)){
           $start_time= date("h:i A", strtotime($this->start_time));
        }else{
            $start_time='';
        }

        if(!empty($this->end_time)){
          $end_time=date("h:i A", strtotime($this->end_time));
        }else{
           $end_time='';
        }


        return [
            'id' => $this->id,
            'user_id'  => $this->user_id,
            'business_id'  => $this->business_id,
            'name' =>  $this->name,
            'date' =>  $this->date,
            'is_business' =>0,
            'start_time'      =>   $start_time,
            'end_time' =>        $end_time,
            'description'=> $this->description,
            'website_name'=>$this->website_name,
            'location_id' => $this->location_id,
             'location'  =>$this->when(
                $this->relationLoaded('location'),
                function () {
                    return new Location($this->location);
                }
            ),
            'images'  => SpecialEventImage::Collection($this->images),
            'business'=>new Business($this->business),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
