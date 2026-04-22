<template>
    <div class="org-settings-dashboard pt-0 px-0">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Leave Type Definitions</h4>
                    <p class="text-muted small mb-0">Configure master records for various leave categories like Sick, Casual, or LOP.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-bookmark-plus-fill']" class="me-2"></i>
                        {{ isForm ? 'Close Editor' : 'Define Category' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden border-start-primary">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ leave_type.id ? 'Modify Category' : 'New Leave Classification' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Classification Details</h6>
                                <div class="row g-3">
                                    <forms-text-field name="leave_type" label="Category Name" v-model="leave_type.leave_type" placeholder="e.g. Sick Leave" classes="col-md-7"></forms-text-field>
                                    <forms-text-field name="code" label="Short Code" v-model="leave_type.code" placeholder="SL" classes="col-md-5"></forms-text-field>
                                    <forms-select-field name="is_lop" label="Is this Loss of Pay?" v-model="leave_type.is_lop" 
                                    :options="[{ key: 'Yes (Deduction)', val: 'Yes' }, { key: 'No (Paid)', val: 'No' }]" classes="col-12 mt-2"></forms-select-field>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4 text-center d-flex align-items-center justify-content-center">
                            <div class="p-4 bg-soft-info rounded-circle shadow-inner">
                                <i class="bi bi-bookmark-star display-3 text-info"></i>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="leave_type.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="isDelete = true">
                                    <i class="bi bi-trash3 me-2"></i> Delete Category
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Removal
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill shadow-none" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="leave_type.id ? 'Save Changes' : 'Initialize Category'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Data List -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0 text-dark">Category Inventory</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group shadow-sm border border-light rounded-pill overflow-hidden bg-white">
                            <span class="input-group-text bg-transparent border-0 ps-3 ml-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-transparent border-0 py-2" v-model="params.value" placeholder="Filter categories..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3 cursor-pointer" style="width: 80px;" @click="orderBy('id')">ID <i class="bi bi-sort-down ms-1"></i></th>
                            <th class="cursor-pointer" @click="orderBy('leave_type')">Category <i class="bi bi-sort-down ms-1"></i></th>
                            <th class="cursor-pointer" @click="orderBy('code')">Code <i class="bi bi-sort-down ms-1"></i></th>
                            <th>Policy Type</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="dept in leave_types" :key="dept.id" class="cursor-pointer transition-all hover-glow border-bottom border-light" @click="edit(dept)">
                            <td class="ps-4">
                                <span class="badge bg-white text-muted border fw-mono">#{{ dept.id }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ dept.leave_type }}</div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-light text-primary border px-3 py-2 fw-mono">
                                    {{ dept.code }}
                                </span>
                            </td>
                            <td>
                                <span v-if="dept.is_lop === 'Yes'" class="badge bg-soft-danger text-danger rounded-pill px-3 py-2 border border-danger border-opacity-10">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> Loss of Pay
                                </span>
                                <span v-else class="badge bg-soft-success text-success rounded-pill px-3 py-2 border border-success border-opacity-10">
                                    <i class="bi bi-check-circle-fill me-1"></i> Paid Entitlement
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 transition-all" @click.stop="edit(dept)">
                                    <i class="bi bi-pencil-square text-primary me-1"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <tr v-if="leave_types.length === 0">
                            <td colspan="5" class="text-center py-5">
                                <div class="opacity-50 py-5">
                                    <i class="bi bi-bookmarks-fill display-1 text-light-emphasis"></i>
                                    <p class="mt-2 fw-bold">No categories defined.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-dark rounded-pill px-5 transition-all hover-shadow-sm" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-chevron-double-down me-2"></i>
                    Fetch More Categories
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
            loading: false,
            isDelete: false,
            leave_type: {
                id: null,
                leave_type: null,
                code: null,
                is_lop: 'No',
            },
            leave_types: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: 'leave_type',
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
            let url = '/organisation_settings/leaves_setup/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.current_page == 1){
                    this.leave_types = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.leave_types.push(item);
                    });
                }
                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.leave_types = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.leave_type.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            this.leave_type.id = null;
            this.leave_type.leave_type = null;
            this.leave_type.code = null;
            this.leave_type.is_lop = 'No';
            this.isDelete = false;
        },

        add(){
            this.loading = true;
            axios.post('/organisation_settings/leaves_setup/add', this.leave_type).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/organisation_settings/leaves_setup/update', this.leave_type).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        deleteNow(){
            this.loading = true;
            axios.post('/organisation_settings/leaves_setup/delete', this.leave_type).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        edit(item){
            this.leave_type.id = item.id;
            this.leave_type.leave_type = item.leave_type;
            this.leave_type.code = item.code;
            this.leave_type.is_lop = item.is_lop;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

    },

    created(){
        this.fetch();
    },

}
</script>

<style scoped>
.org-settings-dashboard {
    background-color: transparent;
    min-height: auto;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.bg-soft-danger { background-color: #fef2f2; color: #ef4444; }
.bg-soft-success { background-color: #f0fdf4; color: #22c55e; }
.bg-soft-info { background-color: #f0f9ff; color: #0ea5e9; }

.hover-glow:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    background-color: #f1f5f9;
}

.border-start-primary { border-start: 4px solid #6366f1 !important; }
.transition-all { transition: all 0.25s ease; }

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