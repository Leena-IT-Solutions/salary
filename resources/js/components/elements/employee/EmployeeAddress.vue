<template>
    <div class="container-fluid">

        <section-title title="Add Employee Address" class=""></section-title>

        <div  v-if="employee_address" class="row g-4 mb-5">

            <forms-text-field name="address" label="Address" v-model="employee_address.address" error="" classes=""></forms-text-field>

            <forms-text-field name="city" label="City" v-model="employee_address.city" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="pincode" label="Pincode" v-model="employee_address.pincode" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="state" label="State" v-model="employee_address.state" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="country" label="Country" v-model="employee_address.country" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-submit-button name="" v-model="loading" label="Save Employee Address" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="employee_address.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="employee_address.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: ' Employee Address', val: 'employee_address'},{key: 'Code', val: 'code'},]"></forms-select-field>

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
                        <th @click="orderBy('employee_address')" class="cursor-pointer"> Address</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="address in employee_addresses" :key="address.id">
                        <td>{{ address.id }}</td>
                        <td>{{ address.address }} {{ address.city }} {{ address.pincode }} {{ address.state }} {{ address.country }}</td>
                        <td class="text-end">
                            <button class="btn btn-outline-info btn-sm me-2" @click="edit(address)"><i class="bi bi-pencil"></i></button>
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
            employee_address: {
                employee_id: null,
                id: null,
                address: null,
                city: null,
                pincode: null,
                state: null,
                country: null,
            },
            employee_addresses: [],
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

            let url = '/employee/employee_address/' + this.employee_id + '/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.employee_addresses = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.employee_addresses.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.employee_addresses = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.employee_address.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.employee_address.id = null;
            this.employee_address.address = null;
            this.employee_address.city = null;
            this.employee_address.pincode = null;
            this.employee_address.state = null;
            this.employee_address.country = null;
        },

        add(){
            this.loading = true;
            axios.post('/employee/employee_address/add', this.employee_address).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/employee/employee_address/update', this.employee_address).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_address/delete', this.employee_address).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.employee_address.id = item.id;
            this.employee_address.address = item.address;
            this.employee_address.city = item.city;
            this.employee_address.pincode = item.pincode;
            this.employee_address.state = item.state;
            this.employee_address.country = item.country;
        },

    },

    created(){
        this.fetch();
        this.employee_address.employee_id = this.employee_id;
    },

}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>