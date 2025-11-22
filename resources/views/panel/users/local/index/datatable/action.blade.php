<div class="link-item-buttons d-inline-block">
    <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="true">
        <i class="fa fa fa-ellipsis-v text-dark"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-lg">
        <a href='javascript:;' class='btn-edit btn btn-info dropdown-item' data-id='{{ $user->id }}'
            data-url='/{{ $routeCrud }}/edit'>
            <i class='fa fa-edit'></i>
            <span class="ml-2">Editar</span>
        </a>

        @can('admin')
            <a href='javascript:;' class='btn-delete btn btn-danger dropdown-item' data-id='{{ $user->id }}'
                data-url='/{{ $routeCrud }}/delete'>
                <i class='fa fa-trash'></i>
                <span class="ml-2">Excluir</span>
            </a>
        @endcan

        <div class="dropdown-divider"></div>

        @if (filled($user->customer?->alloyal_id) && $user->customer?->isAlloyalActive())
            <a href='javascript:;' class='btn-add-smart-link btn btn-info dropdown-item' data-id='{{ $user->id }}'
                data-url='/{{ $routeCrud }}/createSmartLink'>
                <img src="{{ asset('Auth-Panel/dist/img/logo-alloyal.svg') }}" alt="Alloyal"
                    style="width: 45px; height: 45px; object-fit: contain; margin-right: 15px;">
                <span class="ml-2">Acessar Alloyal</span>
            </a>
        @endif
    </div>
</div>
