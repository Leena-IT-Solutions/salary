<template>
    <div class="container-fluid">

        <section-title title="Add Employee Designation" class=""></section-title>

        <div  v-if="employee_designation" class="row g-4 mb-5">

            <forms-select-field name="designation_id" label="Designation" v-model="employee_designation.designation_id" error="" classes="col-12 col-xl-4" :options="designations"></forms-select-field>

            <forms-date-field name="from" label="From" v-model="employee_designation.from" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-date-field name="to" label="To" v-model="employee_designation.to" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-submit-button name="" v-model="loading" label="Save Employee Designation" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="employee_designation.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="employee_designation.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <!-- Search -->
        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: ' Employee Designation', val: 'employee_designation'},{key: 'Code', val: 'code'},]"></forms-select-field>

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
                        <th @click="orderBy('employee_designation')" class="cursor-pointer"> Designation</th>
                        <th @click="orderBy('employee_designation')" class="cursor-pointer">Form</th>
                        <th @click="orderBy('employee_designation')" class="cursor-pointer">to</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="wl in employee_designations" :key="wl.id">
                        <td>{{ wl.id }}</td>
                        <td>{{ getDesignations(wl.designation_id) }}</td>
                        <td>{{ wl.from }}</td>
                        <td>{{ wl.to }}</td>
                        <td class="text-end">
                            <button class="btn btn-outline-info btn-sm me-2" @click="edit(wl)"><i class="bi bi-pencil"></i></button>
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

    props: ['employee_id', 'designations'],

    data(){
        return {
            loading: false,
            isDelete: false,
            employee_designation: {
                employee_id: null,
                id: null,
                designation_id: null,
                from: null,
                to: null,
            },
            employee_designations: [],
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

        fetch(){

            let url = '/employee/employee_designation/' + this.employee_id + '/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.employee_designations = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.employee_designations.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.employee_designations = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.employee_designation.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.employee_designation.id = null;
            this.employee_designation.designation_id = null;
            this.employee_designation.from = null;
            this.employee_designation.to = null;
        },

        add(){
            this.loading = true;
            axios.post('/employee/employee_designation/add', this.employee_designation).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/employee/employee_designation/update', this.employee_designation).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_designation/delete', this.employee_designation).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.employee_designation.id = item.id;
            this.employee_designation.designation_id = item.designation_id;
            this.employee_designation.from = item.from;
            this.employee_designation.to = item.to;
        },

        getDesignations(id){
            let key = null;
            this.designations.forEach(loc => {
                if(loc.val == id){
                    key = loc.key;
                }
            });
            return key;
        }

    },

    created(){
        this.fetch();
        this.employee_designation.employee_id = this.employee_id;
    },

}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>