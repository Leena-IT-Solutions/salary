<template>
    <div>

        <!-- Header -->
        <div class="py-3 px-4 border">
            <span class="m-0 h4 fw-bold">Statutory Compliance Conditions</span>
            <span class="float-end">
                <button 
                @click="isForm = !isForm"
                :class="[isForm ? 'btn-danger' : 'btn-outline-primary']"
                class="btn btn-sm">
                    {{ isForm ? 'Close Form' : 'Add Statutory Compliance Conditions' }}
                </button>
            </span>
        </div>

        <!-- Scheme Details -->
        <div class="container-fluid px-4 mt-5">
            <div class="shadow rounded p-4">
                <h3 class="m-0">{{ statutory_compliance.scheme_name }} - {{ statutory_compliance.abbreviation }} <span class="float-end">{{ statutory_compliance.registration_number }}</span></h3>
            </div>
        </div>

        <!-- Form -->
        <div v-if="isForm" class="container-fluid px-4 pt-5 m-0 mb-4">

                <div class="row g-4">

                    <forms-checkbox-is v-model="item.is_active" name="is_active" label="Mark this as Active" error="" classes=""></forms-checkbox-is>

                    <forms-select-field name="state" label="State" v-model="item.state" error="" classes="col-12 col-lg-4" :options="state"></forms-select-field>

                    <forms-select-field name="gender" label="Gender" v-model="item.gender" error="" classes="col-12 col-lg-4" :options="gender"></forms-select-field>

                    <forms-select-field name="salary_type" label="Salary Type" v-model="item.salary_type" error="" classes="col-12 col-lg-4" :options="salary_type"></forms-select-field>

                    <forms-select-field name="calculation" label="Calculation Type" v-model="item.calculation" error="" classes="col-12" :options="calculation"></forms-select-field>
                    
                    <forms-number-field v-model="item.min_salary" name="min_salary" label="Minimum Salary" error="" classes="col-12 col-lg-4"></forms-number-field>

                    <forms-number-field v-model="item.max_salary" name="max_salary" label="Maximum Salary" error="" classes="col-12 col-lg-4"></forms-number-field>

                    <forms-number-field v-model="item.restrict_salary_for_calculation" name="restrict_salary_for_calculation" label="Restricted Amount Salary for Calculation" error="" classes="col-12 col-lg-4"></forms-number-field>

                    <forms-number-field v-model="item.employee_contribution" name="employee_contribution" label="Employee Contribution" error="" classes="col-12 col-lg-6"></forms-number-field>

                    <forms-number-field v-model="item.max_employee_contribution" name="max_employee_contribution" label="Maximum Employee Contribution Amount" error="" classes="col-12 col-lg-6"></forms-number-field>

                    <forms-number-field v-model="item.employer_contribution" name="employer_contribution" label="Employer Contribution" error="" classes="col-12 col-lg-6"></forms-number-field>

                    <forms-number-field v-model="item.max_employer_contribution" name="max_employer_contribution" label="Maximum Employer Contribution Amount" error="" classes="col-12 col-lg-6"></forms-number-field>

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
                        <th @click="orderBy('is_active')" class="cursor-pointer">Status</th>
                        <th @click="orderBy('state')" class="cursor-pointer">State</th>
                        <th @click="orderBy('gender')" class="cursor-pointer">Gender</th>
                        <th @click="orderBy('salary_type')" class="cursor-pointer">Salary Type</th>
                        <th @click="orderBy('calculation')" class="cursor-pointer">Calculation</th>
                        <th @click="orderBy('employee_contribution')" class="cursor-pointer">Employee</th>
                        <th @click="orderBy('employer_contribution')" class="cursor-pointer">Employer</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.is_active ? 'Enabled' : 'Disabled' }}</td>
                        <td>{{ row.state }}</td>
                        <td>{{ row.gender }}</td>
                        <td>{{ row.salary_type }}</td>
                        <td>{{ row.calculation }}</td>
                        <td>{{ row.employee_contribution }}{{ row.calculation == "Percentage" ? '%' : row.calculation == "Flat" ? '/-' : '' }}</td>
                        <td>{{ row.employer_contribution }}{{ row.calculation == "Percentage" ? '%' : row.calculation == "Flat" ? '/-' : '' }}</td>
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

    props: ['statutory_compliance'],

    data(){
        return {
            isForm: false,
            isDelete: false,
            loading: false,
            item: {
                id: null,
                statutory_compliance_id: this.statutory_compliance.id,
                gender: null,
                salary_type: null,
                calculation: null,
                min_salary: null,
                max_salary: null,
                restrict_salary_for_calculation: null,
                employee_contribution: null,
                max_employee_contribution: null,
                employer_contribution: null,
                max_employer_contribution: null,
                is_active: null,
                state: null,
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
            },
            gender: [
                {key: 'All', val: 'All'},
                {key: 'Male', val: 'Male'},
                {key: 'Female', val: 'Female'},
                {key: 'Other', val: 'Other'},
            ],
            salary_type: [
                {key: 'Basic Pay', val: 'Basic Pay'},
                {key: 'Gross Pay', val: 'Gross Pay'},
                {key: 'CTC', val: 'CTC'},
                {key: 'None', val: 'None'},
            ],
            calculation: [
                {key: 'Flat', val: 'Flat'},
                {key: 'Percentage', val: 'Percentage'},
                {key: 'CSV', val: 'CSV'},
            ],
            state: [
                {key: 'All', val: 'All'},
                {key: 'Andhra Pradesh', val: 'Andhra Pradesh'},
                {key: 'Arunachal Pradesh', val: 'Arunachal Pradesh'},
                {key: 'Assam', val: 'Assam'},
                {key: 'Bihar', val: 'Bihar'},
                {key: 'Chhattisgarh', val: 'Chhattisgarh'},
                {key: 'Goa', val: 'Goa'},
                {key: 'Gujarat', val: 'Gujarat'},
                {key: 'Haryana', val: 'Haryana'},
                {key: 'Himachal Pradesh', val: 'Himachal Pradesh'},
                {key: 'Jharkhand', val: 'Jharkhand'},
                {key: 'Karnataka', val: 'Karnataka'},
                {key: 'Kerala', val: 'Kerala'},
                {key: 'Maharashtra', val: 'Maharashtra'},
                {key: 'Madhya Pradesh', val: 'Madhya Pradesh'},
                {key: 'Manipur', val: 'Manipur'},
                {key: 'Meghalaya', val: 'Meghalaya'},
                {key: 'Mizoram', val: 'Mizoram'},
                {key: 'Nagaland', val: 'Nagaland'},
                {key: 'Odisha', val: 'Odisha'},
                {key: 'Punjab', val: 'Punjab'},
                {key: 'Rajasthan', val: 'Rajasthan'},
                {key: 'Sikkim', val: 'Sikkim'},
                {key: 'Tamil Nadu', val: 'Tamil Nadu'},
                {key: 'Tripura', val: 'Tripura'},
                {key: 'Telangana', val: 'Telangana'},
                {key: 'Uttar Pradesh', val: 'Uttar Pradesh'},
                {key: 'Uttarakhand', val: 'Uttarakhand'},
                {key: 'West Bengal', val: 'West Bengal'},
                {key: 'Andaman & Nicobar', val: 'Andaman & Nicobar'},
                {key: 'Chandigarh', val: 'Chandigarh'},
                {key: 'Dadra & Nagar Haveli', val: 'Dadra & Nagar Haveli'},
                {key: 'Daman & Diu', val: 'Daman & Diu'},
                {key: 'Delhi', val: 'Delhi'},
                {key: 'Jammu & Kashmir', val: 'Jammu & Kashmir'},
                {key: 'Ladakh', val: 'Ladakh'},
                {key: 'Lakshadweep', val: 'Lakshadweep'},
                {key: 'Puducherry', val: 'Puducherry'},
            ],
        };
    },

    methods: {

        reset(){
            this.item.id = null;
            this.item.statutory_compliance_id = this.statutory_compliance.id;
            this.item.gender = null;
            this.item.salary_type = null;
            this.item.calculation = null;
            this.item.min_salary = null;
            this.item.max_salary = null;
            this.item.restrict_salary_for_calculation = null;
            this.item.employee_contribution = null;
            this.item.max_employee_contribution = null;
            this.item.employer_contribution = null;
            this.item.max_employer_contribution = null;
            this.item.is_active = null;
            this.item.state = null;
        },

        edit(item){
            this.item.id = item.id;
            this.item.statutory_compliance_id = item.statutory_compliance_id;
            this.item.gender = item.gender;
            this.item.salary_type = item.salary_type;
            this.item.calculation = item.calculation;
            this.item.min_salary = item.min_salary;
            this.item.max_salary = item.max_salary;
            this.item.restrict_salary_for_calculation = item.restrict_salary_for_calculation;
            this.item.employee_contribution = item.employee_contribution;
            this.item.max_employee_contribution = item.max_employee_contribution;
            this.item.employer_contribution = item.employer_contribution;
            this.item.max_employer_contribution = item.max_employer_contribution;
            this.item.is_active = item.is_active;
            this.item.state = item.state;
            this.isForm = true;
        },

        fetch(){

            let url = '/salary_settings/statutory_compliance/'+this.statutory_compliance.id+'/condition/fetch';
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
            axios.post('/salary_settings/statutory_compliance/'+this.statutory_compliance.id+'/condition/add', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/salary_settings/statutory_compliance/'+this.statutory_compliance.id+'/condition/update', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/salary_settings/statutory_compliance/'+this.statutory_compliance.id+'/condition/delete', this.item).then(res => {
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