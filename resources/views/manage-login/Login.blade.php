@extends('Layout')
@section('title', 'Login Page')
@section('content')
    <div class="container">
        <div class="mt-5">
              @if($errors->any())
                  <div id="flash-message" class="col-12">
                      @foreach($errors->all() as $error)
                          <div  class="alert alert-danger">{{ $error }}</div>
                      @endforeach
                  </div>
              @endif

              @if(session()->has('error'))
              <div id="flash-message" class="alert alert-danger">{{ session('error') }}</div> 
              @endif

              @if(session()->has('success'))
              <div id="flash-message" class="alert alert-success">{{ session('success') }}</div> 
              @endif

          <form action="{{ route('manage-login.PostLogin') }}" method="POST" class="ms-auto me-auto mt-5" style="width: 100%; max-width: 500px;">
              @csrf
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="name" placeholder="Enter your username">
              </div>
              
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Enter your password">
              </div>
              <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@endsection