@extends('layouts.newapp')

@section('head')
<title>Dashboard Overview</title>
@endsection

@section('content')

<page-header title="Overview"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    <div class="row g-4">

        <div class="col-6">
            <div class="shadow p-4 rounded-3">
                <h1>{{ $present }}</h1>
                <h5 class="">Presend Employees</h5>
            </div>
        </div>

        <div class="col-6">
            <div class="shadow p-4 rounded-3">
                <h1>{{ $absent }}</h1>
                <h5 class="">Absent Employees</h5>
            </div>
        </div>

        <div class="col">
            <div class="shadow p-4 rounded-3">
                <h1>{{ $total_employee }}</h1>
                <h5 class="">Total Employees</h5>
            </div>
        </div>

    </div>

</div>

@endsection 