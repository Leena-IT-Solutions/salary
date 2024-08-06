@extends('layouts.newapp')

@section('head')
<title>Earning Component</title>
@endsection

@section('content')

    <earnings-component :types="{{ $types }}"></earnings-component>

@endsection