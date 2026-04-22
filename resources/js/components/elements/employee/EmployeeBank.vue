<template>
    <div class="bank-details-suite">
        <div class="row g-4">
            <!-- Entry Card -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Remittance Credentials</h6>
                    </div>
                    <div class="card-body p-4 bg-light-subtle">
                        <div class="row g-3">
                            <forms-text-field name="account_name" label="Account Holder Name" v-model="item.account_name" placeholder="Full legal name as per bank" classes="col-12"></forms-text-field>
                            
                            <forms-text-field name="account_number" label="Account Number" v-model="item.account_number" placeholder="000000000000" classes="col-12"></forms-text-field>
                            
                            <forms-select-field name="account_type" label="Account Type" v-model="item.account_type" :options="[
                                {key: 'Salary Account', val: 'Salary'},
                                {key: 'Savings Account', val: 'Savings'},
                                {key: 'Current Account', val: 'Current'},
                                {key: 'NRI Account', val: 'NRI'}
                            ]" classes="col-12"></forms-select-field>

                            <div class="col-12">
                                <div class="row g-3">
                                    <forms-text-field name="bank_name" label="Financial Institution" v-model="item.bank_name" placeholder="e.g. HDFC Bank" classes="col-12 col-md-6"></forms-text-field>
                                    <forms-text-field name="ifsc" label="IFSC Code" v-model="item.ifsc" placeholder="ABCD0123456" classes="col-12 col-md-6"></forms-text-field>
                                    <forms-text-field name="branch" label="Branch Location" v-model="item.branch" placeholder="e.g. Downtown Branch" classes="col-12"></forms-text-field>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div v-if="item.id">
                                    <button v-if="!isDelete" class="btn btn-outline-danger btn-sm rounded-pill px-3" @click="isDelete = true">
                                        Archive Details
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill px-3 animate-pulse" @click="deleteNow()">
                                        Confirm
                                    </button>
                                </div>
                                <div v-else></div>
                                
                                <forms-submit-button v-model="loading" :label="item.id ? 'Save Changes' : 'Register Account'" @click="save()" classes="px-4 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Record Section -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Financial Settlement Registry</h6>
                        <span class="badge bg-soft-success text-success px-3 rounded-pill">Verified Records</span>
                    </div>

                    <div class="p-4" v-if="items.length === 0">
                        <div class="text-center opacity-25 py-5">
                            <i class="bi bi-bank2 display-1"></i>
                            <p class="mt-2 fw-bold">No bank accounts linked</p>
                        </div>
                    </div>

                    <div class="bank-ledger p-4" v-else>
                        <div v-for="row in items" :key="row.id" class="bank-card border rounded-4 p-4 mb-3 transition-all hover-glow shadow-sm bg-white" @click="edit(row)">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="bank-avatar bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="bi bi-credit-card-2-front fs-4"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="fw-900 mb-0 text-dark">{{ row.account_name }}</h6>
                                        <span class="badge rounded-pill fw-bold" :class="row.account_type === 'Salary' ? 'bg-primary' : 'bg-light text-muted border'">{{ row.account_type }}</span>
                                    </div>
                                    <div class="fw-bold text-muted small mb-2 fw-mono fs-6">{{ row.account_number }}</div>
                                    <div class="d-flex gap-4 text-muted small fw-semibold">
                                        <span><i class="bi bi-bank me-1"></i> {{ row.bank_name }}</span>
                                        <span><i class="bi bi-geo-alt me-1"></i> {{ row.branch }}</span>
                                        <span class="text-uppercase"><i class="bi bi-code-square me-1"></i> {{ row.ifsc }}</span>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-link text-primary p-0 shadow-none"><i class="bi bi-pencil-square fs-4"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {
    props: ['employee_id'],
    data(){
        return {
            loading: false,
            isDelete: false,
            item: {
                id: null, employee_id: null, account_name: null, account_number: null,
                account_type: null, bank_name: null, branch: null, ifsc: null,
            },
            items: [],
            params: { key: null, value: null, by: 'id', order: 'desc', rows: 10 }
        };
    },
    methods: {
        fetch(){
            axios.get('/employee/employee_bank/'+this.item.employee_id+'/fetch/', {params: this.params}).then(res => {
                this.items = res.data.data;
                this.loading = false;
            });
        },
        save(){
            this.loading = true;
            let url = this.item.id ? '/employee/employee_bank/update' : '/employee/employee_bank/add';
            axios.post(url, this.item).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        reset(){
            Object.keys(this.item).forEach(key => this.item[key] = (key === 'employee_id' ? this.employee_id : null));
            this.isDelete = false;
        },
        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_bank/delete', this.item).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        edit(item){
            Object.keys(this.item).forEach(key => this.item[key] = item[key]);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    },
    created(){
        this.item.employee_id = this.employee_id;
        this.fetch();
    },
}
</script>

<style scoped>
.bank-details-suite { padding: 1rem 0; }
.bank-card { border-color: #f1f5f9; cursor: pointer; }
.bank-card:hover { border-color: #6366f1; transform: scale(1.01); }
.bg-soft-primary { background-color: #eef2ff; }
.bg-soft-success { background-color: #f0fdf4; }
.fw-900 { font-weight: 900; }
.fw-mono { font-family: ui-monospace, SFMono-Regular, monospace; }
.transition-all { transition: all 0.2s ease; }
.animate-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>