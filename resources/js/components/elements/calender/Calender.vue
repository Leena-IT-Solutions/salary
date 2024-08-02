<template>
    <div>
        <year-form @yearChange="onYearChange($event)"></year-form>

        <div class="row">

            <div class="p-3 col-12 col-xl-12">

                <div class="row align-items-center">
                    <div class="col-auto"><button class="btn btn-dark" :disabled="currentMonth <= 0" @click="navigateMonth('Prev')">Prev</button></div>
                    <div class="col">
                        <h3 class="text-center my-3 p-0 m-0">{{ months[currentMonth] }}, {{ currentYear ? currentYear.key : '' }}</h3>
                    </div>
                    <div class="col-auto"><button class="btn btn-dark" :disabled="currentMonth >= 11" @click="navigateMonth('Next')">Next</button></div>
                </div>

                

                <div class="d-flex flex-wrap justify-content-start">

                    <div
                    v-for="dd, ind in days" :key="ind" 
                    class="text-bg-primary text-center py-3 border" 
                    style="width: 14.28%;">{{ dd }}</div>

                    <template v-for="n of 42" :key="n">
                        <div
                        v-if="n-firstDay <= day_count[currentMonth]"
                        @click="addRemoveDate(n - firstDay)"
                        class="text-center py-3 border border-dark border-opacity-50"
                        :class="[
                            (checkData(n-firstDay) == 'Weekoff' ? 'text-bg-success' : ''), 
                            (checkData(n-firstDay) == 'Holiday' ? 'text-bg-danger' : ''),
                            (checkData(n-firstDay) == 'Halfday' ? 'text-bg-warning' : ''),
                            (sendIfExist(n -firstDay) ? 'border border-4' : '')]"
                        style="width: 14.28%; cursor: pointer;">
                        {{ (n - firstDay <= 0 || n-firstDay > day_count[currentMonth]) ? '' :  n - firstDay }}
                        {{ checkData(n-firstDay) }}
                        </div>
                    </template>
                    

                </div>

            </div>

            <div class="p-3 col-12 col-xl-12">

                <!-- <div class="mb-4">
                    <button @click="which = 'Day Group'" :class="[which == 'Day Group' ? 'btn-dark' : 'btn-outline-dark']" class="btn btn-sm me-2">Day Group</button>
                    <button @click="which = 'Date Range'" :class="[which == 'Date Range' ? 'btn-dark' : 'btn-outline-dark']" class="btn btn-sm me-2">Date Range</button>
                    <button @click="which = 'Single Day'" :class="[which == 'Single Day' ? 'btn-dark' : 'btn-outline-dark']" class="btn btn-sm me-2">Single Day</button>
                </div>
                

                <div v-if="which == 'Day Group'">Day Group</div>
                <div v-if="which == 'Date Range'">Date Range</div>
                <div v-if="which == 'Single Day'">Single Day</div> -->


                <div v-if="dates.length > 0" class="row g-3">
                    <forms-select-field name="day_type" label="Day Type" v-model="fd.day_type" error="" classes="col-12 col-xl-4" :options="[
                        {key: 'Weekoff', val: 'Weekoff'},
                        {key: 'Holiday', val: 'Holiday'},
                        {key: 'Halfday', val: 'Halfday'},
                    ]"></forms-select-field>
    
                    <forms-text-field name="remark" label="Remark" v-model="fd.remark" error="" classes="col-12 col-xl-8"></forms-text-field>

                    <forms-submit-button name="" label="Save Days" @click="save()" classes="col-auto btn-lg py-2"></forms-submit-button>

                    <div class="col-auto py-2">
                        <button @click="deleteRecords()" class="btn btn-danger">Delete Records</button>
                    </div>


                </div>

                <div>Holidays

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="sd in special_days" :key="sd.id">
                                <tr  v-if="sd.day_type == 'Holiday' &&  isValidRecord(sd.special_day)">
                                    <td>{{ sd.special_day }}</td>
                                    <td>{{ sd.day_type }}</td>
                                    <td>{{ sd.remark }}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                </div>

                <!-- <div v-for="date, ind in dates" :key="ind">
                    {{ date }}
                </div> -->

            </div>

        </div>

    </div>
</template>

<script>
import axios from 'axios';

export default {

    data(){
        return {
            fd: {
                day_type: null,
                remark: null
            },

            currentYear: null,
            currentMonth: 0,
            months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            month_numbers: ['01','02','03','04','05','06','07','08','09','10','11','12'],
            days: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            day_count: [31,28,31,30,31,30,31,31,30,31,30,31],
            isLeapYear: false,
            firstDay: null,

            dates: [],
            special_days: [],

        };
    },

    methods: {

        isValidRecord(dt){
            let d = new Date(dt);
            return d.getMonth() == this.currentMonth;
        },

        checkData(dt){
            if(this.currentYear && dt > 0){

                if(dt < 10){
                    dt = this.month_numbers[dt - 1]
                }

                let newDT = this.currentYear.key + '-' + this.month_numbers[this.currentMonth] + '-' + dt;

                let a =  this.special_days.some(sday => sday.special_day === newDT);

                let b = null;
                this.special_days.forEach(item => {
                    if(item.special_day == newDT){
                        b = item.day_type;
                    }
                })
                return b;

            }
            
        },

        fetch(){
            axios.get('/calender/special_day/fetch/' + this.currentYear.key)
            .then(res => {
                this.special_days = res.data;
            });
        },

        deleteRecords(){
            let data = this.fd;
            data.dates = this.dates;
            axios.post('/calender/special_day/delete', data)
            .then(res => {
                this.fetch();
                this.reset();
            });
        },

        reset(){
            this.fd.day_type = null;
            this.fd.remark = null;
            this.dates = [];
        },

        save(){
            let data = this.fd;
            data.dates = this.dates;
            axios.post('/calender/special_day/save', data)
            .then(res => {
                this.fetch();
                this.reset();
            });
        },

        sendIfExist(dt){
            if(this.currentYear && dt > 0){
                let newDT = this.currentYear.key + '-' + this.month_numbers[this.currentMonth] + '-' + dt;
                if(this.dates.includes(newDT)){
                    return true;
                }
            }
            return false;
        },

        addRemoveDate(dt){
            if(this.currentYear && dt > 0){
                let newDT = this.currentYear.key + '-' + this.month_numbers[this.currentMonth] + '-' + dt;
                if(!this.dates.includes(newDT)){
                    this.dates.push(newDT);
                } else {
                    this.dates.splice(this.dates.indexOf(newDT), 1);
                }
            }
        },

        setFirstDay(){
            let dt = this.currentYear.key + "-" + this.month_numbers[this.currentMonth] + "-01";
            let d = new Date(dt);
            this.firstDay = d.getDay();
        },

        navigateMonth(what){
            if(what == 'Prev' && this.currentMonth > 0){
                this.currentMonth--;
                this.setFirstDay();
            }
            if(what == 'Next' && this.currentMonth < 11){
                this.currentMonth++;
                this.setFirstDay();
            }
        },

        onYearChange(e){
            this.currentYear = e;
            if(this.currentYear != null){
                let r = parseInt(this.currentYear.key) % 4;
                this.day_count[1] = r == 0 ? 29 : 28;
                let d = new Date();
                this.currentMonth = d.getMonth();
                this.setFirstDay();
                this.fetch();
                this.reset();

                
            }
        },

    },

    created(){
    },

    computed: {
    },

}
</script>