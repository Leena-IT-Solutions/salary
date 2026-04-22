<template>
    <div class="org-settings-dashboard pt-0 px-0">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">Brand Assets</h5>
                <span class="badge bg-soft-primary text-primary rounded-pill px-3">Visual Identity</span>
            </div>
            
            <div class="card-body p-4 bg-light-subtle">
                <div class="row align-items-center g-4">
                    <div class="col-12 col-md-auto">
                        <div class="logo-preview-container p-2 bg-white rounded-4 shadow-sm border border-light position-relative overflow-hidden" style="width: 180px; height: 180px;">
                            <div v-if="profile && profile.logo" class="h-100 w-100 d-flex align-items-center justify-content-center bg-white">
                                <img :src="profile.logo" class="img-fluid rounded-3" style="max-height: 100%; object-fit: contain;">
                            </div>
                            <div v-else class="h-100 w-100 d-flex flex-column align-items-center justify-content-center text-muted opacity-25">
                                <i class="bi bi-image display-4"></i>
                                <small class="mt-2 fw-bold">NO LOGO</small>
                            </div>
                            
                            <div v-if="loading" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md">
                        <div class="ms-md-3">
                            <h6 class="fw-bold text-dark mb-2">Corporate Logo Specification</h6>
                            <p class="text-muted small mb-4 lh-base">
                                Upload your organization's primary logo. We recommend a transparent PNG/SVG with a minimum width of 400px for high-fidelity rendering on payroll reports and payslips.
                            </p>
                            
                            <div class="upload-action-box position-relative" style="max-width: 400px;">
                                <forms-file-field name="logo" label="Change Logo Image" v-model="logo" @change="logoChanged($event)" classes="mb-0 custom-file-modern"></forms-file-field>
                                <div v-if="success" class="mt-2 small text-success fw-bold animate__animated animate__fadeInUp">
                                    <i class="bi bi-check-circle-fill me-1"></i> Asset synchronized successfully!
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

    data(){
        return {
            logo: null,
            profile: null,
            loading: false,
            success: false
        };
    },

    methods: {
        fetch_profile(){
            axios.get('/organisation_settings/company_profile/fetch').then(res => {
                this.profile = res.data;
                this.loading = false;
            });
        },

        logoChanged(e){
            if (!e.target.files.length) return;
            
            this.loading = true;
            this.success = false;
            let fd = new FormData;
            fd.append("logo", e.target.files[0]);

            let config = {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }

            axios.post('/organisation_settings/company_profile/logo_upload', fd, config).then(res => {
                this.fetch_profile();
                this.success = true;
                setTimeout(() => { this.success = false; }, 3000);
            }).catch(err => {
                this.loading = false;
            });
        }
    },

    created(){
        this.fetch_profile();
    },

}
</script>

<style scoped>
.logo-preview-container {
    transition: all 0.3s ease;
}

.logo-preview-container:hover {
    border-color: #6366f1 !important;
    box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.1) !important;
}

.bg-soft-primary { background-color: #eef2ff; color: #6366f1; }
.bg-light-subtle { background-color: #f8fafc !important; }

.transition-all { transition: all 0.3s ease; }

.custom-file-modern :deep(.form-control) {
    border-radius: 12px;
    padding: 12px;
    background-color: white;
    border: 2px dashed #e2e8f0;
}

.custom-file-modern :deep(.form-control:hover) {
    border-color: #6366f1;
}
</style>