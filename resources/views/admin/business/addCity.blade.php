@extends('admin.layouts.app')
@section('title',$title)
@section('user_name',$user->first_name." ".$user->last_name)
@section('role',$user->role)
@section('content')
<div class="content-wrapper">
   <div class="row">
    <div class="col-md-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body pb-0">
 
          <h6 class="card-title">Add Location</h6>
            
            <form class="forms-sample mb-4" action="{{url('admin/createCity')}}" method="post" enctype="multipart/form-data" >
              @csrf
             
                <div class="form-group">
                  <label for="ADVERTISER_SLOT_PRICE"> Name</label>
                  <div class="input-group">
                    <!-- <span class="input-group-addon" id="basic-addon1">$</span> -->
                    <input type="text" name="name" class="form-control" id="ADVERTISER_SLOT_PRICE" placeholder="Name" value="" required />
                  </div>
                
                @if ($errors->has('ADVERTISER_SLOT_PRICE'))
                <div class="error">{{ $errors->first('ADVERTISER_SLOT_PRICE') }}</div>
                @endif
                </div>
                <div class="form-group">
                  <label for="TARGET_MARKET_SUB_PRICE">Hash Tag</label>
                  <div class="input-group">
                   <!--  <span class="input-group-addon" id="basic-addon1">$</span> -->
                    <input type="text" name="hash_tag" class="form-control" id="TARGET_MARKET_SUB_PRICE" placeholder="Name in spanish" value="" required />
                   
                  </div>
                 
                  @if ($errors->has('TARGET_MARKET_SUB_PRICE'))
                  <div class="error">{{ $errors->first('ADVERTISER_SLOT_PRICE') }}</div>
                  @endif
                </div>
              
                <button type="submit" class=" own_btn_background mr-2 btn  btn-success ">Update</button>
              </form>
        
        </div>
      </div>
    </div>
   </div>
</div>
@endsection