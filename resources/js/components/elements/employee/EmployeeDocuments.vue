<template>
    <div class="container-fluid">

        <section-title title="Add Employee Document" class=""></section-title>

        <div  v-if="employee_document" class="row g-4 mb-5">

            <forms-text-field name="document_name" label="Document Name" v-model="employee_document.document_name" error="" classes=""></forms-text-field>

            <forms-file-field @change="getImageObject($event)" v-model="employee_document.photo" name="document" label="Document" error="" classes=""></forms-file-field>

            <forms-submit-button name="" v-model="loading" label="Save Employee Document" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="employee_document.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="employee_document.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <!-- Search -->
        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: ' Employee Document', val: 'employee_document'},{key: 'Code', val: 'code'},]"></forms-select-field>

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
                        <th @click="orderBy('document_name')" class="cursor-pointer">Document</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="wl in employee_documents" :key="wl.id">
                        <td>{{ wl.id }}</td>
                        <td><img :src="wl.document" class="me-3" style="width: 60px;"> {{ wl.document_name }}</td>
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

    props: ['employee_id'],

    data(){
        return {
            loading: false,
            isDelete: false,
            employee_document: {
                employee_id: null,
                id: null,
                photo: null,
                document: {},
                document_name: null,
            },
            employee_documents: [],
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

        getImageObject(e){
            this.employee_document.document = e.target.files[0];
        },

        fetch(){

            let url = '/employee/employee_document/' + this.employee_id + '/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.employee_documents = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.employee_documents.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.employee_documents = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.employee_document.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.employee_document.id = null;
            this.employee_document.document_name = null;
            this.employee_document.document = null;
            this.employee_document.photo = null;
        },

        add(){
            this.loading = true;

            let fd = new FormData();
            fd.append('document_name', this.employee_document.document_name);
            fd.append('document', this.employee_document.document);
            fd.append('employee_id', this.employee_document.employee_id);

            axios.post('/employee/employee_document/add', fd).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;

            let fd = new FormData();
            fd.append('id', this.employee_document.id);
            fd.append('document_name', this.employee_document.document_name);
            fd.append('document', this.employee_document.document);
            fd.append('employee_id', this.employee_document.employee_id);

            axios.post('/employee/employee_document/update', fd).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_document/delete', this.employee_document).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.employee_document.id = item.id;
            this.employee_document.document_name = item.document_name;
        },

    },

    created(){
        this.fetch();
        this.employee_document.employee_id = this.employee_id;
    },

}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>