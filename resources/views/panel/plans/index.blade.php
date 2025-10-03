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
                                            <th>Prioridade</th>
                                            <th>Nome</th>
                                            <th>Valor</th>
                                            <th>Ativo</th>
                                            <th>Ciclo</th>
                                            <th>Dependentes</th>
                                            <th>Pagamento</th>
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
@endsection

@includeIf("$routeAmbient.$routeCrud.local.index.head")
@includeIf("$routeAmbient.$routeCrud.local.index.javascript")
