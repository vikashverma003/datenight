<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use Auth;
use App\Models\PurchasedSpots;
use App\Models\Setting;
use App\Models\AdvertiseEventSlotPuruchase;
use App\Models\Business;
use App\Models\AgeGroup;
use App\Models\PlanSpot;
use DateTime;

class PlanController extends Controller
{

    use \App\Traits\APIResponseManager;
    use \App\Traits\CommonUtil;

    public function __construct(Plan $plan,PurchasedSpots $purchasedSpots,Setting $setting,AdvertiseEventSlotPuruchase $advertiseEventSlotPuruchase,Business $business,AgeGroup $ageGroup,PlanSpot $planSpot){
        $this->plan=$plan;
        $this->purchasedSpots=$purchasedSpots;
        $this->setting=$setting;
        $this->advertiseEventSlotPuruchase=$advertiseEventSlotPuruchase;
        $this->business=$business;
        $this->ageGroup=$ageGroup;
        $this->planSpot=$planSpot;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans=$this->plan->getAll();
        $remaingCount=  $this->planSpot->getRemaingSlot();
        $today = date("F"); 
        $a='plan';
        foreach ($plans as $res) {
           $res->name=$today.' '.$a;
        }   
       
$data=collect(['plans'=> $plans,'remaingCount'=>$remaingCount]);
  return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user=Auth::user();
       $plans=$this->plan->getById($id);
       //print_r($plans);die();
       $remaingCount=  $this->planSpot->getRemaingSlot();
       $add=$this->setting->getASP();
       $other['ADVERTISER_SLOT']=['price'=>$add->option_value,'is_purchased'=>$this->advertiseEventSlotPuruchase->isPurchased($user->id)>0?1:0];
       $add=$this->setting->getTMSP();
        $ageGroups=$this->ageGroup->getOwnAgeGroup($user);
       $other['TARGET_MARKET']=['price'=>$add->option_value,'is_purchased'=>$this->business->isPurchased($user->id)>0?1:0,'ageGroups'=> $ageGroups];
       $myplans=$this->purchasedSpots->isSlotPurchased($user->id);
       $other['SLOT_DETAIL']=['slot_no'=>!is_null($myplans)?$myplans->planSpot->spot_no:null,'is_purchased'=>!is_null($myplans)?1:0,'remaing_slot_count'=> $remaingCount];
       $data=collect(['plan'=> $plans,'addition'=>$other]);
  return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function myPlan(Request $request){
        $user=Auth::user();
        $plans=$this->purchasedSpots->userPurchasedSlot($user->id);
        $add=$this->setting->getASP();

        $Purcheddata=Plan::first();
        $today = date("F"); 
        $a='plan';
        $Purcheddata['name']=$today.' '.$a;
        //print_r($plans);die();

        $name=$Purcheddata->name;
        $other['ADVERTISER_SLOT']=['price'=>$add->option_value,'name'=>$name,'is_purchased'=>$this->advertiseEventSlotPuruchase->isPurchased($user->id)>0?1:0,'detail'=>$this->advertiseEventSlotPuruchase->purchasedPlanDetail($user->id)];

        $add=$this->setting->getTMSP();
        $other['TARGET_MARKET']=['price'=>$add->option_value,'name'=>$name,'is_purchased'=>$this->business->isPurchased($user->id)>0?1:0,
        'detail'=>$this->business->purchasedPlanDetail($user->id)];

        $myplans=$this->purchasedSpots->isSlotPurchased($user->id);
        $other['SLOT_DETAIL']=['slot_no'=>!is_null($myplans)?$myplans->planSpot->spot_no:null,'is_purchased'=>!is_null($myplans)?1:0,
        'detail'=>$myplans];
        $data=collect(['plan'=> $plans,'addition'=>$other]);

        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);

    }
}
