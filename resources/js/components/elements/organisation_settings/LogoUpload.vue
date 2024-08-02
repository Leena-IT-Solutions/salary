<template>

    <div class="container-fluid">

        <section-title title="Set Company Logo" class=""></section-title>

        <div  v-if="profile" class="" style="max-width: 320px;">
            
            <div class="border p-2 rounded mb-3">
                <div class="image image-contain image-wide rounded overflow-hidden">
                    <img :src="profile.logo">
                </div>
            </div>

            <forms-file-field name="logo" label="Add Logo" v-model="logo" error="" classes="" @change="logoChanged($event)"></forms-file-field>

        </div>

    </div>

</template>

<script>
import axios from "axios";
export default {

    data(){
        return {
            logo: null,
            profile: null,
        };
    },

    methods: {

        fetch_profile(){
            axios.get('/organisation_settings/company_profile/fetch').then(res => {
                this.profile = res.data;
            });
        },

        logoChanged(e){

            let fd = new FormData;

            fd.append("logo", e.target.files[0]);

            let config = {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }

            axios.post('/organisation_settings/company_profile/logo_upload', fd, config).then(res => {
                this.fetch_profile();
            });

        }

    },

    created(){

        this.fetch_profile();

    },

}
</script>