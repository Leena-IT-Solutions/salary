<template>
    <div>
        <div class="mb-5 clearfix">
            <button class="btn float-end" :class="isForm ? 'btn-danger' : 'btn-outline-primary'" @click="isForm = !isForm">{{ isForm ? 'Close' : 'Add Employee' }}</button>
        </div>
        <!-- Employee Form -->
        <div class="row g-4 mb-5" v-if="isForm">

            <forms-text-field name="first_name" label="First Name" v-model="employee.first_name" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-text-field name="middle_name" label="Middle Name" v-model="employee.middle_name" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-text-field name="last_name" label="Last Name" v-model="employee.last_name" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-text-field name="employee_code" label="Employee Code" v-model="employee.employee_code" error="" classes="col-12 col-xl-6"></forms-text-field>

            <forms-text-field name="tagid" label="RFID Tag ID" v-model="employee.tagid" error="" classes="col-12 col-xl-6"></forms-text-field>

            <forms-text-field name="email" label="Email" v-model="employee.email" error="" classes="col-12 col-xl-6"></forms-text-field>

            <forms-text-field name="phone" label="Phone Number" v-model="employee.phone" error="" classes="col-12 col-xl-6"></forms-text-field>

            <forms-date-field name="doj" label="Date of Joining" v-model="employee.doj" error="" classes="col-12 col-xl-4"></forms-date-field>

            <forms-date-field name="doe" label="Exit Date" v-model="employee.doe" error="" classes="col-12 col-xl-4"></forms-date-field>

            <forms-date-field name="dob" label="Date of Birth" v-model="employee.dob" error="" classes="col-12 col-xl-4"></forms-date-field>

            <forms-select-field name="gender" label="Gender" v-model="employee.gender" error="" classes="col-12 col-xl-4" :options="[
                {key: 'Male', val: 'Male'},
                {key: 'Female', val: 'Female'},
                {key: 'Other', val: 'Other'},
            ]"></forms-select-field>

            <forms-select-field name="blood_group" label="Blood Group" v-model="employee.blood_group" error="" classes="col-12 col-xl-4" :options="[
                {key: 'O +ve', val: 'O +ve'},
                {key: 'O -ve', val: 'O -ve'},
                {key: 'A +ve', val: 'A +ve'},
                {key: 'A -ve', val: 'A -ve'},
                {key: 'B +ve', val: 'B +ve'},
                {key: 'B -ve', val: 'B -ve'},
                {key: 'AB +ve', val: 'AB +ve'},
                {key: 'AB -ve', val: 'AB -ve'},
                {key: 'HH', val: 'HH'},
                {key: 'Other', val: 'Other'},
            ]"></forms-select-field>

            <forms-text-field name="mothertongue" label="Mother Tongue" v-model="employee.mothertongue" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-select-field name="religion" label="Religion" v-model="employee.religion" error="" classes="col-12 col-xl-4" :options="[
                {key: 'Hindu', val: 'Hindu'},
                {key: 'Muslim', val: 'Muslim'},
                {key: 'Christian', val: 'Christian'},
                {key: 'Sikh', val: 'Sikh'},
                {key: 'Buddhist', val: 'Buddhist'},
                {key: 'Jain', val: 'Jain'},
                {key: 'Atheist', val: 'Atheist'},
                {key: 'Other', val: 'Other'},
            ]"></forms-select-field>

            <forms-text-field name="cast" label="Cast" v-model="employee.cast" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-text-field name="subcast" label="Subcast" v-model="employee.subcast" error="" classes="col-12 col-xl-4"></forms-text-field>

            <forms-text-field name="nationality" label="Nationality" v-model="employee.nationality" error="" classes="col-12 col-xl-3"></forms-text-field>

            <forms-select-field name="marital_status" label="Marital Status" v-model="employee.marital_status" error="" classes="col-12 col-xl-3" :options="[
                {key: 'Married', val: 'Married'},
                {key: 'Widowed', val: 'Widowed'},
                {key: 'Separated', val: 'Separated'},
                {key: 'Divorced', val: 'Divorced'},
                {key: 'Single', val: 'Single'},
                {key: 'Other', val: 'Other'},
            ]"></forms-select-field>

            <forms-select-field name="qualification" label="Qualification" v-model="employee.qualification" error="" classes="col-12 col-xl-3" :options="[
                {key: 'Primary School', val: 'Primary School'},
                {key: 'Secondary School', val: 'Secondary School'},
                {key: 'High School', val: 'High School'},
                {key: 'Undergraduate', val: 'Undergraduate'},
                {key: 'Graduate', val: 'Graduate'},
                {key: 'Diploma', val: 'Diploma'},
                {key: 'Masters', val: 'Masters'},
                {key: 'Doctorate', val: 'Doctorate'},
                {key: 'Other', val: 'Other'},
            ]"></forms-select-field>

            <forms-text-field name="degree" label="Degree" v-model="employee.degree" error="" classes="col-12 col-xl-3"></forms-text-field>

            <forms-text-field name="aadhar" label="Aadhar" v-model="employee.aadhar" error="" classes="col-12 col-xl-3"></forms-text-field>

            <forms-text-field name="pan" label="PAN" v-model="employee.pan" error="" classes="col-12 col-xl-3"></forms-text-field>

            <forms-text-field name="pf" label="PF Number" v-model="employee.pf" error="" classes="col-12 col-xl-3"></forms-text-field>

            <forms-text-field name="uan" label="UAN" v-model="employee.uan" error="" classes="col-12 col-xl-3"></forms-text-field>

            <forms-text-field name="esic" label="ESIC" v-model="employee.esic" error="" classes="col-12"></forms-text-field>

            <forms-submit-button name="" v-model="loading" label="Save employee" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="employee.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="employee.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <!-- Search -->
        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: 'Phone Number', val: 'phone'},{key: 'Email Address', val: 'email'},]"></forms-select-field>

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
                        <th @click="orderBy('first_name')" class="cursor-pointer">Name</th>
                        <th @click="orderBy('employee_code')" class="cursor-pointer">Employee Code</th>
                        <th class="cursor-pointer">Department</th>
                        <th class="cursor-pointer">Designation</th>
                        <th @click="orderBy('phone')" class="cursor-pointer">Phone</th>
                        <th @click="orderBy('email')" class="cursor-pointer">Email</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="emp in employees" :key="emp.id">
                        <td>{{ emp.id }}</td>
                        <td class="text-nowrap">{{ emp.first_name }} {{ emp.middle_name }} {{ emp.last_name }}</td>
                        <td class="text-nowrap">{{ emp.employee_code }}</td>
                        <td class="text-nowrap"><span v-if="emp.employee_department">{{ emp.employee_department.department.department }}</span></td>
                        <td class="text-nowrap"><span v-if="emp.employee_designation">{{ emp.employee_designation.designation.designation }}</span></td>
                        <td class="text-nowrap">{{ emp.phone }}</td>
                        <td class="text-nowrap">{{ emp.email }}</td>
                        <td class="text-end">
                            <button class="btn btn-outline-info btn-sm me-2" @click="edit(emp)"><i class="bi bi-pencil"></i></button>
                            <a class="btn btn-primary btn-sm me-2" :href="'/employee/profile/'+emp.id"><i class="bi bi-person"></i></a>
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
import axios from 'axios'
export default {

    data(){
        return {
            isForm: false,
            loading: false,
            isDelete: false,
            employee: {
                id: null,
                first_name: null,
                middle_name: null,
                last_name: null,
                employee_code: null,
                tagid: null,
                email: null,
                phone: null,
                doj: null,
                doe: null,
                dob: null,
                gender: null,
                blood_group: null,
                religion: null,
                cast: null,
                subcast: null,
                mothertongue: null,
                nationality: null,
                marital_status: null,
                qualification: null,
                degree: null,
                aadhar: null,
                pan: null,
                pf: null,
                uan: null,
                esic: null,
            },
            employees: [],
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

            let url = '/employee/employee_manager/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.employees = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.employees.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.employees = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.employee.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.employee.id = null;
            this.employee.first_name = null;
            this.employee.middle_name = null;
            this.employee.last_name = null;
            this.employee.employee_code = null;
            this.employee.tagid = null;
            this.employee.email = null;
            this.employee.phone = null;
            this.employee.doj = null;
            this.employee.doe = null;
            this.employee.dob = null;
            this.employee.gender = null;
            this.employee.blood_group = null;
            this.employee.religion = null;
            this.employee.cast = null;
            this.employee.subcast = null;
            this.employee.mothertongue = null;
            this.employee.nationality = null;
            this.employee.marital_status = null;
            this.employee.qualification = null;
            this.employee.degree = null;
            this.employee.aadhar = null;
            this.employee.pan = null;
            this.employee.pf = null;
            this.employee.uan = null;
            this.employee.esic = null;
        },

        add(){
            this.loading = true;
            axios.post('/employee/employee_manager/add', this.employee).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/employee/employee_manager/update', this.employee).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_manager/delete', this.employee).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.isForm = true;
            this.employee.id = item.id;
            this.employee.first_name = item.first_name;
            this.employee.middle_name = item.middle_name;
            this.employee.last_name = item.last_name;
            this.employee.employee_code = item.employee_code;
            this.employee.tagid = item.tagid;
            this.employee.email = item.email;
            this.employee.phone = item.phone;
            this.employee.doj = item.doj;
            this.employee.doe = item.doe;
            this.employee.dob = item.dob;
            this.employee.gender = item.gender;
            this.employee.blood_group = item.blood_group;
            this.employee.religion = item.religion;
            this.employee.cast = item.cast;
            this.employee.subcast = item.subcast;
            this.employee.mothertongue = item.mothertongue;
            this.employee.nationality = item.nationality;
            this.employee.marital_status = item.marital_status;
            this.employee.qualification = item.qualification;
            this.employee.degree = item.degree;
            this.employee.aadhar = item.aadhar;
            this.employee.pan = item.pan;
            this.employee.pf = item.pf;
            this.employee.uan = item.uan;
            this.employee.esic = item.esic;
        },

    },

    created(){
        this.fetch();
    },

}
</script>