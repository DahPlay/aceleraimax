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

        @if (!is_null($alloyalUser))
            @if (!is_null($user->customer->web_smart_link))
                <a href='{{ $user->customer->web_smart_link }}' target="_blank" class='btn btn-info dropdown-item'>
                    <i class='fa fa-bell'></i>
                    <span class="ml-2">Acessar Alloyal</span>
                </a>
            @else
                <a href='javascript:;' class='btn-add-smart-link btn btn-info dropdown-item'
                    data-id='{{ $user->id }}' data-url='/{{ $routeCrud }}/createSmartLink'>
                    <i class='fa fa-bell-slash'></i>
                    <span class="ml-2">Gerar Smart Link</span>
                </a>
            @endif

        @endif
    </div>
</div>
