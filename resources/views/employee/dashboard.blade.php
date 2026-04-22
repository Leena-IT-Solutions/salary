@extends('layouts.newapp')

@section('head')
<title>Employee Dashboard | {{ config('app.name') }}</title>
@endsection

@section('content')

<div class="container-fluid px-4 py-4">
    <employee-dashboard active-tab="{{ $tab ?? 'overview' }}"></employee-dashboard>
</div>

@endsection
