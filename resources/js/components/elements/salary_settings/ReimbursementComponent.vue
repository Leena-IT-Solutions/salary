<template>
    <div class="reimbursement-dashboard">
        <!-- Top Action Bar -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Reimbursement Config</h4>
                    <p class="text-muted small mb-0">Manage employee expense claims and reimbursement structures.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-plus-circle']" class="me-2"></i>
                        {{ isForm ? 'Close Form' : 'New Component' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ item.id ? 'Modify Reimbursement Component' : 'Add Reimbursement Component' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-7">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Component Setup</h6>
                                <div class="row g-3">
                                    <forms-select-field v-model="item.reimbursement_type_id" name="reimbursement_type_id" label="Reimbursement Category" :options="types" classes="col-md-6"></forms-select-field>
                                    <forms-text-field v-if="item.reimbursement_type_id == 0" v-model="item.custom_type" name="custom_type" label="Custom Category Name" classes="col-md-6"></forms-text-field>
                                    <forms-text-field v-model="item.name" name="name" label="Component Title" classes="col-md-6"></forms-text-field>
                                    <forms-text-field v-model="item.name_in_payslip" name="name_in_payslip" label="Pay-slip Designation" classes="col-md-6"></forms-text-field>
                                    
                                    <div class="col-md-6 mt-4">
                                        <label class="form-label fw-bold small text-uppercase">Standard Limit / Value</label>
                                        <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                                            <span class="input-group-text bg-white border-end-0">₹</span>
                                            <input type="number" v-model="item.value" class="form-control border-start-0" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Operational Logic</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border shadow-sm">
                                            <input class="form-check-input" type="checkbox" v-model="item.is_active" id="checkActive">
                                            <label class="form-check-label fw-bold d-block" for="checkActive">
                                                Active Component
                                                <small class="d-block text-muted fw-normal mt-1">Enable this reimbursement type for employee selection.</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border shadow-sm">
                                            <input class="form-check-input" type="checkbox" v-model="item.is_annual" id="checkAnnual">
                                            <label class="form-check-label fw-bold d-block" for="checkAnnual">
                                                Annual Disbursement
                                                <small class="d-block text-muted fw-normal mt-1">Mark as an annual payout instead of monthly.</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="item.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="deleteItem()">
                                    <i class="bi bi-trash3 me-2"></i> Delete
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="item.id ? 'Save Changes' : 'Create Component'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Components Grid -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0">Library Coverage</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="search-input-group position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" class="form-control rounded-pill ps-5 py-2 border-light shadow-sm bg-light" v-model="params.value" placeholder="Filter reimbursements..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3">ID</th>
                            <th>Reimbursement Title</th>
                            <th>Disbursement Cycle</th>
                            <th class="text-center">Reference Value</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in items" :key="row.id" class="cursor-pointer transition-all border-bottom border-light" @click="edit(row)">
                            <td class="ps-4">
                                <span class="text-muted fw-mono">#{{ row.id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-avatar me-3 bg-soft-primary text-primary">
                                        <i class="bi bi-receipt"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ row.name }}</div>
                                        <div class="text-muted small">{{ row.name_in_payslip }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span :class="row.is_annual ? 'badge-soft-info' : 'badge-soft-purple'" class="badge rounded-pill px-3 py-2">
                                    {{ row.is_annual ? 'Annual' : 'Monthly' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark">₹{{ row.value }}/-</div>
                            </td>
                            <td class="text-center">
                                <span :class="row.is_active ? 'text-success' : 'text-danger'" class="small fw-bold">
                                    <i :class="row.is_active ? 'bi bi-lightning-fill' : 'bi bi-lightning'" class="me-1"></i>
                                    {{ row.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-white btn-sm shadow-sm border rounded-pill px-3 transition-all" @click.stop="edit(row)">
                                    <i class="bi bi-pencil-fill text-primary me-2"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="6" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="bi bi-card-checklist display-1"></i>
                                    <p class="mt-2">No reimbursement components found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-link text-decoration-none fw-bold" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-arrow-repeat me-2"></i>
                    {{ next_page_url ? 'Discover More Components' : 'End of Records' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {

    props: ['types'],

    data(){
        return {
            isForm: false,
            isDelete: false,
            loading: false,
            item: {
                id: null,
                reimbursement_type_id: null,
                custom_type: null,
                name: null,
                name_in_payslip: null,
                value: 0,
                is_active: true,
                is_annual: false,
            },
            items: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: 'name',
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
            this.item.reimbursement_type_id = null;
            this.item.custom_type = null;
            this.item.name = null;
            this.item.name_in_payslip = null;
            this.item.value = 0;
            this.item.is_active = true;
            this.item.is_annual = false;
            this.isDelete = false;
        },

        edit(item){
            this.item.id = item.id;
            this.item.reimbursement_type_id = item.reimbursement_type_id;
            this.item.custom_type = item.custom_type;
            this.item.name = item.name;
            this.item.name_in_payslip = item.name_in_payslip;
            this.item.value = item.value;
            this.item.is_active = item.is_active ? true : false;
            this.item.is_annual = item.is_annual ? true : false;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fetch(){
            let url = '/salary_settings/reimbursement/fetch';
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
            axios.post('/salary_settings/reimbursement/add', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/salary_settings/reimbursement/update', this.item).then(res => {
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
            axios.post('/salary_settings/reimbursement/delete', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

    },

    created () {
        this.types.push({
            key: 'Custom Reimbursement Category',
            val: 0
        });

        this.fetch();
    },

}
</script>

<style scoped>
.reimbursement-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.icon-avatar {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.25rem;
}

.bg-soft-primary { background-color: #eef2ff; }
.text-primary { color: #6366f1 !important; }

.badge-soft-info { background-color: #f0f9ff; color: #0369a1; }
.badge-soft-purple { background-color: #f5f3ff; color: #6d28d9; }

.hover-bg-light:hover {
    background-color: #f1f5f9;
}

.transition-all {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.fw-mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.8rem;
}

/* Transitions */
.fade-slide-enter-active, .fade-slide-leave-active {
    transition: all 0.4s ease;
}
.fade-slide-enter-from, .fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

@keyframes shakeX {
    from, to { transform: translate3d(0, 0, 0); }
    10%, 30%, 50%, 70%, 90% { transform: translate3d(-10px, 0, 0); }
    20%, 40%, 60%, 80% { transform: translate3d(10px, 0, 0); }
}
.animate__shakeX { animation-name: shakeX; animation-duration: 0.5s; }

.bg-light {
    background-color: #f1f5f9 !important;
}
</style>