<template>
    <div class="container-fluid">

        <div  v-if="item" class="row g-4 mb-5">

            <forms-text-field name="fy_name" label="Financial Year Name" v-model="item.fy_name" error="" classes="col-12"></forms-text-field>

            <forms-date-field name="from" label="From" v-model="item.from" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-date-field name="to" label="To" v-model="item.to" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-select-field name="is_current_year" label="Set this as a current year" v-model="item.is_current_year" error="" classes="col-12 col-lg-4" 
            :options="[{ key: 'Yes', val: 'Yes' }, { key: 'No', val: 'No' }]"></forms-select-field>

            <forms-submit-button name="" v-model="loading" label="Save Financial Year" @click="save()" classes="col-6"></forms-submit-button>

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
                        <th @click="orderBy('fy_name')" class="cursor-pointer">Financial Year</th>
                        <th @click="orderBy('from')" class="cursor-pointer">From</th>
                        <th @click="orderBy('to')" class="cursor-pointer">To</th>
                        <th @click="orderBy('is_current_year')" class="cursor-pointer">Current Year</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.fy_name }}</td>
                        <td>{{ row.from }}</td>
                        <td>{{ row.to }}</td>
                        <td>{{ row.is_current_year }}</td>
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
                fy_name: null,
                from: null,
                to: null,
                is_current_year: null,
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
            this.item.fy_name = null;
            this.item.from = null;
            this.item.to = null;
            this.item.is_current_year = null;
        },

        edit(item){
            this.item.id = item.id;
            this.item.fy_name = item.fy_name;
            this.item.from = item.from;
            this.item.to = item.to;
            this.item.is_current_year = item.is_current_year;
        },

        fetch(){

            let url = '/application_settings/financial_year/fetch';
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
            axios.post('/application_settings/financial_year/add', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/application_settings/financial_year/update', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/application_settings/financial_year/delete', this.item).then(res => {
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