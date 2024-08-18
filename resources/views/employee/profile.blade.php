@extends('layouts.newapp')

@section('head')
<title>Employee Profile</title>
@endsection

@section('content')

<!-- <page-header title="{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}"></page-header> -->

<div class="container-fluid px-4 mt-4 m-0">

    <table class="table table-sm">
        <tbody>
        
            <tr>
                <td class="text-no-wrap" style="width:320px;">
                    <div class="w-50">
                        <div class="image image-s img-cover">
                            <img src="{{ $employee->employee_photo->media }}" alt="">
                        </div>
                    </div>
                </td>
                <td class="align-middle">
                    <span class="h4 fw-bold d-block">
                        {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}
                        <span class="small text-secondary">
                            : {{ $employee->employee_designation->designation->designation }} - {{ $employee->employee_designation->designation->code }}
                        </span>
                    </span>
                    <span class="d-block small">{{ $employee->employee_address->address }} {{ $employee->employee_address->city }} {{ $employee->employee_address->pincode }} {{ $employee->employee_address->state }} {{ $employee->employee_address->country }}</span>
                    <span class="d-block">{{ $employee->phone }}</span>
                    <span class="d-block">{{ $employee->email }}</span>
                </td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Employee Code</td>
                <td>{{ $employee->employee_code }} - {{ $employee->tagid }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Department</td>
                <td>{{ $employee->employee_department->department->department }} - {{ $employee->employee_department->department->code }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Date of Birth</td>
                <td>{{ $employee->dob }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Gender</td>
                <td>{{ $employee->gender }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Blood Group</td>
                <td>{{ $employee->blood_group }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Religion</td>
                <td>{{ $employee->religion }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Cast</td>
                <td>{{ $employee->cast }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Subcast</td>
                <td>{{ $employee->subcast }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Mother Tongue</td>
                <td>{{ $employee->mothertongue }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Nationality</td>
                <td>{{ $employee->nationality }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Marital Status</td>
                <td>{{ $employee->marital_status }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Qualification</td>
                <td>{{ $employee->qualification }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Degree</td>
                <td>{{ $employee->degree }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Aadhar Number</td>
                <td>{{ $employee->aadhar }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Permanent Account Number - PAN</td>
                <td>{{ $employee->pan }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">PF Number</td>
                <td>{{ $employee->pf }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Universal Account Number</td>
                <td>{{ $employee->uan }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Joining Date</td>
                <td>{{ $employee->doj }}</td>
            </tr>
            <tr>
                <td class="text-no-wrap" style="width:320px;">Exit Date</td>
                <td>{{ $employee->doe }}</td>
            </tr>
        </tbody>
    </table>

</div>


<div class="container-fluid px-4 py-4 m-0">

    <employee-update 
    :employee="{{ $employee }}" 
    :work_locations="{{ $work_locations }}" 
    :designations="{{ $designations }}" 
    :departments="{{ $departments }}"
    :leave_groups="{{ $leave_groups }}"
    :salary_groups="{{ $salary_groups }}"
    :services="{{ $services }}"></employee-update>

</div>


@endsection