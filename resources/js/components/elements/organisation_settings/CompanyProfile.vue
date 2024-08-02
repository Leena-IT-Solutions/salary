<template>

    <div class="container-fluid">

        <section-title title="Update Company Profile" class=""></section-title>

        <div  v-if="profile" class="row g-4">

            <forms-text-field name="company_name" label="Company Name" v-model="profile.company_name" error="" classes=""></forms-text-field>

            <forms-text-field name="address" label="Address" v-model="profile.address" error="" classes=""></forms-text-field>

            <forms-text-field name="city" label="City" v-model="profile.city" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="pincode" label="Pincode" v-model="profile.pincode" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="state" label="State" v-model="profile.state" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="country" label="Country" v-model="profile.country" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="phone" label="Phone" v-model="profile.phone" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="email" label="Email Address" v-model="profile.email" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-submit-button name="" v-model="loading" label="Save Profile" @click="update_profile()"></forms-submit-button>

        </div>

    </div>

</template>

<script>
import axios from "axios";
export default {

    data(){
        return {
            loading: false,
            profile: {
                company_name: null,
                address: null,
                city: null,
                state: null,
                pincode: null,
                country: null,
                phone: null,
                email: null,
            },
        };
    },

    methods: {

        fetch_profile(){
            axios.get('/organisation_settings/company_profile/fetch').then(res => {
                this.profile.company_name = res.data.company_name;
                this.profile.address = res.data.address;
                this.profile.city = res.data.city;
                this.profile.state = res.data.state;
                this.profile.pincode = res.data.pincode;
                this.profile.country = res.data.country;
                this.profile.phone = res.data.phone;
                this.profile.email = res.data.email;
            });
        },

        update_profile(){

            this.loading = true;

            axios.post('/organisation_settings/company_profile/update', this.profile).then(res => {
                this.loading = false;
                this.fetch_profile();
            });

        }

    },

    created(){

        this.fetch_profile();

    },

}
</script>