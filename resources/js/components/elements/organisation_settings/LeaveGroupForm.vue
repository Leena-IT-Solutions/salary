<template>
    <div class="org-settings-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Leave Entitlements & Groups</h4>
                    <p class="text-muted small mb-0">Define corporate leave policies by grouping various leave types for different staff levels.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-plus-circle-fill']" class="me-2"></i>
                        {{ isForm ? 'Close Editor' : 'Create Leave Group' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden border-start-primary">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ leave_group.id ? 'Edit Group Policy' : 'New Leave Policy Definition' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Policy Identity</h6>
                                <div class="row g-3">
                                    <forms-text-field name="name" label="Group Policy Name" v-model="leave_group.name" :error="errors.name" placeholder="e.g. Standard Full-Time Staff" classes="col-12"></forms-text-field>
                                    
                                    <div class="mt-4 text-center">
                                        <div class="total-leaves-display p-4 bg-primary text-white rounded-circle shadow-lg mx-auto d-inline-block" style="width: 140px; height: 140px;">
                                            <div class="fs-1 fw-bold">{{ total_leaves }}</div>
                                            <div class="small fw-bold opacity-75">TOTAL DAYS</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-7">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Component Entitlements</h6>
                                <div class="leave-options-grid pe-2" style="max-height: 400px; overflow-y: auto;">
                                    <div v-for="l in leavesOptions" :key="l.val" class="leave-option-item p-3 mb-3 bg-light rounded-4 border transition-all" :class="{'selected-leave': l.x > 0}">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-check form-switch me-3 ps-0">
                                                        <input class="form-check-input ms-0" type="checkbox" v-model="l.is" :id="'ltid_'+l.val" @change="l.x = l.is ? (l.x || 1) : 0">
                                                    </div>
                                                    <div>
                                                        <label :for="'ltid_'+l.val" class="fw-bold text-dark d-block mb-0 cursor-pointer">{{ l.key }}</label>
                                                        <small class="text-muted">Set annual quota for this type</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <div class="input-group input-group-sm rounded-3 overflow-hidden shadow-sm" style="width: 100px;">
                                                    <input v-model="l.x" type="number" class="form-control border-0 text-center fw-bold" :disabled="!l.is">
                                                    <span class="input-group-text border-0 bg-white small">Days</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <div v-if="leave_group.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="isDelete = true">
                                    <i class="bi bi-trash3 me-2"></i> Delete Group
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Permanent Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill shadow-none" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="leave_group.id ? 'Save Policy' : 'Activate Policy'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Visual Group Cards -->
        <div class="row g-4">
            <div v-for="lg in leave_groups" :key="lg.id" class="col-12 col-md-6 col-xxl-4">
                <div class="card leave-group-card border-0 shadow-sm rounded-4 h-100 overflow-hidden transition-all">
                    <div class="card-header bg-white p-4 pb-0 border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-soft-primary text-primary rounded-pill mb-2 px-3">Policy #{{ lg.id }}</span>
                            <h5 class="fw-bold text-dark mb-0">{{ lg.name }}</h5>
                        </div>
                        <button @click="edit(lg)" class="btn btn-soft-light rounded-circle shadow-sm p-2 transition-all">
                            <i class="bi bi-pencil-square text-primary"></i>
                        </button>
                    </div>
                    
                    <div class="card-body p-4 mt-2">
                        <div class="leave-breakdown mb-4">
                            <div v-for="hd in lg.lgh" :key="hd.id" class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light-subtle rounded-3 border-start border-3 border-primary border-opacity-25">
                                <span class="small fw-semibold text-muted">{{ hd.leave_master.leave_type }}</span>
                                <span class="badge bg-white text-dark border px-2">{{ hd.no_of_leaves }} <small>days</small></span>
                            </div>
                        </div>
                        
                        <div class="total-metric p-3 rounded-4 bg-primary text-white d-flex justify-content-between align-items-center shadow-lg">
                            <span class="fw-bold opacity-75 small">TOTAL ANNUAL QUOTA</span>
                            <span class="fs-4 fw-bold">{{ lg.total_leaves }} <small class="fs-6">Days</small></span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="leave_groups.length === 0" class="col-12 text-center py-5">
                <div class="opacity-50 py-5">
                    <i class="bi bi-calendar-range-fill display-1 text-light-emphasis"></i>
                    <p class="mt-2 fw-bold">No leave groups configured.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {

    props: ['leaves'],

    data(){
        return {
            isForm: false,
            loading: false,
            isDelete: false,
            leave_group: {
                id: null,
                name: null,
                total_leaves: 0,
                heads: [],
            },
            errors: {
                name: null,
                total_leaves: null,
            },
            leavesOptions: [],
            leave_groups: [],
        };
    },

    computed: {
        total_leaves() {
            let tot = 0;
            this.leavesOptions.forEach(item => {
                if (item.is) {
                    tot += item.x * 1;
                }
            });
            this.leave_group.total_leaves = tot;
            return tot;
        }
    },

    methods: {
        toggleForm() {
            if(this.isForm) {
                this.resetForm();
                this.isForm = false;
            } else {
                this.isForm = true;
            }
        },

        edit(lg){
            this.resetForm();
            this.leave_group.id = lg.id;
            this.leave_group.name = lg.name;
            this.leave_group.total_leaves = lg.total_leaves;

            lg.lgh.forEach(ll => {
                this.leavesOptions.forEach(item => {
                    if(ll.leave_master_id == item.val){
                        item.is = true;
                        item.x = ll.no_of_leaves;
                    }
                });
            });
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            axios.post('/organisation_settings/leaves_setup/delete_lg', this.leave_group).then(res => {
                this.isDelete = false;
                this.loading = false;
                this.resetForm();
                this.fetch();
                this.isForm = false;
            });
        },

        fetch(){
            axios.get('/organisation_settings/leaves_setup/fetch_lg')
            .then(res => {
                this.leave_groups = res.data;
            });
        },

        save(){
            let url = this.leave_group.id == null ? '/organisation_settings/leaves_setup/save_lg' : '/organisation_settings/leaves_setup/update_lg';

            this.resetErrors();
            this.leave_group.heads = this.leavesOptions;
            this.loading = true;
            axios.post(url, this.leave_group)
            .then(res => {
                this.loading = false;
                this.resetForm();
                this.fetch();
                this.isForm = false;
            })
            .catch(error => {
                this.loading = false;
                if (error.response && error.response.data && error.response.data.errors) {
                    this.setErrors(error.response.data.errors);
                }
            });
        },

        resetForm(){
            this.leave_group.id = null;
            this.leave_group.name = null;
            this.leave_group.total_leaves = 0;
            this.leave_group.heads = [];
            this.isDelete = false;
            this.makeLeavesOption();
        },

        resetErrors(){
            this.errors.name = null;
            this.errors.total_leaves = null;
        },

        setErrors(errors){
            this.errors.name = errors.name ? errors.name[0] : null;
            this.errors.total_leaves = errors.total_leaves ? errors.total_leaves[0] : null;
        },

        makeLeavesOption(){
            this.leavesOptions = [];
            this.leaves.forEach(l => {
                let item = {
                    key: l.leave_type + ' - ' + l.code,
                    val: l.id,
                    x: 0,
                    is: false
                };
                this.leavesOptions.push(item);
            });
        },

    },

    created(){
        this.makeLeavesOption();
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

.bg-soft-primary { background-color: #eef2ff; color: #6366f1; }
.bg-soft-light { background-color: #f8fafc; }

.total-leaves-display {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 8px solid rgba(255, 255, 255, 0.2);
}

.leave-option-item:hover {
    border-color: #6366f1 !important;
}

.selected-leave {
    background-color: #f5f3ff !important;
    border-color: #a78bfa !important;
}

.leave-group-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
}

.border-start-primary { border-start: 4px solid #6366f1 !important; }
.transition-all { transition: all 0.3s ease; }

.cursor-pointer { cursor: pointer; }

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

.leave-options-grid::-webkit-scrollbar { width: 4px; }
.leave-options-grid::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>