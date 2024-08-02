<template>
    <div>
        <div class="row g-4 align-items-stretch d-flex mb-5">

            <!-- <div class="col-12 col-md-6 col-xxl-4" v-if="profile">
                <div class="shadow p-4 rounded-3 h-100">
                    <h5 class="fw-bold">Head Office</h5>
                    <h3>{{ profile.company_name }}</h3>
                    <p class="mb-1">{{ profile.address }} {{ profile.city }} {{ profile.state }} {{ profile.country }} {{ profile.pincode }}</p>
                    <p class="mb-1">{{ profile.phone }}</p>
                    <p class="mb-3">{{ profile.email }}</p>
                    <a class="btn btn-outline-primary btn-sm me-2" href="/settings/company_profile">Edit</a>
                </div>
            </div> -->

            <div class="col-12 col-md-6 col-xxl-4" v-for="wl, ind in workLocations" :key="ind">
                <div class="shadow p-4 rounded-3 h-100">
                    <h5 class="fw-bold">{{ wl.location_name }}</h5>
                    <h3>{{ profile.company_name }}</h3>
                    <p class="mb-1">{{ wl.address }} {{ wl.city }} {{ wl.state }} {{ wl.country }} {{ wl.pincode }}</p>
                    <p class="mb-1">{{ wl.phone }}</p>
                    <p class="mb-3">{{ wl.email }}</p>
                    <a class="btn btn-outline-primary btn-sm me-2" href="#wlform" @click="edit(wl)">Edit</a>
                    <button class="btn btn-outline-danger btn-sm me-2" v-if="isDelete != wl.id" @click="isDelete = wl.id">Delete</button>
                    <button class="btn btn-outline-danger btn-sm me-2" v-if="isDelete == wl.id" @click="deleteWL(wl.id)">Sure to delete</button>
                    <button class="btn btn-outline-warning btn-sm me-2" v-if="isDelete == wl.id" @click="isDelete = null">Cancel</button>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xxl-4">
                <div class="shadow p-4 rounded-3 h-100 d-flex justify-content-center align-items-center">
                    <a href="#wlform" class="btn border-0" @click="isForm = !isForm">
                        <i class="bi bi-plus-circle display-1"></i>
                        <span class="d-block">Add Work Locations</span>
                    </a>
                </div>
            </div>

        </div>

        <div v-if="isForm" id="wlform">
            <section-title title="Add Work Location" class=""></section-title>
    
            <div  v-if="workLocation" class="row g-4">
    
                <forms-text-field name="location_name" @input="errors.location_name = null" label="Location Name" v-model="workLocation.location_name" :error="errors.location_name" classes=""></forms-text-field>
    
                <forms-text-field name="address" @input="errors.address = null" label="Address" v-model="workLocation.address" :error="errors.address" classes=""></forms-text-field>
    
                <forms-text-field name="city" @input="errors.city = null" label="City" v-model="workLocation.city" :error="errors.city" classes="col-12 col-lg-6"></forms-text-field>
    
                <forms-text-field name="pincode" @input="errors.pincode = null" label="Pincode" v-model="workLocation.pincode" :error="errors.pincode" classes="col-12 col-lg-6"></forms-text-field>
    
                <forms-text-field name="state" @input="errors.state = null" label="State" v-model="workLocation.state" :error="errors.state" classes="col-12 col-lg-6"></forms-text-field>
    
                <forms-text-field name="country" @input="errors.country = null" label="Country" v-model="workLocation.country" :error="errors.country" classes="col-12 col-lg-6"></forms-text-field>
    
                <forms-text-field name="phone" @input="errors.phone = null" label="Phone" v-model="workLocation.phone" :error="errors.phone" classes="col-12 col-lg-6"></forms-text-field>
    
                <forms-text-field name="email" @input="errors.email = null" label="Email Address" v-model="workLocation.email" :error="errors.email" classes="col-12 col-lg-6"></forms-text-field>
    
                <forms-submit-button name="" v-model="loading" label="Save Profile" @click="saveWorkLocation()"></forms-submit-button>
    
            </div>
        </div>


    </div>
</template>

<script>
import axios from "axios";
export default {

    data(){
        return {
            isForm: false,
            isDelete: null,
            loading: false,
            profile: null,
            workLocation: {
                id: null,
                location_name: null,
                address: null,
                city: null,
                state: null,
                pincode: null,
                country: null,
                phone: null,
                email: null,
            },
            errors: {
                location_name: null,
                address: null,
                city: null,
                state: null,
                pincode: null,
                country: null,
                phone: null,
                email: null,
            },
            workLocations: [],
        };
    },

    methods: {

        edit(wl){
            window.location.hash = "wlform";
            this.isForm = true;
            this.workLocation.id = wl.id;
            this.workLocation.location_name = wl.location_name;
            this.workLocation.address = wl.address;
            this.workLocation.city = wl.city;
            this.workLocation.state = wl.state;
            this.workLocation.pincode = wl.pincode;
            this.workLocation.country = wl.country;
            this.workLocation.phone = wl.phone;
            this.workLocation.email = wl.email;
        },

        reset(){
            this.workLocation.id = null;
            this.workLocation.location_name = null;
            this.workLocation.address = null;
            this.workLocation.city = null;
            this.workLocation.state = null;
            this.workLocation.pincode = null;
            this.workLocation.country = null;
            this.workLocation.phone = null;
            this.workLocation.email = null;
        },

        resetErrors(){
            this.errors.location_name = null;
            this.errors.address = null;
            this.errors.city = null;
            this.errors.state = null;
            this.errors.pincode = null;
            this.errors.country = null;
            this.errors.phone = null;
            this.errors.email = null;
        },

        setErrors(errors){
            this.errors.location_name = errors.location_name ? errors.location_name[0] : null;
            this.errors.address = errors.address ? errors.address[0] : null;
            this.errors.city = errors.city ? errors.city[0] : null;
            this.errors.state = errors.state ? errors.state[0] : null;
            this.errors.pincode = errors.pincode ? errors.pincode[0] : null;
            this.errors.country = errors.country ? errors.country[0] : null;
            this.errors.phone = errors.phone ? errors.phone[0] : null;
            this.errors.email = errors.email ? errors.email[0] : null;
        },

        fetch_profile(){
            axios.get('/organisation_settings/company_profile/fetch')
            .then(res => {
                this.profile = res.data;
            });
        },

        fetch_work_locations(){
            axios.get('/organisation_settings/work_location/fetch').then(res => {
                this.loading = false;
                this.workLocations = res.data;
            });
        },

        add(){
            this.resetErrors();
            axios.post('/organisation_settings/work_location/add', this.workLocation)
            .then(res => {
                this.loading = false;
                this.fetch_work_locations();
                this.reset();
            }).catch(error => {
                this.loading = false;
                let errs = error.response.data.errors;
                this.setErrors(errs);
            });
        },

        update(){
            this.resetErrors();
            axios.post('/organisation_settings/work_location/update', this.workLocation).then(res => {
                this.fetch_work_locations();
                this.reset();
            });
        },

        deleteWL(id){
            axios.post('/organisation_settings/work_location/delete', {id: id}).then(res => {
                this.fetch_work_locations();
                this.reset();
            });
        },

        saveWorkLocation(){

            this.loading = true;

            if(this.workLocation.id == null){
                this.add();
            } else {
                this.update();
            }

        }

    },

    created(){

        this.fetch_profile();

        this.fetch_work_locations();

    },

}
</script>