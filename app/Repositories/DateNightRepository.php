<?php
namespace App\Repositories;
use App\Models\DateNight;
use App\User;
use App\Http\Resources\DateNight as DateNightResource;
use App\Http\Resources\DateNightCollection;
 use Carbon\Carbon;


use App\Repositories\Interfaces\DateNightRepositoryInterface;



class DateNightRepository implements DateNightRepositoryInterface
{
    public function all()
    {
        $location= DateNight::all();
       return $this->resourceCollection($location);
    }

  

    public function create(array $data){
        return DateNight::create([
            'user_id'           =>  $data['user_id'],
            'name'              =>  $data['name'],
            'date'              =>  $data['date'],
            'start_time'        =>  $data['start_time'],
            'location'          =>  $data['location'],
            'custom_business_count'=>$data['custom_business_count']
            ]);
    }

    public function resource($dateNight){
        return new DateNightResource($dateNight);
    }
    public function resourceCollection($dateNights){
        return  new DateNightCollection($dateNights);
    }

    public function collection($locations){
       // return  new LocationCollection($locations);
    }


    public function getCreatedDateNights($user_id,$isPrev=0,$exclude=[],$perPage=10){
        $dateNights= DateNight::where('user_id',$user_id)->whereNotIn('id',$exclude);
        
        if($isPrev==1){
            $dateNights=  $dateNights->previous();
        }else if($isPrev==2){
            $dateNights=  $dateNights->presentFuture();
        }else{
           $dateNights=  $dateNights->presentFuture();
        }

        $dateNights=$dateNights->with(['locationd','businesses.images','contacts','user'])->orderBy('id','desc')->paginate($perPage);
        
      foreach ($dateNights as $res) {

        $image=$res->user->profile_image;
        //print_r($res->contacts->toArray());
       // die();
       if(isset($res->contacts)){
        foreach ($res->contacts as $ret) {
           $phone_number= ltrim($ret['contact_no'], '1');
           $match=User::where('phone_number',$phone_number)->first();
           if(!empty($match->profile_image)){
           $ret['profile_image'] =is_null($match->profile_image)?'':env('APP_URL')."".env('IMAGE_UPLOAD_PATH').'/'.$match->profile_image;
           }else{
            $ret['profile_image'] ='';
           }
        }
       }
      }

        return $this->resourceCollection($dateNights);
    }

    public function getInvitedDateNights($user,$isPrev=0,$exclude=[],$perPage=10){
        $dateNights= DateNight::whereHas('contacts',function($q) use($user){
            $q->where('status','<>',config('constants.datenight_action.DECLINED'))->where(function($qq) use($user){
                $qq->where('contact_no',$user->phone_code.''.$user->phone_number)->orWhere('contact_no',$user->phone_number);
            });
        })->where('user_id','!=',$user->id)->whereNotIn('id',$exclude);
        
        if($isPrev==1){
            $dateNights=  $dateNights->previous();
         }else if($isPrev==2){
            $dateNights=  $dateNights->presentFuture();
        }else{
           $dateNights=  $dateNights->presentFuture();
        }
       
      //  $dateNights=$dateNights->with(['locationd','businesses.images','contacts','user'])->orderBy('id','desc')->paginate($perPage);

         $dateNights=$dateNights->with(['locationd','businesses.images','contacts','user'])->orderBy('id','desc')->paginate($perPage);

   
      foreach ($dateNights as $res) {

        $image=$res->user->profile_image;
        //print_r($res->contacts->toArray());
       // die();
       if(isset($res->contacts)){
        foreach ($res->contacts as $ret) {
           $phone_number= ltrim($ret['contact_no'], '1');
           $match=User::where('phone_number',$phone_number)->first();
           if(!empty($match->profile_image)){
           $ret['profile_image'] =is_null($match->profile_image)?'':env('APP_URL')."".env('IMAGE_UPLOAD_PATH').'/'.$match->profile_image;
           }else{
            $ret['profile_image'] ='';
           }
         
        }
       }

      }

        

        return $this->resourceCollection($dateNights);
    }
   
    public function find($id){
        return DateNight::where('id',$id)->first();
    }
   
    public function getById($id){
       return   DateNight::where('id',$id)->first();
    }

