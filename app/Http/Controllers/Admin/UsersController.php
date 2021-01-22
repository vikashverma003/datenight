<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function __construct(User $user){
        $this->user=$user;
    }
    public function index(Request $req){
        $user=Auth::user();
        $data=$req->all();

        //$users = $this->user->getUserByRole(config('constants.role.USER'));
        $users = $this->user->filterData(config('constants.role.USER'),$data);
       

        return view('admin.users.index', ['title' => 'Users Manager','user'=>$user,'users'=>$users]);
    }

    public function show(Request $req){
    
    }
    public function edit(Request $req,$id){
    $user=Auth::user();
    $title="Business Manager";
    $user = $this->user->getUserById($id);
    return view('admin.users.edit',['title' => $title,'user'=>$user,'business'=>$user]);
    }
    public function update(Request $req){
        
    }
}
