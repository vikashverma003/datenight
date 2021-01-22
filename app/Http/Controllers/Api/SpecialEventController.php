<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SpecialEvent;
use App\Models\SpecialEventImage;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\AdvertiseEventSlotPuruchase;
use App\Models\Business;
use App\Models\Setting;

class SpecialEventController extends Controller
{
    use \App\Traits\APIResponseManager;
    use \App\Traits\CommonUtil;

    private $specialEventObj;
    private $specialEventImageObj;
    public function __construct(SpecialEvent $specialEvent,SpecialEventImage $specialEventImage,AdvertiseEventSlotPuruchase $advertiseEventSlotPuruchase,Business $business,Setting $setting){
       
        $this->specialEventObj=$specialEvent;
        $this->specialEventImageObj=$specialEventImage;
        $this->setting=$setting;
        $this->advertiseEventSlotPuruchase=$advertiseEventSlotPuruchase;
        $this->business=$business;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

            try{
  
            $event=$this->specialEventObj->getEvent();
            $user=Auth::user();
            $add=$this->setting->getASP();
            $other['ADVERTISER_SLOT']=['price'=>$add->option_value,'is_purchased'=>$this->advertiseEventSlotPuruchase->isPurchased($user->id)>0?1:0];
            $add=$this->setting->getTMSP();
            $other['TARGET_MARKET']=['price'=>$add->option_value,'is_purchased'=>$this->business->isPurchased($user->id)>0?1:0];
           
            $data=collect(['event'=> $event,'addition'=>$other]);
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response', $data);
 
         } catch (\PDOException $e) {
     
             return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
         }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        try{
            $request->validate([
                'name'      =>  'required',
                //'date'      => 'required|date_format:m-d-Y',
                //'start_time'=> 'required|date_format:H:i',
                //'end_time' => 'required|date_format:H:i',
              //  'description'  =>'required|min:50',
                'website_name' =>'required',
                'event_images.*'=> 'required',
              //  'location_id'   =>'required'
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            try{
                $date= \DateTime::createFromFormat('m-d-Y', $request->date);
            DB::beginTransaction();
            $loggedInUser=Auth::user();
            if(!empty($request->date)){
                $dt=$date->format('Y-m-d');
            }else{
                $dt='';
            }
            if(!empty($request->start_time)){
                $start=$request->start_time;
            }else{
                $start='';
            }
            if(!empty($request->end_time)){
                $end=$request->end_time;
            }else{
               $end='';
            }
          
            $eventCreated=$this->specialEventObj->createEvent([
                'user_id'      =>$loggedInUser->id,
                'name'         => $request->name,
                'business_id'  =>  $loggedInUser->business->id ,
                'event_type'   =>  $request->event_type,
                'date'         =>   $dt,
                'start_time'   => $start,
                'end_time'     => $end,
                'description'  =>$request->description??null,
                'valid_for_date'  =>  null,
                'website_name'=>$request->website_name,
                'location_id'=>$loggedInUser->business->location,
            ]);
           
            if($request->has('event_images')){
                if(count($request->event_images)>0){
                    foreach($request->event_images as $key=> $image){
                        $extention=$request->extension[$key]??'png';
                    $extensionArray=explode('/',$extention);
                    $extension=$extensionArray[count($extensionArray)-1];
                $imageName=$this->uploadBase64($image,env('BUSINESS_IMAGE_UPLOAD_PATH'),$extension);

                $videothu=null;
                if($extension=='mp4'){
                    $videothu=$this->video_thumb($imageName,env('BUSINESS_IMAGE_UPLOAD_PATH'));
                }

                $this->specialEventImageObj->createEventImage([
                    'special_event_id'=> $eventCreated->id,
                    'file_name'       => $imageName,
                    'video_image'     => $videothu??null,
                    'active'          =>1
                ]);
                    }
                }
                }
            DB::commit();

            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_UPDATE_SUCCESS', 'response',$eventCreated);
        } catch (\PDOException $e) {
            DB::rollback();
            $errorResponse = $e->getMessage();
            $this->printLog('73','BusinessEventController',$errorResponse,config('constants.LOG_TYPE.ERROR'));
    
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateEvent(Request $request)
    {
        try{
            $request->validate([
                'id'        =>'required|exists:special_events,id',
                'name'      =>  'required',
               // 'date'      => 'required|date_format:m-d-Y',
               // 'start_time'=> 'required|date_format:H:i',
                //'end_time' => 'required|date_format:H:i',
              //  'description'  =>'required|min:50',
                'website_name' =>'required',
            //    'event_images.*'=> 'required',
            //    'location_id'   =>'required'
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            try{
                $date= \DateTime::createFromFormat('m-d-Y', $request->date);
            DB::beginTransaction();
            $loggedInUser=Auth::user();
            if(!empty($request->date)){
            $dt=$date->format('Y-m-d');
            }else{
            $dt='';
            }

            if(!empty($request->start_time)){
                 $start=$request->start_time;
            }else{
                $start='';
            }

            if(!empty($request->end_time)){
                 $end=$request->end_time;
            }else{
                $end='';
            }

       
            $eventCreated=$this->specialEventObj->updateEvent($request->id,[
             //   'user_id'           =>$loggedInUser->id,
                'name'         => $request->name,
              //  'business_id'  =>  $loggedInUser->business->id ,
            //    'event_type' =>  $request->event_type,
                'date'  =>   $dt,
                'start_time'      =>  $start,
                'end_time'    => $end,
                'description'  =>$request->description??null,
                'valid_for_date'  =>  null,
                'website_name'=>$request->website_name,
                'location_id'=>$loggedInUser->business->location,
            ]);
          
            DB::commit();
          
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'EVENT_UPDATE_SUCCESS', 'response', $eventCreated);
        } catch (\PDOException $e) {
            DB::rollback();
            $errorResponse = $e->getMessage();
        
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       
            try{
            DB::beginTransaction();
          //  $loggedInUser=Auth::user();
        $this->specialEventImageObj->deleteImages($id);
        $this->specialEventObj->deleteEvent($id);
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
        try{


            $request->validate([
                'event_asset'=>"required",
                'event_id'   =>"required|exists:special_events,id"
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            $user=Auth::user();
            try{
                DB::beginTransaction();
            if($request->has('event_asset')){
        
                $extention=$request->extension??null;
                 $extensionArray=explode('/',$extention);
                    $extension=$extensionArray[count($extensionArray)-1];
                $imageName=$this->uploadBase64($request->event_asset,env('BUSINESS_IMAGE_UPLOAD_PATH'),$extension);

                $videothu=null;
                if($extension=='mp4'){
                    $videothu=$this->video_thumb($imageName,env('BUSINESS_IMAGE_UPLOAD_PATH'));
                }
                $this->specialEventImageObj->createEventImage([
                    'special_event_id'=> $request->event_id,
                    'file_name'=> $imageName,
                    'video_image'  => $videothu??null,
                    'active'=>1
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
                'id'=>"required|exists:special_event_images,id"
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            $user=Auth::user();
            try{
                DB::beginTransaction();
                $this->specialEventImageObj->deleteById($request->id);
                DB::commit();
                return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'BUSINESS_PROFILE_DELETE_SUCCESS');
            } catch (\PDOException $e) {
                DB::rollback();
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
            }    
    }
}
