@extends('superadmin.auth.layouts.app')

@section('content')
    <div class="account-content">
        <div class="row login-wrapper m-0">
            <div class="col-lg-6 p-0">
                <div class="login-content">
                    <form action="{{ route('superadmin.login.submit') }}" method="POST">
                        @csrf
                        <div class="login-userset">
                            <div class="login-logo logo-normal">
                                <img src="{{ asset('assets/img/logo.svg') }}" alt="img">
                            </div>
                            <a href="#" class="login-logo logo-white">
                                <img src="{{ asset('assets/img/logo-white.svg') }}" alt="Img">
                            </a>
                            <div class="login-userheading">
                                <h3>Sign In</h3>
                                
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    {{ $errors->first() }}
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <input type="text" name="email" value="{{ old('email') }}" class="form-control border-end-0" required autofocus>
                                    <span class="input-group-text border-start-0">
                                        <i class="ti ti-mail"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="pass-group">
                                    <input type="password" name="password" class="pass-input form-control" required>
                                    <span class="ti toggle-password ti-eye-off text-gray-9"></span>
                                </div>
                            </div>
                            <div class="form-login authentication-check">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="custom-control custom-checkbox">
                                            <label class="checkboxs ps-4 mb-0 pb-0 line-height-1">
                                                <input type="checkbox" name="remember">
                                                <span class="checkmarks"></span>Remember me
                                            </label>
                                        </div>
                                    </div>
                                   
                                </div>
                            </div>
                            <div class="form-login">
                                <button type="submit" class="btn btn-login">Sign In</button>
                            </div>
                            
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6 p-0">
                <div class="login-img">
                    <img src="{{ asset('assets/img/authentication/authentication-01.svg') }}" alt="img">
                </div>
            </div>
        </div>
    </div>
@endsection
