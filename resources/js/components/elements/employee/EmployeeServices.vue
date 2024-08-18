<template>
    <div class="container-fluid">

        <section-title title="Add Department" class=""></section-title>

        <!-- Form -->
        <div  v-if="item" class="row g-4 mb-5">

            <forms-text-field name="employee_id" label="Employee ID" v-model="item.employee_id" error="" classes="col-12"></forms-text-field>

            <forms-select-field name="services_component_id" label="Services" v-model="item.services_component_id" error="" classes="col-12 col-xl-4" :options="services"></forms-select-field>

            <forms-date-field name="from" label="From" v-model="item.from" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-date-field name="to" label="To" v-model="item.to" error="" classes="col-12 col-lg-4"></forms-date-field>

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
                        <th @click="orderBy('name')" class="cursor-pointer">Service</th>
                        <th @click="orderBy('from')" class="cursor-pointer">From</th>
                        <th @click="orderBy('to')" class="cursor-pointer">To</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.services_component.name }}</td>
                        <td>{{ row.from }}</td>
                        <td>{{ row.to }}</td>
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

    props: ['employee_id', 'services'],

    data(){
        return {
            loading: false,
            isDelete: false,
            item: {
                id: null,
                employee_id: null,
                services_component_id: null,
                from: null,
                to: null,
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
            this.item.services_component_id = null;
            this.item.from = null;
            this.item.account_type = null;
        },

        edit(item){
            this.item.id = item.id;
            this.item.employee_id = item.employee_id;
            this.item.services_component_id = item.services_component_id;
            this.item.from = item.from;
            this.item.account_type = item.account_type;
        },

        fetch(){

            let url = '/employee/employee_services/'+this.item.employee_id+'/fetch/';
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
            axios.post('/employee/employee_services/add', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/employee/employee_services/update', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_services/delete', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

    },

    created(){
        this.item.employee_id = this.employee_id;
        this.fetch();
        console.log(this.services);
    },

}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>