@extends('layouts.newapp')

@section('head')
<title>Exemption and Deduction</title>
@endsection

@section('content')

    <exemption-and-deduction-component :types="{{ $types }}"></exemption-and-deduction-component>
    
@endsection