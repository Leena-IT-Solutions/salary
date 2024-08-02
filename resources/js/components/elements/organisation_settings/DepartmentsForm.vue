<template>
    <div class="container-fluid">

        <section-title title="Add Department" class=""></section-title>

        <div  v-if="department" class="row g-4 mb-5">

            <forms-text-field name="department" label="Department" v-model="department.department" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="code" label="Code" v-model="department.code" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="description" label="Description" v-model="department.description" error="" classes="col-12"></forms-text-field>

            <forms-submit-button name="" v-model="loading" label="Save department" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="department.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="department.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
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
                        <th @click="orderBy('department')" class="cursor-pointer">Department</th>
                        <th @click="orderBy('code')" class="cursor-pointer">Code</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="dept in departments" :key="dept.id">
                        <td>{{ dept.id }}</td>
                        <td>{{ dept.department }}</td>
                        <td>{{ dept.code }}</td>
                        <td class="text-end">
                            <button class="btn btn-outline-info btn-sm me-2" @click="edit(dept)"><i class="bi bi-pencil"></i></button>
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
            department: {
                id: null,
                department: null,
                code: null,
                description: null,
            },
            departments: [],
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

            let url = '/organisation_settings/departments/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.departments = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.departments.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.departments = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.department.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.department.id = null;
            this.department.department = null;
            this.department.code = null;
            this.department.description = null;
        },

        add(){
            this.loading = true;
            axios.post('/organisation_settings/departments/add', this.department).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/organisation_settings/departments/update', this.department).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/organisation_settings/departments/delete', this.department).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.department.id = item.id;
            this.department.department = item.department;
            this.department.code = item.code;
            this.department.description = item.description;
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