@extends('layouts.pdf')

@section('head')
    <title>Attendance Report</title>
@endsection

@section('content')

<table style="font-size: 2.5mm;" class="">
    <thead>
        <tr>
            <th colspan="{{ sizeof($dds) + 2 }}" class="p-3">Report for {{ date("d-m-Y", strtotime($from)) }} to {{ date("d-m-Y", strtotime($to)) }}</th>
        </tr>
        <tr>
            <th>Name</th>
            <th>Emp Code</th>
            @foreach($dds as $dt)
            <th>{{$dt}}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $employee)
            <tr>
                <td>{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}</td>
                <td>{{ $employee->employee_code }}</td>
                
                    @foreach($employee->employee_shifts->where('dt', '>=', $from)->where('dt', '<=', $to) as $es)
                    <td>
                        {{ $es->status }}
                        @foreach($es->employee_attendance as $ea)
                        {{$ea->tm}}
                        <br>
                        @endforeach
                    </td>
                    @endforeach
                
            </tr>
        @endforeach
        
    </tbody>
</table>

@endsection