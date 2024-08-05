<template>
    <div class="container-fluid">

        <div class="row g-4 mb-4 align-items-center">
            <forms-text-field @change="getEmployee()" name="employee_code" label="Enter Employee Code" v-model="employee_code" error="" classes="col-12 col-lg-6"></forms-text-field>
            <div v-if="employee" class="col">
                <span class="h5">{{ employee.first_name }} {{ employee.middle_name }} {{ employee.last_name }} - {{ employee.id }}</span>
            </div>
        </div>

        <div  v-if="(item && employee) || item.id != null" class="row g-4 mb-5">

            <!-- <forms-text-field name="employee_id" label="Employee ID" v-model="item.employee_id" error="" classes="col-12"></forms-text-field> -->

            <forms-date-field name="ot_date" label="Overtime Date" v-model="item.ot_date" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-number-field name="hrs" label="Hours" v-model="item.hrs" error="" classes="col-12 col-lg-4"></forms-number-field>

            <forms-select-field name="status" label="Status" v-model="item.status" error="" classes="col-12 col-lg-4" 
            :options="[{ key: 'Approved', val: 'Approved' }, { key: 'Rejected', val: 'Rejected' }]"></forms-select-field>

            <forms-text-field name="note" label="Note" v-model="item.note" error="" classes="col-12"></forms-text-field>

            <forms-submit-button name="" v-model="loading" label="Save department" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="item.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="item.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

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

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th @click="orderBy('id')" class="cursor-pointer" style="width: 60px;">ID</th>
                        <th class="cursor-pointer">Employee</th>
                        <th @click="orderBy('ot_date')" class="cursor-pointer">Date</th>
                        <th @click="orderBy('hrs')" class="cursor-pointer">Hours</th>
                        <th @click="orderBy('note')" class="cursor-pointer">Note</th>
                        <th @click="orderBy('status')" class="cursor-pointer">Status</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.employee.first_name }} {{ row.employee.middle_name }} {{ row.employee.last_name }}</td>
                        <td>{{ row.ot_date }}</td>
                        <td>{{ row.hrs }} {{ (row.hrs > 1) ? 'Hrs' : 'Hr' }}</td>
                        <td>{{ row.note }}</td>
                        <td>{{ row.status }}</td>
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
                employee_id: null,
                ot_date: null,
                hrs: null,
                note: null,
                status: null,
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
            employee_code: null,
            employee: null,
        };
    },

    methods: {

        reset(){
            this.item.id = null;
            this.item.employee_id = null;
            this.item.ot_date = null;
            this.item.hrs = null;
            this.item.note = null;
            this.item.status = null;
        },

        edit(item){
            this.item.id = item.id;
            this.item.employee_id = item.employee_id;
            this.item.ot_date = item.ot_date;
            this.item.hrs = item.hrs;
            this.item.note = item.note;
            this.item.status = item.status;
        },

        fetch(){

            let url = '/approvals/overtime/fetch';
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
            axios.post('/approvals/overtime/add', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/approvals/overtime/update', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/approvals/overtime/delete', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        getEmployee(){
            this.reset();
            axios.get('/approvals/overtime/employee/' + this.employee_code).then(res => {
                this.employee = res.data.employee;
                this.item.employee_id = this.employee.id;
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