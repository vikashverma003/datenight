@extends('admin.layouts.app')
@section('title',$title)
@section('user_name',$user->name)
@section('role',$user->role)
@section('content')
<div class="content-wrapper">
   <div class="row">
    <div class="profile-data" style="border-radius: 8px;margin:auto;width:80% !important;">
   <div class="col-md-12  grid-margin stretch-card">
          
    
  
      <div class="row">
          <div class="col-md-12">


              <h4>User Profile</h4>
              @if(!is_null(@$user->profile_image))
              <div style="position:relative">
              <img src="{{env('APP_URL')."".env('IMAGE_UPLOAD_PATH').'/'.$user->profile_image}}" alt="" class="profileImg2" style="width: 114px;border-radius: 50%;height: 114px !important;">
             
              </div>
              @else
              <div class="first_letter">
                  <span>{{strtoupper(substr(@$user->name,0,1))}}</span>
                 
              </div>
             @endif
              <div class="row">
                  <div class="col-md-6">
                      <h3>Name</h3>
                      <p>{{@$user->name}}</p>
                  </div>
                  <div class="col-md-6">
                      <h3>Email Id</h3>
                      <p>{{@$user->email}} <img src="{{asset('web/images/tick.png')}}" alt=""
                              style="all: unset;">
                      </p>
                  </div>
                  <div class="col-md-6">
                      <h3>Contact Number</h3>
                      <p>+ {{@$user->phone_code}} {{@$user->phone_number}}</p>
                  </div>
                  <div class="col-md-6">
                    <h3>Location</h3>
                    <p>{{@$user->location->name}}
                    </p>
                </div>
              </div>

             

          

              
          </div>
      </div>
  </div>

            </div>
   </div>
</div>
<style>
 

</style>
@endsection
