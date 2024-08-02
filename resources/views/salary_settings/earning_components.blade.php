@extends('layouts.newapp')

@section('head')
<title>Earning Component</title>
@endsection

@section('content')

    <earnings-component :earning_types="{{ $earning_types }}"></earnings-component>

@endsection