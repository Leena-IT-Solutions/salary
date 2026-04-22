<template>
    <div class="statutory-dashboard">
        <!-- Top Action Bar -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Statutory Compliance</h4>
                    <p class="text-muted small mb-0">Manage legal compliance schemes (PF, ESI, PT) and registration details.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-plus-circle']" class="me-2"></i>
                        {{ isForm ? 'Close Form' : 'Register Scheme' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ item.id ? 'Modify Statutory Scheme' : 'New Statutory Registration' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-7">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Scheme Details</h6>
                                <div class="row g-3">
                                    <forms-text-field v-model="item.scheme_name" name="scheme_name" label="Full Scheme Name" placeholder="e.g. Employee Provident Fund" classes="col-12"></forms-text-field>
                                    <forms-text-field v-model="item.abbreviation" name="abbreviation" label="Short Name / Code" placeholder="e.g. PF" classes="col-md-6"></forms-text-field>
                                    <forms-text-field v-model="item.registration_number" name="registration_number" label="Registration Number" placeholder="Organization ID" classes="col-md-6"></forms-text-field>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Compliance Rules</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border shadow-sm">
                                            <input class="form-check-input" type="checkbox" v-model="item.is_active" id="checkActive">
                                            <label class="form-check-label fw-bold d-block" for="checkActive">
                                                Scheme Status
                                                <small class="d-block text-muted fw-normal mt-1">If enabled, deductions will be processed in payroll.</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border shadow-sm">
                                            <input class="form-check-input" type="checkbox" v-model="item.is_part_of_salary" id="checkSalaryPart">
                                            <label class="form-check-label fw-bold d-block" for="checkSalaryPart">
                                                Salary Structure Part
                                                <small class="d-block text-muted fw-normal mt-1">Display this as part of the employee's CTC structure.</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border shadow-sm">
                                            <input class="form-check-input" type="checkbox" v-model="item.is_pro_rata" id="checkProRata">
                                            <label class="form-check-label fw-bold d-block" for="checkProRata">
                                                Pro-rata basis
                                                <small class="d-block text-muted fw-normal mt-1">Adjust deduction based on worked days.</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="item.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="deleteItem()">
                                    <i class="bi bi-trash3 me-2"></i> Delete Scheme
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Permanent Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="item.id ? 'Verify & Update' : 'Register Compliance'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Compliance List -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0">Active Compliance Schemes</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="search-box">
                            <div class="input-group bg-light rounded-pill border-0 px-2">
                                <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" class="form-control bg-transparent border-0 py-2" v-model="params.value" placeholder="Filter schemes..." @keyup.enter="search()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light shadow-sm">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3">ID</th>
                            <th>Scheme & Identity</th>
                            <th>Registration ID</th>
                            <th class="text-center">Payroll Integration</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in items" :key="row.id" class="cursor-pointer transition-all border-bottom border-light" @click="edit(row)">
                            <td class="ps-4">
                                <span class="text-muted small fw-mono">#{{ row.id }}</span>
                            </td>
                            <td>
                                <a :href="'/salary_settings/statutory_compliance/'+ row.id +'/condition/'" class="text-decoration-none" @click.stop>
                                    <div class="d-flex align-items-center group-hover-translate">
                                        <div class="icon-orb me-3 bg-gradient-primary text-white">
                                            {{ row.abbreviation[0] }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ row.scheme_name }}</div>
                                            <div class="text-primary small fw-semibold">{{ row.abbreviation }} <i class="bi bi-arrow-right-short"></i></div>
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <td>
                                <span class="text-dark fw-medium small">{{ row.registration_number || 'Not Provided' }}</span>
                            </td>
                            <td class="text-center">
                                <span v-if="row.is_part_of_salary" class="badge-soft-success badge rounded-pill px-3">Active structure</span>
                                <span v-else class="text-muted small">Conditional</span>
                            </td>
                            <td class="text-center">
                                <span v-if="row.is_active" class="badge bg-success rounded-circle p-1" title="Enabled"><i class="bi bi-check text-white"></i></span>
                                <span v-else class="badge bg-secondary rounded-circle p-1" title="Disabled"><i class="bi bi-x text-white"></i></span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a :href="'/salary_settings/statutory_compliance/'+ row.id +'/condition/'" class="btn btn-sm btn-soft-primary rounded-pill px-3" @click.stop>Conditions</a>
                                    <button class="btn btn-sm btn-white border shadow-sm rounded-circle p-2" @click.stop="edit(row)">
                                        <i class="bi bi-pencil-fill text-primary"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4 opacity-50">
                                    <i class="bi bi-shield-check display-1"></i>
                                    <h5 class="mt-3">Awaiting compliance setup</h5>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-primary rounded-pill px-5 transition-all shadow-sm" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-arrow-down-short fs-4 align-middle"></i>
                    Show More Schemes
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {

    data(){
        return {
            isForm: false,
            isDelete: false,
            loading: false,
            item: {
                id: null,
                scheme_name: null,
                abbreviation: null,
                registration_number: null,
                is_active: true,
                is_part_of_salary: true,
                is_pro_rata: true,
            },
            items: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: 'scheme_name',
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

        reset(){
            this.item.id = null;
            this.item.scheme_name = null;
            this.item.abbreviation = null;
            this.item.registration_number = null;
            this.item.is_active = true;
            this.item.is_part_of_salary = true;
            this.item.is_pro_rata = true;
            this.isDelete = false;
        },

        edit(item){
            this.item.id = item.id;
            this.item.scheme_name = item.scheme_name;
            this.item.abbreviation = item.abbreviation;
            this.item.registration_number = item.registration_number;
            this.item.is_active = item.is_active ? true : false;
            this.item.is_part_of_salary = item.is_part_of_salary ? true : false;
            this.item.is_pro_rata = item.is_pro_rata ? true : false;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fetch(){
            let url = '/salary_settings/statutory_compliance/fetch';
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
            if(this.item.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        add(){
            this.loading = true;
            axios.post('/salary_settings/statutory_compliance/add', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/salary_settings/statutory_compliance/update', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/salary_settings/statutory_compliance/delete', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

    },

    created () {
        this.fetch();
    },

}
</script>

<style scoped>
.statutory-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.icon-orb {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 700;
    font-size: 1.25rem;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
}

.bg-soft-primary { background-color: #eef2ff; }
.btn-soft-primary { background-color: #eef2ff; color: #6366f1; border: none; }
.btn-soft-primary:hover { background-color: #6366f1; color: white; }

.badge-soft-success { background-color: #ecfdf5; color: #059669; }

.hover-bg-light:hover { background-color: #f8fafc; }

.group-hover-translate { transition: transform 0.3s ease; }
tr:hover .group-hover-translate { transform: translateX(5px); }

.transition-all { transition: all 0.25s ease; }

.fw-mono { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; }

/* Form Transitions */
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.4s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-15px); }

@keyframes shakeX {
    from, to { transform: translate3d(0, 0, 0); }
    10%, 30%, 50%, 70%, 90% { transform: translate3d(-10px, 0, 0); }
    20%, 40%, 60%, 80% { transform: translate3d(10px, 0, 0); }
}
.animate__shakeX { animation-name: shakeX; animation-duration: 0.5s; }
</style>