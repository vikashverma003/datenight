<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use DB;

use App\Http\Resources\User as UserResource;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','last_name','email', 'password','role','phone_code','phone_number','date_of_birth','account_status','registration_step','device_token','profile_image','lat','lng','timezone','city','location_id','social_id','social_type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /***
     * Create use code
     * 
     */

    public function createUser($data){
        $createdUser= self::create(
            [
                'name'  =>  $data['name']??'',
                'last_name'=>$data['last_name']??null,
                'email' =>  $data['email'],
                'password'  =>  Hash::make($data['password']),
                'role'      =>  $data['role'],
                'phone_code'    => $data['phone_code'],
                'phone_number'  =>  $data['phone_number'],
                'date_of_birth' =>  $data['date_of_birth'],
                'device_token'  => $data['device_token']??null,
                'profile_image'=>$data['profile_image']??null,
                'lat'          =>$data['lat']??null,
                'lng'           => $data['lng']??null,
                'timezone'      => $data['timezone']??null,
                'city'          =>$data['city']??null,
                'location_id'          =>$data['location_id']??null,
                'account_status'    => config('constants.account_status.ACTIVE'),
                'registration_step' => config('constants.registration_step.FIRST'),
                'social_type'       => $data['social_type']??null,
                'social_id'         =>$data['social_id']??null
            ]
        );
       return $this->user_resource($createdUser);

    }


    public function user_resource($user){
        return new UserResource($user);
    }

    public function createPassportToken($user){
        return $user->createToken('dayNightApp')->accessToken;
    }
    public function getAccessToken($request){
        $token=$request->header('Authorization');
        $arraytoken=explode(' ',$token);
       return $arraytoken[trim(count($arraytoken)-1)];
    }

    public function getUserById($id){
        return self::where('id',$id)->first();
    }
    /****************All Relations here **************** */
     
    public function business()
    {
        return $this->hasOne('App\Models\Business');
    }
    public function favBusiness()
    {
        return $this->belongsToMany('App\Models\Business','user_fav_businesses','user_id','business_id')->withTimestamps();

    }
    public function location()
    {
        return $this->belongsTo('App\Models\Location');
    }

    public function getUserByIdWithLoad($id,$relation){
        return self::where('id',$id)->with($relation)->first();
    }

    public function checkSocial($data){
      return  self::where('social_id',$data['social_id'])->where('social_type',$data['social_type'])->first();
              
    }

    public function countByRole($roleName){
        $usr= User::where('role',$roleName);
        return $usr->count();
        }

        public function getUserByMonthWise($roleName){
            $usr=User::select(DB::raw("count(*) as total"),DB::raw("MONTH(created_at) as Month"))->where('role',$roleName)->groupBy(DB::raw("MONTH(created_at)"));
            //print_r($usr->get()->toArray());
            //die();
             return $usr->get();
         }

         public function getUserByRole($roleName,$perpage=10){
            $usr= User::where('role',$roleName)->with('location');
                   
                    return  $usr->paginate($perpage);
            }  

         public function filterData($roleName,$data,$perpage=10){
            $usr= User::where('role',$roleName)->where('delete_status',0)->with('location');
            if(isset($data['search'])){

            $usr=$usr->Where('email', 'like', '%' . $data['search'] . '%')->orWhere('name', 'like', '%' . $data['search'] . '%');

            }
            // print_r($usr);
            // die();
                   
                    return  $usr->paginate($perpage);
            }      

           

}
