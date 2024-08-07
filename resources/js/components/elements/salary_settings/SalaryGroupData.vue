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
        <div class="container-fluid px-5 pt-5 m-0 mb-4">

            <div class="row g-1 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Earnings</h3>
                </div>
                <div class="col-12 pointer p-2" :class="(getItemStatus(earning.id, 'earning')) ? 'text-bg-success rounded' : ''" @dblclick="updateSGD(earning.id, 'earning')" v-for="earning in earnings" :key="earning.id">
                    {{ earning.name }}
                </div>
            </div>

            <div class="row g-1 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Services</h3>
                </div>
                <div class="col-12 pointer p-2" :class="(getItemStatus(service.id, 'service')) ? 'text-bg-success rounded' : ''" @dblclick="updateSGD(service.id, 'service')" v-for="service in services" :key="service.id">
                    {{ service.name }}
                </div>
            </div>

            <div class="row g-1 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Reimbursements</h3>
                </div>
                <div class="col-12 pointer p-2" :class="(getItemStatus(reim.id, 'reimbursement')) ? 'text-bg-success rounded' : ''" @dblclick="updateSGD(reim.id, 'reimbursement')" v-for="reim in reimbursements" :key="reim.id">
                    {{ reim.name }}
                </div>
            </div>

            <div class="row g-1 mb-4">
                <div class="col-12 mb-2">
                    <h3 class="m-0">Statutory Compliance Components</h3>
                </div>
                <div class="col-12 pointer p-2" :class="(getItemStatus(statu.id, 'statutory')) ? 'text-bg-success rounded' : ''" @dblclick="updateSGD(statu.id, 'statutory')" v-for="statu in statutory" :key="statu.id">
                    {{ statu.scheme_name }} - {{ statu.abbreviation }}
                </div>
            </div>

        </div>

        <!-- Data -->
        <!-- <div v-if="!isForm" class="container-fluid px-4 pt-5 m-0 mb-4">
            <div class="row g-4">

                Salary Group Components Display
            </div>
        </div> -->

    </div>
</template>

<script>
import axios from "axios";
export default {

    props: ['salary_group', 'earnings', 'services', 'reimbursements', 'statutory'],

    data(){
        return {
            isForm: false,
            item: {
                id: null,
            },
            items: [],
        };
    },

    methods: {

        updateSGD(id, what){
            let data = {
                salary_group_id: this.salary_group.id,
                common_id: id,
                what: what,
            };
            axios.post('/salary_settings/salary_group/data/update', data).then(res => {
                this.items = res.data;
            });
        },

        fetch(){
            axios.get('/salary_settings/salary_group/'+this.salary_group.id+'/data/fetch').then(res => {
                this.items = res.data;
            });
        },

        getItemStatus(id, what){
            let is = false;
            this.items.forEach(row => {
                if(row.what == what && row.common_id == id){
                    is = true;
                }
            });
            return is;
        },

    },

    created () {
        this.fetch();
    },

}
</script>

<style>
.pointer {
    cursor: pointer;
    user-select: none;
}
</style>