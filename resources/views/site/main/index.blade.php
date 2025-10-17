<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{config('custom.project_name')}}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Exo:ital,wght@0,100..900;1,100..900&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/helvetica-neue-55" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('Auth-Panel/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('Auth-Panel/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('Auth-Panel/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="shortcut icon" href="{{ config('custom.favicon') }}" />
    <link rel="stylesheet" href="{{ asset('Auth-Panel/dist/css/front/front.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
</head>

<body>

    @if (config('custom.simple_home') == "NAO")

        <header class="d-flex justify-content-center justify-content-lg-end position-relative"
            style="background-image: url('{{ config('custom.background_home_image') }}'); background-repeat: no-repeat;">

            <!-- Escurece a imagem, mas fica por baixo de tudo -->

            <div style="
                                                background-color: {{ config('custom.background_home_image_blur') }};
                                                position: absolute;
                                                top: 0;
                                                left: 0;
                                                width: 100%;
                                                height: 100%;
                                                z-index: 0;
                                            "></div>

            <!-- Tudo que vem depois, com z-index: 1, fica acima -->
            <div style="z-index: 1;">
                <!-- texto, imagem, menu, etc. -->
            </div>

            <div class="align-items-center container-nav d-flex justify-content-between position-absolute">
                <img src="{{ config('custom.logo_1') }}" style="width: {{ config('custom.logo_home_width') }}px;">

                <div class="d-flex flex-column d-lg-none menu" onclick="toggleMenu()">
                    <div class="menu-bar" style="background-color: {{ config('custom.icone_menu_mobile') }}"></div>
                    <div class="menu-bar" style="background-color: {{ config('custom.icone_menu_mobile') }}"></div>
                    <div class="menu-bar" style="background-color: {{ config('custom.icone_menu_mobile') }}"></div>
                </div>

                <nav
                    class="justify-content-end justify-content-lg-center ml-0 mr-0 navbar navbar-expand col nav d-none d-lg-flex">

                    <ul class="d-lg-flex d-none justify-content-lg-center navbar-nav">
                        <li class="px-4" style="background-color: {{ config('custom.background_home_menu_color') }};"><a
                                href="#planos"
                                style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.text_menu_1') }}</a>
                        </li>

                        <li class="px-4" style="background-color: {{ config('custom.background_home_menu_color') }};"><a
                                href="#sobre-a-plataforma"
                                style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.text_menu_2') }}</a>
                        </li>
                        <li class="px-4" style="background-color: {{ config('custom.background_home_menu_color') }};"><a
                                href="#catalogo"
                                style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.text_menu_3') }}</a>
                        </li>
                        <li class="px-4" style="background-color: {{ config('custom.background_home_menu_color') }};"><a
                                href="{{ route('login') }}"
                                style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.text_menu_4') }}</a>
                        </li>
                        <li class="active px-4"
                            style="background-color: {{ config('custom.background_button_home_menu_color_cadastre') }};"><a
                                href="#planos">{{ config('custom.text_menu_5') }}</a></li>
                        <li class="active px-4 d-flex align-items-center"
                            style="background-color: {{ config('custom.background_home_menu_color') }}; border-radius: 10px;">
                            <a href="{{ config('custom.portal_link') }}" target="_blank" class="d-flex align-items-center"
                                style="color: {{ config('custom.text_home_menu_color') }}; font-weight: bold;">
                                Acessar <img src="{{ config('custom.logo_1') }}" style="width: {{ config('custom.text_menu_6_logo_home_width') }}px; margin-right: 8px;"
                                    alt="Logo">

                            </a>
                        </li>
                    </ul>
                </nav>
            </div>


            <nav class="mobile-menu" id="mobileMenu"
                style="background-color: {{ config('custom.background_home_menu_color') }};">
                <ul>
                    <li><a href="#planos" onclick="toggleMenu()"
                            style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.text_menu_1') }}</a>
                    </li>
                    <li><a href="#sobre-a-plataforma" onclick="toggleMenu()"
                            style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.text_menu_2') }}</a>
                    </li>
                    <li><a href="#catalogo" onclick="toggleMenu()"
                            style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.text_menu_3') }}</a>
                    </li>
                    <li><a href="{{ route('login') }}"
                            style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.text_menu_4') }}</a>
                    </li>
                    <li><a href="#planos"
                            style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.text_menu_5') }}</a>
                    </li>
                    <li class="active px-4" style="background-color: {{ config('custom.background_home_menu_color') }};">
                        <a href="{{ config('custom.portal_link') }}" target="_blank"
                            style="color: {{ config('custom.text_button_home_menu_color_cadastre') }};">
                            <img src="{{ config('custom.logo_1') }}" style="width: 140px; margin: 0px;" alt="">
                        </a>
                    </li>
                </ul>
            </nav>


            <div class="div-header d-flex flex-column justify-content-center px-5 px-lg-0"
                style="position: relative; z-index: 1;">
                <span
                    style="color: {{ config('custom.title_home_color_capa') }};">{{ config('custom.titulo_home_capa') }}</span>

                <p style="color: {{ config('custom.text_home_color_capa') }};">{{ config('custom.text_home_capa') }}</p>

                <a href="#planos"
                    style="background-color: {{ config('custom.background_button_home_menu_color_cadastre') }}; color: {{ config('custom.text_button_home_menu_color_cadastre') }};">{{ config('custom.text_button_home_menu_experimente') }}</a>
            </div>

            </div>
        </header>



        <section id="sobre-a-plataforma" class="d-flex first-section flex-column flex-lg-row section-container text-center">
            <div>
                <h3 style="color: {{ config('custom.text_home') }};">{{ config('custom.titulo_video') }}</h3>
                <p style="color: {{ config('custom.text_home') }};">{{ config('custom.text_video') }}</p>
            </div>

            <div class="first-section-video">
                <iframe height="315" src="{{ config('custom.link_video') }}" title="YouTube video player" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
        </section>

        <section class="align-items-center d-flex flex-column-reverse flex-lg-row second-section section-container">
            <div class="position-relative d-inline-block">
                <img class="vetor-info" src="{{ config('custom.card_home_image') }}" alt="">

                <div class="about-container position-absolute d-flex flex-column">
                    <div class="about">
                        <img src="{{ asset('Auth-Panel/dist/img/about-icon.svg') }}" alt="">

                        <div class="about-item">
                            <span class="about-title">{{ config('custom.title_card_1') }}</span>
                            <span class="about-subtitle">{{ config('custom.text_card_1') }}</span>
                        </div>
                    </div>
                    <div class="about">
                        <img src="{{ asset('Auth-Panel/dist/img/about-icon.svg') }}" alt="">

                        <div class="about-item">
                            <span class="about-title">{{ config('custom.title_card_2') }}</span>
                            <span class="about-subtitle">{{ config('custom.text_card_2') }}</span>
                        </div>
                    </div>

                    <div class="about">
                        <img src="{{ asset('Auth-Panel/dist/img/about-icon.svg') }}" alt="">

                        <div class="about-item">
                            <span class="about-title">{{ config('custom.title_card_3') }}</span>
                            <span class="about-subtitle">{{ config('custom.text_card_3') }}</span>
                        </div>
                    </div>
                </div>

                <img class="arrow position-absolute" src="{{ config('custom.seta_home_image') }}" alt="">
            </div>
            <div class="">
                <h2 class="font-weight-bold" style="color: {{ config('custom.text_home') }};">
                    {{ config('custom.title_session_card') }}
                </h2>

                <p style="color: {{ config('custom.text_home') }};">{{ config('custom.text_session_card') }}</p>
            </div>
        </section>

        <section id="catalogo" class="third-section section-container pr-0">
            <h3 class="font-weight-bold" style="color: {{ config('custom.text_home') }};">
                {{ config('custom.title_channels') }}
            </h3>

            <div class="swiper mySwiper">
                <div class="swiper-wrapper channels-agro">
                    <div class="channel-item swiper-slide d-flex justify-content-center align-items-center">
                        <span class="channel-number" style="color: {{ config('custom.number_home') }};">1</span>
                        <img class="channel-photo" style="border-color: {{ config('custom.border_channel') }};"
                            src="{{ config('custom.image_channel_1') }}" alt="">
                    </div>

                    <div class="channel-item swiper-slide d-flex justify-content-center align-items-center">
                        <span class="channel-number" style="color: {{ config('custom.number_home') }};">2</span>
                        <img class="channel-photo" style="border-color: {{ config('custom.border_channel') }};"
                            src="{{ config('custom.image_channel_2') }}" alt="">
                    </div>


                    <div class="channel-item swiper-slide d-flex justify-content-center align-items-center">
                        <span class="channel-number" style="color: {{ config('custom.number_home') }};">3</span>
                        <img class="channel-photo" style="border-color: {{ config('custom.border_channel') }};"
                            src="{{ config('custom.image_channel_3') }}" alt="">
                    </div>

                    <div class="channel-item swiper-slide d-flex justify-content-center align-items-center">
                        <span class="channel-number" style="color: {{ config('custom.number_home') }};">4</span>
                        <img class="channel-photo" style="border-color: {{ config('custom.border_channel') }};"
                            src="{{ config('custom.image_channel_4') }}" alt="">
                    </div>

                    <div class="channel-item swiper-slide d-flex justify-content-center align-items-center">
                        <span class="channel-number" style="color: {{ config('custom.number_home') }};">5</span>
                        <img class="channel-photo" style="border-color: {{ config('custom.border_channel') }};"
                            src="{{ config('custom.image_channel_5') }}" alt="">
                    </div>
                </div>

                <div class="swiper-button-next" style="color: {{ config('custom.text-home') }} !important;"></div>
                <div class="swiper-button-prev" style="color: {{ config('custom.text-home') }} !important;"></div>

            </div>
        </section>

        <section class="fourth-section section-container pr-0">
            <h3 class="font-weight-bold" style="color: {{ config('custom.text_home') }};">
                {{ config('custom.title_movies') }}
            </h3>

            <div class="swiper moviesSwiper">
                <div class="swiper-wrapper movies-area">
                    <div class="swiper-slide d-flex justify-content-center align-items-center">
                        <img class="channel-photo" style="height: {{config('custom.height_channel')}};"
                            src="{{ config('custom.image_movie_1') }}" alt="">
                    </div>

                    <div class="swiper-slide d-flex justify-content-center align-items-center">
                        <img class="channel-photo" style="height: {{config('custom.height_channel')}};"
                            src="{{ config('custom.image_movie_2') }}" alt="">
                    </div>

                    <div class="swiper-slide d-flex justify-content-center align-items-center">
                        <img class="channel-photo" style="height: {{config('custom.height_channel')}};"
                            src="{{ config('custom.image_movie_3') }}" alt="">
                    </div>

                    <div class="swiper-slide d-flex justify-content-center align-items-center">
                        <img class="channel-photo" style="height: {{config('custom.height_channel')}};"
                            src="{{ config('custom.image_movie_4') }}" alt="">
                    </div>

                    <div class="swiper-slide d-flex justify-content-center align-items-center">
                        <img class="channel-photo" style="height: {{config('custom.height_channel')}};"
                            src="{{ config('custom.image_movie_5') }}" alt="">
                    </div>
                </div>

                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </section>

        <section class="fifth-section section-container position-relative"
            style="background-color: {{config('custom.background_people')}};">
            <div class="container-woman col-6">
                <img src="{{ config('custom.image_people') }}" alt="">
            </div>

            <div class="container-infos col-6">
                <h3 style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.title_people') }}</h3>
                <p style="color: {{ config('custom.text_home_menu_color') }};">{{ config('custom.text_people') }}</p>
            </div>
        </section> <!-- OCULTAR -->
    @else
        <div class="banner-container">
            <img src="{{ config('custom.home_simple_image') }}" alt="Imagem de fundo"
                class="banner-background">

            <div class="banner-overlay 
            {{ config('custom.banner_overlay') === 'CLARO' ? 'banner-overlay-white' : 'banner-overlay-black' }}">
            </div>

            <!-- Botões no canto superior direito -->
            <div class="banner-button-top-right">
                <a href="{{ route('login') }}" target="_blank" rel="noopener noreferrer" class="banner-login-button">
                    {{ config('custom.text_menu_4') }}
                </a>

                <a href="{{ config('custom.portal_link') }}" target="_blank" rel="noopener noreferrer"
                    class="banner-access-link d-flex align-items-center">
                    <span style="margin-right: 8px;">Acessar</span>
                    <img src="{{ config('custom.logo_1') }}" style="width: {{ config('custom.text_menu_6_logo_home_width') }}px;" alt="Logo">
                </a>
            </div>

            <div class="banner-logo-center">
                <img src="{{ config('custom.logo_1') }}" alt="Logo">
            </div>
        </div>

        <style>
            html,
            body {
                margin: 0;
                padding: 0;
            }

            .banner-container {
                position: relative;
                width: 100vw;
                height: 200px;
                overflow: hidden;
                border-radius: 0 0 20px 20px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                display: flex;
                align-items: center;
                justify-content: center;
                background: transparent;
            }

            .banner-background {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 0 0 20px 20px;
                z-index: 0;
            }

            .banner-overlay {
                position: absolute;
                inset: 0;
                border-radius: 0 0 20px 20px;
                pointer-events: none;
                z-index: 1;
            }

            .banner-overlay-white {
                background: linear-gradient(to right,
                        rgba(255, 255, 255, 0) 0%,
                        rgba(255, 255, 255, 0.4) 40%,
                        rgba(255, 255, 255, 0.7) 50%,
                        rgba(255, 255, 255, 0.4) 60%,
                        rgba(255, 255, 255, 0) 100%);
            }

            .banner-overlay-black {
                background: linear-gradient(to right,
                        rgba(0, 0, 0, 0) 0%,
                        rgba(0, 0, 0, 0.4) 40%,
                        rgba(0, 0, 0, 0.6) 50%,
                        rgba(0, 0, 0, 0.4) 60%,
                        rgba(0, 0, 0, 0) 100%);
            }

            .banner-logo-center {
                position: relative;
                z-index: 2;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .banner-logo-center img {
                width: 180px;
                height: 180px;
                object-fit: contain;
                border-radius: 24px;
                display: block;
                background: transparent;
                padding: 0;
                z-index: 3;
            }

            .banner-button-top-right {
                position: absolute;
                top: 20px;
                right: 25px;
                z-index: 3;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .banner-login-button,
            .banner-access-link {
                padding: 10px 20px;
                background-color:
                    {{ config('custom.background_home_menu_color') }}
                ;
                color:
                    {{ config('custom.text_home_menu_color') }}
                ;
                border: none;
                border-radius: 8px;
                text-decoration: none;
                font-weight: bold;
                font-size: 16px;
                display: flex;
                align-items: center;
            }

            .banner-access-link img {
                margin-left: 5px;
            }

            @media (max-width: 768px) {
                .banner-container {
                    height: 140px;
                }

                .banner-logo-center img {
                    width: 120px;
                    height: 120px;
                }

                .banner-button-top-right {
                    top: 15px;
                    right: 15px;
                    flex-direction: column;
                    align-items: flex-end;
                    gap: 8px;
                }

                .banner-login-button,
                .banner-access-link {
                    padding: 8px 14px;
                    font-size: 14px;
                }
            }
        </style>



    @endif
    </br></br></br>
    @include('site.partials.plan-section')

    <footer class="section-container d-flex flex-column align-items-center"
        style="background-color: {{ config('custom.background_baseboard') }};">
        <p>{{ config('custom.text_baseboard') }}</p>

        <div
            class="d-flex align-items-center justify-content-center w-100 position-relative container-media flex-column flex-sm-row">
            <div class="social-media d-flex justify-content-center">
                <div class="container-social-media"
                    style="background-color: {{ config('custom.background_social_media') }};">
                    <a href="{{ config('custom.link_social_media_1') }}"><img
                            src="{{ config('custom.image_social_media_1') }}" alt=""></a>
                </div>
                <div class="container-social-media"
                    style="background-color: {{ config('custom.background_social_media') }};">
                    <a href="{{ config('custom.link_social_media_2') }}"><img
                            src="{{ config('custom.image_social_media_2') }}" alt=""></a>
                </div>
                <div class="container-social-media"
                    style="background-color: {{ config('custom.background_social_media') }};">
                    <a href="{{ config('custom.link_social_media_3') }}"><img
                            src="{{ config('custom.image_social_media_3') }}" alt=""></a>
                </div>
            </div>
            <img class="logo-footer" src="{{ config('custom.logo_baseboard') }}" alt="">
        </div>
        <p class="copyright-footer">{{ config('custom.text_copy') }}</p>
    </footer>

    <script src="{{ asset('Auth-Panel/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('Auth-Panel/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('Auth-Panel/dist/js/adminlte.min.js') }}"></script>
    <script src="{{ asset('Auth-Panel/dist/js/demo.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <!-- Bootstrap CSS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('Auth-Panel/dist/js/front/front.js') }}"></script>
    <script>
        $(function () {
            const swiper = new Swiper('.mySwiper', {
                slidesPerView: 4,
                slidesPerGroup: 1,
                loop: false,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                spaceBetween: 10,
                breakpoints: {
                    992: {
                        slidesPerView: 4,
                    },
                    768: {
                        slidesPerView: 2,
                    },
                    576: {
                        slidesPerView: 1,
                    },
                    300: {
                        slidesPerView: 1,
                    }
                }
            });

            const moviesSwiper = new Swiper('.moviesSwiper', {
                slidesPerView: 4,
                slidesPerGroup: 1,
                loop: false,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                spaceBetween: 10,
                breakpoints: {
                    992: {
                        slidesPerView: 4,
                    },
                    768: {
                        slidesPerView: 2,
                    },
                    576: {
                        slidesPerView: 1,
                    },
                    300: {
                        slidesPerView: 1,
                    }
                }
            });


            $('a[href^="#"]').on('click', function (event) {
                event.preventDefault();

                const target = $(this.getAttribute('href'));

                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top
                    }, 1000);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            @foreach($cycles as $cycleKey => $cycleName)
                new Swiper('.mySwiper-{{ $cycleKey }}', {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    loop: true,
                    pagination: {
                        el: '.swiper-pagination-{{ $cycleKey }}',
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.swiper-next-{{ $cycleKey }}',
                        prevEl: '.swiper-prev-{{ $cycleKey }}',
                    },
                    breakpoints: {
                        576: {
                            slidesPerView: 1,
                        },
                        768: {
                            slidesPerView: 2,
                        },
                        992: {
                            slidesPerView: 3,
                        }
                    }
                });
            @endforeach
    });

        $(document).ready(function () {
            // Espera um pequeno tempo para garantir que todos os elementos estejam no DOM (ajuste se necessário)
            setTimeout(function () {
                let maxHeight = 0;

                // Seleciona todos os cards
                $('.swiper-slide').each(function () {
                    let cardHeight = $(this).height();
                    if (cardHeight > maxHeight) {
                        maxHeight = cardHeight;
                    }
                });

                // Aplica a maior altura a todos os cards
                $('.swiper-slide').height(maxHeight);
            }, 300); // você pode aumentar esse delay se os elementos demorarem mais a aparecer
        });
    </script>

    <!-- inicio Flut -->
    <script src={{ config('custom.flut') }}></script>
    <!-- fim Flut -->
</body>

<style>
    /* For Swiper buttons if they use ::after (commonly the case) */
    .swiper-button-next::after,
    .swiper-button-prev::after {
        color: {{ config('custom.text_home') }}

        !important;
        font-size: 40px;
        /* ajuste opcional */
    }

    /* Para garantir visibilidade */
    .swiper-button-next,
    .swiper-button-prev {
        z-index: 10;
    }
</style>


</html>