<template>
    <div class="earnings-dashboard">
        <!-- Top Action Bar -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Earning Components</h4>
                    <p class="text-muted small mb-0">Manage salary earnings, calculation types, and tax settings.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-plus-circle']" class="me-2"></i>
                        {{ isForm ? 'Close Form' : 'Create Earning' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Form Section -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ item.id ? 'Edit Earning Component' : 'New Earning Component' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-7">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Primary Configuration</h6>
                                <div class="row g-3">
                                    <forms-select-field v-model="item.earning_type_id" name="earning_type_id" label="Earning Category" :options="types" classes="col-md-6"></forms-select-field>
                                    <forms-text-field v-if="item.earning_type_id == 0" v-model="item.custom_type" name="custom_type" label="Specify Custom Category" classes="col-md-6"></forms-text-field>
                                    <forms-text-field v-model="item.name" name="name" label="Earning Display Name" classes="col-md-6"></forms-text-field>
                                    <forms-text-field v-model="item.name_in_payslip" name="name_in_payslip" label="Pay-slip Label" classes="col-md-6"></forms-text-field>
                                    
                                    <div class="col-12 mt-4">
                                        <div class="p-3 bg-light rounded-3 border border-dashed">
                                            <div class="row g-3">
                                                <forms-radio-field v-model="item.pay_time" name="pay_time" label="Payment Frequency" classes="col-md-6" :options="[
                                                    {key: 'Fixed (Every Month)', val: 'Fixed'},
                                                    {key: 'Variable (Dynamic)', val: 'Variable'},
                                                ]"></forms-radio-field>

                                                <forms-radio-field v-model="item.calculation" name="calculation" label="Calculation Logic" classes="col-md-6" :options="[
                                                    {key: 'Flat Amount', val: 'Flat'},
                                                    {key: 'Percentage (%)', val: 'Basic'},
                                                ]"></forms-radio-field>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mt-4">
                                        <label class="form-label fw-bold small text-uppercase">{{ item.calculation == 'Flat' ? 'Monthly Amount' : 'Percentage Value' }}</label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-white">{{ item.calculation == 'Flat' ? '₹' : '%' }}</span>
                                            <input type="number" v-model="item.value" class="form-control border-start-0" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Policy & Statutory Rules</h6>
                                <div class="row g-2">
                                    <div v-for="rule in policyRules" :key="rule.key" class="col-6">
                                        <div class="form-check form-switch p-2 ps-5 rounded-3 hover-bg-light transition-all border mb-1">
                                            <input class="form-check-input" type="checkbox" v-model="item[rule.key]" :id="'check' + rule.key">
                                            <label class="form-check-label small fw-medium" :for="'check' + rule.key">{{ rule.label }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="item.id">
                                <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill" @click="deleteItem()">
                                    <i class="bi bi-trash3 me-2"></i> Delete
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                                    Confirm Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill" @click="toggleForm">Cancel</button>
                                <forms-submit-button name="" v-model="loading" :label="item.id ? 'Apply Changes' : 'Create Earning'" @click="save()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Search & Grid Section -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white p-4 border-0">
                <div class="row g-3 align-items-center">
                    <div class="col">
                        <h5 class="fw-bold mb-0">Components Library</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" v-model="params.value" placeholder="Search components..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold text-muted">
                            <th class="ps-4 py-3">ID</th>
                            <th>Earning Component Details</th>
                            <th>Frequency</th>
                            <th>Calculation</th>
                            <th class="text-center">Value</th>
                            <th class="text-center">Taxable</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in items" :key="row.id" class="cursor-pointer transition-all" @click="edit(row)">
                            <td class="ps-4">
                                <span class="text-muted fw-mono small">#{{ row.id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-box-sm me-3 bg-soft-primary text-primary rounded-3">
                                        <i class="bi bi-wallet2"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ row.name }}</div>
                                        <div class="text-muted small">{{ row.name_in_payslip }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span :class="row.pay_time === 'Fixed' ? 'badge-soft-success' : 'badge-soft-info'" class="badge rounded-pill px-3 py-2">
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
                                <i :class="row.is_taxable ? 'bi bi-check-circle-fill text-success' : 'bi bi-dash-circle text-muted'" class="fs-5"></i>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-light btn-icon rounded-circle shadow-sm" @click.stop="edit(row)">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <p class="mt-3 text-muted">No earning components found.</p>
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
                    Load More Components
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
                earning_type_id: null,
                custom_type: null,
                name: null,
                name_in_payslip: null,
                calculation: 'Flat',
                pay_time: 'Fixed',
                value: 0,
                is_fbp: false,
                is_fbp_restricted: false,
                is_active: true,
                is_ctc: true,
                is_taxable: true,
                is_pro_rata: true,
                is_compensable: false,
                is_basic_pay: false,
                is_fullepf: false,
                is_gross_pay: true,
                is_in_payslip: true,
            },
            policyRules: [
                { key: 'is_active', label: 'Component is Active' },
                { key: 'is_taxable', label: 'Earning is Taxable' },
                { key: 'is_ctc', label: 'Part of CTC' },
                { key: 'is_in_payslip', label: 'Show in Payslip' },
                { key: 'is_pro_rata', label: 'Pro-rata Basis' },
                { key: 'is_basic_pay', label: 'Basic Pay Rule' },
                { key: 'is_gross_pay', label: 'Gross Pay Rule' },
                { key: 'is_compensable', label: 'Compensable Earning' }
            ],
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
            searchTimer: null,
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
            this.item.earning_type_id = null;
            this.item.custom_type = null;
            this.item.name = null;
            this.item.name_in_payslip = null;
            this.item.calculation = 'Flat';
            this.item.pay_time = 'Fixed';
            this.item.value = 0;
            this.item.is_fbp = false;
            this.item.is_fbp_restricted = null;
            this.item.is_active = true;
            this.item.is_ctc = true;
            this.item.is_taxable = true;
            this.item.is_pro_rata = true;
            this.item.is_compensable = false;
            this.item.is_basic_pay = false;
            this.item.is_fullepf = false;
            this.item.is_gross_pay = true;
            this.item.is_in_payslip = true;
            this.isDelete = false;
        },

        edit(item){
            this.item.id = item.id;
            this.item.earning_type_id = item.earning_type_id;
            this.item.custom_type = item.custom_type;
            this.item.name = item.name;
            this.item.name_in_payslip = item.name_in_payslip;
            this.item.calculation = item.calculation;
            this.item.pay_time = item.pay_time;
            this.item.value = item.value;
            this.item.is_fbp = item.is_fbp;
            this.item.is_fbp_restricted = item.is_fbp_restricted;
            this.item.is_active = item.is_active;
            this.item.is_ctc = item.is_ctc;
            this.item.is_taxable = item.is_taxable;
            this.item.is_pro_rata = item.is_pro_rata;
            this.item.is_compensable = item.is_compensable;
            this.item.is_basic_pay = item.is_basic_pay;
            this.item.is_fullepf = item.is_fullepf;
            this.item.is_gross_pay = item.is_gross_pay;
            this.item.is_in_payslip = item.is_in_payslip;
            this.isForm = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        fetch(){
            let url = '/salary_settings/earnings/fetch';
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
            axios.post('/salary_settings/earnings/add', this.item).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            axios.post('/salary_settings/earnings/update', this.item).then(res => {
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
            axios.post('/salary_settings/earnings/delete', this.item).then(res => {
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
            key: 'Custom Earning Type',
            val: 0
        });

        this.fetch();
    },

}
</script>

<style scoped>
.earnings-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.card {
    transition: all 0.3s ease;
}

.icon-box-sm {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.bg-soft-primary { background-color: #eef2ff; }
.text-primary { color: #6366f1 !important; }

.badge-soft-success { background-color: #ecfdf5; color: #059669; }
.badge-soft-info { background-color: #f0f9ff; color: #0ea5e9; }

.hover-bg-light:hover {
    background-color: #f8fafc;
}

.btn-icon {
    width: 36px;
    height: 36px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.transition-all {
    transition: all 0.2s ease-in-out;
}

.cursor-pointer {
    cursor: pointer;
}

.fw-mono {
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

/* Form Animations */
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
</style>