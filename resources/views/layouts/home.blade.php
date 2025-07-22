<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.components.meta-head')
    @stack('style')

    {{--  This Custom Meta or CSS  --}}
</head>

<body>
    <div class="wrapper">
        @include('layouts.components.sidebar')

        <div class="main-panel">
            @include('layouts.components.navbar')

            <div class="container">
                <div class="page-inner">
                    @yield('content')
                </div>
            </div>

            @include('layouts.components.footer')
        </div>
    </div>

    @include('layouts.components.script')
    @stack('script')

    {{--  This Custom Script  --}}
    <script>
        $(document).ready(function() {
            $('.select-add').select2({
                placeholder: 'Silahkan Pilih...',
                allowClear: true,
                dropdownParent: $('#modalBackdrop'),
                width: '100%',
                theme: 'bootstrap-5',
            });
        });
        $(document).on('shown.bs.modal', '.modalEdit', function() {
            const modal = $(this);
            modal.find('.select-edit').select2({
                placeholder: 'Silahkan Pilih...',
                allowClear: true,
                dropdownParent: modal,
                width: '100%',
                theme: 'bootstrap-5'
            });
        });
        $(document).on('hidden.bs.modal', '.modalEdit', function() {
            const modal = $(this);
            modal.find('.select-edit').select2('destroy');
        });
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search--dropdown .select2-search__field').placeholder =
                'Ketikan Pencarian...';
        });

        // $(document).ready(function() {
        //     $('.select-edit').select2({
        //         placeholder: 'Silahkan Pilih...',
        //         allowClear: true,
        //         dropdownParent: $('.modalEdit'),
        //         width: '100%',
        //         theme: 'bootstrap-5',
        //     });
        // });
        // $('select').on('select2:open', function() {
        //     $('.select2-search--dropdown .select2-search__field').attr('placeholder', 'Ketikan Pencarian...');
        // });


        // setInterval(() => {
        //     fetch('/trigger-utang-reminder')
        //         .then(response => response.json())
        //         .then(data => {
        //             console.log('Reminder dipicu:', data);
        //         })
        //         .catch(error => {
        //             console.error('Gagal polling reminder:', error);
        //         });
        // }, 5000);
        // setInterval(() => {
        //     fetch('/trigger-piutang-reminder')
        //         .then(response => response.json())
        //         .then(data => {
        //             console.log('Reminder piutang dipicu:', data);
        //         })
        //         .catch(error => {
        //             console.error('Gagal polling reminder piutang:', error);
        //         });
        // }, 5000);
    </script>

</body>

</html>
