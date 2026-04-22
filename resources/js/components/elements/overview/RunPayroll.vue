<template>
    <div class="payroll-manager">
        <!-- Dashboard Header -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-1">Payroll Management</h2>
                <p class="text-muted">Configure, Review, and Execute your organization's payroll cycle.</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <div class="d-inline-flex bg-white p-2 rounded-2xl shadow-sm border">
                    <button 
                        @click="viewMode = 'create'" 
                        :class="['btn btn-sm px-4', viewMode === 'create' ? 'btn-primary shadow-sm' : 'btn-link text-decoration-none text-muted']">
                        <i class="bi bi-plus-circle me-2"></i>New Cycle
                    </button>
                    <button 
                        @click="viewMode = 'history'" 
                        :class="['btn btn-sm px-4', viewMode === 'history' ? 'btn-primary shadow-sm' : 'btn-link text-decoration-none text-muted']">
                        <i class="bi bi-clock-history me-2"></i>History
                    </button>
                </div>
            </div>
        </div>

        <!-- NEW PAYROLL WIZARD -->
        <div v-if="viewMode === 'create'" class="payroll-wizard">
            <!-- Stepper -->
            <div class="stepper-wrapper mb-5">
                <div class="stepper">
                    <div v-for="s in 3" :key="s" class="step-item" :class="{'active': currentStep === s, 'completed': currentStep > s}">
                        <div class="step-icon">
                            <i v-if="currentStep > s" class="bi bi-check-lg"></i>
                            <span v-else>{{ s }}</span>
                        </div>
                        <div class="step-label">
                            <span v-if="s === 1">Cycle Config</span>
                            <span v-if="s === 2">Employees</span>
                            <span v-if="s === 3">Review</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Step 1: Cycle Configuration -->
                    <div v-if="currentStep === 1" class="card rounded-xl shadow-premium border-0 animate__animated animate__fadeIn">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">Cycle Configuration</h5>
                            <p class="text-muted small">Define the timeframe and cycle details</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <forms-select-field name="financial_year_id" label="Financial Year" v-model="item.financial_year_id" error="" classes="col-12 col-md-6" :options="financial_years"></forms-select-field>
                                <forms-text-field name="payroll_name" label="Payroll Name" v-model="item.payroll_name" error="" classes="col-12 col-md-6" placeholder="e.g. March 2024 Payroll"></forms-text-field>
                                
                                <div class="col-12 py-2">
                                    <div class="p-3 bg-light rounded-xl d-flex align-items-center justify-content-between">
                                        <button @click="shift_dates('prev')" class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm">
                                            <i class="bi bi-chevron-left"></i>
                                        </button>
                                        <div class="text-center flex-grow-1 mx-3">
                                            <div class="label text-muted small text-uppercase fw-bold mb-1">Active Cycle Window</div>
                                            <div class="h5 fw-bold text-primary mb-0">{{ formatDateDisplay(item.from) }} — {{ formatDateDisplay(item.to) }}</div>
                                        </div>
                                        <button @click="shift_dates('next')" class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm">
                                            <i class="bi bi-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>

                                <forms-date-field @change="processPayroll()" name="from" label="From Date" v-model="item.from" error="" classes="col-12 col-md-6"></forms-date-field>
                                <forms-date-field @change="processPayroll()" name="to" label="To Date" v-model="item.to" error="" classes="col-12 col-md-6"></forms-date-field>
                            </div>

                            <div class="mt-5 d-flex justify-content-between">
                                <button class="btn btn-outline-secondary px-4" @click="viewMode = 'history'">Cancel</button>
                                <button class="btn btn-primary px-5" @click="nextStep()" :disabled="!item.payroll_name">
                                    Next: Select Employees <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Employee Selection -->
                    <div v-if="currentStep === 2" class="card rounded-xl shadow-premium border-0 animate__animated animate__fadeIn">
                        <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-0">Employee Selection</h5>
                                <p class="text-muted small">Select employees to include in this cycle</p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm px-3" @click="selectAllEmployees()">Select All</button>
                                <button class="btn btn-outline-secondary btn-sm px-3" @click="item.eids = []">Clear</button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <!-- Search in list -->
                            <div class="input-group mb-4 shadow-sm rounded-xl overflow-hidden border">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" class="form-control border-0 py-3" placeholder="Search by name or code..." v-model="employeeSearch">
                            </div>

                            <div class="employee-list-container custom-scrollbar">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="text-muted small text-uppercase">
                                            <tr>
                                                <th style="width: 40px;"></th>
                                                <th>Employee</th>
                                                <th>Code</th>
                                                <th class="text-end">CTC</th>
                                                <th class="text-end">Gross</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="employee in filteredEmployees" :key="employee.id" 
                                                class="cursor-pointer" 
                                                @click="addRemoveEmployee(employee.id)">
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" :checked="isSelected(employee.id)">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                                            {{ employee.first_name.charAt(0) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">{{ employee.first_name }} {{ employee.last_name }}</div>
                                                            <div class="text-muted small">Joined {{ formatDateDisplayShort(employee.doj) }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-light text-dark">{{ employee.employee_code }}</span></td>
                                                <td class="text-end fw-semibold">
                                                    <span v-if="employee.employee_salaries.length > 0">₹{{ Number(employee.employee_salaries[0].ctc).toLocaleString() }}</span>
                                                </td>
                                                <td class="text-end fw-semibold">
                                                    <span v-if="employee.employee_salaries.length > 0">₹{{ Number(employee.employee_salaries[0].gross_pay).toLocaleString() }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-5 d-flex justify-content-between border-top pt-4">
                                <button class="btn btn-outline-secondary px-4" @click="currentStep = 1">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button class="btn btn-primary px-5" @click="currentStep = 3" :disabled="item.eids.length === 0">
                                    Review Cycle <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Review & Process -->
                    <div v-if="currentStep === 3" class="card rounded-xl shadow-premium border-0 animate__animated animate__fadeIn">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">Cycle Review</h5>
                            <p class="text-muted small">Final verification before processing</p>
                        </div>
                        <div class="card-body p-4 text-center py-5">
                            <div class="mb-5">
                                <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-4" style="width: 80px; height: 80px; font-size: 2.5rem; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <h3 class="fw-bold">Ready to Process</h3>
                                <p class="text-muted">You are about to run payroll for <strong>{{ item.eids.length }} employees</strong> for the period of <strong>{{ formatDateDisplay(item.from) }} to {{ formatDateDisplay(item.to) }}</strong>.</p>
                            </div>

                            <div class="process-summary p-4 bg-light rounded-2xl mb-5 text-start">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-muted small mb-1">Payroll Name</div>
                                        <div class="fw-bold">{{ item.payroll_name }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small mb-1">Financial Year</div>
                                        <div class="fw-bold">{{ getFYName(item.financial_year_id) }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small mb-1">Working Days</div>
                                        <div class="fw-bold"><span class="badge bg-primary">{{ item.working_days }} / {{ item.actual_days }}</span></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small mb-1">Total Employees</div>
                                        <div class="fw-bold">{{ item.eids.length }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning border-0 rounded-xl d-flex align-items-center gap-3 text-start small mb-5">
                                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                                <div>This action will calculate salary, statutory deductions, and generate payslips for all selected employees. Ensure all attendance evaluates are completed.</div>
                            </div>

                            <div class="d-flex justify-content-between border-top pt-4">
                                <button class="btn btn-outline-secondary px-4" @click="currentStep = 2">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button class="btn btn-primary px-5 py-3 rounded-xl fw-bold" @click="save()" :disabled="loading">
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    <i v-else class="bi bi-play-fill me-2 fs-5"></i>
                                    EXECUTE PAYROLL NOW
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR STATS -->
                <div class="col-lg-4">
                    <div class="card rounded-xl border-0 shadow-premium sticky-top" style="top: 2rem;">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h6 class="fw-bold mb-0">Cycle Progress</h6>
                        </div>
                        <div class="card-body px-4 pb-4 pt-2">
                            <div class="d-flex flex-column gap-4">
                                <!-- Stat Item -->
                                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-xl border-start border-4 border-primary">
                                    <div>
                                        <div class="text-muted small mb-0">Employees Selected</div>
                                        <div class="h4 fw-bold mb-0">{{ item.eids.length }}</div>
                                    </div>
                                    <div class="text-primary fs-3 opacity-25"><i class="bi bi-people"></i></div>
                                </div>

                                <!-- Stat Item -->
                                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-xl border-start border-4 border-info">
                                    <div>
                                        <div class="text-muted small mb-0">Days in Cycle</div>
                                        <div class="h4 fw-bold mb-0">{{ item.actual_days || 0 }}</div>
                                    </div>
                                    <div class="text-info fs-3 opacity-25"><i class="bi bi-calendar-check"></i></div>
                                </div>

                                <!-- Help Box -->
                                <div class="bg-indigo-soft text-indigo p-3 rounded-xl small">
                                    <div class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Quick Tip</div>
                                    You can shift cycle dates using the prev/next buttons to quickly move between months.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HISTORY VIEW -->
        <div v-if="viewMode === 'history'" class="payroll-history animate__animated animate__fadeIn">
            <!-- Search & Filters -->
            <div class="card rounded-xl shadow-sm border-0 mb-4 overflow-hidden">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-end">
                        <forms-select-field name="column" label="Search By" placeholder="" 
                            v-model="params.key" error="" classes="col-lg-3" 
                            :options="[{key: 'ID', val: 'id'},{key: 'Name', val: 'payroll_name'}]"></forms-select-field>

                        <forms-text-field name="search" label="Search Query" v-model="params.value" error="" classes="col-lg-7" placeholder="Type to search..."></forms-text-field>

                        <div class="col-lg-2">
                            <button class="btn btn-primary w-100 h-100 py-3" @click="search()">
                                <i class="bi bi-search me-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List -->
            <div class="card rounded-xl shadow-premium border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th @click="orderBy('id')" class="cursor-pointer px-4" style="width: 80px;">ID <i class="bi bi-sort-numeric-down opacity-50 ms-1"></i></th>
                                    <th @click="orderBy('payroll_name')" class="cursor-pointer">Payroll Name</th>
                                    <th>Period Range</th>
                                    <th class="text-end">Employees</th>
                                    <th class="text-end px-4" style="width: 150px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in items" :key="row.id">
                                    <td class="px-4 fw-bold text-muted">{{ row.id }}</td>
                                    <td>
                                        <a :href="'/payslip/payroll/'+row.id" class="text-decoration-none fw-bold text-dark d-flex align-items-center gap-2">
                                            <div class="avatar-xs bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center">
                                                <i class="bi bi-cash-stack"></i>
                                            </div>
                                            {{ row.payroll_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 small">
                                            <span class="text-muted">{{ formatDateDisplayShort(row.from) }}</span>
                                            <i class="bi bi-arrow-right text-muted opacity-50"></i>
                                            <span class="text-muted">{{ formatDateDisplayShort(row.to) }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge rounded-pill bg-light text-dark fw-bold border">{{ row.payroll_employee_count ?? '--' }}</span>
                                    </td>
                                    <td class="text-end px-4">
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical fs-5"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-xl">
                                                <li><a class="dropdown-item py-2" :href="'/payslip/payroll/'+row.id"><i class="bi bi-eye me-2"></i> View Details</a></li>
                                                <li><button class="dropdown-item py-2" @click="edit(row)"><i class="bi bi-pencil me-2"></i> Re-Calculate</button></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><button class="dropdown-item text-danger py-2" @click="confirmDelete(row)"><i class="bi bi-trash me-2"></i> Delete Cycle</button></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="items.length === 0">
                                    <td colspan="5" class="py-5 text-center text-muted italic">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                        No payroll records found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-if="next_page_url" class="card-footer bg-transparent border-0 text-center py-4">
                    <button class="btn btn-outline-primary px-5 rounded-pill" :disabled="loading" @click="fetch()">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        Load More Records
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="isDelete" class="modal-backdrop fade show"></div>
        <div v-if="isDelete" class="modal fade show d-block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-2xl">
                    <div class="modal-body p-4 text-center">
                        <div class="text-danger mb-4"><i class="bi bi-exclamation-circle fs-1"></i></div>
                        <h4 class="fw-bold mb-2">Delete Payroll Cycle?</h4>
                        <p class="text-muted mb-4">This will permanently remove the payroll data for all employees in this cycle. This action cannot be undone.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <button class="btn btn-light px-4" @click="isDelete = false">Cancel</button>
                            <button class="btn btn-danger px-4" @click="deleteNow()">Yes, Delete Anyway</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import axios from "axios";

export default {
    props: ['financial_years', 'from', 'to', 'fy'],

    data() {
        return {
            windowWidth: window.innerWidth,
            viewMode: 'create', // 'create' or 'history'
            currentStep: 1,
            employeeSearch: '',
            loading: false,
            isDelete: false,
            itemToEdit: null,
            item: {
                id: null,
                financial_year_id: null,
                payroll_name: null,
                from: null,
                to: null,
                working_days: null,
                actual_days: null,
                gross_pay: null,
                net_pay: null,
                eids: [],
            },
            items: [],
            employees: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: 'payroll_name',
                value: null,
                by: 'id',
                order: 'desc',
                rows: 1,
            },
        };
    },

    computed: {
        filteredEmployees() {
            if (!this.employeeSearch) return this.employees;
            const q = this.employeeSearch.toLowerCase();
            return this.employees.filter(e => 
                e.first_name.toLowerCase().includes(q) || 
                e.last_name.toLowerCase().includes(q) || 
                e.employee_code.toLowerCase().includes(q)
            );
        }
    },

    methods: {
        reset() {
            this.item.id = null;
            this.item.payroll_name = null;
            this.item.working_days = null;
            this.item.actual_days = null;
            this.item.gross_pay = null;
            this.item.net_pay = null;
            this.item.eids = [];
            this.currentStep = 1;
            this.viewMode = 'history';
        },

        getFYName(id) {
            const fy = this.financial_years.find(f => f.val === id);
            return fy ? fy.key : '--';
        },

        formatDateDisplay(date) {
            if (!date) return '--';
            return new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        formatDateDisplayShort(date) {
            if (!date) return '--';
            return new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        },

        nextStep() {
            if (this.currentStep < 3) this.currentStep++;
        },

        selectAllEmployees() {
            this.item.eids = this.employees.map(e => e.id);
        },

        edit(item) {
            this.item.id = item.id;
            this.item.financial_year_id = item.financial_year_id;
            this.item.payroll_name = item.payroll_name;
            this.item.from = item.from;
            this.item.to = item.to;
            this.item.working_days = item.working_days;
            this.item.actual_days = item.actual_days;
            this.item.gross_pay = item.gross_pay;
            this.item.net_pay = item.net_pay;
            this.viewMode = 'create';
            this.currentStep = 1;
            this.processPayroll();
        },

        shift_dates(what) {
            axios.post('/overview/run_payroll/shift_dates', { from: this.item.from, what: what }).then(res => {
                this.item.from = res.data.from;
                this.item.to = res.data.to;
                this.processPayroll();
            });
        },

        processPayroll() {
            this.item.eids = [];
            if (this.item.from && this.item.to) {
                axios.post('/overview/run_payroll/fetch_employees', this.item).then(res => {
                    this.item.working_days = res.data.working_days;
                    this.item.actual_days = res.data.actual_days;
                    this.employees = res.data.employees;
                });
            }
        },

        isSelected(id) {
            return this.item.eids.includes(id);
        },

        addRemoveEmployee(id) {
            let arr = this.item.eids;
            if (arr.includes(id)) {
                arr.splice(arr.indexOf(id), 1)
            } else {
                arr.push(id);
            }
        },

        fetch() {
            this.loading = true;
            let url = '/overview/run_payroll/fetch';
            if (this.next_page_url != null) {
                url = this.next_page_url;
            }

            axios.get(url, { params: this.params }).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if (this.current_page == 1) {
                    this.items = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.items.push(item);
                    });
                }
                this.loading = false;
            });
        },

        search() {
            this.current_page = 1;
            this.next_page_url = null;
            this.items = [];
            this.fetch();
        },

        orderBy(col) {
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save() {
            this.loading = true;
            const endpoint = this.item.id ? '/overview/run_payroll/update' : '/overview/run_payroll/add';
            axios.post(endpoint, this.item).then(res => {
                this.loading = false;
                this.reset();
                this.search();
                this.viewMode = 'history';
            }).catch(err => {
                this.loading = false;
                alert("An error occurred while processing payroll.");
            });
        },

        confirmDelete(item) {
            this.item.id = item.id;
            this.isDelete = true;
        },

        deleteNow() {
            this.loading = true;
            axios.post('/overview/run_payroll/delete', { id: this.item.id }).then(res => {
                this.loading = false;
                this.isDelete = false;
                this.search();
            });
        },
    },

    created() {
        this.item.from = this.from;
        this.item.to = this.to;
        this.item.financial_year_id = this.fy.id;
        this.fetch();
        this.processPayroll();
    },
}
</script>

<style scoped>
.payroll-manager {
    max-width: 1200px;
    margin: 0 auto;
}

.shadow-premium {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.rounded-xl {
    border-radius: 1rem;
}

.rounded-2xl {
    border-radius: 1.5rem;
}

.cursor-pointer {
    cursor: pointer;
}

/* Stepper Styles */
.stepper-wrapper {
    position: relative;
    padding: 0 1rem;
}

.stepper {
    display: flex;
    justify-content: space-between;
    position: relative;
    max-width: 600px;
    margin: 0 auto;
}

.stepper::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    width: 100%;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}

.step-item {
    position: relative;
    z-index: 1;
    text-align: center;
    background: transparent;
    flex: 1;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    font-weight: bold;
    color: #adb5bd;
    transition: all 0.3s ease;
}

.step-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #adb5bd;
    transition: all 0.3s ease;
}

.step-item.active .step-icon {
    border-color: var(--bs-primary);
    background: var(--bs-primary);
    color: #fff;
    box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.1);
}

.step-item.active .step-label {
    color: var(--bs-primary);
    font-weight: bold;
}

.step-item.completed .step-icon {
    border-color: #198754;
    background: #198754;
    color: #fff;
}

.step-item.completed .step-label {
    color: #198754;
}

.employee-list-container {
    max-height: 400px;
    overflow-y: auto;
}

.avatar-sm {
    width: 40px;
    height: 40px;
}

.avatar-xs {
    width: 28px;
    height: 28px;
}

.bg-indigo-soft {
    background-color: #f0f3ff;
}

.text-indigo {
    color: #5856d6;
}

.bg-primary-soft {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}

.bg-light {
    background-color: #f8f9fa !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.02);
}

.animate__animated {
    animation-duration: 0.5s;
}

/* Custom Scrollbar for list */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e9ecef;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #dee2e6;
}
</style>