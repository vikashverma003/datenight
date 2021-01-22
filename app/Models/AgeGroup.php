<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgeGroup extends Model
{
    protected $fillable = [
        'user_id', 'business_id', 'min_age','max_age'
    ];
    public function createAgeGroup($data){
        $createdAgeGroup= self::create(
            [
                'user_id'  =>  $data['user_id'],
                'business_id' =>  $data['business_id'],
                'min_age'  =>  $data['min_age'],
                'max_age'      =>  $data['max_age']
            ]
        );
        return $createdAgeGroup;
    //   return $this->business_resource($createdBusiness);

    }

    public function getOwnAgeGroup($user){
        return self::where('user_id',$user->id)->get();
    }
}
