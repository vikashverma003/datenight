<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable=['user_id','plan_spot_id','purchased_spots_id','amount','user_card_id','stripe_transaction_id'];

    public function createTransaction($data){
        return self::create([
            'user_id'=>$data['user_id'],
            'plan_spot_id'=>$data['plan_spot_id'],
            'purchased_spots_id'=>$data['purchased_spots_id'],
            'amount'   =>$data['amount'],
            'user_card_id'=>$data['user_card_id'],
            'stripe_transaction_id'=>$data['stripe_transaction_id']
        ]);
    }
}
