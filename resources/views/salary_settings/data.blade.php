@extends('layouts.newapp')

@section('head')
<title>Salary Group</title>
@endsection

@section('content')

    <salary-groupable :salary_group="{{ $salary_group }}"></salary-groupable>

@endsection