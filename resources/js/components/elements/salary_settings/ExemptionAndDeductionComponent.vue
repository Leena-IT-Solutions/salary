<template>
    <div class="exemption-dashboard">
        <!-- Top Action Bar -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Exemptions & Deductions</h4>
                    <p class="text-muted small mb-0">Manage tax exemptions and voluntary/statutory salary deductions.</p>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-plus-circle']" class="me-2"></i>
                        {{ isForm ? 'Close Form' : 'Add Component' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ item.id ? 'Edit Configuration' : 'New Configuration' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-8">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Component Particulars</h6>
                                <div class="row g-3">
                                    <forms-select-field v-model="item.exe_and_ded_type_id" name="exe_and_ded_type_id" label="Section/Type" :options="types" classes="col-md-6"></forms-select-field>
                                    <forms-text-field v-if="item.exe_and_ded_type_id == 0" v-model="item.custom_type" name="custom_type" label="Specify Category" classes="col-md-6"></forms-text-field>
                                    <forms-text-field v-model="item.name" name="name" label="Display Name" classes="col-md-6"></forms-text-field>
                                    <forms-text-field v-model="item.name_in_payslip" name="name_in_payslip" label="Pay-slip Label" classes="col-md-6"></forms-text-field>
                                    
                                    <div class="col-12 mt-4">
                                        <div class="p-3 bg-light rounded-4 border">
                                            <div class="row align-items-end g-3">
                                                <forms-radio-field v-model="item.calculation" name="calculation" label="Calculation Method" classes="col-md-7" :options="[
                                                    {key: 'Flat (Amount)', val: 'Flat'},
                                                    {key: 'Percentage (%) of Basic', val: 'Basic'},
                                                ]"></forms-radio-field>

                                                <div class="col-md-5">
                                                    <label class="form-label small fw-bold text-uppercase">Value / Rate</label>
                                                    <div class="input-group input-group-lg shadow-sm">
                                                        <span class="input-group-text bg-white">{{ item.calculation == 'Flat' ? '₹' : '%' }}</span>
                                                        <input type="number" v-model="item.value" class="form-control border-start-0" placeholder="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white shadow-hover">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Status & Visibility</h6>
                                <div class="col-12">
                                    <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border mb-3">
                                        <input class="form-check-input mt-2" type="checkbox" v-model="item.is_active" id="checkActive">
                                        <label class="form-check-label ms-2" for="checkActive">
                                            <span class="fw-bold d-block">Active Component</span>
                                            <small class="text-muted">Available for processing in current payroll cycles.</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="mt-auto pt-4 bg-light p-3 rounded-4 border border-dashed">
                                    <p class="small text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Changes to this component will affect future payroll calculations for all associated employees.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="item.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="deleteItem()">
                                    <i class="bi bi-trash3 me-2"></i> Delete Configuration
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__headShake" @click="deleteNow()">
                                    Confirm Permanent Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill" @click="toggleForm">Discard</button>
                                <forms-submit-button name="" v-model="loading" :label="item.id ? 'Update Component' : 'Save Component'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Components List -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
                            Defined Structures
                            <span class="badge bg-soft-primary text-primary ms-3 fs-6 px-3 rounded-pill">{{ items.length }} Components</span>
                        </h5>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group shadow-sm border-0 rounded-pill overflow-hidden bg-light">
                            <span class="input-group-text bg-transparent border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-transparent border-0 py-2" v-model="params.value" placeholder="Search by name or type..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3">ID</th>
                            <th>Component Name</th>
                            <th>Calculation Logic</th>
                            <th class="text-center">Rate / Value</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in items" :key="row.id" class="cursor-pointer transition-all hover-row" @click="edit(row)">
                            <td class="ps-4 text-muted small fw-mono">#{{ row.id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-sq-box me-3 bg-soft-danger text-danger">
                                        <i class="bi bi-patch-minus"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ row.name }}</div>
                                        <div class="text-muted small">{{ row.name_in_payslip }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-soft-secondary badge rounded-pill px-3 py-2 text-dark border">
                                    {{ row.calculation }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold fs-6 text-primary">
                                    {{ row.calculation == 'Flat' ? '₹' : '' }}{{ row.value }}{{ row.calculation != 'Flat' ? '%' : '' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span v-if="row.is_active" class="pulse-indicator-container">
                                    <span class="pulse-dot bg-success"></span>
                                    <span class="text-success small fw-bold">Active</span>
                                </span>
                                <span v-else class="text-muted small fw-bold">Inactive</span>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 transition-all" @click.stop="edit(row)">
                                    Modify
                                </button>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state-card py-4">
                                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-box-4835634-4021008.png" style="width: 120px; filter: grayscale(1); opacity: 0.5;">
                                    <h6 class="text-muted mt-3">No components found.</h6>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-dark rounded-pill px-5 transition-all w-custom" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-arrow-down-circle-fill me-2"></i>
                    Expand Lists
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
                exe_and_ded_type_id: null,
                custom_type: null,
                name: null,
                name_in_payslip: null,
                calculation: 'Flat',
                value: 0,
                is_active: true,
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
            this.item.exe_and_ded_type_id = null;
            this.item.custom_type = null;
            this.item.name = null;
            this.item.name_in_payslip = null;
            this.item.calculation = 'Flat';
            this.item.value = 0;
            this.item.is_active = true;
            this.isDelete = false;
        },

        edit(item){
            this.item.id = item.id;
            this.item.exe_and_ded_type_id = item.exe_and_ded_type_id;
            this.item.custom_type = item.custom_type;
            this.item.name = item.name;
            this.item.name_in_payslip = item.name_in_payslip;
            this.item.calculation = item.calculation;
            this.item.value = item.value;
            this.item.is_active = item.is_active ? true : false;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fetch(){
            let url = '/salary_settings/exemption_and_deduction/fetch';
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
            axios.post('/salary_settings/exemption_and_deduction/add', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/salary_settings/exemption_and_deduction/update', this.item).then(res => {
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
            axios.post('/salary_settings/exemption_and_deduction/delete', this.item).then(res => {
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
            key: 'Custom Exemption/Deduction Category',
            val: 0
        });

        this.fetch();
    },

}
</script>

<style scoped>
.exemption-dashboard {
    background-color: #f4f7f6;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.icon-sq-box {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.25rem;
}

.bg-soft-danger { background-color: #fee2e2; }
.bg-soft-primary { background-color: #eef2ff; }
.text-danger { color: #ef4444 !important; }
.text-primary { color: #6366f1 !important; }

.hover-row:hover {
    background-color: #f8fafc;
    transform: scale(1.002);
}

.transition-all {
    transition: all 0.2s ease-in-out;
}

.fw-mono {
    font-family: 'Courier New', Courier, monospace;
}

/* Pulse indicator */
.pulse-indicator-container {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    position: relative;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(74, 222, 128, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
}

/* Transitions */
.fade-slide-enter-active, .fade-slide-leave-active {
    transition: opacity 0.4s, transform 0.4s;
}
.fade-slide-enter-from, .fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.w-custom { min-width: 250px; }

@keyframes headShake {
    0% { transform: translateX(0); }
    6.5% { transform: translateX(-6px) rotateY(-9deg); }
    18.5% { transform: translateX(5px) rotateY(7deg); }
    31.5% { transform: translateX(-3px) rotateY(-5deg); }
    43.5% { transform: translateX(2px) rotateY(3deg); }
    50% { transform: translateX(0); }
}
.animate__headShake { animation-name: headShake; animation-duration: 1s; }
</style>