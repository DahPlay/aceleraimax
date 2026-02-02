@extends('auth.template.index')

@section('headLocal')
    <link rel="stylesheet" href="{{ asset('Auth-Panel/dist/css/front/front.css') }}">

    <style>
        .card-brand-icon {
            position: absolute;
            right: 7px;
            top: 38px;
            width: 38px;
            height: auto;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 42px;
            cursor: pointer;
            color: #6c757d;
            z-index: 5;
        }

        .toggle-password:hover {
            color: #000;
        }

        .title-input2 {
            color: {{ config('custom.text_color_form') }};
            font-weight: 500;
        }

        .subtitle-register2 {
            font-weight: 700;
            color: {{ config('custom.text_color_form') }};
            margin-bottom: 50px !important;
            text-align: center;
        }

        /* New step-by-step styles */
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .step-progress:before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 1;
        }

        .step-progress-bar {
            position: absolute;
            top: 15px;
            left: 0;
            height: 2px;
            background: {{ config('custom.button_color_entrar') }};
            z-index: 2;
            transition: width 0.3s ease;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 3;
            flex: 1;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .step.active .step-number {
            background: {{ config('custom.button_color_entrar') }};
        }

        .step.completed .step-number {
            background: #28a745;
        }

        .step-label {
            font-size: 12px;
            color: #999;
            text-align: center;
        }

        .step.active .step-label {
            color: {{ config('custom.text_color_form') }};
            font-weight: bold;
        }

        .step-content {
            display: none;

        }

        .step-content.active {
            display: block;
        }

        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 15px;
        }

        .btn-nav {
            padding: 10px 25px;
            border-radius: 5px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-back {
            background: transparent;
            color: {{ config('custom.text_color_form') }};
            border: 1px solid #ddd;
        }

        .btn-back:hover {
            background: #f5f5f5;
        }

        .btn-next,
        .btn-submit {
            background: {{ config('custom.button_color_entrar') }};
            color: white;
            margin-left: auto;
        }

        .btn-next:hover,
        .btn-submit:hover {
            opacity: 0.9;
        }

        .btn-submit {
            padding: 12px 25px;
            font-weight: 600;
        }

        .footer-links {
            margin-top: 30px;
            text-align: center;
        }

        .footer-links a {
            display: block;
            margin-bottom: 10px;
            color: {{ config('custom.text_color_form') }};
            text-decoration: none;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* Plan modal adjustments */
        .plan-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .best-seller-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: #ff5722;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .plan-price {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        .plan-features {
            margin: 15px 0;
        }

        .plan-features li {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .plan-features li:before {
            content: "✓";
            margin-right: 8px;
            color: {{ config('custom.button_color_entrar') }};
        }
    </style>
@endsection

@section('content')
    <div class="register-box flex-column">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-register m-auto"
            style="background-color: {{ config('custom.background_form') }};
            color: {{ config('custom.text_color_recuperar') }};
            border: 4px solid {{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">

            <div class="card-body-register login-card-body">
                <div class="login-logo">
                    <a href="{{ route('login') }}">
                        <img src="{{ config('custom.logo_1') }}" alt="">
                    </a>
                </div>

                <p class="subtitle-register2">Crie sua conta e aproveite todo nosso conteúdo!</p>

                <!-- Step Progress -->
                <div class="step-progress">
                    <div class="step-progress-bar" style="width: 0%;"></div>
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Plano</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Dados Pessoais</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Credenciais</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Pagamento</div>
                    </div>
                </div>

                <form action="{{ route('register') }}" method="post" id="registerForm">
                    @csrf

                    <input id="total_with_discounted" type="hidden" value="">

                    <input type="hidden" name="source" id="source" class="form-control" required
                        value="{{ old('source', session('customerData')['source'] ?? '') }}"
                        {{ isset(session('customerData')['source']) ? 'readonly' : '' }}>

                    <!-- Step 1: Plan Selection -->
                    <div class="step-content active" data-step-content="1">
                        <div class="input-group mb-3" style="color: {{ config('custom.text_color_recuperar') }};">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="title-input2 mb-0" for="plan">Planos *</label>
                                <button type="button" class="btn btn-primary btn-view title-input2" data-toggle="modal"
                                    data-target="#modalPlanos">Ver planos
                                </button>
                            </div>

                            <select id="plan_id" class="form-control" name="plan_id" required>
                                <option value="">Selecione...</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}"
                                        data-telemedicine="{{ $plan->is_active_telemedicine }}"
                                        @selected($plan->id == $planId)>
                                        {{ $plan->name . ' - ' . number_format($plan->value, 2, ',', '.') }}

                                    </option>
                                @endforeach
                            </select>

                            <div class="form-group mt-3">
                                <label for="coupon" style="color:{{ config('custom.text_color_form') }}">Cupom de
                                    Desconto</label>
                                <div class="d-flex gap-2">
                                    <input type="text" id="coupon" name="coupon" class="form-control"
                                        placeholder="Digite seu cupom">
                                    <button type="button" id="applyCoupon" class="btn btn-primary">Aplicar</button>
                                </div>
                                <small id="couponFeedback" class="form-text"></small>
                                <small id="couponWarning" class="form-text text-warning d-none mt-1">
                                    ⚠️ Aplique um cupom válido ou limpe o campo para continuar.
                                </small>
                            </div>
                        </div>

                        <div class="navigation-buttons">
                            <button disabled type="button" class="btn btn-nav btn-next" data-next="2"
                                style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Próximo</button>
                        </div>
                    </div>

                    <!-- Step 2: Personal Info -->
                    <div class="step-content" data-step-content="2">
                        <div class="input-group mb-3">
                            <label class="title-input2" for="name">Qual seu nome completo *</label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="Digite seu nome completo *" required
                                value="{{ old('name', session('customerData')['name'] ?? '') }}">
                            <span id="name-error" class="mt-1 small text-danger d-none">
                                ⚠️ Por favor, informe seu nome e sobrenome.
                            </span>
                        </div>

                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                            <hr>
                        @enderror

                        <div class="input-group mb-3">
                            <label class="title-input2" for="document">CPF *</label>
                            <input type="text" @error('document') has-error @enderror
                                value="{{ old('document') ?? '' }}" name="document" id="document" class="form-control"
                                placeholder="Digite seu cpf *" required>

                            <span id="cpf-invalid" class="mt-1 small text-danger d-none">
                                ⚠️ CPF inválido.
                            </span>

                            <span id="cpf-checking" class="mt-1 small text-info d-none">
                                🔄 Verificando CPF...
                            </span>

                            <span id="cpf-exists" class="mt-1 small text-danger d-none">
                                ⚠️ Este CPF já está cadastrado.
                            </span>
                        </div>

                        <div id="dependentes-fields" style="display:none;">
                            <div class="input-group mb-3">
                                <label class="title-input2" for="cpf_dependente_1">CPF Dependente 1</label>
                                <input type="text" name="cpf_dependente_1" id="cpf_dependente_1" class="form-control"
                                    placeholder="Digite o CPF do seu 1° Dependente">
                            </div>
                            <div class="input-group mb-3">
                                <label class="title-input2" for="cpf_dependente_2">CPF Dependente 2</label>
                                <input type="text" name="cpf_dependente_2" id="cpf_dependente_2" class="form-control"
                                    placeholder="Digite o CPF do seu 2° Dependente">
                            </div>
                            <div class="input-group mb-3">
                                <label class="title-input2" for="cpf_dependente_3">CPF Dependente 3</label>
                                <input type="text" name="cpf_dependente_3" id="cpf_dependente_3" class="form-control"
                                    placeholder="Digite o CPF do seu 3° Dependente">
                            </div>
                        </div>

                        @error('document')
                            <span class="text-danger">{{ $message }}</span>
                            <hr>
                        @enderror

                        <div class="input-group mb-3">
                            <label class="title-input2" for="mobile">Digite seu Celular *</label>
                            <input type="text" @error('mobile') has-error @enderror value="{{ old('mobile') ?? '' }}"
                                name="mobile" id="mobile" class="form-control" placeholder="(00) 00000-0000"
                                required>
                        </div>

                        @error('mobile')
                            <span class="text-danger">{{ $message }}</span>
                            <hr>
                        @enderror

                        <div class="input-group mb-3">
                            <label class="title-input2" for="email">Digite seu email *</label>
                            <input type="email" name="email" id="email" class="form-control"
                                placeholder="meuemail@mail.com" required
                                value="{{ old('email', session('customerData')['email'] ?? '') }}">

                            <span id="email-error" class="mt-1 small text-danger d-none">
                                ⚠️ Por favor, informe um e-mail válido.
                            </span>

                            <span id="email-checking" class="mt-1 small text-info d-none">
                                🔄 Verificando disponibilidade do email...
                            </span>

                            <span id="email-exists" class="mt-3 small text-danger d-none">
                                ⚠️ Este e-mail já está cadastrado, acesse um dos links abaixo para prosseguir.
                                <span id="email-actions" class="d-block mt-3"></span>
                            </span>
                        </div>

                        @error('email')
                            <span class="text-danger">{{ $message }}</span>
                            <hr>
                        @enderror

                        <div class="navigation-buttons">
                            <button type="button" class="btn btn-nav btn-back" data-prev="1"
                                style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Voltar</button>
                            <button disabled type="button" class="btn btn-nav btn-next" data-next="3"
                                style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Próximo</button>
                        </div>
                    </div>

                    <!-- Step 3: Credentials -->
                    <div class="step-content" data-step-content="3">
                        <div class="input-group mb-3">
                            <label class="title-input2" for="usuario">Usuário *</label>
                            <input type="text" name="login" id="usuario" class="form-control"
                                placeholder="Usuário *"
                                value="{{ old('login', session('customerData')['login'] ?? '') }}" readonly>
                        </div>

                        @error('login')
                            <span class="text-danger">{{ $message }}</span>
                            <hr>
                        @enderror

                        @if (
                            !session()->has('customerData') ||
                                (session()->has('customerData') && session('customerData')['source'] !== 'temporarily'))
                            <div class="input-group mb-1 position-relative">
                                <label class="title-input2 w-100" for="password">Crie sua senha *</label>

                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Crie uma senha forte" value="{{ old('password') ?? '' }}" required
                                    minlength="6">

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
                                <span class="text-danger">{{ $message }}</span>
                                <hr>
                            @enderror

                            <div class="input-group mb-1 position-relative">
                                <label class="title-input2 w-100" for="password_confirmation">Confirmação de senha
                                    *</label>

                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control" placeholder="Repita sua senha"
                                    value="{{ old('password_confirmation') ?? '' }}" required>

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
                        @endif

                        <div class="navigation-buttons">
                            <button type="button" class="btn btn-nav btn-back" data-prev="2"
                                style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Voltar</button>
                            <button disabled id="btnNextStep" type="button" class="btn btn-nav btn-next" data-next="4"
                                style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Próximo</button>
                        </div>
                    </div>

                    <!-- Step 4: Payment Info -->
                    <div class="step-content" data-step-content="4" id="step-4">
                        <div id="access_free" class="card bg-gradient-cyan mb-4" style="display: none;">
                            <div class="card-header">
                                🎁 Acesso gratuito liberado!
                            </div>
                            <div class="card-body">
                                <span>Cupom de 100% aplicado 🎉🎉</span><br>
                                <span>Aceita os termos e condições, clique em finalizar cadastro e tenha acesso gratuito a
                                    plataforma.</span>
                            </div>
                        </div>

                        <div id="credit-card-fields">
                            <div class="input-group mb-3 position-relative">
                                <label class="title-input2" for="card_number">Número do cartão</label>

                                <input name="credit_card_number" id="card_number" class="form-control"
                                    placeholder="Informe o número do cartão" required>

                                <img id="card-brand" src="" alt="" class="card-brand-icon d-none">
                            </div>

                            <div class="input-group mb-3">
                                <label class="title-input2" for="card_name">Nome do titular do cartão</label>
                                <input type="text" name="credit_card_name" id="card_name" class="form-control"
                                    placeholder="Informe o nome do titular do cartão"
                                    value="{{ old('credit_card_name') ?? '' }}" required>
                            </div>

                            <div class="input-group mb-3">
                                <label class="title-input2" for="card_expiry_month">Mês</label>
                                <input type="text" name="credit_card_expiry_month" id="card_expiry_month"
                                    class="form-control" minlength="2" maxlength="2" placeholder="00"
                                    value="{{ old('credit_card_expiry_month') ?? '' }}" required>

                                <label class="title-input2" for="card_expiry_year">Ano</label>
                                <input type="text" name="credit_card_expiry_year" id="card_expiry_year"
                                    class="form-control" minlength="4" maxlength="4" placeholder="0000"
                                    value="{{ old('credit_card_expiry_year') ?? '' }}" required>
                            </div>

                            <div class="input-group mb-3">
                                <label class="title-input2" for="card_ccv">CVV</label>
                                <input type="text" name="credit_card_ccv" id="card_ccv" class="form-control"
                                    required minlength="3" maxlength="4" inputmode="numeric">
                            </div>
                        </div>

                        <div class="d-flex flex-row input-group mb-2 mt-4">
                            <input type="checkbox" name="terms" id="terms" placeholder="000" minlength="3"
                                maxlength="4" required="" value="">
                            <span class="text-white ml-2">Aceitar termos e condições</span>
                            <a href="#" class="ml-2">visualizar termo.</a>
                        </div>

                        <div class="navigation-buttons">
                            <button type="button" class="btn btn-nav btn-back" data-prev="3"
                                style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Voltar</button>
                            <button type="submit" class="btn btn-nav btn-submit"
                                style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Finalizar
                                Cadastro</button>
                        </div>

                        @php
                            $baseUrl = config('app.url');
                            if (app()->environment('local')) {
                                $baseUrl .= ':8000';
                            }
                        @endphp

                        <div class="footer-links">
                            <a href="{{ route('login') }}">
                                <i class="fa fa-user-plus mr-2"></i> Já tenho conta
                            </a>
                            <a href="{{ $baseUrl }}">
                                <i class="fa fa-home mr-2"></i> Voltar para Home
                            </a>
                        </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <footer class="section-container d-flex flex-column align-items-center footer-register"
        style="background-color: {{ config('custom.background_baseboard') }};">
        <p>{{ config('custom.text_baseboard') }}</p>

        <div
            class="d-flex align-items-center justify-content-center w-100 position-relative container-media flex-column flex-sm-row">
            <div class="social-media d-flex justify-content-center">
                <div class="container-social-media"
                    style="background-color: {{ config('custom.background_social_media') }};">
                    <a href="{{ config('custom.link_social_media_1') }}"><img
                            src="{{ config('custom.image_social_media_1') }}" alt=""></a>
                </div>
            </div>

            <img class="logo-footer" src="{{ config('custom.logo_1') }}" alt="">
        </div>
        <p class="copyright-footer">{{ config('custom.text_copy') }}</p>
    </footer>

    <div class="modal fade" id="modalPlanos" tabindex="-1" aria-labelledby="modalLabelPlanos" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelPlanos">Planos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row">
                            @foreach ($plans as $plan)
                                <div class="col-md-4 mb-4">
                                    <div class="plan-card {{ $plan->is_best_seller ? 'best-seller' : '' }}">
                                        @if ($plan->is_best_seller)
                                            <div class="best-seller-badge">Mais vendido</div>
                                        @endif
                                        <h4>{{ $plan->name }}</h4>
                                        <div class="plan-price">R$ {{ number_format($plan->value, 2, ',', '.') }}</div>
                                        <ul class="plan-features">
                                            @foreach ($plan->benefits as $benefit)
                                                <li>{{ $benefit->description }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn btn-primary w-100"
                                            onclick="selectPlan({{ $plan->id }})" data-dismiss="modal">
                                            Assinar
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-center mt-4">Curta nossas séries, filmes e conteúdos exclusivos feitos para você!
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('javascriptLocal')
    <script>
        const INITIAL_STEP = {{ session('register_step', 1) }};
    </script>

    <script>
        let emailIsValid = false;
        let emailExistsApp = false;
        let emailExistsStreaming = false;

        let couponApplied = false;
        let couponFieldHasValue = false;
        let emailIsChecking = false;

        let cpfIsValid = false;
        let cpfExists = false;
        let cpfIsChecking = false;

        const LOGIN_URL = "{{ route('login') }}";
        const PASSWORD_URL = "{{ route('password.request') }}";
        const PORTAL_URL = "{{ config('custom.portal_link') }}";

        function updateStep1Button() {
            const $step1 = $('[data-step-content="1"]');
            const $btnStep1 = $step1.find('.btn-next');
            const planSelected = $('#plan_id').val() !== '';

            let canProceed = false;

            if (planSelected) {
                if (!couponFieldHasValue) {
                    canProceed = true;
                    $('#couponWarning').addClass('d-none');
                } else if (couponApplied) {
                    canProceed = true;
                    $('#couponWarning').addClass('d-none');
                } else {
                    canProceed = false;
                    $('#couponWarning').removeClass('d-none');
                }
            }

            $btnStep1.prop('disabled', !canProceed);
        }

        $(function() {
            initSelects2();
            initMasks();

            const $dependentesFields = $('#dependentes-fields');

            $('#plan_id option').each(function() {
                $(this).data('original-text', $(this).text());
            });

            $('#plan_id').on('change', function() {
                const planId = $(this).val();
                const telemedicine = $(this).find(':selected').data('telemedicine');

                if (telemedicine == 1) {
                    $dependentesFields.show();
                } else {
                    $dependentesFields.hide();
                    $dependentesFields.find('input').val('');
                }

                updateStep1Button();
            });

            const initialTelemedicine = $('#plan_id').find(':selected').data('telemedicine');
            if (initialTelemedicine == 1) {
                $dependentesFields.show();
            } else {
                $dependentesFields.hide();
            }

            updateStep1Button();

            $('#coupon').on('input', function() {
                const value = $(this).val().trim();
                couponFieldHasValue = value.length > 0;

                if (!couponFieldHasValue) {
                    couponApplied = false;
                    $('#couponFeedback').text('').removeClass('text-success text-danger');
                    $('#couponWarning').addClass('d-none');

                    const $selectedOption = $('#plan_id option:selected');
                    if ($selectedOption.length) {
                        const originalText = $selectedOption.data('original-text');
                        if (originalText) {
                            $selectedOption.text(originalText);
                        }
                    }

                    $('#total_with_discounted').val('');
                    toggleStep4Visibility();
                }

                updateStep1Button();
            });

            initStepNavigation();

            $('#name').on('input', function() {
                const input = this;

                nextTick(() => {
                    const value = input.value.trim();
                    const parts = value.split(/\s+/).filter(p => p.length > 0);
                    const hasFullName = parts.length >= 2;

                    if (!hasFullName && value !== '') {
                        $('#name-error').removeClass('d-none');
                        $(input).addClass('is-invalid');
                    } else {
                        $('#name-error').addClass('d-none');
                        $(input).removeClass('is-invalid');
                    }

                    updateStep2Button();
                });
            });

            let cpfTouched = false;
            let cpfTimeout = null;

            $('#document').on('input', function() {
                cpfTouched = true;

                const raw = $(this).val();
                const cpf = raw.replace(/\D/g, '');

                clearTimeout(cpfTimeout);

                cpfIsValid = false;
                cpfExists = false;
                cpfIsChecking = false;

                $('#cpf-invalid, #cpf-exists, #cpf-checking').addClass('d-none');
                $(this).removeClass('is-invalid');

                if (cpf.length < 11) {
                    updateStep2Button();
                    return;
                }

                if (!isValidCPF(cpf)) {
                    $('#cpf-invalid').removeClass('d-none');
                    $(this).addClass('is-invalid');
                    updateStep2Button();
                    return;
                }

                cpfIsValid = true;
                cpfIsChecking = true;
                $('#cpf-checking').removeClass('d-none');
                updateStep2Button();

                cpfTimeout = setTimeout(() => {
                    $.post('{{ route('check.cpf') }}', {
                        cpf: cpf,
                        _token: '{{ csrf_token() }}'
                    }).done(response => {
                        cpfExists = response.exists;

                        if (cpfExists) {
                            $('#cpf-exists').removeClass('d-none');
                            $('#document').addClass('is-invalid');
                        }

                        cpfIsChecking = false;
                        $('#cpf-checking').addClass('d-none');
                        updateStep2Button();
                    }).fail(() => {
                        cpfIsChecking = false;
                        $('#cpf-checking').addClass('d-none');
                        updateStep2Button();
                    });
                }, 400);

                updateStep2Button();
            });

            $('#mobile').on('input blur', function() {
                updateStep2Button();
            });

            let emailTimeout = null;

            syncUsernameWithEmail('', false);

            $('#email').on('input', function() {
                const input = this;

                emailIsValid = false;
                emailExistsApp = false;
                emailExistsStreaming = false;
                emailIsChecking = false;

                $('#email-exists').addClass('d-none');
                $('#email-actions').empty();
                $('#email-checking').addClass('d-none');

                nextTick(() => {
                    const value = input.value.trim();

                    clearTimeout(emailTimeout);

                    $('#email-error, #email-exists-app, #email-exists-streaming').addClass(
                        'd-none');
                    $(input).removeClass('is-invalid');

                    if (!value) {
                        updateStep2Button();
                        return;
                    }

                    if (!isValidEmail(value)) {
                        $('#email-error').removeClass('d-none');
                        $(input).addClass('is-invalid');
                        updateStep2Button();
                        return;
                    }

                    emailIsValid = true;
                    emailIsChecking = true;
                    $('#email-checking').removeClass('d-none');
                    updateStep2Button();

                    emailTimeout = setTimeout(() => {
                        Promise.all([
                            $.post('{{ route('check.email') }}', {
                                email: value,
                                _token: '{{ csrf_token() }}'
                            }),
                            $.post('{{ route('check.email.streaming') }}', {
                                email: value,
                                _token: '{{ csrf_token() }}'
                            })
                        ]).then(([appResponse, streamingResponse]) => {
                            emailExistsApp = appResponse.exists;
                            emailExistsStreaming = streamingResponse.exists;

                            updateEmailExistsMessage();

                            if (emailExistsApp || emailExistsStreaming) {
                                $('#email').addClass('is-invalid');
                            } else {
                                $('#email').removeClass('is-invalid');
                            }

                            const emailFullyValid = emailIsValid && !
                                emailExistsApp && !emailExistsStreaming;

                            syncUsernameWithEmail(emailFullyValid ? value : '',
                                emailFullyValid);

                            emailIsChecking = false;
                            $('#email-checking').addClass('d-none');
                            updateStep2Button();

                        }).catch(error => {
                            console.error('Erro ao verificar email:', error);
                            emailIsChecking = false;
                            $('#email-checking').addClass('d-none');

                            $('#email-error').removeClass('d-none').text(
                                '⚠️ Erro ao verificar email. Tente novamente.');
                            $('#email').addClass('is-invalid');

                            updateStep2Button();
                        });

                    }, 400);

                    updateStep2Button();
                });
            });

            function updateStep2Button() {
                const $step2 = $('[data-step-content="2"]');
                const $btnStep2 = $step2.find('.btn-next');

                const stepValid =
                    validateRequiredFields($step2) &&
                    cpfIsValid &&
                    !cpfExists &&
                    !cpfIsChecking &&
                    emailIsValid &&
                    !emailExistsApp &&
                    !emailExistsStreaming &&
                    !emailIsChecking;

                $btnStep2.prop('disabled', !stepValid);

                updateStepStatus();
            }

            $('form').on('submit', function() {
                $('#name-error').addClass('d-none');
                $('#name').removeClass('is-invalid');
            });

            const $password = $('#password');
            const $confirm = $('#password_confirmation');
            const $btnNext = $('#btnNextStep');

            let passwordValid = false;
            let confirmValid = false;

            function validateForm() {
                let password = $password.val();
                let confirm = $confirm.val();

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

                const requiredFieldsValid = validateRequiredFields(
                    $('[data-step-content="3"]')
                );

                $btnNext.prop('disabled', !(passwordValid && confirmValid && requiredFieldsValid));
            }

            $password.on('input', validateForm);
            $confirm.on('input', validateForm);

            // Step 2
            const $step2 = $('[data-step-content="2"]');
            const $btnStep2 = $step2.find('.btn-next');

            $step2.find('input').on('input', function() {
                $btnStep2.prop('disabled', !validateRequiredFields($step2));
            });

            // Step 3
            let requiredFieldsValid = validateRequiredFields(
                $('[data-step-content="3"]')
            );

            $btnNext.prop('disabled', !(passwordValid && confirmValid && requiredFieldsValid));

            // Step 4
            const $step4 = $('[data-step-content="4"]');
            const $btnSubmit = $step4.find('.btn-submit');

            $step4.find('input').on('input change', function() {
                const isValid = validateRequiredFields($step4);

                $btnSubmit.prop('disabled', !isValid);
                updateStepStatus();
            });

            $(document).ready(function() {
                $btnSubmit.prop('disabled', !validateRequiredFields($step4));
            });

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

            if (INITIAL_STEP > 1) {
                navigateToStep(INITIAL_STEP);
                updateStepStatus();
            }

            if ($('.alert-danger').length) {
                $('html, body').animate({
                    scrollTop: $('.alert-danger').offset().top - 20
                }, 400);
            }

            $('#card_number').on('input', function() {
                const number = $(this).val().replace(/\D/g, '');
                const brand = detectCardBrand(number);

                const $cvv = $('#card_ccv');
                const $brandIcon = $('#card-brand');

                if (brand === 'amex') {
                    $cvv.attr({
                        maxlength: 4,
                        minlength: 4,
                        placeholder: '0000'
                    });
                } else {
                    $cvv.attr({
                        maxlength: 3,
                        minlength: 3,
                        placeholder: '000'
                    });
                }

                if (brand) {
                    $brandIcon
                        .attr('src', `/images/cards/${brand}.svg`)
                        .attr('alt', brand)
                        .removeClass('d-none');
                } else {
                    $brandIcon
                        .attr('src', '')
                        .attr('alt', '')
                        .addClass('d-none');
                }
            });

        });

        function detectCardBrand(number) {
            if (/^4/.test(number)) return 'visa';
            if (/^(5[1-5]|2[2-7])/.test(number)) return 'mastercard';
            if (/^3[47]/.test(number)) return 'amex';
            if (/^6(?:011|5)/.test(number)) return 'discover';
            if (/^3(?:0[0-5]|[68])/.test(number)) return 'diners';
            if (/^35/.test(number)) return 'jcb';
            if (/^(4011|4312|4389|4514|4576|5041)/.test(number)) return 'elo';
            return null;
        }

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

        function revalidateStep2() {
            const $step2 = $('[data-step-content="2"]');
            const $btnStep2 = $step2.find('.btn-next');

            const cpf = ($('#document').val() || '').replace(/\D/g, '');
            const email = ($('#email').val() || '').trim();

            cpfIsValid = cpf.length === 11 && isValidCPF(cpf);
            cpfExists = false;
            cpfIsChecking = false;

            $('#cpf-invalid, #cpf-exists, #cpf-checking').addClass('d-none');
            $('#document').removeClass('is-invalid');

            if (cpf && !cpfIsValid) {
                $('#cpf-invalid').removeClass('d-none');
                $('#document').addClass('is-invalid');
            }

            emailIsValid = isValidEmail(email);
            emailExistsApp = false;
            emailExistsStreaming = false;
            emailIsChecking = false;

            $('#email-error, #email-checking').addClass('d-none');
            $('#email').removeClass('is-invalid');

            if (email && !emailIsValid) {
                $('#email-error').removeClass('d-none');
                $('#email').addClass('is-invalid');
            }

            const stepValid =
                validateRequiredFields($step2) &&
                cpfIsValid &&
                !cpfExists &&
                !cpfIsChecking &&
                emailIsValid &&
                !emailExistsApp &&
                !emailExistsStreaming &&
                !emailIsChecking;

            $btnStep2.prop('disabled', !stepValid);

            updateStepStatus();
        }

        function revalidateStep3() {
            const $step3 = $('[data-step-content="3"]');

            const password = $('#password').val() || '';
            const confirm = $('#password_confirmation').val() || '';

            if (!$('#password').length) {
                $('#btnNextStep').prop('disabled', !validateRequiredFields($step3));
                return;
            }

            passwordValid = validatePasswordRules(password);
            confirmValid = confirm.length > 0 && password === confirm;

            const requiredFieldsValid = validateRequiredFields($step3);

            $('#btnNextStep').prop(
                'disabled',
                !(passwordValid && confirmValid && requiredFieldsValid)
            );
        }

        function toggleStep4Visibility() {
            const total = $('#total_with_discounted').val();
            const isFree = total === '0,00';

            $('#access_free').toggle(isFree);

            $('#credit-card-fields').toggle(!isFree);

            $('#credit-card-fields')
                .find('input')
                .prop('required', !isFree);
        }

        function updateEmailExistsMessage() {
            const $wrapper = $('#email-exists');
            const $actions = $('#email-actions');

            $actions.empty();

            if (!emailExistsApp && !emailExistsStreaming) {
                $wrapper.addClass('d-none');
                return;
            }

            if (emailExistsApp) {
                $actions.append(`
                <a href="${LOGIN_URL}"
                class="ml-1 text-cyan font-weight-bold btn btn-default">
                Minha conta
                </a>

                <span class="mx-1">|</span>

                <a href="${PASSWORD_URL}"
                class="text-cyan font-weight-bold btn btn-default">
                Recuperar senha
                </a>
            `);
            }

            if (emailExistsStreaming) {
                if (emailExistsApp) {
                    $actions.append(`<span class="mx-1">|</span>`);
                }

                $actions.append(`
                <a href="${PORTAL_URL}"
                target="_blank"
                class="text-cyan font-weight-bold btn btn-default">
                Portal
                </a>
            `);
            }

            $wrapper.removeClass('d-none');
        }

        function syncUsernameWithEmail(email, isValid) {
            const $username = $('#usuario');

            if ($username.val()) {
                return;
            }

            if (isValid) {
                $username.val(email);
            }
        }


        function nextTick(fn) {
            requestAnimationFrame(fn);
        }

        function isValidCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');

            if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) {
                return false;
            }

            let sum = 0;
            let remainder;

            for (let i = 1; i <= 9; i++) {
                sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
            }

            remainder = (sum * 10) % 11;
            if (remainder === 10 || remainder === 11) remainder = 0;
            if (remainder !== parseInt(cpf.substring(9, 10))) return false;

            sum = 0;
            for (let i = 1; i <= 10; i++) {
                sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
            }

            remainder = (sum * 10) % 11;
            if (remainder === 10 || remainder === 11) remainder = 0;
            if (remainder !== parseInt(cpf.substring(10, 11))) return false;

            return true;
        }

        function isValidFullName(name) {
            if (!name) return false;

            const parts = name.trim().split(/\s+/);
            return parts.length >= 2;
        }

        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function validateRequiredFields($step) {
            let isValid = true;

            $step.find('input, select, textarea').each(function() {
                const $field = $(this);

                if (!$field.prop('required') || !$field.is(':visible')) {
                    return;
                }

                const type = $field.attr('type');
                const value = $field.val();
                const id = $field.attr('id');

                if (type === 'checkbox') {
                    if (!$field.is(':checked')) {
                        isValid = false;
                    }
                } else if (type === 'email') {
                    if (!value || !isValidEmail(value)) {
                        isValid = false;
                    }
                } else if (id === 'name') {
                    if (!isValidFullName(value)) {
                        isValid = false;
                    }
                } else if ($field.is('select')) {
                    if (!value) {
                        isValid = false;
                    }
                } else {
                    if (!value) {
                        isValid = false;
                    }
                }
            });

            return isValid;
        }

        function updateStepStatus() {
            const currentStep = $('.step.active').data('step');

            $('.step').each(function() {
                const stepNumber = $(this).data('step');
                const $stepContent = $('[data-step-content="' + stepNumber + '"]');

                $(this).removeClass('completed');

                if (stepNumber < currentStep) {
                    if (validateRequiredFields($stepContent)) {
                        $(this).addClass('completed');
                    }
                }

                if (stepNumber === currentStep) {
                    if (validateRequiredFields($stepContent)) {
                        $(this).addClass('completed');
                    }
                }
            });
        }

        function initSelects2() {
            $('#plan_id').select2({
                theme: "bootstrap4",
                allowClear: true,
            });
        }

        function initMasks() {
            $('#document').mask('000.000.000-00');
            $('#cpf_dependente_1').mask('000.000.000-00');
            $('#cpf_dependente_2').mask('000.000.000-00');
            $('#cpf_dependente_3').mask('000.000.000-00');
            $('#card_number').mask('0000 0000 0000 0000');
            $('#mobile').mask('(00) 00000-0000');
        }

        function initStepNavigation() {
            $('.btn-next').on('click', function() {
                const nextStep = $(this).data('next');

                navigateToStep(nextStep);
                updateStepStatus();
            });

            $('.btn-back').on('click', function() {
                const prevStep = $(this).data('prev');
                navigateToStep(prevStep);
                updateStepStatus();
            });
        }

        function navigateToStep(stepNumber) {
            toggleStep4Visibility();

            $('.step-content').removeClass('active');
            $(`.step-content[data-step-content="${stepNumber}"]`).addClass('active');

            const progressPercentage = ((stepNumber - 1) / 3) * 100;
            $('.step-progress-bar').css('width', progressPercentage + '%');

            $('.step').removeClass('active');
            $(`.step[data-step="${stepNumber}"]`).addClass('active');

            if (stepNumber === 2) {
                revalidateStep2();
            }

            if (stepNumber === 3) {
                revalidateStep3();
            }
        }

        function selectPlan(planId) {
            $('#plan_id').val(planId).trigger('change');
        }

        document.getElementById('applyCoupon').addEventListener('click', function() {
            const coupon = document.getElementById('coupon').value.trim();
            const planId = document.querySelector('select[name="plan_id"]').value;

            if (!planId) {
                document.getElementById('couponFeedback').innerText = 'Selecione um plano primeiro.';
                document.getElementById('couponFeedback').className = 'form-text text-danger';
                return;
            }

            if (!coupon) {
                document.getElementById('couponFeedback').innerText = 'Digite um cupom.';
                document.getElementById('couponFeedback').className = 'form-text text-danger';
                return;
            }

            fetch('/validate-coupon', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        coupon: coupon,
                        plan_id: planId
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    const feedback = document.getElementById('couponFeedback');
                    if (data.valid) {
                        couponApplied = true;
                        feedback.innerText = data.message;
                        feedback.className = 'form-text text-success';

                        const selectedOption = document.querySelector(
                            `select[name="plan_id"] option[value="${planId}"]`);
                        if (selectedOption) {
                            const originalText = selectedOption.getAttribute('data-original-text') ||
                                $(selectedOption).data('original-text');
                            const planName = originalText.split(' - ')[0];
                            selectedOption.innerText = `${planName} - R$ ${data.discounted_value}`;
                        }

                        $("#total_with_discounted").val(data.discounted_value);
                        toggleStep4Visibility();

                        updateStep1Button();
                    } else {
                        couponApplied = false;
                        feedback.innerText = data.message;
                        feedback.className = 'form-text text-danger';

                        $("#total_with_discounted").val('');
                        toggleStep4Visibility();

                        updateStep1Button();
                    }
                })
                .catch(error => {
                    console.error('Erro ao validar cupom:', error);
                    couponApplied = false;
                    document.getElementById('couponFeedback').innerText =
                        'Erro ao validar cupom. Tente novamente.';
                    document.getElementById('couponFeedback').className = 'form-text text-danger';
                    updateStep1Button();
                });
        });
    </script>
@endsection
