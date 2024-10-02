<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\FinancialYear;

Auth::routes(['verify' => true]);

/***********************************
# Overview Routes 
************************************/

Route::get('/attendance/save', [App\Http\Controllers\AttendanceMachineController::class, 'save']);
Route::post('/attendance/evalute', [App\Http\Controllers\AttendanceMachineController::class, 'evalute']);

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/calender', [App\Http\Controllers\CalenderController::class, 'calender']);
Route::get('/calender/working_years', [App\Http\Controllers\CalenderController::class, 'working_years']);
Route::post('/calender/working_years/add', [App\Http\Controllers\CalenderController::class, 'add']);
Route::get('/calender/special_day/fetch/{year}', [App\Http\Controllers\CalenderController::class, 'fetch']);
Route::post('/calender/special_day/save', [App\Http\Controllers\CalenderController::class, 'save']);
Route::post('/calender/special_day/delete', [App\Http\Controllers\CalenderController::class, 'delete']);

Route::get('/employee_shift', [App\Http\Controllers\EmployeeShiftController::class, 'employee_shift']);
Route::get('/employee_shift/employee/fetch', [App\Http\Controllers\EmployeeShiftController::class, 'fetch']);
Route::post('/employee_shift/save', [App\Http\Controllers\EmployeeShiftController::class, 'save']);

Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'attendance']);
Route::get('/attendance/fetch', [App\Http\Controllers\AttendanceController::class, 'fetch']);

Route::get('/attendance_evalution_report', [App\Http\Controllers\AttendanceController::class, 'attendance_evalution_report']);
Route::get('/attendance_evalution_report/get_data', [App\Http\Controllers\AttendanceController::class, 'get_data']);
Route::post('/attendance_evalution_report/run_lop', [App\Http\Controllers\AttendanceController::class, 'run_lop']);

Route::get('/run_payroll', [App\Http\Controllers\RunPayrollController::class, 'run_payroll']);
Route::get('/overview/run_payroll/fetch', [App\Http\Controllers\RunPayrollController::class, 'fetch']);
Route::post('/overview/run_payroll/add', [App\Http\Controllers\RunPayrollController::class, 'add']);
Route::post('/overview/run_payroll/update', [App\Http\Controllers\RunPayrollController::class, 'update']);
Route::post('/overview/run_payroll/delete', [App\Http\Controllers\RunPayrollController::class, 'delete']);
Route::post('/overview/run_payroll/fetch_employees', [App\Http\Controllers\RunPayrollController::class, 'fetch_employees']);
Route::post('/overview/run_payroll/shift_dates', [App\Http\Controllers\RunPayrollController::class, 'shift_dates']);

Route::get('/payslip/payroll/{id}', [App\Http\Controllers\PayslipController::class, 'payslip']);




/***********************************
# PDF Pages 
************************************/
//Route::get('/pdf/demo/{id}', [App\Http\Controllers\PDFController::class, 'demo']);
Route::get('/pdf/payslip/{id}', [App\Http\Controllers\PDFController::class, 'payslip']);
Route::get('/pdf/bank_letter/{id}', [App\Http\Controllers\PDFController::class, 'bank_letter']);
Route::get('/pdf/ca_report/{id}', [App\Http\Controllers\PDFController::class, 'ca_report']);
Route::get('/excel/ca_report/{id}', [App\Http\Controllers\PDFController::class, 'excel_ca_report']);
Route::get('/pdf/attendance/{from}/{to}', [App\Http\Controllers\PDFController::class, 'attendance']);



/***********************************
# Employee Routes 
************************************/

/* Employee Manager */
Route::get('/employee/employee_manager', [App\Http\Controllers\EmployeeController::class, 'employee_manager']);
Route::get('/employee/profile/{id}', [App\Http\Controllers\EmployeeController::class, 'profile']);
Route::get('/employee/employee_manager/fetch', [App\Http\Controllers\EmployeeController::class, 'fetch']);
Route::post('/employee/employee_manager/add', [App\Http\Controllers\EmployeeController::class, 'add']);
Route::post('/employee/employee_manager/update', [App\Http\Controllers\EmployeeController::class, 'update']);
Route::post('/employee/employee_manager/delete', [App\Http\Controllers\EmployeeController::class, 'delete']);

