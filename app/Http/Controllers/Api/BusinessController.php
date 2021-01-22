<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Models\Business;
use  App\Models\BusinessImage;
use URL;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Str;
use App\Models\SpecialEvent;

class BusinessController extends Controller
{
    use \App\Traits\APIResponseManager;
    use \App\Traits\CommonUtil;

    protected $userObj;
    protected $businessObj;
    private $businessImageObj;
   

    public function __construct(User $user,Business $business,BusinessImage $businessImage,SpecialEvent $specialEvent)
    {
        $this->userObj=$user;
        $this->businessObj=$business;
        $this->businessImageObj=$businessImage;
        $this->specialEvent=$specialEvent;
    }

    public function businessProfileImages(Request $request){
	\Log::info($request->all());
        try{
         
         $user=Auth::user();

           if($user->role == 'advertiser'){

             $request->validate([
                'restaurant_name' => 'required',
                //'opening_time*'=> 'required|date_format:H:i',
                //'closing_time' => 'required|date_format:H:i',
                //'description'  =>'required|min:25',
                'business_images.*'=> 'required',
             
                ]);


           }else{
            $request->validate([
                'restaurant_name' => 'required',
                'opening_time*'=> 'required|date_format:H:i',
                'closing_time' => 'required|date_format:H:i',
                //'description'  =>'required|min:25',
                'business_images.*'=> 'required',
             
                ]);
        }
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            $this->printLog('45','BusinessController',$request->all(),config('constants.LOG_TYPE.ERROR'));
            $user=Auth::user();
            try{
                DB::beginTransaction();
            // if($files=$request->file('business_images')){
            //     foreach($files as $file){
            //         $imageName=$this->uploadGalery($file,env('BUSINESS_IMAGE_UPLOAD_PATH'));
            //         $this->businessImageObj->createBusinessImage([
            //             'business_id'=>$user->business->id,
            //             'image_url' =>$imageName
            //         ]);
            //     }
            // }
            if($request->has('business_images')){
                if(count($request->business_images)>0){
                    foreach($request->business_images as $key=> $image){
                        $extention=$request->extension[$key]??'png';
                    $extensionArray=explode('/',$extention);
                    $extension=$extensionArray[count($extensionArray)-1];
                $imageName=$this->uploadBase64($image,env('BUSINESS_IMAGE_UPLOAD_PATH'),$extension);
                $videothu=null;
                if($extension=='mp4'){
                    $videothu=$this->video_thumb($imageName,env('BUSINESS_IMAGE_UPLOAD_PATH'));
                }
                $this->businessImageObj->createBusinessImage([
                    'business_id'=>$user->business->id,
                    'image_url'  =>$imageName,
                    'video_image'=> $videothu,
                ]);
                    }
                }
                }

            $status=$this->businessObj->updateBusiness($user->business->id,[
                'name'              => $request->restaurant_name,
                'opening_time'      => $request->opening_time??null,
                'closing_time'      => $request->closing_time??null,
                'description'       => $request->description,
                //'website_link'      => $request->website_link??'',
             //   'min_age'          => $request->min_age,
             //      'max_age'          => $request->max_age

            ]);
        $user->registration_step=config('constants.registration_step.SECOND');
        $user->account_status   = config('constants.account_status.ACTIVE');
        $user->save(); 
        $refreshUser=$this->userObj->getUserById($user->id);
        $refreshUser->access_token=$this->userObj->getAccessToken($request);
        $updatedUser=$this->userObj->user_resource($refreshUser);
        DB::commit();
        // $user->device_token=null;
        // $user->save();
        // $user->token()->revoke();
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_UPDATE_SUCCESS', 'response',  $updatedUser);
    } catch (\PDOException $e) {
        DB::rollback();
        $errorResponse = $e->getMessage();
        $this->printLog('73','BusinessController',$errorResponse,config('constants.LOG_TYPE.ERROR'));

        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }

    } //businessProfileImages End here

    public function getBusinesses(Request $request){
        $loggedInUser=Auth::user();
        $this->printLog('90','BusinessController',$request->all());
        try{
            $lat=$request->lat??'30.733315';
            $lng=$request->lng??'76.779419';
    $currentPage=(int) $request->has('page')?$request->get('page'):1;
        $businessesLists=$this->businessObj->getBusinessesList($lat,$lng,20,$currentPage,$loggedInUser->location_id);
        if( $currentPage==1){
           // $businessesLists=collect($businessesLists);
        $fiveAdvertisement=$this->specialEvent->getFiveAdvertisment();
      // dd( $fiveAdvertisement);
        $fiveAdvertisement=collect($fiveAdvertisement);
        $i=4;
        $buscount=$businessesLists->count();

        foreach($fiveAdvertisement as $value){
            
            if($buscount==$i){
             $businessesLists->splice($i,0,(object) [$value]);
             $i=$i+1;
            
            }elseif($buscount>=$i){

             $businessesLists->splice($i,0,(object) [$value]);
             $i=$i+5;
            
            }else{
                    $businessesLists->splice($i,0,(object) [$value]);
                    $i=$i+1;
                   // break;
                }
        }
    }
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $businessesLists);
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        $this->printLog('94','BusinessController',$errorResponse,config('constants.LOG_TYPE.ERROR'));
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
    }

    public function getBusiness($id){
        try{
        $business=$this->businessObj->getById($id,['images','user','businessEvent.assets','locationd']);
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',$business);
        }catch (\PDOException $e) {
            $errorResponse = $e->getMessage();
            $this->printLog('104','BusinessController',$errorResponse,config('constants.LOG_TYPE.ERROR'));
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        }
    }

    public function myBusiness(Request $request){
        try{
            $request->validate([
                'business_id' => 'required|exists:businesses,id',
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            try{
                
                $business=$this->businessObj->getById($request->business_id);
                $business->load(['user','images','businessEvent','locationd']);
                $updatedB= $this->businessObj->business_resource($business);
               
               return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',$updatedB);
            }catch (\PDOException $e) {
                $errorResponse = $e->getMessage();
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
            }
    }

    public function editBusinessTiming(Request $request){
        \Log::info($request->all());
            try{
    
    
                $request->validate([
                  //  'restaurant_name' => 'required',
                    'opening_time*'=> 'required|date_format:H:i',
                    'closing_time' => 'required|date_format:H:i',
                    'description'  =>'required|min:50',
                   // 'business_images.*'=> 'required',
                    //'min_age'   =>'required',
                    //'max_age'   =>'required'
                    ]);
                
                 } catch (\Illuminate\Validation\ValidationException $e) {
                    $errorResponse = $this->ValidationResponseFormating($e);
                    return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
                }
                $user=Auth::user();
                try{
                    DB::beginTransaction();
               
    
              
    
                $status=$this->businessObj->updateBusiness($user->business->id,[
               //     'name'              =>  $request->restaurant_name,
                    'opening_time'     => $request->opening_time,
                    'closing_time'     => $request->closing_time,
                    'description'      => $request->description,
                    'website_link'     => $request->has('website_link')?$request->website_link:'',
                    'min_age'          => $request->min_age,
                    'max_age'          => $request->max_age
    
                ]);
            $user->save(); 
            $refreshUser=$this->userObj->getUserById($user->id);
            $refreshUser->access_token=$this->userObj->getAccessToken($request);
            $updatedUser=$this->userObj->user_resource($refreshUser);
            DB::commit();
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_UPDATE_SUCCESS', 'response',  $updatedUser);
        } catch (\PDOException $e) {
            DB::rollback();
            $errorResponse = $e->getMessage();
            $this->printLog('73','BusinessController',$errorResponse,config('constants.LOG_TYPE.ERROR'));
    
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        }
    
        } //businessProfileImages End here


        public function businessImageAdd(Request $request){
            try{
    
    
                $request->validate([
                    'image_upload'=>"required"
                    ]);
                
                 } catch (\Illuminate\Validation\ValidationException $e) {
                    $errorResponse = $this->ValidationResponseFormating($e);
                    return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
                }
                $user=Auth::user();
                try{
                    DB::beginTransaction();
                if($request->has('image_upload')){
                    //$extension=$request->extension??null;

                    $extention=$request->extension??null;
                 $extensionArray=explode('/',$extention);
                    $extension=$extensionArray[count($extensionArray)-1];
                $imageName=$this->uploadBase64($request->image_upload,env('BUSINESS_IMAGE_UPLOAD_PATH'),$extension);

                $videothu=null;
                if($extension=='mp4'){
                    $videothu=$this->video_thumb($imageName,env('BUSINESS_IMAGE_UPLOAD_PATH'));
                }

                    // if($extension!='mp4'){
                    // $stringParts = explode("/", $extension);
                    // $ext = $stringParts[1];
                    // $imageName=$this->uploadBase64($request->event_asset,env('BUSINESS_IMAGE_UPLOAD_PATH'),$ext);
                    // $videothu=null;
                    // }else{

                    // $imageName=$this->uploadBase64($request->event_asset,env('BUSINESS_IMAGE_UPLOAD_PATH'),$extension);
                    // $videothu=null;
                    // }

                    // if($extension=='mp4'){
                    //     $videothu=$this->video_thumb($imageName,env('BUSINESS_IMAGE_UPLOAD_PATH'));
                    // }
                    $this->businessImageObj->createBusinessImage([
                        'business_id'=> $user->business->id,
                        'image_url'  => $imageName,
                        'video_image'=> $videothu,
                    ]);
                    // $this->businessImageObj->createBusinessImage([
                    //     'business_id'=>$user->business->id,
                    //     'image_url' =>$imageName
                    // ]);
                    }
                    DB::commit();
                    return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_UPLOAD_SUCCESS');
                } catch (\PDOException $e) {
                    DB::rollback();
                    return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
                }    
        }

        public function businessImageDelete(Request $request){
            try{
    
    
                $request->validate([
                    'id'=>"required"
                    ]);
                
                 } catch (\Illuminate\Validation\ValidationException $e) {
                    $errorResponse = $this->ValidationResponseFormating($e);
                    return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
                }
                $user=Auth::user();
                try{
                    DB::beginTransaction();
                    $this->businessImageObj->deleteById($request->id,$user->business->id);
                    DB::commit();
                    return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_DELETE_SUCCESS');
                } catch (\PDOException $e) {
                    DB::rollback();
                    return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
                }    
        }


        public function onOfTargetAudiance(Request $request){
            $user=Auth::user();
            $targetSubscribe=$this->businessObj->purchasedPlanDetail($user->id);
            if(!is_null($targetSubscribe)){
                $user->is_target_on=!$user->is_target_on;
                $user->save();
                 $user->access_token=$this->userObj->getAccessToken($request);
                  $updatedUser =$user->load(['location','business']);
                 $updatedUser1=$this->userObj->user_resource($updatedUser);
                return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'TARGET_AUDIANCE_BUTTON_STATUS_CHANGE','response',  $updatedUser1);
            }else{
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'TARGET_AUDIANCE_SUBSCRIPTION_NOT_PURCHASED');

            }
            dd($targetSubscribe);
        }

       public static function notification(){
        
        $token='ehys4I_2S_2khMCq8QIkQ7:APA91bFDg8KSsBuLjnXrGJuNCH98JhJ80rCs_1U8BR76d1M0JuwTRjXSckEC_4n3iL7N_Zmi8bUVilH7j8a0GZ1d6F9x-5dMXyeJClh-CVaDgNkDSK8CE-poxj04hLwmH86THCT9NWXS';

        $title='Notification';
        $body ='Today Notifications';
        $sound='true';

        $ch = curl_init("https://fcm.googleapis.com/fcm/send"); 
          $notification = array('title' =>$title , 'body' => $body, 'vibrate'=> true, 'sound'=> $sound, 'content-available' => true, 'priority' => 'high'); 
          $data = array('title' => $title, 'body' => $body);
          $arrayToSend = array('to' => $token, 'notification' => $notification, 'data' => $data);
          
           $json = json_encode($arrayToSend); 
           $headers = array();
           $headers[] = 'Content-Type: application/json';
           
           //Doctor
           $headers[] = 'Authorization: key=AAAASQoK9Hg:APA91bGmKnBixCa1QAcinBmQ-WVuUsDYN_2XkKXlME1m2onHM6J9qu0jF_7BR1Vi1Edo4sz1wb5xhk1W9SKdsc-YHbC_Qa5lI9GaFST30VsvvfHAW7OrtD6D7RpAlbbh8mDXaYrETcQP';
           curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
           curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
           curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
           
           curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, true);  
           curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
           curl_setopt($ch, CURLOPT_POST, 1);
      
          $response = curl_exec($ch);
          curl_close($ch);
          
          return $response ;
              
      }  


      public function getContact(Request $request){
        //$data = array('phone_code' => $request->phone_code, );
        $phone=$request['phone_number'];
        
        \Log::info($phone);

        if(!empty($phone)){
        foreach ($phone as $res) {

            
             $str = $res;
             $newres = substr($str, 1);
           //die();
            $data = User::where('role','user')->where('phone_number', $res)->orWhere('phone_number', $newres)->first();

             if(!empty($data->id)){
                $url= URL::to('/');
                $data['profile_image']= $url.'/uploads/images/'.$data->profile_image;
                $rester[]=$data;
             }
        }

        if(!empty($rester)){
         return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',$rester);
        }else{
            $data=User::where('id',0)->get();
       return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',$data);
        }
       }else{
        $data=User::where('id',0)->get();
       return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',$data);
       }
        
      }

}
