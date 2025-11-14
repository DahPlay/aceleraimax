<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title">Criar Smart Link na Alloyal</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <form id="formCreateSmartLink{{ ucfirst($routeCrud) }}">
        @csrf
        @method('PUT')

        <input type="hidden" id="id" name="id" value="{{ $user->id }}">

        <div class="modal-body">
            <div class="row d-flex align-items-center">
                <div class="col-12">
                    <p>Clique em confirmar para gerar o Smart Link na Alloyal</p>
                </div>
            </div>
        </div>

        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary btn-submit">Confirmar</button>
        </div>
    </form>
</div>

<script>
    $("#formCreateSmartLink{{ ucfirst($routeCrud) }}").on('submit', function(e) {
        e.preventDefault();

        $(".btn-submit").attr('disabled', true).text('Enviando...');

        var id = $("#id").val();

        $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                type: 'POST',
                url: '{{ $routeCrud }}/store-smart-link/' + id,
                data: [],
                processData: false,
                contentType: false,
            })
            .done(function(data) {

                if (data.status == 400) {
                    Object.keys(data.errors).forEach((item) => {
                        $("#" + item).addClass('is-invalid');
                        toastMessage('fa fa-exclamation', 'bg-danger', 'Ops, houve um erro!', data
                            .errors[item]);
                    });

                    $(".btn-submit").removeAttr('disabled', true).text('Confirmar');
                } else if (data.status == 200) {
                    $(".modal").modal('hide');

                    $('#table').DataTable().draw(true);

                    toastMessage('fa fa-check', 'bg-success', 'Sucesso!', data.message);
                } else {
                    toastMessage('fa fa-exclamation', 'bg-warning', 'Atenção!',
                        'Tente novamente ou entre em contato com o administrador do sistema !');
                }

            })
            .fail(function() {
                console.log('fail');
            })
    });
</script>