/* Employee Address */
Route::get('/employee/employee_address/{id}/fetch', [App\Http\Controllers\EmployeeAddressController::class, 'fetch']);
Route::post('/employee/employee_address/add', [App\Http\Controllers\EmployeeAddressController::class, 'add']);
Route::post('/employee/employee_address/update', [App\Http\Controllers\EmployeeAddressController::class, 'update']);
Route::post('/employee/employee_address/delete', [App\Http\Controllers\EmployeeAddressController::class, 'delete']);

/* Employee Work Location */
Route::get('/employee/employee_work_location/{id}/fetch', [App\Http\Controllers\EmployeeWorkLocationController::class, 'fetch']);
Route::post('/employee/employee_work_location/add', [App\Http\Controllers\EmployeeWorkLocationController::class, 'add']);
Route::post('/employee/employee_work_location/update', [App\Http\Controllers\EmployeeWorkLocationController::class, 'update']);
Route::post('/employee/employee_work_location/delete', [App\Http\Controllers\EmployeeWorkLocationController::class, 'delete']);

/* Employee Designation */
Route::get('/employee/employee_designation/{id}/fetch', [App\Http\Controllers\EmployeeDesignationController::class, 'fetch']);
Route::post('/employee/employee_designation/add', [App\Http\Controllers\EmployeeDesignationController::class, 'add']);
Route::post('/employee/employee_designation/update', [App\Http\Controllers\EmployeeDesignationController::class, 'update']);
Route::post('/employee/employee_designation/delete', [App\Http\Controllers\EmployeeDesignationController::class, 'delete']);

/* Employee Department */
Route::get('/employee/employee_department/{id}/fetch', [App\Http\Controllers\EmployeeDepartmentController::class, 'fetch']);
Route::post('/employee/employee_department/add', [App\Http\Controllers\EmployeeDepartmentController::class, 'add']);
Route::post('/employee/employee_department/update', [App\Http\Controllers\EmployeeDepartmentController::class, 'update']);
Route::post('/employee/employee_department/delete', [App\Http\Controllers\EmployeeDepartmentController::class, 'delete']);

/* Employee Photo */
Route::get('/employee/employee_photo/{id}/fetch', [App\Http\Controllers\EmployeePhotoController::class, 'fetch']);
Route::post('/employee/employee_photo/add', [App\Http\Controllers\EmployeePhotoController::class, 'add']);
Route::post('/employee/employee_photo/update', [App\Http\Controllers\EmployeePhotoController::class, 'update']);
Route::post('/employee/employee_photo/delete', [App\Http\Controllers\EmployeePhotoController::class, 'delete']);

/* Employee Document */
Route::get('/employee/employee_document/{id}/fetch', [App\Http\Controllers\EmployeeDocumentsController::class, 'fetch']);
Route::post('/employee/employee_document/add', [App\Http\Controllers\EmployeeDocumentsController::class, 'add']);
Route::post('/employee/employee_document/update', [App\Http\Controllers\EmployeeDocumentsController::class, 'update']);
Route::post('/employee/employee_document/delete', [App\Http\Controllers\EmployeeDocumentsController::class, 'delete']);

/* Employee Document */
Route::get('/employee/employee_leave_group/{id}/fetch', [App\Http\Controllers\EmployeeLeaveGroupController::class, 'fetch']);
Route::post('/employee/employee_leave_group/add', [App\Http\Controllers\EmployeeLeaveGroupController::class, 'add']);
Route::post('/employee/employee_leave_group/update', [App\Http\Controllers\EmployeeLeaveGroupController::class, 'update']);
Route::post('/employee/employee_leave_group/delete', [App\Http\Controllers\EmployeeLeaveGroupController::class, 'delete']);

/* Employee Salary */
Route::get('/employee/salary_group/{id}/salary_group', [App\Http\Controllers\EmployeeSalaryController::class, 'salary_group']);
Route::get('/employee/salary_group/{id}/fetch', [App\Http\Controllers\EmployeeSalaryController::class, 'fetch']);
Route::post('/employee/salary_group/employee_salary/save', [App\Http\Controllers\EmployeeSalaryController::class, 'save']);
Route::post('/employee/salary_group/delete', [App\Http\Controllers\EmployeeSalaryController::class, 'delete']);

