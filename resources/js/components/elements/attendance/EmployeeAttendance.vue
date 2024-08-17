<template>
    <div class="container-fluid position-relative">
        
        <!-- Filter -->
        <div class="row g-3 mb-4">
            <forms-select-field
            @change="fetch()"
            name="work_location_id" label="Work Location" v-model="employeeFilter.work_location_id" error="" classes="col" :options="work_locations"></forms-select-field>

            <forms-select-field
            @change="fetch()"
            name="department_id" label="Departments" v-model="employeeFilter.department_id" error="" classes="col" :options="location_departments"></forms-select-field>

            <forms-select-field
            @change="changeReportType()"
            name="report_type" label="Report Type" v-model="employeeFilter.report_type" error="" classes="col" :options="[{key: 'Daily Report', val: 'Daily'}]"></forms-select-field>

        </div>

        <!-- Calender Navigation -->
        <div class="container-fluid">
            <div class="row justify-content-center align-items-center mb-4" v-if="employeeFilter.report_type == 'Daily'">
                <div class="col-auto">
                    <button class="btn btn-dark btn-sm" @click="prevDay()">PREV</button>
                </div>
                <div class="col-auto fs-4"><div>{{ employeeFilter.current_date }}</div></div>
                <div class="col-auto">
                    <button class="btn btn-dark btn-sm" @click="nextDay()">NEXT</button>
                </div>
            </div>
    
            <div class="row justify-content-center align-items-center mb-4" v-if="employeeFilter.report_type == 'Monthly'">
                <div class="col-auto">
                    <button class="btn btn-dark btn-sm" @click="prevMonth()">PREV</button>
                </div>
                <div class="col-auto fs-4"><div>{{ employeeFilter.current_month_name }}, {{ employeeFilter.current_year }}</div></div>
                <div class="col-auto">
                    <button class="btn btn-dark btn-sm" @click="nextMonth()">NEXT</button>
                </div>
            </div>
        </div>

        <div class="container-fluid mb-3 text-center">
            <span class="bg-danger-subtle p-2 fs-6 d-inline-block rounded me-2">Weekoff / Holiday</span>
            <span class="bg-secondary-subtle p-2 fs-6 d-inline-block rounded me-2">Absent</span>
            <span class="bg-success-subtle p-2 fs-6 d-inline-block rounded me-2">Leave / Halfday / Short Leave</span>
            <span class="bg-warning-subtle p-2 fs-6 d-inline-block rounded me-2">Time Update / On Duty</span>
        </div>

        <!-- Data Grid -->
        <div class="">
            <div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr class="table-dark">
                                <th class="text-nowrap">Employee</th>
                                <th class="text-nowrap">Code</th>
                                <th class="text-nowrap">Shift</th>
                                <th class="text-nowrap">Status</th>
                                <th class="text-nowrap">LOP</th>
                                <th class="text-nowrap">Late</th>
                                <th class="text-nowrap">Early</th>
                                <th v-if="employeeFilter.report_type == 'Daily'" class="text-nowrap">Punch Time</th>
            
                                <template v-if="employeeFilter.report_type == 'Monthly'">
                                    <th class="text-nowrap" v-for="dd,ind in day_count[months.indexOf(employeeFilter.current_month)]" :key="ind">
                                        <!-- {{employeeFilter.current_year}}-{{ employeeFilter.current_month }}- -->{{ (dd*1 < 10 ? "0"+dd : dd) }}
                                    </th>
                                </template>
            
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="emp in employees" :key="emp.id">
                                <template v-if="emp.employee_shifts">
                                    <template v-if="emp.employee_shifts.length > 0">
                                        <template v-if="employeeFilter.report_type == 'Daily'">
                                            <tr
                                            :class="[
                                                (emp.employee_shifts[0].status) == 'Absent' ? 'text-light table-secondary' : '',
                                                (emp.employee_shifts[0].status) == 'On Duty' || (emp.employee_shifts[0].status) == 'Time Update' ? 'text-light table-warning' : '',
                                                (emp.employee_shifts[0].status) == 'Weekoff' || (emp.employee_shifts[0].status) == 'Holiday' ? 'text-light table-danger' : '',
                                                (emp.employee_shifts[0].status) == 'Leave' || (emp.employee_shifts[0].status) == 'Short Leave' || (emp.employee_shifts[0].status) == 'Halfday Leave' ? 'text-light table-success' : '',
                                                ]">
                                                <td class="text-nowrap">{{ emp.first_name }} {{ emp.middle_name }} {{ emp.last_name }}</td>
                                                <td class="text-nowrap">{{ emp.employee_code }}</td>
    
                                                <td class="text-nowrap">{{ emp.employee_shifts[0].working_shift.name }} {{ emp.employee_shifts[0].working_shift.in }} - {{ emp.employee_shifts[0].working_shift.out }}</td>
                                                <td class="text-nowrap">{{ emp.employee_shifts[0].status }}</td>
                                                <td class="text-nowrap">{{ emp.employee_shifts[0].lop }}</td>
                                                <td class="text-nowrap">{{ emp.employee_shifts[0].late }} min</td>
                                                <td class="text-nowrap">{{ emp.employee_shifts[0].early }} min</td>
                                                <td class="text-nowrap">
                                                    <span class="" v-for="att,iii in emp.employee_shifts[0].employee_attendance" :key="att.id">
                                                        {{ att.tm }} {{ emp.employee_shifts[0].employee_attendance.length == (iii + 1) ? '' : ' - ' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                    </template>
                                </template>
                            </template>
                        </tbody>
                        <!-- <tbody>
                            <template v-for="emp in employees" :key="emp.id">
                                <tr>
                                    <td class="text-nowrap">{{ emp.first_name }} {{ emp.middle_name }} {{ emp.last_name }}</td>
                                    <td class="text-nowrap">{{ emp.employee_code }}</td>
                                    
                                    <td class="text-nowrap fs-6" v-if="employeeFilter.report_type == 'Daily'">
                                        <template v-if="emp.employee_shifts">
                                            <template v-if="emp.employee_shifts.length > 0">
                                                {{ emp.employee_shifts[0].working_shift.name }} 
                                                <div class="small" v-for="att in emp.employee_shifts[0].employee_attendance" :key="att.id">
                                                    {{ att.tm }}
                                                </div>
                                            </template>
                                        </template>
                                    </td>
    
                                    <template v-if="employeeFilter.report_type == 'Monthly'">
                                        <td class="text-wrap" v-for="dd,ind in day_count[months.indexOf(employeeFilter.current_month)]" :key="ind">
                                            <template v-if="emp.employee_shifts">
                                                <template v-if="emp.employee_shifts.length > 0" >
    
                                                    <div :set="sss = demo((employeeFilter.current_year + '-' + employeeFilter.current_month + '-' + (dd*1 < 10 ? '0'+dd : dd)), emp.employee_shifts)" >
                                                        <div v-if="sss != null">
                                                            <span class="fs-10">{{ sss.status }}</span>
                                                            <span class="text-nowrap d-block">{{ sss.working_shift.name }}</span>
                                                            <span class="d-block" v-for="att in sss.employee_attendance" :key="att.id">
                                                                {{ att.tm }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </template>
                                        </td>
                                    </template>
    
                                </tr>
                            </template>
                        </tbody> -->
                    </table>
                </div>
            </div>
        </div>

        <!-- Evalute Button -->
        <div class="container-fluid text-center">
            <button class="btn btn-primary" @click="evaluteLOP()">Evalute LOP</button>
        </div>

    </div>
</template>

<script>
import axios from 'axios';
export default {

    props: [
        'locations',
        'departments',
        'today',
        'month',
    ],

    data(){
        return {

            loading: false,
            work_locations: [],
            location_departments: [],
            months: ["01","02","03","04","05","06","07","08","09","10","11","12"],
            month_name: ["January","February","March","April","May","June","July","August","Septmber","October","November","December"],
            day_count: [31,28,31,30,31,30,31,31,30,31,30,31],

            employeeFilter: {
                work_location_id: 0,
                department_id: 0,
                report_type: 'Daily',
                current_date: null,
                current_month: null,
                current_year: null,
                current_month_name: null
            },

            employees: [],

        };
    },

    methods: {

        evaluteLOP(){
            this.employees.forEach(employee => {
                axios.post('/attendance/evalute', {
                    on_date: this.employeeFilter.current_date,
                    employee_id: employee.id,
                }).then(res=> {
                    this.fetch();
                });
            });
        },

        demo(dt, shifts){
            let a = shifts.filter( function (shift){
                let dd = new Date(shift.dt);
                dd = dd.getTime();
                let ddd = new Date(dt)
                ddd = ddd.getTime();
                return dd == ddd;
            });
            return a.length > 0 ? a[0] : null;
            //return a.length > 0 ? a[0].working_shift.name + "" + working_shift.employee_attendance: "None";
        },

        setCurrentDate(dt){
            let d = new Date(dt);
            this.employeeFilter.current_date = `${d.getFullYear()}-${this.months[d.getMonth()]}-${d.getDate()}`;
            this.employeeFilter.current_month = this.months[d.getMonth()];
            this.employeeFilter.current_year = d.getFullYear();
            let ind = this.months.indexOf(this.employeeFilter.current_month);
            this.employeeFilter.current_month_name = this.month_name[ind];
            let mod = this.employeeFilter.current_year % 4;
            this.day_count[1] = mod == 0 ? 29 : 28;

            // console.log(this.employeeFilter.current_date);
            // console.log(this.employeeFilter.current_month);
            // console.log(this.employeeFilter.current_year);
            // console.log(this.employeeFilter.current_month_name);

        },

        changeReportType(){
            this.setCurrentDate(this.today);
            this.fetch();
        },

        nextMonth(){
            let ind = this.months.indexOf(this.employeeFilter.current_month);
            let newDate = new Date(this.employeeFilter.current_year, ind + 1);
            this.setCurrentDate(newDate.toString());
            this.fetch();
        },

        prevMonth(){
            let ind = this.months.indexOf(this.employeeFilter.current_month);
            let newDate = new Date(this.employeeFilter.current_year, ind - 1);
            this.setCurrentDate(newDate.toString());
            this.fetch();
        },

        nextDay(){
            let cd = this.employeeFilter.current_date;
            let d = new Date(cd);
            d.setDate(d.getDate() + 1);
            let dt = `${d.getFullYear()}-${this.months[d.getMonth()]}-${d.getDate()}`;
            this.employeeFilter.current_date = dt;
            this.fetch();
        },

        prevDay(){
            let cd = this.employeeFilter.current_date;
            let d = new Date(cd);
            d.setDate(d.getDate() - 1);
            let dt = `${d.getFullYear()}-${this.months[d.getMonth()]}-${d.getDate()}`;
            this.employeeFilter.current_date = dt;
            this.fetch();
        },

        fetch(){
            axios.get('/attendance/fetch', {
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
        // this.employeeFilter.current_date = this.today;
        // this.employeeFilter.current_month = this.month;
        // let ind = this.months.indexOf(this.employeeFilter.current_month);
        // this.employeeFilter.current_month_name = this.month_name[ind];

        this.setCurrentDate(this.today);
        this.addSelectAllOption();
        this.fetch();
    },

}
</script>

<style>
.fs-10{
    font-size: 10px;
}
</style>