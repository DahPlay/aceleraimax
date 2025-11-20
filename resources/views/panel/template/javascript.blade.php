<script src="{{ asset('Auth-Panel/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/dist/js/adminlte.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/dist/js/demo.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/dist/js/init-datatable.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/jquery.mask.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<script src="{{ asset('Auth-Panel/plugins/datatables-colreorder/js/dataTables.colReorder.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('Auth-Panel/plugins/select2/js/select2.full.min.js') }} "></script>

@yield('javascriptLocal')

<script>
    $(function() {
        $(".carregando").fadeOut();

        $(document).on('change', "#btn-marcar-todos", function() {
            $(".checkbox").prop('checked', $(this).prop(
                "checked"));
        });

        $(document).on('click', ".btn-add-smart-link", function(e) {
            openModal(this, e, 'modal-md');
        });
    });

    function openModal(button, e, modal_witdh) {
        e.preventDefault();

        var id = $(button).data('id');
        var url = $(button).data('url');
        var local = $(button).data('local');

        // Pega o routeCrud atual da url
        var routeCrud = url.split("/")[1];
        // Monta a rota de create
        routeCrud = '/' + routeCrud + '/create';

        // Exemplo de modal create ou sem necessidade de passagem de parâmetro
        var exceptionsList = [
            '/transaction/createReceita',
        ];

        // Compara a rota atual com a rota de create
        if (
            url != routeCrud &&
            !inArray(url, exceptionsList)
        ) {
            url = url + '/' + id;
        } else {
            if (
                !inArray(url, exceptionsList)
            ) {
                url = routeCrud;
            }
        }

        var data = {
            local: local
        }

        $.ajax({
            url: url,
            method: 'GET',
            data: data,
            success: function(response) {
                if (response.status && response.status == 403) {
                    Object.keys(response.errors).forEach((item) => {
                        $("#" + item).addClass('is-invalid');
                        toastMessage('fa fa-exclamation', 'bg-warning',
                            'Ops, permissão de acesso...', response
                            .errors[item]);
                    });
                } else {
                    $("#modal").modal({
                        keyboard: true,
                        show: true
                    });

                    var z_index = modalOpenToModal();

                    $(modal).css('z-index', ++z_index);

                    $('#modal .modal-dialog').attr('class', 'modal-dialog ' + modal_witdh);
                    $('#modal .modal-dialog').html(response);
                }
            },
            error: function(response) {
                toastMessage('bg-danger', 'Ops, houve um erro!', 'Usuário não encontrado!');
            }
        })
    }

    function modalOpenToModal() {
        var qtd_modals_open = $('.modal.show').length;

        var last_modal_open = $('.modal.show')[(qtd_modals_open - 1)];

        $(last_modal_open).attr('style', 'display: block');

        var z_index = $(last_modal_open).css('z-index');

        return z_index;
    }

    function inArray(value, list) {
        var length = list.length;
        for (var i = 0; i < length; i++) {
            if (list[i] == value) return true;
        }
        return false;
    }

    function toastMessage(icon, type, title, text) {
        $(document).Toasts('create', {
            class: type,
            title: title,
            position: 'topRight',
            body: text,
            autohide: true,
            delay: 5000,
            fade: true,
            icon: icon
        });
    }

    function executeAll(button, e, modal_witdh) {
        e.preventDefault();

        var url = $(button).data('url');
        var token = $(button).data('token');

        var checkeds = new Array();
        $("input[name='ids[]']:checked").each(function() {
            var id = $(this).val();
            var name = $(this).parent().parent().find('.name').text();
            var buy_in = $(this).parent().parent().find('.buy_in').text();

            var data = {
                id: id,
                name: name,
                buy_in: buy_in
            }
            checkeds.push(data);
        });

        if (checkeds.length > 0) {
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    checkeds: checkeds,
                    _token: token
                },
                success: function(response) {
                    modal_witdh
                    $("#modal .modal-dialog").attr('class', 'modal-dialog ' + modal_witdh);
                    $("#modal .modal-dialog").html(response);
                    $("#modal").modal("show");
                },
                error: function(response) {
                    toastMessage('bg-danger', 'Ops, houve um erro!', 'Usuário não encontrado!');
                }
            })
        } else {
            Swal.fire({
                title: 'Nenhum item selecionado!',
                html: '<span>Selecione pelo menos um item.<span>'
            });
        }
    }
</script>

@if (count($errors) > 0)
    <script>
        $(document).Toasts('create', {
            class: 'bg-danger',
            title: 'Atenção ao(s) seguinte(s) erro(s):',
            position: 'topRight',
            autohide: true,
            delay: 5000,
            icon: 'fa fa fa-exclamation',
            body: [
                @foreach ($errors->all() as $error)
                    "<li>{{ $error }}</li>",
                @endforeach
            ]
        })
    </script>
@endif

@if (session('danger'))
    <script>
        toastMessage('fa fa-exclamation', 'bg-danger', 'Ops, houve um erro!', '{{ session('danger') }}');
    </script>
@endif

@if (session('success'))
    <script>
        toastMessage('fa fa-check', 'bg-success', 'Sucesso!', '{{ session('success') }}');
    </script>
@endif

@if (session('warning'))
    <script>
        toastMessage('fa fa-exclamation', 'bg-warning', 'Atenção!', '{{ session('warning') }}');
    </script>
@endif
