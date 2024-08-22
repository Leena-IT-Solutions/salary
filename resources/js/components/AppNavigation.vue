<template>
    <div class="container text-light py-4">

        <div class="mb-4">
            <div>
                <h6 class="text-uppercase fw-bold m-0 d-inline-block mb-2">Overview
                    <!-- <hr class="w-50 border-3 my-2"> -->
                </h6>
            </div>
    
            <div class="d-grid gap-1">
                
                <a 
                href="/" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-gear me-2"></i>
                    Dashboard
                </a>

                <a href="/calender" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/calender' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Calender
                </a>

                <a href="/employee_shift" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/employee_shift' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Employee Shift Manager
                </a>

                <a href="/attendance" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/attendance' ? 'active' : ''"
                type="button">
                    <i class="bi bi-airplane me-2"></i>
                    Attendance
                </a>

                <a href="/run_payroll" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/run_payroll' || cpath == '/payslip' ? 'active' : ''"
                type="button">
                    <i class="bi bi-airplane me-2"></i>
                    Run Payroll
                </a>

            </div>
        </div>

        <!-- Approvals -->
        <div class="mb-4">
            <div>
                <h6 class="text-uppercase fw-bold m-0 d-inline-block mb-2">Apporvals</h6>
            </div>
    
            <div class="d-grid gap-1">
                
                <a href="/approvals/leave" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/approvals/leave' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Leave
                </a>

                <a href="/approvals/time_update" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/approvals/time_update' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Time Update
                </a>

                <a href="/approvals/on_duty" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/approvals/on_duty' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    On Duty
                </a>

                <a href="/approvals/shortleave" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/approvals/shortleave' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Short Leave
                </a>

                <a href="/approvals/overtime" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/approvals/overtime' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Overtime
                </a>

                <a href="/approvals/fine" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/approvals/fine' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Fine
                </a>

                <a href="/approvals/variable_pay" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/approvals/variable_pay' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Variable Pay
                </a>

                <a href="/approvals/loan_and_advance" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/approvals/loan_and_advance' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Loans and Advance
                </a>

                <a href="/approvals/reimbursement" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/approvals/reimbursement' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Reimbursement
                </a>

                <a href="/approvals/exemption_and_deduction" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/approvals/exemption_and_deduction' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Exemption and Deduction
                </a>

            </div>
        </div>

        <div class="mb-4">
            <div>
                <h6 class="text-uppercase fw-bold m-0 d-inline-block mb-2">Employee</h6>
            </div>
    
            <div class="d-grid gap-1">
                
                <a href="/employee/employee_manager" 
                class="btn btn-lg btn-primary btn-sm text-start"
                :class="cpath == '/employee/employee_manager' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person me-2"></i>
                    Employee Manager
                </a>

            </div>
        </div>

        <div class="mb-4">
            <div>
                <h6 class="text-uppercase fw-bold m-0 d-inline-block mb-2">Organisation Settings</h6>
            </div>

            <div class="d-grid gap-1">

                <a 
                href="/organisation_settings/company_profile" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/organisation_settings/company_profile' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-buildings me-2"></i>
                    Company Profile
                </a>
                
                <a 
                href="/organisation_settings/work_location" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/organisation_settings/work_location' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-pin-map me-2"></i>
                    Work Locations
                </a>

                <a 
                href="/organisation_settings/departments" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/organisation_settings/departments' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-person-workspace me-2"></i>
                    Departments
                </a>

                <a 
                href="/organisation_settings/designations" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/organisation_settings/designations' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-mortarboard me-2"></i>
                    Designation
                </a>

                <a 
                href="/organisation_settings/working_shifts" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/organisation_settings/working_shifts' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-clock me-2"></i>
                    Working Shifts
                </a>

                <a 
                href="/organisation_settings/leaves_setup" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/organisation_settings/leaves_setup' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-umbrella me-2"></i>
                    Leaves Setup
                </a>

            </div>
    
        </div>

        <div class="mb-4">
            <div>
                <h6 class="text-uppercase fw-bold m-0 d-inline-block mb-2">Salary Settings</h6>
            </div>
    
            <div class="d-grid gap-1">

                <a 
                href="/salary_settings/earnings" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/salary_settings/earnings' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Earnings
                </a>

                <a 
                href="/salary_settings/services" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/salary_settings/services' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Services
                </a>

                <a 
                href="/salary_settings/reimbursement" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/salary_settings/reimbursement' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Reimbursement
                </a>

                <a 
                href="/salary_settings/exemption_and_deduction" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/salary_settings/exemption_and_deduction' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Exemption And Deduction
                </a>

                <a 
                href="/salary_settings/statutory_compliance" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/salary_settings/statutory_compliance' ? 'active' : ''"   
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Statutory Compliance
                </a>

                <a 
                href="/salary_settings/salary_group" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/salary_settings/salary_group' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Salary Groups
                </a>

                <a 
                href="/" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Payslip Formats
                </a>

                <!-- <a 
                href="/" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Taxes
                </a> -->

                <!-- <a 
                href="/" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Pay Schedule
                </a> -->

                <a 
                href="/" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Email Template
                </a>

            </div>
        </div>

        <div class="mb-4">
            <div>
                <h6 class="text-uppercase fw-bold m-0 d-inline-block mb-2">Application Settings</h6>
            </div>
    
            <div class="d-grid gap-1">

                <a 
                href="/application_settings/financial_year" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/application_settings/financial_year' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Financial Year
                </a>

                <a 
                href="/application_settings/user_and_roles" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/application_settings/user_and_roles' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    User & Roles
                </a>

                <a 
                href="/application_settings/preference" 
                class="btn btn-lg btn-primary btn-sm text-start" 
                :class="cpath == '/application_settings/preference' ? 'active' : ''" 
                type="button">
                    <i class="bi bi-currency-rupee me-2"></i>
                    Preference
                </a>

                <a class="btn btn-lg btn-primary btn-sm text-start" href="/logout"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    Logout
                </a>

            </div>
        </div>
        

    </div>
</template>

<script>
export default {

    data(){
        return {
            cpath: "",
        };
    },

    created(){

        this.cpath = window.location.pathname;
    },

}
</script>