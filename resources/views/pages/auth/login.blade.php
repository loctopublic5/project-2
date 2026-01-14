@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-5">
            <div class="card border-0 shadow-sm p-4">
                <h3 class="text-center fw-bold mb-4">Đăng nhập</h3>
                <form id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" placeholder="name@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" placeholder="********">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="btnLogin">
                        ĐĂNG NHẬP
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection