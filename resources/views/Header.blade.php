<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
          </li>
          @auth
            <li class="nav-item">
                <a class="nav-link" href="{{ route('manage-login.Logout')}}">Logout</a>
            </li>
          @else
          <li class="nav-item">
            <a class="nav-link" href="{{ route('login')}}">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('manage-login.Registration')}}">Registration</a>
          </li>
          @endauth
        </ul>
        <span class="navbar-text">
          @auth
            Welcome back, {{ auth()->user()->name }}
          @else
            Welcome back
          @endauth
        </span>
      </div>
    </div>
  </nav>