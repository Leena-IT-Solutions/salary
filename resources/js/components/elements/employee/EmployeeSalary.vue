<template>
    <div class="container-fluid">
        
        <!-- Form -->
        <div  v-if="item" class="row g-4 mb-5">

            <forms-text-field name="employee_id" label="Employee ID" v-model="item.employee_id" error="" classes="col-12 col-lg-4"></forms-text-field>

            <forms-number-field name="employer_contribution" label="Employer Contribution" v-model="item.employer_contribution" error="" classes="col-12 col-lg-4"></forms-number-field>

            <forms-number-field name="gross" label="Gross Salary" v-model="item.gross" error="" classes="col-12 col-lg-4"></forms-number-field>

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

        <!-- Show Salary Components -->
        <div class="mb-3">
            <h3 class="m-0">Salary Main Components</h3>
        </div>

        <ul class="list_group">
            <li class="list_group_item">Cost to Company CTC: {{ this.item.ctc }}</li>
            <li class="list_group_item">Gross Pay: {{ this.item.gross }}</li>
            <li class="list_group_item">Basic Pay: {{ this.item.basic_pay }}</li>
            <li class="list_group_item">Employer Contribution: {{ this.item.employer_contribution }}</li>
            <li class="list_group_item">Remaining Amount: {{ this.item.remaining_amount }}</li>
            <li class="list_group_item">Earnings Amount: {{ this.item.earnings_total }}</li>
            <li class="list_group_item">Total Gross Percentage: {{ this.item.total_gross_percentage }}</li>
        </ul>

        <div class="mb-3">
            <h3 class="m-0">Earnings</h3>
        </div>
        <div v-for="earning in earnings" :key="earning.id" class="row g-2 mb-4">
            <div class="col-12">
                {{ earning.earning.name }} - {{ earning.monthly }}
            </div>
            <!-- <div class="col-12">
                {{ earning }}
            </div> -->
        </div>

        <div class="mb-3">
            <h3 class="m-0">Services</h3>
        </div>
        <div v-for="service in services" :key="service.id" class="row g-2 mb-4">
            <div class="col-12">
                {{ service.service.name }} - {{ service.monthly }}
            </div>
            <!-- <div class="col-12">
                {{ service }}
            </div> -->
        </div>

        <div class="mb-3">
            <h3 class="m-0">Statutory Components</h3>
        </div>
        <template v-for="statu in statutory" :key="statu.id">
            <div class="row g-2 mb-4" v-if="statu.monthly > 0">
                <div class="col-12">
                    {{ statu.statutory.scheme_name }} - {{ statu.monthly }}
                </div>
                <!-- <div class="col-12">
                    {{ statu }}
                </div> -->
            </div>
        </template>

        <div class="mb-3">
            <h3 class="m-0">Reimbursements</h3>
        </div>
        <div v-for="reim in reimbursements" :key="reim.id" class="row g-2 mb-4">
            <div class="col-12">
                {{ reim.reimbursement.name }} - {{ reim.monthly }}
            </div>
            <!-- <div class="col-12">
                {{ reim }}
            </div> -->
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
                ctc: null,
                employer_contribution: null,
                gross: null,
                basic_pay: null,
                remaining_amount: null,
                earnings_total: null,
                total_gross_percentage: null,
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
            statutory: [],
        };
    },

    methods: {

        async getSalaryComponents(){
            let response = null;
            response = await axios.get('/employee/salary_group_data/'+this.item.salary_group_id+'/fetch').then(res => res);
            response.data.forEach(item => {
                if(item.what == 'earning'){
                    this.earnings.push(item);
                }
                if(item.what == 'service'){
                    this.services.push(item);
                }
                if(item.what == 'reimbursement'){
                    this.reimbursements.push(item);
                }
                if(item.what == 'statutory'){
                    this.statutory.push(item);
                }
            });

            this.calculateSalary();
        },

        startCalculation(){
            this.earnings = [];
            this.services = [];
            this.reimbursements = [];
            this.statutory = [];
            
            this.item.basic_pay = 0;
            this.item.employer_contribution = 0;
            this.item.earnings_total = 0;
            this.item.total_gross_percentage = 0;
            this.item.remaining_amount = 0;
            this.item.gross = 0;

            if((this.item.ctc != null && this.item.ctc != "") && (this.item.salary_group_id != null && this.item.salary_group_id != "")){
                this.getSalaryComponents();
            }
        },

        calculateSalary(){
            this.calculateCTCComponents();
            this.calculateBasicSalaryComponents();
            this.calculateFlatAmountComponents();
            this.calculateEmployerContributionWithoutGross();
            this.calculateTotalGrossPercentage();
            this.calculateFinalGrossSalary();
            this.calculateAfterGrossSalary();
        },

        calculateCTCComponents(){
            this.earnings.forEach(item => {
                if(item.earning.calculation == "CTC" && item.earning.is_active && item.earning.is_part_of_salary && item.earning.is_epf){
                    let monthly = this.item.ctc * item.earning.value/100;
                    item.monthly = monthly;
                    this.item.basic_pay += monthly;
                    this.item.earnings_total += monthly;
                }
            });
            this.services.forEach(item => {
                if(item.service.calculation == "CTC" && item.service.is_active && item.service.is_part_of_salary && item.service.is_epf){
                    let monthly = this.item.ctc * item.service.value/100;
                    item.monthly = monthly;
                    this.item.basic_pay += monthly;
                }
            });
        },

        calculateBasicSalaryComponents(){
            this.earnings.forEach(item => {
                if(item.earning.calculation == "Basic" && item.earning.is_active && item.earning.is_part_of_salary){
                    let monthly = this.item.basic_pay * item.earning.value / 100;
                    item.monthly = monthly;
                    this.item.earnings_total += monthly;
                }
            });
            this.services.forEach(item => {
                if(item.service.calculation == "Basic" && item.service.is_active && item.service.is_part_of_salary){
                    let monthly = this.item.basic_pay * item.service.value / 100;
                    item.monthly = monthly;
                }
            });
        },

        calculateFlatAmountComponents(){
            this.earnings.forEach(item => {
                if(item.earning.calculation == "Flat" && item.earning.is_active && item.earning.is_part_of_salary){
                    let monthly = item.earning.value;
                    item.monthly = monthly;
                    this.item.earnings_total += monthly;
                }
            });
            this.services.forEach(item => {
                if(item.service.calculation == "Flat" && item.service.is_active){
                    let monthly = item.service.value;
                    item.monthly = monthly;
                }
            });
            this.reimbursements.forEach(item => {
                if(item.reimbursement.is_active){
                    let monthly = item.reimbursement.value;
                    item.monthly = monthly;
                }
            });
        },

        calculateEmployerContributionWithoutGross(){
            this.statutory.forEach(row => {
                
                if(row.statutory.is_active){
                    row.statutory.statutory_compliance_conditions.forEach(cond => {
                        if(
                            cond.is_active && 
                            (cond.gender == "All" || cond.gender == this.employee.gender) && 
                            (cond.state == "All" || this.employee.employee_work_location.work_location.state) &&
                            (cond.employer_contribution != null && cond.employer_contribution != 0) &&
                            cond.salary_type != "Gross Pay"
                        ){
                            if(cond != null){

                                let salary_amount = 0;
                                let min = 0;
                                let max = 0;
                                let monthly = 0;
                                if(cond.min_salary != null){
                                    min = cond.min_salary
                                }
                                if(cond.max_salary != null){
                                    max = cond.max_salary
                                }
                                if(cond.salary_type == "Basic Pay"){
                                    salary_amount = this.item.basic_pay;
                                }
                                if(cond.salary_type == "CTC"){
                                    salary_amount = this.item.ctc;
                                }

                                if(salary_amount >= min && (max == 0 || salary_amount <= max )){

                                    if(cond.calculation == "Flat"){
                                        monthly = cond.employer_contribution;
                                    }

                                    if(cond.calculation == "Percentage"){
                                        monthly = this.item.basic_pay * cond.employer_contribution / 100;
                                    }

                                    if(cond.max_employer_contribution != null && cond.max_employer_contribution != 0){
                                        if(cond.max_employer_contribution < monthly){
                                            monthly = cond.max_employer_contribution;
                                        }
                                    }
                                    row.monthly = Math.round(monthly);
                                    this.item.employer_contribution += monthly;

                                }

                            }
                            
                        }
                    });
                }

            });
        },

        calculateTotalGrossPercentage(){
            this.statutory.forEach(row => {
                
                if(row.statutory.is_active){
                    row.statutory.statutory_compliance_conditions.forEach(cond => {
                        if(
                            cond.is_active && 
                            (cond.gender == "All" || cond.gender == this.employee.gender) && 
                            (cond.state == "All" || this.employee.employee_work_location.work_location.state) &&
                            (cond.employer_contribution != null && cond.employer_contribution != 0) &&
                            cond.salary_type == "Gross Pay" && 
                            cond.calculation == "Percentage"
                        ){
                            if(cond != null){
                                this.item.total_gross_percentage += cond.employer_contribution * 1;
                            }
                        }
                    });
                }

            });
        },

        calculateFinalGrossSalary(){
            let x = ((this.item.ctc - this.item.employer_contribution) - (this.item.earnings_total + this.item.employer_contribution)) / 2;
            let y = (this.item.ctc - this.item.employer_contribution) / ((x/this.item.total_gross_percentage) * (100 + this.item.total_gross_percentage));
            let z = y * x;
            this.item.employer_contribution = Math.round(z + this.item.employer_contribution);
            this.item.gross = Math.round(this.item.ctc - this.item.employer_contribution);
            this.item.remaining_amount = Math.round(this.item.gross - this.item.earnings_total);
        },

        calculateAfterGrossSalary(){
            this.statutory.forEach(row => {
                
                if(row.statutory.is_active){
                    row.statutory.statutory_compliance_conditions.forEach(cond => {
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
                                monthly = this.item.gross * cond.employer_contribution / 100;
                                if(cond.max_employer_contribution != null && cond.max_employer_contribution != 0){
                                    if(cond.max_employer_contribution < monthly){
                                        monthly = cond.max_employer_contribution;
                                    }
                                }
                                row.monthly = Math.round(monthly);
                            }
                        }
                    });
                }

            });
        },

        save(){
            axios.post('/employee/salary_group_data/employee_salary/save', this.item).then(res => {
                console.log(res);
            });
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
        this.showEmployeeSalary();
    },

}
</script>