@extends('layouts.newapp')

@section('head')
<title>Employee Profile</title>
@endsection

@section('content')

<page-header title="{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}"></page-header>



<div class="container-fluid px-4 py-5 m-0">

    Date of Birth: {{ $employee->dob }}

</div>


<div class="container-fluid px-4 py-5 m-0">

    <employee-update 
    :employee="{{ $employee }}" 
    :work_locations="{{ $work_locations }}" 
    :designations="{{ $designations }}" 
    :departments="{{ $departments }}"
    :leave_groups="{{ $leave_groups }}"
    :salary_groups="{{ $salary_groups }}"></employee-update>

</div>


@endsection