/* Employee Bank */
Route::get('/employee/employee_bank/{id}/fetch', [App\Http\Controllers\EmployeeBankController::class, 'fetch']);
Route::post('/employee/employee_bank/add', [App\Http\Controllers\EmployeeBankController::class, 'add']);
Route::post('/employee/employee_bank/update', [App\Http\Controllers\EmployeeBankController::class, 'update']);
Route::post('/employee/employee_bank/delete', [App\Http\Controllers\EmployeeBankController::class, 'delete']);

/* Employee Services */
Route::get('/employee/employee_services/{id}/fetch', [App\Http\Controllers\EmployeeServicesController::class, 'fetch']);
Route::post('/employee/employee_services/add', [App\Http\Controllers\EmployeeServicesController::class, 'add']);
Route::post('/employee/employee_services/update', [App\Http\Controllers\EmployeeServicesController::class, 'update']);
Route::post('/employee/employee_services/delete', [App\Http\Controllers\EmployeeServicesController::class, 'delete']);




/***********************************
# Organization Settings Routes 
************************************/

/* Company Profile */
Route::get('/organisation_settings/company_profile', [App\Http\Controllers\CompanyProfileController::class, 'company_profile']);
Route::get('/organisation_settings/company_profile/fetch', [App\Http\Controllers\CompanyProfileController::class, 'fetch']);
Route::post('/organisation_settings/company_profile/logo_upload', [App\Http\Controllers\CompanyProfileController::class, 'logo_upload']);
Route::post('/organisation_settings/company_profile/update', [App\Http\Controllers\CompanyProfileController::class, 'update']);

/* Work Location */
Route::get('/organisation_settings/work_location', [App\Http\Controllers\WorkLocationController::class, 'work_location']);
Route::get('/organisation_settings/work_location/fetch', [App\Http\Controllers\WorkLocationController::class, 'fetch']);
Route::post('/organisation_settings/work_location/add', [App\Http\Controllers\WorkLocationController::class, 'add']);
Route::post('/organisation_settings/work_location/update', [App\Http\Controllers\WorkLocationController::class, 'update']);
Route::post('/organisation_settings/work_location/delete', [App\Http\Controllers\WorkLocationController::class, 'delete']);

/* Departments */
Route::get('/organisation_settings/departments', [App\Http\Controllers\DepartmentController::class, 'departments']);
Route::get('/organisation_settings/departments/fetch', [App\Http\Controllers\DepartmentController::class, 'fetch']);
Route::post('/organisation_settings/departments/add', [App\Http\Controllers\DepartmentController::class, 'add']);
Route::post('/organisation_settings/departments/update', [App\Http\Controllers\DepartmentController::class, 'update']);
Route::post('/organisation_settings/departments/delete', [App\Http\Controllers\DepartmentController::class, 'delete']);

/* Designations */
Route::get('/organisation_settings/designations', [App\Http\Controllers\DesignationController::class, 'designations']);
Route::get('/organisation_settings/designations/fetch', [App\Http\Controllers\DesignationController::class, 'fetch']);
Route::post('/organisation_settings/designations/add', [App\Http\Controllers\DesignationController::class, 'add']);
Route::post('/organisation_settings/designations/update', [App\Http\Controllers\DesignationController::class, 'update']);
Route::post('/organisation_settings/designations/delete', [App\Http\Controllers\DesignationController::class, 'delete']);

/* Working Shifts */
Route::get('/organisation_settings/working_shifts', [App\Http\Controllers\WorkingShiftController::class, 'working_shifts']);
Route::get('/organisation_settings/working_shifts/fetch', [App\Http\Controllers\WorkingShiftController::class, 'fetch']);
Route::post('/organisation_settings/working_shifts/add', [App\Http\Controllers\WorkingShiftController::class, 'add']);
Route::post('/organisation_settings/working_shifts/update', [App\Http\Controllers\WorkingShiftController::class, 'update']);
Route::post('/organisation_settings/working_shifts/delete', [App\Http\Controllers\WorkingShiftController::class, 'delete']);

