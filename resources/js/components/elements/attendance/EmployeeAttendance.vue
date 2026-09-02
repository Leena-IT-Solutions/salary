<template>
    <div class="attendance-wrapper pb-5">
        <div class="attendance-container animate__animated animate__fadeIn">
            
            <!-- Premium Filter & Nav Section -->
            <div class="card border-0 shadow-premium mb-4 overflow-hidden">
                <div class="card-body p-4 bg-white">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-6">
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
                        
                        <div class="col-lg-6 border-start-lg">
                            <div class="d-flex align-items-center justify-content-center flex-column flex-sm-row gap-2">
                                <div class="d-flex align-items-center">
                                    <button class="btn btn-outline-primary btn-icon rounded-circle me-3 shadow-sm" @click="prevDay()">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    
                                    <div class="position-relative">
                                        <div class="text-center px-4 py-2 bg-light rounded-pill shadow-sm border cursor-pointer hover-lift" 
                                             @click="triggerDatePicker"
                                             title="Click to select a date">
                                            <h5 class="fw-bold m-0 text-primary d-flex align-items-center justify-content-center">
                                                <i class="bi bi-calendar-event me-2"></i>
                                                {{ formatDisplayDate(employeeFilter.current_date) }}
                                                <i class="bi bi-caret-down-fill ms-2 small opacity-50"></i>
                                            </h5>
                                        </div>
                                        <!-- Hidden Date Input -->
                                        <input type="date" ref="datePicker" 
                                               class="position-absolute translate-middle opacity-0" 
                                               style="top: 50%; left: 50%; width: 100%; z-index: -1;"
                                               v-model="employeeFilter.current_date" 
                                               @change="fetch()">
                                    </div>

                                    <button class="btn btn-outline-primary btn-icon rounded-circle ms-3 shadow-sm" @click="nextDay()">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>

                                <!-- Calendar Status Badge -->
                                <span class="badge px-3 py-2 rounded-pill shadow-xs fw-bold d-inline-flex align-items-center gap-1.5 ms-sm-2"
                                      :class="calendarStatus.badgeClass" style="font-size: 0.85rem;">
                                    <i :class="['bi', calendarStatus.icon]"></i>
                                    {{ calendarStatus.label }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4 legend-container">
                <span class="legend-item bg-danger-subtle text-danger border border-danger border-opacity-10 py-1 px-3 rounded-pill small fw-bold">
                    <i class="bi bi-house-door me-1"></i> Weekoff / Holiday
                </span>
                <span class="legend-item bg-secondary-subtle text-secondary border border-secondary border-opacity-10 py-1 px-3 rounded-pill small fw-bold">
                    <i class="bi bi-person-x me-1"></i> Absent
                </span>
                <span class="legend-item bg-success-subtle text-success border border-success border-opacity-10 py-1 px-3 rounded-pill small fw-bold">
                    <i class="bi bi-card-checklist me-1"></i> Leave / Halfday
                </span>
                <span class="legend-item bg-warning-subtle text-warning border border-warning border-opacity-10 py-1 px-3 rounded-pill small fw-bold">
                    <i class="bi bi-clock-history me-1"></i> Time Update / On Duty
                </span>
            </div>

            <!-- Attendance Roster -->
            <div class="card border-0 shadow-premium overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <h5 class="fw-bold m-0"><i class="bi bi-people-fill text-primary me-2"></i> Attendance Log</h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">{{ filteredEmployees.length }} Records</span>
                        <span class="badge px-3 py-1.5 rounded-pill fw-bold" :class="calendarStatus.badgeClass">
                            <i :class="['bi', calendarStatus.icon]" class="me-1"></i> {{ calendarStatus.label }}
                        </span>
                    </div>
                    <div class="search-container position-relative" style="min-width: 300px;">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" v-model="searchQuery" class="form-control rounded-pill ps-5 border-light-subtle shadow-sm" placeholder="Search by name or employee code...">
                        <button v-if="searchQuery" @click="searchQuery = ''" class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-2 text-muted p-0"><i class="bi bi-x-circle-fill"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive custom-scrollbar">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-4 border-0">Employee</th>
                                    <th class="border-0">Code</th>
                                    <th class="border-0">Shift Information</th>
                                    <th class="border-0 text-center">Status</th>
                                    <th class="border-0 text-center">Stats (LOP/Late/Early)</th>
                                    <th class="border-0 text-end pe-4">Punch Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="emp in filteredEmployees" :key="emp.id">
                                    <tr v-if="emp.employee_shifts && emp.employee_shifts.length > 0"
                                        class="transition-all"
                                        :class="getStatusRowClass(emp.employee_shifts[0].status)">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ emp.first_name }} {{ emp.last_name }}</div>
                                            <div class="text-muted small">{{ emp.middle_name }}</div>
                                        </td>
                                        <td><span class="badge bg-light text-dark font-monospace fs-6 px-3 py-2 border">{{ emp.employee_code }}</span></td>
                                        <td>
                                            <div class="fw-500">{{ emp.employee_shifts[0].working_shift.name }}</div>
                                            <div class="small text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ emp.employee_shifts[0].working_shift.in }} - {{ emp.employee_shifts[0].working_shift.out }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge px-3 py-2 fw-bold d-inline-flex align-items-center" :class="getStatusBadgeClass(emp.employee_shifts[0].status)">
                                                <i :class="getStatusIcon(emp.employee_shifts[0].status)" class="me-1"></i>
                                                {{ emp.employee_shifts[0].status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="stats-grid">
                                                <span class="stat-bubble" title="LOP">L: {{ emp.employee_shifts[0].lop }}</span>
                                                <span class="stat-bubble ms-1" :class="{'text-danger': emp.employee_shifts[0].late > 0}" title="Late">↑ {{ emp.employee_shifts[0].late }}m</span>
                                                <span class="stat-bubble ms-1 text-primary-soft" title="Early">↓ {{ emp.employee_shifts[0].early }}m</span>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex flex-wrap justify-content-end gap-1 align-items-center">
                                                <template v-if="emp.employee_shifts[0].employee_attendance.length > 0">
                                                    <button v-for="att in emp.employee_shifts[0].employee_attendance" :key="att.id"
                                                          @click="openEditModal(emp.employee_shifts[0], emp.first_name + ' ' + emp.last_name)"
                                                          class="btn btn-xs btn-light text-primary border border-primary border-opacity-10 hover-lift shadow-xs fw-bold rounded-pill px-2 py-0"
                                                          style="font-size: 0.7rem; line-height: 1.4;">
                                                        {{ att.tm }}
                                                    </button>
                                                </template>
                                                <button v-else @click="openEditModal(emp.employee_shifts[0], emp.first_name + ' ' + emp.last_name)"
                                                        class="btn btn-sm btn-outline-primary rounded-circle border-dashed p-0 hover-lift d-flex align-items-center justify-content-center"
                                                        style="width: 24px; height: 24px;"
                                                        title="Add Attendance">
                                                    <i class="bi bi-plus-lg" style="font-size: 0.75rem;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Evaluation Footer -->
            <div class="card border-0 shadow-premium bg-white mt-4 border-top border-primary border-3">
                <div class="card-body p-4 text-center">
                    <div class="d-flex align-items-center justify-content-center flex-column flex-md-row gap-3">
                        <div class="text-md-start">
                            <h6 class="fw-bold mb-1 text-dark">Process Attendance Data</h6>
                            <p class="text-muted mb-0 small">Finalize LOP and late calculations for the selected date.</p>
                        </div>
                        <div class="d-flex gap-3">
                            <button class="btn btn-white border-primary border-2 text-primary btn-lg px-4 fw-bold shadow-sm hover-lift" @click="autoUpdateTimeAll()" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="bi bi-magic me-2"></i>
                                Auto-Fill Missing Times
                            </button>
                            <button class="btn btn-primary btn-lg px-5 fw-bold shadow-lg hover-lift" @click="evaluteLOP()" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="bi bi-lightning-charge-fill me-2"></i>
                                Evaluate LOP Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Move Modal OUTSIDE the animated div to fix stacking context (Behind Backdrop Issue) -->
        <div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-hidden="true" ref="editModal">
            <div class="modal-dialog modal-dialog-centered" :class="modalMode === 'paycycle' ? 'modal-xl modal-dialog-scrollable' : ''">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    <!-- Modal Header with Premium Segmented Tabs -->
                    <div class="modal-header border-0 py-3 px-4 flex-column align-items-stretch" style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background-color: rgba(255, 255, 255, 0.2) !important;">
                                    <i class="bi bi-clock-history fs-4" style="color: #ffffff !important;"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold m-0 fs-5" style="color: #ffffff !important;">Adjust Time Entry</h5>
                                    <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                                        <span class="small mb-0 fw-medium" style="color: rgba(255, 255, 255, 0.8) !important;">{{ editForm.employee_name }}</span>
                                        <span v-if="modalMode === 'single' && editForm.on_date" 
                                              class="badge rounded-pill small fw-bold px-3 py-1" 
                                              style="background-color: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.3) !important;">
                                            <i class="bi bi-calendar-event me-1.5" style="color: #fbbf24 !important;"></i>{{ formatDateAndDay(editForm.on_date) }}
                                        </span>
                                        <span v-if="modalMode === 'paycycle' && payCycleData.from" 
                                              class="badge rounded-pill small fw-bold px-3 py-1" 
                                              style="background-color: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.3) !important;">
                                            <i class="bi bi-calendar3 me-1.5" style="color: #fbbf24 !important;"></i>Pay Cycle: {{ formatDateShort(payCycleData.from) }} — {{ formatDateShort(payCycleData.to) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <!-- Mode Switcher Segmented Control -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 pt-2" style="border-top: 1px solid rgba(255, 255, 255, 0.2) !important;">
                            <div class="p-1 rounded-pill d-inline-flex gap-1" style="background-color: rgba(0, 0, 0, 0.25) !important;">
                                <button type="button" 
                                        class="btn btn-sm rounded-pill fw-bold px-3.5 py-1.5 text-nowrap transition-all border-0"
                                        :style="modalMode === 'single' ? 'background-color: #ffffff !important; color: #4f46e5 !important; font-weight: 700 !important; box-shadow: 0 2px 6px rgba(0,0,0,0.2);' : 'background-color: transparent !important; color: rgba(255, 255, 255, 0.85) !important; font-weight: 600 !important;'"
                                        @click="switchModalMode('single')">
                                    <i class="bi bi-calendar-day me-1.5"></i>Selected Date
                                </button>
                                <button type="button" 
                                        class="btn btn-sm rounded-pill fw-bold px-3.5 py-1.5 text-nowrap transition-all border-0"
                                        :style="modalMode === 'paycycle' ? 'background-color: #ffffff !important; color: #4f46e5 !important; font-weight: 700 !important; box-shadow: 0 2px 6px rgba(0,0,0,0.2);' : 'background-color: transparent !important; color: rgba(255, 255, 255, 0.85) !important; font-weight: 600 !important;'"
                                        @click="switchModalMode('paycycle')">
                                    <i class="bi bi-calendar-range me-1.5"></i>Full Pay Cycle
                                </button>
                            </div>

                            <div v-if="modalMode === 'paycycle'">
                                <button class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold transition-all" 
                                        style="background-color: #ffffff !important; color: #4f46e5 !important; border: none !important; box-shadow: 0 2px 6px rgba(0,0,0,0.15);"
                                        @click="fillAllPayCycleMissing()">
                                    <i class="bi bi-magic me-1.5" style="color: #d97706 !important;"></i>Auto-Fill All Missing
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4">
                        
                        <!-- Single Date Mode -->
                        <div v-if="modalMode === 'single'">
                            <div class="alert alert-info border-0 bg-info bg-opacity-10 rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-info-circle-fill me-1"></i>
                                    <span class="small">Modifying times will automatically re-evaluate LOP and Status for this record.</span>
                                </div>
                                <div v-if="editForm.on_date" class="badge bg-white text-dark border px-3 py-1.5 rounded-2 fw-bold text-nowrap">
                                    <i class="bi bi-calendar3 text-primary me-1.5"></i>{{ formatDateAndDay(editForm.on_date) }}
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-900 text-uppercase tracking-wider text-muted mb-2">Punch IN Time</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-success"><i class="bi bi-login"></i></span>
                                        <input type="time" class="form-control border-start-0 ps-0" v-model="editForm.in_time">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-900 text-uppercase tracking-wider text-muted mb-2">Punch OUT Time</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-danger"><i class="bi bi-logout"></i></span>
                                        <input type="time" class="form-control border-start-0 ps-0" v-model="editForm.out_time">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Full Pay Cycle Mode -->
                        <div v-if="modalMode === 'paycycle'">
                            <div v-if="loadingPayCycle" class="text-center py-5">
                                <div class="spinner-border text-primary me-2"></div>
                                <div class="text-muted small mt-2">Loading pay cycle shifts...</div>
                            </div>

                            <div v-else>
                                <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-3 p-3 mb-3 d-flex align-items-center justify-content-between">
                                    <div class="small">
                                        <i class="bi bi-info-circle-fill me-1"></i>
                                        Review and modify punch times for all dates in the active pay cycle. Click <strong>Save Pay Cycle Changes</strong> to apply updates.
                                    </div>
                                    <div class="fw-bold small text-primary text-nowrap ms-2">
                                        {{ payCycleData.shifts.length }} Days in Cycle
                                    </div>
                                </div>

                                <div class="table-responsive custom-scrollbar" style="max-height: 400px;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light sticky-top">
                                            <tr class="small text-uppercase text-muted">
                                                <th style="width: 170px;">Date & Day</th>
                                                <th style="width: 120px;">Status</th>
                                                <th>Shift Hours</th>
                                                <th style="width: 150px;">Punch IN</th>
                                                <th style="width: 150px;">Punch OUT</th>
                                                <th class="text-end" style="width: 120px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="row in payCycleData.shifts" :key="row.dt"
                                                :class="{'table-warning bg-warning bg-opacity-10': row.day_name === 'Sunday' || row.status === 'Weekoff', 'table-danger bg-danger bg-opacity-10': row.special_day === 'Holiday' || row.status === 'Holiday', 'table-active': row.dt === editForm.on_date}">
                                                <td>
                                                    <div class="fw-bold text-dark">{{ row.formatted_date }}</div>
                                                    <div class="text-muted small">{{ row.day_name }}</div>
                                                </td>
                                                <td>
                                                    <span class="badge px-2.5 py-1 fw-bold" :class="getStatusBadgeClass(row.status)">
                                                        {{ row.status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="small fw-semibold text-dark">{{ row.working_shift_name }}</div>
                                                    <div class="small text-muted">{{ row.std_in }} - {{ row.std_out }}</div>
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control form-control-sm" v-model="row.in_time" @change="row.is_modified = true">
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control form-control-sm" v-model="row.out_time" @change="row.is_modified = true">
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <button class="btn btn-xs btn-outline-primary rounded px-2" @click="fillRowStandard(row)" title="Fill standard shift times">
                                                            <i class="bi bi-clock-history"></i>
                                                        </button>
                                                        <button class="btn btn-xs btn-outline-secondary rounded px-2" @click="clearRowTimes(row)" title="Clear times">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light border-0 py-3 px-4 justify-content-between">
                        <template v-if="modalMode === 'single'">
                            <button type="button" class="btn btn-outline-danger border-0 fw-bold px-3" @click="deleteTimes()" :disabled="deleting">
                                <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
                                <i v-else class="bi bi-trash3 me-1"></i>
                                Delete Record
                            </button>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" @click="saveTimes()" :disabled="saving">
                                    <span v-show="saving" class="spinner-border spinner-border-sm me-2"></span>
                                    Update Record
                                </button>
                            </div>
                        </template>

                        <template v-if="modalMode === 'paycycle'">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3" @click="fillAllPayCycleMissing()">
                                    <i class="bi bi-magic me-1"></i>Auto-Fill All Missing
                                </button>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" @click="savePayCycleTimes()" :disabled="savingPayCycle">
                                    <span v-show="savingPayCycle" class="spinner-border spinner-border-sm me-2"></span>
                                    <i class="bi bi-check2-circle me-1.5"></i>Save Pay Cycle Changes
                                </button>
                            </div>
                        </template>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import * as bootstrap from 'bootstrap';

export default {
    props: ['locations', 'departments', 'today', 'month'],
    data(){
        return {
            loading: false,
            work_locations: [],
            location_departments: [],
            months: ["01","02","03","04","05","06","07","08","09","10","11","12"],
            month_name: ["January","February","March","April","May","June","July","August","Septmber","October","November","December"],
            day_count: [31,28,31,30,31,30,31,31,30,31,30,31],
            employeeFilter: {
                work_location_id: 0,
                department_id: 0,
                report_type: 'Daily',
                current_date: null,
                current_month: null,
                current_year: null,
                current_month_name: null
            },
            employees: [],
            specialDayInfo: null,
            saving: false,
            deleting: false,
            modalMode: 'single',
            loadingPayCycle: false,
            savingPayCycle: false,
            payCycleData: {
                employee_id: null,
                from: null,
                to: null,
                shifts: []
            },
            editModal: null,
            editForm: {
                employee_id: null,
                employee_shift_id: null,
                employee_name: '',
                in_time: null,
                out_time: null
            },
            searchQuery: ''
        };
    },
    computed: {
        filteredEmployees() {
            if (!this.searchQuery) return this.employees;
            const query = this.searchQuery.toLowerCase();
            return this.employees.filter(emp => {
                const fullName = `${emp.first_name} ${emp.middle_name || ''} ${emp.last_name}`.toLowerCase();
                const code = (emp.employee_code || '').toLowerCase();
                return fullName.includes(query) || code.includes(query);
            });
        },
        calendarStatus() {
            if (this.specialDayInfo) {
                const type = this.specialDayInfo.day_type;
                const remark = this.specialDayInfo.remark;
                let label = type;
                if (remark && remark !== 'wo' && remark.toLowerCase() !== type.toLowerCase()) {
                    label += ` (${remark})`;
                }
                return {
                    label: label,
                    type: type,
                    badgeClass: type === 'Holiday' ? 'bg-danger text-white' : (type === 'Weekoff' ? 'bg-warning text-dark' : 'bg-info text-white'),
                    icon: type === 'Holiday' ? 'bi-gift-fill' : (type === 'Weekoff' ? 'bi-house-door-fill' : 'bi-sun-fill')
                };
            }
            if (this.employeeFilter.current_date) {
                const parts = this.employeeFilter.current_date.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    if (d.getDay() === 0) { // Sunday
                        return {
                            label: 'Sunday Weekoff',
                            type: 'Weekoff',
                            badgeClass: 'bg-warning text-dark',
                            icon: 'bi-house-door-fill'
                        };
                    }
                }
            }
            return {
                label: 'Working Day',
                type: 'Working Day',
                badgeClass: 'bg-success text-white',
                icon: 'bi-briefcase-fill'
            };
        }
    },
    methods: {
        deleteTimes() {
            if (confirm('Are you sure you want to delete all punch records for this shift?')) {
                this.deleting = true;
                axios.post('/attendance/delete_times', {
                    employee_shift_id: this.editForm.employee_shift_id
                })
                .then(res => {
                    this.deleting = false;
                    this.editModal.hide();
                    this.fetch();
                })
                .catch(err => {
                    this.deleting = false;
                    alert('Error deleting records.');
                });
            }
        },
        parseTime(ts) {
            if (!ts) return 0;
            let h = 0, m = 0, s = 0;
            const cleanTs = ts.trim();
            if (/am|pm/i.test(cleanTs)) {
                const match = cleanTs.match(/(\d+):(\d+)(?::(\d+))?\s*(am|pm)/i);
                if (match) {
                    h = parseInt(match[1]);
                    m = parseInt(match[2]);
                    s = parseInt(match[3] || 0);
                    const ampm = match[4].toLowerCase();
                    if (ampm === 'pm' && h < 12) h += 12;
                    if (ampm === 'am' && h === 12) h = 0;
                }
            } else {
                const parts = cleanTs.split(':');
                h = parseInt(parts[0]);
                m = parseInt(parts[1] || 0);
                s = parseInt(parts[2] || 0);
            }
            return h * 3600 + m * 60 + s;
        },
        formatDateAndDay(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                const year = parseInt(parts[0]);
                const month = parseInt(parts[1]) - 1;
                const day = parseInt(parts[2]);
                const d = new Date(year, month, day);
                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const dayName = days[d.getDay()];
                const monthName = months[d.getMonth()];
                const formattedDay = day < 10 ? '0' + day : day;
                return `${formattedDay} ${monthName} ${year}, ${dayName}`;
            }
            return dateStr;
        },
        switchModalMode(mode) {
            this.modalMode = mode;
            if (mode === 'paycycle' && (!this.payCycleData.shifts || this.payCycleData.shifts.length === 0 || this.payCycleData.employee_id !== this.editForm.employee_id)) {
                this.fetchPayCycleShifts();
            }
        },
        fetchPayCycleShifts() {
            this.loadingPayCycle = true;
            axios.get('/attendance/employee_paycycle_shifts', {
                params: {
                    employee_id: this.editForm.employee_id,
                    current_date: this.editForm.on_date || this.employeeFilter.current_date
                }
            })
            .then(res => {
                this.loadingPayCycle = false;
                this.payCycleData = {
                    employee_id: this.editForm.employee_id,
                    from: res.data.from,
                    to: res.data.to,
                    shifts: res.data.shifts || []
                };
            })
            .catch(err => {
                this.loadingPayCycle = false;
                alert('Error loading pay cycle shifts.');
            });
        },
        fillRowStandard(row) {
            row.in_time = row.std_in;
            row.out_time = row.std_out;
            row.is_modified = true;
        },
        clearRowTimes(row) {
            row.in_time = null;
            row.out_time = null;
            row.is_modified = true;
        },
        fillAllPayCycleMissing() {
            if (!this.payCycleData.shifts) return;
            this.payCycleData.shifts.forEach(row => {
                if (!row.in_time && !row.out_time && row.status !== 'Weekoff' && row.status !== 'Holiday') {
                    row.in_time = row.std_in;
                    row.out_time = row.std_out;
                    row.is_modified = true;
                }
            });
        },
        savePayCycleTimes() {
            const modifiedShifts = this.payCycleData.shifts.filter(s => s.is_modified);
            if (modifiedShifts.length === 0) {
                alert('No changes detected in the pay cycle roster.');
                return;
            }
            this.savingPayCycle = true;
            axios.post('/attendance/batch_update_times', {
                employee_id: this.editForm.employee_id,
                shifts: modifiedShifts
            })
            .then(res => {
                this.savingPayCycle = false;
                this.editModal.hide();
                this.fetch();
            })
            .catch(err => {
                this.savingPayCycle = false;
                alert('Error saving pay cycle changes.');
            });
        },
        formatDateShort(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                return `${parseInt(parts[2])} ${months[parseInt(parts[1]) - 1]} ${parts[0]}`;
            }
            return dateStr;
        },
        openEditModal(shift, name) {
            this.editForm.employee_id = shift ? shift.employee_id : null;
            this.editForm.employee_shift_id = shift ? shift.id : null;
            this.editForm.employee_name = name;
            this.editForm.on_date = shift ? shift.dt : this.employeeFilter.current_date;
            this.modalMode = 'single';
            this.payCycleData = { employee_id: null, from: null, to: null, shifts: [] };
            
            const attendance = shift.employee_attendance;
            const shiftIn = shift.working_shift.in;
            const shiftOut = shift.working_shift.out;

            if (attendance.length === 0) {
                // Both punches missed - use standard shift times
                this.editForm.in_time = shiftIn;
                this.editForm.out_time = shiftOut;
            } else if (attendance.length === 1) {
                // One punch record - decide if it's In or Out based on proximity to shift times
                const singlePunch = attendance[0].tm;
                const punchSec = this.parseTime(singlePunch);
                const inSec = this.parseTime(shiftIn);
                const outSec = this.parseTime(shiftOut);

                // Calculate distances
                let distToIn = Math.abs(punchSec - inSec);
                let distToOut = Math.abs(punchSec - outSec);

                // Handle night shifts (Out < In)
                if (outSec < inSec) {
                    // For night shifts, if punch is in early morning, it's closer to Out of previous day
                    // If punch is late evening, it's closer to In
                    if (punchSec < 12 * 3600) { // Morning (e.g. 05:00)
                        distToIn = Math.abs((punchSec + 24 * 3600) - inSec); // Distance to 21:00 is (05:00+24) - 21:00 = 8h
                        distToOut = Math.abs(punchSec - outSec); // Distance to 06:00 is 1h
                    } else { // Evening (e.g. 21:30)
                        distToIn = Math.abs(punchSec - inSec); // Distance to 21:00 is 0.5h
                        distToOut = Math.abs(punchSec - (outSec + 24 * 3600)); // Distance to 06:00(next day) is (06:00+24) - 21:30 = 8.5h
                    }
                }

                if (distToIn <= distToOut) {
                    // Closer to In punch, so we set it as In and assume standard Out
                    this.editForm.in_time = singlePunch;
                    this.editForm.out_time = shiftOut;
                } else {
                    // Closer to Out punch, so we set it as Out and assume standard In
                    this.editForm.in_time = shiftIn;
                    this.editForm.out_time = singlePunch;
                }
            } else {
                // Multiple punches - set first as In and last as Out
                this.editForm.in_time = attendance[0].tm;
                this.editForm.out_time = attendance[attendance.length - 1].tm;
            }

            if (!this.editModal) {
                this.editModal = new bootstrap.Modal(document.getElementById('editAttendanceModal'));
            }
            this.editModal.show();
        },
        saveTimes() {
            this.saving = true;
            axios.post('/attendance/update_times', this.editForm)
            .then(res => {
                this.saving = false;
                this.editModal.hide();
                this.fetch();
            })
            .catch(err => {
                this.saving = false;
                alert('Error updating times. Please try again.');
            });
        },
        autoUpdateTimeAll() {
            if (confirm('This will fill missing punch data for all employees based on their standard shifts for today. Continue?')) {
                this.loading = true;
                axios.post('/attendance/auto_update_all', {
                    on_date: this.employeeFilter.current_date
                })
                .then(res => {
                    this.loading = false;
                    this.fetch();
                })
                .catch(err => {
                    this.loading = false;
                    alert('Error auto-updating times.');
                });
            }
        },
        evaluteLOP(){
            this.loading = true;
            const employeeIds = this.employees.map(employee => employee.id);

            axios.post('/attendance/evalute', {
                on_date: this.employeeFilter.current_date,
                employee_ids: employeeIds,
            })
            .then(() => {
                this.loading = false;
                this.fetch();
            })
            .catch(err => {
                this.loading = false;
                alert('Error evaluating LOP.');
            });
        },
        triggerDatePicker() {
            this.$refs.datePicker.showPicker();
        },
        setCurrentDate(dt){
            let d = new Date(dt);
            this.employeeFilter.current_date = `${d.getFullYear()}-${this.months[d.getMonth()]}-${d.getDate()}`;
            this.employeeFilter.current_month = this.months[d.getMonth()];
            this.employeeFilter.current_year = d.getFullYear();
            this.employeeFilter.current_month_name = this.month_name[d.getMonth()];
            this.day_count[1] = (this.employeeFilter.current_year % 4 === 0) ? 29 : 28;
        },
        formatDisplayDate(dateString) {
            if (!dateString) return '';
            const parts = dateString.split('-');
            if (parts.length === 3) {
                const year = parseInt(parts[0]);
                const month = parseInt(parts[1]) - 1;
                const day = parseInt(parts[2]);
                const d = new Date(year, month, day);
                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                const dayName = days[d.getDay()];
                const monthName = months[d.getMonth()];
                const formattedDay = day < 10 ? '0' + day : day;
                return `${formattedDay} ${monthName} ${year}, ${dayName}`;
            }
            return dateString;
        },
        nextDay(){
            let d = new Date(this.employeeFilter.current_date);
            d.setDate(d.getDate() + 1);
            this.setCurrentDate(d);
            this.fetch();
        },
        prevDay(){
            let d = new Date(this.employeeFilter.current_date);
            d.setDate(d.getDate() - 1);
            this.setCurrentDate(d);
            this.fetch();
        },
        fetch(){
            axios.get('/attendance/fetch', { params: this.employeeFilter })
            .then(res => {
                if (Array.isArray(res.data)) {
                    this.employees = res.data;
                    this.specialDayInfo = null;
                } else {
                    this.employees = res.data.employees || [];
                    this.specialDayInfo = res.data.special_day || null;
                }
            });
        },
        addSelectAllOption(){
            let option = { val: "0", key: "All" };
            this.work_locations = [option, ...this.locations];
            this.location_departments = [option, ...this.departments];
        },
        getStatusBadgeClass(status) {
            switch(status) {
                case 'Present': return 'bg-success bg-opacity-10 text-success';
                case 'Absent': return 'bg-secondary bg-opacity-10 text-secondary';
                case 'Weekoff':
                case 'Holiday': return 'bg-danger bg-opacity-10 text-danger';
                case 'On Duty':
                case 'Time Update': return 'bg-warning bg-opacity-10 text-warning';
                case 'Leave': 
                case 'Halfday Leave':
                case 'Short Leave': return 'bg-info bg-opacity-10 text-info';
                default: return 'bg-light text-dark';
            }
        },
        getStatusRowClass(status) {
            if (status === 'Absent') return 'bg-soft-gray bg-opacity-25';
            if (status === 'Weekoff' || status === 'Holiday') return 'bg-soft-red bg-opacity-25';
            return '';
        },
        getStatusIcon(status) {
            switch(status) {
                case 'Present': return 'bi bi-check-circle-fill';
                case 'Absent': return 'bi bi-x-circle-fill';
                case 'Weekoff':
                case 'Holiday': return 'bi bi-calendar-x-fill';
                case 'On Duty': return 'bi bi-geo-alt-fill';
                case 'Time Update': return 'bi bi-clock-fill';
                case 'Leave': return 'bi bi-file-earmark-text-fill';
                default: return 'bi bi-dot';
            }
        }
    },
    created(){
        this.setCurrentDate(this.today);
        this.addSelectAllOption();
        this.fetch();
    },
}
</script>

<style scoped lang="scss">
.min-width-200 { min-width: 200px; }
.btn-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; }
.shadow-premium { box-shadow: 0 10px 40px rgba(0,0,0,0.05) !important; }
.border-start-lg { border-left: 0; }
@media (min-width: 992px) { .border-start-lg { border-left: 1px solid #eee; } }

.transition-all { transition: all 0.2s ease; }
.custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }

.stat-bubble {
    font-size: 0.75rem;
    padding: 2px 8px;
    background: #f8f9fa;
    border-radius: 4px;
    font-weight: 600;
}
.shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }

.bg-soft-gray { background-color: #f8f9fa; }
.bg-soft-red { background-color: rgba(var(--bs-danger-rgb), 0.02); }

.legend-item {
    transition: transform 0.2s;
    cursor: default;
    &:hover { transform: translateY(-2px); }
}

.min-width-200 { min-width: 250px; }
</style>