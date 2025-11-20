<div class="modal-content bg-gradient-light">
    <div class="modal-header">
        <h4 class="modal-title">{{ $user->name }} tem certeza que deseja acessar a Alloyal?</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="row d-flex align-items-center">
            <div class="col-12">
                <a href="{{ $user->customer->web_smart_link }}" target="_blank" class="nav-link text-white"
                    style="
                                background-color: #fff;
                                border-radius: 12px;
                                text-decoration: none;
                                color: #000;
                                box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
                                transition: all 0.3s ease;
                                border-radius: 10px;
                                display: flex;
                                align-items: center;
                                padding: 10px;
                                margin: 10px;
                            ">
                    <img src="{{ asset('Auth-Panel/dist/img/logo-alloyal.svg') }}" alt="Alloyal"
                        style="width: 45px; height: 45px; object-fit: contain; margin-right: 15px;">
                    <p style="margin: 0; font-weight: bold; font-size: 16px;" class="text-body">Acessar a
                        Alloyal
                    </p>
                </a>
            </div>
        </div>
    </div>

    <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
    </div>
</div>
