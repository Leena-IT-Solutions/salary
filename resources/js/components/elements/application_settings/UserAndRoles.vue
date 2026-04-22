<template>
    <div class="user-management-dashboard">
        <!-- Top Action Bar -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">User Accounts & Roles</h4>
                    <p class="text-muted small mb-0">Control administrative access and manage system user permissions.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-person-plus-fill']" class="me-2"></i>
                        {{ isForm ? 'Close Editor' : 'Create User' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ item.id ? 'Update User Privileges' : 'System User Onboarding' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Profile Information</h6>
                                <div class="row g-3">
                                    <forms-text-field name="name" label="Full Legal Name" v-model="item.name" placeholder="John Doe" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="username" label="System Username" v-model="item.username" placeholder="jdoe_admin" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="email" label="Professional Email" v-model="item.email" placeholder="john@company.com" classes="col-12"></forms-text-field>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Access Control</h6>
                                <forms-select-field name="role" label="System Role" v-model="item.role" 
                                :options="[{ key: 'Administrator', val: 'Administrator' }, { key: 'Time Office', val: 'Time Office' }, { key: 'Employee', val: 'Employee' }]" classes="mb-3"></forms-select-field>
                                
                                <forms-select-field name="status" label="Account Status" v-model="item.status" 
                                :options="[{ key: 'Active (Permit Access)', val: 'Active' }, { key: 'Inactive (Deny Access)', val: 'Inactive' }]" classes="mb-2"></forms-select-field>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="item.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="deleteItem()">
                                    <i class="bi bi-person-x me-2"></i> Revoke Access
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Account Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="item.id ? 'Apply Updates' : 'Grant Access'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- User Directory -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0">System User Directory</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="search-field position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" class="form-control rounded-pill ps-5 py-2 border-0 bg-light shadow-none" v-model="params.value" placeholder="Filter by name or email..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3 cursor-pointer" style="width: 80px;" @click="orderBy('id')">
                                UID <i class="bi bi-arrow-down-up ms-1 small"></i>
                            </th>
                            <th class="cursor-pointer" @click="orderBy('name')">
                                User Profile <i class="bi bi-arrow-down-up ms-1 small"></i>
                            </th>
                            <th class="cursor-pointer" @click="orderBy('username')">
                                Credentials <i class="bi bi-arrow-down-up ms-1 small"></i>
                            </th>
                            <th class="cursor-pointer" @click="orderBy('role')">
                                Role <i class="bi bi-arrow-down-up ms-1 small"></i>
                            </th>
                            <th class="text-center cursor-pointer" @click="orderBy('status')">
                                Status <i class="bi bi-arrow-down-up ms-1 small"></i>
                            </th>
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
                                    <div class="user-avatar-sm me-3" :class="getRoleClass(row.role)">
                                        {{ (row.name && row.name.length > 0) ? row.name.charAt(0).toUpperCase() : '?' }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ row.name || 'Unnamed User' }}</div>
                                        <div class="text-muted small">{{ row.email || 'No email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small text-muted"><i class="bi bi-person-badge me-1"></i> {{ row.username }}</div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-medium">
                                    {{ row.role }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span v-if="row.status === 'Active'" class="badge-soft-success badge border border-success border-opacity-25 rounded-pill px-3 py-2">
                                    <i class="bi bi-circle-fill me-1 tiny-dot"></i> Active
                                </span>
                                <span v-else class="badge bg-light text-muted rounded-pill px-3 py-2 border">Deactivated</span>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-white border shadow-sm rounded-circle p-2 hover-shadow transition-all" @click.stop="edit(row)">
                                    <i class="bi bi-shield-lock text-primary"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="6" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="bi bi-people display-1"></i>
                                    <p class="mt-2 fw-bold">No registered users found.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-dark rounded-pill px-5 transition-all w-100 w-md-auto" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-chevron-bar-down me-2"></i>
                    Expand Staff List
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
                name: null,
                email: null,
                username: null,
                role: 'Employee',
                status: 'Active',
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

        getRoleClass(role) {
            if (role === 'Administrator') return 'bg-danger-subtle text-danger';
            if (role === 'Time Office') return 'bg-info-subtle text-info';
            return 'bg-secondary-subtle text-secondary';
        },

        reset(){
            this.item.id = null;
            this.item.name = null;
            this.item.email = null;
            this.item.username = null;
            this.item.role = 'Employee';
            this.item.status = 'Active';
            this.isDelete = false;
        },

        edit(item){
            this.item.id = item.id;
            this.item.name = item.name;
            this.item.email = item.email;
            this.item.username = item.username;
            this.item.role = item.role;
            this.item.status = item.status;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fetch(){
            let url = '/application_settings/user_and_roles/fetch';
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
            axios.post('/application_settings/user_and_roles/add', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/application_settings/user_and_roles/update', this.item).then(res => {
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
            axios.post('/application_settings/user_and_roles/delete', this.item).then(res => {
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
.user-management-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.user-avatar-sm {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-weight: 700;
    font-size: 1.1rem;
}

.bg-soft-success { background-color: #ecfdf5; color: #059669; }
.badge-soft-success { background-color: #ecfdf5; color: #059669; }

.tiny-dot { font-size: 0.6rem; vertical-align: middle; }

.hover-shadow:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: scale(1.1);
}

.transition-all {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fw-mono {
    font-family: 'JetBrains Mono', SFMono-Regular, monospace;
}

/* Animations */
.fade-slide-enter-active, .fade-slide-leave-active {
    transition: all 0.4s ease;
}
.fade-slide-enter-from, .fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-20px);
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

.bg-danger-subtle { background-color: #fef2f2 !important; }
.bg-info-subtle { background-color: #f0f9ff !important; }
.bg-secondary-subtle { background-color: #f1f5f9 !important; }
</style>