<template>
    <div class="document-vault-suite">
        <div class="row g-4">
            <!-- Upload Section -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Upload New Credential</h6>
                    </div>
                    <div class="card-body p-4 bg-light-subtle">
                        <div class="row g-3">
                            <forms-text-field name="document_name" label="Document Title" v-model="employee_document.document_name" placeholder="e.g. Master's Degree Certificate" classes="col-12"></forms-text-field>
                            
                            <forms-file-field @change="getImageObject($event)" v-model="employee_document.photo" name="document" label="Electronic Copy" error="" classes="col-12 custom-file-modern"></forms-file-field>
                            
                            <div class="col-12 mt-4 p-3 bg-white rounded-4 border border-info border-opacity-10">
                                <p class="text-info small mb-0 fw-semibold">
                                    <i class="bi bi-info-circle-fill me-2"></i> Accepted formats: PDF, PNG, JPG (Max 5MB).
                                </p>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div v-if="employee_document.id">
                                    <button v-if="!isDelete" class="btn btn-outline-danger btn-sm rounded-pill px-3" @click="isDelete = true">
                                        Delete Asset
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill px-3 animate-pulse" @click="deleteNow()">
                                        Confirm
                                    </button>
                                </div>
                                <div v-else></div>
                                
                                <forms-submit-button name="" v-model="loading" :label="employee_document.id ? 'Save Changes' : 'Vault Document'" @click="save()" classes="px-4 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Archive Section -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Compliance Vault Inventory</h6>
                        <div class="input-group input-group-sm rounded-pill bg-light border-0 px-2" style="width: 200px;">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control bg-transparent border-0 shadow-none ps-0" v-model="params.value" placeholder="Filter vault..." @keyup.enter="search()">
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr class="text-uppercase small fw-800 text-muted">
                                    <th class="ps-4 py-3" style="width: 80px;">S.No</th>
                                    <th>Document Profile</th>
                                    <th class="pe-4 text-end">Management</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(wl, index) in employee_documents" :key="wl.id" class="transition-all hover-glow border-bottom border-light" @click="edit(wl)">
                                    <td class="ps-4">
                                        <span class="text-muted small">#{{ wl.id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="doc-icon-container me-3 bg-soft-info text-info rounded-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                <i class="bi bi-file-earmark-pdf fs-4" v-if="wl.document.endsWith('.pdf')"></i>
                                                <i class="bi bi-file-earmark-image fs-4" v-else></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-0">{{ wl.document_name }}</div>
                                                <a :href="'/storage' + wl.document" target="_blank" class="text-primary tiny fw-bold text-decoration-none" @click.stop>
                                                    <i class="bi bi-eye me-1"></i> Preview Asset
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                            <button class="btn btn-white btn-sm px-3" title="Edit Meta" @click.stop="edit(wl)"><i class="bi bi-pencil-square text-primary"></i></button>
                                            <a :href="'/storage' + wl.document" target="_blank" class="btn btn-white btn-sm px-3 border-start" title="Download Source" @click.stop><i class="bi bi-download text-muted"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="employee_documents.length === 0">
                                    <td colspan="3" class="text-center py-5">
                                        <div class="opacity-25 py-4">
                                            <i class="bi bi-folder-x display-1"></i>
                                            <p class="mt-2 fw-bold">No credentials archived</p>
                                        </div>
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
    props: ['employee_id'],
    data(){
        return {
            loading: false,
            isDelete: false,
            employee_document: {
                employee_id: null,
                id: null,
                photo: null,
                document: {},
                document_name: null,
            },
            employee_documents: [],
            params: { key: null, value: null, by: 'id', order: 'desc', rows: 10 }
        };
    },
    methods: {
        getImageObject(e){
            this.employee_document.document = e.target.files[0];
        },
        fetch(){
            axios.get('/employee/employee_document/' + this.employee_id + '/fetch', {params: this.params}).then(res => {
                this.employee_documents = res.data.data;
                this.loading = false;
            });
        },
        search(){
            this.fetch();
        },
        save(){
            this.loading = true;
            let fd = new FormData();
            if (this.employee_document.id) fd.append('id', this.employee_document.id);
            fd.append('document_name', this.employee_document.document_name);
            fd.append('document', this.employee_document.document);
            fd.append('employee_id', this.employee_id);

            let url = this.employee_document.id ? '/employee/employee_document/update' : '/employee/employee_document/add';
            axios.post(url, fd).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        reset(){
            this.employee_document.id = null;
            this.employee_document.document_name = null;
            this.employee_document.document = null;
            this.isDelete = false;
        },
        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_document/delete', this.employee_document).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        edit(item){
            this.employee_document.id = item.id;
            this.employee_document.document_name = item.document_name;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    },
    created(){
        this.fetch();
    },
}
</script>

<style scoped>
.document-vault-suite { padding: 1rem 0; }
.doc-icon-container { transition: all 0.3s ease; }
.bg-soft-info { background-color: #f0f9ff; color: #0ea5e9; }
.transition-all { transition: all 0.2s ease; }
.hover-glow:hover { background-color: #f8fafc; cursor: pointer; }
.hover-glow:hover .doc-icon-container { background-color: #e0f2fe; }
.tiny { font-size: 0.7rem; }
.fw-800 { font-weight: 800; }
.animate-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>