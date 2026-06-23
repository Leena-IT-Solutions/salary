<template>
    <div class="shift-manager-container animate__animated animate__fadeIn">
        
        <!-- Filters Header -->
        <div class="card border-0 shadow-premium mb-4 overflow-hidden">
            <div class="card-body p-4 bg-white">
                <div class="row align-items-end g-3">
                    <div class="col">
                        <h5 class="fw-bold mb-3 d-flex align-items-center">
                            <i class="bi bi-funnel me-2 text-primary"></i> Filter Employees
                        </h5>
                        <div class="row g-3">
                            <forms-select-field
                                @change="fetch()"
                                name="work_location_id" 
                                label="Work Location" 
                                v-model="employeeFilter.work_location_id" 
                                error="" 
                                classes="col-12 col-md-6" 
                                :options="work_locations">
                            </forms-select-field>

                            <forms-select-field
                                @change="fetch()"
                                name="department_id" 
                                label="Departments" 
                                v-model="employeeFilter.department_id" 
                                error="" 
                                classes="col-12 col-md-6" 
                                :options="location_departments">
                            </forms-select-field>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Employee List Table -->
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-premium h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold m-0">Employee Roster</h5>
                        <span class="badge bg-primary rounded-pill">{{ employees.length }} Members</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive custom-scrollbar" style="max-height: 600px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="ps-4 border-0">ID</th>
                                        <th class="border-0">Employee Name</th>
                                        <th class="border-0 text-center">Department/Location</th>
                                        <th class="border-0 text-center pe-4">Current Shift</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="emp in employees" :key="emp.id" 
                                        @click="addRemoveEmployee(emp.id)"
                                        class="cursor-pointer transition-all"
                                        :class="{'table-active-premium': isSelected(emp.id)}">
                                        <td class="ps-4">
                                            <span class="text-secondary small fw-bold">#{{ emp.id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-3 bg-primary bg-opacity-10 text-primary">
                                                    {{ (emp.first_name && emp.first_name.length > 0) ? emp.first_name.charAt(0).toUpperCase() : '?' }}{{ (emp.last_name && emp.last_name.length > 0) ? emp.last_name.charAt(0).toUpperCase() : '' }}
                                                </div>
                                                <div>
                                                    <p class="mb-0 fw-bold text-dark">{{ emp.first_name }} {{ emp.last_name }}</p>
                                                    <p class="mb-0 text-muted small">{{ emp.middle_name || '' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span v-if="emp.employee_department && emp.employee_department.department" class="badge bg-light text-dark fw-normal mb-1 d-block">
                                                {{ emp.employee_department.department.department }}
                                            </span>
                                            <span v-else class="text-muted small d-block mb-1 opacity-50">No Department</span>

                                            <span v-if="emp.employee_work_location && emp.employee_work_location.work_location" class="text-muted small d-block">
                                                {{ emp.employee_work_location.work_location.location_name }}
                                            </span>
                                            <span v-else class="text-muted small d-block opacity-50">No Location</span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <template v-if="emp.employee_shift && emp.employee_shift.working_shift">
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-1">
                                                        <i class="bi bi-clock-history me-1"></i>
                                                        {{ emp.employee_shift.working_shift.name }}
                                                    </span>
                                                    <span class="text-secondary small fw-500">
                                                        <i class="bi bi-calendar3 me-1 tiny-text"></i>
                                                        {{ formatDate(emp.employee_shift.dt) }}
                                                    </span>
                                                </div>
                                            </template>
                                            <span v-else class="text-muted small">Not Assigned</span>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shift Assignment Panel -->
            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-premium sticky-top" style="top: 2rem;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4 border-bottom pb-2">
                            <i class="bi bi-calendar-check me-2 text-primary"></i> Assign Shift
                        </h5>
                        
                        <div v-if="employeeShiftForm.employees.length === 0" class="alert alert-soft-primary mb-4 border-0">
                            <i class="bi bi-info-circle me-2"></i> Please select employees from the list.
                        </div>
                        <div v-else class="mb-4">
                            <span class="badge bg-primary w-100 py-2 fs-6 mb-3">
                                {{ employeeShiftForm.employees.length }} Employees Selected
                            </span>
                        </div>

                        <div class="row g-3">
                            <forms-select-field name="working_shift_id" label="Shift Type" v-model="employeeShiftForm.working_shift_id" error="" classes="col-12" :options="shifts"></forms-select-field>
            
                            <forms-date-field name="from" label="Effective From" v-model="employeeShiftForm.from" error="" classes="col-12"></forms-date-field>
            
                            <forms-date-field name="to" label="Effective To" v-model="employeeShiftForm.to" error="" classes="col-12"></forms-date-field>

                            <div class="col-12 mt-4">
                                <button class="btn btn-primary w-100 py-3 fw-bold" 
                                        @click="assignShift()" 
                                        :disabled="loading || employeeShiftForm.employees.length === 0">
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    <i v-else class="bi bi-check2-circle me-2"></i>
                                    Assign Shift Now
                                </button>
                                <button class="btn btn-link w-100 mt-2 text-muted text-decoration-none small" @click="reset()">
                                    Clear Selection
                                </button>
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
    props: ['shifts', 'locations', 'departments'],
    data(){
        return {
            loading: false,
            work_locations: [],
            location_departments: [],
            employeeFilter: { work_location_id: 0, department_id: 0 },
            employeeShiftForm: { employees: [], working_shift_id: null, from: null, to: null },
            employees: [],
        };
    },
    methods: {
        reset(){
            this.employeeShiftForm.employees = [];
            this.employeeShiftForm.working_shift_id = null;
            this.employeeShiftForm.from = null;
            this.employeeShiftForm.to = null;
        },
        assignShift(){
            this.loading = true;
            axios.post('/employee_shift/save', this.employeeShiftForm)
            .then(() => {
                this.loading = false;
                this.reset();
                this.fetch();
            })
        },
        isSelected(id){
            return this.employeeShiftForm.employees.includes(id);
        },
        addRemoveEmployee(id){
            let arr = this.employeeShiftForm.employees;
            if(arr.includes(id)){
                arr.splice(arr.indexOf(id), 1)
            } else {
                arr.push(id);
            }
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const d = new Date(dateString);
            return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        },
        fetch(){
            axios.get('/employee_shift/employee/fetch', {
                params: this.employeeFilter
            })
            .then(res => {
                this.employees = res.data;
            });
        },
        addSelectAllOption(){
            let option = { val: "0", key: "All Locations / Depts" };
            this.work_locations = [option, ...this.locations];
            this.location_departments = [option, ...this.departments];
        },
    },
    created(){
        this.addSelectAllOption();
        this.fetch();
    },
}
</script>

<style scoped lang="scss">
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.2s ease-in-out; }

.table-hover tbody tr:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.02);
    transform: scale(1.002);
}

.table-active-premium {
    background-color: rgba(var(--bs-primary-rgb), 0.05) !important;
    border-left: 4px solid var(--bs-primary) !important;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.alert-soft-primary {
    background-color: rgba(var(--bs-primary-rgb), 0.08);
    color: var(--bs-primary);
}

.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }

.tiny-text { font-size: 0.7rem; }

.sticky-top {
    z-index: 100;
}
</style>