<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title">Criar</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <form id="formCreate{{ ucfirst($routeCrud) }}">
        @csrf
        @method('POST')

        @include("includes.forms.$routeCrud")

        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary btn-submit">Criar</button>
        </div>
    </form>
</div>

<script>
    $("#formCreate{{ ucfirst($routeCrud) }}").on('submit', function(e) {
        e.preventDefault();

        $(".btn-submit").attr('disabled', true).text('Enviando...');

        const formData = getFormData();

        $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                type: 'POST',
                url: '{{ route('panel.coupons.store') }}',
                data: formData,
                processData: false,
                contentType: false,
            })
            .done(function(data) {
                if (data.status === 400) {
                    Object.keys(data.errors).forEach((item) => {
                        $("#" + item).addClass('is-invalid');
                        toastMessage('fa fa-exclamation', 'bg-danger', 'Ops, houve um erro!', data
                            .errors[item]);
                    });
                    $(".btn-submit").removeAttr('disabled').text('Criar');
                } else if (data.status === 200) {
                    $(".modal").modal('hide');
                    $('#table').DataTable().draw(true);
                    toastMessage('fa fa-check', 'bg-success', 'Sucesso!', data.message);
                } else {
                    toastMessage('fa fa-exclamation', 'bg-warning', 'Atenção!',
                        'Tente novamente ou entre em contato com o administrador do sistema!');
                }
            })
            .fail(function(xhr) {
                console.error('Erro AJAX:', xhr);
                toastMessage('fa fa-exclamation', 'bg-danger', 'Erro interno!',
                    'Ocorreu um erro ao enviar os dados. Tente novamente.');
                $(".btn-submit").removeAttr('disabled').text('Criar');
            });
    });
</script>
