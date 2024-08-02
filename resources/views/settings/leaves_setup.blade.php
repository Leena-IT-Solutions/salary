@extends('layouts.newapp')

@section('head')
<title>Leaves Setup</title>
@endsection

@section('content')

<page-header title="Leaves Setup"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    <!-- Nav pills -->
    <ul class="nav nav-tabs mb-5">
        <li class="nav-item">
            <a class="nav-link px-5 me-2 active" data-bs-toggle="pill" href="#home">Leave Group</a>
        </li>
        <li class="nav-item">
            <a class="nav-link px-5" data-bs-toggle="pill" href="#menu1">Leave Type</a>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        
        <div class="tab-pane active" id="home">
            <leave-group-form :leaves="{{ $leaves }}"></leave-group-form>
        </div>

        <div class="tab-pane fade" id="menu1">
            <leave-type-form></leave-type-form>
        </div>

    </div>

</div>
@endsection