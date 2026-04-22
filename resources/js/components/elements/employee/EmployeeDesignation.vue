<template>
    <div class="designation-suite">
        <div class="row g-4">
            <!-- Assignment Section -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Role Appointment</h6>
                    </div>
                    <div class="card-body p-4 bg-light-subtle">
                        <div class="row g-3">
                            <forms-select-field name="designation_id" label="Professional Title" v-model="employee_designation.designation_id" :options="designations" classes="col-12"></forms-select-field>
                            
                            <div class="col-12">
                                <div class="row g-3">
                                    <forms-date-field name="from" label="Appointment Start" v-model="employee_designation.from" classes="col-6"></forms-date-field>
                                    <forms-date-field name="to" label="Termination/Promotion" v-model="employee_designation.to" classes="col-6"></forms-date-field>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div v-if="employee_designation.id">
                                    <button v-if="!isDelete" class="btn btn-outline-danger btn-sm rounded-pill px-3" @click="isDelete = true">
                                        End Promotion
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill px-3 animate-pulse" @click="deleteNow()">
                                        Confirm
                                    </button>
                                </div>
                                <div v-else></div>
                                
                                <forms-submit-button v-model="loading" :label="employee_designation.id ? 'Modify Tenure' : 'Appoint Role'" @click="save()" classes="px-4 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promotion Track Section -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Professional Trajectory</h6>
                        <span class="badge bg-soft-warning text-warning px-3 rounded-pill">Career Path</span>
                    </div>

                    <div class="p-4" v-if="employee_designations.length === 0">
                        <div class="text-center opacity-25 py-5">
                            <i class="bi bi-award-fill display-1"></i>
                            <p class="mt-2 fw-bold">No historical role appointments</p>
                        </div>
                    </div>

                    <div class="table-responsive" v-else>
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr class="text-uppercase small fw-800 text-muted">
                                    <th class="ps-4 py-3">Official Designation</th>
                                    <th>Tenure Period</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="wl in employee_designations" :key="wl.id" class="transition-all hover-glow border-bottom border-light" @click="edit(wl)">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="role-icon me-3 bg-soft-primary text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person-workspace"></i>
                                            </div>
                                            <div class="fw-bold text-dark">{{ getDesignations(wl.designation_id) }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2 small fw-bold text-muted">
                                            <span class="fw-mono">{{ wl.from }}</span>
                                            <span class="text-light opacity-50">/</span>
                                            <span class="fw-mono">{{ wl.to || 'Ongoing' }}</span>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-white border shadow-sm rounded-circle p-2" @click.stop="edit(wl)">
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
    props: ['employee_id', 'designations'],
    data(){
        return {
            loading: false,
            isDelete: false,
            employee_designation: {
                employee_id: null, id: null, designation_id: null, from: null, to: null,
            },
            employee_designations: [],
            params: { key: null, value: null, by: 'id', order: 'desc', rows: 10 }
        };
    },
    methods: {
        fetch(){
            axios.get('/employee/employee_designation/' + this.employee_id + '/fetch', {params: this.params}).then(res => {
                this.employee_designations = res.data.data;
                this.loading = false;
            });
        },
        save(){
            this.loading = true;
            let url = this.employee_designation.id ? '/employee/employee_designation/update' : '/employee/employee_designation/add';
            axios.post(url, this.employee_designation).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        reset(){
            Object.keys(this.employee_designation).forEach(key => this.employee_designation[key] = (key === 'employee_id' ? this.employee_id : null));
            this.isDelete = false;
        },
        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_designation/delete', this.employee_designation).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        edit(item){
            Object.keys(this.employee_designation).forEach(key => this.employee_designation[key] = item[key]);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        getDesignations(id){
            let des = this.designations.find(d => d.val == id);
            return des ? des.key : 'Unknown Role';
        }
    },
    created(){
        this.fetch();
        this.employee_designation.employee_id = this.employee_id;
    },
}
</script>

<style scoped>
.designation-suite { padding: 1rem 0; }
.bg-soft-primary { background-color: #eef2ff; }
.bg-soft-warning { background-color: #fffbeb; }
.transition-all { transition: all 0.2s ease; }
.hover-glow:hover { background-color: #f8fafc; cursor: pointer; }
.fw-800 { font-weight: 800; }
.fw-mono { font-family: ui-monospace, SFMono-Regular, monospace; }
.animate-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>