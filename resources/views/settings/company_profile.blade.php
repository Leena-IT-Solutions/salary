@extends('layouts.newapp')

@section('head')
<title>Company Profile</title>
@endsection

@section('content')

<page-header title="Company Profile"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    <logo-upload class="mb-5"></logo-upload>

    <company-profile class="mb-5"></company-profile>

    <company-registration></company-registration>

</div>
@endsection