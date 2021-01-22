<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialEventImage extends Model
{
    protected $fillable = [
        'special_event_id', 'file_name', 'active','video_image'
    ];
    
    public function createEventImage($data){
      return self::create([
            'special_event_id'=>$data['special_event_id'],
            'file_name'=>$data['file_name'],
            'video_image' => $data['video_image'],
            'active'=>1
        ]);
    }

    public function deleteImages($event_id){
        return self::where('special_event_id',$event_id)->delete();
    }

    public function deleteById($id){

        return self::where('id',$id)->delete();
    }
}
