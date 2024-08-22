<template>
    <div class="container-fluid">

        <!-- Form -->
        <div  v-if="item" class="row g-4 mb-5">

            <forms-select-field name="financial_year_id" label="Financial Year" v-model="item.financial_year_id" error="" classes="col-12 col-lg-6" :options="financial_years"></forms-select-field>

            <forms-text-field name="payroll_name" label="Payroll Name" v-model="item.payroll_name" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-date-field @change="processPayroll()" name="from" label="From" v-model="item.from" error="" classes="col-12 col-lg-6"></forms-date-field>

            <forms-date-field @change="processPayroll()" name="to" label="To" v-model="item.to" error="" classes="col-12 col-lg-6"></forms-date-field>


            <div class="col-12 py-4">
                <div class="row align-items-center justify-content-center">
                    <div class="col-auto">
                        <button @click="shift_dates('prev')" class="btn btn-dark">PREV</button>
                    </div>
                    <div class="col-auto h4 m-0">
                        {{ item.from }} - {{ item.to }}
                    </div>
                    <div class="col-auto">
                        <button @click="shift_dates('next')" class="btn btn-dark">NEXT</button>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Actual Days in Pay Cycle</th>
                            <th>Working Days in Pay Cycle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ item.actual_days }}</td>
                            <td>{{ item.working_days }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-12">
                <table class="table table-bordered">
                    <thead>
                        <tr class="table-primary">
                            <th class="text-center" style="width: 80px;">SR</th>
                            <th>Employee</th>
                            <th>Employee Code</th>
                            <th>CTC</th>
                            <th>Gross Pay</th>
                            <th>Effective From</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee, ind in employees" :key="employee.id"
                        class="cursor-pointer"
                        :class="[isSelected(employee.id) == true ? 'table-info' : '']"
                        @click="addRemoveEmployee(employee.id)">
                            <td class="text-center">{{ ind + 1 }}</td>
                            <td>{{ employee.first_name }} {{ employee.middle_name }} {{ employee.last_name }}</td>
                            <td>{{ employee.employee_code }}</td>
                            <td>
                                <template v-if="employee.employee_salaries.length > 0">
                                    {{ employee.employee_salaries[0].ctc }}
                                </template>
                            </td>
                            <td>
                                <template v-if="employee.employee_salaries.length > 0">
                                    {{ employee.employee_salaries[0].gross_pay }}
                                </template>
                            </td>
                            <td>
                                <template v-if="employee.employee_salaries.length > 0">
                                    {{ employee.employee_salaries[0].effective_from }}
                                </template>
                            </td>
                        </tr>
                    </tbody>

                </table>
                
            </div>

            <forms-submit-button name="" v-model="loading" label="Run Payroll" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="item.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="item.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <!-- Search -->
        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: 'Department', val: 'department'},{key: 'Code', val: 'code'},]"></forms-select-field>

            <forms-text-field name="search" label="Type Search Sring" v-model="params.value" error="" classes="col"></forms-text-field>

            <div class="col-auto">
                <button class="btn btn-primary h-100" @click="search()">Search</button>
            </div>
        </div>

        <!-- Data -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th @click="orderBy('id')" class="cursor-pointer" style="width: 60px;">ID</th>
                        <th @click="orderBy('payroll_name')" class="cursor-pointer">Name</th>
                        <th @click="orderBy('from')" class="cursor-pointer">From</th>
                        <th @click="orderBy('to')" class="cursor-pointer">To</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.payroll_name }}</td>
                        <td>{{ row.from }}</td>
                        <td>{{ row.to }}</td>
                        <td class="text-end">
                            <button class="btn btn-outline-info btn-sm me-2" @click="edit(row)"><i class="bi bi-pencil"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center">
                <button class="btn btn-dark" :disabled="next_page_url == null" @click="fetch()">Load More</button>
            </div>
        </div>

    </div>
</template>

<script>
import axios from "axios";
export default {

    props: ['financial_years', 'from', 'to', 'fy'],

    data(){
        return {
            loading: false,
            isDelete: false,
            item: {
                id: null,
                financial_year_id: null,
                payroll_name: null,
                from: null,
                to: null,
                working_days: null,
                actual_days: null,
                gross_pay: null,
                net_pay: null,
                eids : [],
            },
            items: [],
            employees: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: null,
                value: null,
                by: 'id',
                order: 'desc',
                rows: 1,
            },
        };
    },

    methods: {

        reset(){
            this.item.id = null;
            this.item.payroll_name = null;
            this.item.working_days = null;
            this.item.actual_days = null;
            this.item.gross_pay = null;
            this.item.net_pay = null;
        },

        edit(item){
            this.item.id = item.id;
            this.item.financial_year_id = item.financial_year_id;
            this.item.payroll_name = item.payroll_name;
            this.item.from = item.from;
            this.item.to = item.to;
            this.item.working_days = item.working_days;
            this.item.actual_days = item.actual_days;
            this.item.gross_pay = item.gross_pay;
            this.item.net_pay = item.net_pay;
        },

        shift_dates(what){
            axios.post('/overview/run_payroll/shift_dates', { from: this.item.from, what: what }).then(res=> {
                this.item.from = res.data.from;
                this.item.to = res.data.to;
                this.processPayroll();
            });
        },

        formatDate(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) 
                month = '0' + month;
            if (day.length < 2) 
                day = '0' + day;
            return [year, month, day].join('-');
        },

        processPayroll(){
            this.item.eids = [];
            if(this.item.from && this.item.to){
                axios.post('/overview/run_payroll/fetch_employees', this.item).then(res => {
                    this.item.working_days = res.data.working_days;
                    this.item.actual_days = res.data.actual_days;
                    this.employees = res.data.employees;
                });
            }
        },

        isSelected(id){
            return this.item.eids.includes(id);
        },

        addRemoveEmployee(id){
            let arr = this.item.eids;
            if(arr.includes(id)){
                arr.splice(arr.indexOf(id), 1)
            } else {
                arr.push(id);
            }
        },

        fetch(){

            let url = '/overview/run_payroll/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.items = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.items.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.items = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.item.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        add(){
            this.loading = true;
            axios.post('/overview/run_payroll/add', this.item).then(res => {
                console.log(res.data);
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/overview/run_payroll/update', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/overview/run_payroll/delete', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

    },

    created(){
        this.item.from = this.from;
        this.item.to = this.to;
        this.item.financial_year_id = this.fy.id;
        this.fetch();
        this.processPayroll();
    },

}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>