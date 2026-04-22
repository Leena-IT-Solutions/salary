<template>
    <div class="org-settings-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Business Units & Departments</h4>
                    <p class="text-muted small mb-0">Define the organizational hierarchy and departmental divisions.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-plus-circle']" class="me-2"></i>
                        {{ isForm ? 'Close Editor' : 'Create Department' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ item.id ? 'Edit Department details' : 'New Division Entry' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Operational Alignment</h6>
                                <div class="row g-3">
                                    <forms-text-field name="department" label="Department Name" v-model="item.department" placeholder="e.g. Talent Acquisition" classes="col-md-8"></forms-text-field>
                                    <forms-text-field name="code" label="Unit Code" v-model="item.code" placeholder="HR-TA" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="description" label="Objective / Description" v-model="item.description" placeholder="Short summary of departmental functions..." classes="col-12 mt-2"></forms-text-field>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4 text-center d-flex align-items-center justify-content-center">
                            <div class="p-4 bg-soft-primary rounded-circle shadow-inner">
                                <i class="bi bi-diagram-3 display-3 text-primary"></i>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="item.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="deleteItem()">
                                    <i class="bi bi-trash3 me-2"></i> Delete Department
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Permanent Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="item.id ? 'Save Updates' : 'Initialize Department'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
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
                        <h5 class="fw-bold mb-0 text-dark">Active Divisions</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group shadow-sm border border-light rounded-pill overflow-hidden bg-white">
                            <span class="input-group-text bg-transparent border-0 ps-3 ml-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-transparent border-0 py-2" v-model="params.value" placeholder="Filter by name or code..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3 cursor-pointer" style="width: 80px;" @click="orderBy('id')">ID <i class="bi bi-sort-down ms-1"></i></th>
                            <th class="cursor-pointer" @click="orderBy('department')">Department Identity <i class="bi bi-sort-down ms-1"></i></th>
                            <th class="cursor-pointer" @click="orderBy('code')">Unit Code <i class="bi bi-sort-down ms-1"></i></th>
                            <th>Objective</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in items" :key="row.id" class="cursor-pointer transition-all hover-glow border-bottom border-light" @click="edit(row)">
                            <td class="ps-4">
                                <span class="badge bg-white text-muted border fw-mono">#{{ row.id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="dept-brand me-3 bg-soft-primary text-primary">
                                        {{ (row.department && row.department.length > 0) ? row.department.charAt(0).toUpperCase() : '?' }}
                                    </div>
                                    <div class="fw-bold text-dark fs-6">{{ row.department }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-light text-primary border px-3 py-2 fw-mono">
                                    {{ row.code }}
                                </span>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 300px;">{{ row.description || '—' }}</div>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 transition-all" @click.stop="edit(row)">
                                    <i class="bi bi-pencil-square text-primary me-1"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="5" class="text-center py-5">
                                <div class="opacity-50 py-5">
                                    <i class="bi bi-diagram-3-fill display-1 text-light-emphasis"></i>
                                    <p class="mt-2 fw-bold">No departments found.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-dark rounded-pill px-5 transition-all hover-shadow-sm" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-arrow-repeat me-2"></i>
                    Fetch More Units
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
            item: {
                id: null,
                department: null,
                code: null,
                description: null,
            },
            items: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: 'department',
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
            this.item.department = null;
            this.item.code = null;
            this.item.description = null;
            this.isDelete = false;
        },

        edit(item){
            this.item.id = item.id;
            this.item.department = item.department;
            this.item.code = item.code;
            this.item.description = item.description;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fetch(){
            let url = '/organisation_settings/departments/fetch';
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
            axios.post('/organisation_settings/departments/add', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/organisation_settings/departments/update', this.item).then(res => {
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
            axios.post('/organisation_settings/departments/delete', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

    },

    created(){
        this.fetch();
    },

}
</script>

<style scoped>
.org-settings-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.dept-brand {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.2rem;
}

.bg-soft-primary { background-color: #eef2ff; color: #6366f1; }
.transition-all { transition: all 0.25s ease; }

.hover-glow:hover {
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.08);
}

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