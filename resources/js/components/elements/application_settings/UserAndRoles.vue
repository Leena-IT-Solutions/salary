<template>
    <div class="container-fluid">

        <section-title title="Add Department" class=""></section-title>

        <!-- Form -->
        <div  v-if="item" class="row g-4 mb-5">

            <forms-text-field name="name" label="Name" v-model="item.name" error="" classes="col-12 col-lg-4"></forms-text-field>

            <forms-text-field name="email" label="Email" v-model="item.email" error="" classes="col-12 col-lg-4"></forms-text-field>

            <forms-text-field name="username" label="Username" v-model="item.username" error="" classes="col-12 col-lg-4"></forms-text-field>

            <forms-select-field name="role" label="Role" v-model="item.role" error="" classes="col-12 col-lg-6" 
            :options="[{ key: 'Administrator', val: 'Administrator' }, { key: 'Time Office', val: 'Time Office' }, { key: 'Employee', val: 'Employee' }]"></forms-select-field>

            <forms-select-field name="status" label="Status" v-model="item.status" error="" classes="col-12 col-lg-6" 
            :options="[{ key: 'Active', val: 'Active' }, { key: 'Inactive', val: 'Inactive' }]"></forms-select-field>

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
                        <th @click="orderBy('name')" class="cursor-pointer">Name</th>
                        <th @click="orderBy('email')" class="cursor-pointer">Email</th>
                        <th @click="orderBy('username')" class="cursor-pointer">Username</th>
                        <th @click="orderBy('role')" class="cursor-pointer">Role</th>
                        <th @click="orderBy('status')" class="cursor-pointer">Status</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.name }}</td>
                        <td>{{ row.email }}</td>
                        <td>{{ row.username }}</td>
                        <td>{{ row.role }}</td>
                        <td>{{ row.status }}</td>
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
                name: null,
                email: null,
                username: null,
                role: null,
                status: null,
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
            this.item.name = null;
            this.item.email = null;
            this.item.username = null;
            this.item.role = null;
            this.item.status = null;
        },

        edit(item){
            this.item.id = item.id;
            this.item.name = item.name;
            this.item.email = item.email;
            this.item.username = item.username;
            this.item.role = item.role;
            this.item.status = item.status;
        },

        fetch(){

            let url = '/application_settings/user_and_roles/fetch';
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
            axios.post('/application_settings/user_and_roles/add', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/application_settings/user_and_roles/update', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/application_settings/user_and_roles/delete', this.item).then(res => {
                this.reset();
                this.search();
            });
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