<template>
    <div class="container-fluid">

        <div class="row g-4 mb-4 align-items-center">
            <forms-text-field @change="getEmployee()" name="employee_code" label="Enter Employee Code" v-model="employee_code" error="" classes="col-12 col-lg-6"></forms-text-field>
            <div v-if="employee" class="col">
                <span class="h5">{{ employee.first_name }} {{ employee.middle_name }} {{ employee.last_name }} - {{ employee.id }}</span>
            </div>
        </div>

        <div  v-if="item" class="row g-4 mb-5 align-items-center">

            <!-- <forms-text-field name="employee_id" label="Employee ID" v-model="item.employee_id" error="" classes="col-12"></forms-text-field> -->

            <forms-date-field name="application_date" label="Application Date" v-model="item.application_date" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-date-field name="disbursed_date" label="Disbursed Date" v-model="item.disbursed_date" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-date-field name="close_date" label="Closing Date" v-model="item.close_date" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-text-field @input="calculateEMI()" name="loan_amount" label="Loan Amount" v-model="item.loan_amount" error="" classes="col-12 col-lg-4"></forms-text-field>

            <forms-text-field @input="calculateEMI()" name="rate_of_interest" label="Rate of Interest" v-model="item.rate_of_interest" error="" classes="col-12 col-lg-4"></forms-text-field>

            <forms-number-field @input="calculateEMI()" name="tenure" label="Tenure" v-model="item.tenure" error="" classes="col-12 col-lg-4"></forms-number-field>

            <forms-select-field name="status" label="Status" v-model="item.status" error="" classes="col-12 col-lg-4" 
            :options="[{ key: 'Approved', val: 'Approved' }, { key: 'Rejected', val: 'Rejected' }]"></forms-select-field>

            <forms-select-field name="is_pause" label="Is Paused?" v-model="item.is_pause" error="" classes="col-12 col-lg-4" 
            :options="[{ key: 'Yes', val: 'Yes' }, { key: 'No', val: 'No' }]"></forms-select-field>

            <!-- <forms-text-field name="emi_amount" label="EMI Amount" v-model="item.emi_amount" error="" classes="col-12 col-lg-4"></forms-text-field> -->

            <div v-if="item.emi_amount" class="col-12 col-lg-4 h3">
                EMI: Rs {{ item.emi_amount }}/-
            </div>

            <forms-text-field name="reason" label="Reason" v-model="item.reason" error="" classes="col-12"></forms-text-field>


            <forms-submit-button name="" v-model="loading" label="Save loan and advance" @click="save()" classes="col-6"></forms-submit-button>

            

            <div class="col-6 text-end">
                <button v-if="item.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="item.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

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

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th @click="orderBy('id')" class="cursor-pointer" style="width: 60px;">ID</th>
                        <th class="cursor-pointer">Employee</th>
                        <th @click="orderBy('application_date')" class="cursor-pointer">Applied On</th>
                        <th @click="orderBy('status')" class="cursor-pointer">Status</th>
                        <th @click="orderBy('loan_amount')" class="cursor-pointer">Loan Amount</th>
                        <th @click="orderBy('rate_of_interest')" class="cursor-pointer">Interest</th>
                        <th @click="orderBy('tenure')" class="cursor-pointer">Tenure</th>
                        <th @click="orderBy('emi_amount')" class="cursor-pointer">EMI Amount</th>
                        <th @click="orderBy('disbursed_date')" class="cursor-pointer">Disbursed On</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.employee.first_name }} {{ row.employee.middle_name }} {{ row.employee.last_name }}</td>
                        <td>{{ row.application_date }}</td>
                        <td>{{ row.status }}</td>
                        <td>{{ row.loan_amount }}</td>
                        <td>{{ row.rate_of_interest }}%</td>
                        <td>{{ row.tenure }} {{ row.tenure > 1 ? 'Months' : 'Month' }}</td>
                        <td>{{ row.emi_amount }}</td>
                        <td>{{ row.disbursed_date }}</td>
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

    data(){
        return {
            loading: false,
            isDelete: false,
            item: {
                id: null,
                employee_id: null,
                application_date: null,
                disbursed_date: null,
                close_date: null,
                loan_amount: null,
                emi_amount: null,
                rate_of_interest: null,
                tenure: null,
                status: null,
                reason: null,
                is_pause: null,
            },
            items: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: null,
                value: null,
                by: 'id',
                order: 'desc',
                rows: 1,
            },
            employee_code: null,
            employee: null,
        };
    },

    methods: {

        reset(){
            this.item.id = null;
            this.item.employee_id = null;
            this.item.application_date = null;
            this.item.disbursed_date = null;
            this.item.close_date = null;
            this.item.loan_amount = null;
            this.item.emi_amount = null;
            this.item.rate_of_interest = null;
            this.item.tenure = null;
            this.item.status = null;
            this.item.reason = null;
            this.item.is_pause = null;
        },

        edit(item){
            this.item.id = item.id;
            this.item.employee_id = item.employee_id;
            this.item.application_date = item.application_date;
            this.item.disbursed_date = item.disbursed_date;
            this.item.close_date = item.close_date;
            this.item.loan_amount = item.loan_amount;
            this.item.emi_amount = item.emi_amount;
            this.item.rate_of_interest = item.rate_of_interest;
            this.item.tenure = item.tenure;
            this.item.status = item.status;
            this.item.reason = item.reason;
            this.item.is_pause = item.is_pause;
        },

        fetch(){

            let url = '/approvals/loan_and_advance/fetch';
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
            axios.post('/approvals/loan_and_advance/add', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/approvals/loan_and_advance/update', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/approvals/loan_and_advance/delete', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        getEmployee(){
            this.reset();
            axios.get('/approvals/overtime/employee/' + this.employee_code).then(res => {
                this.employee = res.data.employee;
                this.item.employee_id = this.employee.id;
            });
        },

        calculateEMI(){
            if(this.item.loan_amount != null && this.item.tenure != null && this.item.rate_of_interest != null){
                let emi = 0;
                let p = this.item.loan_amount;
                let i = this.item.rate_of_interest / (12 * 100);
                let n = this.item.tenure;
                let con = Math.pow((1 + i), n);

                emi = (p * con * i) / (con - 1);

                this.item.emi_amount = Math.round(emi);
            }
        },

    },

    created(){
        this.fetch();
    },

}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>