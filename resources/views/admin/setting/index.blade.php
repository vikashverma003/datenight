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
          <h6 class="card-title">Price Setting</h6>
            
            <form class="forms-sample mb-4" method="post" enctype="multipart/form-data" >
              @csrf
             
                <div class="form-group">
                  <label for="ADVERTISER_SLOT_PRICE"> Advertiser Slot Price</label>
                  <div class="input-group">
                    <span class="input-group-addon" id="basic-addon1">$</span>
                    <input type="text" name="ADVERTISER_SLOT_PRICE" class="form-control" id="ADVERTISER_SLOT_PRICE" placeholder="Advertiser Slot Price" value="{{$A->option_value}}" required />
                  </div>
                
                @if ($errors->has('ADVERTISER_SLOT_PRICE'))
                <div class="error">{{ $errors->first('ADVERTISER_SLOT_PRICE') }}</div>
                @endif
                </div>
                <div class="form-group">
                  <label for="TARGET_MARKET_SUB_PRICE">Target Market Sub Price</label>
                  <div class="input-group">
                    <span class="input-group-addon" id="basic-addon1">$</span>
                    <input type="text" name="TARGET_MARKET_SUB_PRICE" class="form-control" id="TARGET_MARKET_SUB_PRICE" placeholder="Target Market Sub Price" value="{{$T->option_value}}" required />
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