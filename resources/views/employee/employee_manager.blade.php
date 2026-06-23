@extends('layouts.newapp')

@section('head')
<title>Employee Manager</title>
@endsection

@section('content')

<page-header title="Employee Manager"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    <employee-manager :departments="{{ json_encode($departments) }}" :designations="{{ json_encode($designations) }}"></employee-manager>

</div>

@endsection