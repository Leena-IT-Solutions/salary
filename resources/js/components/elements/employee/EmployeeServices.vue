<template>
    <div class="services-provision-suite">
        <div class="row g-4">
            <!-- Provisioning Section -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Provision Facility</h6>
                    </div>
                    <div class="card-body p-4 bg-light-subtle">
                        <div class="row g-3">
                            <forms-select-field name="services_component_id" label="Service Catalog Item" v-model="item.services_component_id" :options="services" classes="col-12"></forms-select-field>
                            
                            <div class="col-12">
                                <div class="row g-3">
                                    <forms-date-field name="from" label="Start Provisioning" v-model="item.from" classes="col-6"></forms-date-field>
                                    <forms-date-field name="to" label="Expiry/Cessation" v-model="item.to" classes="col-6"></forms-date-field>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div v-if="item.id">
                                    <button v-if="!isDelete" class="btn btn-outline-danger btn-sm rounded-pill px-3" @click="isDelete = true">
                                        Terminate Service
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill px-3 animate-pulse" @click="deleteNow()">
                                        Confirm
                                    </button>
                                </div>
                                <div v-else></div>
                                
                                <forms-submit-button v-model="loading" :label="item.id ? 'Modify Tenure' : 'Activate Service'" @click="save()" classes="px-4 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Inventory Section -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Active Provisions Registry</h6>
                        <div class="input-group input-group-sm rounded-pill bg-light border-0 px-2" style="width: 200px;">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-transparent border-0 shadow-none ps-0" placeholder="Filter Provisions..." v-model="params.value" @keyup.enter="search()">
                        </div>
                    </div>

                    <div class="p-4" v-if="items.length === 0">
                        <div class="text-center opacity-25 py-5">
                            <i class="bi bi-gear-wide-connected display-1"></i>
                            <p class="mt-2 fw-bold">No facilities actively provisioned</p>
                        </div>
                    </div>

                    <div class="table-responsive" v-else>
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr class="text-uppercase tiny fw-800 text-muted">
                                    <th class="ps-4 py-3">Service Entity</th>
                                    <th>Provision Period</th>
                                    <th class="pe-4 text-end">Management</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="row in items" :key="row.id" class="transition-all hover-glow border-bottom border-light" @click="edit(row)">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="service-icon me-3 bg-soft-info text-info rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-lightning-charge"></i>
                                            </div>
                                            <div class="fw-bold text-dark">{{ row.services_component?.name || 'Assigned Service' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 small fw-bold text-muted">
                                            <span class="fw-mono">{{ row.from }}</span>
                                            <i class="bi bi-arrow-right opacity-25"></i>
                                            <span class="fw-mono">{{ row.to || 'Active' }}</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-white border shadow-sm rounded-circle p-2" @click.stop="edit(row)">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
export default {
    props: ['employee_id', 'services'],
    data(){
        return {
            loading: false,
            isDelete: false,
            item: {
                id: null, employee_id: null, services_component_id: null, from: null, to: null,
            },
            items: [],
            params: { key: null, value: null, by: 'id', order: 'desc', rows: 10 }
        };
    },
    methods: {
        reset(){
            Object.keys(this.item).forEach(key => this.item[key] = (key === 'employee_id' ? this.employee_id : null));
            this.isDelete = false;
        },
        edit(item){
            Object.keys(this.item).forEach(key => this.item[key] = item[key]);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        fetch(){
            axios.get('/employee/employee_services/'+this.item.employee_id+'/fetch/', {params: this.params}).then(res => {
                this.items = res.data.data;
                this.loading = false;
            });
        },
        search(){ this.fetch(); },
        save(){
            this.loading = true;
            let url = this.item.id ? '/employee/employee_services/update' : '/employee/employee_services/add';
            axios.post(url, this.item).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        deleteItem(){ this.isDelete = true; },
        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_services/delete', this.item).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
    },
    created(){
        this.item.employee_id = this.employee_id;
        this.fetch();
    },
}
</script>

<style scoped>
.services-provision-suite { padding: 1rem 0; }
.bg-soft-info { background-color: #f0f9ff; }
.transition-all { transition: all 0.2s ease; }
.hover-glow:hover { background-color: #f8fafc; cursor: pointer; }
.fw-900 { font-weight: 900; }
.fw-800 { font-weight: 800; }
.fw-mono { font-family: ui-monospace, SFMono-Regular, monospace; }
.tiny { font-size: 0.7rem; }
.animate-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>