@extends("panel.template.index")

@section('content')
    <div class="content-wrapper d-flex justify-content-center align-items-center">
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="error-page mt-0">
                            <h2 class="headline text-danger mb-0">403</h2>

                            <div class="error-content">
                                <h3><i class="fas fa-exclamation-triangle text-danger"></i> Oops! Acesso Negado.</h3>

                                <p>Você não tem permissão para acessar esta página.</p>

                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
