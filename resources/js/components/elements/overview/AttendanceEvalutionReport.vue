<template>
    <div class="evaluation-report-container animate__animated animate__fadeIn">
        
        <!-- Control Hub -->
        <div class="card border-0 shadow-premium mb-4 overflow-hidden">
            <div class="card-body p-4 bg-white">
                <div class="row align-items-center g-4">
                    <div class="col-xl-6">
                        <div class="d-flex align-items-center justify-content-start gap-3">
                            <button @click="shift_dates('prev')" class="btn btn-outline-primary btn-icon rounded-circle shadow-sm">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            
                            <div class="row g-2 flex-grow-1">
                                <div class="col">
                                    <forms-date-field @change="getData()" name="from" label="Start Date" v-model="item.from" error="" classes="col-12"></forms-date-field>
                                </div>
                                <div class="col">
                                    <forms-date-field @change="getData()" name="to" label="End Date" v-model="item.to" error="" classes="col-12"></forms-date-field>
                                </div>
                            </div>

                            <button @click="shift_dates('next')" class="btn btn-outline-primary btn-icon rounded-circle shadow-sm">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="d-flex align-items-center justify-content-xl-end gap-3">
                            <div v-if="selectedIds.length > 0" class="me-auto text-primary fw-bold small">
                                <i class="bi bi-check-all"></i> {{ selectedIds.length }} selected
                            </div>
                            
                            <button @click="evalutePayCycle()" :disabled="loading || selectedIds.length === 0" class="btn btn-primary px-4 py-2 fw-bold shadow-sm d-flex align-items-center">
                                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="bi bi-lightning-charge-fill me-2"></i>
                                {{ loading ? (progressText || 'Processing...') : 'Evaluate Selected' }}
                            </button>

                            <button @click="exportPdf()" :disabled="selectedIds.length === 0" class="btn btn-outline-dark px-4 py-2 fw-bold shadow-sm d-flex align-items-center">
                                <i class="bi bi-file-earmark-pdf-fill me-2 text-danger"></i>
                                Matrix Report
                            </button>

                            <button @click="individualReport()" :disabled="selectedIds.length === 0" class="btn btn-outline-dark px-4 py-2 fw-bold shadow-sm d-flex align-items-center">
                                <i class="bi bi-person-lines-fill me-2 text-primary"></i>
                                Individual Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Matrix -->
        <div class="card border-0 shadow-premium overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold m-0"><i class="bi bi-grid-3x3-gap text-primary me-2"></i> Audit Matrix</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 rounded-pill">{{ employees.length }} Employees</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive custom-scrollbar" style="max-height: 700px;">
                    <table class="table table-hover align-middle mb-0 matrix-table">
                        <thead class="bg-light sticky-header">
                            <tr>
                                <th class="sticky-column bg-light ps-4 border-end border-bottom-0" style="z-index: 1022; left: 0;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check custom-checkbox">
                                            <input class="form-check-input" type="checkbox" v-model="selectAll" @change="toggleSelectAll">
                                        </div>
                                        <span>Employee Detail</span>
                                    </div>
                                </th>
                                <th v-for="(dt, ind) in dates" :key="dt" class="text-center border-bottom-0 date-header min-w-150">
                                    <div class="fw-bold text-primary">{{ ddmmyyyys[ind].split('-')[0] }}</div>
                                    <div class="small text-muted">{{ ddmmyyyys[ind].split('-')[1] }}-{{ ddmmyyyys[ind].split('-')[2] }}</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="employee in employees" :key="employee.id" :class="{'bg-primary bg-opacity-5': selectedIds.includes(employee.id)}">
                                <td class="sticky-column bg-white ps-4 border-end" style="left: 0; cursor: pointer;" @click="toggleEmployeeSelection(employee.id)">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check custom-checkbox">
                                            <input class="form-check-input" type="checkbox" :value="employee.id" v-model="selectedIds" @click.stop>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ employee.first_name }} {{ employee.last_name }}</div>
                                            <div class="badge bg-light text-muted font-monospace small">#{{ employee.employee_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td v-for="dt in dates" :key="dt" class="text-center border-start-light p-1">
                                    <div :set="obj = getStatus(dt, employee.employee_shifts)">
                                        <div v-if="obj" class="status-cell rounded-3 p-2 h-100 transition-all cursor-pointer shadow-hover" 
                                             :class="getCellClass(obj.status)"
                                             @click="openEditModal(obj, employee.first_name + ' ' + employee.last_name)">
                                            
                                            <div class="fw-bold fs-7 mb-1">{{ obj.status || 'P' }}</div>
                                            
                                            <div class="punch-mini d-flex flex-wrap justify-content-center gap-1 mb-1">
                                                <span v-for="att in obj.employee_attendance" :key="att.id" class="badge-mini shadow-xs transition-all">
                                                    {{ att.tm }}
                                                </span>
                                            </div>

                                            <div class="matrix-stats opacity-75">
                                                <div v-if="obj.late > 0" class="text-danger">↑{{ obj.late }}m</div>
                                                <div v-if="obj.early > 0" class="text-primary-soft">↓{{ obj.early }}m</div>
                                                <div v-if="obj.lop > 0" class="fw-bold text-dark">L:{{ obj.lop }}</div>
                                            </div>
                                        </div>
                                        <div v-else class="text-muted p-2 h-100 bg-light bg-opacity-50 rounded-3 small d-flex align-items-center justify-content-center cursor-pointer shadow-hover-inset"
                                             @click="openEditModal(null, employee.first_name + ' ' + employee.last_name, dt, employee.id)">
                                            <i class="bi bi-plus text-primary opacity-50 fs-4"></i>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Modal (Same as Attendance Page for Consistency) -->
        <div class="modal fade" id="editEvaluationPunchModal" tabindex="-1" aria-hidden="true" ref="editModal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header bg-primary bg-opacity-5 border-0 py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3 text-primary">
                                <i class="bi bi-clock-history fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold m-0 text-dark">Adjust Time Entry</h5>
                                <p class="text-muted small mb-0">{{ editForm.employee_name }}</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 bg-info bg-opacity-10 small mb-4">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Changes will reflect after re-evaluating the pay cycle.
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
                    <div class="modal-footer bg-light border-0 py-3 px-4 justify-content-between">
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
    props: ['from', 'to'],
    data(){
        return {
            loading: false,
            progressText: '',
            saving: false,
            deleting: false,
            item: { from: null, to: null },
            employees: [],
            dates: [],
            dds: [],
            ddmmyyyys: [],
            selectedIds: [],
            selectAll: false,
            editModal: null,
            editForm: {
                employee_shift_id: null,
                employee_id: null,
                on_date: null,
                employee_name: '',
                in_time: null,
                out_time: null
            }
        };
    },
    methods: {
        openEditModal(shift, name, date = null, employee_id = null) {
            this.editForm.employee_name = name;
            
            if (shift) {
                this.editForm.employee_shift_id = shift.id;
                this.editForm.on_date = shift.dt;
                this.editForm.employee_id = shift.employee_id;
                
                // Fallback to shift times if attendance is missing
                this.editForm.in_time = (shift.employee_attendance && shift.employee_attendance.length > 0) 
                    ? shift.employee_attendance[0].tm 
                    : (shift.working_shift ? shift.working_shift.in : null);
                    
                this.editForm.out_time = (shift.employee_attendance && shift.employee_attendance.length > 1) 
                    ? shift.employee_attendance[shift.employee_attendance.length - 1].tm 
                    : (shift.working_shift ? shift.working_shift.out : null);
            } else {
                this.editForm.employee_shift_id = null;
                this.editForm.on_date = date;
                this.editForm.employee_id = employee_id;
                this.editForm.in_time = null;
                this.editForm.out_time = null;
            }

            if (!this.editModal) {
                this.editModal = new bootstrap.Modal(document.getElementById('editEvaluationPunchModal'));
            }
            this.editModal.show();
        },
        saveTimes() {
            this.saving = true;
            axios.post('/attendance/update_times', this.editForm)
            .then(res => {
                this.saving = false;
                this.editModal.hide();
                this.getData();
            })
            .catch(err => {
                this.saving = false;
                alert('Error updating times.');
            });
        },
        deleteTimes() {
            if (confirm('Are you sure you want to delete records for this date?')) {
                this.deleting = true;
                axios.post('/attendance/delete_times', { 
                    employee_shift_id: this.editForm.employee_shift_id 
                })
                .then(res => {
                    this.deleting = false;
                    this.editModal.hide();
                    this.getData();
                })
                .catch(err => {
                    this.deleting = false;
                    alert('Error deleting records.');
                });
            }
        },
        evalutePayCycle(){
            this.loading = true;
            this.progressText = 'Starting...';
            let data = {
                from: this.item.from,
                to: this.item.to,
                eids: this.employees.map(emp => emp.id),
            };
            axios.post('/attendance_evalution_report/run_lop', data).then((res) => {
                const jobId = res.data.jobId;
                this.pollProgress(jobId);
            }).catch(err => {
                this.loading = false;
                this.progressText = '';
                alert('Error starting evaluation.');
            });
        },
        pollProgress(jobId, attempts = 0){
            if (attempts > 45) {
                this.loading = false;
                this.progressText = '';
                this.getData();
                return;
            }
            axios.get(`/attendance_evalution_report/progress/${jobId}`).then(res => {
                const status = res.data.status;
                if (status === 'completed') {
                    this.loading = false;
                    this.progressText = '';
                    this.getData();
                } else if (status === 'failed') {
                    this.loading = false;
                    this.progressText = '';
                    alert('Evaluation failed: ' + (res.data.error || 'Unknown error'));
                    this.getData();
                } else {
                    const processed = res.data.processed || 0;
                    const total = res.data.total || (this.employees ? this.employees.length : 0);
                    this.progressText = `Evaluating: ${processed} / ${total}`;
                    setTimeout(() => {
                        this.pollProgress(jobId, attempts + 1);
                    }, 1000);
                }
            }).catch(err => {
                this.loading = false;
                this.progressText = '';
                this.getData();
            });
        },
        shift_dates(what){
            axios.post('/overview/run_payroll/shift_dates', { from: this.item.from, what: what }).then(res=> {
                this.item.from = res.data.from;
                this.item.to = res.data.to;
                this.getData();
            });
        },
        getStatus(dt, data){
            let a = data.filter(d => {
                let dd = new Date(d.dt).getTime();
                let ddd = new Date(dt).getTime();
                return dd === ddd;
            });
            return a.length > 0 ? a[0] : null;
        },
        getData(){
            axios.get('/attendance_evalution_report/get_data', { params: this.item })
            .then(res => {
                this.employees = res.data.employees;
                this.dates = res.data.dates;
                this.dds = res.data.dds;
                this.ddmmyyyys = res.data.ddmmyyyys;
                this.selectedIds = [];
                this.selectAll = false;
            });
        },
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedIds = this.employees.map(emp => emp.id);
            } else {
                this.selectedIds = [];
            }
        },
        toggleEmployeeSelection(employeeId) {
            const index = this.selectedIds.indexOf(employeeId);
            if (index > -1) {
                this.selectedIds.splice(index, 1);
            } else {
                this.selectedIds.push(employeeId);
            }
        },
        exportPdf() {
            if (this.selectedIds.length === 0) {
                alert('Please select at least one employee.');
                return;
            }
            const url = `/pdf/attendance/${this.item.from}/${this.item.to}?eids=${this.selectedIds.join(',')}`;
            window.open(url, '_blank');
        },
        individualReport() {
            if (this.selectedIds.length === 0) {
                alert('Please select at least one employee.');
                return;
            }
            const url = `/pdf/individual_attendance/${this.item.from}/${this.item.to}?eids=${this.selectedIds.join(',')}`;
            window.open(url, '_blank');
        },
        getCellClass(status) {
            switch(status) {
                case 'Present': return 'bg-success bg-opacity-10 border border-success border-opacity-25 border-start-5';
                case 'Absent': return 'bg-danger bg-opacity-10 border border-danger border-opacity-25 border-start-5';
                case 'Leave': return 'bg-info bg-opacity-10 border border-info border-opacity-25 border-start-5';
                case 'Weekoff':
                case 'Holiday': return 'bg-warning bg-opacity-10 border border-warning border-opacity-25 border-start-5';
                default: return 'bg-light border border-start-5';
            }
        }
    },
    created(){
        this.item.from = this.from;
        this.item.to = this.to;
        this.getData();
    },
}
</script>

<style scoped lang="scss">
.min-w-150 { min-width: 150px; }
.btn-icon { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; }

.sticky-header {
    position: sticky;
    top: 0;
    z-index: 1021;
}

.sticky-column {
    position: sticky;
    left: 0;
    z-index: 1020;
    min-width: 250px;
}

.matrix-table {
    border-collapse: separate;
    border-spacing: 0;
}

.cursor-pointer { cursor: pointer; }
.shadow-hover:hover { 
    background-color: rgba(var(--bs-primary-rgb), 0.05) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}
.shadow-hover-inset:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.status-cell {
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.border-start-5 { border-left-width: 5px !important; }

.badge-mini {
    font-size: 0.65rem;
    padding: 1px 4px;
    background: white;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 3px;
    color: #444;
}

.fs-7 { font-size: 0.825rem; }

.matrix-stats {
    font-size: 0.7rem;
    line-height: 1.1;
}

.border-start-light { border-left: 1px solid #f1f1f1; }

.custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f8f9fa; }
</style>