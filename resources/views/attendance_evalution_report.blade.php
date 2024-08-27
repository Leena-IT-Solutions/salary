@extends('layouts.newapp')

@section('head')
<title>Attendance Evalution Report</title>
@endsection

@section('content')

<page-header title="Attendance Evalution Report"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    <div class="">
        <attendance-evalution-report from="{{ $from }}" to="{{ $to }}"></attendance-evalution-report>
    </div>

</div>

@endsection