<template>
    <div class="container-fluid">

        <section-title title="Add Leave Type" class=""></section-title>

        <div  v-if="leave_type" class="row g-4 mb-5">

            <forms-text-field name="leave_type" label="Leave Type" v-model="leave_type.leave_type" error="" classes="col-12 col-lg-4"></forms-text-field>

            <forms-text-field name="code" label="Code" v-model="leave_type.code" error="" classes="col-12 col-lg-4"></forms-text-field>

            <forms-select-field name="is_lop" label="Is loss of pay?" v-model="leave_type.is_lop" error="" classes="col-12 col-lg-4" 
            :options="[{ key: 'Yes', val: 'Yes' }, { key: 'No', val: 'No' }]"></forms-select-field>

            <forms-submit-button name="" v-model="loading" label="Save Leave Type" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="leave_type.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="leave_type.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: 'Leave Type', val: 'leave_type'},{key: 'Code', val: 'code'},]"></forms-select-field>

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
                        <th @click="orderBy('leave_type')" class="cursor-pointer">Leave Type</th>
                        <th @click="orderBy('code')" class="cursor-pointer">Code</th>
                        <th @click="orderBy('code')" class="cursor-pointer">LOP</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="dept in leave_types" :key="dept.id">
                        <td>{{ dept.id }}</td>
                        <td>{{ dept.leave_type }}</td>
                        <td>{{ dept.code }}</td>
                        <td>{{ dept.is_lop }}</td>
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
            leave_type: {
                id: null,
                head: null,
                code: null,
                is_lop: null,
            },
            leave_types: [],
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

            let url = '/organisation_settings/leaves_setup/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.leave_types = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.leave_types.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.leave_types = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.leave_type.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.leave_type.id = null;
            this.leave_type.leave_type = null;
            this.leave_type.code = null;
            this.leave_type.is_lop = null;
        },

        add(){
            this.loading = true;
            axios.post('/organisation_settings/leaves_setup/add', this.leave_type).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/organisation_settings/leaves_setup/update', this.leave_type).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/organisation_settings/leaves_setup/delete', this.leave_type).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.leave_type.id = item.id;
            this.leave_type.leave_type = item.leave_type;
            this.leave_type.code = item.code;
            this.leave_type.is_lop = item.is_lop;
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