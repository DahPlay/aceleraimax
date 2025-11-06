<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title">Gerenciar Cartões</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>

    <div class="modal-body">
        <section id="planos" class="sixth-section d-flex flex-column align-items-center">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between mb-2">
                            <h3>Cartão atual:</h3>
                            <button type="button" class="btn btn-primary btn-change-card" data-url="/order/changeCard"
                                data-id="{{ $order->id }}">Alterar Cartão</button>
                        </div>

                        <table class="table table-light">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Últimos 4 digitos</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $order->customer->credit_card_brand }}</td>
                                    <td>**** **** **** {{ $order->customer->credit_card_number }}</td>
                                    <td><i class="fa fa-check text-success"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <h3>Histórico de cartões:</h3>
                        <table class="table table-light">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Últimos 4 digitos</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($order->customer->creditCards as $creditCard)
                                    <tr>
                                        <td>{{ $creditCard->credit_card_brand }}</td>
                                        <td>**** **** **** {{ $creditCard->credit_card_number }}</td>
                                        <td><i class="fa fa-sync-alt text-info"></i></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Nenhum registro encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
