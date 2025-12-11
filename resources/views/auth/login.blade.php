@extends('auth.template.index')

@section('content')
    <div class="background-login col-6 d-none d-md-flex"
        style="background-image: url('/Auth-Panel/dist/img/{{ config('custom.background_login_image') }}')"></div>
    <div class="login-box login-page col-12 col-md-6 p-0"
        style="background-color: {{ config('custom.background_login_color') }};">


        @if ($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Atenção aos seguintes erro(s):',
                        html: `
                                <ul class="text-left" style="margin: 0; padding-left: 20px;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                `,
                        confirmButtonText: 'Ok',
                        customClass: {
                            popup: 'swal-wide',
                            title: 'swal-title-bold'
                        },
                        didOpen: () => {
                            Swal.getConfirmButton().focus();
                        }
                    });
                });
            </script>
        @endif

        <div class="card col p-0">
            <div class="card-body d-flex flex-column login-card-body p-0">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        @php
                            $baseUrl = config('app.url');
                            if (app()->environment('local')) {
                                $baseUrl .= ':8000';
                            }
                        @endphp

                        <div class="d-flex justify-content-center">
                            <a href="{{ config('custom.portal_link') }}" target="_blank"
                                class="d-flex align-items-center text-decoration-none">
                                <p style="color: {{ config('custom.text_color_acessar') }}; margin: 0;">
                                    Acessar {{ config('custom.project_name') }}
                                </p>

                                <i class="fas fa-external-link ml-2 animate__animated animate__bounce"
                                    style="color: {{ config('custom.text_color_acessar') }}; font-size: 18px;"></i>
                            </a>
                        </div>

                    </div>
                </div>


                <h3 class="login-box-msg" style=" color: {{ config('custom.text_color_gerenciar') }};">Gerenciar Conta</h3>

                <form action="{{ route('login') }}" method="post">
                    @csrf

                    <div class="input-group mb-2 d-flex flex-column">
                        <label for="login"></label>
                        <input type="text" @error('login') is-invalid @enderror value="{{ old('login') ?? '' }}"
                            name="login" id="login" class="form-control w-100" placeholder="Usuário">

                        @error('login')
                            <span class="text-danger position-relative" style="top: 10px;">{{ $message }}</span>
                        @enderror
                    </div>


                    <div class="input-group mb-2 d-flex flex-column">
                        <label for="password"></label>
                        <input type="password" @error('password') has-error @enderror value="{{ old('password') ?? '' }}"
                            name="password" id="password" class="form-control w-100" placeholder="Senha"
                            style="min-height: 40px">

                        @error('password')
                            <span class="text-danger position-relative" style="top: 10px;">{{ $message }}</span>
                        @enderror
                    </div>


                    <div class="">
                        <div class="col-12 d-flex align-items-center justify-content-center">
                            <button type="submit" class="acess-button"
                                style="background-color: {{ config('custom.button_color_entrar') }}; color: {{ config('custom.button_text_color_entrar') }};">Entrar</button>
                        </div>

                        <div class="col-12 d-flex align-items-center justify-content-center">
                            {{-- {{ route('password.email') }} --}}
                            <a href="{{ route('password.request') }}" class="password-button text-center border text-black"
                                style="background-color: {{ config('custom.button_color_senha') }}; color: {{ config('custom.button_text_color_senha') }}; border: {{ config('custom.button_color_senha') }}!important;">
                                <i class="fab fa-lock mr-2"></i> Esqueci minha senha
                            </a>
                        </div>
                    </div>

                    <div class="">
                        <div class="col-12">
                            <div class="social-auth-links text-center mb-3">
                                <p style=" color: {{ config('custom.text_color_conta') }};">Não tem uma conta? <a
                                        href="{{ config('app.url') . '#planos' }}" class=""
                                        style=" color: {{ config('custom.text_color_cadastre') }};">Cadastre-se</a></p>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection