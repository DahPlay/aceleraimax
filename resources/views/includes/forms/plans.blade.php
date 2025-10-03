<div class="modal-body">
    <div class="row">
        <div class="form-group col-12 col-md-2">
            <label for="priority" class="col-form-label text-danger">Prioridade: *</label>
            <div class="input-group">
                <input type="number" id="priority" class="form-control" name="priority" placeholder="Prioridade *"
                       value="{{ $plan->priority ?? old('priority') }}" required>
            </div>
        </div>

        <div class="form-group col-12 col-md-10">
            <label for="name" class="col-form-label text-danger">Nome: *</label>
            <div class="input-group">
                <input type="text" id="name" class="form-control" name="name" placeholder="Nome *"
                       value="{{ $plan->name ?? old('name') }}" required>
            </div>
        </div>

        <div class="form-group col-12 col-md-4 p-0 mt-5">
            <div class="custom-control custom-switch custom-switch-on-primary d-flex align-items-center"
                 style="width: 250px;">
                <input type="checkbox" class="custom-control-input overdue" name="is_active" id="is_active"
                        {{ $plan->is_active ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-normal ml-2" for="is_active">Ativo</label>
            </div>
        </div>

        <div class="form-group col-12 col-md-4 p-0 mt-5">
            <div class="custom-control custom-switch custom-switch-on-primary d-flex align-items-center"
                 style="width: 250px;">
                <input type="checkbox" class="custom-control-input overdue" name="is_best_seller" id="is_best_seller"
                        {{ $plan->is_best_seller ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-normal ml-2" for="is_best_seller">Mais vendido</label>
            </div>
        </div>
        <div class="form-group col-12 col-md-4 p-0 mt-5">
            <div class="custom-control custom-switch custom-switch-on-primary d-flex align-items-center"
                 style="width: 250px;">
                <input type="checkbox" class="custom-control-input overdue" name="is_active_telemedicine" id="is_active_telemedicine"
                        {{ $plan->is_active_telemedicine ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-normal ml-2" for="is_active_telemedicine">Dependentes</label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12 col-md-6">
            <label for="value" class="col-form-label text-danger">Preço: *</label>
            <div class="input-group">
                <input type="text" id="value" class="form-control" name="value" placeholder="Preço *"
                       value="{{ $plan->value ?? old('value') }}" required>
            </div>
        </div>

        <div class="form-group col-12 col-md-6">
            <label for="free_for_days" class="col-form-label text-danger">Dias grátis: *</label>
            <div class="input-group">
                <input type="number" id="free_for_days" class="form-control" name="free_for_days"
                       placeholder="Dias grátis *" value="{{ $plan->free_for_days ?? 0 }}" min="0" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12 col-md-6">
            <label for="cycle" class="col-form-label text-danger">Ciclo de pagamento: *</label>
            <select id="cycle" class="form-control" name="cycle" required>
                @foreach (\App\Enums\CycleAsaasEnum::cases() as $cycle)
                    <option value="{{ $cycle->value }}"
                            {{ old('cycle', $plan->cycle ?? '') == $cycle->value ? 'selected' : '' }}>
                        {{ $cycle->getName() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group col-12 col-md-6">
            <label for="billing_type" class="col-form-label text-danger">Tipo de pagamento: *</label>
            <select id="billing_type" class="form-control" name="billing_type" required>
                @foreach (\App\Enums\BillingTypeAsaasEnum::cases() as $billing_type)
                    <option value="{{ $billing_type->value }}"
                            {{ old('billing_type', $plan->billing_type ?? '') == $billing_type->value ? 'selected' : '' }}>
                        {{ $billing_type->getName() }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12">
            <label for="description" class="col-form-label text-danger">Descrição: *</label>
            <div class="input-group">
                <textarea name="description" id="description" class="form-control" required
                          placeholder="Descrição">{{ $plan->description ?? old('description') }}</textarea>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12">
            <label class="col-form-label text-danger">Selecione o(s) pacote(s): *</label>
            <div id="packages-container">
                @if (isset($packages) && count($packages) > 0)
                    @foreach ($packages as $index => $package)
                        <div class="packages-item d-flex align-items-center mb-2">
                            <input type="checkbox" name="packages[]" value="{{ $package->id }}"
                                   id="packages_{{ $index }}"
                                    {{ (isset($plan) && $plan->packagePlans->pluck('package_id')->contains($package->id)) ? 'checked' : '' }}>
                            <label for="packages_{{ $index }}" class="ml-2">{{ $package->name }}</label>
                        </div>
                    @endforeach
                @else
                    <p>Nenhum pacote disponível.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12">
            <label>Benefícios:</label>
            <div id="benefits-container">
                @if (isset($plan) && count($plan->benefits) > 0)
                    @foreach ($plan->benefits as $index => $benefit)
                        <div class="benefit-item d-flex align-items-center mb-2">
                            <input type="text" name="benefits[]" class="form-control"
                                   value="{{ $benefit->description }}" placeholder="Benefício ${benefitCount}">
                            <button type="button" class="btn btn-danger btn-sm ml-2 remove-benefit">Remover</button>
                        </div>
                    @endforeach
                @else
                    <div class="input-group mb-2">
                        <input type="text" name="benefits[]" class="form-control" placeholder="Benefício 1">
                    </div>
                @endif
            </div>
            <button type="button" class="btn btn-sm btn-primary" id="add-benefit">Adicionar Benefício</button>
        </div>
    </div>
</div>

<script>
    function getFormData() {
        const formData = new FormData()

        formData.append('id', $("#id").val());
        formData.append('name', $("#name").val());
        formData.append('value', $("#value").val());
        formData.append('description', $("#description").val());
        formData.append('cycle', $("#cycle").val());
        formData.append('is_active', $("#is_active").is(':checked') ? 1 : 0);
        formData.append('is_best_seller', $("#is_best_seller").is(':checked') ? 1 : 0);
        formData.append('is_active_telemedicine', $("#is_active_telemedicine").is(':checked') ? 1 : 0);
        formData.append('free_for_days', $("#free_for_days").val());
        formData.append('priority', $("#priority").val());

        $('#benefits-container input[name="benefits[]"]').each(function (index, element) {
            if ($(element).val()) {
                formData.append(`benefits[${index}]`, $(element).val());
            }
        });

        // Captura os combos selecionados
        $('#packages-container input[name="packages[]"]:checked').each(function (index, element) {
            formData.append(`packages[${index}]`, $(element).val());
        });

        return formData;
    }

    $(function () {
        $('#add-benefit').click(function () {
            const benefitCount = $('#benefits-container .benefit-item').length + 2;
            const newBenefit = `
            <div class="benefit-item d-flex align-items-center mb-2">
                <input type="text" name="benefits[]" class="form-control" placeholder="Benefício ${benefitCount}">
                <button type="button" class="btn btn-danger btn-sm ml-2 remove-benefit">Remover</button>
            </div>`;
            $('#benefits-container').append(newBenefit);
        });

        $('#benefits-container').on('click', '.remove-benefit', function () {
            $(this).closest('.benefit-item').remove();
            updateBenefitPlaceholders();
        });

        function updateBenefitPlaceholders() {
            $('#benefits-container .benefit-item input').each(function (index) {
                $(this).attr('placeholder', 'Benefício ' + (index + 1));
            });
        }

        initSelects2();
        initMasks();
    });

    function initSelects2() {
        $('#cycle').select2({
            theme: "bootstrap4",
            placeholder: "Ciclo",
            allowClear: true,
        });

        $('#billing_type').select2({
            theme: "bootstrap4",
            placeholder: "Tipo de Pagamento",
            allowClear: true,
        });
    }

    function initMasks() {
        $("#value").mask('000.000.000.000.000,00', {
            reverse: true
        });
    }

    $('#packages-container input[type="checkbox"]').on('change', function () {
        const exclusives = ['Dahplay - Pacote Superior', 'Dahplay - Completo', 'Dahplay - Premium'];

        const label = $(this).next('label').text().trim();
        const isChecked = $(this).is(':checked');

        if (exclusives.includes(label) && isChecked) {
            $('#packages-container input[type="checkbox"]').each(function () {
                const otherLabel = $(this).next('label').text().trim();
                if (exclusives.includes(otherLabel) && otherLabel !== label) {
                    $(this).prop('checked', false);
                }
            });
        }
    });

    $('#packages-container input[type="checkbox"]').on('change', function () {
        const exclusives = ['Dahplay - Premiere (A)', 'Dahplay - Premiere (F)'];

        const label = $(this).next('label').text().trim();
        const isChecked = $(this).is(':checked');

        if (exclusives.includes(label) && isChecked) {
            $('#packages-container input[type="checkbox"]').each(function () {
                const otherLabel = $(this).next('label').text().trim();
                if (exclusives.includes(otherLabel) && otherLabel !== label) {
                    $(this).prop('checked', false);
                }
            });
        }
    });

    $('#packages-container input[type="checkbox"]').on('change', function () {
        const exclusives = ['Dahplay - Telecine (A)', 'Dahplay - Telecine (F)'];

        const label = $(this).next('label').text().trim();
        const isChecked = $(this).is(':checked');

        if (exclusives.includes(label) && isChecked) {
            $('#packages-container input[type="checkbox"]').each(function () {
                const otherLabel = $(this).next('label').text().trim();
                if (exclusives.includes(otherLabel) && otherLabel !== label) {
                    $(this).prop('checked', false);
                }
            });
        }
    });

</script>
