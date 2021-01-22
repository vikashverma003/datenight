<?php

use Illuminate\Database\Seeder;
use App\Models\PlanSpot;
class SpotsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $price=150;
        for($i=1;$i<=20;$i++){
            PlanSpot::create([
                'plan_id'=>1,
                'spot_no' =>$i,
                'price'=> $price
            ]);
            $price= $price-5;
        }
    }
}
