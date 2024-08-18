<template>
    <div class="container-fluid">

        <section-title title="Add Department" class=""></section-title>

        <!-- Form -->
        <div  v-if="item" class="row g-4 mb-5">

            <forms-text-field name="employee_id" label="Employee ID" v-model="item.employee_id" error="" classes="col-12"></forms-text-field>

            <forms-text-field name="account_name" label="Account Name" v-model="item.account_name" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-text-field name="account_number" label="Account Number" v-model="item.account_number" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-select-field name="account_type" label="Account Type" v-model="item.account_type" error="" classes="col-12 col-xl-4" :options="[
                {key: 'Savings', val: 'Savings'},
                {key: 'Current', val: 'Current'},
                {key: 'Salary', val: 'Salary'},
                {key: 'NRI', val: 'NRI'},
            ]"></forms-select-field>

            <forms-text-field name="bank_name" label="Bank Name" v-model="item.bank_name" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-text-field name="branch" label="Branch" v-model="item.branch" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-text-field name="ifsc" label="IFSC" v-model="item.ifsc" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-submit-button name="" v-model="loading" label="Save department" @click="save()" classes="col-6"></forms-submit-button>

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
                        <th @click="orderBy('account_name')" class="cursor-pointer">Name</th>
                        <th @click="orderBy('account_number')" class="cursor-pointer">Account Number</th>
                        <th @click="orderBy('account_type')" class="cursor-pointer">Type</th>
                        <th @click="orderBy('bank_name')" class="cursor-pointer">Bank</th>
                        <th @click="orderBy('branch')" class="cursor-pointer">Branch</th>
                        <th @click="orderBy('ifsc')" class="cursor-pointer">IFSC</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.account_name }}</td>
                        <td>{{ row.account_number }}</td>
                        <td>{{ row.account_type }}</td>
                        <td>{{ row.bank_name }}</td>
                        <td>{{ row.branch }}</td>
                        <td>{{ row.ifsc }}</td>
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

    props: ['employee_id'],

    data(){
        return {
            loading: false,
            isDelete: false,
            item: {
                id: null,
                employee_id: null,
                account_name: null,
                account_number: null,
                account_type: null,
                bank_name: null,
                branch: null,
                ifsc: null,
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
            }
        };
    },

    methods: {

        reset(){
            this.item.id = null;
            /* this.item.employee_id = null; */
            this.item.account_name = null;
            this.item.account_number = null;
            this.item.account_type = null;
            this.item.bank_name = null;
            this.item.branch = null;
            this.item.ifsc = null;
        },

        edit(item){
            this.item.id = item.id;
            this.item.employee_id = item.employee_id;
            this.item.account_name = item.account_name;
            this.item.account_number = item.account_number;
            this.item.account_type = item.account_type;
            this.item.bank_name = item.bank_name;
            this.item.branch = item.branch;
            this.item.ifsc = item.ifsc;
        },

        fetch(){

            let url = '/employee/employee_bank/'+this.item.employee_id+'/fetch/';
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
            axios.post('/employee/employee_bank/add', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/employee/employee_bank/update', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_bank/delete', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

    },

    created(){
        this.item.employee_id = this.employee_id;
        this.fetch();
    },

}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>