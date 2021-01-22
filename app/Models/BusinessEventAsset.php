<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessEventAsset extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id', 'file_name', 'active','video_image'
    ];
    
    public function createEventAsset($data){
      return self::create([
            'event_id'   =>$data['event_id'],
            'file_name'  =>$data['file_name'],
            'active'     =>1,
            'video_image'=>$data['video_image']
        ]);
    }

    public function deleteAssest($event_id){
        return self::where('event_id',$event_id)->delete();
    }

    public function deleteById($id){
        return self::where('id',$id)->delete();
    }
}
