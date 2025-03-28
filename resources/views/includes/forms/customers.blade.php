<div class="modal-body">
    <div class="row">
        <div class="form-group col-12 col-md-4">
            <label for="name" class="col-form-label text-danger">Nome: *</label>
            <div class="input-group">
                <input type="text" id="name" class="form-control" name="name" placeholder="Nome *"
                    value="{{ $customer->name ?? old('name') }}" required>
            </div>
        </div>

        <div class="form-group col-12 col-md-4">
            <label for="document" class="col-form-label text-danger">Documento: *</label>
            <div class="input-group">
                <input type="text" id="document" class="form-control" name="document" placeholder="Documento *"
                    value="{{ $customer->document ?? old('document') }}" required>
            </div>
        </div>

        <div class="form-group col-12 col-md-4">
            <label for="mobile" class="col-form-label text-danger">Celular: *</label>
            <div class="input-group">
                <input type="text" id="mobile" class="form-control" name="mobile" placeholder="Celular *"
                    value="{{ $customer->mobile ?? old('mobile') }}" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12 col-md-4">
            <label for="viewers_id" class="col-form-label">Cliente Agro Play:</label>
            <div class="input-group">
                <input type="text" id="viewers_id" class="form-control" name="viewers_id" placeholder="viewers_id"
                    value="{{ $customer->viewers_id ?? old('viewers_id') }}" disabled>
            </div>
        </div>

        <div class="form-group col-12 col-md-4">
            <label for="customer_id" class="col-form-label">Cliente Asaas:</label>
            <div class="input-group">
                <input type="text" id="customer_id" class="form-control" name="customer_id" placeholder="customer_id"
                    value="{{ $customer->customer_id ?? old('customer_id') }}" disabled>
            </div>
        </div>

        <div class="form-group col-12 col-md-4">
            <label for="user_id" class="col-form-label">Usuário:</label>
            <select id="user_id" class="form-control" name="user_id" disabled>
                <option value=""></option>
                @foreach ($users as $user)
                    <option value='{{ $user->id }}' {{ $customer->user_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-12 col-md-6">
            <label for="birthdate" class="col-form-label">Data de nascimento:</label>
            <div class="input-group">
                <input type="date" id="birthdate" class="form-control" name="birthdate"
                    value="{{ $customer->birthdate ?? old('birthdate') }}">
            </div>
        </div>

        <div class="form-group col-12 col-md-6">
            <label for="email" class="col-form-label text-danger">E-mail: *</label>
            <div class="input-group">
                <input type="email" id="email" class="form-control" name="email" placeholder="E-mail *"
                    value="{{ $customer->email ?? old('email') }}" required>
            </div>
        </div>
    </div>
</div>

<script>
    function getFormData() {
        const formData = new FormData()

        formData.append('id', $("#id").val());
        formData.append('name', $("#name").val());
        formData.append('document', $("#document").val());
        formData.append('mobile', $("#mobile").val());
        formData.append('birthdate', $("#birthdate").val());
        formData.append('email', $("#email").val());

        return formData;
    }

    $(function() {
        initSelects2();
        initMasks();
    });

    function initSelects2() {
        $('#user_id').select2({
            theme: "bootstrap4",
            placeholder: "Usuário",
            allowClear: true,
        });
    }

    function initMasks() {
        $('#document').mask('000.000.000-00');
        $('#mobile').mask('(00) 00000-0000');
    }
</script>
