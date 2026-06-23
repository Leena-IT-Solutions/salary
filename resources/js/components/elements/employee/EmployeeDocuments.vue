<template>
    <div class="document-vault-suite py-3">
        <div class="row g-4">
            <!-- Card 1: Profile Photo -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden d-flex flex-column">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center">
                            <i class="bi bi-person-bounding-box text-primary me-2 fs-5"></i>
                            Profile Photo
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-light-subtle d-flex flex-column align-items-center justify-content-center text-center flex-grow-1">
                        <div class="preview-container mb-4 position-relative">
                            <div class="avatar-placeholder rounded-circle shadow-inner d-flex align-items-center justify-content-center bg-white border" style="width: 130px; height: 130px; overflow: hidden;">
                                <img v-if="profilePhoto && profilePhoto.media" :src="'/storage' + profilePhoto.media" class="w-100 h-100 object-fit-cover">
                                <i v-else class="bi bi-person display-4 text-muted opacity-25"></i>
                            </div>
                            <span v-if="profilePhoto && profilePhoto.media" class="position-absolute bottom-0 end-0 badge rounded-pill bg-success border border-white p-2">
                                <span class="visually-hidden">Active</span>
                            </span>
                        </div>
                        
                        <p class="text-muted small mb-4 px-2">Headshot used for biometric ID card and employee directory profile.</p>

                        <div class="w-100 mt-auto">
                            <!-- Upload controls -->
                            <div v-if="!profilePhoto || !profilePhoto.media">
                                <label class="btn btn-outline-primary btn-sm rounded-pill w-100 py-2 fw-bold" :disabled="loadingPhoto">
                                    <i v-if="loadingPhoto" class="spinner-border spinner-border-sm me-2"></i>
                                    <i v-else class="bi bi-upload me-2"></i>
                                    Upload Headshot
                                    <input type="file" @change="onPhotoChange($event)" class="d-none" accept="image/*">
                                </label>
                            </div>
                            <!-- Existing Photo Controls -->
                            <div v-else class="d-flex flex-column gap-2">
                                <button @click="openPreview('Profile Photo', '/storage' + profilePhoto.media, 'image')" class="btn btn-light btn-sm rounded-pill py-2 fw-bold">
                                    <i class="bi bi-eye me-2"></i> View Profile Photo
                                </button>
                                <div class="d-flex gap-2">
                                    <label class="btn btn-outline-secondary btn-sm rounded-pill flex-grow-1 py-2 fw-bold" :disabled="loadingPhoto">
                                        Replace
                                        <input type="file" @change="onPhotoChange($event)" class="d-none" accept="image/*">
                                    </label>
                                    <button v-if="!confirmDeletePhoto" class="btn btn-outline-danger btn-sm rounded-pill p-2 px-3" @click="confirmDeletePhoto = true">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill py-2 flex-grow-1 fw-bold animate-pulse" @click="deletePhoto()">
                                        Confirm
                                    </button>
                                </div>
                                <button v-if="confirmDeletePhoto" class="btn btn-link btn-xs text-muted" @click="confirmDeletePhoto = false">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Aadhar Card -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden d-flex flex-column">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center">
                            <i class="bi bi-file-earmark-person text-success me-2 fs-5"></i>
                            Aadhar Card
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-light-subtle d-flex flex-column align-items-center justify-content-center text-center flex-grow-1">
                        <div class="preview-container mb-4">
                            <div class="doc-placeholder rounded-4 d-flex align-items-center justify-content-center bg-white border" style="width: 130px; height: 130px; overflow: hidden;">
                                <div v-if="aadharDoc && aadharDoc.document">
                                    <i class="bi bi-file-earmark-pdf text-danger display-4" v-if="aadharDoc.document.endsWith('.pdf')"></i>
                                    <img v-else :src="'/storage' + aadharDoc.document" class="w-100 h-100 object-fit-cover">
                                </div>
                                <i v-else class="bi bi-card-image display-4 text-muted opacity-25"></i>
                            </div>
                        </div>
                        
                        <p class="text-muted small mb-4 px-2">National identification credential document copy (PDF/Image format).</p>

                        <div class="w-100 mt-auto">
                            <!-- Upload controls -->
                            <div v-if="!aadharDoc || !aadharDoc.document">
                                <label class="btn btn-outline-success btn-sm rounded-pill w-100 py-2 fw-bold" :disabled="loadingAadhar">
                                    <i v-if="loadingAadhar" class="spinner-border spinner-border-sm me-2"></i>
                                    <i v-else class="bi bi-upload me-2"></i>
                                    Upload Aadhar
                                    <input type="file" @change="onAadharChange($event)" class="d-none" accept=".pdf,image/*">
                                </label>
                            </div>
                            <!-- Existing Aadhar Controls -->
                            <div v-else class="d-flex flex-column gap-2">
                                <button @click="openPreview('Aadhar Card', '/storage' + aadharDoc.document)" class="btn btn-light btn-sm rounded-pill py-2 fw-bold">
                                    <i class="bi bi-eye me-2"></i> View Aadhar Card
                                </button>
                                <div class="d-flex gap-2">
                                    <label class="btn btn-outline-secondary btn-sm rounded-pill flex-grow-1 py-2 fw-bold" :disabled="loadingAadhar">
                                        Replace
                                        <input type="file" @change="onAadharChange($event)" class="d-none" accept=".pdf,image/*">
                                    </label>
                                    <button v-if="!confirmDeleteAadhar" class="btn btn-outline-danger btn-sm rounded-pill p-2 px-3" @click="confirmDeleteAadhar = true">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill py-2 flex-grow-1 fw-bold animate-pulse" @click="deleteAadhar()">
                                        Confirm
                                    </button>
                                </div>
                                <button v-if="confirmDeleteAadhar" class="btn btn-link btn-xs text-muted" @click="confirmDeleteAadhar = false">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Pan Card -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden d-flex flex-column">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center">
                            <i class="bi bi-file-earmark-lock text-warning me-2 fs-5"></i>
                            PAN Card
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-light-subtle d-flex flex-column align-items-center justify-content-center text-center flex-grow-1">
                        <div class="preview-container mb-4">
                            <div class="doc-placeholder rounded-4 d-flex align-items-center justify-content-center bg-white border" style="width: 130px; height: 130px; overflow: hidden;">
                                <div v-if="panDoc && panDoc.document">
                                    <i class="bi bi-file-earmark-pdf text-danger display-4" v-if="panDoc.document.endsWith('.pdf')"></i>
                                    <img v-else :src="'/storage' + panDoc.document" class="w-100 h-100 object-fit-cover">
                                </div>
                                <i v-else class="bi bi-credit-card-2-front display-4 text-muted opacity-25"></i>
                            </div>
                        </div>
                        
                        <p class="text-muted small mb-4 px-2">Tax identification credentials document copy (PDF/Image format).</p>

                        <div class="w-100 mt-auto">
                            <!-- Upload controls -->
                            <div v-if="!panDoc || !panDoc.document">
                                <label class="btn btn-outline-warning btn-sm rounded-pill w-100 py-2 fw-bold text-dark" :disabled="loadingPan">
                                    <i v-if="loadingPan" class="spinner-border spinner-border-sm me-2"></i>
                                    <i v-else class="bi bi-upload me-2"></i>
                                    Upload PAN
                                    <input type="file" @change="onPanChange($event)" class="d-none" accept=".pdf,image/*">
                                </label>
                            </div>
                            <!-- Existing PAN Controls -->
                            <div v-else class="d-flex flex-column gap-2">
                                <button @click="openPreview('PAN Card', '/storage' + panDoc.document)" class="btn btn-light btn-sm rounded-pill py-2 fw-bold">
                                    <i class="bi bi-eye me-2"></i> View PAN Card
                                </button>
                                <div class="d-flex gap-2">
                                    <label class="btn btn-outline-secondary btn-sm rounded-pill flex-grow-1 py-2 fw-bold" :disabled="loadingPan">
                                        Replace
                                        <input type="file" @change="onPanChange($event)" class="d-none" accept=".pdf,image/*">
                                    </label>
                                    <button v-if="!confirmDeletePan" class="btn btn-outline-danger btn-sm rounded-pill p-2 px-3" @click="confirmDeletePan = true">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill py-2 flex-grow-1 fw-bold animate-pulse" @click="deletePan()">
                                        Confirm
                                    </button>
                                </div>
                                <button v-if="confirmDeletePan" class="btn btn-link btn-xs text-muted" @click="confirmDeletePan = false">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Premium Preview Modal Overlay -->
        <div v-if="previewModalActive" class="modal-backdrop-custom d-flex align-items-center justify-content-center" @click="closePreviewModal()">
            <div class="modal-dialog-custom rounded-4 shadow-lg bg-white overflow-hidden animate-zoom-in" @click.stop>
                <div class="modal-header-custom py-3 px-4 bg-light d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">{{ previewModalTitle }}</h6>
                    <button class="btn-close shadow-none" @click="closePreviewModal()"></button>
                </div>
                <div class="modal-body-custom p-4 d-flex align-items-center justify-content-center bg-light-subtle" style="min-height: 300px; max-height: 70vh; overflow-y: auto;">
                    <img v-if="previewModalType === 'image'" :src="previewModalUrl" class="img-fluid rounded shadow-sm max-h-preview object-fit-contain">
                    <iframe v-else-if="previewModalType === 'pdf'" :src="previewModalUrl" class="w-100 rounded shadow-sm" style="height: 60vh; border: none;"></iframe>
                </div>
                <div class="modal-footer-custom py-3 px-4 bg-white border-top d-flex justify-content-end gap-2">
                    <a :href="previewModalUrl" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-box-arrow-up-right me-2"></i> Open in New Tab
                    </a>
                    <a :href="previewModalUrl" download class="btn btn-outline-success btn-sm rounded-pill px-4 fw-bold">
                        <i class="bi bi-download me-2"></i> Download File
                    </a>
                    <button class="btn btn-dark btn-sm rounded-pill px-4 fw-bold" @click="closePreviewModal()">Close</button>
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
            profilePhoto: null,
            aadharDoc: null,
            panDoc: null,
            loadingPhoto: false,
            loadingAadhar: false,
            loadingPan: false,
            confirmDeletePhoto: false,
            confirmDeleteAadhar: false,
            confirmDeletePan: false,

            // Modal states
            previewModalActive: false,
            previewModalTitle: '',
            previewModalUrl: '',
            previewModalType: 'image'
        };
    },
    methods: {
        fetchPhoto(){
            axios.get('/employee/employee_photo/' + this.employee_id + '/fetch').then(res => {
                if (res.data && res.data.data && res.data.data.length > 0) {
                    this.profilePhoto = res.data.data[0];
                } else {
                    this.profilePhoto = null;
                }
            });
        },
        fetchDocs(){
            axios.get('/employee/employee_document/' + this.employee_id + '/fetch', {params: {rows: 100}}).then(res => {
                const docs = res.data.data || [];
                this.aadharDoc = docs.find(d => d.document_name === 'Aadhar Card') || null;
                this.panDoc = docs.find(d => d.document_name === 'Pan Card') || null;
            });
        },
        onPhotoChange(e){
            const file = e.target.files[0];
            if (!file) return;
            this.loadingPhoto = true;
            let fd = new FormData();
            fd.append('media', file);
            fd.append('employee_id', this.employee_id);
            if (this.profilePhoto && this.profilePhoto.id) {
                fd.append('id', this.profilePhoto.id);
            }

            let url = (this.profilePhoto && this.profilePhoto.id) ? '/employee/employee_photo/update' : '/employee/employee_photo/add';
            axios.post(url, fd).then(() => {
                this.fetchPhoto();
            }).finally(() => {
                this.loadingPhoto = false;
            });
        },
        deletePhoto(){
            if (!this.profilePhoto || !this.profilePhoto.id) return;
            this.loadingPhoto = true;
            axios.post('/employee/employee_photo/delete', { id: this.profilePhoto.id }).then(() => {
                this.profilePhoto = null;
                this.confirmDeletePhoto = false;
            }).finally(() => {
                this.loadingPhoto = false;
            });
        },
        onAadharChange(e){
            const file = e.target.files[0];
            if (!file) return;
            this.loadingAadhar = true;
            let fd = new FormData();
            fd.append('document', file);
            fd.append('document_name', 'Aadhar Card');
            fd.append('employee_id', this.employee_id);
            if (this.aadharDoc && this.aadharDoc.id) {
                fd.append('id', this.aadharDoc.id);
            }

            let url = (this.aadharDoc && this.aadharDoc.id) ? '/employee/employee_document/update' : '/employee/employee_document/add';
            axios.post(url, fd).then(() => {
                this.fetchDocs();
            }).finally(() => {
                this.loadingAadhar = false;
            });
        },
        deleteAadhar(){
            if (!this.aadharDoc || !this.aadharDoc.id) return;
            this.loadingAadhar = true;
            axios.post('/employee/employee_document/delete', { id: this.aadharDoc.id }).then(() => {
                this.aadharDoc = null;
                this.confirmDeleteAadhar = false;
            }).finally(() => {
                this.loadingAadhar = false;
            });
        },
        onPanChange(e){
            const file = e.target.files[0];
            if (!file) return;
            this.loadingPan = true;
            let fd = new FormData();
            fd.append('document', file);
            fd.append('document_name', 'Pan Card');
            fd.append('employee_id', this.employee_id);
            if (this.panDoc && this.panDoc.id) {
                fd.append('id', this.panDoc.id);
            }

            let url = (this.panDoc && this.panDoc.id) ? '/employee/employee_document/update' : '/employee/employee_document/add';
            axios.post(url, fd).then(() => {
                this.fetchDocs();
            }).finally(() => {
                this.loadingPan = false;
            });
        },
        deletePan(){
            if (!this.panDoc || !this.panDoc.id) return;
            this.loadingPan = true;
            axios.post('/employee/employee_document/delete', { id: this.panDoc.id }).then(() => {
                this.panDoc = null;
                this.confirmDeletePan = false;
            }).finally(() => {
                this.loadingPan = false;
            });
        },

        // Modal methods
        openPreview(title, url, type = null) {
            this.previewModalTitle = title;
            this.previewModalUrl = url;
            if (type) {
                this.previewModalType = type;
            } else {
                this.previewModalType = url.toLowerCase().endsWith('.pdf') ? 'pdf' : 'image';
            }
            this.previewModalActive = true;
        },
        closePreviewModal() {
            this.previewModalActive = false;
            this.previewModalTitle = '';
            this.previewModalUrl = '';
            this.previewModalType = 'image';
        }
    },
    created(){
        this.fetchPhoto();
        this.fetchDocs();
    }
}
</script>

<style scoped>
.document-vault-suite {
    padding: 0.5rem 0;
}
.preview-container, .avatar-placeholder, .doc-placeholder {
    transition: all 0.3s ease;
}
.avatar-placeholder, .doc-placeholder {
    border: 3px dashed #cbd5e1 !important;
}
.avatar-placeholder:hover, .doc-placeholder:hover {
    border-color: #6366f1 !important;
    transform: translateY(-2px);
}
.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06);
}
.bg-light-subtle {
    background-color: #f8fafc !important;
}
.animate-pulse {
    animation: pulse 1.5s infinite;
}
.btn-xs {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .6; }
}

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
    max-width: 800px;
    background: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.max-h-preview {
    max-height: 60vh;
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