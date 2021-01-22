@extends('admin.layouts.app')
@section('title',$title)
@section('user_name',$user->first_name." ".$user->last_name)
@section('role',$user->role)
@section('content')
<div class="content-wrapper">
   <div class="row">
      <div class="col-lg-12 stretch-card">

         <div class="card">
            <div class="card-body">
            @if (\Session::has('success'))
                  <div class="alert alert-success">
                     {!! \Session::get('success') !!}
                  </div>
                @endif
                @if (\Session::has('error'))
                  <div class="alert alert-danger">
                     {!! \Session::get('error') !!}
                  </div>
                @endif
               <h4 class="card-title">Advertiser List</h4>
               {{-- <a class="nav-link add_button" href="{{url('admin/brands/create')}}">
                <i class=" icon-plus menu-icon"></i>
                <span class="menu-title">Add</span>
              </a> --}}


              <form class="expanding-search-form float-right" method="get">
                  <input type="text" class="search-input" id="productSearch" name="search" placeholder="Search" value="">
                  <button class="btn btn-sm btn-success" style="padding:2px;" type="submit">
                    <img src="{{asset('admin/images/search.png')}}"> 
                  </button>
                </form>



               <div class="table-responsive">
                  <table class="table">
                     <thead>
                        <tr>
                           <th>#</th>
                           <th>Name</th>
                           <th>Email</th>
                           <th>Phone No</th>
                           <th>City</th>
                           <th>Account Status</th>
                            <th>Approved Status</th>
                           {{-- <th>Parent Category</th> --}}
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $key=1
                        @endphp
                        @if($businesses->isNotEmpty())
                        @foreach ($businesses as $com)
                        <tr>
                           <td>{{$key++}}</td>
                           <td>{{@$com->name}} {{@$com->last_name}}</td>
                           <td>{{@$com->email}}</td>
                           <td>+ {{@$com->phone_code}} {{@$com->phone_number}}</td>
                           <td>{{@$com->location->name}}</td>
                           <td >
                              @if($com->account_status==config('constants.account_status.ACTIVE'))
                              <a href="{{route('viewUsers',$com->id)}}" class="badge badge-success">Active</a>
                              @else
                              <a href="{{route('viewUsers',$com->id)}}" class="badge badge-danger">Inactive</a>
                              @endif
                           </td>
                             <td>
                              @if($com->approved_status=='0')
                              <a href="{{route('viewApproveds',$com->id)}}" class="badge badge-success">Approved</a>
                              @else
                              <a href="{{route('viewApproveds',$com->id)}}" class="badge badge-danger">Unapproved</a>
                              @endif
                           </td>
                           <td>
                              {{--rl('admin/company/'.$com->id.'/edit') --}}
                            <a class="action-button" href="{{route('businesses.edit',[$com->id])}}" data-toggle="tooltip" title="Edit">
                            <i class=" icon-eye menu-icon"></i>
                            <span class="menu-title"></span>
                            </a>

                            <a class="action-button" href="{{route('deleteAdv',$com->id)}}" data-toggle="tooltip" title="Edit">
                            <i class=" icon-trash menu-icon"></i>
                            <span class="menu-title"></span>
                            </a>

                         </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                           <td colspan="6" style="text-align:center">No Business record exist</td>
                        </tr>
                        @endif
                     </tbody>
                  </table>
                  {{ $businesses->links() }}
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
