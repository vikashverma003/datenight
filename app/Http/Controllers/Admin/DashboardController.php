<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use DB;
use App\User;
class DashboardController extends Controller
{
    public function __construct(User $user){
        $this->user=$user;
    }

    public function index(){
        $user=Auth::user();
        $businesses = $this->user->countByRole(config('constants.role.BUSINESS'));
        $users = $this->user->countByRole(config('constants.role.USER'));
        $advertiser = $this->user->countByRole(config('constants.role.ADVERTISER'));
        $usersGraph=$this->graphState();
       // print_r($usersGraph);
       // die();
        return view('admin.dashboard', ['title' => 'Dashboard','user'=>$user,'businesses'=>$businesses,'users'=>$users,'advertiser'=>$advertiser,'usersGraph'=>$usersGraph]);
    }
    private function graphState(){
        $grahpData=[];
        $grahpData[]=[
          'label'=>'Businesses',
          'data'=> [],
          'backgroundColor'=> [
            'rgba(0, 0, 0,0.2)',
           ],
          'borderColor'=> [
            'rgba(0, 0, 0,1)'
          ],
          'borderWidth'=> 2,
          
        ];
        $data=[0,0,0,0, 0, 0,0,0,0,0,0,0];
        $f=$this->user->getUserByMonthWise(config('constants.role.BUSINESS'));
        if(sizeOf($f)>0){
            foreach($f as $v){
                $data[$v->Month-1]=(int) $v->total;
            }
        }
        $grahpData[0]['data']=$data;
        
        
        $grahpData[]=[
          'label'=>'Users',
          'data'=> [],
          'backgroundColor'=> [
            'rgb(126, 60, 128,0.2)',
           ],
          'borderColor'=> [
            'rgba(126, 60, 128,1)'
          ],
          'borderWidth'=> 2
        ];
        $data=[0,0,0,0, 0, 0,0,0,0,0,0,0];
        $f=$this->user->getUserByMonthWise(config('constants.role.USER'));
        if(sizeOf($f)>0){
            foreach($f as $v){
                $data[$v->Month-1]=(int) $v->total;
            }
        }
        $grahpData[1]['data']=$data;

        $grahpData[]=[
          'label'=>'Advertiser',
          'data'=> [],
          'backgroundColor'=> [
            'rgb(126, 60, 128,0.2)',
           ],
          'borderColor'=> [
            'rgba(126, 60, 128,1)'
          ],
          'borderWidth'=> 2
        ];
        $data=[0,0,0,0, 0, 0,0,0,0,0,0,0];
        $f=$this->user->getUserByMonthWise(config('constants.role.ADVERTISER'));
        if(sizeOf($f)>0){
            foreach($f as $v){
                $data[$v->Month-1]=(int) $v->total;
            }
        }
        $grahpData[1]['data']=$data;
        return $grahpData;
    }
}
