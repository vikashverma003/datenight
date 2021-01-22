<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\Business as BusinessResource;
use App\Http\Resources\BusinessCollection;
use Illuminate\Support\Collection;
use DB;
use Illuminate\Support\Facades\Auth;

class Business extends Model
{
      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'username', 'location','lat','lng','address','website_link','timezone'
    ];

    protected $appends = ['is_favourite','spot_no'];
     /***
     * Create use code
     * 
     */

    public function createBusiness($data){
        $createdBusiness= self::create(
            [
                'user_id'  =>  $data['user_id'],
                'username' =>  $data['username']??null,
                'location' =>  $data['location'],
                'lat'      =>  $data['lat'],
                'lng'      =>  $data['lng'],
                'timezone' =>  $data['timezone'],
                'address'  =>  $data['address'],
                'website_link' =>  $data['website_link']??''
            ]
        );
       return $this->business_resource($createdBusiness);

    }

    public function updateBusiness($id,$data){
        $status= self::where('id',$id)->update(
            [
             //   'name'  =>  $data['name'],
                'opening_time' =>  $data['opening_time'],
                'closing_time'  =>  $data['closing_time'],
                'description'      =>  $data['description'],
                //'website_link' =>  $data['website_link'],
               //// 'min_age'      =>  $data['min_age'],
               // 'max_age'      =>  $data['max_age']
            ]
        );
       return  $status;

    }



    public function business_resource($business){
        return new BusinessResource($business);
    }
    public function business_collection($businesses){
        return  new BusinessCollection($businesses);
    }

    public function business_collection1($businesses){
        return  BusinessResource::Collection($businesses);
    }
    

       
    public function images()
    {
        return $this->hasMany('App\Models\BusinessImage','business_id');
    }

    public function user(){
        return $this->belongsTo('App\User','user_id','id');
    }

    public function businessEvent()
    {
        return $this->hasMany('App\Models\BusinessEvent','business_id');
    }

    public function userFav()
    {
        return $this->belongsToMany('App\User','user_fav_businesses','business_id','user_id')->withTimestamps();
    }

    public function getIsFavouriteAttribute($value)
    {
       $user=\Auth::user();
       if(!is_null($user)){
        return $this->userFav()->where('user_id', $user->id)->count();
      }else{
        return 0;
      }
    }

    public function getBusinessesList($lat,$lng,$per_page=10,int $page=1,$location=1){
    	$skill=($page-1)*$per_page;
		$skill1=($page-1)*5;
		$resultSetA=Self::where('location',$location)->whereHas('user',function($q){            $q->where('account_status',config('constants.account_status.ACTIVE'))
            ->where('registration_step',config('constants.registration_step.SECOND'))
			->where('role','advertiser');
        })->with(['user','images','locationd'])->has('images', '>=', 1)->get()->sortBy('spot_no')->skip($skill)->take(5);
		
		$Advertiser=$resultSetA->count();
		$remaining=$per_page-$Advertiser; 
     $resultSet=Self::where('location',$location)->whereHas('user',function($q){            $q->where('account_status',config('constants.account_status.ACTIVE'))
            ->where('registration_step',config('constants.registration_step.SECOND'))
			->where('role','business');
        })->with(['user','images','locationd'])->has('images', '>=', 1)->get()->sortBy('spot_no')->skip($skill)->take($per_page);
		
		$resultSet1=$resultSet->values()->shuffle()->all();
		$data1=$this->business_collection($resultSet1);
		$data2=$this->business_collection($resultSetA);
		$j=4;
		 for($i=0;$i<$data2->count();$i++){
			 $data1->splice($j,0,(object) [$data2[$i]]);
             $j=$j+5;
		 }
 // $business = new \Illuminate\Pagination\Paginator( $resultSet1,$per_page,$page);
        return  $data1;
      
    }

    public function getBusinessByUserId($user_id,$relation=null){
        $business=self::where('user_id',$user_id);
        if(!is_null($relation)){
            $business=$business->with($relation);
          }
        return $business->first();
    }


    public function getById($id,$with=null){
         $business=self::where('id',$id);
        if(!is_null($with)){
            $business=$business->with($with);
        }
        $business= $business->first();
        return $this->business_resource($business);
    }

    public function getuserFavBusinessesList($user_id,$per_page=10){
        $resultSet=Self::whereHas('userFav',function($q) use($user_id){
               $q->where('user_id',$user_id);
           })
           ->with(['user','images','locationd'])
          ->paginate($per_page);
   
           return  $this->business_collection($resultSet);
         
       }
       public function locationd()
       {
           return $this->belongsTo('App\Models\Location','location');
       }


    public function getSpotNoAttribute(){

        $isPurchased=PurchasedSpots::where('user_id',$this->user->id)->where('start','<',date('Y-m-d H:i:s'))->where('end','>',date('Y-m-d H:i:s'))->first();
        if(!is_null($isPurchased)){
            return $isPurchased->planSpot->spot_no;
        }
        return 1000;
    }


    public function getInviteBusiness($location,$per_page=20,$exclude=null,$page=1){
        $skill=($page-1)*$per_page;
     $resultSet=Self::where('location',$location);
     if(!is_null($exclude)){
  $resultSet=$resultSet->whereNotIn('id',$exclude);
     }
     $resultSet=$resultSet->whereHas('user',function($q){
            $q->where('account_status',config('constants.account_status.ACTIVE'))
            ->where('registration_step',config('constants.registration_step.SECOND'));
        })
      ->has('images', '>=', 1)
     ->get()->sortBy('spot_no')->skip($skill)->take($per_page)->pluck('id')->toArray();

 return $resultSet;
    }
    

    public function getBusnessesWithIds($ids){
        $businesses= self::whereIn('id',$ids)->has('images', '>=', 1)->with(['images'])->get();
        return $this->business_collection1($businesses);
    }

    public function isPurchased($user_id){
        return self::where('user_id',$user_id)->where('is_target_age_rage',1)->where('target_age_rage_expire_date','>',date('Y-m-d H:i:s'))->count();
    }
    public function purchasedPlanDetail($user_id){
        return self::where('user_id',$user_id)->where('is_target_age_rage',1)->where('target_age_rage_expire_date','>',date('Y-m-d H:i:s'))->first();
    }
    
}
