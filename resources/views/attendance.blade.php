@extends('layouts.newapp')

@section('head')
<title>Attendance</title>
@endsection

@section('content')

<page-header title="Attendance"></page-header>

<div class="container-fluid px-4 py-5 m-0">

<div class="">
    <employee-attendance
    today="{{ $today }}"
    month="{{ $month }}"
    :locations="{{ $locations }}"
    :departments="{{ $departments }}">
    </employee-attendance>
</div>


</div>

@endsection