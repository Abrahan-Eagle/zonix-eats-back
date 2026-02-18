@auth
@guest
<header class="c-header c-header-light c-header-fixed">
  @else
  <header class="c-header c-header-light c-header-fixed c-header-with-subheader">
    @endguest
    @guest
    <ul class="c-header-nav d-md-down-none">
      <li class="c-header-nav-item px-3"><a class="c-header-nav-link" href="#"></a></li>
      <li class="c-header-nav-item px-3"><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>
      @if (Route::has('register'))
      <li class="c-header-nav-item px-3"><a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
      </li>
      @endif
    </ul>
    @else
    <button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar"
      data-class="c-sidebar-show">
      <svg class="c-icon c-icon-lg">
        <use xlink:href="{{asset('icons/sprites/free.svg#cil-menu')}}"></use>
      </svg>
    </button><a class="c-header-brand d-lg-none" href="{{ url('/') }}" class="text-decoration-none">
      {{-- <img src="{{ asset('assets/front/images/LOGO_CORRAL.png') }}" alt="Zonix Eats Logo" style="max-height: 20px; width: auto;"> --}}
      <span style="color: #386A20; font-weight: bold; font-size: 20px;">ZONIX EATS</span>
    </a>
    <button class="c-header-toggler c-class-toggler mfs-3 d-md-down-none" type="button" data-target="#sidebar"
      data-class="c-sidebar-lg-show" responsive="true">
      <svg class="c-icon c-icon-lg">

        <use xlink:href="{{asset('icons/sprites/free.svg#cil-menu')}}"></use>
      </svg>
    </button>
    <ul class="c-header-nav d-md-down-none">
      <li class="c-header-nav-item px-3"><a class="c-header-nav-link" href="{{ url('/') }}">Home</a></li>



      <!--
      <li class="c-header-nav-item px-3">
        <a class="btn btn-ghost-info"
          href="https://play.google.com/store/apps/details?id=com.wondershare.pdfelement&hl=es" target="_blank">
          <svg class="c-icon">
            <use xlink:href="{{asset('icons/sprites/free.svg#cil-cloud-upload')}}"></use>
          </svg>&nbsp; Guardar contrato
        </a>
      </li>
    -->


      <li class="c-header-nav-item dropdown">


        <div class="dropdown-menu dropdown-menu-right pt-0">

          <div class="dropdown-divider"></div>


        </div>
      </li>




      <!-- <li class="c-header-nav-item px-3"><a class="c-header-nav-link" href="{{ url('/home') }}">Dashboard</a></li>
      <li class="c-header-nav-item px-3"><a class="c-header-nav-link" href="{{ url('/settings') }}">Settings</a></li> -->
    </ul>
    <ul class="c-header-nav mfs-auto">


      <!--

      <li class="c-header-nav-item dropdown d-md-down-none mx-2"><a class="c-header-nav-link" data-toggle="dropdown"
          href="#" role="button" aria-haspopup="true" aria-expanded="false">

          <svg class="c-icon">

            <use xlink:href="{{asset('icons/sprites/free.svg#cil-bell')}}"></use>
          </svg><span class="badge badge-pill badge-danger">5</span></a>
        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
          <div class="dropdown-header bg-light"><strong>Tienes 5 notificaciones</strong></div><a class="dropdown-item"
            href="#">
            <svg class="c-icon mfe-2 text-success">

              <use xlink:href="{{asset('icons/sprites/free.svg#cil-user-follow')}}"></use>
            </svg> New user registered</a><a class="dropdown-item" href="#">
            <svg class="c-icon mfe-2 text-danger">

              <use xlink:href="{{asset('icons/sprites/free.svg#cil-user-unfollow')}}"></use>
            </svg> User deleted</a><a class="dropdown-item" href="#">
            <svg class="c-icon mfe-2 text-info">

              <use xlink:href="{{asset('icons/sprites/free.svg#cil-chart')}}"></use>
            </svg> Sales report is ready</a><a class="dropdown-item" href="#">
            <svg class="c-icon mfe-2 text-success">

              <use xlink:href="{{asset('icons/sprites/free.svg#cil-basket')}}"></use>
            </svg> New client</a><a class="dropdown-item" href="#">
            <svg class="c-icon mfe-2 text-warning">

              <use xlink:href="{{asset('icons/sprites/free.svg#cil-speedometer')}}"></use>
            </svg> Server overloaded</a>

      </li>

    -->




      <li class="c-header-nav-item px-3 c-d-legacy-none">

        <!--
        <button class="c-class-toggler c-header-nav-btn" type="submit" id="header-tooltip" data-target="body"
          data-class="c-dark-theme" data-toggle="c-tooltip" data-placement="bottom" title="Toggle Light/Dark Mode">
          <svg class="c-icon c-d-dark-none">
            <use xlink:href="{{asset('icons/sprites/free.svg#cil-moon')}}"></use>
          </svg>
          <svg class="c-icon c-d-default-none">
            <use xlink:href="{{asset('icons/sprites/free.svg#cil-sun')}}"></use>
          </svg>
        </button>
