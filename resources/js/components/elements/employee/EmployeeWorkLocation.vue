<template>
    <div class="work-location-suite">
        <div class="row g-4">
            <!-- Assignment Section -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Site Assignment</h6>
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
                            <forms-select-field name="work_location_id" label="Designated Site" v-model="employee_work_location.work_location_id" :error="errors.work_location_id ? errors.work_location_id[0] : ''" :options="locations" classes="col-12"></forms-select-field>
                            
                            <div class="col-12">
                                <div class="row g-3">
                                    <forms-date-field name="from" label="Assignment Start" v-model="employee_work_location.from" :error="errors.from ? errors.from[0] : ''" classes="col-6"></forms-date-field>
                                    <forms-date-field name="to" label="Anticipated End" v-model="employee_work_location.to" :error="errors.to ? errors.to[0] : ''" classes="col-6"></forms-date-field>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div v-if="employee_work_location.id">
                                    <button v-if="!isDelete" class="btn btn-outline-danger btn-sm rounded-pill px-3" @click="isDelete = true">
                                        End Assignment
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill px-3 animate-pulse" @click="deleteNow()">
                                        Confirm
                                    </button>
                                </div>
                                <div v-else></div>
                                
                                <div class="d-flex gap-2">
                                    <button v-if="employee_work_location.id" class="btn btn-light btn-sm rounded-pill px-3" @click="reset()">Cancel</button>
                                    <forms-submit-button v-model="loading" :label="employee_work_location.id ? 'Update Placement' : 'Assign Site'" @click="save()" classes="px-4 shadow-sm"></forms-submit-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline Section -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Assignment Chronology</h6>
                        <span class="badge bg-soft-success text-success px-3 rounded-pill">Active Record</span>
                    </div>

                    <div class="p-4" v-if="employee_work_locations.length === 0">
                        <div class="text-center opacity-25 py-5">
                            <i class="bi bi-geo-fill display-1"></i>
                            <p class="mt-2 fw-bold">No historical site assignments</p>
                        </div>
                    </div>

                    <div class="table-responsive" v-else>
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr class="text-uppercase small fw-800 text-muted">
                                    <th class="ps-4 py-3">Assigned Site</th>
                                    <th>Duration Timeline</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="wl in employee_work_locations" :key="wl.id" class="transition-all hover-glow border-bottom border-light" @click="edit(wl)">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="site-icon me-3 bg-soft-primary text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <div class="fw-bold text-dark">{{ getWL(wl.work_location_id) }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-light text-muted border px-2 py-1 small fw-mono">{{ wl.from }}</span>
                                            <i class="bi bi-arrow-right text-muted small"></i>
                                            <span class="badge bg-light text-muted border px-2 py-1 small fw-mono">{{ wl.to || 'Present' }}</span>
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
    props: ['employee_id', 'locations'],
    data(){
        return {
            loading: false,
            isDelete: false,
            employee_work_location: {
                employee_id: null, id: null, work_location_id: null, from: null, to: null,
            },
            errors: {},
            employee_work_locations: [],
            params: { key: null, value: null, by: 'id', order: 'desc', rows: 10 }
        };
    },
    methods: {
        fetch(){
            axios.get('/employee/employee_work_location/' + this.employee_id + '/fetch', {params: this.params}).then(res => {
                this.employee_work_locations = res.data.data;
                this.loading = false;
            });
        },
        save(){
            this.loading = true;
            this.errors = {};
            let url = this.employee_work_location.id ? '/employee/employee_work_location/update' : '/employee/employee_work_location/add';
            axios.post(url, this.employee_work_location).then(res => {
                this.reset();
                this.fetch();
            }).catch(err => {
                if (err.response && err.response.status === 422) {
                    this.errors = err.response.data.errors;
                }
            }).finally(() => this.loading = false);
        },
        reset(){
            Object.keys(this.employee_work_location).forEach(key => this.employee_work_location[key] = (key === 'employee_id' ? this.employee_id : null));
            this.errors = {};
            this.isDelete = false;
        },
        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_work_location/delete', this.employee_work_location).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        edit(item){
            this.errors = {};
            Object.keys(this.employee_work_location).forEach(key => this.employee_work_location[key] = item[key]);
        },
        getWL(id){
            let loc = this.locations.find(l => l.val == id);
            return loc ? loc.key : 'Unknown Site';
        }
    },
    created(){
        this.fetch();
        this.employee_work_location.employee_id = this.employee_id;
    },
}
</script>

<style scoped>
.work-location-suite { padding: 1rem 0; }
.bg-soft-primary { background-color: #eef2ff; }
.bg-soft-success { background-color: #f0fdf4; }
.transition-all { transition: all 0.2s ease; }
.hover-glow:hover { background-color: #f8fafc; cursor: pointer; }
.fw-800 { font-weight: 800; }
.fw-mono { font-family: ui-monospace, SFMono-Regular, monospace; }
.animate-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>