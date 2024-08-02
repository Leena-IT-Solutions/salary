<template>

    <div class="container-fluid">

        <section-title title="Company Registration Details" class=""></section-title>

        <div  v-if="profile" class="row g-4">

            <forms-text-field name="tan" label="TAN" v-model="profile.tan" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="pan" label="PAN" v-model="profile.pan" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="epf" label="EPF" v-model="profile.epf" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="esic" label="ESIC" v-model="profile.esic" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="gst" label="GST" v-model="profile.gst" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="cin" label="CIN" v-model="profile.cin" error="" classes="col-12 col-lg-6"></forms-text-field>

            <forms-text-field name="ptax" label="Professional TAX" v-model="profile.ptax" error="" classes="col-12 col-lg-6"></forms-text-field>

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
                tan: null,
                pan: null,
                epf: null,
                esic: null,
                gst: null,
                cin: null,
                ptax: null,
            },
        };
    },

    methods: {

        fetch_profile(){
            axios.get('/organisation_settings/company_profile/fetch').then(res => {
                this.profile.tan = res.data.tan;
                this.profile.pan = res.data.pan;
                this.profile.epf = res.data.epf;
                this.profile.esic = res.data.esic;
                this.profile.gst = res.data.gst;
                this.profile.cin = res.data.cin;
                this.profile.ptax = res.data.ptax;
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