/* Leaves Setup */
Route::get('/organisation_settings/leaves_setup', [App\Http\Controllers\LeavesSetupController::class, 'leaves_setup']);
Route::get('/organisation_settings/leaves_setup/fetch', [App\Http\Controllers\LeavesSetupController::class, 'fetch']);
Route::post('/organisation_settings/leaves_setup/add', [App\Http\Controllers\LeavesSetupController::class, 'add']);
Route::post('/organisation_settings/leaves_setup/update', [App\Http\Controllers\LeavesSetupController::class, 'update']);
Route::post('/organisation_settings/leaves_setup/delete', [App\Http\Controllers\LeavesSetupController::class, 'delete']);

Route::get('/organisation_settings/leaves_setup/fetch_lg', [App\Http\Controllers\LeavesSetupController::class, 'fetch_lg']);
Route::post('/organisation_settings/leaves_setup/save_lg', [App\Http\Controllers\LeavesSetupController::class, 'save_lg']);
Route::post('/organisation_settings/leaves_setup/update_lg', [App\Http\Controllers\LeavesSetupController::class, 'update_lg']);
Route::post('/organisation_settings/leaves_setup/delete_lg', [App\Http\Controllers\LeavesSetupController::class, 'delete_lg']);


/* Earnings */
Route::get('/salary_settings/earnings', [App\Http\Controllers\EarningsController::class, 'earnings']);
Route::get('/salary_settings/earnings/fetch', [App\Http\Controllers\EarningsController::class, 'fetch']);
Route::post('/salary_settings/earnings/add', [App\Http\Controllers\EarningsController::class, 'add']);
Route::post('/salary_settings/earnings/update', [App\Http\Controllers\EarningsController::class, 'update']);
Route::post('/salary_settings/earnings/delete', [App\Http\Controllers\EarningsController::class, 'delete']);

/* Services */
Route::get('/salary_settings/services', [App\Http\Controllers\ServicesController::class, 'services']);
Route::get('/salary_settings/services/fetch', [App\Http\Controllers\ServicesController::class, 'fetch']);
Route::post('/salary_settings/services/add', [App\Http\Controllers\ServicesController::class, 'add']);
Route::post('/salary_settings/services/update', [App\Http\Controllers\ServicesController::class, 'update']);
Route::post('/salary_settings/services/delete', [App\Http\Controllers\ServicesController::class, 'delete']);

/* Reimbursement */
Route::get('/salary_settings/reimbursement', [App\Http\Controllers\ReimbursementController::class, 'reimbursement']);
Route::get('/salary_settings/reimbursement/fetch', [App\Http\Controllers\ReimbursementController::class, 'fetch']);
Route::post('/salary_settings/reimbursement/add', [App\Http\Controllers\ReimbursementController::class, 'add']);
Route::post('/salary_settings/reimbursement/update', [App\Http\Controllers\ReimbursementController::class, 'update']);
Route::post('/salary_settings/reimbursement/delete', [App\Http\Controllers\ReimbursementController::class, 'delete']);

/* Exemption and Deduction */
Route::get('/salary_settings/exemption_and_deduction', [App\Http\Controllers\ExemptionAndDeductionController::class, 'exemption_and_deduction']);
Route::get('/salary_settings/exemption_and_deduction/fetch', [App\Http\Controllers\ExemptionAndDeductionController::class, 'fetch']);
Route::post('/salary_settings/exemption_and_deduction/add', [App\Http\Controllers\ExemptionAndDeductionController::class, 'add']);
Route::post('/salary_settings/exemption_and_deduction/update', [App\Http\Controllers\ExemptionAndDeductionController::class, 'update']);
Route::post('/salary_settings/exemption_and_deduction/delete', [App\Http\Controllers\ExemptionAndDeductionController::class, 'delete']);

/* Statutory Compliance */
Route::get('/salary_settings/statutory_compliance', [App\Http\Controllers\StatutoryComplianceController::class, 'statutory_compliance']);
Route::get('/salary_settings/statutory_compliance/fetch', [App\Http\Controllers\StatutoryComplianceController::class, 'fetch']);
Route::post('/salary_settings/statutory_compliance/add', [App\Http\Controllers\StatutoryComplianceController::class, 'add']);
Route::post('/salary_settings/statutory_compliance/update', [App\Http\Controllers\StatutoryComplianceController::class, 'update']);
Route::post('/salary_settings/statutory_compliance/delete', [App\Http\Controllers\StatutoryComplianceController::class, 'delete']);

