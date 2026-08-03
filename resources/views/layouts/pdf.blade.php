<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { 
            margin: 0;
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #333;
            background: #fff;
        }
        .container {
            padding: 8mm 12mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .p-0 { padding: 0mm; }
        .p-1 { padding: 1mm; }
        .p-2 { padding: 2mm; }
        .p-3 { padding: 3mm; }
        .pb-1 { padding-bottom: 1.5mm; }
        .pb-2 { padding-bottom: 3mm; }
        .pb-3 { padding-bottom: 5mm; }
        
        .page-break {
            page-break-after: always;
        }
        .w-full { width: 100%; }
        .w-half { width: 50%; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        table td, table th {
            padding: 2mm;
            vertical-align: top;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #e2e8f0;
        }
        .table-borderless td, .table-borderless th {
            border: 0;
        }
        
        .header-bg {
            background-color: #f8fafc;
            border-bottom: 2px solid #6366f1;
            padding: 8mm 15mm;
        }
        .section-title {
            background-color: #6366f1;
            color: white;
            padding: 1.5mm 3mm;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2mm;
        }
        
        .fw-bold { font-weight: bold; }
        .text-start { text-align: left; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-muted { color: #64748b; }
        .text-primary { color: #6366f1; }
        
        .net-payable-box {
            border: 1px solid #6366f1;
            background-color: #f5f3ff;
            padding: 4mm;
            margin-top: 5mm;
        }
        
        .text-uppercase { text-transform: uppercase; }
        .text-capitalize { text-transform: capitalize; }
        
        hr {
            border: 0;
            border-top: 1px solid #e2e8f0;
            margin: 5mm 0;
        }
    </style>

    @yield('head')
</head>
<body>
    <div id="app">

        @yield('content')

    </div>
</body>
</html>