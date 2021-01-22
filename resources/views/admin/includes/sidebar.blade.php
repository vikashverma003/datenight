{{-- @include('admin.includes.sidebar-skin') --}}
<!-- partial:partials/_sidebar.html -->
<nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item nav-profile">
              <div class="nav-link">
                <div class="profile-image">
                  <img src="{{asset('admin/images/dummy-image.jpg')}}" alt="image" />
                  <span class="online-status online"></span> <!--change class online to offline or busy as needed-->
                </div>
                <div class="profile-name">
                  <p class="name">
                  @yield('user_name')
                  </p>
                  <p class="designation">
                  @yield('role')
                  </p>
                </div>
              </div>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{url('admin/dashboard')}}">
                <i class="icon-rocket menu-icon"></i>
                <span class="menu-title">Dashboard</span>
                 <!--<span class="badge badge-success">New</span> -->
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{url('admin/businesses')}}">
                <i class="icon-people menu-icon"></i>
                <span class="menu-title">Businesses</span>
              {{-- <span class="badge badge-success">00 --}}
                {{-- {{ProjectManager::userCount(config('constants.role.CLIENT'))}} --}}
              {{-- </span>  --}}
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{url('admin/users')}}">
                <i class="icon-people menu-icon"></i>
                <span class="menu-title">Users</span>
              {{-- <span class="badge badge-success">00 --}}
                {{-- {{ProjectManager::userCount(config('constants.role.TALENT'))}} --}}
              {{-- </span>  --}}
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{url('admin/advertiser')}}">
                <i class="icon-people menu-icon"></i>
                <span class="menu-title">Advertiser</span>
                 <!--<span class="badge badge-success">New</span> -->
              </a>
            </li>


            <li class="nav-item">
              <a class="nav-link" href="{{url('admin/setting')}}">
                <i class="icon-settings menu-icon"></i>
                <span class="menu-title">Settings</span>
              {{-- <span class="badge badge-success">00 --}}
                {{-- {{ProjectManager::userCount(config('constants.role.TALENT'))}} --}}
              {{-- </span>  --}}
              </a>
            </li>

           <li class="nav-item">
              <a class="nav-link" href="{{url('admin/city')}}">
                <i class="icon-people menu-icon"></i>
                <span class="menu-title">City</span>
                 <!--<span class="badge badge-success">New</span> -->
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{url('admin/term')}}">
                <i class="icon-rocket menu-icon"></i>
                <span class="menu-title">Term & Condition</span>
                 <!--<span class="badge badge-success">New</span> -->
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{url('admin/policy')}}">
                <i class="icon-rocket menu-icon"></i>
                <span class="menu-title">Privacy Policy</span>
                 <!--<span class="badge badge-success">New</span> -->
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="{{url('admin/about')}}">
                <i class="icon-rocket menu-icon"></i>
                <span class="menu-title">About Us</span>
                 <!--<span class="badge badge-success">New</span> -->
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{url('admin/contact')}}">
                <i class="icon-rocket menu-icon"></i>
                <span class="menu-title">Contact Us</span>
                 <!--<span class="badge badge-success">New</span> -->
              </a>
            </li>

          </ul>
        </nav>
        <!-- partial -->