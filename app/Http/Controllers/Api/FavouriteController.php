<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
class FavouriteController extends Controller
{
    use \App\Traits\APIResponseManager;
    use \App\Traits\CommonUtil;
    private $businessObj;

    public function __construct(Business $business){
        $this->businessObj=$business;
    }

    public function makeFavourite(Request $request){
        try{
            $request->validate([
                'business_id' => 'required|exists:businesses,id',
             ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }  
            try{

                $user=Auth::user();
              if($user->favBusiness()->where('business_id',$request->business_id)->count()>0){
                $user->favBusiness()->detach($request->business_id);
              }else{
                $user->favBusiness()->attach($request->business_id);
              }
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response', []);
        } catch (\PDOException $e) {
            $errorResponse = $e->getMessage();
            $this->printLog('33','FavouriteController',$errorResponse,config('constants.LOG_TYPE.ERROR'));
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        } 
    }
    public function getUserFavouriteRestourent(Request $request){
        try{
        $user=Auth::user();
        $favBusinesses=$this->businessObj->getuserFavBusinessesList($user->id);
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response', $favBusinesses);
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        $this->printLog('46','FavouriteController',$errorResponse,config('constants.LOG_TYPE.ERROR'));
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    } 

    }
}
