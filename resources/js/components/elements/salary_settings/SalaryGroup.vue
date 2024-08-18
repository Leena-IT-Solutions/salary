<template>
    <div>

        <!-- Header -->
        <div class="py-3 px-4 border">
            <span class="m-0 h4 fw-bold">Salary Group</span>
            <span class="float-end">
                <button 
                @click="isForm = !isForm"
                :class="[isForm ? 'btn-danger' : 'btn-outline-primary']"
                class="btn btn-sm">
                    {{ isForm ? 'Close Form' : 'Add Salary Group' }}
                </button>
            </span>
        </div>

        <!-- Form -->
        <div v-if="isForm" class="container-fluid px-4 pt-5 m-0 mb-4">

            <div class="row gx-5 gy-4">

                <div class="col-12 col-xl-6">
                    <div class="row g-4">

                        <forms-text-field v-model="item.salary_group_name" name="salary_group_name" label="Salary Group Name" error="" classes=""></forms-text-field>

                        <forms-text-field v-model="item.multiplier" name="multiplier" label="Multiplier for calculating Overtime" error="" classes=""></forms-text-field>

                        <forms-text-field v-model="item.note" name="note" label="Note" error="" classes=""></forms-text-field>

                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="row g-4">

                        <forms-checkbox-is v-model="item.is_active" name="is_active" label="Mark this as Active" error="" classes=""></forms-checkbox-is>

                    </div>
                </div>

                <forms-submit-button name="" v-model="loading" label="Save Statutory Scheme" @click="save()" classes="col-6"></forms-submit-button>

                <div class="col-6 text-end">
                    <button v-if="item.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                    <button v-if="item.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
                </div>

            </div>

        </div>

        <!-- Search -->
        <div class="row p-4">
            
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
        <div class="table-responsive px-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th @click="orderBy('id')" class="cursor-pointer" style="width: 60px;">ID</th>
                        <th @click="orderBy('salary_group_name')" class="cursor-pointer">Salary Group Name</th>
                        <th @click="orderBy('is_active')" class="cursor-pointer">Status</th>
                        <th @click="orderBy('note')" class="cursor-pointer">Note</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td><a :href="'/salary_settings/salary_groupable/'+ row.id +'/data/'">{{ row.salary_group_name }}</a></td>
                        <td>{{ row.is_active ? 'Enabled' : 'Disabled' }}</td>
                        <td>{{ row.note }}</td>
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
            isForm: false,
            isDelete: false,
            loading: false,
            item: {
                id: null,
                salary_group_name: null,
                note: null,
                multiplier: null,
                is_active: null,
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
            this.item.salary_group_name = null;
            this.item.note = null;
            this.item.multiplier = null;
            this.item.is_active = null;
        },

        edit(item){
            this.item.id = item.id;
            this.item.salary_group_name = item.salary_group_name;
            this.item.note = item.note;
            this.item.multiplier = item.multiplier;
            this.item.is_active = item.is_active;
            this.isForm = true;
        },

        fetch(){

            let url = '/salary_settings/salary_group/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.items = res.data.data;
                } else {
                    res.data.data.forEach(obj => {
                        this.items.push(obj);
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
            axios.post('/salary_settings/salary_group/add', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/salary_settings/salary_group/update', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/salary_settings/salary_group/delete', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

    },

    created () {
        this.fetch();
    },

}
</script>