    public function getCreatedDateNightMatch($user_id,$isPrev=0,$perPage=10){

        $dateNights1= DateNight::where('user_id',$user_id)->get();

        $matchArray=[];
         $matchRestorant=[];
        foreach( $dateNights1 as $datanighttt){
        $owerLike=$datanighttt->likebusinesses()->pluck('businesses.id')->toArray();
 $intiveLike=[];
 $matchRestorant=$owerLike;
foreach($datanighttt->contacts as $contact){
   
        $intiveLike=$contact->businesses()->pluck('businesses.id')->toArray();
        $matchRestorant=array_intersect($matchRestorant,$intiveLike);
    }
        $remainArray=array_diff($owerLike,$matchRestorant);
       
        if(count($owerLike)>0 && count($owerLike)!=count($remainArray)){
            $matchArray[]=$datanighttt->id;
        }
        }   
      
         
        $dateNights= DateNight::where('user_id',$user_id)->whereIn('id',$matchArray);
        
        if($isPrev==1){
            $dateNights=  $dateNights->previous();
         }else if($isPrev==2){
            $dateNights=  $dateNights->presentFuture();
        }else{
           $dateNights=  $dateNights->presentFuture();
        }       
        $dateNights=$dateNights->with(['locationd','businesses'=>function($q) use($matchRestorant){
            $q->whereIn('business_id',$matchRestorant)->with('images');
        },'contacts','user'])->orderBy('id','desc')->paginate($perPage);

 
foreach ($dateNights as $res) {

        $image=$res->user->profile_image;
        //print_r($res->contacts->toArray());
       // die();
       if(isset($res->contacts)){
        foreach ($res->contacts as $ret) {
           $phone_number= ltrim($ret['contact_no'], '1');
           $match=User::where('phone_number',$phone_number)->first();
           if(!empty($match->profile_image)){
           $ret['profile_image'] =is_null($match->profile_image)?'':env('APP_URL')."".env('IMAGE_UPLOAD_PATH').'/'.$match->profile_image;
           }else{
            $ret['profile_image'] ='';
           }
         
        }
       }

      }

        return $this->resourceCollection($dateNights);
    }

 public function getInviteDateNightMatch($user,$isPrev=0,$perPage=10){
   
        $dateNights1= DateNight::whereHas('contacts',function($q) use($user){
            $q->where('status','<>',config('constants.datenight_action.DECLINED'))->where(function($qq) use($user){
                $qq->where('contact_no',$user->phone_code.''.$user->phone_number)->orWhere('contact_no',$user->phone_number);
            });
        })->where('user_id','!=',$user->id)->get();
       
        $matchArray=[];
        $matchRestorant=[];
        foreach( $dateNights1 as $datanighttt){
        $owerLike=$datanighttt->likebusinesses()->pluck('businesses.id')->toArray();
 $intiveLike=[];
 $matchRestorant=$owerLike;
foreach($datanighttt->contacts as $contact){
   
        $intiveLike=$contact->businesses()->pluck('businesses.id')->toArray();
        $matchRestorant=array_intersect($matchRestorant,$intiveLike);
    }
        $remainArray=array_diff($owerLike,$matchRestorant);
       
        if(count($owerLike)>0 && count($owerLike)!=count($remainArray)){
            $matchArray[]=$datanighttt->id;
        }
        }   
       
       // if($isPrev =='0'){

       //  $dateNights= DateNight::whereHas('contacts',function($q) use($user){
       //       $q->where('status','<>',config('constants.datenight_action.DECLINED'))->where(function($qq) use($user){
       //          $qq->where('contact_no',$user->phone_code.''.$user->phone_number)->orWhere('contact_no',$user->phone_number);
       //      });
       //  })->where('user_id','!=',$user->id)->whereIn('id',$matchArray)->whereDate('date', '>', Carbon::now());

       // }else{
        $dateNights= DateNight::whereHas('contacts',function($q) use($user){
             $q->where('status','<>',config('constants.datenight_action.DECLINED'))->where(function($qq) use($user){
                $qq->where('contact_no',$user->phone_code.''.$user->phone_number)->orWhere('contact_no',$user->phone_number);
            });
        })->where('user_id','!=',$user->id)->whereIn('id',$matchArray);
       //}
  
        $dateNights=$dateNights->with(['locationd','businesses'=>function($q) use($matchRestorant){
            $q->whereIn('business_id',$matchRestorant)->with('images');
        },'contacts','user'])->orderBy('id','desc')->paginate($perPage);
      $z=0;
      foreach ($dateNights as $res) {

        $free_trial= $res->date;
        $start_time= $res->start_time;
        $ttt=$free_trial.' '.$start_time;
        $ttts =Carbon::parse($ttt);
        $date = Carbon::now();
      
       
        if($isPrev =='0' && $ttts <= $date) {
         
          //$dateNights[$z];
          unset($dateNights[$z]);

        } else{
          
          $image=$res->user->profile_image;
           if(isset($res->contacts)){
            foreach ($res->contacts as $ret) {
               $phone_number= ltrim($ret['contact_no'], '1');
               $match=User::where('phone_number',$phone_number)->first();
               if(!empty($match->profile_image)){
               $ret['profile_image'] =is_null($match->profile_image)?'':env('APP_URL')."".env('IMAGE_UPLOAD_PATH').'/'.$match->profile_image;
               }else{
                $ret['profile_image'] ='';
               }
             
            }
           }
        }   
        $z++;
      }

    // print_r($dateNights->count());
    // die();
        return $this->resourceCollection($dateNights);
    }


   
}
