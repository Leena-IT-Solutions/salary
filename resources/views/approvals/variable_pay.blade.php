@extends('layouts.newapp')

@section('head')
<title>Variable Pay Approval</title>
@endsection

@section('content')

<page-header title="Variable Pay Approval"></page-header>

<div class="container-fluid px-4 py-5 m-0">

    <variable-pay-approval :types="{{ $types }}"></variable-pay-approval>

</div>

@endsection