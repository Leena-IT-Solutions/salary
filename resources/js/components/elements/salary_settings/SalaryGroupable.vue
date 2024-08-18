<template>
    <div>

        <!-- Header -->
        <div class="py-3 px-4 border">
            <span class="m-0 h4 fw-bold">Salary Group</span>
            <span class="float-end">
                <button 
                @click="isForm = !isForm"
                :class="[isForm ? 'btn-danger' : 'btn-outline-primary']"
                class="btn btn-sm">
                    {{ isForm ? 'Finish Editing' : 'Edit Salary Group Component' }}
                </button>
            </span>
        </div>

        <!-- Salary Group Details -->
        <div class="container-fluid px-4 mt-5">
            <div class="shadow rounded p-4">
                <h3 class="m-0">{{ salary_group.salary_group_name }} <span class="float-end">{{ salary_group.is_active ? 'Active' : 'Deactivated' }}</span></h3>
            </div>
        </div>

        <!-- Form -->
        <div class="container-fluid px-4 pt-5 m-0 mb-4" v-if="isForm">

            <div class="row g-2 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Earnings</h3>
                </div>
                <div class="col-12 pointer p-2" :class="(earning.salary_groups.length > 0) ? 'text-bg-success rounded' : 'rounded shadow'" @dblclick="updateSGD(earning.id, 'earning')" v-for="earning in earnings" :key="earning.id">
                    {{ earning.name }}
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Services</h3>
                </div>
                <div class="col-12 pointer p-2" :class="(service.salary_groups.length > 0) ? 'text-bg-success rounded' : 'rounded shadow'" @dblclick="updateSGD(service.id, 'service')" v-for="service in services" :key="service.id">
                    {{ service.name }}
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Reimbursements</h3>
                </div>
                <div class="col-12 pointer p-2" :class="(reim.salary_groups.length > 0) ? 'text-bg-success rounded' : 'rounded shadow'" @dblclick="updateSGD(reim.id, 'reimbursement')" v-for="reim in reimbursements" :key="reim.id">
                    {{ reim.name }}
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Statutory Compliance Components</h3>
                </div>
                <div class="col-12 pointer p-2" :class="(statu.salary_groups.length > 0) ? 'text-bg-success rounded' : 'rounded shadow'" @dblclick="updateSGD(statu.id, 'statutory')" v-for="statu in statutories" :key="statu.id">
                    {{ statu.scheme_name }} - {{ statu.abbreviation }}
                </div>
            </div>

        </div>

        <!-- Data -->
        <div v-if="!isForm" class="container-fluid px-4 pt-5 m-0 mb-4">
            
            <div class="row g-2 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Earnings</h3>
                </div>
                <div class="col-12 p-2 border" v-for="earning in myEarnings" :key="earning.id">
                    <i class="bi bi-check-all"></i> {{ earning.name }}
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Services</h3>
                </div>
                <div class="col-12 p-2 border" v-for="service in myServices" :key="service.id">
                    <i class="bi bi-check-all"></i> {{ service.name }}
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Reimbursements</h3>
                </div>
                <div class="col-12 p-2 border" v-for="reim in myReimbursements" :key="reim.id">
                    <i class="bi bi-check-all"></i> {{ reim.name }}
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Statutory Compliance Components</h3>
                </div>
                <div class="col-12 p-2 border" v-for="statu in myStatutories" :key="statu.id">
                    <i class="bi bi-check-all"></i> {{ statu.scheme_name }} - {{ statu.abbreviation }}
                </div>
            </div>

        </div>

    </div>
</template>

<script>
import axios from "axios";
export default {

    props: ['salary_group'],

    data(){
        return {
            isForm: false,
            items: null,
            earnings: [],
            services: [],
            reimbursements: [],
            statutories: [],
        };
    },

    computed: {
        myEarnings(){
            let e = [];
            if(this.items){                
                e = this.isForm ? this.earnings : this.items.earnings
            }
            return e;
        },
        myServices(){
            let e = [];
            if(this.items){                
                e = this.isForm ? this.services : this.items.services
            }
            return e;
        },
        myReimbursements(){
            let e = [];
            if(this.items){                
                e = this.isForm ? this.reimbursements : this.items.reimbursements
            }
            return e;
        },
        myStatutories(){
            let e = [];
            if(this.items){                
                e = this.isForm ? this.statutories : this.items.statutories
            }
            return e;
        },
    },

    methods: {

        updateSGD(id, what){
            let data = {
                salary_group_id: this.salary_group.id,
                salary_groupable_id: id,
                salary_groupable_type: what,
            };
            axios.post('/salary_settings/salary_groupable/data/update', data).then(res => {
                this.items = res.data;
                switch(what){
                    case "earning":
                        this.fetch_earnings();
                        break;
                    case "service":
                        this.fetch_services();
                        break;
                    case "reimbursement":
                        this.fetch_reimbursements();
                        break;
                    case "statutory":
                        this.fetch_statutories();
                        break;
                    
                }
            });
        },

        fetch(){
            axios.get('/salary_settings/salary_groupable/'+this.salary_group.id+'/data/fetch').then(res => {
                this.items = res.data;
            });
        },

        fetch_earnings(){
            axios.get('/salary_settings/salary_groupable/'+this.salary_group.id+'/data/earnings').then(res => {
                this.earnings = res.data;
            });
        },

        fetch_services(){
            axios.get('/salary_settings/salary_groupable/'+this.salary_group.id+'/data/services').then(res => {
                this.services = res.data;
            });
        },

        fetch_reimbursements(){
            axios.get('/salary_settings/salary_groupable/'+this.salary_group.id+'/data/reimbursements').then(res => {
                this.reimbursements = res.data;
            });
        },

        fetch_statutories(){
            axios.get('/salary_settings/salary_groupable/'+this.salary_group.id+'/data/statutories').then(res => {
                this.statutories = res.data;
            });
        },

    },

    created () {
        this.fetch();
        this.fetch_earnings();
        this.fetch_services();
        this.fetch_reimbursements();
        this.fetch_statutories();
    },

}
</script>

<style>
.pointer {
    cursor: pointer;
    user-select: none;
}
</style>