Route::get('/salary_settings/statutory_compliance/{id}/condition', [App\Http\Controllers\StatutoryComplianceConditionController::class, 'condition']);
Route::get('/salary_settings/statutory_compliance/{id}/condition/fetch', [App\Http\Controllers\StatutoryComplianceConditionController::class, 'fetch']);
Route::post('/salary_settings/statutory_compliance/{id}/condition/add', [App\Http\Controllers\StatutoryComplianceConditionController::class, 'add']);
Route::post('/salary_settings/statutory_compliance/{id}/condition/update', [App\Http\Controllers\StatutoryComplianceConditionController::class, 'update']);
Route::post('/salary_settings/statutory_compliance/{id}/condition/delete', [App\Http\Controllers\StatutoryComplianceConditionController::class, 'delete']);

/* Salary Group */
Route::get('/salary_settings/salary_group', [App\Http\Controllers\SalaryGroupController::class, 'salary_group']);
Route::get('/salary_settings/salary_group/fetch', [App\Http\Controllers\SalaryGroupController::class, 'fetch']);
Route::post('/salary_settings/salary_group/add', [App\Http\Controllers\SalaryGroupController::class, 'add']);
Route::post('/salary_settings/salary_group/update', [App\Http\Controllers\SalaryGroupController::class, 'update']);
Route::post('/salary_settings/salary_group/delete', [App\Http\Controllers\SalaryGroupController::class, 'delete']);

Route::get('/salary_settings/salary_groupable/{id}/data', [App\Http\Controllers\SalaryGroupableController::class, 'data']);
Route::get('/salary_settings/salary_groupable/{id}/data/fetch', [App\Http\Controllers\SalaryGroupableController::class, 'fetch']);
Route::get('/salary_settings/salary_groupable/{id}/data/earnings', [App\Http\Controllers\SalaryGroupableController::class, 'earnings']);
Route::get('/salary_settings/salary_groupable/{id}/data/services', [App\Http\Controllers\SalaryGroupableController::class, 'services']);
Route::get('/salary_settings/salary_groupable/{id}/data/reimbursements', [App\Http\Controllers\SalaryGroupableController::class, 'reimbursements']);
Route::get('/salary_settings/salary_groupable/{id}/data/statutories', [App\Http\Controllers\SalaryGroupableController::class, 'statutories']);
Route::post('/salary_settings/salary_groupable/data/update', [App\Http\Controllers\SalaryGroupableController::class, 'update']);

// Route::get('/salary_settings/salary_group/{id}/data', [App\Http\Controllers\SalaryGroupDataController::class, 'data']);
// Route::get('/salary_settings/salary_group/{id}/data/fetch', [App\Http\Controllers\SalaryGroupDataController::class, 'fetch']);
// Route::post('/salary_settings/salary_group/data/update', [App\Http\Controllers\SalaryGroupDataController::class, 'update']);


/* Leave Approval */
Route::get('/approvals/leave', [App\Http\Controllers\LeaveApprovalController::class, 'leave']);
Route::get('/approvals/leave/fetch', [App\Http\Controllers\LeaveApprovalController::class, 'fetch']);
Route::post('/approvals/leave/add', [App\Http\Controllers\LeaveApprovalController::class, 'add']);
Route::post('/approvals/leave/update', [App\Http\Controllers\LeaveApprovalController::class, 'update']);
Route::post('/approvals/leave/delete', [App\Http\Controllers\LeaveApprovalController::class, 'delete']);
Route::get('/approvals/leave/employee/{id}/fy/{fyid}', [App\Http\Controllers\LeaveApprovalController::class, 'employee']);

/* Shortleave Approval */
Route::get('/approvals/shortleave', [App\Http\Controllers\ShortleaveApprovalController::class, 'shortleave']);
Route::get('/approvals/shortleave/fetch', [App\Http\Controllers\ShortleaveApprovalController::class, 'fetch']);
Route::post('/approvals/shortleave/add', [App\Http\Controllers\ShortleaveApprovalController::class, 'add']);
Route::post('/approvals/shortleave/update', [App\Http\Controllers\ShortleaveApprovalController::class, 'update']);
Route::post('/approvals/shortleave/delete', [App\Http\Controllers\ShortleaveApprovalController::class, 'delete']);
Route::get('/approvals/shortleave/employee/{id}', [App\Http\Controllers\ShortleaveApprovalController::class, 'employee']);

