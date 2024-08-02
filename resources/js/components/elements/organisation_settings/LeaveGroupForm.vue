<template>
    <div class="container-fluid">
        
        <section-title title="Add Leave Groups" class=""></section-title>

        <div  v-if="leave_group" class="row g-4 mb-5">

            <forms-text-field name="name" label="Leave Group Name" v-model="leave_group.name" :error="errors.name" classes="col-12 col-lg-6"></forms-text-field>

            <forms-number-field name="total_leaves" readonly="true" label="Total Leaves" :value="total_leaves" v-model="leave_group.total_leaves" :error="errors.total_leaves" classes="col-12 col-lg-6"></forms-number-field>

            
            <div class="col-12" v-for="l in leavesOptions" :key="l.val">

                <div class="input-group">
                    <div class="input-group-text">
                        <input 
                        :id="'ltid_'+l.val"
                        :value="l.is"
                        v-model="l.is"
                        class="form-check-input mt-0"
                        type="checkbox"
                        aria-label="Checkbox for following text input">
                    </div>
                    <label :for="'ltid_'+l.val" class="input-group-text" style="min-width: 150px;">{{ l.key }}</label>
                    <input v-model="l.x" type="number" class="form-control" aria-label="No of Leaves">
                </div>

            </div>

            <forms-submit-button name="" v-model="loading" label="Save Leave Type" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="leave_group.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="leave_group.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <div v-for="lg in leave_groups" :key="lg.id" class="card mb-4">
            <div class="card-body">

                <div class="px-3 mb-3">
                    <span class="h3">{{ lg.name }}</span>
                    <button @click="edit(lg)" class="float-end btn btn-link px-0">Edit</button>
                </div>

                <ul class="list-group list-group-flush">
                    <li class="list-group-item" v-for="hd in lg.lgh" :key="hd.id">
                        <span>{{ hd.leave_master.leave_type }} - {{ hd.leave_master.code }} :</span>
                        <span class="float-end">{{ hd.no_of_leaves }}</span>
                    </li>
                    <li class="list-group-item fw-bold">
                        <span>Total Leaves :</span>
                        <span class="float-end">{{ lg.total_leaves }}</span>
                    </li>
                </ul>

            </div>
            
        </div>

    </div>
</template>

<script>
import axios from "axios";
export default {

    props: ['leaves'],

    data(){
        return {
            loading: false,
            isDelete: false,
            leave_group: {
                id: null,
                name: null,
                total_leaves: 0,
                heads: [],
            },
            errors: {
                name: null,
                total_leaves: null,
            },
            leavesOptions: [],
            leave_groups: [],
        };
    },

    computed: {
        total_leaves() {
            let tot = 0;
            this.leavesOptions.forEach(item => {
                item.is = item.x > 0 ? true : false;
                tot += item.x * 1;
            });
            this.leave_group.total_leaves = tot;
            return tot;
        }
    },

    methods: {

        edit(lg){
            this.leave_group.id = lg.id;
            this.leave_group.name = lg.name;
            this.leave_group.total_leaves = lg.total_leaves;

            lg.lgh.forEach(ll => {
                this.leavesOptions.forEach(item => {
                    if(ll.leave_master_id == item.val){
                        item.is = true;
                        item.x = ll.no_of_leaves;
                    }
                });
            });

            
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/organisation_settings/leaves_setup/delete_lg', this.leave_group).then(res => {
                this.isDelete = false;
                this.loading = false;
                this.resetForm();
                this.fetch();
            });
        },

        fetch(){
            axios.get('/organisation_settings/leaves_setup/fetch_lg')
            .then(res => {
                this.leave_groups = res.data;
            });
        },

        save(){
            let url = this.leave_group.id == null ? '/organisation_settings/leaves_setup/save_lg' : '/organisation_settings/leaves_setup/update_lg';

            this.resetErrors();
            this.leave_group.heads = this.leavesOptions;
            this.loading = true;
            axios.post(url, this.leave_group)
            .then(res => {
                this.loading = false;
                this.resetForm();
                this.fetch();
;            })
            .catch(error => {
                this.loading = false;
                let errs = error.response.data.errors;
                this.setErrors(errs);
            });
        },

        resetForm(){
            this.leave_group.name = null;
            this.leave_group.total_leaves = 0;
            this.leave_group.heads = [];
            this.makeLeavesOption();
        },

        resetErrors(){
            this.errors.name = null;
            this.errors.total_leaves = null;
        },

        setErrors(errors){
            this.errors.name = errors.name ? errors.name[0] : null;
            this.errors.total_leaves = errors.total_leaves ? errors.total_leaves[0] : null;
        },

        makeLeavesOption(){
            this.leavesOptions = [];
            this.leaves.forEach(l => {
                let item = {
                    key: l.leave_type + ' - ' + l.code,
                    val: l.id,
                    x: 0,
                    is: false
                };
                this.leavesOptions.push(item);
            });
        },

    },

    created(){
        this.makeLeavesOption();
        this.fetch();
    },
}
</script>