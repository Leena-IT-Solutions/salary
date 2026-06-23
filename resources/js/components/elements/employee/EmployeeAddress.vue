<template>
    <div class="residence-suite">
        <div class="row g-4">
            <!-- Entry Section -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Residential Parameters</h6>
                    </div>
                    <div class="card-body p-4 bg-light-subtle">
                        <!-- General Error Alert -->
                        <div v-if="Object.keys(errors).length > 0" class="alert alert-danger rounded-4 mb-4 border-0 shadow-sm d-flex align-items-center gap-3">
                            <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                            <div>
                                <div class="fw-bold text-danger">Validation Failed</div>
                                <div class="small text-danger opacity-75">Please correct the errors in the highlighted fields below.</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <forms-text-field name="address" label="Street & Landmark" v-model="employee_address.address" :error="errors.address ? errors.address[0] : ''" placeholder="B-12, Green View Apts..." classes="col-12"></forms-text-field>
                            <forms-text-field name="city" label="City" v-model="employee_address.city" :error="errors.city ? errors.city[0] : ''" placeholder="e.g. Mumbai" classes="col-6"></forms-text-field>
                            <forms-text-field name="pincode" label="Pincode" v-model="employee_address.pincode" :error="errors.pincode ? errors.pincode[0] : ''" placeholder="000000" classes="col-6"></forms-text-field>
                            <forms-text-field name="state" label="State/Region" v-model="employee_address.state" :error="errors.state ? errors.state[0] : ''" placeholder="e.g. Maharashtra" classes="col-6"></forms-text-field>
                            <forms-text-field name="country" label="Country" v-model="employee_address.country" :error="errors.country ? errors.country[0] : ''" placeholder="e.g. India" classes="col-6"></forms-text-field>
                            
                            <div class="col-12 d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div v-if="employee_address.id">
                                    <button v-if="!isDelete" class="btn btn-outline-danger btn-sm rounded-pill px-3" @click="isDelete = true">
                                        Remove Address
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill px-3 animate-pulse" @click="deleteNow()">
                                        Confirm
                                    </button>
                                </div>
                                <div v-else></div>
                                
                                <div class="d-flex gap-2">
                                    <button v-if="employee_address.id" class="btn btn-light btn-sm rounded-pill px-3" @click="reset()">Cancel</button>
                                    <forms-submit-button name="" v-model="loading" :label="employee_address.id ? 'Save Changes' : 'Register Address'" @click="save()" classes="px-4 shadow-sm"></forms-submit-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Section -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Address Repository</h6>
                    </div>
                    
                    <div class="p-4" v-if="employee_addresses.length === 0">
                        <div class="text-center opacity-25 py-5">
                            <i class="bi bi-geo-alt display-1"></i>
                            <p class="mt-2 fw-bold">No residence records registered</p>
                        </div>
                    </div>

                    <div class="address-grid p-4" v-else>
                        <div v-for="address in employee_addresses" :key="address.id" class="address-card border rounded-4 p-3 mb-3 transition-all hover-shadow" @click="edit(address)">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex gap-3">
                                    <div class="bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                        <i class="bi bi-house-door-fill"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ address.address }}</div>
                                        <div class="text-muted small">
                                            {{ address.city }}, {{ address.state }} {{ address.pincode }}
                                        </div>
                                        <div class="badge bg-light text-muted border rounded-pill mt-1">{{ address.country }}</div>
                                    </div>
                                </div>
                                <button class="btn btn-link text-primary p-0 shadow-none"><i class="bi bi-pencil-square fs-5"></i></button>
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
            employee_address: {
                employee_id: null, id: null, address: null, city: null, pincode: null, state: null, country: null,
            },
            errors: {},
            employee_addresses: [],
            params: { key: null, value: null, by: 'id', order: 'desc', rows: 10 }
        };
    },
    methods: {
        fetch(){
            axios.get('/employee/employee_address/' + this.employee_id + '/fetch', {params: this.params}).then(res => {
                this.employee_addresses = res.data.data;
                this.loading = false;
            });
        },
        save(){
            this.loading = true;
            this.errors = {};
            let url = this.employee_address.id ? '/employee/employee_address/update' : '/employee/employee_address/add';
            axios.post(url, this.employee_address).then(res => {
                this.reset();
                this.fetch();
            }).catch(err => {
                if (err.response && err.response.status === 422) {
                    this.errors = err.response.data.errors;
                }
            }).finally(() => this.loading = false);
        },
        reset(){
            Object.keys(this.employee_address).forEach(key => this.employee_address[key] = (key === 'employee_id' ? this.employee_id : null));
            this.errors = {};
            this.isDelete = false;
        },
        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_address/delete', this.employee_address).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        edit(item){
            this.errors = {};
            Object.keys(this.employee_address).forEach(key => this.employee_address[key] = item[key]);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    },
    created(){
        this.fetch();
        this.employee_address.employee_id = this.employee_id;
    },
}
</script>

<style scoped>
.residence-suite { padding: 1rem 0; }
.address-card { cursor: pointer; border-color: #f1f5f9; transition: all 0.2s ease; }
.address-card:hover { border-color: #6366f1; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.08); }
.bg-soft-primary { background-color: #eef2ff; }
.animate-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>