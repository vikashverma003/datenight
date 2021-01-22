<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    public function getASP(){
       return  self::where('option_key','ADVERTISER_SLOT_PRICE')->first();
    }
    public function getTMSP(){
      return   self::where('option_key','TARGET_MARKET_SUB_PRICE')->first();
    }
}
