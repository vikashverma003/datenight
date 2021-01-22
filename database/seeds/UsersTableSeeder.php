<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $users = [
        [
          'name'=>"admin",
          'email'=>'admin@code-brew.com',
          'password'=>Hash::make('password'),
          'role'=>config('constants.role.ADMIN'),
          'account_status' => config('constants.account_status.ACTIVE'),
          'country'=>'India',
          'phone_code'=>'91',
          'phone_number'=>'xxxxx-xxxx',
          'date_of_birth'=>'27-02-1991',
          "gender"=>'Male',
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s')
        ]
        ];
        foreach($users as $user)
        {
          \DB::table('users')->insert($user);
        }
    }
}
