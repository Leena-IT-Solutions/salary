<template>
    <div class="container-fluid">

        <section-title title="Add Employee Photo" class=""></section-title>

        <div  v-if="employee_photo" class="row g-4 mb-5">

            <forms-file-field @change="getImageObject($event)" v-model="employee_photo.photo" name="media" label="Media" error="" classes=""></forms-file-field>

            <forms-submit-button name="" v-model="loading" label="Save Employee Photo" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="employee_photo.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="employee_photo.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <!-- Search -->
        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: ' Employee Photo', val: 'employee_photo'},{key: 'Code', val: 'code'},]"></forms-select-field>

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
                        <th @click="orderBy('media')" class="cursor-pointer">Media</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="wl in employee_photos" :key="wl.id">
                        <td>{{ wl.id }}</td>
                        <td><img :src="wl.media" class="me-3" style="width: 60px;"> {{ wl.media }}</td>
                        <td class="text-end">
                            <button class="btn btn-outline-info btn-sm me-2" @click="edit(wl)"><i class="bi bi-pencil"></i></button>
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
            employee_photo: {
                employee_id: null,
                id: null,
                photo: null,
                media: {},
            },
            employee_photos: [],
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

        getImageObject(e){
            this.employee_photo.media = e.target.files[0];
        },

        fetch(){

            let url = '/employee/employee_photo/' + this.employee_id + '/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.employee_photos = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.employee_photos.push(item);
                    });
                }

                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.employee_photos = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.employee_photo.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.employee_photo.id = null;
            this.employee_photo.media = null;
            this.employee_photo.photo = null;
        },

        add(){
            this.loading = true;

            let fd = new FormData();
            fd.append('media', this.employee_photo.media);
            fd.append('employee_id', this.employee_photo.employee_id);

            axios.post('/employee/employee_photo/add', fd).then(res => {
                this.reset();
                this.search();
            });
        },

        update(){
            this.loading = true;

            let fd = new FormData();
            fd.append('id', this.employee_photo.id);
            fd.append('media', this.employee_photo.media);
            fd.append('employee_id', this.employee_photo.employee_id);

            axios.post('/employee/employee_photo/update', fd).then(res => {
                this.reset();
                this.search();
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_photo/delete', this.employee_photo).then(res => {
                this.reset();
                this.search();
            });
        },

        edit(item){
            this.employee_photo.id = item.id;
            //this.employee_photo.media = item.media;
        },

    },

    created(){
        this.fetch();
        this.employee_photo.employee_id = this.employee_id;
    },

}
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}
</style>