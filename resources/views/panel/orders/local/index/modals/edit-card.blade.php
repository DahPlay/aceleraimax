<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title">Gerenciar Cartões</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>

    <form id="formUpdateCard">
        <input type="hidden" name="id_order" id="id_order" value="{{ $id_order }}">

        <div class="modal-body">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group col-12">
                            <label for="card_number" class="col-form-label">Número do cartão *</label>
                            <div class="input-group">
                                <input type="number" name="credit_card_number" id="card_number" class="form-control"
                                    placeholder="Informe o número do cartão" min="13" maxlength="19" required
                                    value="">
                            </div>
                        </div>

                        <div class="form-group col-12">
                            <label for="card_name">Nome do titular do cartão *</label>
                            <div class="input-group mb-3">
                                <input type="text" name="credit_card_name" id="card_name" class="form-control"
                                    placeholder="Nome do titular do cartão" required value="">
                            </div>
                        </div>

                        <div class="form-group col-12">
                            <label for="card_expiry_month">Mês *</label>
                            <div class="input-group mb-3">
                                <input type="text" name="credit_card_expiry_month" id="card_expiry_month"
                                    class="form-control form-group" placeholder="00" min="2" maxlength="2"
                                    required value="">
                            </div>
                        </div>

                        <div class="form-group col-12">
                            <label for="card_expiry_year">Ano *</label>
                            <div class="input-group mb-3">
                                <input type="text" name="credit_card_expiry_year" id="card_expiry_year"
                                    class="form-control form-group" placeholder="0000" minlength="4" maxlength="4"
                                    required value="">
                            </div>
                        </div>

                        <div class="form-group col-12">
                            <label for="card_ccv">CVV *</label>
                            <div class="input-group mb-3">
                                <input type="text" name="credit_card_ccv" id="card_ccv"
                                    class="form-control form-group" placeholder="000" minlength="3" maxlength="4"
                                    required value="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary btn-submit">Atualizar</button>
        </div>
    </form>

</div>

<script>
    $("#formUpdateCard").on('submit', function(e) {
        e.preventDefault();

        $(".btn-submit").attr('disabled', true).text('Enviando...');

        let id_order = $("#id_order").val();

        $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                type: 'PUT',
                url: 'orders/updateCard/' + id_order,
                data: $(this).serialize(),
            })
            .done(function(data) {

                if (data.status == 400) {
                    Object.keys(data.errors).forEach((item) => {
                        $("#" + item).addClass('is-invalid');
                        toastMessage('fa fa-exclamation', 'bg-danger', 'Ops, houve um erro!', data
                            .errors[item]);
                    });

                    $(".btn-submit").removeAttr('disabled', true).text('Atualizar');
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
