@extends('layouts.newapp')

@section('head')
<title>Pending Approvals</title>
@endsection

@section('content')

<page-header title="Pending Approvals"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    <pending-approvals :fy="{{ $fy }}"></pending-approvals>

</div>

@endsection