/* Overtime Approval */
Route::get('/approvals/overtime', [App\Http\Controllers\OvertimeApprovalController::class, 'overtime']);
Route::get('/approvals/overtime/fetch', [App\Http\Controllers\OvertimeApprovalController::class, 'fetch']);
Route::post('/approvals/overtime/add', [App\Http\Controllers\OvertimeApprovalController::class, 'add']);
Route::post('/approvals/overtime/update', [App\Http\Controllers\OvertimeApprovalController::class, 'update']);
Route::post('/approvals/overtime/delete', [App\Http\Controllers\OvertimeApprovalController::class, 'delete']);
Route::get('/approvals/overtime/employee/{id}', [App\Http\Controllers\OvertimeApprovalController::class, 'employee']);

/* Fine */
Route::get('/approvals/fine', [App\Http\Controllers\FineApprovalController::class, 'index']);
Route::get('/approvals/fine/fetch', [App\Http\Controllers\FineApprovalController::class, 'fetch']);
Route::post('/approvals/fine/add', [App\Http\Controllers\FineApprovalController::class, 'add']);
Route::post('/approvals/fine/update', [App\Http\Controllers\FineApprovalController::class, 'update']);
Route::post('/approvals/fine/delete', [App\Http\Controllers\FineApprovalController::class, 'delete']);
Route::get('/approvals/fine/employee/{id}', [App\Http\Controllers\FineApprovalController::class, 'employee']);

/* Variable Pay */
Route::get('/approvals/variable_pay', [App\Http\Controllers\VariablePayApprovalController::class, 'index']);
Route::get('/approvals/variable_pay/fetch', [App\Http\Controllers\VariablePayApprovalController::class, 'fetch']);
Route::post('/approvals/variable_pay/add', [App\Http\Controllers\VariablePayApprovalController::class, 'add']);
Route::post('/approvals/variable_pay/update', [App\Http\Controllers\VariablePayApprovalController::class, 'update']);
Route::post('/approvals/variable_pay/delete', [App\Http\Controllers\VariablePayApprovalController::class, 'delete']);
Route::get('/approvals/variable_pay/employee/{id}', [App\Http\Controllers\VariablePayApprovalController::class, 'employee']);

/* Time Update */
Route::get('/approvals/time_update', [App\Http\Controllers\TimeUpdateController::class, 'time_update']);
Route::get('/approvals/time_update/fetch', [App\Http\Controllers\TimeUpdateController::class, 'fetch']);
Route::post('/approvals/time_update/add', [App\Http\Controllers\TimeUpdateController::class, 'add']);
Route::post('/approvals/time_update/update', [App\Http\Controllers\TimeUpdateController::class, 'update']);
Route::post('/approvals/time_update/delete', [App\Http\Controllers\TimeUpdateController::class, 'delete']);
Route::get('/approvals/time_update/employee/{id}', [App\Http\Controllers\TimeUpdateController::class, 'employee']);

/* On Duty */
Route::get('/approvals/on_duty', [App\Http\Controllers\OnDutyController::class, 'on_duty']);
Route::get('/approvals/on_duty/fetch', [App\Http\Controllers\OnDutyController::class, 'fetch']);
Route::post('/approvals/on_duty/add', [App\Http\Controllers\OnDutyController::class, 'add']);
Route::post('/approvals/on_duty/update', [App\Http\Controllers\OnDutyController::class, 'update']);
Route::post('/approvals/on_duty/delete', [App\Http\Controllers\OnDutyController::class, 'delete']);
Route::get('/approvals/on_duty/employee/{id}', [App\Http\Controllers\OnDutyController::class, 'employee']);

