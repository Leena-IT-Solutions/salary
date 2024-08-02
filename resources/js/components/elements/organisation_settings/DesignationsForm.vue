<template>
    <div class="container-fluid">

        <section-title title="Add Designation" class=""></section-title>

        <div  v-if="designation" class="row g-4 mb-5">

            <forms-text-field name="designation" label="Designation" v-model="designation.designation" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="code" label="Code" v-model="designation.code" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="description" label="Description" v-model="designation.description" error="" classes="col-12"></forms-text-field>

            <forms-submit-button name="" v-model="loading" label="Save designation" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="designation.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="designation.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: 'Designation', val: 'designation'},{key: 'Code', val: 'code'},]"></forms-select-field>

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
                        <th @click="orderBy('designation')" class="cursor-pointer">Designation</th>
                        <th @click="orderBy('code')" class="cursor-pointer">Code</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="dept in designations" :key="dept.id">
                        <td>{{ dept.id }}</td>
                        <td>{{ dept.designation }}</td>
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
            designation: {
                id: null,
                designation: null,
                code: null,
                description: null,
            },
            designations: [],
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

            let url = '/organisation_settings/designations/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.designations = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.designations.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.designations = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.designation.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.designation.id = null;
            this.designation.designation = null;
            this.designation.code = null;
            this.designation.description = null;
        },

        add(){
            this.loading = true;
            axios.post('/organisation_settings/designations/add', this.designation).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/organisation_settings/designations/update', this.designation).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/organisation_settings/designations/delete', this.designation).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.designation.id = item.id;
            this.designation.designation = item.designation;
            this.designation.code = item.code;
            this.designation.description = item.description;
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