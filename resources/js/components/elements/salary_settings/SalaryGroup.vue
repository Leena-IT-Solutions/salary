<template>
    <div class="salary-group-dashboard">
        <!-- Top Action Bar -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Salary Groups</h4>
                    <p class="text-muted small mb-0">Group various salary components together to form a pay package structure.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-plus-circle']" class="me-2"></i>
                        {{ isForm ? 'Close Form' : 'New Salary Group' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ item.id ? 'Modify Group Parameters' : 'Define Salary Structure Group' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-7">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Group Configuration</h6>
                                <div class="row g-3">
                                    <forms-text-field v-model="item.salary_group_name" name="salary_group_name" label="Group Title" placeholder="e.g. Corporate Standard - Level 1" classes="col-12"></forms-text-field>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-uppercase">OT Multiplier</label>
                                        <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                                            <span class="input-group-text bg-light border-end-0 fs-5">×</span>
                                            <input type="text" v-model="item.multiplier" class="form-control border-start-0" placeholder="1.5">
                                        </div>
                                        <small class="text-muted mt-1 d-block font-italic">Factor for hourly overtime calculation.</small>
                                    </div>

                                    <forms-text-field v-model="item.note" name="note" label="Internal Notes / Description" placeholder="Short description of this group..." classes="col-12 mt-2"></forms-text-field>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-dashed-active">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Administration</h6>
                                <div class="col-12">
                                    <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border shadow-sm">
                                        <input class="form-check-input mt-2" type="checkbox" v-model="item.is_active" id="checkActive">
                                        <label class="form-check-label ms-2" for="checkActive">
                                            <span class="fw-bold d-block">Group Availability</span>
                                            <small class="text-muted">If inactive, this group cannot be assigned to new employees.</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="mt-4 p-3 bg-soft-info rounded-4 border border-info border-opacity-25">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-info-circle-fill text-info fs-5"></i>
                                        <p class="small text-info-emphasis mb-0">After creating a group, you must define the individual earning components in the group details view.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="item.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="deleteItem()">
                                    <i class="bi bi-trash3 me-2"></i> Delete Group
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Permanent Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="item.id ? 'Save Updates' : 'Create Salary Group'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Search & Records -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0 text-dark">Salary Structure Library</h5>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group shadow-sm border border-light rounded-pill overflow-hidden bg-white">
                            <span class="input-group-text bg-transparent border-0 ps-3 ml-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-transparent border-0 py-2" v-model="params.value" placeholder="Search groups by name..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3">ID</th>
                            <th>Group Name & Analytics</th>
                            <th>OT Multiplier</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in items" :key="row.id" class="cursor-pointer transition-all hover-glow" @click="edit(row)">
                            <td class="ps-4">
                                <span class="badge bg-light text-dark fw-mono">#{{ row.id }}</span>
                            </td>
                            <td>
                                <a :href="'/salary_settings/salary_groupable/'+ row.id +'/data/'" class="text-decoration-none group-link" @click.stop>
                                    <div class="d-flex align-items-center">
                                        <div class="group-icon-avatar me-3 bg-soft-primary text-primary">
                                            <i class="bi bi-layers-half"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ row.salary_group_name }}</div>
                                            <div class="text-primary small fw-semibold">View Components <i class="bi bi-arrow-right-short"></i></div>
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <td>
                                <div class="badge-soft-info badge rounded-pill px-3 py-2 fw-mono">
                                    {{ row.multiplier }}x
                                </div>
                            </td>
                            <td>
                                <span :class="row.is_active ? 'badge-soft-success' : 'badge-soft-secondary'" class="badge rounded-pill px-3 py-2">
                                    {{ row.is_active ? 'Available' : 'Restricted' }}
                                </span>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 250px;" :title="row.note">
                                    {{ row.note || '—' }}
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                    <a :href="'/salary_settings/salary_groupable/'+ row.id +'/data/'" class="btn btn-white btn-sm px-3 border-end" @click.stop><i class="bi bi-eye text-muted"></i></a>
                                    <button class="btn btn-white btn-sm px-3" @click.stop="edit(row)"><i class="bi bi-pencil text-primary"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state py-5">
                                    <i class="bi bi-box-seam display-1 text-light-emphasis d-block mb-3"></i>
                                    <h6 class="text-muted">No salary groups found.</h6>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-dark rounded-pill px-5 transition-all hover-shadow-sm" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-chevron-down me-2"></i>
                    Load More Groups
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
                salary_group_name: null,
                note: null,
                multiplier: 1.5,
                is_active: true,
            },
            items: [],
            next_page_url: null,
            current_page: 1,
            params: {
                key: 'salary_group_name',
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
            this.item.salary_group_name = null;
            this.item.note = null;
            this.item.multiplier = 1.5;
            this.item.is_active = true;
            this.isDelete = false;
        },

        edit(item){
            this.item.id = item.id;
            this.item.salary_group_name = item.salary_group_name;
            this.item.note = item.note;
            this.item.multiplier = item.multiplier;
            this.item.is_active = item.is_active ? true : false;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fetch(){
            let url = '/salary_settings/salary_group/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.current_page == 1){
                    this.items = res.data.data;
                } else {
                    res.data.data.forEach(obj => {
                        this.items.push(obj);
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
            axios.post('/salary_settings/salary_group/add', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/salary_settings/salary_group/update', this.item).then(res => {
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
            axios.post('/salary_settings/salary_group/delete', this.item).then(res => {
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
.salary-group-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.group-icon-avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 1.5rem;
}

.bg-soft-primary { background-color: #eef2ff; }
.text-primary { color: #6366f1 !important; }

.badge-soft-success { background-color: #ecfdf5; color: #059669; }
.badge-soft-info { background-color: #f0f9ff; color: #0ea5e9; }
.badge-soft-secondary { background-color: #f1f5f9; color: #64748b; }

.hover-bg-light:hover { background-color: #f8fafc; }
.hover-glow:hover { box-shadow: 0 4px 12px rgba(99, 102, 241, 0.08); }

.transition-all { transition: all 0.25s ease; }

.group-link:hover .fw-bold { color: #6366f1 !important; }

.fw-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

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