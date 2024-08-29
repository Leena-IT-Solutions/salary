<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('head')

    <style>
        * {
            margin: 0;
            padding: 0;
        }
        .p-0 { padding: 0mm; }
        .p-1 { padding: 1mm; }
        .p-2 { padding: 3mm; }
        .p-3 { padding: 5mm; }
        .p-4 { padding: 7mm; }
        .p-5 { padding: 15mm; }
        .pb-0 { padding-bottom: 0mm; }
        .pb-1 { padding-bottom: 1mm; }
        .pb-2 { padding-bottom: 3mm; }
        .pb-3 { padding-bottom: 5mm; }
        .pb-4 { padding-bottom: 7mm; }
        .pb-5 { padding-bottom: 15mm; }
        .page-break {
            page-break-after: always;
        }
        .w-full {
            width: 100%;
        }
        .w-half {
            width: 50%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        table td, table th {
            border: 1px solid black;
            padding: 1mm;
        }
        .table-borderless td, .table-borderless th {
            border: 0px;
            padding: 1mm;
        }
        .table-top-bottom td {
            border-top: 1px solid black !important;
            border-bottom: 1px solid black !important;
            border-right: 0px;
            border-left: 0px;
            padding-top: 1mm;
            padding-bottom: 1mm;
        }

        .fw-normal {
            font-weight: normal;
        }
        .fw-bold {
            font-weight: bold;
        }
        .text-start { text-align: left; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .v-top { vertical-align: top; }
        .v-middle { vertical-align: middle; }
        .v-bottom { vertical-align: bottom; }
        .text-uppercase: { text-transform: uppercase; }
        .text-lowercase: { text-transform: lowercase; }
        .text-capitalize: { text-transform: capitalize; }
        .d-inline: { display: inline; }
        .d-inline-block: { display: inline-block; }
        .d-block: { display: block; }
        .d-none: { display: none; }
    </style>

</head>
<body>
    <div id="app">

        @yield('content')

    </div>
</body>
</html>