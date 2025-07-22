<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title>{{ $title ?? 'Page Blank' }} - TEKMT</title>
<meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
<link rel="icon" href="{{ asset('assets/img/finance/tekmt.png') }}" type="image/x-icon" />
@include('layouts.components.script-webfont')

<link rel="stylesheet" href="assets/css/bootstrap.min.css" />
<link rel="stylesheet" href="assets/css/plugins.min.css" />
<link rel="stylesheet" href="assets/css/kaiadmin.min.css" />
<link rel="stylesheet" href="vendor/select2/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.31.0/dist/tabler-icons.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.css">


{{-- Custom CSS --}}
<style>
    .custom-btn {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        cursor: pointer;
    }

    .custom-btn::before {
        content: '';
        color: white;
        font-size: 16px;
        font-weight: bold;
        line-height: 1;
        text-align: center;
    }

    .custom-btn.red {
        background-color: red;
    }

    .custom-btn.warning {
        background-color: orange;
    }
    .custom-btn.primary {
        background-color: #170061;
    }

    .custom-dt-button {
        background: #dbdfed !important;
        color: #333;
        border: none !important;
        padding: 10px 20px !important;
        margin: 5px;
        border-radius: 20px 0 20px 0 !important;
        font-size: 14px;
        transition: all 0.3s ease-in-out;
    }

</style>
