<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('panel.main.index') }}" class="brand-link">
        <img src="{{ config('custom.favicon') }}" alt="{{ config('custom.project_name') }}"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{{ config('custom.project_name') }}</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ file_exists(storage_path('app/public/' . auth()->user()->photo))
    ? asset('storage/' . auth()->user()->photo)
    : asset(auth()->user()->photo) }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->name ?? 'Desconhecido' }}</a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu"
                data-accordion="false">

                @if (auth()->user()->access_id != 1)
                    @can('admin')
                        <li class="nav-item has-treeview {{ request()->is('painel-de-controle') ? 'menu-open' : '' }}">
                            <a href="{{ route('panel.main.index') }}"
                                class="nav-link {{ request()->is('painel-de-controle') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                    @endcan
                @endif

                @if (auth()->user()->access_id == 1)
                    @can('user')
                        <li class="nav-item has-treeview {{ request()->is('painel-de-controle-user') ? 'menu-open' : '' }}">
                            <a href="{{ route('panel.main.index-user') }}"
                                class="nav-link {{ request()->is('painel-de-controle-user') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                    @endcan
                @endif

                @can('admin')
                    <li class="nav-item has-treeview {{ request()->is('users') ? 'menu-open' : '' }}">
                        <a href="{{ route('panel.users.index') }}"
                            class="nav-link {{ request()->is('users') ? 'active' : '' }}">
                            <i class="nav-icon fa fa-user"></i>
                            @if (auth()->user()->access && auth()->user()->access->name === 'User')
                                <p>Usuário</p>
                            @else
                                <p>Clientes</p>
                            @endif
                        </a>
                    </li>
                @endcan

                @can('developer')
                    <li class="nav-item has-treeview" {{ request()->is('accesses') ? 'menu-open' : '' }}>
                        <a href="{{ route('panel.accesses.index') }}"
                            class="nav-link {{ request()->is('accesses') ? 'active' : '' }}">
                            <i class="nav-icon fa fa-key"></i>
                            <p>
                                Perfis
                            </p>
                        </a>
                    </li>
                @endcan

                @can('user')
                    <li class="nav-item has-treeview {{ request()->is('customers') ? 'menu-open' : '' }}">
                        <a href="{{ route('panel.customers.index') }}"
                            class="nav-link {{ request()->is('customers') ? 'active' : '' }}">
                            <i class="nav-icon fa fa-users"></i>

                            @if (auth()->user()->access && auth()->user()->access->name === 'User')
                                <p>Minha Conta</p>
                            @else
                                <p>Cobranças</p>
                            @endif

                        </a>
                    </li>
                @endcan

                @can('admin')
                    <li class="nav-item has-treeview {{ request()->is('plans') ? 'menu-open' : '' }}">
                        <a href="{{ route('panel.plans.index') }}"
                            class="nav-link {{ request()->is('plans') ? 'active' : '' }}">
                            <i class="nav-icon far fa-list-alt"></i>
                            <p>
                                Planos
                            </p>
                        </a>
                    </li>
                @endcan

                @can('admin')
                    <li class="nav-item has-treeview {{ request()->is('coupons') ? 'menu-open' : '' }}">
                        <a href="{{ route('panel.coupons.index') }}"
                            class="nav-link {{ request()->is('coupons') ? 'active' : '' }}">
                            <i class="nav-icon fa fa-ticket-alt"></i>
                            <p>
                                Cupons
                            </p>
                        </a>
                    </li>
                @endcan

                @can('developer')
                    <li class="nav-item has-treeview {{ request()->is('packages') ? 'menu-open' : '' }}">
                        <a href="{{ route('panel.packages.index') }}"
                            class="nav-link {{ request()->is('packages') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-archive"></i>
                            <p>
                                Pacotes
                            </p>
                        </a>
                    </li>
                @endcan

                <li class="nav-item has-treeview {{ request()->is('orders') ? 'menu-open' : '' }}">
                    <a href="{{ route('panel.orders.index') }}"
                        class="nav-link {{ request()->is('orders') ? 'active' : '' }}">
                        <i class="nav-icon fa fa-file-alt"></i>
                        <p>
                            Assinaturas
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ config('custom.portal_link') }}" target="_blank" class="nav-link text-white" style="
                        background-color: {{ config('custom.background_home_color') }};
                        border-radius: 10px;
                        margin: 10px;
                        display: flex;
                        align-items: center;
                        transition: all 0.3s ease;
                        padding: 10px;" onmouseover="this.style.boxShadow='0 0 20px rgba(255, 255, 255, 0.4)'"
                        onmouseout="this.style.boxShadow='0 0 10px rgba(0, 0, 0, 0.2)'">
                        <img src="{{ config('custom.logo_2') }}" alt="Portal"
                            style="width: 45px; height: 45px; object-fit: contain; margin-right: 15px;">
                        <p style="margin: 0; font-weight: bold; font-size: 16px;">Acessar
                            {{ config('custom.project_name') }}
                        </p>
                    </a>
                </li>
                @if (auth()->user()->access_id == 1)
                    <li class="nav-item">
                        <a href="{{ auth()->user()->customer->web_smart_link }}" target="_blank" class="nav-link text-white"
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
                            " onmouseover="this.style.boxShadow='0 0 20px rgba(255, 255, 255, 0.4)'"
                            onmouseout="this.style.boxShadow='0 0 10px rgba(0, 0, 0, 0.2)'">
                            <img src="{{ asset('Auth-Panel/dist/img/logo-alloyal.svg') }}" alt="Alloyal"
                                style="width: 45px; height: 45px; object-fit: contain; margin-right: 15px;">
                            <p style="margin: 0; font-weight: bold; font-size: 16px;" class="text-body">Acessar a Alloyal
                            </p>
                        </a>
                    </li>
                @endif

                @php
                    $baseUrl = config('app.url');
                    if (app()->environment('local')) {
                        $baseUrl .= ':8000';
                    }
                @endphp

                <li class="nav-item">
                    <a href="{{ $baseUrl }}" target="_blank" class="nav-link text-white"
                        style="background-color: {{ config('custom.background_home_color') }}; border-radius: 10px; margin: 10px; display: flex; align-items: center; padding: 10px;">
                        <i class="nav-icon fas fa-home" style="margin-right: 10px;"></i>
                        <p style="margin: 0; font-weight: bold; font-size: 16px;">Voltar para Home</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>