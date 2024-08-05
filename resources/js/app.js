/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp } from 'vue';
import '@fortawesome/fontawesome-free/js/all.js';

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({});

import AppNavigation from './components/AppNavigation.vue';
import PageHeader from './components/elements/PageHeader.vue';
import SectionTitle from './components/elements/SectionTitle.vue';

/* Calender Imports */
import Calender from './components/elements/calender/Calender.vue';
import YearForm from './components/elements/calender/YearForm.vue';

/* Employee Shift Manager */
import EmployeeShiftManager from './components/elements/employee_shift/EmployeeShiftManager.vue';

/* Attendance */
import EmployeeAttendance from './components/elements/attendance/EmployeeAttendance.vue';

/* Form Imports */
import TextField from './components/forms/TextField.vue';
import NumberField from './components/forms/NumberField.vue';
import TimeField from './components/forms/TimeField.vue';
import DateField from './components/forms/DateField.vue';
import TextareaField from './components/forms/TextareaField.vue';
import SubmitButton from './components/forms/SubmitButton.vue';
import SelectField from './components/forms/SelectField.vue';
import RadioField from './components/forms/RadioField.vue';
import CheckboxField from './components/forms/CheckboxField.vue';
import CheckboxIs from './components/forms/CheckboxIs.vue';
import FileField from './components/forms/FileField.vue';
import MyDemoForm from './components/MyDemoForm.vue';

/* Organisation Settings Imports */
import LogoUpload from './components/elements/organisation_settings/LogoUpload.vue';
import CompanyProfile from './components/elements/organisation_settings/CompanyProfile.vue';
import CompanyRegistration from './components/elements/organisation_settings/CompanyRegistration.vue';
import WorkLocations from './components/elements/organisation_settings/WorkLocations.vue';
import DepartmentsForm from './components/elements/organisation_settings/DepartmentsForm.vue';
import DesignationsForm from './components/elements/organisation_settings/DesignationsForm.vue';
import WorkingShiftForm from './components/elements/organisation_settings/WorkingShiftForm.vue';
import LeaveTypeForm from './components/elements/organisation_settings/LeaveTypeForm.vue';
import LeaveGroupForm from './components/elements/organisation_settings/LeaveGroupForm.vue';

/* Salary Settings Import */
import EarningsComponent from './components/elements/salary_settings/EarningsComponent.vue';

/* Employee Imports */
import EmployeeManager from './components/elements/employee/EmployeeManager.vue';
import EmployeeUpdate from './components/elements/employee/EmployeeUpdate.vue';
import EmployeePhoto from './components/elements/employee/EmployeePhoto.vue';
import EmployeeDocuments from './components/elements/employee/EmployeeDocuments.vue';
import EmployeeAddress from './components/elements/employee/EmployeeAddress.vue';
import EmployeeWorkLocation from './components/elements/employee/EmployeeWorkLocation.vue';
import EmployeeDesignation from './components/elements/employee/EmployeeDesignation.vue';
import EmployeeDepartment from './components/elements/employee/EmployeeDepartment.vue';
import EmployeeLeaveGroup from './components/elements/employee/EmployeeLeaveGroup.vue';

/* Approvals Import */
import LeaveApproval from './components/elements/approvals/LeaveApproval.vue';

/* Application Settings Import */
import FinancialYear from './components/elements/application_settings/FinancialYear.vue';


app.component('app-navigation', AppNavigation);
app.component('page-header', PageHeader);
app.component('section-title', SectionTitle);

/* Calender Components */
app.component('app-calender', Calender);
app.component('year-form', YearForm);
app.component('employee-shift-manager', EmployeeShiftManager);
app.component('employee-attendance', EmployeeAttendance);


/* Form Components */
app.component('forms-text-field', TextField);
app.component('forms-number-field', NumberField);
app.component('forms-time-field', TimeField);
app.component('forms-date-field', DateField);
app.component('forms-textarea-field', TextareaField);
app.component('forms-submit-button', SubmitButton);
app.component('forms-select-field', SelectField);
app.component('forms-radio-field', RadioField);
app.component('forms-checkbox-field', CheckboxField);
app.component('forms-checkbox-is', CheckboxIs);
app.component('forms-file-field', FileField);
app.component('my-demo-form', MyDemoForm);

/* Organisation Settings Components */
app.component('logo-upload', LogoUpload);
app.component('company-profile', CompanyProfile);
app.component('company-registration', CompanyRegistration);
app.component('work-locations', WorkLocations);
app.component('departments-form', DepartmentsForm);
app.component('designations-form', DesignationsForm);
app.component('working-shift-form', WorkingShiftForm);
app.component('leave-type-form', LeaveTypeForm);
app.component('leave-group-form', LeaveGroupForm);

/* Salary Settings Components */
app.component('earnings-component', EarningsComponent);

/* Employee Components */
app.component('employee-manager', EmployeeManager);
app.component('employee-update', EmployeeUpdate);
app.component('employee-photo', EmployeePhoto);
app.component('employee-documents', EmployeeDocuments);
app.component('employee-address', EmployeeAddress);
app.component('employee-work-location', EmployeeWorkLocation);
app.component('employee-designation', EmployeeDesignation);
app.component('employee-department', EmployeeDepartment);
app.component('employee-leave-group', EmployeeLeaveGroup);

/* Approvals Components */
app.component('leave-approval', LeaveApproval);


/* Application Settings Components */
app.component('financial-year', FinancialYear);



/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.mount('#app');

import AOS from 'aos';
import 'aos/dist/aos.css';
AOS.init({
    duration: 2000,
});