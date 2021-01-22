<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
use App\User;
use App\Models\Term;
use App\Models\About;
use App\Models\Contact;
use App\Models\Privacy;


class UserController extends Controller
{
    /**
     * Login page
     */
    public function index(){
      if (Auth::check()) {
        return redirect('admin/dashboard');
      }
      Auth::logout();

  
      return view('admin.login', ['title' => 'Login Page']);
    }

    /**
     * Check user Detail
     */
    public function login(Request $request){
        $request->validate([
          'email' => 'required|email|exists:users,email',
          'password' => 'required|min:6',
      ]);
      if (!Auth::check()) {
        $email=$request->get('email');
        $password=$request->get('password');
  
        if (Auth::attempt(['email' => $email, 'password' => $password, 'role' =>config('constants.role.ADMIN'),'account_status'=>config('constants.account_status.ACTIVE')])) {
          return redirect('admin/dashboard');
      }else{
        return redirect('admin/login')->with('error', 'Login credential is not valid ') ;
      }
      }else{
        return redirect('admin/dashboard');
      }
  }
  
  public function logout(){
    Auth::logout();
    return redirect(\URL::previous());
  }
  public function term(Request $request){
    $user=Auth::user();
    $term=Term::where('id',1)->first();

     return view('admin.users.term', ['title' => 'Term & Condition','user'=>$user,'term'=>$term]);
  }

  public function about(Request $request){
    $user=Auth::user();
    $term=About::where('id',1)->first();

     return view('admin.users.about', ['title' => 'About Us','user'=>$user,'term'=>$term]);
  }

  public function contact(Request $request){
    $user=Auth::user();
    $term=Contact::where('id',1)->first();

     return view('admin.users.contact', ['title' => 'About Us','user'=>$user,'term'=>$term]);
  }



  public function policy(Request $request){

    $user=Auth::user();
    $privacy=Privacy::where('id',1)->first();

     return view('admin.users.privacy', ['title' => 'Term & Condition','user'=>$user,'privacy'=>$privacy]);
  }

  public function updateTerm(Request $request){
             Term::where('id',1)->update([
                        'heading'       => $request->heading,
                       
            ]);
     return redirect('admin/term')->with('status','Administrator has been created Successfully.');        
  }

  public function updateAbout(Request $request){
             About::where('id',1)->update([
                        'heading'       => $request->heading,
                       
            ]);
     return redirect('admin/about')->with('status','Administrator has been created Successfully.');        
  }

   public function updateContact(Request $request){
             Contact::where('id',1)->update([
                        'heading'       => $request->heading,
                       
            ]);
     return redirect('admin/contact')->with('status','Administrator has been created Successfully.');        
  }

  public function updatePrivacy(Request $request){
               Privacy::where('id',1)->update([
                        'heading'       => $request->heading,
                       
                ]);
     return redirect('admin/policy')->with('status','Administrator has been created Successfully.');   
  }

  public function delete($id){

      User::where('id',$id)->update([
                  'delete_status'       => '1',
                  'approved_status'     => '0',     
                  'account_status'      => '0',     
            ]);
     return redirect('admin/businesses')->with('success','businesses has been deleted Successfully.'); 

  }

  public function deleteUser($id){

      User::where('id',$id)->update([
                  'delete_status'       => '1',
                  'approved_status'     => '0',     
                  'account_status'      => '0',     
            ]);
     return redirect('admin/users')->with('success','User has been deleted Successfully.'); 

  }

  public function deleteAdv($id){

      User::where('id',$id)->update([
                  'delete_status'       => '1',
                  'approved_status'     => '0',     
                  'account_status'      => '0',     
            ]);
     return redirect('admin/advertiser')->with('success','Advertiser has been deleted Successfully.'); 

  }

}
