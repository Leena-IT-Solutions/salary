@extends('layouts.newapp')

@section('head')
<title>Leave Approval</title>
@endsection

@section('content')

<page-header title="Leave Approval"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    <leave-approval :leaves="{{ $leaves }}" :fy="{{ $fy }}" :fys="{{ $fys }}"></leave-approval>

</div>

@endsection