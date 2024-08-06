@extends('layouts.newapp')

@section('head')
<title>Services</title>
@endsection

@section('content')

    <services-component :types="{{ $types }}"></services-component>

@endsection