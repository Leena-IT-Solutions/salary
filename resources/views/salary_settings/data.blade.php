@extends('layouts.newapp')

@section('head')
<title>Salary Group</title>
@endsection

@section('content')

    <salary-group-data :salary_group="{{ $salary_group }}"
    :earnings="{{ $earnings }}"
    :services="{{ $services }}"
    :reimbursements="{{ $reimbursements }}"
    :statutory="{{ $statutory }}"></salary-group-data>

@endsection