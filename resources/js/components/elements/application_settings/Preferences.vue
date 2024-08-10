<template>
    <div class="container-fluid">
        <div class="row align-items-center p-4 my-0" v-for="setting, ind in settings" :key="setting" :class="ind%2 == 0? 'bg-light' : ''">
            <div class="col">
                {{ setting.key }}
            </div>
            <div class="col-auto">
                <input @input="save(ind)" v-if="setting.type == 'Input'" v-model="setting.val" type="text" style="width: 180px;" class="p-1 px-2 rounded">

                <select 
                v-if="setting.type == 'Select'" 
                v-model="setting.val" 
                @change="save(ind)"
                style="width: 180px;"
                :id="ind"
                class="p-1 px-2 rounded">
                    <option v-for="opt in setting.options" :key="opt" :value="opt.val">{{ opt.key }}</option>
                </select>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    data(){
        return {

            yesNo: [
                {key: 'Yes', val: 'Yes'},
                {key: 'No', val: 'No'},
            ],

            hourMinute: [
                {key: 'Hour', val: 'Hour'},
                {key: 'Minute', val: 'Minute'},
            ],

            daysForSalary: [
                {key: 'Actual Days', val: 'Actual Days'},
                {key: 'Working Days', val: 'Working Days'},
            ],

            days: [
                {key: 1, val: 1},
                {key: 2, val: 2},
                {key: 3, val: 3},
                {key: 4, val: 4},
                {key: 5, val: 5},
                {key: 6, val: 6},
                {key: 7, val: 7},
                {key: 8, val: 8},
                {key: 9, val: 9},
                {key: 10, val: 10},
                {key: 11, val: 11},
                {key: 12, val: 12},
                {key: 13, val: 13},
                {key: 14, val: 14},
                {key: 15, val: 15},
                {key: 16, val: 16},
                {key: 17, val: 17},
                {key: 18, val: 18},
                {key: 19, val: 19},
                {key: 20, val: 20},
                {key: 21, val: 21},
                {key: 22, val: 22},
                {key: 23, val: 23},
                {key: 24, val: 24},
                {key: 25, val: 25},
                {key: 26, val: 26},
                {key: 27, val: 27},
                {key: 28, val: 28},
                {key: 29, val: 29},
                {key: 30, val: 30},
                {key: 31, val: 31}
            ],

            settings: [
                {type: 'Input', key: "Late Days", val: 3},
                {type: 'Input', key: "Late Minutes", val: 5},
                {type: 'Select', key: "Calculate Late Day's Salary on Pro-rata basis", val: 'Yes', options: []},
                {type: 'Select', key: "On Late Calculate LOP as per", val: 'Hour', options: []},
                {type: 'Input', key: "Penalty On Late Mark in LOP", val: 1},
                {type: 'Input', key: "Early Going Days", val: 3},
                {type: 'Input', key: "Early Going Minutes", val: 5},
                {type: 'Select', key: "Calculate Early Going Day's Salary on Pro-rata basis", val: 'Yes', options: []},
                {type: 'Select', key: "On Early Going Calculate LOP as per", val: 'Hour', options: []},
                {type: 'Input', key: "Penalty On Early Going Mark in LOP", val: 1},
                {type: 'Select', key: "Working Days Consideration", val: 'Working Days', options: []},
                {type: 'Select', key: "Salary Cycle Start Date", val: 1, options: []},
                {type: 'Select', key: "Salary Release Date", val: 1, options: []},
            ],
        };
    },

    methods: {
        save(ind){
            axios.post('/application_settings/preference/save', {
                key: this.settings[ind].key,
                value: this.settings[ind].val
            }).then(res => {
                console.log(res);
            });
        },

        async fetch(){
            let resp = await axios.get('/application_settings/preference/fetch').then(res => res.data);
            if(resp){
                resp.forEach(item => {
                    let pos = this.settings.map(e => e.key).indexOf(item.key);
                    this.settings[pos].val = item.value;
                });
            }
            
        },
        
    },

    created(){
        this.settings[2].options = this.yesNo;
        this.settings[3].options = this.hourMinute;
        this.settings[7].options = this.yesNo;
        this.settings[8].options = this.hourMinute;
        this.settings[10].options = this.daysForSalary;
        this.settings[11].options = this.days;
        this.settings[12].options = this.days;
        this.fetch();
    },

    mounted(){
    },
}
</script>