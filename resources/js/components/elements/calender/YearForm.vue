<template>
    <div>

        <div class="row g-3 align-items-center">

            <forms-select-field @change="emitChange()" v-model="working_year_id" name="working_year_id" label="Year" error="" classes="col-12 col-xl-6" :options="years"></forms-select-field>
    
            <forms-number-field @input="validateYear()" v-if="working_year_id == 'New'" v-model="yyyy" name="yyyy" label="Year" error="" classes="col-12 col-xl-6"></forms-number-field>

            <forms-submit-button v-if="working_year_id == 'New'" name="" label="Save New Year" @click="save()" classes="col-auto btn-lg py-2"></forms-submit-button>

            <div class="col" v-if="err">
                <span class="text-danger">{{ err }}</span>
            </div>

        </div>

        
    </div>
</template>

<script>
import axios from 'axios';
export default {

    data(){
        return {
            err: null,
            working_year_id: null,
            yyyy: null,
            years: [],
        };
    },

    methods: {

        emitChange(){
            this.years.forEach(year => {
                if(year.val == this.working_year_id){
                    this.$emit('yearChange', year);
                }
            });
        },

        validateYear(){
            if(this.yyyy.length > 3 && (this.yyyy < 1990 || this.yyyy > 2100)){
                if(this.yyyy < 1990){
                    this.yyyy = 1990;
                } else if(this.yyyy > 2050){
                    this.yyyy = 2050
                }
            }
        },

        getWorkingYears(id=null){
            axios.get('/calender/working_years').then(res => {
                this.years = res.data;
                this.years.push({key: 'Add New Year', val: 'New'});
                if(id!=null){
                    this.working_year_id = id;
                    this.emitChange();
                } else {
                    this.years.forEach(y => {
                        if(y.key == this.getCurrentYear()){
                            this.working_year_id = y.val;
                            this.emitChange();
                        }
                    });
                }
            });
        },

        save(){
            this.err = null;
            axios.post('/calender/working_years/add', {yyyy: this.yyyy})
            .then(res => {
                this.reset();
                this.getWorkingYears(res.data['id']);
            })
            .catch(err => {
                let errs = err.response.data.errors;
                this.err = errs.yyyy[0];
            });
        },

        reset(){
            this.working_year_id = null;
            this.yyyy = null;
        },

        getCurrentYear(){
            return new Date().getFullYear();
        }

    },

    created () {
        this.getWorkingYears();
    },
    
}
</script>