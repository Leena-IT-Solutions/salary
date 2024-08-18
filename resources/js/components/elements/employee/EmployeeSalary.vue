<template>
    <div class="container-fluid">
        
        <!-- Form -->
        <div  v-if="item" class="row g-4 mb-5">

            <forms-select-field @change="startCalculation()" name="salary_group_id" label="Salary Group" v-model="item.salary_group_id" error="" classes="col-12 col-xl-4" :options="salary_groups"></forms-select-field>

            <forms-number-field @change="startCalculation()" name="ctc" label="Cost to Company - CTC per Month" v-model="item.ctc" error="" classes="col-12 col-lg-4"></forms-number-field>

            <forms-date-field name="effective_from" label="Effective From" v-model="item.effective_from" error="" classes="col-12 col-lg-4"></forms-date-field>

            <forms-text-field name="note" label="Note" v-model="item.note" error="" classes="col-12"></forms-text-field>

            <forms-submit-button name="" v-model="loading" label="Save" @click="save()" classes="col-6"></forms-submit-button>

            <div class="col-6 text-end">
                <button v-if="item.id != null && !isDelete" class="btn btn-danger" @click="deleteItem()">Delete Item</button>
                <button v-if="item.id != null && isDelete" class="btn btn-danger" @click="deleteNow()">Confirm & Delete</button>
            </div>

        </div>

        <!-- Data -->
        <table class="table table-sm table-bordered mb-5" v-if="item.ctc > 0">

            <!-- Salary Types -->
            <thead>
                <tr>
                    <th class="h5 fw-bold py-3" colspan="2">Pay Types</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="">Cost to Company CTC</td>
                    <td class="text-end">{{ item.ctc }}</td>
                </tr>
                <tr>
                    <td class="">Gross Pay</td>
                    <td class="text-end">{{ item.gross_pay }}</td>
                </tr>
                <tr>
                    <td class="">Basic Pay</td>
                    <td class="text-end">{{ item.basic_pay }}</td>
                </tr>
                <tr>
                    <td class="">Net Pay</td>
                    <td class="text-end">{{ item.net_pay }}</td>
                </tr>
                <tr>
                    <td class="">Checking Gross Pay</td>
                    <td class="text-end">{{ item.checking_gross_pay }}</td>
                </tr>
            </tbody>

            <!-- Main Calculations -->
            <thead>
                <tr>
                    <th class="h5 fw-bold py-3" colspan="2">Salary Main Components</th>
                </tr>
            </thead>
            <tbody>
                
                <tr>
                    <td class="">Employer Contribution</td>
                    <td class="text-end">{{ this.item.employer_contribution }}</td>
                </tr>
                <tr>
                    <td class="">Remaining Amount</td>
                    <td class="text-end">{{ this.item.remaining_amount }}</td>
                </tr>
                <tr>
                    <td class="">Earnings Amount</td>
                    <td class="text-end">{{ this.item.earnings_total }}</td>
                </tr>
                <tr>
                    <td class="">Total Gross Percentage</td>
                    <td class="text-end">{{ this.item.total_gross_percentage }}</td>
                </tr>
                <tr>
                    <td class="">Overtime Per Hour Rate</td>
                    <td class="text-end">{{ item.per_hour }}</td>
                </tr>
                <tr>
                    <td class="">Overtime Per Minute Rate</td>
                    <td class="text-end">{{ item.per_minute }}</td>
                </tr>
            </tbody>

            <!-- Earning Components -->
            <thead>
                <tr>
                    <th class="h5 fw-bold py-3" colspan="2">Earning Components</th>
                </tr>
            </thead>
            <tbody>
                <template v-for="earning in earnings" :key="earning.id">
                    <tr v-if="earning">
                        <td class="">{{ earning.name }}</td>
                        <td class="text-end">{{ earning.monthly }}</td>
                    </tr>
                </template>
                <tr>
                    <td class="">Special Allowance</td>
                    <td class="text-end">{{ this.item.remaining_amount }}</td>
                </tr>
            </tbody>

            <!-- Statutory Components -->
            <thead>
                <tr>
                    <th class="h5 fw-bold py-3" colspan="2">Statutory Components</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="statu in statutories" :key="statu.id">
                    <td class="">{{ statu.scheme_name }}</td>
                    <td class="text-end">{{ statu.employer_contribution }} | {{ statu.employee_contribution }}</td>
                </tr>
            </tbody>

            <!-- Services -->
            <thead>
                <tr>
                    <th class="h5 fw-bold py-3" colspan="2">Services Available</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="service in services" :key="service.id">
                    <td class="">{{ service.name }}</td>
                    <td class="text-end">{{ service.monthly }}</td>
                </tr>
            </tbody>

            <!-- Reimbursement -->
            <thead>
                <tr>
                    <th class="h5 fw-bold py-3" colspan="2">Reimbursements</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="reim in reimbursements" :key="reim.id">
                    <td class="">{{ reim.name }}</td>
                    <td class="text-end">{{ reim.value }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Search -->
        <div class="row mb-4">
            
            <forms-select-field name="column" label="Column"  placeholder=""
            v-model="params.key" 
            error="" 
            classes="col" 
            :options="[{key: 'ID', val: 'id'},{key: 'Department', val: 'department'},{key: 'Code', val: 'code'},]"></forms-select-field>

            <forms-text-field name="search" label="Type Search Sring" v-model="params.value" error="" classes="col"></forms-text-field>

            <div class="col-auto">
                <button class="btn btn-primary h-100" @click="search()">Search</button>
            </div>
        </div>

        <!-- Fetch Data -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th @click="orderBy('id')" class="cursor-pointer" style="width: 60px;">ID</th>
                        <th @click="orderBy('ctc')" class="cursor-pointer">Per Month CTC</th>
                        <th @click="orderBy('gross_pay')" class="cursor-pointer">Gross Pay</th>
                        <th @click="orderBy('basic_pay')" class="cursor-pointer">Basic Pay</th>
                        <th @click="orderBy('effective_from')" class="cursor-pointer">Effective From</th>
                        <th class="text-end" style="width: 120px;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="row in items" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.ctc }}/-</td>
                        <td>{{ row.gross_pay }}</td>
                        <td>{{ row.basic_pay }}</td>
                        <td>{{ row.effective_from }}</td>
                        <td class="text-end">
                            <button class="btn btn-outline-info btn-sm me-2" @click="edit(row)"><i class="bi bi-pencil"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center">
                <button class="btn btn-dark" :disabled="next_page_url == null" @click="fetch()">Load More</button>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';
export default {

    props: ['salary_groups', 'employee'],

    data(){
        return {
            loading: false,
            isDelete: false,
            item: {
                id: null,
                employee_id: this.employee.id,
                salary_group_id: null,
                effective_from: null,
                note: null,
                
                ctc: 0,
                checking_gross_pay: null,
                gross_pay: null,
                basic_pay: null,
                net_pay: null,
                employer_contribution: null,
                remaining_amount: null,
                earnings_total: null,
                total_gross_percentage: null,
                per_hour: null,
                per_minute: null,
            },
            items: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: null,
                value: null,
                by: 'id',
                order: 'desc',
                rows: 1,
            },

            earnings: [],
            services: [],
            reimbursements: [],
            statutories: [],
            multiplier: 0,
        };
    },

    methods: {

        reset(){
            this.item.id = null;
            this.item.ctc = null;
            this.item.salary_group_id = null;
            this.item.effective_from = null;
            this.item.note = null;

            this.earnings = [];
            this.services = [];
            this.reimbursements = [];
            this.statutory = [];
            this.item.checking_gross_pay = 0;
            this.item.gross_pay = 0;
            this.item.basic_pay = 0;
            this.item.net_pay = 0;
            this.item.employer_contribution = 0;
            this.item.remaining_amount = 0;
            this.item.earnings_total = 0;
            this.item.total_gross_percentage = 0;
            this.item.per_hour = 0;
            this.item.per_minute = 0;
        },

        edit(item){
            this.item.id = item.id;
            this.item.employee_id = item.employee_id;
            this.item.ctc = item.ctc;
            this.item.salary_group_id = item.salary_group_id;
            this.item.effective_from = item.effective_from;
            this.item.note = item.note;
            this.startCalculation();
        },

        async getSalaryComponents(){
            let response = null;
            response = await axios.get('/employee/salary_group/'+this.item.salary_group_id+'/salary_group').then(res => res);
            this.earnings = response.data.earnings
            this.services = response.data.services
            this.reimbursements = response.data.reimbursements
            this.statutories = response.data.statutories
            this.multiplier = response.data.multiplier;
            this.calculateSalary();
        },

        fetch(){
            let url = '/employee/salary_group/'+this.employee.id+'/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.next_page_url != null && this.current_page == 1){
                    this.items = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.items.push(item);
                    });
                }

                this.loading = false;
            });
        },

        save(){
            axios.post('/employee/salary_group/employee_salary/save', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.items = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/employee/salary_group/delete', this.item).then(res => {
                this.reset();
                this.search();
            });
        },

        startCalculation(){
            this.earnings = [];
            this.services = [];
            this.reimbursements = [];
            this.statutory = [];
            this.item.checking_gross_pay = 0;
            this.item.gross_pay = 0;
            this.item.basic_pay = 0;
            this.item.net_pay = 0;
            this.item.employer_contribution = 0;
            this.item.remaining_amount = 0;
            this.item.earnings_total = 0;
            this.item.total_gross_percentage = 0;
            this.item.per_hour = 0;
            this.item.per_minute = 0;
            if((this.item.ctc != null && this.item.ctc != "") && (this.item.salary_group_id != null && this.item.salary_group_id != "")){
                this.getSalaryComponents();
            }
        },

        calculateSalary(){
            this.calculateEarnings();
        },

        calculateBasicPay(){
            this.earnings.forEach(earning => {
                if(earning.is_active && earning.is_ctc && earning.is_basic_pay){
                    this.item.basic_pay += earning.monthly;
                }
            });
        },

        calculateEarningsTotal(){
            let earnings_total = 0;
            this.earnings.forEach(earning => {
                earnings_total += earning.monthly;
            });
            return earnings_total;
        },

        calculateEarnings(){
            this.earnings.forEach(earning => {
                if(earning.calculation == "Flat" && earning.is_active && earning.is_ctc){
                    earning.monthly = earning.value;
                }
                if(earning.calculation == "CTC" && earning.is_active && earning.is_ctc){
                    earning.monthly = (this.item.ctc * earning.value) / 100;
                }
            });

            this.calculateBasicPay();

            this.earnings.forEach(earning => {
                if(earning.calculation == "Basic" && earning.is_active && earning.is_ctc){
                    earning.monthly = (this.item.basic_pay * earning.value) / 100;
                }
            });

            this.item.earnings_total = this.calculateEarningsTotal();

            this.calculateStatutoryComponents();
        },

        calculateAverage(CSVstr){
            let str = CSVstr;
            let arr = str.split(",");
            let sum = 0;
            arr.forEach(val => {
                sum += (val*1);
            });
            return sum/arr.length;
        },

        calculateEmployerContribution(){
            let ec = 0;
            this.statutories.forEach(row => {
                ec += row.employer_contribution;
            });
            return ec;
        },

        calculateStatutoryComponents(){
            this.statutories.forEach(row => {
                if(row.is_active && row.is_part_of_salary){
                    row.statutory_compliance_conditions.forEach(cond => {
                        if(
                            cond.is_active && 
                            (cond.gender == "All" || cond.gender == this.employee.gender) && 
                            (cond.state == "All" || this.employee.employee_work_location.work_location.state) &&
                            (cond.employer_contribution != null && cond.employer_contribution != 0) &&
                            cond.salary_type != "Gross Pay"
                        ){
                            if(cond != null){
                                let salary_amount = 0;
                                let monthly = 0;
                                let min = cond.min_salary != null ? cond.min_salary : 0;
                                let max = cond.max_salary != null ? cond.max_salary : 0;

                                if(cond.salary_type == "Basic Pay"){
                                    salary_amount = this.item.basic_pay;
                                } else if(cond.salary_type == "CTC"){
                                    salary_amount = this.item.ctc;
                                } else if(cond.salary_type == "None"){
                                    salary_amount = 0;
                                }

                                if(salary_amount >= min && (max == 0 || salary_amount <= max )){

                                    if(cond.calculation == "Flat"){
                                        monthly = cond.employer_contribution;
                                    }

                                    if(cond.calculation == "CSV"){
                                        monthly = this.calculateAverage(cond.employer_contribution);
                                    }

                                    if(cond.calculation == "Percentage"){
                                        monthly = salary_amount * cond.employer_contribution / 100;
                                    }

                                    if(cond.max_employer_contribution != null && cond.max_employer_contribution != 0){
                                        if(cond.max_employer_contribution < monthly){
                                            monthly = cond.max_employer_contribution;
                                        }
                                    }
                                    row.employer_contribution = Math.round(monthly);

                                }
                            }
                        }
                    });
                }
            });

            this.item.checking_gross_pay =this.item.ctc - this.calculateEmployerContribution();

            this.statutories.forEach(row => {
                if(row.is_active && row.is_part_of_salary){
                    row.statutory_compliance_conditions.forEach(cond => {

                        if(
                            cond.is_active && 
                            (cond.gender == "All" || cond.gender == this.employee.gender) && 
                            (cond.state == "All" || this.employee.employee_work_location.work_location.state) &&
                            (cond.employer_contribution != null && cond.employer_contribution != 0) &&
                            cond.salary_type == "Gross Pay" && 
                            cond.calculation != "Percentage"
                        ){
                            if(cond != null){

                                let monthly = 0;
                                let min = cond.min_salary != null ? cond.min_salary : 0;
                                let max = cond.max_salary != null ? cond.max_salary : 0;
                                let salary_amount = this.item.checking_gross_pay;

                                if(salary_amount >= min && (max == 0 || salary_amount <= max )){

                                    if(cond.calculation == "Flat"){
                                        monthly = cond.employer_contribution;
                                    }

                                    if(cond.calculation == "CSV"){
                                        monthly = this.calculateAverage(cond.employer_contribution);
                                    }

                                    if(cond.max_employer_contribution != null && cond.max_employer_contribution != 0){
                                        if(cond.max_employer_contribution < monthly){
                                            monthly = cond.max_employer_contribution;
                                        }
                                    }
                                    row.employer_contribution = Math.round(monthly);

                                }

                            }
                        }

                        this.item.checking_gross_pay =this.item.ctc - this.calculateEmployerContribution();

                        if(
                            cond.is_active && 
                            (cond.gender == "All" || cond.gender == this.employee.gender) && 
                            (cond.state == "All" || this.employee.employee_work_location.work_location.state) &&
                            (cond.employer_contribution != null && cond.employer_contribution != 0) &&
                            cond.salary_type == "Gross Pay" && 
                            cond.calculation == "Percentage"
                        ){
                            if(cond != null){

                                let salary_amount = this.item.checking_gross_pay;
                                let min = cond.min_salary != null ? cond.min_salary : 0;
                                let max = cond.max_salary != null ? cond.max_salary : 0;

                                if(salary_amount >= min && (max == 0 || salary_amount <= max )){
                                    row.is = true;
                                    this.item.total_gross_percentage += cond.employer_contribution * 1;
                                }

                            }
                        }

                    });
                }
            });

            this.calculateFinalGrossSalary();
            
        },

        calculateFinalGrossSalary(){
            let x = ((this.item.ctc - this.calculateEmployerContribution()) - (this.item.earnings_total + this.calculateEmployerContribution())) / 2;
            let y = (this.item.ctc - this.calculateEmployerContribution()) / ((x/this.item.total_gross_percentage) * (100 + this.item.total_gross_percentage));
            let z = y * x;
            this.item.gross_pay = Math.round(this.item.checking_gross_pay - z);
            this.item.remaining_amount = Math.round(this.item.gross_pay - this.item.earnings_total);
            this.calculateAfterGrossSalary();
            this.item.employer_contribution = this.calculateEmployerContribution();
            this.calculateOTRates();
            this.calculateServices();
            this.calculateEmployeeContribution();
        },

        calculateAfterGrossSalary(){
            this.statutories.forEach(row => {
                
                if(row.is_active){
                    row.statutory_compliance_conditions.forEach(cond => {
                        if(
                            cond.is_active && 
                            (cond.gender == "All" || cond.gender == this.employee.gender) && 
                            (cond.state == "All" || this.employee.employee_work_location.work_location.state) &&
                            (cond.employer_contribution != null && cond.employer_contribution != 0) &&
                            cond.salary_type == "Gross Pay" && 
                            cond.calculation == "Percentage"
                        ){
                            if(cond != null){
                                let monthly = 0;
                                
                                if(row.is){
                                    monthly = this.item.gross_pay * cond.employer_contribution / 100;
                                }
                                
                                if(cond.max_employer_contribution != null && cond.max_employer_contribution != 0){
                                    if(cond.max_employer_contribution < monthly){
                                        monthly = cond.max_employer_contribution;
                                    }
                                }
                                row.employer_contribution = Math.round(monthly);
                            }
                        }
                    });
                }

            });
        },

        calculateOTRates(){
            this.item.per_hour = ((this.item.gross_pay * this.multiplier) / (30.4*8)).toFixed(2);
            this.item.per_minute = ((this.item.gross_pay * this.multiplier) / (30.4*8*60)).toFixed(2);
        },

        calculateServices(){
            this.services.forEach(service => {
                if(service.is_active){
                    if(service.calculation == "Flat"){
                        service.monthly = service.value;
                    }
                    if(service.calculation == "Basic"){
                        service.monthly = (this.item.basic_pay * service.value) / 100;
                    }
                    if(service.calculation == "CTC"){
                        service.monthly = (this.item.ctc * service.value) / 100;
                    }
                }
            });
        },

        calculateEmployeeContribution(){
            this.statutories.forEach(row => {
                if(row.is_active && row.is_part_of_salary){
                    row.statutory_compliance_conditions.forEach(cond => {
                        if(
                            cond.is_active && 
                            (cond.gender == "All" || cond.gender == this.employee.gender) && 
                            (cond.state == "All" || this.employee.employee_work_location.work_location.state) &&
                            (cond.employee_contribution != null && cond.employee_contribution != 0)
                        ){

                            let salary_amount = 0;
                            let min = cond.min_salary != null ? cond.min_salary : 0;
                            let max = cond.max_salary != null ? cond.max_salary : 0;
                            if(cond.salary_type == "CTC"){}
                            if(cond.salary_type == "Gross Pay"){}
                            if(cond.salary_type == "Basic Pay"){}

                            if(cond.salary_type == "Basic Pay"){
                                salary_amount = this.item.basic_pay;
                            } else if(cond.salary_type == "CTC"){
                                salary_amount = this.item.ctc;
                            } else if(cond.salary_type == "Gross Pay"){
                                salary_amount = this.item.gross_pay;
                            } else if(cond.salary_type == "None"){
                                salary_amount = 0;
                            }

                            if(salary_amount >= min && (max == 0 || salary_amount <= max )){}

                            if(cond.calculation == "Flat"){
                                row.employee_contribution = Math.round(cond.employee_contribution * 1);
                            }

                            if(cond.calculation == "Percentage"){
                                if(row.is && cond.salary_type == "Gross Pay"){
                                    row.employee_contribution = Math.round(salary_amount * cond.employee_contribution / 100);
                                }
                            }

                            if(cond.calculation == "Percentage"){
                                if(cond.salary_type == "Basic Pay" || cond.salary_type == "CTC"){
                                    row.employee_contribution = Math.round(salary_amount * cond.employee_contribution / 100);
                                }
                            }

                            if(cond.calculation == "CSV"){
                                row.employee_contribution = Math.round(this.calculateAverage(cond.employee_contribution));
                            }
                        }
                    });
                }
            });

            this.item.net_pay = this.item.gross_pay - this.calculateEmployeeContributionTotal();
        },

        calculateEmployeeContributionTotal(){
            let employeect = 0;
            this.statutories.forEach(statutory => {
                employeect += statutory.employee_contribution;
            });
            return employeect;
        },

        showEmployeeSalary(){
            if(this.employee.employee_salary != null){
                this.item.ctc = this.employee.employee_salary.ctc;
                this.item.salary_group_id = this.employee.employee_salary.salary_group_id;
                this.item.effective_from = this.employee.employee_salary.effective_from;
                this.item.note = this.employee.employee_salary.note;
                this.startCalculation();
            }
        },

    },

    created(){
        this.item.employee_id = this.employee.id;
        this.fetch();
    },

}
</script>