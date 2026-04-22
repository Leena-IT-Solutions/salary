<template>
    <div class="org-settings-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Shift Rosters & Timings</h4>
                    <p class="text-muted small mb-0">Configure operational working hours, half-day policies, and overnight shift logic.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-clock-fill']" class="me-2"></i>
                        {{ isForm ? 'Close Editor' : 'Define New Shift' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden border-start-primary">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ working_shift.id ? 'Edit Shift Configuration' : 'Establish Operational Timing' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-7">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Shift parameters</h6>
                                <div class="row g-3">
                                    <forms-text-field name="name" label="Shift Identification Name" v-model="working_shift.name" placeholder="e.g. General Day Shift" classes="col-12"></forms-text-field>
                                    
                                    <div class="col-md-4">
                                        <div class="time-input-group p-3 bg-soft-success rounded-4 border border-success border-opacity-10 text-center">
                                            <label class="small fw-bold text-success mb-2 d-block text-uppercase">Log In</label>
                                            <forms-time-field name="in" label="" v-model="working_shift.in" classes="mb-0"></forms-time-field>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="time-input-group p-3 bg-soft-warning rounded-4 border border-warning border-opacity-10 text-center">
                                            <label class="small fw-bold text-warning mb-2 d-block text-uppercase">Half-Day</label>
                                            <forms-time-field name="halfday" label="" v-model="working_shift.halfday" classes="mb-0"></forms-time-field>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="time-input-group p-3 bg-soft-info rounded-4 border border-info border-opacity-10 text-center">
                                            <label class="small fw-bold text-info mb-2 d-block text-uppercase">Log Out</label>
                                            <forms-time-field name="out" label="" v-model="working_shift.out" classes="mb-0"></forms-time-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Overnight Logic</h6>
                                <div class="col-12 pt-2">
                                    <forms-select-field name="is_next_day_out" label="Does shift end on the following day?" v-model="working_shift.is_next_day_out" 
                                    :options="[{ key: 'Yes (Night Shift)', val: 1 }, { key: 'No (Same Day)', val: 0 }]" classes="mb-4"></forms-select-field>
                                    
                                    <div class="p-3 bg-light rounded-4 border border-dashed text-center opacity-75">
                                        <i class="bi bi-moon-stars text-primary fs-3 d-block mb-2"></i>
                                        <p class="small mb-0">Select 'Yes' if the 'Log Out' time falls after midnight of the 'Log In' date.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <div v-if="working_shift.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="isDelete = true">
                                    <i class="bi bi-calendar-x me-2"></i> Retire Shift
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Permanent Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill shadow-none" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="working_shift.id ? 'Save Roster' : 'Finalize Roster'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Records Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
                            <i class="bi bi-calendar3-range text-primary me-2"></i>
                            Operational Shifts
                        </h5>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border border-light">
                            <span class="input-group-text bg-transparent border-0 ps-3 ml-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-transparent border-0 py-2" v-model="params.value" placeholder="Search by shift name..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light-subtle">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3 text-start" style="width: 80px;">ID</th>
                            <th class="text-start">Shift Identity</th>
                            <th>Entry</th>
                            <th>Half-Day</th>
                            <th>Exit</th>
                            <th>Type</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in working_shifts" :key="item.id" class="cursor-pointer transition-all hover-glow border-bottom border-light" @click="edit(item)">
                            <td class="ps-4 text-start">
                                <span class="badge bg-white text-muted border fw-mono">#{{ item.id }}</span>
                            </td>
                            <td class="text-start">
                                <div class="fw-bold text-dark fs-6">{{ item.name }}</div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-soft-success text-success px-3 py-2 fw-mono border border-success border-opacity-25">{{ item.in }}</span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-soft-warning text-warning px-3 py-2 fw-mono border border-warning border-opacity-25">{{ item.halfday }}</span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-soft-info text-info px-3 py-2 fw-mono border border-info border-opacity-25">{{ item.out }}</span>
                            </td>
                            <td>
                                <span v-if="item.is_next_day_out" class="badge bg-dark rounded-pill px-3 py-2"><i class="bi bi-moon-fill me-1"></i> Night</span>
                                <span v-else class="badge bg-light text-muted border rounded-pill px-3 py-2"><i class="bi bi-sun-fill me-1"></i> Day</span>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-white border shadow-sm rounded-pill px-3 transition-all" @click.stop="edit(item)">
                                    <i class="bi bi-pencil-square text-primary me-1"></i> Manage
                                </button>
                            </td>
                        </tr>
                        <tr v-if="working_shifts.length === 0">
                            <td colspan="7" class="text-center py-5">
                                <div class="opacity-50 py-5">
                                    <i class="bi bi-alarm display-1 text-light-emphasis"></i>
                                    <p class="mt-2 fw-bold">No active shifts defined.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-dark rounded-pill px-5 transition-all hover-shadow-sm" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-arrow-down-short me-2 fs-5"></i>
                    Load More Shifts
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
            working_shift: {
                id: null,
                name: null,
                in: null,
                out: null,
                halfday: null,
                is_next_day_out: 0,
            },
            working_shifts: [],
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
            this.working_shift.id = null;
            this.working_shift.name = null;
            this.working_shift.in = null;
            this.working_shift.out = null;
            this.working_shift.halfday = null;
            this.working_shift.is_next_day_out = 0;
            this.isDelete = false;
        },

        edit(item){
            this.working_shift.id = item.id;
            this.working_shift.name = item.name;
            this.working_shift.in = item.in;
            this.working_shift.out = item.out;
            this.working_shift.halfday = item.halfday;
            this.working_shift.is_next_day_out = item.is_next_day_out;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fetch(){
            let url = '/organisation_settings/working_shifts/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.current_page == 1){
                    this.working_shifts = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.working_shifts.push(item);
                    });
                }
                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.working_shifts = [];
            this.fetch();
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.working_shift.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        add(){
            this.loading = true;
            axios.post('/organisation_settings/working_shifts/add', this.working_shift).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/organisation_settings/working_shifts/update', this.working_shift).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        deleteNow(){
            this.loading = true;
            axios.post('/organisation_settings/working_shifts/delete', this.working_shift).then(res => {
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

.bg-soft-success { background-color: #ecfdf5; color: #059669; }
.bg-soft-warning { background-color: #fffbeb; color: #d97706; }
.bg-soft-info { background-color: #f0f9ff; color: #0284c7; }

.time-input-group {
    transition: all 0.3s ease;
}

.time-input-group:focus-within {
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    background-color: white !important;
}

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