-->

         <form action="{{ route('update.light') }}" method="get" class="needs-validation" accept-charset="UTF-8"
          enctype="multipart/form-data">



          <!-- <button class="btn btn-block btn-facebook" type="submit"><span>Register</span></button> -->


          <div class="row">
            <div class="col-6">
              <button class=" c-header-nav-btn" type="submit" id="header-tooltip" data-target="body"
                data-class="c-dark-theme" data-toggle="c-tooltip" data-placement="bottom"
                title="Toggle Light/Dark Mode">
                <svg class="c-icon c-d-dark-none">
                  <use xlink:href="{{asset('icons/sprites/free.svg#cil-moon')}}"></use>
                </svg>
                <svg class="c-icon c-d-default-none">
                  <use xlink:href="{{asset('icons/sprites/free.svg#cil-sun')}}"></use>
                </svg>
              </button>
            </div>
          </div>
        </form>

      </li>



    </ul>
    <ul class="c-header-nav">


      <!--

      <li class="c-header-nav-item dropdown d-md-down-none mx-2"><a class="c-header-nav-link" data-toggle="dropdown"
          href="#" role="button" aria-haspopup="true" aria-expanded="false">

          <svg class="c-icon">

            <use xlink:href="{{asset('icons/sprites/free.svg#cil-bell')}}"></use>
          </svg><span class="badge badge-pill badge-danger">5</span></a>
        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
          <div class="dropdown-header bg-light"><strong>You have 5 notifications</strong></div><a class="dropdown-item"
            href="#">
            <svg class="c-icon mfe-2 text-success">

              <use xlink:href="{{asset('icons/sprites/free.svg#cil-user-follow')}}"></use>
            </svg> New user registered</a><a class="dropdown-item" href="#">
            <svg class="c-icon mfe-2 text-danger">

              <use xlink:href="{{asset('icons/sprites/free.svg#cil-user-unfollow')}}"></use>
            </svg> User deleted</a><a class="dropdown-item" href="#">
            <svg class="c-icon mfe-2 text-info">

              <use xlink:href="{{asset('icons/sprites/free.svg#cil-chart')}}"></use>
            </svg> Sales report is ready</a><a class="dropdown-item" href="#">
            <svg class="c-icon mfe-2 text-success">

              <use xlink:href="{{asset('icons/sprites/free.svg#cil-basket')}}"></use>
            </svg> New client</a><a class="dropdown-item" href="#">
            <svg class="c-icon mfe-2 text-warning">

              <use xlink:href="{{asset('icons/sprites/free.svg#cil-speedometer')}}"></use>
            </svg> Server overloaded</a>
          <div class="dropdown-header bg-light"><strong>Server</strong></div><a class="dropdown-item d-block" href="#">
            <div class="text-uppercase mb-1"><small><b>CPU Usage</b></small></div><span class="progress progress-xs">
              <div class="progress-bar bg-info" role="progressbar" style="width: 25%" aria-valuenow="25"
                aria-valuemin="0" aria-valuemax="100"></div>
            </span><small class="text-muted">348 Processes. 1/4 Cores.</small>
          </a><a class="dropdown-item d-block" href="#">
            <div class="text-uppercase mb-1"><small><b>Memory Usage</b></small></div><span class="progress progress-xs">
              <div class="progress-bar bg-warning" role="progressbar" style="width: 70%" aria-valuenow="70"
                aria-valuemin="0" aria-valuemax="100"></div>
            </span><small class="text-muted">11444GB/16384MB</small>
          </a><a class="dropdown-item d-block" href="#">
            <div class="text-uppercase mb-1"><small><b>SSD 1 Usage</b></small></div><span class="progress progress-xs">
              <div class="progress-bar bg-danger" role="progressbar" style="width: 95%" aria-valuenow="95"
                aria-valuemin="0" aria-valuemax="100"></div>
            </span><small class="text-muted">243GB/256GB</small>
          </a>
        </div>
      </li>

      -->




      <!--

      <li class="c-header-nav-item dropdown d-md-down-none mx-2"><a class="c-header-nav-link" data-toggle="dropdown"
          href="#" role="button" aria-haspopup="true" aria-expanded="false">
          <svg class="c-icon">

            <use xlink:href="{{asset('icons/sprites/free.svg#cil-list-rich')}}"></use>
          </svg><span class="badge badge-pill badge-warning">15</span></a>
        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
          <div class="dropdown-header bg-light"><strong>You have 5 pending tasks</strong></div><a
            class="dropdown-item d-block" href="#">
            <div class="small mb-1">Upgrade NPM &amp; Bower<span class="float-right"><strong>0%</strong></span></div>
            <span class="progress progress-xs">
              <div class="progress-bar bg-info" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                aria-valuemax="100"></div>
            </span>
          </a><a class="dropdown-item d-block" href="#">
            <div class="small mb-1">ReactJS Version<span class="float-right"><strong>25%</strong></span></div><span
              class="progress progress-xs">
              <div class="progress-bar bg-danger" role="progressbar" style="width: 25%" aria-valuenow="25"
                aria-valuemin="0" aria-valuemax="100"></div>
            </span>
          </a><a class="dropdown-item d-block" href="#">
            <div class="small mb-1">VueJS Version<span class="float-right"><strong>50%</strong></span></div><span
              class="progress progress-xs">
              <div class="progress-bar bg-warning" role="progressbar" style="width: 50%" aria-valuenow="50"
                aria-valuemin="0" aria-valuemax="100"></div>
            </span>
          </a><a class="dropdown-item d-block" href="#">
            <div class="small mb-1">Add new layouts<span class="float-right"><strong>75%</strong></span></div><span
              class="progress progress-xs">
              <div class="progress-bar bg-info" role="progressbar" style="width: 75%" aria-valuenow="75"
                aria-valuemin="0" aria-valuemax="100"></div>
            </span>
          </a><a class="dropdown-item d-block" href="#">
            <div class="small mb-1">Angular 8 Version<span class="float-right"><strong>100%</strong></span></div><span
              class="progress progress-xs">
              <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100"
                aria-valuemin="0" aria-valuemax="100"></div>
            </span>
          </a><a class="dropdown-item text-center border-top" href="#"><strong>View all tasks</strong></a>
        </div>
      </li>




    -->

      <!--
      <li class="c-header-nav-item dropdown d-md-down-none mx-2"><a class="c-header-nav-link" data-toggle="dropdown"
          href="#" role="button" aria-haspopup="true" aria-expanded="false">
          <svg class="c-icon">

            <use xlink:href="{{asset('icons/sprites/free.svg#cil-envelope-open')}}"></use>

          </svg><span class="badge badge-pill badge-info">7</span></a>
        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg pt-0">
          <div class="dropdown-header bg-light"><strong>You have 4 messages</strong></div><a class="dropdown-item"
            href="#">
            <div class="message">
              <div class="py-3 mfe-3 float-left">
                <div class="c-avatar"><img loading="lazy" class="c-avatar-img" src="{{asset('assets/img/avatars/7.jpg')}}"
                    alt="apworldsdigitalservices@gmail.com"><span class="c-avatar-status bg-success"></span></div>
              </div>
              <div><small class="text-muted">John Doe</small><small class="text-muted float-right mt-1">Just now</small>
              </div>
              <div class="text-truncate font-weight-bold"><span class="text-danger">!</span> Important message</div>
              <div class="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed
                do eiusmod tempor incididunt...</div>
            </div>
          </a><a class="dropdown-item" href="#">
            <div class="message">
              <div class="py-3 mfe-3 float-left">
                <div class="c-avatar"><img loading="lazy" class="c-avatar-img" src="{{asset('assets/img/avatars/6.jpg')}}"
                    alt="apworldsdigitalservices@gmail.com"><span class="c-avatar-status bg-warning"></span></div>
              </div>
              <div><small class="text-muted">John Doe</small><small class="text-muted float-right mt-1">5 minutes
                  ago</small></div>
              <div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
              <div class="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed
                do eiusmod tempor incididunt...</div>
            </div>
          </a><a class="dropdown-item" href="#">
            <div class="message">
              <div class="py-3 mfe-3 float-left">
                <div class="c-avatar"><img loading="lazy" class="c-avatar-img" src="{{asset('assets/img/avatars/5.jpg')}}"
                    alt="apworldsdigitalservices@gmail.com"><span class="c-avatar-status bg-danger"></span></div>
              </div>
              <div><small class="text-muted">John Doe</small><small class="text-muted float-right mt-1">1:52 PM</small>
              </div>
              <div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
              <div class="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed
                do eiusmod tempor incididunt...</div>
            </div>
          </a><a class="dropdown-item" href="#">
            <div class="message">
              <div class="py-3 mfe-3 float-left">
                <div class="c-avatar"><img loading="lazy" class="c-avatar-img" src="{{asset('assets/img/avatars/4.jpg')}}"
                    alt="apworldsdigitalservices@gmail.com"><span class="c-avatar-status bg-info"></span></div>
              </div>
              <div><small class="text-muted">John Doe</small><small class="text-muted float-right mt-1">4:03 PM</small>
              </div>
              <div class="text-truncate font-weight-bold">Lorem ipsum dolor sit amet</div>
              <div class="small text-muted text-truncate">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed
                do eiusmod tempor incididunt...</div>
            </div>
          </a><a class="dropdown-item text-center border-top" href="#"><strong>View all messages</strong></a>
        </div>
      </li>
    -->






      <li class="c-header-nav-item dropdown"><a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button"
          aria-haspopup="true" aria-expanded="false">
          @php
            // Obtener foto del usuario: primero photo_users del profile, luego profile_pic del user
            $user = auth()->user();
            $userPhoto = null;
            
            if ($user) {
              // Intentar obtener photo_users del profile si existe
              if ($user->profile && !empty($user->profile->photo_users)) {
                $userPhoto = $user->profile->photo_users;
              } 
              // Si no, intentar profile_pic del user
              elseif (!empty($user->profile_pic)) {
                $userPhoto = $user->profile_pic;
              }
            }
            
            // Construir URL de la foto
            if ($userPhoto) {
              if (str_starts_with($userPhoto, 'http')) {
                $photoUrl = $userPhoto;
              } else {
                // Si es de storage, usar asset('storage/...')
                if (str_contains($userPhoto, 'storage/')) {
                  $photoUrl = asset($userPhoto);
                } else {
                  $photoUrl = asset('storage/' . $userPhoto);
                }
              }
            } else {
              // Usar imagen por defecto de avatar
              $photoUrl = asset('images/user/default-user.png');
            }
          @endphp
          <div class="c-avatar">
            <img loading="lazy" class="c-avatar-img" src="{{ $photoUrl }}"
              alt="{{ $user->email ?? 'Usuario' }}"
              onerror="this.onerror=null; this.src='{{ asset('images/user/default-user.png') }}';">
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-right pt-0">


          <div class="dropdown-header bg-light py-2">
            <strong>Miembro: {{auth()->user() -> name}} <br> desde:</strong>
            {{date('d/M/Y', strtotime(auth()->user() -> created_at))}}
          </div>





          <div class="dropdown-header bg-light py-2"><strong>Settings</strong></div>


          @can('haveaccess', 'users.index')
          <a class="dropdown-item" href="{{ route('users.index') }}">
            <svg class="c-icon mfe-2">
              <use xlink:href="{{asset('icons/sprites/free.svg#cil-user')}}"></use>
            </svg>Usuarios
          </a>
          @endcan

          @can('haveaccess', 'roles.index')
          <a class="dropdown-item" href="{{ route('roles.index') }}">
            <svg class="c-icon mfe-2">
              <use xlink:href="{{asset('icons/sprites/free.svg#cil-user')}}"></use>
            </svg>Roles
          </a>
          @endcan

          <div class="dropdown-divider"></div>

          <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                  document.getElementById('logout-form').submit();"><svg class="c-icon mfe-2">
              <use xlink:href="{{asset('icons/sprites/free.svg#cil-account-logout')}}"></use>
            </svg>
            {{ __('Logout') }}</a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
        </div>
      </li>

    </ul>
    @guest
    @else
    <!-- <div class="c-subheader justify-content-between px-3">

      <ol class="breadcrumb border-0 m-0 px-0 px-md-3">
        <li class="breadcrumb-item">Home</li>
        <li class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
      </ol>
      <div class="c-header-nav d-md-down-none mfe-2"><a class="c-header-nav-link" href="#">
          <svg class="c-icon">
            <use xlink:href="{{asset('icons/sprites/free.svg#cil-speedometer')}}"></use>
          </svg></a><a class="c-header-nav-link" href="#">
          <svg class="c-icon">

            <use xlink:href="{{asset('icons/sprites/free.svg#cil-graph')}}"></use>
          </svg> &nbsp;Dashboard</a><a class="c-header-nav-link" href="#">
          <svg class="c-icon">
            <use xlink:href="{{asset('icons/sprites/free.svg#cil-settings')}}"></use>
          </svg> &nbsp;Settings</a></div>
    </div> -->
    @endguest
    @endguest
  </header>
  @endauth
