<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\BusinessEvent;
use App\Models\BusinessEventAsset;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class BusinessEventController extends Controller
{
    use \App\Traits\APIResponseManager;
    use \App\Traits\CommonUtil;

    private $businessObj;
    private $businessEventObj;
    private $businessEventAssetObj;
    public function __construct(Business $business,BusinessEvent $businessEvent,BusinessEventAsset $businessEventAsset){
        $this->businessObj=$business;
        $this->businessEventObj=$businessEvent;
        $this->businessEventAsset=$businessEventAsset;
    }


    public function createEvent(Request $request){
	//\Log::info($request->all());
        try{
            $request->validate([
                'event_name'      =>  'required',
                'event_type' => 'required',
                'date'      => 'required|date_format:m-d-Y',
                'start_time'=> 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'description'  =>'required|min:50',
                'is_regular_basis' =>'required|integer',
                'event_assets.*'=> 'required',
                'recurring_type' => ["required_if:is_regular_basis,1",Rule::in([config('constants.event_recurring_type.MONTHLY'),config('constants.event_recurring_type.WEEKLY')])],
                'week_no'=> "required_if:recurring_type,".config('constants.event_recurring_type.MONTHLY'),
                'week_day'=>"required_if:is_regular_basis,1",

                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            try{
                $date= \DateTime::createFromFormat('m-d-Y', $request->date);
            DB::beginTransaction();
            $loggedInUser=Auth::user();
            $business=$this->businessObj->getBusinessByUserId($loggedInUser->id);
            $eventCreated=$this->businessEventObj->createEvent([
                'event_name'         => $request->event_name,
                'business_id'  =>  $business->id ,
                'event_type' =>  $request->event_type,
                'date'  =>   $date->format('Y-m-d'),
                'start_time'      => $request->start_time,
                'end_time'    => $request->end_time,
                'description'  =>$request->description,
                'is_regular_basis'  =>  $request->is_regular_basis,
                'recurring_type'   =>  $request->recurring_type??null,
                'month_day'       => $request->week_no??null,
                'week_day'       =>$request->week_day??null
            ]);
            // if($files=$request->file('event_assets')){
            //     foreach($files as $file){
            //         $imageName=$this->uploadGalery($file,env('BUSINESS_IMAGE_UPLOAD_PATH'));
            //         $this->businessEventAsset->createEventAsset([
            //             'event_id'=> $eventCreated->id,
            //             'file_name'=> $imageName,
            //             'active'=>1
            //         ]);
            //     }
            // }
            if($request->has('event_assets')){
                if(count($request->event_assets)>0){
                    foreach($request->event_assets as $key=> $image){
                        $extention=$request->extension[$key]??'png';
                    $extensionArray=explode('/',$extention);
                    $extension=$extensionArray[count($extensionArray)-1];
                $imageName=$this->uploadBase64($image,env('BUSINESS_IMAGE_UPLOAD_PATH'),$extension);
                $videothu=null;
                if($extension=='mp4'){
                    $videothu=$this->video_thumb($imageName,env('BUSINESS_IMAGE_UPLOAD_PATH'));
                }
                $this->businessEventAsset->createEventAsset([
                    'event_id'=> $eventCreated->id,
                    'file_name'=> $imageName,
                    'active'=>1,
                    'video_image'=>$videothu
                ]);
                    }
                }
                }
            DB::commit();
            $business=$this->businessObj->getBusinessByUserId($loggedInUser->id,
            ['user','images','businessEvent.assets']);
            $updatedEvent=$this->businessObj->business_resource($business);
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_UPDATE_SUCCESS', 'response', $updatedEvent);
        } catch (\PDOException $e) {
            DB::rollback();
            $errorResponse = $e->getMessage();
            $this->printLog('73','BusinessEventController',$errorResponse,config('constants.LOG_TYPE.ERROR'));
    
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        }
    }

    public function getMyEvents(Request $request){
        try{
            $request->validate([
                'status' => ["required",Rule::in([config("constants.business_event_type.ONGOING"),config("constants.business_event_type.UPCOMMING"),config("constants.business_event_type.PAST")])],
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
           $logedInUser=Auth::user();
           try{
            $timezone=is_null($logedInUser->timezone)?'Asia/Kolkata':$logedInUser->timezone;
           $events=$this->businessEventObj->getEventByBusinessId($logedInUser->business->id,$request->status,$timezone);
        //   $events
           $updatedEvent=$this->businessEventObj->event_resource_collection($events);
           return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_UPDATE_SUCCESS', 'response', $updatedEvent);

        } catch (\PDOException $e) {
            $errorResponse = $e->getMessage();
            $this->printLog('73','BusinessEventController',$errorResponse,config('constants.LOG_TYPE.ERROR'));
    
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        }
           
    }

    public function editEvent(Request $request){
        \Log::info($request->all());
        try{
            $request->validate([
                'event_id'         => 'required',
                'event_name'      =>  'required',
                'event_type' => 'required',
                'date'      => 'required|date_format:m-d-Y',
                'start_time'=> 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'description'  =>'required|min:100',
                'is_regular_basis' =>'required|integer',
               // 'event_assets.*'=> 'required',
                'recurring_type' => ["required_if:is_regular_basis,1",Rule::in([config('constants.event_recurring_type.MONTHLY'),config('constants.event_recurring_type.WEEKLY')])],
                'week_no'=> "required_if:recurring_type,".config('constants.event_recurring_type.MONTHLY'),
                'week_day'=>"required_if:is_regular_basis,1",
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            try{
                $date= \DateTime::createFromFormat('m-d-Y', $request->date);
            DB::beginTransaction();
            $loggedInUser=Auth::user();
            $business=$this->businessObj->getBusinessByUserId($loggedInUser->id);
            $eventCreated=$this->businessEventObj->updateEvent($request->event_id,[
                'event_name'         => $request->event_name,
                'business_id'  =>  $business->id ,
                'event_type' =>  $request->event_type,
                'date'  =>   $date->format('Y-m-d'),
                'start_time'      => $request->start_time,
                'end_time'    => $request->end_time,
                'description'  =>$request->description,
                'is_regular_basis'  =>  $request->is_regular_basis,
                'recurring_type'   =>  $request->recurring_type??null,
                'month_day'       => $request->week_no??null,
                'week_day'       =>$request->week_day??null
            ]);
            // if($files=$request->file('event_assets')){
            //     foreach($files as $file){
            //         $imageName=$this->uploadGalery($file,env('BUSINESS_IMAGE_UPLOAD_PATH'));
            //         $this->businessEventAsset->createEventAsset([
            //             'event_id'=> $eventCreated->id,
            //             'file_name'=> $imageName,
            //             'active'=>1
            //         ]);
            //     }
            // }

            // if($request->has('event_assets')){
            //     if(count($request->event_assets)>0){
            //         foreach($request->event_assets as $image){
            //     $imageName=$this->uploadBase64($image,env('BUSINESS_IMAGE_UPLOAD_PATH'));
            //     $this->businessEventAsset->createEventAsset([
            //         'event_id'=> $eventCreated->id,
            //         'file_name'=> $imageName,
            //         'active'=>1
            //     ]);
            //         }
            //     }
            //     }
            DB::commit();
            $business=$this->businessObj->getBusinessByUserId($loggedInUser->id,
            ['user','images','businessEvent.assets']);
            $updatedEvent=$this->businessObj->business_resource($business);
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'EVENT_UPDATE_SUCCESS', 'response', $updatedEvent);
        } catch (\PDOException $e) {
            DB::rollback();
            $errorResponse = $e->getMessage();
            $this->printLog('73','BusinessEventController',$errorResponse,config('constants.LOG_TYPE.ERROR'));
    
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        }
    }

    public function deleteEvent(Request $request){
        \Log::info($request->all());
        try{
            $request->validate([
                'event_id'         => 'required',
                'delete_type'      => ["required",Rule::in([config('constants.event_delete_type.PERMANENT_DELETE'),config('constants.event_delete_type.DELETE_FOR_NOW')])],
               
             ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            try{
            DB::beginTransaction();
          //  $loggedInUser=Auth::user();
          if($request->delete_type==config('constants.event_delete_type.PERMANENT_DELETE')){
        $this->businessEventAsset->deleteAssest($request->event_id);
        $this->businessEventObj->deleteEvent($request->event_id);
        }else{
            $this->businessEventObj->deleteEventForNow($request->event_id);
        }
        DB::commit();
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'EVENT_DELETE_SUCCESS');
    } catch (\PDOException $e) {
        DB::rollback();
        $errorResponse = $e->getMessage();
        $this->printLog('73','BusinessEventController',$errorResponse,config('constants.LOG_TYPE.ERROR'));

        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
    }


    public function eventAssetAdd(Request $request){
        \Log::info($request->all());
        try{

            $request->validate([
                'event_asset'=>"required",
                'event_id'   =>"required|exists:business_events,id"
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            $user=Auth::user();
            try{

                

                DB::beginTransaction();
            if($request->has('event_asset')){
        
                $extension=$request->extension??null;
               \Log::info($extension);
               if($extension!='mp4'){
                $stringParts = explode("/", $extension);
                $ext = $stringParts[1];
                $imageName=$this->uploadBase64($request->event_asset,env('BUSINESS_IMAGE_UPLOAD_PATH'),$ext);
                $videothu=null;
               }else{
           
                $imageName=$this->uploadBase64($request->event_asset,env('BUSINESS_IMAGE_UPLOAD_PATH'),$extension);
                $videothu=null;
               }
                if($extension=='mp4'){
                    $videothu=$this->video_thumb($imageName,env('BUSINESS_IMAGE_UPLOAD_PATH'));
                }
                $this->businessEventAsset->createEventAsset([
                    'event_id'=> $request->event_id,
                    'file_name'=> $imageName,
                    'active'=>1,
                    'video_image'=>$videothu
                ]);
                }

                DB::commit();
                return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_UPLOAD_SUCCESS');
            } catch (\PDOException $e) {
                DB::rollback();
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
            }    
    }

    public function eventAssetDelete(Request $request){
        try{


            $request->validate([
                'id'=>"required|exists:business_event_assets,id"
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            $user=Auth::user();
            try{
                DB::beginTransaction();
                $this->businessEventAsset->deleteById($request->id);
                DB::commit();
                return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_DELETE_SUCCESS');
            } catch (\PDOException $e) {
                DB::rollback();
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
            }    
    }

    public function getEvent(Request $request){
        try{


            $request->validate([
                'id'   =>"required|exists:business_events,id"
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            try{
  
            $event=$this->businessEventObj->getEventById($request->id);
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_UPDATE_SUCCESS', 'response', $event);
 
         } catch (\PDOException $e) {
     
             return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
         }


    }

}
