<template>
    <div class="employee-manager-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Staff Directory</h4>
                    <p class="text-muted small mb-0">Unified lifecycle management for all corporate employees and workforce data.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-person-x' : 'bi bi-person-plus-fill']" class="me-2"></i>
                        {{ isForm ? 'Close Enrollment' : 'Enroll Employee' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Multi-Section Enrollment Form -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-5 overflow-hidden border-top-primary">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ employee.id ? 'Edit Employee Credentials' : 'New Staff Registration' }}</h5>
                    <span v-if="employee.id" class="badge bg-light text-muted border fw-mono">UID: #{{ employee.id }}</span>
                </div>
                
                <div class="card-body p-4 bg-light-subtle">
                    <!-- General Error Alert -->
                    <div v-if="Object.keys(errors).length > 0" class="alert alert-danger rounded-4 mb-4 border-0 shadow-sm d-flex align-items-center gap-3">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                        <div>
                            <div class="fw-bold text-danger">Validation Failed</div>
                            <div class="small text-danger opacity-75">Please correct the errors in the highlighted fields below.</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Left Column: Primary & Personal -->
                        <div class="col-12 col-xl-8">
                            <!-- Basic Profile -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-person me-2"></i>Basic Identity</h6>
                                <div class="row g-3">
                                    <forms-text-field name="first_name" label="First Name" v-model="employee.first_name" :error="errors.first_name ? errors.first_name[0] : ''" placeholder="Legal first name" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="middle_name" label="Middle Name" v-model="employee.middle_name" :error="errors.middle_name ? errors.middle_name[0] : ''" placeholder="Optional" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="last_name" label="Last Name" v-model="employee.last_name" :error="errors.last_name ? errors.last_name[0] : ''" placeholder="Legal last name" classes="col-md-4"></forms-text-field>
                                    
                                    <forms-text-field name="employee_code" label="Corporate ID / Staff Code" v-model="employee.employee_code" :error="errors.employee_code ? errors.employee_code[0] : ''" placeholder="EMP-001" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="tagid" label="RFID / Biometric Tag ID" v-model="employee.tagid" :error="errors.tagid ? errors.tagid[0] : ''" placeholder="8-digit hex code" classes="col-md-6"></forms-text-field>
                                </div>
                            </div>

                            <!-- Demographics -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-info-circle me-2"></i>Demographics</h6>
                                <div class="row g-3">
                                    <forms-select-field name="gender" label="Gender Identity" v-model="employee.gender" :error="errors.gender ? errors.gender[0] : ''" :options="[{key: 'Male', val: 'Male'}, {key: 'Female', val: 'Female'}, {key: 'Other', val: 'Other'}]" classes="col-md-4"></forms-select-field>
                                    <forms-select-field name="marital_status" label="Marital Status" v-model="employee.marital_status" :error="errors.marital_status ? errors.marital_status[0] : ''" :options="[{key: 'Married', val: 'Married'}, {key: 'Widowed', val: 'Widowed'}, {key: 'Separated', val: 'Separated'}, {key: 'Divorced', val: 'Divorced'}, {key: 'Single', val: 'Single'}]" classes="col-md-4"></forms-select-field>
                                    <forms-select-field name="blood_group" label="Blood Group" v-model="employee.blood_group" :error="errors.blood_group ? errors.blood_group[0] : ''" :options="[{key: 'O +ve', val: 'O +ve'}, {key: 'O -ve', val: 'O -ve'}, {key: 'A +ve', val: 'A +ve'}, {key: 'B +ve', val: 'B +ve'}, {key: 'AB +ve', val: 'AB +ve'}]" classes="col-md-4"></forms-select-field>
                                    <forms-text-field name="nationality" label="Nationality" v-model="employee.nationality" :error="errors.nationality ? errors.nationality[0] : ''" placeholder="e.g. Indian" classes="col-md-6"></forms-text-field>
                                    <forms-select-field name="religion" label="Religion" v-model="employee.religion" :error="errors.religion ? errors.religion[0] : ''" :options="[{key: 'Hindu', val: 'Hindu'}, {key: 'Muslim', val: 'Muslim'}, {key: 'Christian', val: 'Christian'}, {key: 'Sikh', val: 'Sikh'}, {key: 'Buddhist', val: 'Buddhist'}, {key: 'Jain', val: 'Jain'}, {key: 'Atheist', val: 'Atheist'}, {key: 'Other', val: 'Other'}]" classes="col-md-6"></forms-select-field>
                                    <forms-text-field name="cast" label="Caste" v-model="employee.cast" :error="errors.cast ? errors.cast[0] : ''" placeholder="Caste" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="subcast" label="Sub-caste" v-model="employee.subcast" :error="errors.subcast ? errors.subcast[0] : ''" placeholder="Sub-caste" classes="col-md-6"></forms-text-field>
                                </div>
                            </div>

                            <!-- Statutory & Documents -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-file-earmark-lock me-2"></i>Statutory IDs & Banking</h6>
                                <div class="row g-3">
                                    <forms-text-field name="aadhar" label="National Identification (Aadhar)" v-model="employee.aadhar" :error="errors.aadhar ? errors.aadhar[0] : ''" placeholder="12-digit number" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="pan" label="Tax ID (PAN)" v-model="employee.pan" :error="errors.pan ? errors.pan[0] : ''" placeholder="Alpha-numeric PAN" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="pf" label="PF Registration Number" v-model="employee.pf" :error="errors.pf ? errors.pf[0] : ''" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="uan" label="Universal Account (UAN)" v-model="employee.uan" :error="errors.uan ? errors.uan[0] : ''" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="esic" label="ESIC Number" v-model="employee.esic" :error="errors.esic ? errors.esic[0] : ''" classes="col-md-4"></forms-text-field>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Timeline & Attributes -->
                        <div class="col-12 col-xl-4">
                            <!-- Contact & Communication -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-envelope me-2"></i>Communication Channel</h6>
                                <div class="row g-3">
                                    <forms-text-field name="email" label="Professional Email" v-model="employee.email" :error="errors.email ? errors.email[0] : ''" placeholder="work@company.com" classes="col-12"></forms-text-field>
                                    <forms-text-field name="phone" label="Primary Phone" v-model="employee.phone" :error="errors.phone ? errors.phone[0] : ''" placeholder="+1 (000) 000-0000" classes="col-12"></forms-text-field>
                                </div>
                            </div>

                            <!-- Key Timelines -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-calendar-event me-2"></i>Critical Timelines</h6>
                                <div class="row g-3">
                                    <forms-date-field name="doj" label="Joining Date" v-model="employee.doj" :error="errors.doj ? errors.doj[0] : ''" classes="col-12"></forms-date-field>
                                    <forms-date-field name="dob" label="Date of Birth" v-model="employee.dob" :error="errors.dob ? errors.dob[0] : ''" classes="col-12"></forms-date-field>
                                    <forms-date-field name="doe" label="Resignation / Exit Date" v-model="employee.doe" :error="errors.doe ? errors.doe[0] : ''" classes="col-12"></forms-date-field>
                                </div>
                            </div>



                            <!-- Professional & Cultural -->
                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-translate me-2"></i>Cultural & Professional</h6>
                                <div class="row g-3">
                                    <forms-text-field name="mothertongue" label="Native Language" v-model="employee.mothertongue" :error="errors.mothertongue ? errors.mothertongue[0] : ''" classes="col-12"></forms-text-field>
                                    <forms-select-field name="qualification" label="Top Qualification" v-model="employee.qualification" :error="errors.qualification ? errors.qualification[0] : ''" :options="[{key: 'Undergraduate', val: 'Undergraduate'}, {key: 'Graduate', val: 'Graduate'}, {key: 'Masters', val: 'Masters'}, {key: 'Doctorate', val: 'Doctorate'}]" classes="col-12"></forms-select-field>
                                    <forms-text-field name="degree" label="Highest Educational Degree" v-model="employee.degree" :error="errors.degree ? errors.degree[0] : ''" placeholder="e.g. B.Tech, MBA" classes="col-12"></forms-text-field>
                                </div>
                            </div>
                        </div>

                        <!-- Form Action Bar -->
                        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3 mt-4 border-top pt-4">
                            <div v-if="employee.id" class="d-grid d-md-block">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="deleteItem()">
                                    <i class="bi bi-person-dash me-2"></i> Terminate Account
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Removal
                                </button>
                            </div>
                            <div v-else class="d-none d-md-block"></div>

                            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                                <button class="btn btn-light btn-lg px-4 rounded-pill shadow-none" @click="toggleForm">Discard Changes</button>
                                <forms-submit-button name="" v-model="loading" :label="employee.id ? 'Save Personnel Data' : 'Submit Enrollment'" @click="save()" classes="btn-lg px-5 shadow-sm rounded-pill"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Search & Staff Library -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
                            <i class="bi bi-person-lines-fill text-primary me-2 f-5"></i>
                            Staff Inventory
                        </h5>
                    </div>
                    <div class="col-md-5">
                        <div class="search-container position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" class="form-control rounded-pill ps-5 py-3 border-0 bg-light shadow-none" v-model="params.value" placeholder="Search by name, email, or employee code..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3 cursor-pointer" @click="orderBy('id')">Staff ID <i class="bi bi-sort-down ms-1"></i></th>
                            <th class="cursor-pointer" @click="orderBy('first_name')">Personnel Profile</th>
                            <th>Job Function</th>
                            <th class="cursor-pointer" @click="orderBy('employee_code')">Code</th>
                            <th>Connectivity</th>
                            <th class="pe-4 text-end">Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="emp in employees" :key="emp.id" class="cursor-pointer transition-all hover-glow border-bottom border-light" @click="edit(emp)">
                            <td class="ps-4">
                                <span class="badge bg-white text-muted border fw-mono">#{{ emp.id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="emp-avatar me-3 bg-gradient-staff">
                                        {{ (emp.first_name && emp.first_name.length > 0) ? emp.first_name.charAt(0).toUpperCase() : '?' }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ emp.first_name }} {{ emp.last_name }}</div>
                                        <div class="text-primary small fw-semibold">{{ (emp.employee_designation && emp.employee_designation.designation) ? emp.employee_designation.designation.designation : 'Draft Profile' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div v-if="emp.employee_department" class="small fw-medium text-muted">
                                    <i class="bi bi-building me-1"></i> {{ emp.employee_department.department.department }}
                                </div>
                                <div v-else class="text-muted opacity-50 tiny">Dept unassigned</div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-soft-info text-info border border-info border-opacity-25 px-3 py-2 fw-mono">
                                    {{ emp.employee_code || 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="connectivity-grid">
                                    <div class="small text-muted mb-1"><i class="bi bi-telephone-outbound me-2 opacity-75"></i> {{ emp.phone || '—' }}</div>
                                    <div class="small text-muted"><i class="bi bi-envelope-at me-2 opacity-75"></i> {{ emp.email || '—' }}</div>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                    <a :href="'/employee/profile/'+emp.id" class="btn btn-white btn-sm px-3 border-end" title="Full Profile" @click.stop><i class="bi bi-person-vcard text-primary"></i></a>
                                    <button class="btn btn-white btn-sm px-3" title="Assign Details" @click.stop="edit(emp)"><i class="bi bi-pencil-square text-muted"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="employees.length === 0">
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state py-5">
                                    <i class="bi bi-people-fill display-1 text-light-emphasis d-block mb-3 opacity-25"></i>
                                    <h6 class="text-muted fw-bold">No Personnel Records Matching Search</h6>
                                    <p class="text-muted small">Try adjusting your filters or search keywords.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-dark rounded-pill px-5 transition-all hover-shadow-sm" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-chevron-bar-down me-2"></i>
                    Load Additional Staff
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
export default {

    data(){
        return {
            isForm: false,
            loading: false,
            isDelete: false,
            employee: {
                id: null,
                first_name: null,
                middle_name: null,
                last_name: null,
                employee_code: null,
                tagid: null,
                email: null,
                phone: null,
                doj: null,
                doe: null,
                dob: null,
                gender: null,
                blood_group: null,
                religion: null,
                cast: null,
                subcast: null,
                mothertongue: null,
                nationality: 'Indian',
                marital_status: null,
                qualification: null,
                degree: null,
                aadhar: null,
                pan: null,
                pf: null,
                uan: null,
                esic: null,
            },
            errors: {},
            employees: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: null,
                value: null,
                by: 'id',
                order: 'desc',
                rows: 10,
            },
            searchTimer: null
        };
    },

    watch: {
        "params.value": function (val) {
            if (this.searchTimer) clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => {
                this.search();
            }, 500);
        },
    },

    methods: {
        toggleForm() {
            if(this.isForm) {
                this.reset();
                this.isForm = false;
            } else {
                this.isForm = true;
            }
        },

        fetch(){
            let url = '/employee/employee_manager/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.current_page == 1){
                    this.employees = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.employees.push(item);
                    });
                }
                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.employees = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.employee.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            Object.keys(this.employee).forEach(key => this.employee[key] = null);
            this.employee.nationality = 'Indian';
            this.errors = {};
            this.isDelete = false;
        },

        add(){
            this.loading = true;
            this.errors = {};
            axios.post('/employee/employee_manager/add', this.employee).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).catch(err => {
                if (err.response && err.response.status === 422) {
                    this.errors = err.response.data.errors;
                }
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            this.errors = {};
            axios.post('/employee/employee_manager/update', this.employee).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).catch(err => {
                if (err.response && err.response.status === 422) {
                    this.errors = err.response.data.errors;
                }
            }).finally(() => {
                this.loading = false;
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            this.errors = {};
            axios.post('/employee/employee_manager/delete', this.employee).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        edit(item){
            this.reset();
            this.isForm = true;
            Object.keys(this.employee).forEach(key => {
                this.employee[key] = item[key];
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

    },

    created(){
        this.fetch();
    },

}
</script>

<style scoped>
.employee-manager-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.emp-avatar {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-weight: 700;
    color: white;
    font-size: 1.1rem;
}

.bg-gradient-staff {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
}

.border-top-primary {
    border-top: 4px solid #6366f1 !important;
}

.bg-soft-info { background-color: #e0f2fe; }
.text-info { color: #0ea5e9; }

.hover-glow:hover {
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
    background-color: #f1f5f9;
}

.transition-all { transition: all 0.25s ease; }
.tiny { font-size: 0.7rem; }
.fw-mono { font-family: ui-monospace, SFMono-Regular, monospace; }

/* Transitions */
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.4s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }

@keyframes shakeX {
    from, to { transform: translate3d(0, 0, 0); }
    10%, 30%, 50%, 70%, 90% { transform: translate3d(-10px, 0, 0); }
    20%, 40%, 60%, 80% { transform: translate3d(10px, 0, 0); }
}
.animate__shakeX { animation-name: shakeX; animation-duration: 0.5s; }

.bg-light-subtle { background-color: #f8fafc !important; }
</style>