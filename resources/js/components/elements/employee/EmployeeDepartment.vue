<template>
    <div class="container-fluid">

        <section-title title="Add Employee Department" class=""></section-title>

        <div  v-if="employee_department" class="row g-4 mb-5">

            <forms-select-field name="department_id" label="Department" v-model="employee_department.department_id" error="" classes="col-12 col-xl-4" :options="departments"></forms-select-field>

            <forms-date-field name="from" label="From" v-model="employee_department.from" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-date-field name="to" label="To" v-model="employee_department.to" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-submit-button name="" v-model="loading" label="Save Employee Department" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="employee_department.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="employee_department.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <!-- Search -->
        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: ' Employee Department', val: 'employee_department'},{key: 'Code', val: 'code'},]"></forms-select-field>

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
                        <th @click="orderBy('employee_department')" class="cursor-pointer"> Department</th>
                        <th @click="orderBy('employee_department')" class="cursor-pointer">Form</th>
                        <th @click="orderBy('employee_department')" class="cursor-pointer">to</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="wl in employee_departments" :key="wl.id">
                        <td>{{ wl.id }}</td>
                        <td>{{ getDepartment(wl.department_id) }}</td>
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

    props: ['employee_id', 'departments'],

    data(){
        return {
            loading: false,
            isDelete: false,
            employee_department: {
                employee_id: null,
                id: null,
                department_id: null,
                from: null,
                to: null,
            },
            employee_departments: [],
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

            let url = '/employee/employee_department/' + this.employee_id + '/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.employee_departments = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.employee_departments.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.employee_departments = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.employee_department.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.employee_department.id = null;
            this.employee_department.department_id = null;
            this.employee_department.from = null;
            this.employee_department.to = null;
        },

        add(){
            this.loading = true;
            axios.post('/employee/employee_department/add', this.employee_department).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/employee/employee_department/update', this.employee_department).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_department/delete', this.employee_department).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.employee_department.id = item.id;
            this.employee_department.department_id = item.department_id;
            this.employee_department.from = item.from;
            this.employee_department.to = item.to;
        },

        getDepartment(id){
            let key = null;
            this.departments.forEach(loc => {
                if(loc.val == id){
                    key = loc.key;
                }
            });
            return key;
        }

    },

    created(){
        this.fetch();
        this.employee_department.employee_id = this.employee_id;
    },

}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>