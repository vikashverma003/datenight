<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Http\Resources\UserCard as UserCardResource;
class UserCard extends Model
{
    protected $fillable=[
    'user_id',
    'customer_id',
    'brand',
    'last4',
    'name',
    'exp_month',
    'exp_year',
    'card_id',
    'card_type'
    ];

    public function createCard($data){
     $data= self::create([
            'user_id'=>$data['user_id'],
            'customer_id'=>$data['customer_id'],
            'brand'=>$data['brand'],
            'last4'=>$data['last4'],
            'name'=>$data['name'],
            'exp_month'=>$data['exp_month'],
            'exp_year'=>$data['exp_year'],
            'card_id'=>$data['card_id'],
            'card_type'=>$data['card_type']??null
        ]);
       return  $this->user_card_resource($data);
    }
    public function user_card_resource($usercard){
        return new UserCardResource($usercard);
    }
    public function user_card_collection($usercard){
        return UserCardResource::Collection($usercard);
    }

    public function getAll(){
        $data=self::all();
       return $this->user_card_collection($data);

    }
    public function getAllByUserId($user_id){
        $data=self::where('user_id',$user_id)->orderBy('id', 'DESC')->get();
       return $this->user_card_collection($data);

    }
    

    public function setDefault($user_id,$id){
        self::where('user_id',$user_id)->update([
            'is_default'=>0
        ]);
       return self::where('id',$id)->where('user_id',$user_id)->update([
            'is_default'=>1
        ]);
    }

    public function getById($id,$user_id){
      return self::where('id',$id)->where('user_id',$user_id)->first();
    }
}
