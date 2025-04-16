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

            $(document).on('click', ".btn-delete", function(e) {
                openModal(this, e, 'modal-lg');
            });

            $(document).on('click', "#btn-remover", function(e) {
                executeAll(this, e, 'modal-lg');
            });
        });


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
                    data: 'name',
                    orderable: true,
                    searchable: true,
                    className: 'name align-middle'
                },
                {
                    data: 'percent',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'cod',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'is_active',
                    orderable: true,
                    searchable: true,
                    className: 'align-middle'
                },
                {
                    data: 'observation',
                    orderable: false,
                    searchable: false,
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
    </script>
@endsection
