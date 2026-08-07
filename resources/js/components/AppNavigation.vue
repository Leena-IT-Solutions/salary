<template>
    <div class="nav-container py-3">
        
        <!-- View Switcher (Only for Administrators and Time Office) -->
        <div v-if="role == 'Administrator' || role == 'Time Office'" class="view-mode-selector mb-4 px-2">
            <div class="d-flex bg-white bg-opacity-10 p-1 rounded-3 gap-1">
                <button @click="switchView('Administrator')" 
                        :class="['flex-grow-1 btn btn-sm py-2 rounded-2 transition-all border-0 text-truncate', activeView == 'Administrator' ? 'btn-white shadow-sm fw-bold' : 'text-white text-opacity-75']">
                    {{ role == 'Time Office' ? 'Time Office' : 'Admin' }}
                </button>
                <button @click="switchView('Employee')" 
                        :class="['flex-grow-1 btn btn-sm py-2 rounded-2 transition-all border-0', activeView == 'Employee' ? 'btn-white shadow-sm fw-bold' : 'text-white text-opacity-75']">
                    Portal
                </button>
            </div>
        </div>

        <!-- Administrator Hub -->
        <div v-if="activeView == 'Administrator' && role == 'Administrator'">
            <!-- Overview Section -->
            <div class="nav-section mb-4">
                <h6 class="nav-section-title">Overview</h6>
                <div class="nav-items">
                    <a href="/" class="nav-item-link" :class="{ 'active': cpath == '/' }">
                        <i class="bi bi-grid-fill" style="color: #818cf8;"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/calender" class="nav-item-link" :class="{ 'active': cpath == '/calender' }">
                        <i class="bi bi-calendar3" style="color: #fbbf24;"></i>
                        <span>Calendar</span>
                    </a>
                    <a href="/employee_shift" class="nav-item-link" :class="{ 'active': cpath == '/employee_shift' }">
                        <i class="bi bi-clock-history" style="color: #a78bfa;"></i>
                        <span>Shift Manager</span>
                    </a>
                    <a href="/attendance" class="nav-item-link" :class="{ 'active': cpath == '/attendance' }">
                        <i class="bi bi-fingerprint" style="color: #22d3ee;"></i>
                        <span>Attendance</span>
                    </a>
                    <a href="/attendance_evalution_report" class="nav-item-link" :class="{ 'active': cpath == '/attendance_evalution_report' }">
                        <i class="bi bi-file-earmark-bar-graph" style="color: #6366f1;"></i>
                        <span>Evaluation Report</span>
                    </a>
                    <a href="/run_payroll" class="nav-item-link" :class="{ 'active': cpath == '/run_payroll' || cpath == '/payslip' }">
                        <i class="bi bi-cash-stack" style="color: #34d399;"></i>
                        <span>Run Payroll</span>
                    </a>
                </div>
            </div>

            <!-- Employee Section -->
            <div class="nav-section mb-4">
                <h6 class="nav-section-title">Employees</h6>
                <div class="nav-items">
                    <a href="/employee/employee_manager" class="nav-item-link" :class="{ 'active': cpath.startsWith('/employee/') }">
                        <i class="bi bi-people" style="color: #a78bfa;"></i>
                        <span>Employee Manager</span>
                    </a>
                </div>
            </div>

            <!-- Approvals Section -->
            <div class="nav-section mb-4">
                <h6 class="nav-section-title">Approvals</h6>
                <div class="nav-items">
                    <a href="/approvals/pending" class="nav-item-link" :class="{ 'active': cpath == '/approvals/pending' }">
                        <i class="bi bi-clock-history" style="color: #fb7185;"></i>
                        <span>Pending Approvals</span>
                    </a>
                    <a href="/approvals/leave" class="nav-item-link" :class="{ 'active': cpath == '/approvals/leave' }">
                        <i class="bi bi-calendar-check" style="color: #f472b6;"></i>
                        <span>Leave</span>
                    </a>
                    <a href="/approvals/time_update" class="nav-item-link" :class="{ 'active': cpath == '/approvals/time_update' }">
                        <i class="bi bi-clock-history" style="color: #e879f9;"></i>
                        <span>Time Update</span>
                    </a>
                    <a href="/approvals/on_duty" class="nav-item-link" :class="{ 'active': cpath == '/approvals/on_duty' }">
                        <i class="bi bi-briefcase" style="color: #c084fc;"></i>
                        <span>On Duty</span>
                    </a>
                    <a href="/approvals/shortleave" class="nav-item-link" :class="{ 'active': cpath == '/approvals/shortleave' }">
                        <i class="bi bi-hourglass-split" style="color: #a78bfa;"></i>
                        <span>Short Leave</span>
                    </a>
                    <a href="/approvals/overtime" class="nav-item-link" :class="{ 'active': cpath == '/approvals/overtime' }">
                        <i class="bi bi-watch" style="color: #818cf8;"></i>
                        <span>Overtime</span>
                    </a>
                    <a href="/approvals/fine" class="nav-item-link" :class="{ 'active': cpath == '/approvals/fine' }">
                        <i class="bi bi-exclamation-octagon" style="color: #f87171;"></i>
                        <span>Fine</span>
                    </a>
                    <a href="/approvals/variable_pay" class="nav-item-link" :class="{ 'active': cpath == '/approvals/variable_pay' }">
                        <i class="bi bi-plus-circle" style="color: #4ade80;"></i>
                        <span>Variable Pay</span>
                    </a>
                    <a href="/approvals/loan_and_advance" class="nav-item-link" :class="{ 'active': cpath == '/approvals/loan_and_advance' }">
                        <i class="bi bi-bank" style="color: #38bdf8;"></i>
                        <span>Loans & Advance</span>
                    </a>
                    <a href="/approvals/reimbursement" class="nav-item-link" :class="{ 'active': cpath == '/approvals/reimbursement' }">
                        <i class="bi bi-receipt" style="color: #fbbf24;"></i>
                        <span>Reimbursement</span>
                    </a>
                    <a href="/approvals/exemption_and_deduction" class="nav-item-link" :class="{ 'active': cpath == '/approvals/exemption_and_deduction' }">
                        <i class="bi bi-calculator" style="color: #a3e635;"></i>
                        <span>Exemption & Ded.</span>
                    </a>
                </div>
            </div>

            <!-- Organization Settings -->
            <div class="nav-section mb-4">
                <h6 class="nav-section-title">Organization</h6>
                <div class="nav-items">
                    <a href="/organisation_settings/company_profile" class="nav-item-link" :class="{ 'active': cpath == '/organisation_settings/company_profile' }">
                        <i class="bi bi-buildings" style="color: #818cf8;"></i>
                        <span>Company Profile</span>
                    </a>
                    <a href="/organisation_settings/work_location" class="nav-item-link" :class="{ 'active': cpath == '/organisation_settings/work_location' }">
                        <i class="bi bi-geo-alt" style="color: #fb7185;"></i>
                        <span>Work Locations</span>
                    </a>
                    <a href="/organisation_settings/departments" class="nav-item-link" :class="{ 'active': cpath == '/organisation_settings/departments' }">
                        <i class="bi bi-diagram-3" style="color: #34d399;"></i>
                        <span>Departments</span>
                    </a>
                    <a href="/organisation_settings/designations" class="nav-item-link" :class="{ 'active': cpath == '/organisation_settings/designations' }">
                        <i class="bi bi-award" style="color: #fbbf24;"></i>
                        <span>Designations</span>
                    </a>
                    <a href="/organisation_settings/working_shifts" class="nav-item-link" :class="{ 'active': cpath == '/organisation_settings/working_shifts' }">
                        <i class="bi bi-clock" style="color: #a78bfa;"></i>
                        <span>Working Shifts</span>
                    </a>
                    <a href="/organisation_settings/leaves_setup" class="nav-item-link" :class="{ 'active': cpath == '/organisation_settings/leaves_setup' }">
                        <i class="bi bi-calendar-event" style="color: #22d3ee;"></i>
                        <span>Leaves Setup</span>
                    </a>
                </div>
            </div>

            <!-- Salary Settings -->
            <div class="nav-section mb-4">
                <h6 class="nav-section-title">Salary Settings</h6>
                <div class="nav-items">
                    <a href="/salary_settings/earnings" class="nav-item-link" :class="{ 'active': cpath == '/salary_settings/earnings' }">
                        <i class="bi bi-wallet2" style="color: #6366f1;"></i>
                        <span>Earnings</span>
                    </a>
                    <a href="/salary_settings/services" class="nav-item-link" :class="{ 'active': cpath == '/salary_settings/services' }">
                        <i class="bi bi-gear-wide-connected" style="color: #14b8a6;"></i>
                        <span>Services</span>
                    </a>
                    <a href="/salary_settings/reimbursement" class="nav-item-link" :class="{ 'active': cpath == '/salary_settings/reimbursement' }">
                        <i class="bi bi-credit-card" style="color: #f59e0b;"></i>
                        <span>Reimbursement</span>
                    </a>
                    <a href="/salary_settings/exemption_and_deduction" class="nav-item-link" :class="{ 'active': cpath == '/salary_settings/exemption_and_deduction' }">
                        <i class="bi bi-patch-minus" style="color: #f43f5e;"></i>
                        <span>Exemption & Ded.</span>
                    </a>
                    <a href="/salary_settings/statutory_compliance" class="nav-item-link" :class="{ 'active': cpath == '/salary_settings/statutory_compliance' }">
                        <i class="bi bi-shield-check" style="color: #10b981;"></i>
                        <span>Statutory Comp.</span>
                    </a>
                    <a href="/salary_settings/salary_group" class="nav-item-link" :class="{ 'active': cpath == '/salary_settings/salary_group' }">
                        <i class="bi bi-layers" style="color: #8b5cf6;"></i>
                        <span>Salary Groups</span>
                    </a>
                </div>
            </div>

            <!-- System Section (Only visible in Administrator Hub) -->
            <div class="nav-section mb-4">
                <h6 class="nav-section-title">System</h6>
                <div class="nav-items">
                    <a href="/application_settings/financial_year" class="nav-item-link" :class="{ 'active': cpath == '/application_settings/financial_year' }">
                        <i class="bi bi-calendar-range" style="color: #fbbf24;"></i>
                        <span>Financial Year</span>
                    </a>
                    <a href="/application_settings/user_and_roles" class="nav-item-link" :class="{ 'active': cpath == '/application_settings/user_and_roles' }">
                        <i class="bi bi-person-badge" style="color: #818cf8;"></i>
                        <span>User & Roles</span>
                    </a>
                    <a href="/application_settings/preference" class="nav-item-link" :class="{ 'active': cpath == '/application_settings/preference' }">
                        <i class="bi bi-sliders" style="color: #22d3ee;"></i>
                        <span>Preference</span>
                    </a>
                    <a href="/application_settings/configure_machine" class="nav-item-link" :class="{ 'active': cpath == '/application_settings/configure_machine' }">
                        <i class="bi bi-cpu" style="color: #10b981;"></i>
                        <span>Configure Machine</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Time Office Hub -->
        <div v-if="activeView == 'Administrator' && role == 'Time Office'">
            <!-- Overview Section -->
            <div class="nav-section mb-4">
                <h6 class="nav-section-title">Overview</h6>
                <div class="nav-items">
                    <a href="/" class="nav-item-link" :class="{ 'active': cpath == '/' }">
                        <i class="bi bi-grid-fill" style="color: #818cf8;"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/calender" class="nav-item-link" :class="{ 'active': cpath == '/calender' }">
                        <i class="bi bi-calendar3" style="color: #fbbf24;"></i>
                        <span>Calendar</span>
                    </a>
                    <a href="/employee_shift" class="nav-item-link" :class="{ 'active': cpath == '/employee_shift' }">
                        <i class="bi bi-clock-history" style="color: #a78bfa;"></i>
                        <span>Shift Manager</span>
                    </a>
                    <a href="/attendance" class="nav-item-link" :class="{ 'active': cpath == '/attendance' }">
                        <i class="bi bi-fingerprint" style="color: #22d3ee;"></i>
                        <span>Attendance</span>
                    </a>
                </div>
            </div>

            <!-- Approvals Section -->
            <div class="nav-section mb-4">
                <h6 class="nav-section-title">Approvals</h6>
                <div class="nav-items">
                    <a href="/approvals/pending" class="nav-item-link" :class="{ 'active': cpath == '/approvals/pending' }">
                        <i class="bi bi-clock-history" style="color: #fb7185;"></i>
                        <span>Pending Approvals</span>
                    </a>
                    <a href="/approvals/leave" class="nav-item-link" :class="{ 'active': cpath == '/approvals/leave' }">
                        <i class="bi bi-calendar-check" style="color: #f472b6;"></i>
                        <span>Leave</span>
                    </a>
                    <a href="/approvals/time_update" class="nav-item-link" :class="{ 'active': cpath == '/approvals/time_update' }">
                        <i class="bi bi-clock-history" style="color: #e879f9;"></i>
                        <span>Time Update</span>
                    </a>
                    <a href="/approvals/on_duty" class="nav-item-link" :class="{ 'active': cpath == '/approvals/on_duty' }">
                        <i class="bi bi-briefcase" style="color: #c084fc;"></i>
                        <span>On Duty</span>
                    </a>
                    <a href="/approvals/shortleave" class="nav-item-link" :class="{ 'active': cpath == '/approvals/shortleave' }">
                        <i class="bi bi-hourglass-split" style="color: #a78bfa;"></i>
                        <span>Short Leave</span>
                    </a>
                    <a href="/approvals/overtime" class="nav-item-link" :class="{ 'active': cpath == '/approvals/overtime' }">
                        <i class="bi bi-watch" style="color: #818cf8;"></i>
                        <span>Overtime</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Employee Portal -->
        <div v-if="activeView == 'Employee'">
            <div class="nav-section mb-4">
                <h6 class="nav-section-title">My Portal</h6>
                <div class="nav-items">
                    <a href="/employee/dashboard" class="nav-item-link" :class="{ 'active': cpath == '/employee/dashboard' }">
                        <i class="bi bi-grid-1x2-fill" style="color: #818cf8;"></i>
                        <span>My Dashboard</span>
                    </a>
                    <a href="/employee/requests" class="nav-item-link" :class="{ 'active': cpath == '/employee/requests' }">
                        <i class="bi bi-lightning-charge-fill" style="color: #fbbf24;"></i>
                        <span>Direct Channels</span>
                    </a>
                    <a href="/employee/payslips" class="nav-item-link" :class="{ 'active': cpath == '/employee/payslips' }">
                        <i class="bi bi-file-earmark-pdf-fill" style="color: #22d3ee;"></i>
                        <span>My Payslips</span>
                    </a>
                    <a href="/employee/attendance" class="nav-item-link" :class="{ 'active': cpath == '/employee/attendance' }">
                        <i class="bi bi-calendar-check" style="color: #34d399;"></i>
                        <span>Attendance</span>
                    </a>
                    <a href="/employee/holidays" class="nav-item-link" :class="{ 'active': cpath == '/employee/holidays' }">
                        <i class="bi bi-calendar-event" style="color: #fbbf24;"></i>
                        <span>Holidays</span>
                    </a>
                    <a href="/employee/profile" class="nav-item-link" :class="{ 'active': cpath == '/employee/profile' }">
                        <i class="bi bi-person-badge-fill" style="color: #fb7185;"></i>
                        <span>My Profile</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Common Sections -->
        <div class="nav-section mb-4">
            <div class="nav-items">
                <a class="nav-item-link text-danger" href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right" style="color: #f87171;"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    data() {
        return {
            cpath: "",
            role: window.User ? window.User.role : 'Employee',
            activeView: 'Employee',
        };
    },
    created() {
        this.cpath = window.location.pathname;
        
        // Initialize active view
        const savedView = localStorage.getItem('salary_active_view');
        if (this.role === 'Administrator' || this.role === 'Time Office') {
            this.activeView = savedView || 'Administrator';
        } else {
            this.activeView = 'Employee';
        }
    },
    methods: {
        switchView(view) {
            this.activeView = view;
            localStorage.setItem('salary_active_view', view);
        }
    },
    mounted() {
        this.$nextTick(() => {
            const activeLink = this.$el.querySelector('.nav-item-link.active');
            if (activeLink) {
                activeLink.scrollIntoView({ behavior: 'auto', block: 'center' });
            }
        });
    },
}
</script>

<style lang="scss" scoped>
.nav-container {
    padding: 0 1rem;
}

.nav-section-title {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.5);
    margin-bottom: 0.75rem;
    padding-left: 1rem;
    letter-spacing: 0.05em;
}

.nav-items {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.nav-item-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    font-weight: 400;

    i {
        font-size: 1.1rem;
    }

    &:hover {
        color: white;
        background: rgba(255, 255, 255, 0.1);
        transform: translateX(4px);
    }

    &.active {
        color: white;
        background: rgba(255, 255, 255, 0.2);
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
}

.btn-white {
    background-color: white !important;
    color: #4f46e5 !important;
}
</style>