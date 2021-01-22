<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\BusinessImage as BusinessImageResource;
use App\Http\Resources\BusinessImageCollection;


class BusinessImage extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_id', 'image_url', 'active','video_image'
    ];

     /***
     * Create use code
     * 
     */

    public function createBusinessImage($data){
        $createdImage= self::create(
            [
                'business_id'  =>  $data['business_id'],
                'image_url' =>  $data['image_url'],
                'active'  => 1,
                'video_image'=>$data['video_image']
            ]
        );
       return $this->business_image_resource($createdImage);

    }

    public function business_image_resource($image){
        return new BusinessImageResource($image);
    }
    
    public function business_images_collection($images){
        return new BusinessImageCollection($images);
    }

    public function deleteById($id,$business_id){
        return self::where('id',$id)->where('business_id',$business_id)->delete();
    }
    
}
