<template>
    <div class="container-fluid">

        <!-- Form -->
        <div  v-if="item" class="row align-items-center g-4 mb-5">

            


            <div class="col-auto py-4">
                <div class="row align-items-center justify-content-start">
                    <div class="col-auto">
                        <button @click="shift_dates('prev')" class="btn btn-dark">PREV</button>
                    </div>
                    <!-- <div class="col-auto h4 m-0">
                        {{ item.from }} - {{ item.to }}
                    </div> -->
                    
                    <forms-date-field @change="getData()" name="from" label="From" v-model="item.from" error="" classes="col-auto"></forms-date-field>

                    <forms-date-field @change="getData()" name="to" label="To" v-model="item.to" error="" classes="col-auto"></forms-date-field>

                    <div class="col-auto">
                        <button @click="shift_dates('next')" class="btn btn-dark">NEXT</button>
                    </div>
                    <div class="col-auto">
                        <button @click="evalutePayCycle()" :disabled="loading" class="btn btn-primary">
                            <span v-if="!loading">Evalute Pay Cycle</span>
                            <span v-if="loading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span v-if="loading" role="status"> Evaluting</span>
                        </button>

                    </div>
                </div>
            </div>

        </div>

        <!-- Data -->
         <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr class="table-dark">
                        <th>Employee</th>
                        <th>Code</th>
                        <th v-for="dt, ind in dates" :key="dt" class="position-relative">
                            <span class="text-nowrap v">{{ ddmmyyyys[ind] }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="employee in employees" :key="employee.id">
                        <td class="text-nowrap">{{ employee.first_name }} {{ employee.middle_name }} {{ employee.last_name }}</td>
                        <td>{{ employee.employee_code }}</td>
                        <td v-for="dt in dates" :key="dt" class="position-relative">
                            <div :set="obj = getStatus(dt, employee.employee_shifts)" class="">
                                <div v-if="obj">
                                    <div class="text-nowrap">{{ obj.status ? obj.status : 'Pending' }}</div>
                                    <div v-for="att in obj.employee_attendance" :key="att" class="">{{ att.tm }}</div>
                                    <div class="text-nowrap">Late: {{ obj.late }}min</div>
                                    <div class="text-nowrap">Early: {{ obj.early }}min</div>
                                    <div class="text-nowrap">LOP: {{ obj.lop }}</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
         </div>

    </div>
</template>

<script>
export default {

    props: ['from', 'to'],

    data(){
        return {
            loading: false,
            item: {
                from: null,
                to: null,
            },
            employees: [],
            dates: [],
            dds: [],
            ddmmyyyys: [],
        };
    },

    methods: {

        evalutePayCycle(){
            this.loading = true;
            let eids = [];
            this.employees.forEach(emp => {
                eids.push(emp.id);
            });
            let data = {
                from: this.item.from,
                to: this.item.to,
                eids: eids,
            };
            axios.post('/attendance_evalution_report/run_lop', data).then(res=> {
                this.loading = false;
                this.getData();
            });
        },

        shift_dates(what){
            axios.post('/overview/run_payroll/shift_dates', { from: this.item.from, what: what }).then(res=> {
                this.item.from = res.data.from;
                this.item.to = res.data.to;
                this.getData();
            });
        },

        getStatus(dt, data){
            let a = data.filter(d => {
                let dd = new Date(d.dt).getTime();
                let ddd = new Date(dt).getTime();
                if(dd == ddd){
                    return d;
                }
            });

            return a.length > 0 ? a[0] : null;
        },

        getData(){
            axios.get('/attendance_evalution_report/get_data', {
                params: this.item
            })
            .then(res => {
                this.employees = res.data.employees;
                this.dates = res.data.dates;
                this.dds = res.data.dds;
                this.ddmmyyyys = res.data.ddmmyyyys;
            });
        },

    },

    created(){
        this.item.from = this.from;
        this.item.to = this.to;
        this.getData();
    },

}
</script>