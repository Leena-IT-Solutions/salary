<template>
    <div>

        <div class="row">
            <div class="col-12">
               
                <div class="row g-3 mb-4">
                    <forms-select-field
                    @change="fetch()"
                    name="work_location_id" label="Work Location" v-model="employeeFilter.work_location_id" error="" classes="col" :options="work_locations"></forms-select-field>

                    <forms-select-field
                    @change="fetch()"
                    name="department_id" label="Departments" v-model="employeeFilter.department_id" error="" classes="col" :options="location_departments"></forms-select-field>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered ">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th>Employee</th>
                                <th class="text-center">Location</th>
                                <th class="text-center">Department</th>
                                <th class="text-center">Shift</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template  v-for="emp in employees" :key="emp.id">
                                <tr
                                @click="addRemoveEmployee(emp.id)"
                                :class="[isSelected(emp.id) == true ? 'table-info' : '']"
                                style="cursor:pointer;">
                                    <td class="text-center">{{ emp.id }}</td>
                                    <td class="text-nowrap">{{ emp.first_name }} {{ emp.middle_name }} {{ emp.last_name }}</td>
                                    <td class="text-center text-nowrap">{{ emp.employee_work_location.work_location.location_name }}</td>
                                    <td class="text-center text-nowrap">{{ emp.employee_department.department.department }}</td>
                                    <td class="text-center text-nowrap" v-if="emp.employee_shift">
                                        {{ emp.employee_shift.dt }} - {{ emp.employee_shift.working_shift.name }}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12">

                <div class="row g-3">
                    
                    <forms-select-field name="working_shift_id" label="Employee Shift" v-model="employeeShiftForm.working_shift_id" error="" classes="col-12 col-lg-4" :options="shifts"></forms-select-field>
    
                    <forms-date-field name="from" label="From Date" v-model="employeeShiftForm.from" error="" classes="col-12 col-lg-4"></forms-date-field>
    
                    <forms-date-field name="to" label="To Date" v-model="employeeShiftForm.to" error="" classes="col-12 col-lg-4"></forms-date-field>

                    <forms-submit-button @click="assignShift()" v-model="loading" name="" label="Assign Shift"></forms-submit-button>

                </div>


            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';

export default {

    props: [
        'shifts',
        'locations',
        'departments'
    ],

    data(){
        return {

            loading: false,
            work_locations: [],
            location_departments: [],

            employeeFilter: {
                work_location_id: 0,
                department_id: 0,
            },

            employeeShiftForm: {
                employees: [],
                working_shift_id: null,
                from: null,
                to: null,
            },

            employees: [],

        };
    },

    methods: {

        reset(){
            this.employeeShiftForm.employees = [];
            this.employeeShiftForm.working_shift_id = null;
            this.employeeShiftForm.from = null;
            this.employeeShiftForm.to = null;
        },

        assignShift(){
            this.loading = true;
            axios.post('/employee_shift/save', this.employeeShiftForm)
            .then(res => {
                this.loading = false;
                this.reset();
                this.fetch();
            })
        },

        isSelected(id){
            return this.employeeShiftForm.employees.includes(id);
        },

        addRemoveEmployee(id){
            let arr = this.employeeShiftForm.employees;
            if(arr.includes(id)){
                arr.splice(arr.indexOf(id), 1)
            } else {
                arr.push(id);
            }
        },
        
        fetch(){
            axios.get('/employee_shift/employee/fetch', {
                params: this.employeeFilter
            })
            .then(res => {
                this.employees = res.data;
            });
        },

        addSelectAllOption(){
            let option = {
                val: "0",
                key: "All"
            };
            this.work_locations.push(option);
            this.location_departments.push(option);
            this.locations.forEach(loc => {
                this.work_locations.push(loc);
            });
            this.departments.forEach(dept => {
                this.location_departments.push(dept);
            });
        },

    },

    created(){
        this.addSelectAllOption();
        this.fetch();
    },

}
</script>