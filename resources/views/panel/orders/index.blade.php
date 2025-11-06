@extends("$routeAmbient.template.index")

@section('content')
    <div class="content-wrapper">
        @include("$routeAmbient.$routeCrud.breadcrumb")

        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="table" class="table table-hover" width="100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 1%"></th>
                                            <th><input type="checkbox" id="btn-marcar-todos"></th>
                                            <th>#</th>
                                            <th>Cliente</th>
                                            <th>Plano</th>
                                            <th>Valor</th>
                                            <th>Assinatura</th>
                                            <th>Fatura</th>
                                            <th>Cupom</th>
                                            <th>Cliente Asaas</th>
                                            <th>Assinatura Status</th>
                                            <th>Pagamento</th>
                                            <th>Vencimento</th>
                                            <th>Ciclo</th>
                                            <th>Criação</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-change-card fade" id="modal-change-card" name="modal" tabindex="-1" role="dialog"
        aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog modal-md">

        </div>
    </div>
@endsection

@includeIf("$routeAmbient.$routeCrud.local.index.head")
@includeIf("$routeAmbient.$routeCrud.local.index.javascript")
