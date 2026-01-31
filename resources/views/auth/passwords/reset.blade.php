@extends('auth.template.index')

@section('headLocal')
    <style>
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 11px;
            cursor: pointer;
            color: #6c757d;
            z-index: 5;
        }

        .toggle-password:hover {
            color: #000;
        }
    </style>
@endsection

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

            <div class="card-body d-flex flex-column login-card-body p-0">
                <div class="card mb-5">
                    <div class="card-body text-center">
                        @php
                            $baseUrl = config('app.url');
                            if (app()->environment('local')) {
                                $baseUrl .= ':8000';
                            }
                        @endphp

                        <div class="social-auth-links text-center mb-3">
                            <p style="color: {{ config('custom.text_color_conta') }};">
                                Voltar para
                                <a href="{{ $baseUrl }}"
                                    style="color: {{ config('custom.text_color_cadastre') }};">Home</a>
                            </p>
                        </div>
                        <div class="d-flex justify-content-center">
                            <p>Redefinir Senha</p>
                            <i class="fa fa-arrow-down ml-2 animate__animated animate__bounce"></i>
                        </div>
                        <a href="{{ config('custom.portal_link') }}" target="_blank">
                            <img src="{{ config('custom.logo_1') }}" style="width: 140px;"
                                alt="{{ config('custom.project_name') }}">
                        </a>
                    </div>
                </div>

                <h3 class="login-box-msg" style="color: {{ config('custom.text_color_gerenciar') }};">Definir nova senha
                </h3>

                <form action="{{ route('password.update') }}" method="post">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="input-group mb-2 d-flex flex-column">
                        <label for="email"></label>
                        <input type="email" @error('email') is-invalid @enderror value="{{ $email ?? old('email') }}"
                            name="email" id="email" class="form-control w-100" placeholder="E-mail">

                        @error('email')
                            <span class="text-danger position-relative" style="top: 10px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group mb-2 d-flex position-relative">
                        <label for="password"></label>

                        <input type="password" @error('password') is-invalid @enderror name="password" id="password"
                            class="form-control w-100" placeholder="Nova senha">

                        <span class="toggle-password" data-target="#password">
                            <i class="fa fa-eye"></i>
                        </span>
                    </div>

                    <ul id="passwordRules" class="list-unstyled mt-2">
                        <li id="rule-length" class="text-danger">✖ Mínimo de 6 caracteres</li>
                        <li id="rule-uppercase" class="text-danger">✖ Pelo menos 1 letra maiúscula</li>
                        <li id="rule-number" class="text-danger">✖ Pelo menos 1 número</li>
                        <li id="rule-special" class="text-danger">✖ Pelo menos 1 caractere especial</li>
                    </ul>

                    @error('password')
                        <span class="text-danger position-relative" style="top: 10px;">{{ $message }}</span>
                    @enderror

                    <div class="input-group mb-2 d-flex position-relative">
                        <label for="password_confirmation"></label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control w-100" placeholder="Confirme a senha">

                        <span class="toggle-password" data-target="#password_confirmation">
                            <i class="fa fa-eye"></i>
                        </span>
                    </div>

                    <small id="passwordConfirmHelp" class="form-text text-white">
                        Repita a senha digitada acima.
                    </small>

                    @error('password_confirmation')
                        <span class="text-danger">{{ $message }}</span>
                        <hr>
                    @enderror

                    <div class="col-12 d-flex align-items-center justify-content-center">
                        <button type="submit" class="acess-button"
                            style="background-color: {{ config('custom.button_color_entrar') }}; color: {{ config('custom.button_text_color_entrar') }};">Redefinir
                            senha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('javascriptLocal')
    <script>
        $(function() {
            const $password = $('#password');
            const $confirm = $('#password_confirmation');
            const $submitBtn = $('button[type="submit"]');

            function validatePasswordRules(password) {
                let rules = {
                    length: password.length >= 6,
                    uppercase: /[A-Z]/.test(password),
                    number: /\d/.test(password),
                    special: /[@$!%*#?&._-]/.test(password)
                };

                $('#rule-length').toggleClass('text-success', rules.length)
                    .toggleClass('text-danger', !rules.length)
                    .text(`${rules.length ? '✔' : '✖'} Mínimo de 6 caracteres`);

                $('#rule-uppercase').toggleClass('text-success', rules.uppercase)
                    .toggleClass('text-danger', !rules.uppercase)
                    .text(`${rules.uppercase ? '✔' : '✖'} Pelo menos 1 letra maiúscula`);

                $('#rule-number').toggleClass('text-success', rules.number)
                    .toggleClass('text-danger', !rules.number)
                    .text(`${rules.number ? '✔' : '✖'} Pelo menos 1 número`);

                $('#rule-special').toggleClass('text-success', rules.special)
                    .toggleClass('text-danger', !rules.special)
                    .text(`${rules.special ? '✔' : '✖'} Pelo menos 1 caractere especial`);

                return Object.values(rules).every(Boolean);
            }

            function validateConfirm(password, confirm) {
                return confirm.length > 0 && password === confirm;
            }

            let passwordValid = false;
            let confirmValid = false;

            function validateForm() {
                let password = $password.val();
                let confirm = $confirm.val();
                let email = $('#email').val();

                passwordValid = validatePasswordRules(password);
                confirmValid = validateConfirm(password, confirm);

                $('#passwordConfirmHelp')
                    .text(
                        confirm.length === 0 ?
                        'Repita a senha digitada acima.' :
                        confirmValid ?
                        'As senhas conferem ✔' :
                        'As senhas não conferem.'
                    )
                    .toggleClass('text-success', confirmValid)
                    .toggleClass('text-danger', confirm.length > 0 && !confirmValid)
                    .toggleClass('text-white', confirm.length === 0);

                const isFormValid = passwordValid && confirmValid && email.length > 0;
                $submitBtn.prop('disabled', !isFormValid);
            }

            $password.on('input', validateForm);
            $confirm.on('input', validateForm);
            $('#email').on('input', validateForm);

            $(document).on('click', '.toggle-password', function() {
                const target = $($(this).data('target'));
                const icon = $(this).find('i');

                if (target.attr('type') === 'password') {
                    target.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    target.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            $('#password, #password_confirmation').on('copy paste cut', function(e) {
                e.preventDefault();
            });

            validateForm();
        });
    </script>
@endsection
