<?php

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocatonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $location=[
            0=>[
                    'name'=>' St Louis',
                    'hash_tag'=>'stLouis',
                    'active'    =>1
            ],
            1=>[
                'name'=>'Chicago',
                'hash_tag'=>'chicago',
                'active'    =>1
        ],  
        3=>[
            'name'=>'Atlanta',
            'hash_tag'=>'atlanta',
            'active'    =>1
        ],
    ];
        foreach($location as $l){
            Location::create($l);
        }
       
    }
}
