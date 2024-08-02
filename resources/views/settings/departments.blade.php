@extends('layouts.newapp')

@section('head')
<title>Departments</title>
@endsection

@section('content')

<page-header title="Departments Manager"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    <departments-form></departments-form>

</div>
@endsection