<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;
use App\Models\Business;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Term;
use App\Models\About;
use App\Models\Contact;
use App\Models\Privacy;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use DB;
use URL;
use Twilio\Rest\Client;
use App\Repositories\Interfaces\LocationRepositoryInterface;

class UserController extends Controller
{
    use \App\Traits\APIResponseManager;
    use \App\Traits\CommonUtil;

    protected $userObj;
    protected $businessObj;
   

    public function __construct(User $user,Business $business,LocationRepositoryInterface $location)
    {
        $this->userObj=$user;
        $this->businessObj=$business;
        $this->location= $location;
    }
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {
        if($request->role==config('constants.role.ADVERTISER')){
             $dataa=[
            'first_name'=>"required_if:role,==,config('constants.role.USER')",
            'last_name'=>"required_if:role,==,config('constants.role.USER')",
            'name' => "required_if:role,==,config('constants.role.ADVERTISER')",
            'email' => 'required|unique:users,email',
            'password' => 'required|min:6',
            'role'         => ["required",Rule::in([config('constants.role.USER'),config('constants.role.BUSINESS'),config('constants.role.ADVERTISER')])],
            'city'=> "required_if:role,==,config('constants.role.USER')",
            'phone_code'=>'required',
            'phone_number'=>'required',
            'date_of_birth'=>"required_if:role,==,config('constants.role.USER')|date_format:Y-m-d",
            'device_token' => 'string',
            'location' => 'required|exists:locations,id',
            'business_username'=>"required_if:role,==,config('constants.role.ADVERTISER')",
            // 'location'=>"required_if:role,==,config('constants.role.BUSINESS')",
            'address'=>"required_if:role,==,config('constants.role.ADVERTISER')",
            'website_link'=>"required_if:role,==,config('constants.role.ADVERTISER')",
            'profile_image'=>"required_if:role,==,config('constants.role.ADVERTISER')"
            
            ];

        }else{
            $dataa=[
            'first_name'=>"required_if:role,==,config('constants.role.USER')",
            'last_name'=>"required_if:role,==,config('constants.role.USER')",
            'name' => "required_if:role,==,config('constants.role.BUSINESS')",
            'email' => 'required|unique:users,email',
            'password' => 'required|min:6',
            'role'         => ["required",Rule::in([config('constants.role.USER'),config('constants.role.BUSINESS'),config('constants.role.ADVERTISER')])],
            'city'=> "required_if:role,==,config('constants.role.USER')",
            'phone_code'=>'required',
            'phone_number'=>'required',
            'date_of_birth'=>"required_if:role,==,config('constants.role.USER')|date_format:Y-m-d",
            'device_token' => 'string',
            'location' => 'required|exists:locations,id',
            'business_username'=>"required_if:role,==,config('constants.role.BUSINESS')",
            // 'location'=>"required_if:role,==,config('constants.role.BUSINESS')",
            'address'=>"required_if:role,==,config('constants.role.BUSINESS')",
            'website_link'=>"required_if:role,==,config('constants.role.BUSINESS')",
            'profile_image'=>"required_if:role,==,config('constants.role.BUSINESS')"
            
            ];
        }
        
        try{
        $request->validate($dataa);
        
		 } catch (\Illuminate\Validation\ValidationException $e) {
            $errorResponse = $this->ValidationResponseFormating($e);
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
        }

		try{
        DB::beginTransaction();
        $imageName=null;
                // if ($request->hasFile('profile_image')) {
                //     $image = $request->file('profile_image');
                //     $imageName=$this->uploadGalery($image);
                // }
                if($request->has('profile_image')){
                $imageName=$this->uploadBase64($request->profile_image);
                }
            $createdUser=$this->userObj->createUser([
                'name'          =>  $request->name??$request->first_name,
                'last_name'     =>  $request->last_name??null,
                'email'         =>  $request->email,
                'password'      =>  $request->password,
                'role'          =>  $request->role,
                'phone_code'    =>  $request->phone_code,
                'phone_number'  =>  $request->phone_number,
                'date_of_birth' =>  $request->date_of_birth??null,
                'device_token'  =>  $request->device_token??null,
                'city'          =>  $request->city??null,
                'profile_image' =>  $imageName,
                'lat'           =>  $request->lat??null,
                'lng'           =>  $request->lng??null,
                'location_id'   =>  $request->location??null,
                'timezone'      =>  $request->timezone??null,
                'social_type'   =>  $request->has('social_type')?$request->social_type:null,
                'social_id'     =>  $request->has('social_id')?$request->social_id:null
            ]);

            if($request->role==config('constants.role.BUSINESS') || $request->role==config('constants.role.ADVERTISER')){
                $business=$this->businessObj->createBusiness([
                    'user_id'  =>  $createdUser->id,
                    'username' =>  $request->business_username??null,
                    'location'  => $request->location??null,
                    'lat'      =>  $request->lat??null,
                    'lng'       => $request->lng??null,
                    'timezone'  => $request->timezone??null,
                    'address'  =>  $request->address??null,
                    'website_link' =>  $request->website_link??null
                ]);
            }


          
            $createdUser =$createdUser->load(['location','business']);
            $token=$this->userObj->createPassportToken($createdUser);
            $otp=rand(100000,999999);
            $ns='+';

           if(!empty($request->phone_code)){
            
            if($request->role =='user'){

                $number =$ns.$request->phone_code.$request->phone_number;
                $account_sid = getenv("TWILIO_SID");
                $auth_token = getenv("TWILIO_AUTH_TOKEN");
                $twilio_number = getenv("TWILIO_NUMBER");
                $client = new Client($account_sid, $auth_token);
                $data=$client->messages->create($number, ['from' => $twilio_number, 'body' =>  $otp]);
            }

            }
            $createdUser->access_token=$token;
            $createdUser->otp=$otp;
         DB::commit();   
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'USER_REGISTER_SUCCESS', 'response',  $createdUser);
    } catch (\PDOException $e) {
        DB::rollback();
        $errorResponse = $e->getMessage();
        \Log::info($errorResponse);
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
     
    }
  
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        \Log::info($request->all());
		 try{
			// Log::info($_SERVER['HTTP_USER_AGENT']);
			// Log::info(json_encode($request->all()));
        $request->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required|string',
            'device_token'=>'string',
           // 'role'         => ["required",Rule::in([config('constants.role.USER'),config('constants.role.BUSINESS'),config('constants.role.ADVERTISER')])]
            ]);
        
		 } catch (\Illuminate\Validation\ValidationException $e) {
            $errorResponse = $this->ValidationResponseFormating($e);
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 
            'BAD_REQUEST', 'error_details', $errorResponse);
        }

		try{

           // dd($request->all());

                if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                    return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'USER_PASSWORD_WRONG', 'response','');
                }
                $user=Auth::user();

                 User::where('id',$user->id)->update([
                        'device_token'   => $request->device_token,
                       
            ]);

                // if($user->account_status != config('constants.account_status.ACTIVE')){
                //     return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'ACCOUNT_NOT_VERIFY', 'error_details','');
                // }

                if($user->delete_status == 1){
                    return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'EMAIL_NOT_EXIST', 'error_details','');
                }

                if($user->role != 'user'){
                if($user->approved_status != 1){
                    return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'ACCOUNT_NOT_VERIFY', 'error_details','');
                }
                }

              
                
                $user->device_token=$request->device_token??null;
                $user->lat=$request->lat??null;
                $user->lng=$request->lng??null;
                $user->save();
                $token=$this->userObj->createPassportToken($user);
                $user->access_token=$token;
                $updatedUser =$user->load(['location','business']);
                $updatedUser=$this->userObj->user_resource($updatedUser);

                User::where('id',$updatedUser->id)->update([
                        'device_token'   => $request->device_token,
                       
            ]);

        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'USER_LOGIN_SUCCESS', 'response', $updatedUser);
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
		 
	 }
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
		try{
			$request->user()->device_token=null;
			$request->user()->save();
        $request->user()->token()->revoke();
		 return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'USER_LOGOUT_SUCCESS');
         // return response()->json([
             // 'message' => 'Successfully logged out'
        // ]);
		} catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
    }
  
    
    public function  imageUpload(Request $request){
        try{
            $request->validate([
                'image' => 'required',
            ]);
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }

            $user=Auth::user();
            try{
                $imageName=null;
                // if ($request->hasFile('image')) {
                //     $image = $request->file('image');
                //     $imageName=$this->uploadGalery($image);
                // }
                if($request->has('image')){
                    $imageName=$this->uploadBase64($request->image);
                    }
            $user->profile_image=$imageName;
            $user->registration_step=config('constants.registration_step.SECOND');
            $user->save();
            $user->access_token=$this->userObj->getAccessToken($request);
            $updatedUser=$this->userObj->user_resource($user);
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'USER_IMAGE_UPLOAD_SUCCESS', 'response',  $updatedUser);
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
    }

    public function skipImageUpload(Request $request){
        try{
        $user=Auth::user();
        $user->registration_step=config('constants.registration_step.SECOND');
        $user->save();

        $user->access_token=$this->userObj->getAccessToken($request);
        $updatedUser=$this->userObj->user_resource($user);

        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'USER_IMAGE_SKIP_UPLOAD_SUCCESS', 'response',  $updatedUser);

    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
    }

    public function locations(Request $request){
        try{

            $locations=Location::orderBy('name','ASC')->get();
            $data=array();

            foreach ($locations as $rese) {
               $hash=ucwords($rese->hash_tag);
               $data[]=array('id'=>$rese->id,
                             'name'=>$rese->name,
                             'hash_tag'=>$hash,
                              );
            }

        //$locationss=$this->location->all();
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }

    }

    public function editUserProfile(Request $request){
        try{
            $request->validate([
               'first_name'=>'required',
               'last_name'=>'required',
               'contact_no'=>'required',
               'location'=>'required|exists:locations,id',
               'dob'    =>'required|date_format:m-d-Y',
            //  /  'profile_image'=>"required"
            //   'email'=>'required'
             ]);
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }  
            $user=Auth::user();
                // if ($request->hasFile('profile_image')) {
                //     $image = $request->file('profile_image');
                //     $imageName=$this->uploadGalery($image);
                //     $user->profile_image= $imageName;
                // }
                if($request->has('profile_image')){
                    $imageName=$this->uploadBase64($request->profile_image);
                    $user->profile_image= $imageName;
                    }
           // dd($request->all());
          
            try{
            DB::beginTransaction();
            $date= \DateTime::createFromFormat('d-m-Y', $request->dob);
            $user->name=$request->first_name;
            $user->last_name=$request->last_name;
            $user->phone_number=$request->contact_no;
            $user->date_of_birth=$date->format('Y-m-d');
            $user->location_id=$request->location;
            $user->email=$request->email;
            $user->save();
            DB::commit();
            $user->access_token=$this->userObj->getAccessToken($request);
             $updatedUser=$user->load(['location']);
            $updatedUser=$this->userObj->user_resource($updatedUser);
           
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'PROFILE_EDIT_SUCCESS', 'response',  $updatedUser);
        } catch (\PDOException $e) {
            DB::rollback();
            $errorResponse = $e->getMessage();
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        }


    }

    public function editBusinessProfile(Request $request){
        \Log::info($request->all());
        try{
            $request->validate([
               'name'=>'required',
               'business_username'=>'required',
               'contact_no'=>'required',
               'location'=>'required|exists:locations,id',
               'address'    =>'required',
               'website_link'=>'required'
             ]);
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
           // dd($request->all());
            $user=Auth::user();
            // if ($request->hasFile('profile_image')) {
            //     $image = $request->file('profile_image');
            //     $imageName=$this->uploadGalery($image);
            //     $user->profile_image= $imageName;
            // }
            if($request->has('profile_image')){
                $imageName=$this->uploadBase64($request->profile_image);
                $user->profile_image= $imageName;
                }
            try{
                DB::beginTransaction();
            
            $date= \DateTime::createFromFormat('d-m-Y', $request->dob);
            $user->name=$request->name;
            $user->phone_number=$request->contact_no;
            $user->address=$request->address;
            $user->email=$request->email;
            $user->location_id=$request->location;
            $user->save();
            $business=$this->businessObj->getBusinessByUserId($user->id);
            $business->username=$request->business_username;
            $business->website_link=$request->website_link;
            $business->save();
           
            DB::commit();
            $updateUserDAta=$this->userObj->getUserByIdWithLoad($user->id,['business','location']);
          // $updateUserDAta1 =$updateUserDAta->load(['business']);
            $updateUserDAta->access_token=$this->userObj->getAccessToken($request);
            $updatedUser=$this->userObj->user_resource($updateUserDAta);
            
            return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'PROFILE_EDIT_SUCCESS', 'response',  $updatedUser);
        } catch (\PDOException $e) {
            DB::rollback();
            $errorResponse = $e->getMessage();
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
        }


    }

    public function changePassword(Request $request){
        try{
            $request->validate([
               'old_password'=>'required',
               'new_password'=>'min:6|required_with:confirm_password|same:confirm_password',
               'confirm_password'=>'min:6',
             ]);
             } catch (\Illuminate\Validation\ValidationException $e) {
                $errorResponse = $this->ValidationResponseFormating($e);
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'BAD_REQUEST', 'error_details', $errorResponse);
            }
            try{
                $user=Auth::user();
                DB::beginTransaction();
                if (\Hash::check($request->old_password, $user->password)) { 
                    $user->fill([
                     'password' => \Hash::make($request->new_password)
                     ])->save();
                     DB::commit();
                    }else{
                        DB::rollback();
                        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'OLD_PASSWORD_NOT_MATCH_ERROR');
                    }
                 
                    return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'PASSWORD_UPDATE_SUCCESS', 'response',[]);
            }catch (\PDOException $e) {
                DB::rollback();
                $errorResponse = $e->getMessage();
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
            }
    }

    public function (Request $request){
        try{onOffNotification
         
        $user=Auth::user();
       $user->is_notify=$user->is_notify==1?0:1;
        $user->save();
        $updatedUser =$user->load(['location','business']);
        $user->access_token=$this->userObj->getAccessToken($request);
        $updatedUser=$this->userObj->user_resource($user);

        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'USER_IMAGE_SKIP_UPLOAD_SUCCESS', 'response',  $updatedUser);

    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
    }

    public function ContactUs(Request $request){
        try{
         
        $user=Auth::user();
        $data[]=[
            'icon'=>'',
            'type'=>1,
            'title'=>'Call Now',
            'des' =>'691-156-7244'
        ];
        $data[]=[
            'icon'=>'',
            'type'=>2,
            'title'=>'Mail Us',
            'des' =>'support@datenight.com'
        ];
        $data[]=[
            'icon'=>'',
            'type'=>3,
            'title'=>'Follow us on instagram',
            'des' =>'@datenight'
        ];


        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);

    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
    }

    public function termCondition(){
        return view('api.term-condition');
    }
    public function aboutus(){
        return view('api.about');
    }

    public function checkSocialUserExist(Request $request){
        try{
			// Log::info($_SERVER['HTTP_USER_AGENT']);
			// Log::info(json_encode($request->all()));
        $request->validate([
            'social_id' => 'required',
            'social_type' => ["required",Rule::in([config('constants.social_type.GOOGLE'),config('constants.social_type.INSTAGRAM'),config('constants.social_type.APPLE_LOGIN')])],
           
            ]);
        
		 } catch (\Illuminate\Validation\ValidationException $e) {
            $errorResponse = $this->ValidationResponseFormating($e);
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 
            'BAD_REQUEST', 'error_details', $errorResponse);
        }

		try{
            
            $existuser=$this->userObj->checkSocial( $request->all());

            if(is_null($existuser)){
               return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'SOCIAL_USER_NOT_EXIST');
            }
            // To log a user into the application by their ID, use the loginUsingId method:

                Auth::loginUsingId($existuser->id);
                $user=Auth::user();
                $user->device_token=$request->device_token??null;
                $user->lat=$request->lat??null;
                $user->lng=$request->lng??null;
                $user->save();
                $token=$this->userObj->createPassportToken($user);
                $user->access_token=$token;
                $updatedUser =$user->load(['location','business']);
                $updatedUser=$this->userObj->user_resource($updatedUser);
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'USER_LOGIN_SUCCESS', 'response', $updatedUser);
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
		
    }

    public function notificationList(Request $request){
       
      try{
           
        $request->validate([
            'user_id' => 'required',
        ]);
        
         } catch (\Illuminate\Validation\ValidationException $e) {
            $errorResponse = $this->ValidationResponseFormating($e);
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 
            'BAD_REQUEST', 'error_details', $errorResponse);
        }

    try{
           $data= Notification::where('user_id',$request->user_id)->orderBy('id', 'DESC')->get();
           foreach ($data as $res) {
              if(!empty($res->profile_image)){
               $url= URL::to('/');
               $res['profile_image']=$url.'/uploads/images/'.$res->profile_image;
               }else{
                $res['profile_image']='';
               }
           }

       return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }
    }
    
    public function updateNotification(Request $request){

         try{
           
        $request->validate([
            'user_id' => 'required',
        ]);
        
         } catch (\Illuminate\Validation\ValidationException $e) {
            $errorResponse = $this->ValidationResponseFormating($e);
            return $this->responseManager(Config('statuscodes.request_status.ERROR'), 
            'BAD_REQUEST', 'error_details', $errorResponse);
        }

        try{
         
            $data= Notification::where('user_id',$request->user_id)->update([
                                    'badge'   => '1',
                                    'updated_at'     => new \DateTime,
                       
            ]);

       return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'UPDATE_SUCESS');
    } catch (\PDOException $e) {
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    }

    }

    public function countLocation(Request $request){

       $data = DB::table('businesses')->select(DB::raw('DISTINCT location, COUNT(*) AS count_pid'))->groupBy('location')->orderBy('count_pid', 'desc')->get();
          $location=array();
        foreach ($data as $res) {
           if($res->count_pid >= 40){
            $location[]=location::where('id',$res->location)->where('active',1)->first();
           }
        }
      return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $location);
    }

    public function term(Request $request){
         $data= Term::where('id',1)->first();
         return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);
    }

    public function about(Request $request){
         $data= About::where('id',1)->first();
         return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);
    }

    public function contact(Request $request){
         $data= Contact::where('id',1)->first();
         return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);
    }
    
    public function Privacy(Request $request){
        $data= Privacy::where('id',1)->first();
         return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'INFORMATION_FETCH_SUCCESS', 'response',  $data);
    }

}
