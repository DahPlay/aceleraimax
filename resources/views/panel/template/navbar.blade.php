<nav class="main-header navbar navbar-expand navbar-dark navbar-dark position-relative">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    {{-- Botão centralizado com proteção de clique --}}
    <div class="position-absolute w-100 d-flex justify-content-center"
        style="left: 0; top: 50%; transform: translateY(-50%); pointer-events: none;">
        <a href="{{ config('custom.portal_link') }}" target="_blank"
            style="
                background-color: {{ config('custom.background_home_color') }};
                padding: 10px 30px;
                border-radius: 12px;
                text-align: center;
                text-decoration: none;
                color: #fff;
                display: flex;
                align-items: center;
                gap: 12px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
                transition: all 0.3s ease;
                font-size: 18px;
                font-weight: bold;
                pointer-events: auto;
                z-index: 0;
            "
            onmouseover="this.style.boxShadow='0 0 20px rgba(255, 255, 255, 0.4)'"
            onmouseout="this.style.boxShadow='0 0 10px rgba(0, 0, 0, 0.2)'">
            <span>ACESSE O PORTAL</span>
            <img src="{{ config('custom.logo_1') }}" alt="Logo" style="height: 30px; width: auto;">
        </a>

        @if (
            auth()->user()->access_id === 1 &&
            isset(auth()->user()->customer) &&
            !is_null(auth()->user()->customer->web_smart_link)
        )
            <a href="{{ auth()->user()->customer->web_smart_link }}" target="_blank"
                style="
                background-color: #fff;
                padding: 10px 30px;
                border-radius: 12px;
                text-align: center;
                text-decoration: none;
                color: #000;
                display: flex;
                align-items: center;
                gap: 12px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
                transition: all 0.3s ease;
                font-size: 18px;
                font-weight: bold;
                pointer-events: auto;
                z-index: 0;
                margin-left: 10px;
            "
                onmouseover="this.style.boxShadow='0 0 20px rgba(255, 255, 255, 0.4)'"
                onmouseout="this.style.boxShadow='0 0 10px rgba(0, 0, 0, 0.2)'">
                <span>ACESSE A ALLOYAL</span>
                <img src="{{ asset('Auth-Panel/dist/img/logo-alloyal.svg') }}" alt="Logo"
                    style="height: 30px; width: auto;">
            </a>
        @endif
    </div>

    <div class="ml-auto d-flex align-items-center">
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <span class="d-md-inline">Olá, {{ Auth::user()->name }}</span>
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <a href="javascript:;" data-url="users/edit" data-id="{{ Auth::user()->id }}"
                        class="btn-edit dropdown-item py-2">
                        Dados cadastrais
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="post" class="mb-0">
                        @csrf
                    </form>

                    <a href="{{ route('logout') }}" class="dropdown-item"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Sair
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>
