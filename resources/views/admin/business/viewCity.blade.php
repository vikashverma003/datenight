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
              <a href="{{url('admin/addLocation')}}" class="own_btn_background mr-2 btn  btn-success">
                  Add Location
                          </a>
               <!-- <h4 class="card-title">Location List</h4> -->
               {{-- <a class="nav-link add_button" href="{{url('admin/brands/create')}}">
                <i class=" icon-plus menu-icon"></i>
                <span class="menu-title">Add</span>
              </a> --}}
               <div class="table-responsive">
                  <table class="table">
                     <thead>
                        <tr>
                           <th>#</th>
                           <th>Name</th>
                           <th>Hash Tag</th>
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $key=1
                        @endphp
                        @if($data->isNotEmpty())
                        @foreach ($data as $com)
                        <tr>
                           <td>{{$key++}}</td>
                           <td>{{@$com->name}}</td>
                           <td>{{@$com->hash_tag}}</td>
                           <td>
                              {{--rl('admin/company/'.$com->id.'/edit') --}}
                            <a class="action-button" href="{{route('updateCity',$com->id)}}" data-toggle="tooltip" title="Edit">
                            <i class=" icon-pencil menu-icon"></i>
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
                
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
