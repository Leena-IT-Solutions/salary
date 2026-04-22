<template>
    <div class="salary-architecture-suite">
        <!-- Configuration Section -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-0">
                <h6 class="fw-bold mb-0 text-dark">Compensation Structure Config</h6>
            </div>
            <div class="card-body p-4 bg-light-subtle">
                <div class="row g-4 align-items-end">
                    <forms-select-field @input="startCalculation()" name="salary_group_id" label="Salary Grade/Group" v-model="item.salary_group_id" :options="salary_groups" classes="col-12 col-lg-4"></forms-select-field>
                    
                    <forms-number-field @input="startCalculation()" name="ctc" label="Monthly CTC (Cost to Company)" v-model="item.ctc" placeholder="0.00" classes="col-12 col-lg-3"></forms-number-field>
                    
                    <forms-date-field name="effective_from" label="Implementation Date" v-model="item.effective_from" classes="col-12 col-lg-3"></forms-date-field>
                    
                    <div class="col-12 col-lg-2">
                        <forms-submit-button v-model="loading" label="Finalize Structure" @click="save()" classes="w-100 shadow-sm"></forms-submit-button>
                    </div>
                    
                    <div class="col-12">
                        <forms-text-field name="note" label="Configuration Notes" v-model="item.note" placeholder="Revision reasons or special conditions..." classes=""></forms-text-field>
                    </div>

                    <div class="col-12 text-end" v-if="item.id">
                        <button v-if="!isDelete" class="btn btn-outline-danger btn-sm rounded-pill px-4" @click="deleteItem()">Archive Record</button>
                        <button v-else class="btn btn-danger btn-sm rounded-pill px-4 animate-pulse" @click="deleteNow()">Confirm Deletion</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Breakdown -->
        <div v-if="item.ctc > 0" class="row g-4 mb-4">
            <!-- Summary Overview -->
            <div class="col-12">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="summary-card p-4 rounded-4 bg-primary text-white shadow-sm text-center">
                            <div class="small fw-bold opacity-75 text-uppercase mb-1">Gross Pay</div>
                            <div class="display-6 fw-900">{{ item.gross_pay }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card p-4 rounded-4 bg-success text-white shadow-sm text-center border">
                            <div class="small fw-bold opacity-75 text-uppercase mb-1">Take Home (Net)</div>
                            <div class="display-6 fw-900">{{ item.net_pay }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card p-4 rounded-4 bg-white border shadow-sm text-center">
                            <div class="small fw-bold text-muted text-uppercase mb-1">Basic Salary</div>
                            <div class="display-6 fw-900 text-dark">{{ item.basic_pay }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-card p-4 rounded-4 bg-dark text-white shadow-sm text-center">
                            <div class="small fw-bold opacity-75 text-uppercase mb-1">Hourly Rate</div>
                            <div class="h3 fw-900 mb-0">₹ {{ item.per_hour }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Breakdowns -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="fw-bold mb-0"><i class="bi bi-wallet2 me-2 text-primary"></i>Earnings Spectrum</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light small">
                                    <tr>
                                        <th class="ps-4">Component</th>
                                        <th class="text-end pe-4">Monthly Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="earning in earnings" :key="earning.id">
                                        <td class="ps-4 fw-semibold text-muted">{{ earning.name }}</td>
                                        <td class="text-end pe-4 fw-bold text-dark">{{ earning.monthly }}</td>
                                    </tr>
                                    <tr class="bg-light-subtle">
                                        <td class="ps-4 fw-bold text-primary">Special Allowance</td>
                                        <td class="text-end pe-4 fw-bold text-primary">{{ item.remaining_amount }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="fw-bold mb-0"><i class="bi bi-shield-check me-2 text-info"></i>Statutory Compliance</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 text-nowrap">
                                <thead class="bg-light small">
                                    <tr>
                                        <th class="ps-4">Compliance Scheme</th>
                                        <th class="text-center">Employer</th>
                                        <th class="text-end pe-4">Employee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="statu in statutories" :key="statu.id">
                                        <td class="ps-4 fw-semibold text-muted">{{ statu.scheme_name }}</td>
                                        <td class="text-center fw-bold text-danger">-{{ statu.employer_contribution }}</td>
                                        <td class="text-end pe-4 fw-bold text-danger">-{{ statu.employee_contribution }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-light-subtle">
                                    <tr>
                                        <td class="ps-4 fw-bold">Total Statutory Burden</td>
                                        <td colspan="2" class="text-end pe-4 fw-bold">
                                            Employer: {{ item.employer_contribution }} | Employee: {{ calculateEmployeeContributionTotal() }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services and OTs -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">Ancillary Components & Metrics</h6>
                        <span class="badge bg-soft-info text-info rounded-pill px-3">Derived Values</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4 text-center">
                            <div class="col-md-4 border-end" v-for="service in services" :key="service.id">
                                <div class="text-muted small fw-bold mb-1">{{ service.name }} Capability</div>
                                <div class="h5 fw-900 mb-0">₹ {{ service.monthly }}</div>
                            </div>
                            <div class="col-md-4" v-if="reimbursements.length">
                                <div class="text-muted small fw-bold mb-1">Reimbursement Limit</div>
                                <div class="h5 fw-900 mb-0">₹ {{ reimbursements[0].value }}</div>
                            </div>
                            <div class="col-md-4 border-start">
                                <div class="text-muted small fw-bold mb-1">Minute Overtime Rate</div>
                                <div class="h5 fw-900 mb-0">₹ {{ item.per_minute }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-2">
            <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">Compensation Revision Log</h6>
                <div class="input-group input-group-sm rounded-pill bg-light border-0 px-2" style="width: 200px;">
                    <span class="input-group-text bg-transparent border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-transparent border-0 shadow-none ps-0" placeholder="Filter revisions..." v-model="params.value" @keyup.enter="search()">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr class="text-uppercase tiny fw-800 text-muted">
                            <th class="ps-4">Rev. ID</th>
                            <th>Effective Date</th>
                            <th>CTC Structure</th>
                            <th>Gross/Net</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in items" :key="row.id" class="transition-all hover-glow border-bottom border-light">
                            <td class="ps-4"><span class="badge bg-white border text-muted px-2">#{{ row.id }}</span></td>
                            <td class="fw-bold text-dark">{{ row.effective_from }}</td>
                            <td>
                                <div class="fw-900 text-primary">₹ {{ row.ctc }}</div>
                                <div class="tiny text-muted fw-semibold">{{ row.salary_group_name }}</div>
                            </td>
                            <td>
                                <div class="small fw-bold">G: {{ row.gross_pay }} <span class="mx-1 text-light">|</span> N: {{ row.net_pay }}</div>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-white border shadow-sm rounded-circle p-2" @click="edit(row)">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="5" class="text-center py-5 opacity-25">
                                <i class="bi bi-journal-x display-2"></i>
                                <p class="mt-2 fw-bold">No salary history recorded</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
                id: null, employee_id: this.employee.id, salary_group_id: null, effective_from: null, note: null,
                ctc: 0, checking_gross_pay: null, new_checking_gross_pay: null, gross_pay: 0, basic_pay: 0, net_pay: 0,
                employer_contribution: 0, remaining_amount: 0, earnings_total: 0, total_gross_percentage: 0, per_hour: 0, per_minute: 0,
            },
            items: [],
            params: { key: null, value: null, by: 'id', order: 'desc', rows: 10 },
            earnings: [], services: [], reimbursements: [], statutories: [], multiplier: 0,
        };
    },
    methods: {
        reset(){
            this.item = {
                id: null, employee_id: this.employee.id, salary_group_id: null, effective_from: null, note: null,
                ctc: 0, checking_gross_pay: 0, new_checking_gross_pay: 0, gross_pay: 0, basic_pay: 0, net_pay: 0,
                employer_contribution: 0, remaining_amount: 0, earnings_total: 0, total_gross_percentage: 0, per_hour: 0, per_minute: 0,
            };
            this.earnings = []; this.services = []; this.reimbursements = []; this.statutories = [];
            this.isDelete = false;
        },
        edit(item){
            Object.keys(this.item).forEach(key => this.item[key] = item[key]);
            this.startCalculation();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        async getSalaryComponents(){
            let response = await axios.get('/employee/salary_group/'+this.item.salary_group_id+'/salary_group');
            this.earnings = response.data.earnings;
            this.services = response.data.services;
            this.reimbursements = response.data.reimbursements;
            this.statutories = response.data.statutories;
            this.multiplier = response.data.multiplier;
            this.calculateSalary();
        },
        fetch(){
            axios.get('/employee/salary_group/'+this.employee.id+'/fetch', {params: this.params}).then(res => {
                this.items = res.data.data;
                this.loading = false;
            });
        },
        save(){
            this.loading = true;
            let data = this.item;
            data.statutories = this.statutories;
            axios.post('/employee/salary_group/employee_salary/save', data).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        search(){ this.fetch(); },
        deleteItem(){ this.isDelete = true; },
        deleteNow(){
            this.loading = true;
            axios.post('/employee/salary_group/delete', this.item).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        startCalculation(){
            this.item.total_gross_percentage = 0;
            this.item.basic_pay = 0;
            if(this.item.ctc > 0 && this.item.salary_group_id){
                this.getSalaryComponents();
            }
        },
        calculateSalary(){ this.calculateEarnings(); },
        calculateBasicPay(){
            this.item.basic_pay = 0;
            this.earnings.forEach(earning => {
                if(earning.is_active && earning.is_ctc && earning.is_basic_pay){
                    this.item.basic_pay += earning.monthly;
                }
            });
        },
        calculateEarningsTotal(){
            let total = 0;
            this.earnings.forEach(e => total += e.monthly);
            return total;
        },
        calculateEarnings(){
            this.earnings.forEach(earning => {
                if(earning.is_active && earning.is_ctc){
                    if(earning.calculation == "Flat") earning.monthly = earning.value;
                    if(earning.calculation == "CTC") earning.monthly = (this.item.ctc * earning.value) / 100;
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
            let arr = CSVstr.split(",");
            let sum = arr.reduce((a, b) => a + (b*1), 0);
            return sum/(arr.length);
        },
        calculateEmployerContribution(){
            return this.statutories.reduce((sum, row) => sum + (row.employer_contribution || 0), 0);
        },
        calculateStatutoryComponents(){
            this.item.total_gross_percentage = 0;
            this.statutories.forEach(row => {
                if(row.is_active && row.is_part_of_salary){
                    row.statutory_compliance_conditions.forEach(cond => {
                        if(cond.is_active && (cond.gender == "All" || cond.gender == this.employee.gender) && 
                           (cond.state == "All" || cond.state == this.employee.employee_work_location?.work_location?.state) &&
                           (cond.employer_contribution != null && cond.employer_contribution != 0) && cond.salary_type != "Gross Pay"){
                            
                            let salary_amount = 0;
                            if(cond.salary_type == "Basic Pay") salary_amount = this.item.basic_pay;
                            else if(cond.salary_type == "CTC") salary_amount = this.item.ctc;

                            let min = cond.min_salary || 0;
                            let max = cond.max_salary || 0;

                            if(salary_amount >= min && (max == 0 || salary_amount <= max)){
                                let monthly = 0;
                                if(cond.calculation == "Flat") monthly = cond.employer_contribution;
                                if(cond.calculation == "CSV") monthly = this.calculateAverage(cond.employer_contribution);
                                if(cond.calculation == "Percentage") monthly = salary_amount * cond.employer_contribution / 100;
                                
                                if(cond.max_employer_contribution && cond.max_employer_contribution < monthly) monthly = cond.max_employer_contribution;
                                row.is = true;
                                row.employer_contribution = Math.round(monthly);
                            }
                        }
                    });
                }
            });

            this.item.checking_gross_pay = this.item.ctc - this.calculateEmployerContribution();

            this.statutories.forEach(row => {
                if(row.is_active && row.is_part_of_salary){
                    row.statutory_compliance_conditions.forEach(cond => {
                        if(cond.is_active && (cond.gender == "All" || cond.gender == this.employee.gender) && 
                           (cond.state == "All" || this.employee.employee_work_location?.work_location?.state) &&
                           (cond.employer_contribution != null && cond.employer_contribution != 0) &&
                           cond.salary_type == "Gross Pay" && cond.calculation != "Percentage"){
                            
                            let salary_amount = this.item.checking_gross_pay;
                            let min = cond.min_salary || 0;
                            let max = cond.max_salary || 0;

                            if(salary_amount >= min && (max == 0 || salary_amount <= max)){
                                let monthly = 0;
                                if(cond.calculation == "Flat") monthly = cond.employer_contribution;
                                if(cond.calculation == "CSV") monthly = this.calculateAverage(cond.employer_contribution);
                                if(cond.max_employer_contribution && cond.max_employer_contribution < monthly) monthly = cond.max_employer_contribution;
                                row.is = true;
                                row.employer_contribution = Math.round(monthly);
                            }
                        }
                        this.item.new_checking_gross_pay = this.item.ctc - this.calculateEmployerContribution();
                        if(cond.is_active && (cond.gender == "All" || cond.gender == this.employee.gender) && 
                           (cond.state == "All" || this.employee.employee_work_location?.work_location?.state) &&
                           cond.salary_type == "Gross Pay" && cond.calculation == "Percentage"){
                            let salary_amount = this.item.new_checking_gross_pay;
                            if(salary_amount >= (cond.min_salary || 0) && ((cond.max_salary || 0) == 0 || salary_amount <= cond.max_salary)){
                                row.is = true;
                                this.item.total_gross_percentage += (cond.employer_contribution * 1);
                            }
                        }
                    });
                }
            });
            this.calculateFinalGrossSalary();
        },
        calculateFinalGrossSalary(){
            let ec = this.calculateEmployerContribution();
            let x = ((this.item.ctc - ec) - (this.item.earnings_total + ec)) / 2;
            let y = (this.item.ctc - ec) / ((x/this.item.total_gross_percentage) * (100 + this.item.total_gross_percentage));
            let z = y * x || 0;
            this.item.gross_pay = Math.round(this.item.new_checking_gross_pay - z);
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
                        if(cond.is_active && (cond.gender == "All" || cond.gender == this.employee.gender) && 
                           (cond.state == "All" || this.employee.employee_work_location?.work_location?.state) &&
                           cond.salary_type == "Gross Pay" && cond.calculation == "Percentage" && row.is){
                            let monthly = this.item.gross_pay * cond.employer_contribution / 100;
                            if(cond.max_employer_contribution && cond.max_employer_contribution < monthly) monthly = cond.max_employer_contribution;
                            row.employer_contribution = Math.round(monthly);
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
            this.services.forEach(s => {
                if(s.is_active){
                    if(s.calculation == "Flat") s.monthly = s.value;
                    if(s.calculation == "Basic") s.monthly = (this.item.basic_pay * s.value) / 100;
                    if(s.calculation == "CTC") s.monthly = (this.item.ctc * s.value) / 100;
                }
            });
        },
        calculateEmployeeContribution(){
            this.statutories.forEach(row => {
                if(row.is_active && row.is_part_of_salary){
                    row.statutory_compliance_conditions.forEach(cond => {
                        if(cond.is_active && (cond.gender == "All" || cond.gender == this.employee.gender) && 
                           (cond.state == "All" || this.employee.employee_work_location?.work_location?.state) &&
                           (cond.employee_contribution != null && cond.employee_contribution != 0)){
                            
                            let salary_amount = 0;
                            if(cond.salary_type == "Basic Pay") salary_amount = this.item.basic_pay;
                            else if(cond.salary_type == "CTC") salary_amount = this.item.ctc;
                            else if(cond.salary_type == "Gross Pay") salary_amount = this.item.gross_pay;

                            if(salary_amount >= (cond.min_salary || 0) && ((cond.max_salary || 0) == 0 || salary_amount <= cond.max_salary)){
                                if(cond.calculation == "Flat") row.employee_contribution = Math.round(cond.employee_contribution * 1);
                                if(cond.calculation == "Percentage") row.employee_contribution = Math.round(salary_amount * cond.employee_contribution / 100);
                                if(cond.calculation == "CSV") row.employee_contribution = Math.round(this.calculateAverage(cond.employee_contribution));
                                row.is = true; row.is_id = cond.id;
                            }
                        }
                    });
                }
            });
            this.item.net_pay = this.item.gross_pay - this.calculateEmployeeContributionTotal();
        },
        calculateEmployeeContributionTotal(){
            return this.statutories.reduce((sum, s) => sum + (s.employee_contribution || 0), 0);
        },
    },
    created(){
        this.fetch();
        if(this.employee.employee_salary){
            this.item.ctc = this.employee.employee_salary.ctc;
            this.item.salary_group_id = this.employee.employee_salary.salary_group_id;
            this.item.effective_from = this.employee.employee_salary.effective_from;
            this.item.note = this.employee.employee_salary.note;
            this.startCalculation();
        }
    },
}
</script>

<style scoped>
.salary-architecture-suite { padding: 1rem 0; }
.summary-card { transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column; justify-content: center; }
.summary-card:hover { transform: translateY(-5px); box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important; }
.bg-light-subtle { background-color: #f8fafc; }
.tiny { font-size: 0.7rem; }
.fw-900 { font-weight: 900; }
.fw-800 { font-weight: 800; }
.fw-mono { font-family: ui-monospace, SFMono-Regular, monospace; }
.bg-soft-info { background-color: #f0f9ff; color: #0ea5e9; }
.transition-all { transition: all 0.2s ease; }
.hover-glow:hover { background-color: #f8fafc; }
.animate-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>