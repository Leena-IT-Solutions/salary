@extends('layouts.newapp')

@section('head')
<title>Reimbursement</title>
@endsection

@section('content')

    <reimbursement-component :types="{{ $types }}"></reimbursement-component>

@endsection