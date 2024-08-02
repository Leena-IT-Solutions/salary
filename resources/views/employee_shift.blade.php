@extends('layouts.newapp')

@section('head')
<title>Employee Shift</title>
@endsection

@section('content')

<page-header title="Employee Shift Manager"></page-header>

<div class="container-fluid px-4 py-5 m-0">

<employee-shift-manager :shifts="{{ $shifts }}" :locations="{{ $locations }}" :departments="{{ $departments }}"></employee-shift-manager>

<!-- 
Employee List

Shift List
Locations List
Department List
-->

</div>

@endsection