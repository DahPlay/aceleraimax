@section('javascriptLocal')
    <script src="{{ asset('Auth-Panel/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('Auth-Panel/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
    <script>
        $(function() {
            initDatatable();

            $(document).on('click', ".btn-add", function(e) {
                openModal(this, e, 'modal-lg');
            });

            $(document).on('click', ".btn-edit", function(e) {
                openModal(this, e, 'modal-lg');
            });

            $(document).on('click', ".btn-show", function(e) {
                openModal(this, e, 'modal-lg');
            });

            $(document).on('click', ".btn-delete", function(e) {
                openModal(this, e, 'modal-lg');
            });

            $(document).on('click', "#btn-remover", function(e) {
                executeAll(this, e, 'modal-lg');
            });

            $(document).on('click', ".btn-cancel", function(e) {
                openModal(this, e, 'modal-lg');
            });

            $(document).on('click', ".btn-change", function(e) {
                openModal(this, e, 'modal-lg');
            });

            $(document).on('click', ".btn-change-card", function(e) {
                openModalOut(this, e, "change-card")
            });
        });

        function removeImage() {
            event.preventDefault();
            var url = $(this).parent().find("img").data('url');
            var id = $(this).parent().find("img").data('id');
            var token = $(this).parent().find("img").data('token');

            var main = $(this);

            var confirm = window.confirm("Deseja remover a imagem ?");

            if (confirm) {
                $.ajax({
                    url: "{{ route('panel.users.removeImage') }}",
                    type: "post",
                    data: {
                        "id": id,
                        "_token": token
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data) {
                            $(main).parent('div').html(
                                "<img width='100' src='{{ asset('/storage/avatars/default.png') }}' alt=''>"
                            );
                        }
                    }
                });
            }
        }

        function initDatatable() {
            tableManage.setName('#table');
            tableManage.setPerPage(10);
            tableManage.setColumnDefs([{
                "targets": 0,
                "orderable": false
            }]);
            tableManage.setOrder([
                [2, 'desc']
            ]);
            tableManage.setColumns([{
                    data: 'responsive',
                    orderable: false,
                    searchable: false,
                    className: 'align-middle',
                },
                {
                    data: 'checkbox',
                    orderable: false,
                    searchable: false,
                    className: 'align-middle'
                },
                {
                    data: 'id',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'customer.name',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'plan.name',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'value',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'subscription_asaas_id',
                    orderable: true,
                    searchable: true,
                    className: 'name align-middle'
                },
                {
                    data: 'payment_asaas_id',
                    orderable: true,
                    searchable: true,
                    className: 'name align-middle'
                },
                {
                    data: 'coupon_name',
                    orderable: true,
                    searchable: true,
                    className: 'name align-middle'
                },
                {
                    data: 'customer_asaas_id',
                    orderable: true,
                    searchable: true,
                    className: 'name align-middle'
                },
                {
                    data: 'status',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'payment_status',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'next_due_date',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'cycle',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'created_at',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                }
            ]);
            tableManage.setButton();
            tableManage.setRoute('{{ route("panel.$routeCrud.loadDatatable") }}');
            tableManage.setLengthMenu(
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "Todos"]
            );
            // 'Bfrtip'
            tableManage.setPluginButtonsDom(
                '<"wrapper"<"datatable-header"Blfr><"datatable-scroll"t><"datatable-footer"ip>>');
            tableManage.setPluginButtons(
                [{
                        extend: 'excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'colvis',
                        text: 'Colunas',
                    }
                ],
            );
            tableManage.render();
            tableManage.filter(true, '#table', ['', 'Ações']);
        }

        function openModalOut(button, e, modal) {
            e.preventDefault();

            var url = $(button).data('url');
            var id = $(button).data('id');
            var patient_id = $(button).data('patient-id');
            var modal = "#modal-" + modal;

            // Pega o routeCrud atual da url
            var routeCrud = url.split("/")[1];
            // Monta a rota de cadastro
            routeCrud = '/' + routeCrud + '/cadastro';

            var exceptionsList = [
                '/transaction/cadastroReceita',
            ];

            // Compara a rota atual com a rota de cadastro
            if (
                url != routeCrud &&
                !inArray(url, exceptionsList)
            ) {
                if (patient_id != undefined) {
                    url = url + '/' + id + '/' + patient_id;
                } else {
                    url = url + '/' + id;
                }
            } else {
                if (
                    !inArray(url, exceptionsList)
                ) {
                    url = routeCrud;
                }
            }

            $.ajax({
                    url: url,
                    method: 'GET',
                })
                .done(function(response) {
                    if (response.status && response.status == 403) {
                        Object.keys(response.errors).forEach((item) => {
                            $("#" + item).addClass('is-invalid');
                            toastMessage('fa fa-exclamation', 'bg-warning', 'Ops, permissão de acesso...',
                                response
                                .errors[item]);
                        });
                    } else {
                        $(modal).modal({
                            keyboard: true,
                            show: true
                        });

                        var z_index = modalOpenToModal();

                        $(modal).css('z-index', ++z_index);

                        $(modal + ' .modal-dialog').html(response);
                    }
                })
        }
    </script>
@endsection
