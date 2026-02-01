<div class="modal-body">
    <div class="row d-flex align-items-center">
        <div class="form-group col-12 col-md-6">
            <label for="photo" class="col-form-label">Foto:</label>
            <div class="input-group">
                <input type="file" id="photo" name="photo">
            </div>
        </div>

        @if ($user->photo)
            <div class="col-6">
                <div class="card">
                    <div class="card-body m-auto">
                        <img width="100" data-url="/user/removeImage/" data-id="{{ $user->id }}"
                            data-token={{ csrf_token() }} src="{{ asset($user->photo) }}" alt="">
                        @if ($user->photo != 'avatars/default.png')
                            <button type="button" class="btn-remove" title="Remover">x</button>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="col-6">
                <div class="card">
                    <div class="card-body m-auto">
                        <img width="100" src="{{ asset('Auth-Panel/dist/img/not-image.png') }}" alt="">
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="form-group col-6">
            <label for="name" class="col-form-label text-danger">Nome: *</label>
            <div class="input-group flex-column">
                <input type="text" id="name" class="form-control w-100" name="name" placeholder="Nome *"
                    value="{{ $user->name ?? old('name') }}" required>
                <span id="name-error" class="mt-1 small text-danger d-none">
                    ⚠️ Por favor, informe seu nome e sobrenome.
                </span>
            </div>
        </div>
        <div class="form-group col-3">
            <label for="login" class="col-form-label text-danger">Usuário: *</label>
            <div class="input-group">
                <input type="text" id="login" class="form-control" name="login" placeholder="Usuário *"
                    value="{{ $user->login ?? old('login') }}" required>
            </div>
        </div>

        <div class="form-group col-3">
            <label for="access_id" class="col-form-label text-danger">Perfil: *</label>
            <select id="access_id" class="form-control" name="access_id" required>
                @foreach ($accesses as $access)
                    <option value='{{ $access->id }}' {{ $user->access_id == $access->id ? 'selected' : '' }}>
                        {{ $access->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12 col-md-4">
            <label for="email" class="col-form-label text-danger">E-mail: *</label>
            <div class="input-group">
                <input type="email" id="email" class="form-control" name="email" placeholder="E-mail *"
                    value="{{ $user->email ?? old('email') }}" required>
            </div>
        </div>

        <div class="form-group col-4">
            <label for="document" class="col-form-label text-danger">CPF: *</label>
            <div class="input-group">
                <input type="text" id="document" class="form-control" name="document" placeholder="CPF *"
                    value="{{ $user->customer->document ?? old('document') }}" required>
            </div>
        </div>

        <div class="form-group col-4">
            <label for="mobile" class="col-form-label text-danger">Celular: *</label>
            <div class="input-group">
                <input type="text" id="mobile" class="form-control" name="mobile" placeholder="Celular *"
                    value="{{ $user->customer->mobile ?? old('mobile') }}" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12 col-md-4 position-relative">
            <label for="password" class="col-form-label">Senha:</label>
            <div class="input-group position-relative">
                <input type="password" id="password" class="form-control" name="password" placeholder="Senha"
                    autocomplete="off">
                <span class="toggle-password-modal" data-target="#password"
                    style="position: absolute; right: 12px; top: 8px; cursor: pointer; color: #6c757d; z-index: 5;">
                    <i class="fa fa-eye"></i>
                </span>
            </div>
        </div>

        <div class="form-group col-12 col-md-4 position-relative">
            <label for="password_confirmation" class="col-form-label">Confirmar senha:</label>
            <div class="input-group position-relative">
                <input type="password" id="password_confirmation" class="form-control" name="password_confirmation"
                    placeholder="Confirmar senha" autocomplete="off">
                <span class="toggle-password-modal" data-target="#password_confirmation"
                    style="position: absolute; right: 12px; top: 8px; cursor: pointer; color: #6c757d; z-index: 5;">
                    <i class="fa fa-eye"></i>
                </span>
            </div>
        </div>

        <div class="col-12">
            <ul id="passwordRulesModal" class="list-unstyled mt-2 d-none">
                <li id="rule-length-modal" class="text-muted small">✖ Mínimo de 6 caracteres</li>
                <li id="rule-uppercase-modal" class="text-muted small">✖ Pelo menos 1 letra maiúscula</li>
                <li id="rule-number-modal" class="text-muted small">✖ Pelo menos 1 número</li>
                <li id="rule-special-modal" class="text-muted small">✖ Pelo menos 1 caractere especial</li>
            </ul>
            <small id="passwordConfirmHelpModal" class="form-text text-muted d-none">
                Repita a senha digitada acima.
            </small>
        </div>
    </div>
</div>

<script>
    function getFormData() {
        const formData = new FormData()

        formData.append('id', $("#id").val());
        formData.append('name', $("#name").val());
        formData.append('login', $("#login").val());
        formData.append('access_id', $("#access_id").val());
        formData.append('email', $("#email").val());
        formData.append('password', $("#password").val());
        formData.append('password_confirmation', $("#password_confirmation").val());
        formData.append('document', $("#document").val());
        formData.append('mobile', $("#mobile").val());

        if ($('#photo').length) {
            if (document.getElementById('photo').files.length) {
                formData.append('photo', document.getElementById('photo').files[0])
            }
        }

        return formData;
    }

    $(function() {
        initSelects2();
        initMasks();

        const $nameInput = $('#name');

        validateFullNameInput($nameInput);

        $nameInput.on('blur input', function() {
            validateFullNameInput($(this));
        });

        const $password = $('#password');
        const $confirm = $('#password_confirmation');
        const $rulesContainer = $('#passwordRulesModal');
        const $confirmHelp = $('#passwordConfirmHelpModal');
        const $submitBtn = $('.btn-submit');

        let passwordValid = true;
        let confirmValid = true;

        function validatePasswordRules(password) {
            if (password.length === 0) {
                $rulesContainer.addClass('d-none');
                return true;
            }

            $rulesContainer.removeClass('d-none');

            let rules = {
                length: password.length >= 6,
                uppercase: /[A-Z]/.test(password),
                number: /\d/.test(password),
                special: /[@$!%*#?&._-]/.test(password)
            };

            $('#rule-length-modal')
                .toggleClass('text-success', rules.length)
                .toggleClass('text-muted', !rules.length)
                .html(`${rules.length ? '✔' : '✖'} Mínimo de 6 caracteres`);

            $('#rule-uppercase-modal')
                .toggleClass('text-success', rules.uppercase)
                .toggleClass('text-muted', !rules.uppercase)
                .html(`${rules.uppercase ? '✔' : '✖'} Pelo menos 1 letra maiúscula`);

            $('#rule-number-modal')
                .toggleClass('text-success', rules.number)
                .toggleClass('text-muted', !rules.number)
                .html(`${rules.number ? '✔' : '✖'} Pelo menos 1 número`);

            $('#rule-special-modal')
                .toggleClass('text-success', rules.special)
                .toggleClass('text-muted', !rules.special)
                .html(`${rules.special ? '✔' : '✖'} Pelo menos 1 caractere especial`);

            return Object.values(rules).every(Boolean);
        }

        function validateConfirm(password, confirm) {
            if (password.length === 0 && confirm.length === 0) {
                return true;
            }
            if (password.length > 0 && confirm.length === 0) {
                return false;
            }
            return password === confirm;
        }

        function validatePasswordForm() {
            let password = $password.val();
            let confirm = $confirm.val();

            if (password.length === 0 && confirm.length === 0) {
                passwordValid = true;
                confirmValid = true;
                $confirmHelp.addClass('d-none');
                $rulesContainer.addClass('d-none');
                updateSubmitButton();
                return;
            }

            if (password.length > 0) {
                passwordValid = validatePasswordRules(password);
            } else {
                passwordValid = false;
                $rulesContainer.addClass('d-none');
            }

            confirmValid = validateConfirm(password, confirm);

            if (password.length === 0 && confirm.length === 0) {
                $confirmHelp.addClass('d-none');
            } else if (confirm.length === 0 && password.length > 0) {
                $confirmHelp.removeClass('d-none')
                    .text('Repita a senha digitada acima.')
                    .removeClass('text-success text-danger')
                    .addClass('text-muted');
            } else if (confirm.length > 0) {
                $confirmHelp.removeClass('d-none');
                if (confirmValid) {
                    $confirmHelp.text('As senhas conferem ✔')
                        .removeClass('text-danger text-muted')
                        .addClass('text-success');
                } else {
                    $confirmHelp.text('As senhas não conferem.')
                        .removeClass('text-success text-muted')
                        .addClass('text-danger');
                }
            }

            updateSubmitButton();
        }

        function updateSubmitButton() {
            const canSubmit = (passwordValid && confirmValid);

            if ($submitBtn.length) {
                $submitBtn.prop('disabled', !canSubmit);
            }
        }

        $password.on('input', validatePasswordForm);
        $confirm.on('input', validatePasswordForm);

        $(document).on('click', '.toggle-password-modal', function() {
            const targetSelector = $(this).data('target');
            const $target = $(targetSelector);
            const $icon = $(this).find('i');

            if ($target.attr('type') === 'password') {
                $target.attr('type', 'text');
                $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                $target.attr('type', 'password');
                $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        $('#password, #password_confirmation').on('copy paste cut', function(e) {
            e.preventDefault();
        });

        validatePasswordForm();
    });

    function validateFullNameInput($input) {
        const value = $input.val().trim();
        const parts = value.split(/\s+/).filter(part => part.length > 0);
        const hasFullName = parts.length >= 2;

        if (!hasFullName && value !== '') {
            $('#name-error').removeClass('d-none');
            $input.addClass('is-invalid');
        } else {
            $('#name-error').addClass('d-none');
            $input.removeClass('is-invalid');
        }
    }

    function initSelects2() {
        $('#access_id').select2({
            theme: "bootstrap4",
            placeholder: "Perfis",
            allowClear: true,
        });
    }

    function initMasks() {
        $('#document').mask('000.000.000-00');
        $('#mobile').mask('(00) 00000-0000');
    }
</script>
