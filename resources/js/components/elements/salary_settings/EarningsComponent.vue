<template>
    <div>

        <div class="py-3 px-4 border">
            <span class="m-0 h4 fw-bold">Earning Components</span>
            <span class="float-end">
                <button 
                @click="isForm = !isForm"
                :class="[isForm ? 'btn-danger' : 'btn-outline-primary']"
                class="btn btn-sm">
                    {{ isForm ? 'Close Form' : 'Add Earning Component' }}
                </button>
            </span>
        </div>

        <!-- Earning Form -->
        <div v-if="isForm" class="container-fluid px-4 pt-5 m-0">

            
            <div class="row gx-5 gy-4">

                <div class="col-12 col-xl-6">
                    <div class="row g-4">

                        <forms-select-field v-model="earning.earning_type_id" name="earning_type_id" label="Earning Type" error="" classes="" 
                        :options="earning_types"></forms-select-field>

                        <forms-text-field v-if="earning.earning_type_id == 0" v-model="earning.custom_earning_type" name="custom_earning_type" label="Custom Earning Name" error="" classes=""></forms-text-field>

                        <forms-text-field v-model="earning.name" name="name" label="Earning Name" error="" classes=""></forms-text-field>

                        <forms-text-field v-model="earning.name_in_payslip" name="name_in_payslip" label="Name in Payslip" error="" classes=""></forms-text-field>

                        <forms-radio-field v-model="earning.pay_time" name="pay_time" label="Pay Time" error="" classes="" :options="[
                            {key: 'Every Month (Fixed)', val: 'Fixed'},
                            {key: 'Any Pay Cycle (Variable)', val: 'Variable'},
                        ]"></forms-radio-field>

                        <forms-radio-field v-model="earning.calculation" name="calculation" label="Calculation Type" error="" classes="" :options="[
                            {key: 'Flat Amount', val: 'Flat'},
                            {key: 'Percentage of Basic', val: 'Basic'},
                            {key: 'Percentage of CTC', val: 'CTC'},
                        ]"></forms-radio-field>

                        <forms-number-field
                        v-model="earning.value"
                        v-if="earning.calculation != null && earning.calculation == 'Flat'"
                        name="value" :label="'Amount'" error="" classes=""></forms-number-field>

                        <forms-number-field
                        v-model="earning.value"
                        v-if="earning.calculation != null && earning.calculation != 'Flat'"
                        name="value" :label="'Percentage'" error="" classes=""></forms-number-field>

                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="row g-4">

                        <forms-checkbox-is v-model="earning.is_active" name="is_active" label="Mark this as Active" error="" classes=""></forms-checkbox-is>

                        <forms-checkbox-is v-model="earning.is_part_of_salary" name="is_part_of_salary" label="Make this earning a part of the employee's salary structure" error="" classes=""></forms-checkbox-is>

                        <forms-checkbox-is v-model="earning.is_taxable" name="is_taxable" label="This is a taxable earning" error="" classes=""></forms-checkbox-is>

                        <forms-checkbox-is @change="changedFBP()" v-model="earning.is_fbp" name="is_fbp" label="Include this as a FBP component" error="" classes=""></forms-checkbox-is>

                        <forms-checkbox-is v-if="earning.is_fbp" v-model="earning.is_fbp_restricted" name="is_fbp_restricted" label="Restrict Employee from Overriding the FBP Amount" error="" classes="ps-5"></forms-checkbox-is>

                        <forms-checkbox-is v-model="earning.is_pro_rata" name="is_pro_rata" label="Calculate on pro-rata basis" error="" classes=""></forms-checkbox-is>

                        <forms-checkbox-is v-model="earning.is_epf" name="is_epf" label="Consider for EPF contribution" error="" classes=""></forms-checkbox-is>

                        <forms-checkbox-is v-model="earning.is_esi" name="is_esi" label="Consider for ESIC contribution" error="" classes=""></forms-checkbox-is>

                        <forms-checkbox-is v-model="earning.is_in_payslip" name="is_in_payslip" label="Show this component in payslip" error="" classes=""></forms-checkbox-is>

                    </div>
                </div>

                <forms-submit-button name="" v-model="loading" label="Save Earning" @click="save()" classes="col-6"></forms-submit-button>

                <div class="col-6 text-end">
                    <button v-if="earning.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                    <button v-if="earning.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
                </div>

            </div>

        </div>

        <div class="table-responsive px-4 pt-5">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th @click="orderBy('id')" class="cursor-pointer" style="width: 60px;">ID</th>
                        <th @click="orderBy('earning_type_id')" class="cursor-pointer">Earning Type</th>
                        <th @click="orderBy('name')" class="cursor-pointer">Earning Name</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="earn in earnings" :key="earn.id">
                        <td>{{ earn.id }}</td>
                        <td>{{ earn.earning_type_id }}</td>
                        <td>{{ earn.name }}</td>
                        <td class="text-end">
                            <button class="btn btn-outline-info btn-sm me-2" @click="edit(earn)"><i class="bi bi-pencil"></i></button>
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

    props: ['earning_types'],

    data(){
        return {
            isForm: false,
            isDelete: false,
            loading: false,
            earning: {
                id: null,
                earning_type_id: null,
                custom_earning_type: null,
                name: null,
                name_in_payslip: null,
                calculation: null,
                pay_time: null,
                value: null,
                is_fbp: false,
                is_fbp_restricted: false,
                is_active: false,
                is_part_of_salary: false,
                is_taxable: false,
                is_pro_rata: false,
                is_epf: false,
                is_esi: false,
                is_in_payslip: false,
            },
            earnings: [],
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

        changedFBP(){
            if(this.earning.is_fbp == false){
                this.earning.is_fbp_restricted = false;
            }
        },

        fetch(){

            let url = '/salary_settings/earnings/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.earnings = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.earnings.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.earnings = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.earning.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        add(){
            this.loading = true;
            axios.post('/salary_settings/earnings/add', this.earning).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/salary_settings/earnings/update', this.earning).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/salary_settings/earnings/delete', this.earning).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.earning.id = item.id;
            this.earning.earning_type_id = item.earning_type_id;
            this.earning.custom_earning_type = item.custom_earning_type;
            this.earning.name = item.name;
            this.earning.name_in_payslip = item.name_in_payslip;
            this.earning.calculation = item.calculation;
            this.earning.pay_time = item.pay_time;
            this.earning.value = item.value;
            this.earning.is_fbp = item.is_fbp;
            this.earning.is_fbp_restricted = item.is_fbp_restricted;
            this.earning.is_active = item.is_active;
            this.earning.is_part_of_salary = item.is_part_of_salary;
            this.earning.is_taxable = item.is_taxable;
            this.earning.is_pro_rata = item.is_pro_rata;
            this.earning.is_epf = item.is_epf;
            this.earning.is_esi = item.is_esi;
            this.earning.is_in_payslip = item.is_in_payslip;
        },

        reset(){
            this.earning.id = null;
            this.earning.earning_type_id = null;
            this.earning.custom_earning_type = null;
            this.earning.name = null;
            this.earning.name_in_payslip = null;
            this.earning.calculation = null;
            this.earning.pay_time = null;
            this.earning.value = null;
            this.earning.is_fbp = false;
            this.earning.is_fbp_restricted = null;
            this.earning.is_active = false;
            this.earning.is_part_of_salary = false;
            this.earning.is_taxable = false;
            this.earning.is_pro_rata = false;
            this.earning.is_epf = false;
            this.earning.is_esi = false;
            this.earning.is_in_payslip = false;
        },
    },

    created () {
        this.earning_types.push({
            key: 'Custom Earning Type',
            val: 0
        });

        this.fetch();
    },

}
</script>