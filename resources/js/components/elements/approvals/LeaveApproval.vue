<template>
    <div class="container-fluid">

        <div class="row g-4 mb-4">
            <forms-text-field @change="getEmployee()" name="employee_code" label="Enter Employee Code" v-model="employee_code" error="" classes="col-12 col-lg-6"></forms-text-field>
    
            <forms-select-field @change="getEmployee()" name="selected_fy" label="Financial Year" v-model="selected_fy" error="" classes="col-12 col-lg-6" 
            :options="fys"></forms-select-field>
        </div>


        <div v-if="employee != null" class="mb-4 card p-4">
            <div class="fw-bold h3">
                {{ employee.first_name }} {{ employee.middle_name }} {{ employee.last_name }}
            </div>

            <div>
                <ul class="list-group list-group-flush">
                    
                    <li class="list-group-item"> </li>
                    <li class="list-group-item" >
                        
                    </li>
                </ul>

                <ul class="list-group list-group-flush">
                    
                    <li class="list-group-item h5">{{ employee.employee_leave_group.leave_group.name }}</li>

                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Leaves
                        <span class="h4 btn btn-success rounded-4">{{ totalUsed() }} / {{ employee.employee_leave_group.leave_group.total_leaves }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center"
                    v-for="lll in employee.employee_leave_group.leave_group.lgh" :key="lll.id">
                        {{ lll.leave_master.leave_type }}
                        <span class="h4 btn btn-primary rounded-4">{{ getUsed(lll.leave_master_id) }} / {{ lll.no_of_leaves }}</span>
                    </li>
                </ul>
            </div>

            <div >
                
            </div>

        </div>

        <div  v-if="item" class="row g-4 mb-5">

            <!-- <forms-text-field name="employee_id" label="Employee ID" v-model="item.employee_id" error="" classes="col-12"></forms-text-field> -->

            <forms-select-field name="leave_master_id" label="Leave Type" v-model="item.leave_master_id" error="" classes="col-12 col-lg-4" 
            :options="leaves"></forms-select-field>

            <forms-date-field name="from" label="From Date" v-model="item.from" error="" classes="col-12 col-lg-4"></forms-date-field>
    
            <forms-date-field name="to" label="To Date" v-model="item.to" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-select-field name="status" label="Status" v-model="item.status" error="" classes="col-12 col-lg-4" 
            :options="[{ key: 'Approved', val: 'Approved' }, { key: 'Rejected', val: 'Rejected' }]"></forms-select-field>

            <forms-select-field name="is_halfday" label="Is Halfday?" v-model="item.is_halfday" error="" classes="col-12 col-lg-4" 
            :options="[{ key: 'Yes', val: 'Yes' }, { key: 'No', val: 'No' }]"></forms-select-field>

            <forms-select-field name="is_lop" label="Is loss of pay?" v-model="item.is_lop" error="" classes="col-12 col-lg-4" 
            :options="[{ key: 'Yes', val: 'Yes' }, { key: 'No', val: 'No' }]"></forms-select-field>

            <forms-text-field name="reason" label="Reason" v-model="item.reason" error="" classes="col-12"></forms-text-field>

            <forms-submit-button name="" v-model="loading" label="Save leave" @click="save()" classes="col-12"></forms-submit-button>

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
            :options="[{key: 'ID', val: 'id'},{key: 'Designation', val: 'designation'},{key: 'Code', val: 'code'},]"></forms-select-field>

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
                        <th class="cursor-pointer">Leave Type</th>
                        <th @click="orderBy('from')" class="cursor-pointer">From</th>
                        <th @click="orderBy('to')" class="cursor-pointer">To</th>
                        <th @click="orderBy('no_of_days')" class="cursor-pointer">Days</th>
                        <th @click="orderBy('status')" class="cursor-pointer">Status</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.employee.first_name }} {{ row.employee.middle_name }} {{ row.employee.last_name }}</td>
                        <td>{{ row.leave_master.leave_type }}</td>
                        <td>{{ row.from }}</td>
                        <td>{{ row.to }}</td>
                        <td>{{ row.no_of_days }}</td>
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

    props: ['leaves', 'fy', 'fys'],

    data(){
        return {
            loading: false,
            isDelete: false,
            item: {
                id: null,
                employee_id: null,
                leave_master_id: null,
                from: null,
                to: null,
                reason: null,
                status: null,
                is_halfday: null,
                is_lop: null,
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
            selected_fy: null,
            used: null,
        };
    },

    methods: {

        reset(){
            this.item.id = null;
            this.item.employee_id = null;
            this.item.leave_master_id = null;
            this.item.from = null;
            this.item.to = null;
            this.item.reason = null;
            this.item.status = null;
            this.item.is_halfday = null;
            this.item.is_lop = null;
            this.employee = null;
            this.used = null;
        },

        edit(entry){
            this.item.id = entry.id;
            this.item.employee_id = entry.employee_id;
            this.item.leave_master_id = entry.leave_master_id;
            this.item.from = entry.from;
            this.item.to = entry.to;
            this.item.reason = entry.reason;
            this.item.status = entry.status;
            this.item.is_halfday = entry.is_halfday;
            this.item.is_lop = entry.is_lop;
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

        fetch(){
            let url = '/approvals/leave/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }
            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.items = res.data.data;
                } else {
                    res.data.data.forEach(res => {
                        this.items.push(res);
                    });
                }
                this.loading = false;
            });
        },

        add(){
            this.loading = true;
            axios.post('/approvals/leave/add', this.item).then(res => {
                this.reset();
                this.search();
                this.employee_code = null;
            });
        },

        update(){
            this.loading = true;
            axios.post('/approvals/leave/update', this.item).then(res => {
                this.reset();
                this.search();
                this.employee_code = null;
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/approvals/leave/delete', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        getEmployee(){
            this.reset();
            axios.get('/approvals/leave/employee/' + this.employee_code + '/fy/' + this.selected_fy).then(res => {
                this.employee = res.data.employee;
                this.used = res.data.leaves_availed;
                this.item.employee_id = this.employee.id;
            });
        },

        getUsed(id){
            let a = 0;
            this.used.forEach(lt => {
                if(lt.id == id){
                    a = lt.used;
                }
            });
            return a;
        },

        totalUsed(){
            let total = 0;
            this.used.forEach(lt => {
                total += lt.used;
            });
            return total;
        },

    },

    created(){
        this.fetch();
        this.selected_fy = this.fy.id;
    },

}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>