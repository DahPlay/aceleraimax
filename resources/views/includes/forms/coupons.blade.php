<div class="modal-body">
    <div class="row">
        <div class="form-group col-12 col-md-6">
            <label for="name" class="col-form-label text-danger">Nome: *</label>
            <div class="input-group">
                <input type="text" id="name" class="form-control" name="name" placeholder="Nome *"
                       value="{{ $coupon->name ?? old('name') }}" required>
            </div>
        </div>

        <div class="form-group col-12 col-md-3 p-0">
            <div class="custom-control custom-switch custom-switch-on-primary d-flex align-items-center"
                 style="width: 250px;">
                <input type="checkbox" class="custom-control-input overdue" name="is_active" id="is_active"
                        {{ $coupon->is_active ? 'checked' : '' }}>
                <label class="custom-control-label font-weight-normal ml-2" for="is_active">Ativo</label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12 col-md-6">
            <label for="percent" class="col-form-label text-danger">Porcentagem de desconto: *</label>
            <div class="input-group">
                <input type="number" id="percent" class="form-control" name="percent" placeholder="Porcentage *"
                       value="{{ $coupon->percent ?? old('percent') }}" min="0" max="100" required>
            </div>
        </div>

        <div class="form-group col-12 col-md-6">
            <label for="cod" class="col-form-label text-danger">Código: *</label>
            <div class="input-group">
                <input type="number" id="cod" class="form-control" name="cod"
                       placeholder="Código *" value="{{ $coupon->cod ?? 0 }}" min="0" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12">
            <label for="observation" class="col-form-label text-danger">Observações: *</label>
            <div class="input-group">
                <textarea name="observation" id="observation" class="form-control" required
                          placeholder="Descrição">{{ $coupon->observation ?? old('observation') }}</textarea>
            </div>
        </div>
    </div>

</div>

<script>
    function getFormData() {
        const formData = new FormData()

        formData.append('id', $("#id").val());
        formData.append('name', $("#name").val());
        formData.append('percent', $("#percent").val());
        formData.append('observation', $("#observation").val());
        formData.append('percent', $("#percent").val());
        formData.append('cod', $("#cod").val());
        formData.append('is_active', $("#is_active").is(':checked') ? 1 : 0);

        return formData;
    }
</script>
