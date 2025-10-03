@extends('auth.template.index')

@section('css')
    <link rel="stylesheet" href="{{ asset('Auth-Panel/dist/css/front/front.css') }}">
@endsection

<style>
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

    .btn-next, .btn-submit {
        background: {{ config('custom.button_color_entrar') }};
        color: white;
        margin-left: auto;
    }

    .btn-next:hover, .btn-submit:hover {
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

@section('content')
    <div class="register-box flex-column" >
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
                                    <option value="{{ $plan->id }}" data-telemedicine="{{ $plan->is_active_telemedicine }}" @selected($plan->id == $planId)>
                                        {{ $plan->name . ' - ' . number_format($plan->value, 2, ',', '.') }}
                                        
                                    </option>
                                @endforeach
                            </select>

                            <div class="form-group mt-3">
                                <label for="coupon" style="color:{{ config('custom.text_color_form') }}">Cupom de Desconto</label>
                                <div class="d-flex gap-2">
                                    <input type="text" id="coupon" name="coupon" class="form-control"
                                           placeholder="Digite seu cupom">
                                    <button type="button" id="applyCoupon" class="btn btn-primary">Aplicar</button>
                                </div>
                                <small id="couponFeedback" class="form-text text-danger"></small>
                            </div>
                        </div>

                        <div class="navigation-buttons">
                            <button type="button" class="btn btn-nav btn-next" data-next="2" style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Próximo</button>
                        </div>
                    </div>

                    <!-- Step 2: Personal Info -->
                    <div class="step-content" data-step-content="2">
                        <div class="input-group mb-3">
                            <label class="title-input2" for="name">Qual seu nome completo *</label>
                            <input type="text" name="name" id="name" class="form-control"
                                   placeholder="Digite seu nome completo *" required
                                   value="{{ old('name', session('customerData')['name'] ?? '') }}">
                        </div>

                        @error('name')
                        <span class="text-danger">{{ $message }}</span>
                        <hr>
                        @enderror

                        <div class="input-group mb-3">
                            <label class="title-input2" for="document">CPF *</label>
                            <input type="text" @error('document') has-error @enderror value="{{ old('document') ?? '' }}"
                                   name="document" id="document" class="form-control" placeholder="Digite seu cpf *"
                                   required>
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
                                   name="mobile" id="mobile" class="form-control" placeholder="(00) 00000-0000" required>
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
                        </div>

                        @error('email')
                        <span class="text-danger">{{ $message }}</span>
                        <hr>
                        @enderror

                        <div class="navigation-buttons">
                            <button type="button" class="btn btn-nav btn-back" data-prev="1" style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Voltar</button>
                            <button type="button" class="btn btn-nav btn-next" data-next="3" style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Próximo</button>
                        </div>
                    </div>

                    <!-- Step 3: Credentials -->
                    <div class="step-content" data-step-content="3">
                        <div class="input-group mb-3">
                            <label class="title-input2" for="usuario">Digite seu usuário *</label>
                            <input type="text" name="login" id="usuario" class="form-control" placeholder="Usuário *"
                                   required value="{{ old('login', session('customerData')['login'] ?? '') }}">
                        </div>

                        @error('login')
                        <span class="text-danger">{{ $message }}</span>
                        <hr>
                        @enderror

                        @if (
                            !session()->has('customerData') ||
                                (session()->has('customerData') && session('customerData')['source'] !== 'temporarily'))
                            <div class="input-group mb-3">
                                <label class="title-input2" for="password">Crie sua senha *</label>
                                <input type="password" @error('password') has-error @enderror
                                value="{{ session()->has('authenticate') ? session('customerData')['password'] : '' }}"
                                       name="password" id="password" class="form-control" placeholder="Crie uma senha forte"
                                       required {{ session()->has('authenticate') ? 'readonly' : '' }}>
                            </div>

                            @error('password')
                            <span class="text-danger">{{ $message }}</span>
                            <hr>
                            @enderror

                            <div class="input-group mb-3">
                                <label class="title-input2" for="password_confirmation">Confirmação de senha *</label>
                                <input type="password" @error('password_confirmation') has-error @enderror
                                value="{{ old('password_confirmation') ?? '' }}" name="password_confirmation"
                                       id="password_confirmation" class="form-control" placeholder="Repita sua senha"
                                       required>
                            </div>

                            @error('password_confirmation')
                            <span class="text-danger">{{ $message }}</span>
                            <hr>
                            @enderror
                        @endif

                        <div class="navigation-buttons">
                            <button type="button" class="btn btn-nav btn-back" data-prev="2" style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Voltar</button>
                            <button type="button" class="btn btn-nav btn-next" data-next="4" style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Próximo</button>
                        </div>
                    </div>

                    <!-- Step 4: Payment -->
                    <div class="step-content" data-step-content="4">
                        <div class="input-group mb-3">
                            <label class="title-input2" for="card_number">Número do cartão</label>
                            <input name="credit_card_number" id="card_number" class="form-control" placeholder="Informe o número do cartão" min="13" maxlength="19"
                                   required value="{{ old('credit_card_number', session('customerData')['credit_card_number'] ?? '') }}">
                        </div>

                        <div class="input-group mb-3">
                            <label class="title-input2" for="card_name">Nome do titular do cartão</label>
                            <input type="text" name="credit_card_name" id="card_name" class="form-control" placeholder="Nome do titular do cartão"
                                   required value="{{ old('credit_card_name', session('customerData')['credit_card_name'] ?? '') }}">
                        </div>

                        <div class="input-group mb-3">
                            <label class="title-input2" for="card_expiry_month">Mês</label>
                            <input type="text" name="credit_card_expiry_month" id="card_expiry_month" class="form-control form-group" placeholder="00" min="2" maxlength="2"
                                   required value="{{ old('credit_card_expiry_month', session('customerData')['credit_card_expiry_month'] ?? '') }}">

                            <label class="title-input2" for="card_expiry_year">Ano</label>
                            <input type="text" name="credit_card_expiry_year" id="card_expiry_year" class="form-control form-group" placeholder="0000" minlength="4" maxlength="4"
                                   required value="{{ old('credit_card_expiry_year', session('customerData')['credit_card_expiry_year'] ?? '') }}">
                        </div>

                        <div class="input-group mb-3">
                            <label class="title-input2" for="card_ccv">CVV</label>
                            <input type="text" name="credit_card_ccv" id="card_ccv" class="form-control form-group" placeholder="000" minlength="3" maxlength="4"
                                   required value="{{ old('credit_card_ccv', session('customerData')['credit_card_ccv'] ?? '') }}">
                        </div>

                        @error('login')
                        <span class="text-danger">{{ $message }}</span>
                        <hr>
                        @enderror



                        <div class="navigation-buttons">
                            <button type="button" class="btn btn-nav btn-back" data-prev="3" style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Voltar</button>
                            <button type="submit" class="btn btn-nav btn-submit" style="background-color:{{ config('custom.background_button_next_prev') }}; color:{{ config('custom.text_color_button_next_prev') }};">Finalizar Cadastro</button>
                        </div>
                    </div>

                    <div class="footer-links">
                        <a href="{{ route('login') }}">
                            <i class="fa fa-user-plus mr-2"></i> Já tenho conta
                        </a>
                        <a href="{{ url('/') }}">
                            <i class="fa fa-home mr-2"></i> Voltar para Home
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <footer class="section-container d-flex flex-column align-items-center footer-register"
            style="background-color: {{ config('custom.background_baseboard') }};">
        <p>{{ config('custom.text_baseboard') }}</p>

        <div class="d-flex align-items-center justify-content-center w-100 position-relative container-media flex-column flex-sm-row">
            <div class="social-media d-flex justify-content-center">
                <div class="container-social-media"
                     style="background-color: {{ config('custom.background_social_media') }};">
                    <a href="{{ config('custom.link_social_media_1') }}"><img
                            src="{{ config('custom.image_social_media_1') }}" alt=""></a>
                </div>
                <div class="container-social-media"
                     style="background-color: {{ config('custom.background_social_media') }};">
                    <a href="{{ config('custom.link_social_media_2') }}"><img
                            src="{{ config('custom.image_social_media_2') }}" alt=""></a>
                </div>
                <div class="container-social-media"
                     style="background-color: {{ config('custom.background_social_media') }};">
                    <a href="{{ config('custom.link_social_media_3') }}"><img
                            src="{{ config('custom.image_social_media_3') }}" alt=""></a>
                </div>
            </div>
            <img class="logo-footer" src="{{ config('custom.logo_baseboard') }}" alt="">
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
                                        @if($plan->is_best_seller)
                                            <div class="best-seller-badge">Mais vendido</div>
                                        @endif
                                        <h4>{{ $plan->name }}</h4>
                                        <div class="plan-price">R$ {{ number_format($plan->value, 2, ',', '.') }}</div>
                                        <ul class="plan-features">
                                            @foreach($plan->benefits as $benefit)
                                                <li>{{ $benefit->description }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn btn-primary w-100" onclick="selectPlan({{ $plan->id }})" data-dismiss="modal">
                                            Assinar
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-center mt-4">Curta nossas séries, filmes e conteúdos exclusivos feitos para você!</p>
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
        $(function () {
            initSelects2();
            initMasks();
            const $dependentesFields = $('#dependentes-fields');

            $('#plan_id').on('change', function () {
                const planId = $(this).val();
                const telemedicine = $(this).find(':selected').data('telemedicine');

                // console.log('Plano selecionado ID:', planId);
                // console.log('Is active telemedicine:', telemedicine);

                if (telemedicine == 1) {
                    $dependentesFields.show();                    
                    $dependentesFields.show();
                } else {
                    $dependentesFields.hide();                    
                    $dependentesFields.hide();
                    // opcional: limpar os campos ao esconder
                    $dependentesFields.find('input').val('');                    
                    $dependentesFields.find('input').val('');
                }
            });

            // Se o select já vier com valor selecionado ao carregar a página
            const initialTelemedicine = $('#plan_id').find(':selected').data('telemedicine');
            if (initialTelemedicine == 1) {
                $dependentesFields.show();                
                $dependentesFields.show();
            } else {
                $dependentesFields.hide();                
                $dependentesFields.hide();
            }
            initStepNavigation();
            
        });

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
            });

            $('.btn-back').on('click', function() {
                const prevStep = $(this).data('prev');
                navigateToStep(prevStep);
            });
        }

        function navigateToStep(stepNumber) {
            // Hide all step contents
            $('.step-content').removeClass('active');

            // Show current step content
            $(`.step-content[data-step-content="${stepNumber}"]`).addClass('active');

            // Update progress bar
            const progressPercentage = ((stepNumber - 1) / 3) * 100;
            $('.step-progress-bar').css('width', progressPercentage + '%');

            // Update step indicators
            $('.step').removeClass('active');
            $(`.step[data-step="${stepNumber}"]`).addClass('active');
        }

        function selectPlan(planId) {
            $('#plan_id').val(planId).trigger('change');
        }

        document.getElementById('applyCoupon').addEventListener('click', function () {
            const coupon = document.getElementById('coupon').value;
            const planId = document.querySelector('select[name="plan_id"]').value;

            if (!coupon || !planId) {
                document.getElementById('couponFeedback').innerText = 'Selecione um plano e insira um cupom.';
                return;
            }

            fetch('/validate-coupon', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ coupon: coupon, plan_id: planId }),
            })
                .then(response => response.json())
                .then(data => {
                    const feedback = document.getElementById('couponFeedback');
                    if (data.valid) {
                        feedback.innerText = data.message;
                        feedback.classList.remove('text-danger');
                        feedback.classList.add('text-success');

                        const selectedOption = document.querySelector(`select[name="plan_id"] option[value="${planId}"]`);
                        if (selectedOption) {
                            selectedOption.innerText = `${selectedOption.innerText.split(' - ')[0]} - R$ ${data.discounted_value}`;
                        }

                    } else {
                        feedback.innerText = data.message;
                        feedback.classList.remove('text-success');
                        feedback.classList.add('text-danger');
                    }
                });
        });
    </script>
@endsection
