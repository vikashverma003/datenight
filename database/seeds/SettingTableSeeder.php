<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;
class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       
        Setting::create([
                'option_key'=>'ADVERTISER_SLOT_PRICE',
                'option_value' =>10,
            ]);
            Setting::create([
                'option_key'=>'TARGET_MARKET_SUB_PRICE',
                'option_value' =>20,
            ]);
    }
}
