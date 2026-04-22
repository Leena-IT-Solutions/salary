<template>
    <div class="services-dashboard">
        <!-- Top Action Bar -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Services Components</h4>
                    <p class="text-muted small mb-0">Define and manage peripheral service components and benefits.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-plus-circle']" class="me-2"></i>
                        {{ isForm ? 'Close Form' : 'Add Service' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ item.id ? 'Edit Service Details' : 'Configure New Service' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-7">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Service Definition</h6>
                                <div class="row g-3">
                                    <forms-select-field v-model="item.services_type_id" name="services_type_id" label="Service Category" :options="types" classes="col-md-6"></forms-select-field>
                                    <forms-text-field v-if="item.services_type_id == 0" v-model="item.custom_type" name="custom_type" label="Custom Category Name" classes="col-md-6"></forms-text-field>
                                    <forms-text-field v-model="item.name" name="name" label="Service Display Name" classes="col-md-6"></forms-text-field>
                                    <forms-text-field v-model="item.name_in_payslip" name="name_in_payslip" label="Pay-slip Label" classes="col-md-6"></forms-text-field>
                                    
                                    <div class="col-12 mt-4">
                                        <div class="p-3 bg-light rounded-3 border border-dashed">
                                            <div class="row g-3">
                                                <forms-radio-field v-model="item.pay_time" name="pay_time" label="Schedule" classes="col-md-6" :options="[
                                                    {key: 'Fixed (Recurring)', val: 'Fixed'},
                                                    {key: 'Variable (Dynamic)', val: 'Variable'},
                                                ]"></forms-radio-field>

                                                <forms-radio-field v-model="item.calculation" name="calculation" label="Calculation Type" classes="col-md-6" :options="[
                                                    {key: 'Flat Amount', val: 'Flat'},
                                                    {key: 'Percentage (%)', val: 'Basic'},
                                                ]"></forms-radio-field>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mt-4">
                                        <label class="form-label fw-bold small text-uppercase">{{ item.calculation == 'Flat' ? 'Standard Amount' : 'Percentage Value' }}</label>
                                        <div class="input-group input-group-lg shadow-sm">
                                            <span class="input-group-text bg-white">{{ item.calculation == 'Flat' ? '₹' : '%' }}</span>
                                            <input type="number" v-model="item.value" class="form-control border-start-0" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Rules & Compliance</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border">
                                            <input class="form-check-input" type="checkbox" v-model="item.is_active" id="checkActive">
                                            <label class="form-check-label fw-bold d-block" for="checkActive">
                                                Active Status
                                                <small class="d-block text-muted fw-normal mt-1">Component is available for use in salary groups.</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border">
                                            <input class="form-check-input" type="checkbox" v-model="item.is_compulsory" id="checkCompulsory">
                                            <label class="form-check-label fw-bold d-block" for="checkCompulsory">
                                                Compulsory
                                                <small class="d-block text-muted fw-normal mt-1">Automatically included for all eligible employees.</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border">
                                            <input class="form-check-input" type="checkbox" v-model="item.is_pro_rata" id="checkProRata">
                                            <label class="form-check-label fw-bold d-block" for="checkProRata">
                                                Pro-rata Calculation
                                                <small class="d-block text-muted fw-normal mt-1">Adjust based on days worked.</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch p-3 ps-5 rounded-4 hover-bg-light transition-all border">
                                            <input class="form-check-input" type="checkbox" v-model="item.is_in_payslip" id="checkPayslip">
                                            <label class="form-check-label fw-bold d-block" for="checkPayslip">
                                                Display in Payslip
                                                <small class="d-block text-muted fw-normal mt-1">Visible to employee in their monthly statement.</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="item.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="deleteItem()">
                                    <i class="bi bi-trash3 me-2"></i> Delete Service
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Permanent Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="item.id ? 'Save Changes' : 'Register Service'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Library Section -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0">Registered Services</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group shadow-sm rounded-pill overflow-hidden">
                            <span class="input-group-text bg-white border-end-0 ps-4 ml-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 py-2 ps-2" v-model="params.value" placeholder="Search by service name..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold text-muted">
                            <th class="ps-4 py-3" style="width: 80px;">ID</th>
                            <th>Service Identifier</th>
                            <th>Schedule</th>
                            <th>Calculation</th>
                            <th class="text-center">Rate/Val</th>
                            <th class="text-center">Compulsory</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in items" :key="row.id" class="cursor-pointer transition-all" @click="edit(row)">
                            <td class="ps-4">
                                <span class="badge bg-light text-dark fw-mono">#{{ row.id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-box-sm me-3 bg-soft-info text-info rounded-circle">
                                        <i class="bi bi-gear-wide-connected"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ row.name }}</div>
                                        <div class="text-muted small">Visible as: {{ row.name_in_payslip }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span :class="row.pay_time === 'Fixed' ? 'badge-soft-success' : 'badge-soft-warning'" class="badge rounded-pill px-3 py-2">
                                    {{ row.pay_time }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-medium text-dark">{{ row.calculation }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold h6 mb-0 text-primary">
                                    {{ row.calculation == 'Flat' ? '₹' : '' }}{{ row.value }}{{ row.calculation != 'Flat' ? '%' : '' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span v-if="row.is_compulsory" class="badge-soft-danger badge rounded-pill">Required</span>
                                <span v-else class="text-muted small">Optional</span>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-light btn-icon rounded-circle hover-shadow transition-all" @click.stop="edit(row)">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="7" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-suit-heart display-1 text-light-emphasis d-block mb-3"></i>
                                    <h6 class="text-muted">No services configured yet.</h6>
                                    <button class="btn btn-sm btn-link mt-2 text-decoration-none" @click="toggleForm">Add your first service componente</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-dark rounded-pill px-5 transition-all w-100 w-md-auto" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-arrow-down-circle me-2"></i>
                    Load more services
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
                services_type_id: null,
                custom_type: null,
                name: null,
                name_in_payslip: null,
                calculation: 'Flat',
                pay_time: 'Fixed',
                value: 0,
                is_active: true,
                is_pro_rata: true,
                is_in_payslip: true,
                is_compulsory: false,
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
            this.item.services_type_id = null;
            this.item.custom_type = null;
            this.item.name = null;
            this.item.name_in_payslip = null;
            this.item.calculation = 'Flat';
            this.item.pay_time = 'Fixed';
            this.item.value = 0;
            this.item.is_active = true;
            this.item.is_pro_rata = true;
            this.item.is_in_payslip = true;
            this.item.is_compulsory = false;
            this.isDelete = false;
        },

        edit(item){
            this.item.id = item.id;
            this.item.services_type_id = item.services_type_id;
            this.item.custom_type = item.custom_type;
            this.item.name = item.name;
            this.item.name_in_payslip = item.name_in_payslip;
            this.item.calculation = item.calculation;
            this.item.pay_time = item.pay_time;
            this.item.value = item.value;
            this.item.is_active = item.is_active ? true : false;
            this.item.is_pro_rata = item.is_pro_rata ? true : false;
            this.item.is_in_payslip = item.is_in_payslip ? true : false;
            this.item.is_compulsory = item.is_compulsory ? true : false;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fetch(){
            let url = '/salary_settings/services/fetch';
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
            axios.post('/salary_settings/services/add', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/salary_settings/services/update', this.item).then(res => {
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
            axios.post('/salary_settings/services/delete', this.item).then(res => {
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
            key: 'Custom Service Category',
            val: 0
        });

        this.fetch();
    },

}
</script>

<style scoped>
.services-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.icon-box-sm {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.bg-soft-info { background-color: #e0f2fe; }
.text-info { color: #0284c7 !important; }

.badge-soft-success { background-color: #ecfdf5; color: #059669; }
.badge-soft-warning { background-color: #fffbeb; color: #d97706; }
.badge-soft-danger { background-color: #fef2f2; color: #dc2626; }

.hover-bg-light:hover {
    background-color: #f8fafc;
}

.hover-shadow:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.btn-icon {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.transition-all {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.fw-mono {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 0.85rem;
}

/* Transitions */
.fade-slide-enter-active, .fade-slide-leave-active {
    transition: all 0.4s ease;
}
.fade-slide-enter-from, .fade-slide-leave-to {
    opacity: 0;
    transform: scale(0.98) translateY(-10px);
}

@keyframes shakeX {
    from, to { transform: translate3d(0, 0, 0); }
    10%, 30%, 50%, 70%, 90% { transform: translate3d(-10px, 0, 0); }
    20%, 40%, 60%, 80% { transform: translate3d(10px, 0, 0); }
}
.animate__shakeX { animation-name: shakeX; animation-duration: 0.5s; }
</style>