@extends('auth.template.index')

@section('content')
<div class="background-login col-6 d-none d-md-flex"
    style="background-image: url('/Auth-Panel/dist/img/{{ config('custom.background_password_image') }}')"></div>

<div class="login-box login-page col-12 col-md-6 p-0"
    style="background-color: {{ config('custom.background_password_color') }};">
    <div class="card col p-0">
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @php
            $baseUrl = config('app.url');
            if (app()->environment('local')) {
                $baseUrl .= ':8000';
            }
        @endphp

        <div class="social-auth-links text-center mb-3">
            <p style="color: {{ config('custom.text_color_conta') }};">
                Voltar para
                <a href="{{ $baseUrl }}" style="color: {{ config('custom.text_color_cadastre') }};">Home</a>
            </p>
        </div>

        <div class="card-body d-flex flex-column login-card-body p-0">
            <div class="card mb-5">
                <div class="card-body text-center">
                    <div class="d-flex justify-content-center">
                        <p style="color: {{ config('custom.text_color_recuperar') }};">Recuperar Senha</p>
                        <i class="fa fa-arrow-down ml-2 animate__animated animate__bounce"
                            style="color: {{ config('custom.text_color_recuperar') }};"></i>
                    </div>
                    <a href="{{ config('custom.portal_link') }}" target="_blank">
                        <img src="{{ config('custom.logo_1') }}" style="width: 140px;"
                            alt="{{ config('custom.project_name') }}">
                    </a>
                </div>
            </div>

            <h3 class="login-box-msg" style="color: {{ config('custom.text_color_gerenciar') }};">Recuperar sua conta
            </h3>

            <form action="{{ route('password.email') }}" method="post">
                @csrf

                <div class="input-group mb-2 d-flex flex-column">
                    <label for="email"></label>
                    <input type="email" @error('email') is-invalid @enderror value="{{ old('email') ?? '' }}"
                        name="email" id="email" class="form-control w-100" placeholder="E-mail">

                    @error('email')
                        <span class="text-danger position-relative" style="top: 10px;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-12 d-flex align-items-center justify-content-center">
                    <button type="submit" class="acess-button"
                        style="background-color: {{ config('custom.button_color_entrar') }}; color: {{ config('custom.button_text_color_entrar') }};">Enviar
                        link de recuperação</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
