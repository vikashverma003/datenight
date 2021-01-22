<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserCard;
use App\User;
use Auth;
use DB;
use App\Helper;
use App\Models\Notification;
use App\Models\PlanSpot;
use App\Models\Transaction;
use App\Models\PurchasedSpots;
use Illuminate\Validation\Rule;
use App\Models\AdvertiseEventSlotPuruchase;
use App\Models\Setting;
use App\Models\Business;
use App\Models\AgeGroup;

class PaymentController extends Controller
{
    use \App\Traits\APIResponseManager;
    use \App\Traits\CommonUtil;
    use \App\Traits\StripeManager;
    public function __construct(UserCard $userCard,PlanSpot $planSpot,Transaction $transaction,PurchasedSpots $purchasedSpots,AdvertiseEventSlotPuruchase $advertiseEventSlotPuruchase,Setting $setting,Business $business,AgeGroup $ageGroup){
        $this->userCard=$userCard;
        $this->planSpot=$planSpot;
        $this->transaction=$transaction;
        $this->purchasedSpots=$purchasedSpots;
        $this->advertiseEventSlotPuruchase=$advertiseEventSlotPuruchase;
        $this->setting=$setting;
        $this->business=$business;
        $this->ageGroup=$ageGroup;
    }

    public function addCardToStripe(Request $request){
        \Log::info($request->all());
        try{
            $request->validate([
                'token' => 'required',
                'card_type'=>'required'
                ]);
            
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
        $user=Auth::user();
        try{
       self::stripeInit();
      $customer= self::addCard($request->token,$user->email);
      if($customer['status']==1){
      \Log::info($customer);
      $data=$this->userCard->createCard([
        'user_id'=>  $user->id,
        'customer_id'=> $customer['data']['id'],
        'brand'=>$customer['data']['sources']['data'][0]['brand'],
        'last4'=>$customer['data']['sources']['data'][0]['last4'],
        'name'=>$customer['data']['sources']['data'][0]['name'],
        'exp_month'=>$customer['data']['sources']['data'][0]['exp_month'],
        'exp_year'=>$customer['data']['sources']['data'][0]['exp_year'],
        'card_id'=>$customer['data']['sources']['data'][0]['id'],
        'card_type'=>$request->card_type
    ]);

    return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);
      }else{
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'STRIPE_ERROR', 'error_details', $customer['data']);
      }
        } catch (\PDOException $e) {
            DB::rollback();
            $errorResponse = $e->getMessage();
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        }   
    }

    public function getCards(Request $request){
        $user=Auth::user();
        $cards=$this->userCard->getAllByUserId($user->id);
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $cards);
    }

    public function setDefaultCard(Request $request){
        $user=Auth::user();
        $this->userCard->setDefault($user->id,$request->card_id);
        $cards=$this->userCard->getAllByUserId($user->id);
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $cards);
    }

    public function purchaseSlot(Request $request){
	\Log::info($request->all());
     /*
     spot=1
     special event=>2,
     age target=3
      $payment_type= [ 1 	=>	1
        1,2 	=>	2
        1,2,3	=>	3
        2	=>	4
        2,3	=>	5
        3	=>	6
        1,3	=>	7
    ];*/
    if(in_array($request->payment_type,[1,2,3,7])){
        $validation=[
            'card_id' => 'required|exists:user_cards,id',
            'spot_id'=>'required|exists:plan_spots,id',
            'payment_type'=>["required",Rule::in([1,2,3,4,5,6,7])]
        ];
    }else{
        $validation=[
            'card_id' => 'required|exists:user_cards,id',
            'payment_type'=>["required",Rule::in([1,2,3,4,5,6,7])]
        ];
    }
    if(in_array($request->payment_type,[3,5,6,7])){
        $validation['age_group.*']='required';
    }
        try{

            $request->validate($validation);
            
             }catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
        $user=Auth::user();
        $cardDetail=$this->userCard->getById($request->card_id,$user->id);
        if(is_null($cardDetail)){
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'USER_CARD_NOT_EXIST');
        }
        $slot_price=0;
        $avertiseSlotPrice=0;
        $targetAgePrice=0;

       if(in_array($request->payment_type,[1,2,3,7])){
        $myplans=$this->purchasedSpots->isSlotPurchased($user->id);
        if(!is_null($myplans)){
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'SLOT_ALREADY_PURCHASED');
        }
        
        $planSpot=$this->planSpot->getById($request->spot_id);
       
        $isAvaiable=$this->planSpot->isSlotAvaliable($planSpot->id);
        if( $isAvaiable==0){
             return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'SLOT_ALREADY_PURCHASED');
        }
        $slot_price=$planSpot->price;
    }
    if(in_array($request->payment_type,[2,3,4,5])){
        if($this->advertiseEventSlotPuruchase->isPurchased($user->id)>0){
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'ADERVERTISER_SLOT_ALREADY_PURCHASE');
        }
       $detail= $this->setting->getASP();
       $avertiseSlotPrice=$detail->option_value??10;
    }

    if(in_array($request->payment_type,[3,5,6,7])){
        if($this->business->isPurchased($user->id)>0){
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'TARGET_AUDIANCE_ALREADY_PURCHASE');
        }
        $detail= $this->setting->getTMSP();
        $targetAgePrice=$detail->option_value??20;
     }
      $total=$targetAgePrice+$avertiseSlotPrice+$slot_price;

        self::stripeInit();
        $charge=self::makePayment($cardDetail->customer_id,$total);
        \Log::info(json_encode($charge));

        if($charge['status']==1){
            if(isset($charge['data']['id'])){
                if(in_array($request->payment_type,[1,2,3,7])){
                $enddate = strtotime("+7 day");
                $purchasedSpot=$this->purchasedSpots->createPurchasedPlan([
                    'user_id'=>$user->id,
                    'plan_spot_id'=>$request->spot_id,
                    'start'=>date('Y-m-d H:i:s'),
                    'end'   =>date('Y-m-d H:i:s', $enddate)
                ]);
                }
                if(in_array($request->payment_type,[2,3,4,5])){
                    $enddate = strtotime("+7 day");
                    $this->advertiseEventSlotPuruchase->createAdvertise([
                        'user_id'=>$user->id,
                        'business_id'=>$user->business->id,
                        'start'=>date('Y-m-d H:i:s'),
                        'end'   =>date('Y-m-d H:i:s', $enddate)
                    ]);
                }
                if(in_array($request->payment_type,[3,5,6,7])){
                    
                    $enddate = strtotime("+30 day");
                    $business=$this->business->getBusinessByUserId($user->id);
                    $business->is_target_age_rage=1;
                    $business->target_age_rage_expire_date=date('Y-m-d H:i:s', $enddate);
                    foreach($request->age_group as $age_group){
                    $ageGroupA=explode('-',$age_group);
                    $this->ageGroup->createAgeGroup([
                        'user_id'     =>  $user->id,
                        'business_id' => $business->id,
                        'min_age'     =>   $ageGroupA[0],
                        'max_age'     =>   $ageGroupA[1]
                    ]);
                    }
                    $business->save();
                    $user->is_target_on=1;
                    $user->save();
                }
                $payInfo=$this->transaction->createTransaction([
                    'user_id'=>$user->id,
                    'plan_spot_id'=>$request->spot_id??null,
                    'purchased_spots_id'=>$purchasedSpot->id??null,
                    'amount'   => $total,
                    'user_card_id'=>$cardDetail->customer_id,
                    'stripe_transaction_id'=>$charge['data']['id']
                
                ]);
                $info=Auth::user();
                $data=User::where('id',$info->id)->first();

                $token=$data->device_token;
                // Helper::SendPushNotifications($token,'Spot Purchased','Spot Purchased Successfully.');
                //  $info= DB::table('notification')->insert([
                //                'type'               => '2',
                //                'title'              => 'Spot Purchased',
                //                'action_id'          => $payInfo->id??null,
                //                'message'            => 'Spot Purchased Successfully.',
                //                'user_id'            => $info->id,
                //                'created_at'         => new \DateTime,
                //                'updated_at'         => new \DateTime, 
                //               ]);

               
                return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'SPOT_PURCHASE_SUCCESS', 'response',[]);
            }else{
            	return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'STRIPE_ERROR');
            }
        }else{
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'STRIPE_ERROR', 'error_details', $charge['data']);
        }
    

    }



}
