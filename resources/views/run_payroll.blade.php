@extends('layouts.newapp')

@section('head')
<title>Run Payroll</title>
@endsection

@section('content')

<page-header title="Run Payroll"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    <run-payroll :financial_years="{{ $financial_years }}" from="{{ $from }}" to="{{ $to }}" :fy="{{ $fy }}"></run-payroll>

</div>

@endsection