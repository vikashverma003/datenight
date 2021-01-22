<?php

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Plan::create([
            'name'  =>'November Subscription Plan',
            'description'=>"It's time to increase your customers! Buy our subscription plan and save a spot of your choice amongst the top 20 restaurants that will be listed for a date night Hurry!"
        ]);
    }
}
