@extends('layouts.newapp')

@section('head')
<title>Statutory Compliance</title>
@endsection

@section('content')

    <statutory-compliance-condition :statutory_compliance="{{ $statutory_compliance }}"></statutory-compliance-condition>

@endsection