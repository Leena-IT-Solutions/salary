<template>
    <div class="payroll-details-container px-4 py-5 animate__animated animate__fadeIn">
        <!-- Floating Action Bar -->
        <div class="floating-actions shadow-premium bg-white p-3 rounded-2xl border mb-5 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="/run_payroll" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-file-earmark-text fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-uppercase letter-spacing-1">{{ payroll.payroll_name }}</h5>
                    <p class="text-muted small mb-0">{{ formatDateDisplay(payroll.from) }} — {{ formatDateDisplay(payroll.to) }}</p>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <button @click="openBulkEmailModal()" class="btn btn-indigo shadow-sm d-flex align-items-center gap-2">
                    <i class="bi bi-envelope-paper-fill"></i>
                    <span>Send All Emails</span>
                    <span class="badge bg-white text-indigo rounded-pill px-2 py-0.5 small fw-bold ms-1">
                        {{ sentCount }}/{{ payroll.payroll_employees.length }} Sent
                    </span>
                </button>
                <a :href="'/pdf/payslip/'+payroll.id" target="_blank" class="btn btn-outline-primary shadow-sm"><i class="bi bi-file-pdf me-2"></i>Download All</a>
                <div class="dropdown">
                    <button class="btn btn-primary shadow-sm px-4" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-download me-2"></i>Export Reports
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-premium border-0 rounded-xl mt-2">
                        <li><a class="dropdown-item py-2" :href="'/pdf/bank_letter/'+payroll.id" target="_blank"><i class="bi bi-bank me-2 text-primary"></i>Bank Letter</a></li>
                        <li><a class="dropdown-item py-2" :href="'/pdf/ca_report/'+payroll.id" target="_blank"><i class="bi bi-file-earmark-break me-2 text-danger"></i>CA Report (PDF)</a></li>
                        <li><a class="dropdown-item py-2" :href="'/excel/ca_report/'+payroll.id" target="_blank"><i class="bi bi-file-earmark-excel me-2 text-success"></i>CA Report (Excel)</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Summary Dashboard -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card border-0 shadow-premium rounded-xl h-100 overflow-hidden">
                    <div class="card-body p-4 position-relative">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Total Net Payout</div>
                        <div class="h3 fw-bold text-primary mb-0">₹ {{ Number(payroll.net_payable_amount).toLocaleString() }}</div>
                        <div class="stats-deco"><i class="bi bi-currency-rupee"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-premium rounded-xl h-100 overflow-hidden">
                    <div class="card-body p-4 position-relative">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Total Employees</div>
                        <div class="h3 fw-bold text-dark mb-0">{{ payroll.payroll_employees.length }}</div>
                        <div class="stats-deco"><i class="bi bi-people"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-premium rounded-xl h-100 overflow-hidden text-danger text-opacity-75">
                    <div class="card-body p-4 position-relative">
                        <div class="text-muted small text-uppercase fw-bold mb-1 font-black">Total Deductions</div>
                        <div class="h3 fw-bold mb-0">₹ {{ Number(payroll.gross_deduction).toLocaleString() }}</div>
                        <div class="stats-deco"><i class="bi bi-graph-down-arrow"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-premium rounded-xl h-100 overflow-hidden text-success text-opacity-75">
                    <div class="card-body p-4 position-relative">
                        <div class="text-muted small text-uppercase fw-bold mb-1 font-black">Gross Salary</div>
                        <div class="h3 fw-bold mb-0">₹ {{ Number(payroll.gross_salary).toLocaleString() }}</div>
                        <div class="stats-deco"><i class="bi bi-wallet2"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <div class="input-group shadow-sm rounded-xl overflow-hidden border">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-0 py-3" placeholder="Search by name, code or designation..." v-model="searchQuery">
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted small fw-bold text-uppercase">Showing {{ filteredResults.length }} of {{ payroll.payroll_employees.length }} Payslips</span>
            </div>
        </div>

        <!-- Employee List Cards -->
        <div class="row g-4">
            <div v-for="emp in filteredResults" :key="emp.id" class="col-12">
                <div class="card border-0 shadow-premium rounded-xl employee-payslip-card" :class="{'is-expanded': expandedId === emp.id, 'overflow-hidden': expandedId !== emp.id}">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width: 50px; height: 50px;">
                                        {{ emp.employee.first_name.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <h6 class="fw-bold mb-0">{{ emp.employee.first_name }} {{ emp.employee.middle_name }} {{ emp.employee.last_name }}</h6>
                                        </div>
                                        <div class="text-muted small mb-1">{{ emp.employee.employee_code }} • {{ emp.employee.employee_designation?.designation?.designation }}</div>
                                        <!-- Email Sent Status Badge -->
                                        <div>
                                            <span v-if="emp.is_email_sent" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 font-semibold" style="font-size: 0.7rem;" :title="emp.email_sent_at ? 'Sent: ' + formatDateDisplayShort(emp.email_sent_at) : 'Sent'">
                                                <i class="bi bi-envelope-check-fill me-1"></i>Email Sent
                                            </span>
                                            <span v-else class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1 font-semibold" style="font-size: 0.7rem;">
                                                <i class="bi bi-envelope-dash me-1"></i>Not Sent
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="text-muted small text-uppercase mb-1">Payable Days</div>
                                    <div class="fw-bold"><i class="bi bi-calendar-check text-success me-2"></i>{{ emp.payroll_employee_attendances?.payable_days }} Days</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-end pe-4">
                                    <div class="text-muted small text-uppercase mb-1">Net Salary</div>
                                    <div class="h5 fw-bold text-primary mb-0">₹ {{ Number(emp.net_payable_amount).toLocaleString() }}</div>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-light rounded-pill px-4 btn-sm fw-bold border" @click="toggleExpand(emp.id)">
                                    {{ expandedId === emp.id ? 'Hide Details' : 'View Details' }}
                                    <i class="bi ms-2" :class="expandedId === emp.id ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Expanded Content -->
                        <div v-if="expandedId === emp.id" class="detailed-payslip mt-4 pt-4 pb-3 border-top animate__animated animate__slideInDown">
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-2xl h-100">
                                        <h6 class="fw-bold mb-3 d-flex align-items-center justify-content-between">
                                            <span>Earnings</span>
                                            <span class="text-success badge bg-success bg-opacity-10">₹ {{ Number(emp.gross_salary).toLocaleString() }}</span>
                                        </h6>
                                        <div class="list-group list-group-flush bg-transparent">
                                            <div v-for="breakup in filterBreakups(emp, 'earnings')" :key="breakup.id" class="list-group-item bg-transparent d-flex justify-content-between px-0 py-2 border-dashed">
                                                <span class="text-muted">{{ breakup.name_in_payslip }}</span>
                                                <span class="fw-semibold">₹ {{ Number(breakup.actual_payable_amount).toLocaleString() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-2xl h-100">
                                        <h6 class="fw-bold mb-3 d-flex align-items-center justify-content-between">
                                            <span>Deductions</span>
                                            <span class="text-danger badge bg-danger bg-opacity-10">₹ {{ Number(emp.gross_deduction).toLocaleString() }}</span>
                                        </h6>
                                        <div class="list-group list-group-flush bg-transparent">
                                            <div v-for="breakup in filterBreakups(emp, 'deductions')" :key="breakup.id" class="list-group-item bg-transparent d-flex justify-content-between px-0 py-2 border-dashed">
                                                <span class="text-muted">{{ breakup.name_in_payslip }}</span>
                                                <span class="fw-semibold">₹ {{ Number(breakup.actual_payable_amount).toLocaleString() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Bank & Details Footer -->
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="d-flex gap-4 p-3 border rounded-xl bg-white shadow-sm">
                                        <div v-if="emp.employee.employee_bank">
                                            <div class="text-muted small text-uppercase">Bank Account</div>
                                            <div class="fw-bold small text-primary mb-1">{{ emp.employee.employee_bank?.bank_name }}</div>
                                            <div class="fw-medium small">{{ emp.employee.employee_bank?.account_number }}</div>
                                        </div>
                                        <div v-if="emp.employee.pf">
                                            <div class="text-muted small text-uppercase">EPF Account</div>
                                            <div class="fw-bold small">{{ emp.employee.pf }}</div>
                                        </div>
                                        <div>
                                            <div class="text-muted small text-uppercase">Joined Date</div>
                                            <div class="fw-bold small">{{ formatDateDisplayShort(emp.employee.doj) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-muted small text-uppercase">Email Status</div>
                                            <div class="fw-bold small text-success" v-if="emp.is_email_sent">
                                                <i class="bi bi-check-circle-fill me-1"></i>Sent ({{ formatDateDisplayShort(emp.email_sent_at) }})
                                            </div>
                                            <div class="fw-bold small text-muted" v-else>
                                                <i class="bi bi-dash-circle me-1"></i>Not Sent
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="p-3 border border-primary border-opacity-25 rounded-xl bg-primary bg-opacity-10 h-100 d-flex flex-column justify-content-center">
                                        <div class="text-muted small text-uppercase mb-1 font-black">Net Payable Amount</div>
                                        <div class="h4 fw-bold text-primary text-center mb-0">₹ {{ Number(emp.net_payable_amount).toLocaleString() }} /-</div>
                                        <div class="text-muted text-center x-small mt-2 font-italic text-capitalize">"{{ emp.amount_str }} Rupees Only"</div>
                                    </div>
                                    <button @click="sendIndividualEmail(emp)" class="btn btn-indigo btn-sm w-100 mt-3 rounded-pill" :disabled="emp.sendingEmail">
                                        <span v-if="emp.sendingEmail" class="spinner-border spinner-border-sm me-2"></span>
                                        <i v-else :class="emp.is_email_sent ? 'bi bi-envelope-check me-2' : 'bi bi-envelope me-2'"></i>
                                        {{ emp.is_email_sent ? 'Resend Payslip via Email' : 'Send Payslip via Email' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filteredResults.length === 0" class="col-12 py-5 text-center text-muted">
                <i class="bi bi-person-x-fill fs-1 d-block mb-3 opacity-25"></i>
                <h5>No employees found matching your search.</h5>
                <button class="btn btn-link" @click="searchQuery = ''">Clear Search</button>
            </div>
        </div>

        <!-- Bulk Email Dispatch Modal -->
        <div v-if="showBulkModal" class="modal-backdrop fade show"></div>
        <div v-if="showBulkModal" class="modal d-block tab-index-1" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-2xl">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-envelope-paper me-2 text-primary"></i>Bulk Email Dispatch</h5>
                        <button v-if="!isDispatching" type="button" class="btn-close" @click="closeBulkModal()"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Options State -->
                        <div v-if="!isDispatching && !dispatchComplete">
                            <p class="text-muted small">Choose how you would like to dispatch payslip emails to employees for {{ payroll.payroll_name }}:</p>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-xl text-center border">
                                        <div class="small text-muted text-uppercase fw-bold mb-1">Sent Emails</div>
                                        <div class="h4 fw-bold text-success mb-0">{{ sentCount }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded-xl text-center border">
                                        <div class="small text-muted text-uppercase fw-bold mb-1">Unsent Emails</div>
                                        <div class="h4 fw-bold text-danger mb-0">{{ unsentCount }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button v-if="unsentCount > 0" @click="startBulkDispatch('unsent')" class="btn btn-indigo py-2.5 rounded-xl font-semibold">
                                    <i class="bi bi-send me-2"></i>Send to Unsent Employees Only ({{ unsentCount }})
                                </button>
                                <button @click="startBulkDispatch('all')" class="btn btn-outline-primary py-2.5 rounded-xl font-semibold">
                                    <i class="bi bi-arrow-repeat me-2"></i>Resend to All Employees ({{ payroll.payroll_employees.length }})
                                </button>
                            </div>
                        </div>

                        <!-- Dispatching State -->
                        <div v-if="isDispatching" class="py-3 text-center">
                            <div class="mb-3">
                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Dispatching Payslip Emails...</h6>
                            <p class="text-muted small mb-3">Processing {{ dispatchCurrentIndex + 1 }} of {{ dispatchQueue.length }}: <strong class="text-dark">{{ currentDispatchEmployeeName }}</strong></p>
                            
                            <div class="progress mb-3 rounded-pill" style="height: 12px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-indigo" role="progressbar" :style="{ width: dispatchProgressPercent + '%' }"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between small text-muted font-mono mb-3">
                                <span>Progress: {{ dispatchProgressPercent }}%</span>
                                <span>Success: {{ dispatchSuccessCount }} | Failed: {{ dispatchErrorCount }}</span>
                            </div>

                            <button @click="stopBulkDispatch()" class="btn btn-sm btn-outline-danger rounded-pill px-4">
                                Stop Dispatch
                            </button>
                        </div>

                        <!-- Completion State -->
                        <div v-if="dispatchComplete" class="py-2">
                            <div class="text-center mb-3">
                                <div class="icon-circle bg-success bg-opacity-10 text-success mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 60px; height: 60px;">
                                    <i class="bi bi-check-circle-fill fs-2"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Email Dispatch Completed</h5>
                                <p class="text-muted small mb-0">Successfully dispatched {{ dispatchSuccessCount }} email(s).</p>
                            </div>

                            <div v-if="dispatchErrors.length > 0" class="alert alert-warning small rounded-xl mb-3">
                                <strong>Errors encountered:</strong>
                                <ul class="mb-0 ps-3 mt-1">
                                    <li v-for="(err, idx) in dispatchErrors" :key="idx">{{ err }}</li>
                                </ul>
                            </div>

                            <div class="text-end mt-4">
                                <button @click="closeBulkModal()" class="btn btn-primary rounded-xl px-4">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: ['payroll', 'company'],

    data() {
        return {
            searchQuery: '',
            expandedId: null,
            sendingEmail: false,

            // Bulk Modal & Queue State
            showBulkModal: false,
            isDispatching: false,
            dispatchComplete: false,
            dispatchQueue: [],
            dispatchCurrentIndex: 0,
            dispatchSuccessCount: 0,
            dispatchErrorCount: 0,
            dispatchErrors: [],
            stopRequested: false,
        };
    },

    computed: {
        filteredResults() {
            if (!this.searchQuery) return this.payroll.payroll_employees;
            const q = this.searchQuery.toLowerCase();
            return this.payroll.payroll_employees.filter(emp => 
                emp.employee.first_name.toLowerCase().includes(q) ||
                emp.employee.last_name.toLowerCase().includes(q) ||
                emp.employee.employee_code.toLowerCase().includes(q) ||
                (emp.employee.employee_designation?.designation?.designation || '').toLowerCase().includes(q)
            );
        },

        sentCount() {
            return this.payroll.payroll_employees.filter(e => e.is_email_sent).length;
        },

        unsentCount() {
            return this.payroll.payroll_employees.length - this.sentCount;
        },

        dispatchProgressPercent() {
            if (this.dispatchQueue.length === 0) return 0;
            return Math.round((this.dispatchCurrentIndex / this.dispatchQueue.length) * 100);
        },

        currentDispatchEmployeeName() {
            if (!this.dispatchQueue.length || this.dispatchCurrentIndex >= this.dispatchQueue.length) return '';
            const emp = this.dispatchQueue[this.dispatchCurrentIndex];
            return `${emp.employee.first_name} ${emp.employee.last_name}`;
        }
    },

    methods: {
        formatDateDisplay(date) {
            if (!date) return '--';
            return new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        formatDateDisplayShort(date) {
            if (!date) return '--';
            return new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        },

        toggleExpand(id) {
            this.expandedId = this.expandedId === id ? null : id;
        },

        openBulkEmailModal() {
            this.showBulkModal = true;
            this.isDispatching = false;
            this.dispatchComplete = false;
            this.dispatchQueue = [];
            this.dispatchCurrentIndex = 0;
            this.dispatchSuccessCount = 0;
            this.dispatchErrorCount = 0;
            this.dispatchErrors = [];
            this.stopRequested = false;
        },

        closeBulkModal() {
            if (this.isDispatching) return;
            this.showBulkModal = false;
        },

        async startBulkDispatch(mode) {
            if (mode === 'unsent') {
                this.dispatchQueue = this.payroll.payroll_employees.filter(e => !e.is_email_sent);
            } else {
                this.dispatchQueue = [...this.payroll.payroll_employees];
            }

            if (this.dispatchQueue.length === 0) {
                alert("No employees to send emails to.");
                return;
            }

            this.isDispatching = true;
            this.dispatchComplete = false;
            this.dispatchCurrentIndex = 0;
            this.dispatchSuccessCount = 0;
            this.dispatchErrorCount = 0;
            this.dispatchErrors = [];
            this.stopRequested = false;

            for (let i = 0; i < this.dispatchQueue.length; i++) {
                if (this.stopRequested) break;
                this.dispatchCurrentIndex = i;
                const emp = this.dispatchQueue[i];

                try {
                    const res = await axios.post('/payroll/send_email', { type: 'single', id: emp.id });
                    if (res.data.sent_count > 0) {
                        emp.is_email_sent = true;
                        emp.email_sent_at = res.data.payroll_employee?.email_sent_at || new Date().toISOString();
                        this.dispatchSuccessCount++;
                    } else {
                        this.dispatchErrorCount++;
                        if (res.data.errors && res.data.errors.length) {
                            this.dispatchErrors.push(...res.data.errors);
                        } else {
                            this.dispatchErrors.push(`Failed for ${emp.employee.first_name} ${emp.employee.last_name}`);
                        }
                    }
                } catch (err) {
                    this.dispatchErrorCount++;
                    this.dispatchErrors.push(`Error sending to ${emp.employee.first_name}: ` + (err.response?.data?.message || err.message));
                }
            }

            this.isDispatching = false;
            this.dispatchComplete = true;
        },

        stopBulkDispatch() {
            this.stopRequested = true;
        },

        async sendIndividualEmail(emp) {
            this.$set ? this.$set(emp, 'sendingEmail', true) : emp.sendingEmail = true;
            try {
                const res = await axios.post('/payroll/send_email', { type: 'single', id: emp.id });
                if (res.data.sent_count > 0) {
                    emp.is_email_sent = true;
                    emp.email_sent_at = res.data.payroll_employee?.email_sent_at || new Date().toISOString();
                    alert(`Email sent successfully to ${emp.employee.first_name}!`);
                } else {
                    alert(res.data.errors ? res.data.errors.join("\n") : "Failed to send email.");
                }
            } catch (err) {
                alert("Error sending email: " + (err.response?.data?.message || err.message));
            } finally {
                emp.sendingEmail = false;
            }
        },

        filterBreakups(emp, type) {
            const breakingTypes = {
                earnings: [
                    'App\\Models\\PayrollEmployeeAttendance',
                    'App\\Models\\Earning',
                    'App\\Models\\ReimbursementApproval',
                    'App\\Models\\EmployeeSalary',
                ],
                deductions: [
                    'App\\Models\\FineApproval',
                    'App\\Models\\ServicesComponent',
                    'App\\Models\\StatutoryComplianceCondition',
                ]
            };

            return emp.payroll_employee_breakups.filter(b => {
                if (type === 'earnings') {
                    return breakingTypes.earnings.includes(b.breakupable_type) || (b.breakupable_type === 'App\\Models\\LoanAndAdvanceApproval' && b.name_in_payslip === 'Loan');
                } else {
                    return breakingTypes.deductions.includes(b.breakupable_type) || (b.breakupable_type === 'App\\Models\\LoanAndAdvanceApproval' && b.name_in_payslip === 'Loan EMI');
                }
            });
        }
    }
}
</script>

<style scoped>
.payroll-details-container {
    max-width: 1200px;
    margin: 0 auto;
}

.floating-actions {
    position: sticky;
    top: 1rem;
    z-index: 100;
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

.letter-spacing-1 {
    letter-spacing: 1px;
}

.stats-deco {
    position: absolute;
    right: -10px;
    bottom: -15px;
    font-size: 5rem;
    opacity: 0.03;
    transform: rotate(-15deg);
    pointer-events: none;
}

.employee-payslip-card {
    transition: all 0.3s ease;
}

.employee-payslip-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
}

.employee-payslip-card.is-expanded {
    border: 1px solid rgba(var(--bs-primary-rgb), 0.2) !important;
}

.border-dashed {
    border-bottom: 1px dashed rgba(0,0,0,0.08) !important;
}

.border-dashed:last-child {
    border-bottom: none !important;
}

.font-black {
    font-weight: 800;
}

.x-small {
    font-size: 0.75rem;
}

.animate__animated {
    animation-duration: 0.5s;
}

.font-italic {
    font-style: italic;
}

.bg-indigo {
    background-color: #6366f1 !important;
}

.text-indigo {
    color: #6366f1 !important;
}

.btn-indigo {
    background-color: #6366f1;
    color: #ffffff;
    border: none;
}

.btn-indigo:hover {
    background-color: #4f46e5;
    color: #ffffff;
}

.modal-backdrop {
    z-index: 1040;
}

.modal {
    z-index: 1050;
}
</style>
