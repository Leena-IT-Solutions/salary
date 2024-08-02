<template>
    <div class="container-fluid">

        <section-title title="Add Working Shift" class=""></section-title>

        <div  v-if="working_shift" class="row g-4 mb-5">

            <forms-text-field name="name" label="Working Shift Name" v-model="working_shift.name" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-select-field name="is_next_day_out" label="Is Next Day Out?" v-model="working_shift.is_next_day_out" error="" classes="col-12 col-lg-6" 
            :options="[{ key: 'Yes', val: 1 }, { key: 'No', val: 0 }]"></forms-select-field>

            <forms-time-field name="in" label="In Time" v-model="working_shift.in" error="" classes="col-12 col-lg-4"></forms-time-field>

            <forms-time-field name="halfday" label="Halfday Out Time" v-model="working_shift.halfday" error="" classes="col-12 col-lg-4"></forms-time-field>

            <forms-time-field name="out" label="Out Time" v-model="working_shift.out" error="" classes="col-12 col-lg-4"></forms-time-field>

            <forms-submit-button name="" v-model="loading" label="Save Working Shift" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="working_shift.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="working_shift.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: 'Working Shift', val: 'working_shift'},{key: 'Code', val: 'code'},]"></forms-select-field>

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
                        <th @click="orderBy('name')" class="cursor-pointer">Shift Name</th>
                        <th @click="orderBy('in')" class="cursor-pointer">In Time</th>
                        <th @click="orderBy('halfday')" class="cursor-pointer">Halfday Out Time</th>
                        <th @click="orderBy('out')" class="cursor-pointer">Out Time</th>
                        <th @click="orderBy('is_next_day_out')" class="cursor-pointer">Next Day Out?</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="item in working_shifts" :key="item.id">
                        <td>{{ item.id }}</td>
                        <td>{{ item.name }}</td>
                        <td>{{ item.in }}</td>
                        <td>{{ item.halfday }}</td>
                        <td>{{ item.out }}</td>
                        <td>{{ item.is_next_day_out }}</td>
                        <td class="text-end">
                            <button class="btn btn-outline-info btn-sm me-2" @click="edit(item)"><i class="bi bi-pencil"></i></button>
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
            working_shift: {
                id: null,
                name: null,
                in: null,
                out: null,
                halfday: null,
                is_next_day_out: null,
            },
            working_shifts: [],
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

            let url = '/organisation_settings/working_shifts/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.working_shifts = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.working_shifts.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.working_shifts = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.working_shift.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.working_shift.id = null;
            this.working_shift.name = null;
            this.working_shift.in = null;
            this.working_shift.out = null;
            this.working_shift.halfday = null;
            this.working_shift.is_next_day_out = null;
        },

        add(){
            this.loading = true;
            axios.post('/organisation_settings/working_shifts/add', this.working_shift).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;
            axios.post('/organisation_settings/working_shifts/update', this.working_shift).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/organisation_settings/working_shifts/delete', this.working_shift).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.working_shift.id = item.id;
            this.working_shift.name = item.name;
            this.working_shift.in = item.in;
            this.working_shift.out = item.out;
            this.working_shift.halfday = item.halfday;
            this.working_shift.is_next_day_out = item.is_next_day_out;
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