<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Helper;
use App\Models\Location;
use DateTime;
use DB;
use Mail;

class BusinessController extends Controller
{
    public function __construct(User $user){
        $this->user=$user;
    }
    public function index(Request $req){
        $user=Auth::user();
        $data=$req->all();

        //$businesses = $this->user->getUserByRole(config('constants.role.BUSINESS'));
        $businesses = $this->user->filterData(config('constants.role.BUSINESS'),$data);
        //$businesses = User::where('role','business')->with('location')->get();
        // print_r($businesses->count());
        // die();
        return view('admin.business.index', ['title' => 'Business Manager','user'=>$user,'businesses'=>$businesses]);
    }

    public function show(Request $req){
    
    }

    public function edit(Request $request,$id){
        $user=Auth::user();
        $title="Business Manager";
        $business = $this->user->getUserById($id);
        return view('admin.business.edit',['title' => $title,'user'=>$user,'business'=>$business]);
    }

    public function update(Request $req){
        
    }

    public function viewUser(Request $req,$id){
     
     $data=User::where('id',$id)->first();
     if($data->account_status =='1'){

         User::where('id',$id)->update([
                    'account_status'    => '0',
                    'updated_at'     => new \DateTime,   
            ]);
     }else{
         User::where('id',$id)->update([
                    'account_status'    => '1',
                    'updated_at'     => new \DateTime,
                       
            ]);
     }
     return redirect('admin/businesses')->with('status','Please Use following format for Upload image jpg,jpeg,png');
    
    }

    public function viewApproved(Request $req,$id){
   
     $data=User::where('id',$id)->first();
     if($data->approved_status =='1'){
   
         User::where('id',$id)->update([
                    'approved_status'    => '0',
                    'updated_at'         => new \DateTime,   
            ]);
     }else{
         
         User::where('id',$id)->update([
                    'approved_status'    => '1',
                    'updated_at'         => new \DateTime,
                       
            ]);
         $data=User::where('id',$id)->first();
         $token=$data->device_token;
         Helper::SendPushNotifications($token,'DateNight','DateNight account has been approved.',6);

           $test= Mail::send('templates.activate', ['user' => $data], function ($m) use ($data) {
            $m->from('datenight@gmail.com', 'DateNight Application');
           $m->to($data->email,$data->email)->subject('Activate User By Email');
           });
     }
     return redirect('admin/businesses')->with('status','Please Use following format for Upload image jpg,jpeg,png');
    
    }
    public function viewApproveds(Request $req,$id){
   
     $data=User::where('id',$id)->first();
     if($data->approved_status =='1'){
   
        User::where('id',$id)->update([
                    'approved_status'    => '0',
                    'updated_at'     => new \DateTime,   
        ]);
     }else{

         User::where('id',$id)->update([
                    'approved_status'    => '1',
                    'updated_at'     => new \DateTime,   
            ]);
         $data=User::where('id',$id)->first();
         $token=$data->device_token;
         Helper::SendPushNotifications($token,'DateNight','DateNight account has been approved.',5);
     }
     return redirect('admin/advertiser')->with('status','Please Use following format for Upload image jpg,jpeg,png');
    
    }

    

    public function viewUsers(Request $req,$id){
     
     $data=User::where('id',$id)->first();
     if($data->account_status =='1'){
         User::where('id',$id)->update([
                    'account_status'    => '0',
                    'updated_at'     => new \DateTime,   
            ]);
     }else{
         User::where('id',$id)->update([
                    'account_status'    => '1',
                    'updated_at'     => new \DateTime,    
            ]);
     }
     return redirect('admin/advertiser')->with('status','Please Use following format for Upload image jpg,jpeg,png');
    }

    public function advertiser(Request $req){
        $user=Auth::user();

         $data=$req->all();

        //$businesses = $this->user->getUserByRole(config('constants.role.ADVERTISER'));
        $businesses = $this->user->filterData(config('constants.role.ADVERTISER'),$data);
        


        return view('admin.business.viewAdvertiser', ['title' => 'Business Manager','user'=>$user,'businesses'=>$businesses]);
    }

    public function city(Request $req){
        $user=Auth::user();
        $data = Location::get()->sortBy('name');
       
        return view('admin.business.viewCity', ['title' => 'Location','user'=>$user,'data'=>$data]);
    }
    public function updateCity(Request $req,$id){

        $user=Auth::user();
        $data = Location::where('id',$id)->first();
        return view('admin.business.editCity', ['title' => 'Location','user'=>$user,'data'=>$data]);
    }

    public function editCity(Request $req){

        $data=Location::where('id',$req->id)->update([
                    'name'       => $req->name,
                    'hash_tag'   => $req->hash_tag,
                    'updated_at' => new \DateTime,    
            ]);
        return redirect('admin/city')->with('status','Location has been updated successfully.');
    }

    public function addLocation(Request $req){

        $user=Auth::user();
        return view('admin.business.addCity', ['title' => 'Location','user'=>$user]);

    }

    public function createCity(Request $req){
    
      DB::table('locations')->insert(['name' => $req->name, 'hash_tag' => $req->hash_tag,'created_at'=>new \DateTime,'updated_at'=>new \DateTime]);
      return redirect('admin/city')->with('status','Location has been updated successfully.');

    }
}
