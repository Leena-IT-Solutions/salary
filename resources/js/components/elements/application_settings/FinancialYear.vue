<template>
    <div class="fy-dashboard">
        <!-- Top Action Bar -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Financial Years</h4>
                    <p class="text-muted small mb-0">Manage accounting periods and identify the active processing year.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-plus-circle']" class="me-2"></i>
                        {{ isForm ? 'Close Form' : 'Add Financial Year' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ item.id ? 'Edit Financial Year' : 'New Financial Period' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Period Definition</h6>
                                <div class="row g-3">
                                    <forms-text-field name="fy_name" label="Financial Year Name" v-model="item.fy_name" placeholder="e.g. FY 2024-25" classes="col-12"></forms-text-field>
                                    
                                    <div class="col-md-6">
                                        <forms-date-field name="from" label="Start Date" v-model="item.from" classes="w-100"></forms-date-field>
                                    </div>
                                    <div class="col-md-6">
                                        <forms-date-field name="to" label="End Date" v-model="item.to" classes="w-100"></forms-date-field>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Status Settings</h6>
                                <div class="col-12">
                                    <forms-select-field name="is_current_year" label="Set as Current Year?" v-model="item.is_current_year" 
                                    :options="[{ key: 'Yes, Active Year', val: 'Yes' }, { key: 'No, Previous/Future', val: 'No' }]" classes="mb-4"></forms-select-field>
                                    
                                    <div class="p-3 bg-soft-info rounded-3 border border-info border-opacity-25 mt-2">
                                        <p class="small text-info-emphasis mb-0"><i class="bi bi-info-circle me-1"></i> The active year determines the default period for all salary processing and attendance logs.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="item.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="deleteItem()">
                                    <i class="bi bi-trash3 me-2"></i> Delete Period
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="item.id ? 'Save Changes' : 'Initialize Year'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Data Grid -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0">Historical Records</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group shadow-sm rounded-pill overflow-hidden bg-light border-0">
                            <span class="input-group-text bg-transparent border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-transparent border-0 py-2" v-model="params.value" placeholder="Search periods..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3" style="width: 80px;">ID</th>
                            <th>Financial Year</th>
                            <th>Valid From</th>
                            <th>Valid To</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in items" :key="row.id" class="cursor-pointer transition-all border-bottom border-light" @click="edit(row)">
                            <td class="ps-4">
                                <span class="badge bg-white text-muted border fw-mono">#{{ row.id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="fy-icon me-3" :class="row.is_current_year === 'Yes' ? 'bg-primary' : 'bg-secondary'">
                                        <i class="bi bi-calendar3-range text-white"></i>
                                    </div>
                                    <div class="fw-bold text-dark fs-6">{{ row.fy_name }}</div>
                                </div>
                            </td>
                            <td class="text-muted fw-medium">{{ formatDate(row.from) }}</td>
                            <td class="text-muted fw-medium">{{ formatDate(row.to) }}</td>
                            <td class="text-center">
                                <span v-if="row.is_current_year === 'Yes'" class="badge-soft-success badge border border-success border-opacity-25 rounded-pill px-3 py-2">
                                    <i class="bi bi-check-circle-fill me-1"></i> Current Year
                                </span>
                                <span v-else class="badge bg-light text-muted rounded-pill px-3 py-2 border">Archived</span>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 transition-all" @click.stop="edit(row)">
                                    <i class="bi bi-pencil-square text-primary me-1"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="6" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="bi bi-calendar-x display-1"></i>
                                    <p class="mt-2 fw-bold">No financial years configured.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-dark rounded-pill px-5 transition-all" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-arrow-down-circle me-2"></i>
                    Load More Years
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
                fy_name: null,
                from: null,
                to: null,
                is_current_year: 'No',
            },
            items: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: 'fy_name',
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

        formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        },

        reset(){
            this.item.id = null;
            this.item.fy_name = null;
            this.item.from = null;
            this.item.to = null;
            this.item.is_current_year = 'No';
            this.isDelete = false;
        },

        edit(item){
            this.item.id = item.id;
            this.item.fy_name = item.fy_name;
            this.item.from = item.from;
            this.item.to = item.to;
            this.item.is_current_year = item.is_current_year;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fetch(){
            let url = '/application_settings/financial_year/fetch';
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
            axios.post('/application_settings/financial_year/add', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/application_settings/financial_year/update', this.item).then(res => {
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
            axios.post('/application_settings/financial_year/delete', this.item).then(res => {
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
.fy-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.fy-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.25rem;
}

.bg-soft-success { background-color: #ecfdf5; color: #059669; }
.badge-soft-success { background-color: #ecfdf5; color: #059669; }

.bg-soft-info { background-color: #e0f2fe; }

.transition-all {
    transition: all 0.25s ease;
}

.fw-mono {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

/* Animations */
.fade-slide-enter-active, .fade-slide-leave-active {
    transition: all 0.4s ease;
}
.fade-slide-enter-from, .fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-15px);
}

@keyframes shakeX {
    from, to { transform: translate3d(0, 0, 0); }
    10%, 30%, 50%, 70%, 90% { transform: translate3d(-10px, 0, 0); }
    20%, 40%, 60%, 80% { transform: translate3d(10px, 0, 0); }
}
.animate__shakeX { animation-name: shakeX; animation-duration: 0.5s; }

.bg-light-subtle {
    background-color: #f8fafc !important;
}
</style>