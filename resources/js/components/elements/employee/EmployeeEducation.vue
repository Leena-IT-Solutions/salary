<template>
    <div class="education-vault-suite">
        <div class="row g-4">
            <!-- Upload Section -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark">{{ employee_education.id ? 'Edit Educational Record' : 'Upload Educational Credential' }}</h6>
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
                            <forms-text-field name="course" label="Course / Degree" v-model="employee_education.course" :error="errors.course ? errors.course[0] : ''" placeholder="e.g. B.Tech Computer Science" classes="col-12"></forms-text-field>
                            
                            <forms-text-field name="board_university" label="Board / University" v-model="employee_education.board_university" :error="errors.board_university ? errors.board_university[0] : ''" placeholder="e.g. Stanford University" classes="col-12"></forms-text-field>
                            
                            <forms-text-field name="year" label="Passing Year" v-model="employee_education.year" :error="errors.year ? errors.year[0] : ''" placeholder="e.g. 2024" classes="col-12"></forms-text-field>
                            
                            <forms-select-field name="result" label="Result" v-model="employee_education.result" :error="errors.result ? errors.result[0] : ''" :options="[{key: 'Pass', val: 'Pass'}, {key: 'Fail', val: 'Fail'}]" classes="col-12"></forms-select-field>
                            
                            <forms-text-field name="aggregate" label="Aggregate (Percentage/CGPA)" v-model="employee_education.aggregate" :error="errors.aggregate ? errors.aggregate[0] : ''" placeholder="e.g. 85% or 8.5 CGPA" classes="col-12"></forms-text-field>

                            <forms-file-field @change="getImageObject($event)" v-model="employee_education.document_file" name="document" label="Educational Document File" :error="errors.document ? errors.document[0] : ''" classes="col-12 custom-file-modern"></forms-file-field>
                            
                            <div class="col-12 mt-4 p-3 bg-white rounded-4 border border-info border-opacity-10">
                                <p class="text-info small mb-0 fw-semibold">
                                    <i class="bi bi-info-circle-fill me-2"></i> Accepted formats: PDF, PNG, JPG (Max 5MB).
                                </p>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div v-if="employee_education.id">
                                    <button v-if="!isDelete" class="btn btn-outline-danger btn-sm rounded-pill px-3" @click="isDelete = true">
                                        Delete Record
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill px-3 animate-pulse" @click="deleteNow()">
                                        Confirm Delete
                                    </button>
                                </div>
                                <div v-else></div>
                                
                                <div class="d-flex gap-2">
                                    <button v-if="employee_education.id" class="btn btn-light btn-sm rounded-pill px-3" @click="reset()">Cancel</button>
                                    <forms-submit-button name="" v-model="loading" :label="employee_education.id ? 'Save Changes' : 'Upload Education'" @click="save()" classes="px-4 shadow-sm"></forms-submit-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Archive Section -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Education Records Inventory</h6>
                        <div class="input-group input-group-sm rounded-pill bg-light border-0 px-2" style="width: 200px;">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-transparent border-0 shadow-none ps-0" v-model="params.value" placeholder="Filter records..." @keyup.enter="search()">
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr class="text-uppercase small fw-800 text-muted">
                                    <th class="ps-4 py-3" style="width: 80px;">S.No</th>
                                    <th>Course Details</th>
                                    <th>Academic Meta</th>
                                    <th class="pe-4 text-end">Management</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(wl, index) in employee_educations" :key="wl.id" class="transition-all hover-glow border-bottom border-light" @click="edit(wl)">
                                    <td class="ps-4">
                                        <span class="text-muted small">#{{ wl.id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="doc-icon-container me-3 bg-soft-info text-info rounded-3 d-flex align-items-center justify-content-center overflow-hidden" style="width: 45px; height: 45px; border: 1px solid #e2e8f0;">
                                                <i class="bi bi-mortarboard fs-4" v-if="wl.document.toLowerCase().endsWith('.pdf')"></i>
                                                <img v-else :src="'/storage' + wl.document" class="w-100 h-100 object-fit-cover">
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-0">{{ wl.course }}</div>
                                                <div class="small text-muted">{{ wl.board_university }}</div>
                                                <button @click.stop="openPreview(wl)" class="btn btn-link text-primary tiny fw-bold text-decoration-none p-0 border-0" style="font-size: 0.7rem;">
                                                    <i class="bi bi-eye me-1"></i> Preview File
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark">Year: {{ wl.year }}</div>
                                        <div class="small text-muted">Result: 
                                            <span :class="[wl.result === 'Pass' ? 'text-success' : 'text-danger']" class="fw-bold">{{ wl.result }}</span>
                                        </div>
                                        <div class="small text-muted">Aggregate: <span class="fw-bold text-secondary">{{ wl.aggregate }}</span></div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                            <button class="btn btn-white btn-sm px-3" title="Edit Meta" @click.stop="edit(wl)"><i class="bi bi-pencil-square text-primary"></i></button>
                                            <a :href="'/storage' + wl.document" download class="btn btn-white btn-sm px-3 border-start" title="Download Source" @click.stop><i class="bi bi-download text-muted"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="employee_educations.length === 0">
                                    <td colspan="4" class="text-center py-5">
                                        <div class="opacity-25 py-4">
                                            <i class="bi bi-folder-x display-1"></i>
                                            <p class="mt-2 fw-bold">No educational records archived</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Premium Educational Credential Preview Modal Overlay -->
        <div v-if="previewModalActive && previewData" class="modal-backdrop-custom d-flex align-items-center justify-content-center" @click="closePreview()">
            <div class="modal-dialog-custom rounded-4 shadow-lg bg-white overflow-hidden animate-zoom-in" style="max-width: 900px;" @click.stop>
                <div class="modal-header-custom py-3 px-4 bg-light d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">Educational Credential Viewer</h6>
                    <button class="btn-close shadow-none" @click="closePreview()"></button>
                </div>
                <div class="modal-body-custom p-4 bg-light-subtle">
                    <div class="row g-4">
                        <!-- Left side: Document Preview -->
                        <div class="col-12 col-md-7 d-flex align-items-center justify-content-center bg-white rounded-3 border p-2" style="min-height: 350px; max-height: 60vh; overflow: auto;">
                            <img v-if="!previewData.document.toLowerCase().endsWith('.pdf')" :src="'/storage' + previewData.document" class="img-fluid rounded shadow-sm object-fit-contain" style="max-height: 55vh;">
                            <iframe v-else :src="'/storage' + previewData.document" class="w-100 rounded shadow-sm" style="height: 55vh; border: none;"></iframe>
                        </div>

                        <!-- Right side: Credential Metadata -->
                        <div class="col-12 col-md-5 d-flex flex-column">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">
                                    <i class="bi bi-mortarboard me-2 text-primary"></i> Academic Profile
                                </h6>
                                <div class="mb-3">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Course / Degree</label>
                                    <div class="fw-bold text-dark fs-5">{{ previewData.course }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Board / University</label>
                                    <div class="fw-medium text-dark">{{ previewData.board_university }}</div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Passing Year</label>
                                        <div class="fw-bold text-dark">{{ previewData.year }}</div>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Result</label>
                                        <div>
                                            <span :class="[previewData.result === 'Pass' ? 'bg-success-subtle text-success border-success' : 'bg-danger-subtle text-danger border-danger']" class="badge border px-3 py-1.5 rounded-pill fw-bold">
                                                {{ previewData.result }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.7rem;">Aggregate Marks</label>
                                    <div class="fw-bold text-secondary fs-4">{{ previewData.aggregate }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-custom py-3 px-4 bg-white border-top d-flex justify-content-end gap-2">
                    <a :href="'/storage' + previewData.document" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-box-arrow-up-right me-2"></i> Open in New Tab
                    </a>
                    <a :href="'/storage' + previewData.document" download class="btn btn-outline-success btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-download me-2"></i> Download File
                    </a>
                    <button class="btn btn-dark btn-sm rounded-pill px-4 fw-bold" @click="closePreview()">Close</button>
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
            employee_education: {
                employee_id: null,
                id: null,
                course: null,
                board_university: null,
                year: null,
                result: 'Pass',
                aggregate: null,
                document_file: null,
                document: null,
            },
            errors: {},
            employee_educations: [],
            params: { key: null, value: null, by: 'id', order: 'desc', rows: 10 },

            // Modal states
            previewModalActive: false,
            previewData: null
        };
    },
    methods: {
        getImageObject(e){
            this.employee_education.document = e.target.files[0];
        },
        fetch(){
            axios.get('/employee/employee_education/' + this.employee_id + '/fetch', {params: this.params}).then(res => {
                this.employee_educations = res.data.data;
                this.loading = false;
            });
        },
        search(){
            this.fetch();
        },
        save(){
            this.loading = true;
            this.errors = {};
            let fd = new FormData();
            if (this.employee_education.id) fd.append('id', this.employee_education.id);
            fd.append('course', this.employee_education.course || '');
            fd.append('board_university', this.employee_education.board_university || '');
            fd.append('year', this.employee_education.year || '');
            fd.append('result', this.employee_education.result || '');
            fd.append('aggregate', this.employee_education.aggregate || '');
            if (this.employee_education.document) {
                fd.append('document', this.employee_education.document);
            }
            fd.append('employee_id', this.employee_id);

            let url = this.employee_education.id ? '/employee/employee_education/update' : '/employee/employee_education/add';
            axios.post(url, fd).then(res => {
                this.reset();
                this.fetch();
            }).catch(err => {
                if (err.response && err.response.status === 422) {
                    this.errors = err.response.data.errors;
                }
            }).finally(() => this.loading = false);
        },
        reset(){
            this.employee_education.id = null;
            this.employee_education.course = null;
            this.employee_education.board_university = null;
            this.employee_education.year = null;
            this.employee_education.result = 'Pass';
            this.employee_education.aggregate = null;
            this.employee_education.document = null;
            this.employee_education.document_file = null;
            this.errors = {};
            this.isDelete = false;
        },
        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_education/delete', this.employee_education).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        edit(item){
            this.errors = {};
            this.employee_education.id = item.id;
            this.employee_education.course = item.course;
            this.employee_education.board_university = item.board_university;
            this.employee_education.year = item.year;
            this.employee_education.result = item.result;
            this.employee_education.aggregate = item.aggregate;
            this.employee_education.document = null;
            this.employee_education.document_file = null;
        },

        // Modal methods
        openPreview(item) {
            this.previewData = item;
            this.previewModalActive = true;
        },
        closePreview() {
            this.previewModalActive = false;
            this.previewData = null;
        }
    },
    created(){
        this.fetch();
    },
}
</script>

<style scoped>
.education-vault-suite { padding: 1rem 0; }
.doc-icon-container { transition: all 0.3s ease; }
.bg-soft-info { background-color: #f0f9ff; color: #0ea5e9; }
.transition-all { transition: all 0.2s ease; }
.hover-glow:hover { background-color: #f8fafc; cursor: pointer; }
.hover-glow:hover .doc-icon-container { background-color: #e0f2fe; }
.tiny { font-size: 0.7rem; }
.fw-800 { font-weight: 800; }
.animate-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }

/* Custom Premium Preview Modal Overlay styles */
.modal-backdrop-custom {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(15, 23, 42, 0.5); /* Semi-transparent blue-gray */
    backdrop-filter: blur(10px); /* Smooth premium glassmorphism blur */
    z-index: 2000; /* Overrides other standard headers and sidebars */
    padding: 2rem;
}
.modal-dialog-custom {
    width: 100%;
    max-width: 900px;
    background: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.animate-zoom-in {
    animation: zoomIn 0.25s ease-out;
}
@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