/* Loan and Advance Approval */
Route::get('/approvals/loan_and_advance', [App\Http\Controllers\LoansAndAdvanceApprovalController::class, 'loan_and_advance']);
Route::get('/approvals/loan_and_advance/fetch', [App\Http\Controllers\LoansAndAdvanceApprovalController::class, 'fetch']);
Route::post('/approvals/loan_and_advance/add', [App\Http\Controllers\LoansAndAdvanceApprovalController::class, 'add']);
Route::post('/approvals/loan_and_advance/update', [App\Http\Controllers\LoansAndAdvanceApprovalController::class, 'update']);
Route::post('/approvals/loan_and_advance/delete', [App\Http\Controllers\LoansAndAdvanceApprovalController::class, 'delete']);
Route::get('/approvals/loan_and_advance/employee/{id}', [App\Http\Controllers\LoansAndAdvanceApprovalController::class, 'employee']);

/* Reimbursement Approval */
Route::get('/approvals/reimbursement', [App\Http\Controllers\ReimbursementApprovalController::class, 'reimbursement']);
Route::get('/approvals/reimbursement/fetch', [App\Http\Controllers\ReimbursementApprovalController::class, 'fetch']);
Route::post('/approvals/reimbursement/add', [App\Http\Controllers\ReimbursementApprovalController::class, 'add']);
Route::post('/approvals/reimbursement/update', [App\Http\Controllers\ReimbursementApprovalController::class, 'update']);
Route::post('/approvals/reimbursement/delete', [App\Http\Controllers\ReimbursementApprovalController::class, 'delete']);
Route::get('/approvals/reimbursement/employee/{id}', [App\Http\Controllers\ReimbursementApprovalController::class, 'employee']);

/* Exemption and Deduction Approval */
Route::get('/approvals/exemption_and_deduction', [App\Http\Controllers\ExemptionAndDeductionApprovalController::class, 'exemption_and_deduction']);
Route::get('/approvals/exemption_and_deduction/fetch', [App\Http\Controllers\ExemptionAndDeductionApprovalController::class, 'fetch']);
Route::post('/approvals/exemption_and_deduction/add', [App\Http\Controllers\ExemptionAndDeductionApprovalController::class, 'add']);
Route::post('/approvals/exemption_and_deduction/update', [App\Http\Controllers\ExemptionAndDeductionApprovalController::class, 'update']);
Route::post('/approvals/exemption_and_deduction/delete', [App\Http\Controllers\ExemptionAndDeductionApprovalController::class, 'delete']);
Route::get('/approvals/exemption_and_deduction/employee/{id}', [App\Http\Controllers\ExemptionAndDeductionApprovalController::class, 'employee']);


/* Financial Year */
Route::get('/application_settings/financial_year', [App\Http\Controllers\FinancialYearController::class, 'financial_year']);
Route::get('/application_settings/financial_year/fetch', [App\Http\Controllers\FinancialYearController::class, 'fetch']);
Route::post('/application_settings/financial_year/add', [App\Http\Controllers\FinancialYearController::class, 'add']);
Route::post('/application_settings/financial_year/update', [App\Http\Controllers\FinancialYearController::class, 'update']);
Route::post('/application_settings/financial_year/delete', [App\Http\Controllers\FinancialYearController::class, 'delete']);

/* User & Roles */
Route::get('/application_settings/user_and_roles', [App\Http\Controllers\UserAndRolesController::class, 'user_and_roles']);
Route::get('/application_settings/user_and_roles/fetch', [App\Http\Controllers\UserAndRolesController::class, 'fetch']);
Route::post('/application_settings/user_and_roles/add', [App\Http\Controllers\UserAndRolesController::class, 'add']);
Route::post('/application_settings/user_and_roles/update', [App\Http\Controllers\UserAndRolesController::class, 'update']);
Route::post('/application_settings/user_and_roles/delete', [App\Http\Controllers\UserAndRolesController::class, 'delete']);

/* Preferance */
Route::get('/application_settings/preference', [App\Http\Controllers\PreferenceController::class, 'preference']);
Route::get('/application_settings/preference/fetch', [App\Http\Controllers\PreferenceController::class, 'fetch']);
Route::post('/application_settings/preference/save', [App\Http\Controllers\PreferenceController::class, 'save']);


View::composer(['*'], function($view){
    $fy = FinancialYear::where('is_current_year', 'Yes')->first();
    $view->with('fy', $fy);
});