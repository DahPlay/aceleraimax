<div class="link-item-buttons d-inline-block">
    <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="true">
        <i class="fa fa fa-ellipsis-v text-dark"></i>
    </a>

    <div class="dropdown-menu dropdown-menu-lg">
        @can('user')
            <a href='javascript:;' class='btn-change btn btn-danger dropdown-item' data-id='{{ $order->id }}'
               data-url='/{{ $routeCrud }}/changePlan'>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                     class="bi bi-arrow-left-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                          d="M1 11.5a.5.5 0 0 0 .5.5h11.793l-3.147 3.146a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 11H1.5a.5.5 0 0 0-.5.5m14-7a.5.5 0 0 1-.5.5H2.707l3.147 3.146a.5.5 0 1 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 1 1 .708.708L2.707 4H14.5a.5.5 0 0 1 .5.5"/>
                </svg>
                <span class="ml-2">Trocar de plano</span>
            </a>
        @endcan

        <a href='javascript:;' class='btn-show btn btn-info dropdown-item' data-id='{{ $order->id }}'
           data-url='/{{ $routeCrud }}/showCards'>
            <i class='fa fa-money-check'></i>
            <span class="ml-2">Trocar de cartão</span>
        </a>

        <a href='javascript:;' class='btn-show btn btn-info dropdown-item' data-id='{{ $order->id }}'
           data-url='/{{ $routeCrud }}/show'>
            <i class='fa fa-eye'></i>
            <span class="ml-2">Ver</span>
        </a>

        {{-- @can('admin')
            <a href='javascript:;' class='btn-delete btn btn-danger dropdown-item' data-id='{{ $order->id }}'
                data-url='/{{ $routeCrud }}/delete'>
                <i class='fa fa-trash'></i>
                <span class="ml-2">Excluir</span>
            </a>
        @endcan --}}

        <a href='javascript:;' class='btn-cancel btn btn-danger dropdown-item' data-id='{{ $order->id }}'
           data-url='/{{ $routeCrud }}/cancel'>
            <i class='fa fa-lock'></i>
            <span class="ml-2">Cancelar</span>
        </a>


        <div class="dropdown-divider"></div>
    </div>
</div>
