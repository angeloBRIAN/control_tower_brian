@extends('layouts.guest')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-lg border-0 mt-5">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-speedometer2 fs-1 text-primary"></i>
                    <h3 class="mt-2">Control Tower</h3>
                    <p class="text-muted">Create a new account</p>
                </div>

                @if($errors->any())
                <div class="alert alert-danger py-2">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-plus me-1"></i>Register
                    </button>
                </form>

                <hr class="my-4">

                <p class="text-center text-muted mb-0">
                    Already have an account? <a href="{{ route('login') }}">Sign In</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
