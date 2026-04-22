<template>
    <div class="time-update-dashboard">
        <!-- Top bar search -->
        <div class="search-bar-modern p-4 mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-8">
                    <label class="form-label fw-bold text-muted small text-uppercase">Employee Search</label>
                    <div class="input-group input-group-lg shadow-sm position-relative">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-person-badge text-primary"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" 
                               @keyup.enter="getEmployee()" 
                               @input="onSearchInput()"
                               placeholder="SEARCH BY NAME, CODE, EMAIL OR MOBILE..." 
                               v-model="employee_code"
                               autocomplete="off">
                        
                        <!-- Suggestions Dropdown -->
                        <div v-if="showSuggestions && suggestions.length > 0" class="suggestions-dropdown shadow-lg rounded-3">
                            <ul class="list-unstyled mb-0">
                                <li v-for="emp in suggestions" :key="emp.id" 
                                    @click="selectEmployee(emp)"
                                    class="suggestion-item p-3 d-flex align-items-center cursor-pointer border-bottom">
                                    <div class="avatar-suggestion me-3 bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                        {{ (emp.first_name ? emp.first_name[0] : '') }}{{ (emp.last_name ? emp.last_name[0] : '') }}
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0 text-dark">{{ emp.first_name }} {{ emp.last_name }}</div>
                                        <div class="small text-muted">ID: {{ emp.employee_code }} • {{ emp.phone || 'No Phone' }}</div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <button class="btn btn-primary btn-lg w-100 shadow-sm fw-bold" @click="getEmployee()">
                        <i class="bi bi-search me-2"></i> LOAD PROFILE
                    </button>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Sidebar: Employee Profile -->
            <div class="col-12 col-xl-4">
                <div v-if="employee" class="profile-card-modern shadow-sm sticky-top" style="top: 20px;">
                    <div class="profile-header text-center p-4">
                        <div class="avatar-placeholder mb-3 mx-auto">
                            {{ employee.first_name[0] }}{{ employee.last_name[0] }}
                        </div>
                        <h4 class="fw-bold mb-1">{{ employee.first_name }} {{ employee.last_name }}</h4>
                        <span class="badge bg-soft-primary text-primary px-3 rounded-pill">{{ employee.employee_code }}</span>
                    </div>
                    
                    <div class="p-4 pt-0">
                        <div class="alert alert-info border-0 rounded-4 small mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Use this section to correct missing or incorrect biometric punch timings for this employee.
                        </div>
                    </div>
                </div>

                <div v-else class="empty-state-card text-center p-5 shadow-sm bg-white rounded-4 border-dashed">
                    <i class="bi bi-clock-history text-muted display-4"></i>
                    <p class="mt-3 text-muted">Enter employee code to start time update process.</p>
                </div>
            </div>

            <!-- Right Content: Form and History -->
            <div class="col-12 col-xl-8">
                <!-- Data Entry Form -->
                <div v-if="(item && employee) || item.id != null" class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            {{ item.id ? 'Modify Time Entry' : 'New Time Correction' }}
                        </h5>
                        <button v-if="item.id" class="btn btn-sm btn-outline-secondary rounded-pill px-3" @click="reset()">
                            Cancel
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <forms-date-field name="on_date" label="Work Date" v-model="item.on_date" :error="errors.on_date" classes="col-12 col-lg-3"></forms-date-field>
                            <forms-time-field name="in_time" label="Check-In Time" v-model="item.in_time" :error="errors.in_time" classes="col-12 col-lg-3"></forms-time-field>
                            <forms-time-field name="out_time" label="Check-Out Time" v-model="item.out_time" :error="errors.out_time" classes="col-12 col-lg-3"></forms-time-field>
                            <forms-select-field name="status" label="Status" :options="[{key: 'Pending', val: 'Pending'}, {key: 'Approved', val: 'Approved'}, {key: 'Rejected', val: 'Rejected'}]" v-model="item.status" :error="errors.status" classes="col-12 col-lg-3"></forms-select-field>
                            
                            <forms-text-field name="reason" label="Correction Reason" placeholder="e.g. Biometric failed, Forgot to punch..." v-model="item.reason" :error="errors.reason" classes="col-12"></forms-text-field>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <div v-if="item.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4" @click="deleteItem()">
                                    <i class="bi bi-trash me-2"></i> Delete
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <forms-submit-button name="" v-model="loading" label="Save Correction" @click="save()" classes="btn-lg px-5"></forms-submit-button>
                        </div>
                    </div>
                </div>

                <!-- History Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="row align-items-center">
                            <div class="col text-start">
                                <h5 class="fw-bold mb-0">Recent Time Updates</h5>
                            </div>
                            <div class="col-auto">
                                <div class="input-group input-group-sm">
                                    <input type="text" v-model="params.value" class="form-control" placeholder="Search logs..." @keyup.enter="search()">
                                    <button class="btn btn-primary" @click="search()"><i class="bi bi-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-uppercase small fw-bold text-muted">
                                    <th class="ps-4 border-0">Employee</th>
                                    <th class="border-0 text-center">Date</th>
                                    <th class="border-0 text-center">Punch In</th>
                                    <th class="border-0 text-center">Punch Out</th>
                                    <th class="border-0 text-center">Status</th>
                                    <th class="pe-4 border-0 text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="row in items" :key="row.id" class="cursor-pointer" @click="edit(row)">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3 bg-soft-info text-info rounded-circle d-flex align-items-center justify-content-center">
                                                {{ row.employee.first_name[0] }}
                                            </div>
                                            <div>
                                                <div class="fw-bold small">{{ row.employee.first_name }} {{ row.employee.last_name }}</div>
                                                <div class="text-muted extra-small">ID: {{ row.employee.employee_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center fw-medium">{{ formatDate(row.on_date) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-soft-success text-success px-2">{{ row.in_time }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-soft-danger text-danger px-2">{{ row.out_time }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-3 py-2" :class="getStatusClass(row.status)">
                                            {{ row.status || 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-light btn-icon rounded-circle" @click.stop="edit(row)">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="items.length === 0">
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-slash-circle display-4 d-block mb-3"></i>
                                        No time update records found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white border-0 text-center py-3">
                        <button class="btn btn-outline-dark rounded-pill px-4 btn-sm" :disabled="next_page_url == null" @click="fetch()">
                            <i class="bi bi-chevron-down me-2"></i> Load More Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {

    data(){
        return {
            loading: false,
            isDelete: false,
            item: {
                id: null,
                employee_id: null,
                on_date: null,
                in_time: null,
                out_time: null,
                reason: null,
                status: 'Pending',
            },
            items: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: null,
                value: null,
                by: 'id',
                order: 'desc',
                rows: 1,
            },
            employee_code: null,
            employee: null,
            errors: {},
            searchTimer: null,
            suggestions: [],
            showSuggestions: false,
            suggestionTimer: null,
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

        reset(){
            this.item.id = null;
            this.item.employee_id = null;
            this.item.on_date = null;
            this.item.in_time = null;
            this.item.out_time = null;
            this.item.reason = null;
            this.item.status = 'Pending';
            this.isDelete = false;
            this.errors = {};
        },

        edit(row){
            this.errors = {};
            this.item.id = row.id;
            this.item.employee_id = row.employee_id;
            this.item.on_date = row.on_date;
            this.item.in_time = row.in_time;
            this.item.out_time = row.out_time;
            this.item.reason = row.reason;
            this.item.status = row.status || 'Pending';

            if(row.employee && row.employee.employee_code){
                this.employee_code = row.employee.employee_code;
                this.getEmployee();
            }
        },

        fetch(){
            let url = '/approvals/time_update/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }
            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.current_page == 1){
                    this.items = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.items.push(item);
                    });
                }
                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.items = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            this.errors = {};
            if(this.item.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        add(){
            this.loading = true;
            axios.post('/approvals/time_update/add', this.item).then(res => {
                this.reset();
                this.search();
            }).catch(err => {
                if(err.response && err.response.status == 422){
                    this.errors = err.response.data.errors;
                }
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/approvals/time_update/update', this.item).then(res => {
                this.reset();
                this.search();
            }).catch(err => {
                if(err.response && err.response.status == 422){
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
            axios.post('/approvals/time_update/delete', this.item).then(res => {
                this.reset();
                this.search();
            }).finally(() => {
                this.loading = false;
            });
        },

        getEmployee(){
            this.showSuggestions = false;
            if(this.employee_code){
                axios.get('/approvals/time_update/employee/' + this.employee_code).then(res => {
                    this.employee = res.data.employee;
                    if(this.employee){
                        this.item.employee_id = this.employee.id;
                    }
                });
            }
        },

        onSearchInput(){
            if (this.suggestionTimer) clearTimeout(this.suggestionTimer);
            if (!this.employee_code || this.employee_code.length < 2) {
                this.suggestions = [];
                this.showSuggestions = false;
                return;
            }
            this.suggestionTimer = setTimeout(() => {
                this.fetchSuggestions();
            }, 300);
        },

        fetchSuggestions(){
            axios.get('/employee/api/search', { params: { q: this.employee_code } }).then(res => {
                this.suggestions = res.data;
                this.showSuggestions = true;
            });
        },

        selectEmployee(emp){
            this.employee_code = emp.employee_code;
            this.getEmployee();
            this.suggestions = [];
            this.showSuggestions = false;
        },

        formatDate(date) {
            if (!date) return '';
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(date).toLocaleDateString('en-IN', options);
        },

        getStatusClass(status) {
            if (!status) return 'bg-soft-warning text-warning';
            switch (status.toLowerCase()) {
                case 'approved': return 'bg-soft-success text-success';
                case 'rejected': return 'bg-soft-danger text-danger';
                default: return 'bg-soft-warning text-warning';
            }
        }

    },

    created(){
        this.fetch();
    },

}
</script>

<style scoped>
.time-update-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding-bottom: 3rem;
}

.search-bar-modern {
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    z-index: 9999;
    margin-top: 0.5rem;
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
}

.suggestion-item {
    transition: all 0.2s;
}

.suggestion-item:hover {
    background-color: #f8fafc;
}

.suggestion-item:last-child {
    border-bottom: none !important;
}

.avatar-suggestion {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
}

.profile-card-modern {
    background: white;
    border-radius: 1.5rem;
    overflow: hidden;
}

.avatar-placeholder {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
    color: white;
    font-size: 2rem;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 2rem;
    box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
}

.bg-soft-primary { background-color: #eef2ff; }
.text-primary { color: #6366f1 !important; }
.bg-soft-success { background-color: #ecfdf5; }
.text-success { color: #059669 !important; }
.bg-soft-danger { background-color: #fef2f2; }
.text-danger { color: #dc2626 !important; }
.bg-soft-info { background-color: #f0f9ff; }
.text-info { color: #0ea5e9 !important; }

.avatar-sm {
    width: 36px;
    height: 36px;
    font-size: 0.8rem;
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.border-dashed {
    border: 2px dashed #e2e8f0;
}

.extra-small {
    font-size: 0.75rem;
}

.cursor-pointer {
    cursor: pointer;
}

.input-group-text {
    border-right: none;
}

.form-control:focus, .form-select:focus {
    box-shadow: none;
    border-color: #6366f1;
}

/* Animations */
@keyframes shakeX {
    from, to { transform: translate3d(0, 0, 0); }
    10%, 30%, 50%, 70%, 90% { transform: translate3d(-10px, 0, 0); }
    20%, 40%, 60%, 80% { transform: translate3d(10px, 0, 0); }
}
.animate__shakeX { animation-name: shakeX; animation-duration: 0.5s; }
</style>