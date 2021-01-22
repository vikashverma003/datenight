<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use  App\Repositories\Interfaces\DateNightRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Helper;
use App\Models\Notification;
use App\Models\DateNightContact;
use Illuminate\Validation\Rule;
use App\Models\Transaction;
use App\Models\UserCard;
use App\Models\Business;

class DateNightController extends Controller
{
    use \App\Traits\APIResponseManager;
    use \App\Traits\CommonUtil;
    use \App\Traits\StripeManager;

    public function __construct(
        DateNightRepositoryInterface $dateNight,
        DateNightContact $dateNightContact,
        Transaction $transaction,
       UserCard $userCard,
       Business $business){
        $this->dateNight=$dateNight;
        $this->dateNightContact=$dateNightContact;
        $this->transaction=$transaction;
        $this->userCard=$userCard;
        $this->business= $business;
    }

    public function createDateNightEvent(Request $request){
        \Log::info($request->all());
        try{
            $request->validate([
                'name' => 'required',
                'date'=> 'required|date_format:Y-m-d',
                'start_time' => 'required|date_format:H:i',
                'location'  =>'required',
                'contacts'=>'required',
                'is_custom_business'=>'required',
                'business_ids.*'=>'required_if:is_custom_business,1|exists:businesses,id',
                'names'         =>'required',
                'card_id'=>'required_if:is_custom_business,1|exists:user_cards,id',
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }

            $user=Auth::user();
            try{
                DB::beginTransaction();
            

            $createdEvent= $this->dateNight->create([
                'user_id'  => $user->id,
                'name'     => $request->name,
                'date'        =>  $request->date,
                'start_time'  => $request->start_time,
                'location'    =>$request->location,
                'custom_business_count'=>$request->has('business_ids')?count($request->business_ids):0

            ]);
            if($request->is_custom_business==1){
                $user=Auth::user();
                $cardDetail=$this->userCard->getById($request->card_id,$user->id);
                if(is_null($cardDetail)){
                    return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'USER_CARD_NOT_EXIST');
                }
                self::stripeInit();
                $charge=self::makePayment($cardDetail->customer_id,config('constants.DATE_NIGHT_CUSTOM_COST'));
                \Log::info(json_encode($charge));
                if($charge['status']==1){
                    if(isset($charge['data']['id'])){
                        $cb=count($request->business_ids);
                        $remainingBusiness=$this->business->getInviteBusiness($request->location,20-$cb,$request->business_ids);
                        $finalarray=array_merge($request->business_ids, $remainingBusiness);
                        $createdEvent->businesses()->sync($finalarray);
                        $createdEvent->likebusinesses()->sync($request->business_ids);
                        $this->transaction->createTransaction([
                            'user_id'=>$user->id,
                            'plan_spot_id'=>null,
                            'purchased_spots_id'=>null,
                            'amount'   =>config('constants.DATE_NIGHT_CUSTOM_COST'),
                            'user_card_id'=>$cardDetail->customer_id,
                            'stripe_transaction_id'=>$charge['data']['id']
                        
                        ]);
                       
                      //  return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'SPOT_PURCHASE_SUCCESS', 'response',[]);
                    }else{
                        DB::rollback();
                        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'STRIPE_ERROR');
                    }
                }else{
                    DB::rollback();
                    return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'STRIPE_ERROR', 'error_details', $charge['data']);
                }
               
            }else{
                $remainingBusiness=$this->business->getInviteBusiness($request->location,20);
                $createdEvent->businesses()->sync( $remainingBusiness);
            }
           
            foreach($request->contacts as $key=> $contacts){

                    $res=$this->dateNightContact->addContact([
                    'date_night_id'=>$createdEvent->id,
                    'contact_no'  => $contacts,
                    'name'        => $request->names[$key]
                ]);
                    $new_string = substr($contacts, 1);
                    $data=User::where('phone_number',$new_string)->first();
                    if(!empty($data->id)){
                    $token=$data->device_token;
                    $userID=Auth::user();
                    $fname=$userID->name;
                    $profile_image=$userID->profile_image;
                    $lname=$userID->last_name;
                    $c = $fname." ".$lname; 
                   $count=Notification::where('user_id',$data->id)->where('badge',0)->count();
                   $finalCount=$count+1;
                    Helper::SendPushNotifications($token,'DateNight Invitation',$c." ".'sent you date night invitation',$finalCount);
                    //$timezone=date_default_timezone_set($data->timezone);

                    $info= DB::table('notification')->insert([
                               'type'               => '1',
                               'title'              => 'DateNight Invitation',
                               'profile_image'      => $profile_image,
                               'action_id'          => $res->id??null,
                               'message'            => $c." ".'sent you date night invitation',
                               'user_id'            => $data->id,
                               'created_at'         => new \DateTime,
                               'updated_at'         => new \DateTime, 
                              ]);
                }
                }
        DB::commit();
        $createdEvent->load(['businesses.images','contacts','locationd']);
        $dateNightResource=$this->dateNight->resource( $createdEvent);
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'DATE_NIGHT_CREATE_SUCCESS', 'response',   $dateNightResource);
    } catch (\PDOException $e) {
        DB::rollback();
        $errorResponse = $e->getMessage();
        $this->printLog('73','BusinessController',$errorResponse,config('constants.LOG_TYPE.ERROR'));

        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
    }


    public function getDateNights(Request $request){

        try{
            $request->validate([
                'type' => 'required'
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            try{
            $user=Auth::user();
            $isPrev=$request->get('is_previous')??0;
            switch($request->type){

                //created DateNights
                case 1:
                    $match=$this->dateNight->getCreatedDateNightMatch($user->id,$isPrev);
                    $matchIds=$match->pluck('id')->toArray();
                    $data= $this->dateNight->getCreatedDateNights($user->id,$isPrev,$matchIds);
                  //print_r($match);  
                    break;

                //created DateNights

                default:
                $match=$this->dateNight->getInviteDateNightMatch($user,$isPrev);
                $matchIds=$match->pluck('id')->toArray();
                $data=$this->dateNight->getInvitedDateNights($user,$isPrev,$matchIds);
                // print_r($match->toArray());   
                 
            }
             
             //print_r($match->toArray);
           
            $dataobt=['datenight'=>$data,'matchArray'=> $match];
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $dataobt);
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        $this->printLog('73','BusinessController',$errorResponse,config('constants.LOG_TYPE.ERROR'));

        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }     
    }

    public function invitationAction(Request $request){
        try{
            $request->validate([
                'datenight_id' => 'required',
                'action_type'  => [Rule::in([config('constants.datenight_action.DECLINED'),config('constants.datenight_action.CONFIRMED')])],
                'selectedBusiness.*'=>'required_if:action_type,'.config('constants.datenight_action.CONFIRMED'),
                'user_type'=> [Rule::in(1,2)],
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            $user=Auth::user();
         //   dd($request->all());
            $datanight= $this->dateNight->find($request->datenight_id);
            // print_r($datanight->toArray());
            // die();
            if($request->user_type==1){

            $datanight->likebusinesses()->detach($request->selectedBusiness);
            $datanight->likebusinesses()->attach($request->selectedBusiness);
            
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'DATE_NIGHT_INVITATION', 'response',[]);

            }else{
                
                $businesses=$datanight->businesses()->pluck('business_id')->toArray();
                // print_r($request->selectedBusiness);
                // die();
                if(!empty($request->selectedBusiness)){
         foreach($request->selectedBusiness as $a){
            if(!in_array($a,$businesses)){
                $datenight->businesses()->attach($a);
            }
         }
     }
           $dateNightContact=DateNightContact::where('date_night_id',$datanight->id)->where(function($q) use($user){
            $q->where('contact_no',$user->phone_code.''.$user->phone_number)->orWhere('contact_no',$user->phone_number);
        })->first();
        try{
            // print_r($dateNightContact);
            // die();
        if(!is_null($dateNightContact)){
            DB::beginTransaction();
            $dateNightContact->status=$request->action_type;
            $dateNightContact->save();

            $dateNightContact_Count=DateNightContact::where('date_night_id',$datanight->id)->get()->count();
            $dateNightContact_Status=DateNightContact::where('date_night_id',$datanight->id)->where('status','=',1)->get()->count();
            $date_user=DateNightContact::where('date_night_id',$datanight->id)->where('status','=',1)->get();

            if($dateNightContact_Count==$dateNightContact_Status){
                foreach($date_user as $date_users){
                    $contacts=$date_users->contact_no;
                    $new_string = substr($contacts, 1);
                    $data=User::where('phone_number',$new_string)->first();
                    $token=$data->device_token;
                     $userID=Auth::user();
                    $fname=$userID->name;
                    $profile_image=$userID->profile_image;
                    $lname=$userID->last_name;
                    $c = $fname." ".$lname; 
                    $finalCount=1;
                    Helper::SendPushNotifications($token,'DateNight Matched with',$c." ".' Happy Date Night',$finalCount);
                   $info= DB::table('notification')->insert([
                               'type'               => '1',
                               'title'              => 'DateNight Matched',
                               'profile_image'      => $profile_image,
                               'action_id'          => $datanight->id??null,
                               'message'            => $c." ".'Happy Date Night',
                               'user_id'            => $data->id,
                               'created_at'         => new \DateTime,
                               'updated_at'         => new \DateTime, 
                              ]);
                }
            }

           $dateNightContact->businesses()->sync($request->selectedBusiness);
            DB::commit();
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'DATE_NIGHT_INVITATION', 'response',[]);
        }

        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DATE_NIGHT_CONTACT_NOT_EXIST');
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        DB::rollback();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
}      
    }

    public function getNextBussiness(Request $request){
      
        try{
            $request->validate([
                'page' => 'required',
                'date_night_id'  => 'required|exists:date_nights,id'            
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
        try{
     $datenight=$this->dateNight->getById($request->date_night_id);
      $businessIds= $datenight->businesses()->pluck('business_id')->toArray();
      $remainingBusiness=$this->business->getInviteBusiness($datenight->location,20,$businessIds,$request->page);
   //   $datenight->businesses()->attach( $remainingBusiness);
      $business=$this->business->getBusnessesWithIds( $remainingBusiness);
      return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response', $business);
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        $this->printLog('73','BusinessController',$errorResponse,config('constants.LOG_TYPE.ERROR'));

        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }  
       } 
}
