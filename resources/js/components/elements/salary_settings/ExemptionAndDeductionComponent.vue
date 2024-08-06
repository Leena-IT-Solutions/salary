<template>
    <div>

        <!-- Header -->
        <div class="py-3 px-4 border">
            <span class="m-0 h4 fw-bold">Exemption And Deduction Components</span>
            <span class="float-end">
                <button 
                @click="isForm = !isForm"
                :class="[isForm ? 'btn-danger' : 'btn-outline-primary']"
                class="btn btn-sm">
                    {{ isForm ? 'Close Form' : 'Add Exemption And Deduction Component' }}
                </button>
            </span>
        </div>

        <!-- Form -->
        <div v-if="isForm" class="container-fluid px-4 pt-5 m-0 mb-4">

            <div class="row gx-5 gy-4">

                <div class="col-12 col-xl-6">
                    <div class="row g-4">

                        <forms-select-field v-model="item.exe_and_ded_type_id" name="exe_and_ded_type_id" label="Exemption and Deduction Type" error="" classes="" 
                        :options="types"></forms-select-field>

                        <forms-text-field v-if="item.exe_and_ded_type_id == 0" v-model="item.custom_type" name="custom_type" label="Custom Exemption and Deduction Name" error="" classes=""></forms-text-field>

                        <forms-text-field v-model="item.name" name="name" label="Exemption and Deduction Name" error="" classes=""></forms-text-field>

                        <forms-text-field v-model="item.name_in_payslip" name="name_in_payslip" label="Name in Payslip" error="" classes=""></forms-text-field>

                        <forms-radio-field v-model="item.calculation" name="calculation" label="Calculation Type" error="" classes="" :options="[
                            {key: 'Flat Amount', val: 'Flat'},
                            {key: 'Percentage of Basic', val: 'Basic'},
                            {key: 'Percentage of CTC', val: 'CTC'},
                        ]"></forms-radio-field>

                        <forms-number-field
                        v-model="item.value"
                        v-if="item.calculation != null && item.calculation == 'Flat'"
                        name="value" :label="'Amount'" error="" classes=""></forms-number-field>

                        <forms-number-field
                        v-model="item.value"
                        v-if="item.calculation != null && item.calculation != 'Flat'"
                        name="value" :label="'Percentage'" error="" classes=""></forms-number-field>

                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="row g-4">

                        <forms-checkbox-is v-model="item.is_active" name="is_active" label="Mark this as Active" error="" classes=""></forms-checkbox-is>

                    </div>
                </div>

                <forms-submit-button name="" v-model="loading" label="Save Exemption And Deduction" @click="save()" classes="col-6"></forms-submit-button>

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
                        <th @click="orderBy('name')" class="cursor-pointer">Service Name</th>
                        <th @click="orderBy('calculation')" class="cursor-pointer">Calculation</th>
                        <th @click="orderBy('value')" class="cursor-pointer">Value</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.name }}</td>
                        <td>{{ row.calculation }}</td>
                        <td>{{ row.value }}{{ row.calculation == "Flat" ? '/-' : '%' }}</td>
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

    props: ['types'],

    data(){
        return {
            isForm: false,
            isDelete: false,
            loading: false,
            item: {
                id: null,
                exe_and_ded_type_id: null,
                custom_type: null,
                name: null,
                name_in_payslip: null,
                calculation: null,
                value: null,
                is_active: false,
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
            this.item.exe_and_ded_type_id = null;
            this.item.custom_type = null;
            this.item.name = null;
            this.item.name_in_payslip = null;
            this.item.calculation = null;
            this.item.value = null;
            this.item.is_active = false;
        },

        edit(item){
            this.item.id = item.id;
            this.item.exe_and_ded_type_id = item.exe_and_ded_type_id;
            this.item.custom_type = item.custom_type;
            this.item.name = item.name;
            this.item.name_in_payslip = item.name_in_payslip;
            this.item.calculation = item.calculation;
            this.item.value = item.value;
            this.item.is_active = item.is_active;
            this.isForm = true;
        },

        fetch(){

            let url = '/salary_settings/exemption_and_deduction/fetch';
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
            axios.post('/salary_settings/exemption_and_deduction/add', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/salary_settings/exemption_and_deduction/update', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/salary_settings/exemption_and_deduction/delete', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

    },

    created () {
        this.types.push({
            key: 'Custom Earning Type',
            val: 0
        });

        this.fetch();
    },

}
</script>