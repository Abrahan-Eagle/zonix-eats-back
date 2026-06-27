@guest
@else

<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
  <div class="c-sidebar-brand d-lg-down-none">
    <a href="{{ url('/') }}" class="text-decoration-none">
       {{-- Logo placeholder --}}
       <span class="c-sidebar-brand-full" style="color: white; font-weight: bold; font-size: 20px;">Zonix Glasses</span>
      <span class="c-sidebar-brand-minimized" style="color: white; font-weight: bold; font-size: 14px;">SA</span>
    </a>
  </div>

  <ul class="c-sidebar-nav" data-drodpown-accordion="true">
    <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{url('/dashboard')}}">
        <svg class="c-sidebar-nav-icon">
          <use xlink:href="{{asset('icons/sprites/free.svg#cil-speedometer')}}"></use>
        </svg>Dashboard
        </a></li>

    <li class="c-sidebar-nav-title">Menu</li>

    @can('haveaccess', 'users.index')
    <li class="c-sidebar-nav-item">
      <a class="c-sidebar-nav-link" href="{{ route('users.index') }}">
        <svg class="c-sidebar-nav-icon">
          <use xlink:href="{{asset('icons/sprites/free.svg#cil-people')}}"></use>
        </svg> Usuarios
      </a>
    </li>
    @endcan

    @can('haveaccess', 'roles.index')
    <li class="c-sidebar-nav-item">
      <a class="c-sidebar-nav-link" href="{{ route('roles.index') }}">
        <svg class="c-sidebar-nav-icon">
          <use xlink:href="{{asset('icons/sprites/free.svg#cil-user')}}"></use>
        </svg> Roles
      </a>
    </li>
    @endcan
  </ul>

  <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent"
    data-class="c-sidebar-minimized"></button>
</div>

@endguest
