<template>
    <div class="org-settings-dashboard pt-0 px-0">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden border-top-primary">
            <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">Statutory Registration</h5>
                <div class="badge bg-soft-info text-info rounded-pill px-3 py-2 border border-info border-opacity-10">
                    <i class="bi bi-shield-lock me-2"></i> Compliance Vault
                </div>
            </div>
            
            <div class="card-body p-4 bg-light-subtle">
                <div v-if="profile" class="row g-4">
                    <!-- Tax Identifiers -->
                    <div class="col-12 col-xl-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                            <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Fiscal Identifiers</h6>
                            <div class="row g-3">
                                <forms-text-field name="tan" label="Tax Deduction ID (TAN)" v-model="profile.tan" placeholder="Alpha-numeric TAN" classes="col-12"></forms-text-field>
                                <forms-text-field name="pan" label="Tax Entity ID (PAN)" v-model="profile.pan" placeholder="Legal Entity PAN" classes="col-12"></forms-text-field>
                                <forms-text-field name="gst" label="Service Tax (GSTIN)" v-model="profile.gst" placeholder="GST Registration No" classes="col-12"></forms-text-field>
                            </div>
                        </div>
                    </div>

                    <!-- Labor & Corporate -->
                    <div class="col-12 col-xl-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                            <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Employment & Governance</h6>
                            <div class="row g-3">
                                <forms-text-field name="epf" label="Provident Fund (EPF)" v-model="profile.epf" placeholder="EPF Regional Code" classes="col-12"></forms-text-field>
                                <forms-text-field name="esic" label="State Insurance (ESIC)" v-model="profile.esic" placeholder="ESIC Unit ID" classes="col-12"></forms-text-field>
                                <forms-text-field name="cin" label="Corporate ID (CIN)" v-model="profile.cin" placeholder="Company Identity Number" classes="col-12"></forms-text-field>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Tax -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 p-4">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="text-uppercase small fw-bold text-muted mb-0">Professional Tax Authority</h6>
                                </div>
                                <div class="col-md-5">
                                    <forms-text-field name="ptax" label="" v-model="profile.ptax" placeholder="PTax Registration ID" classes="mb-0"></forms-text-field>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="col-12 d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
                        <div class="small text-muted me-3">
                            <i class="bi bi-info-circle me-1"></i> Ensure all identifiers match legal certificates.
                        </div>
                        <forms-submit-button name="" v-model="loading" label="Save Statutory Data" @click="update_profile()" classes="btn-lg px-5 shadow-sm rounded-pill"></forms-submit-button>
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
            loading: false,
            profile: {
                tan: null,
                pan: null,
                epf: null,
                esic: null,
                gst: null,
                cin: null,
                ptax: null,
            },
        };
    },

    methods: {
        fetch_profile(){
            axios.get('/organisation_settings/company_profile/fetch').then(res => {
                this.profile.tan = res.data.tan;
                this.profile.pan = res.data.pan;
                this.profile.epf = res.data.epf;
                this.profile.esic = res.data.esic;
                this.profile.gst = res.data.gst;
                this.profile.cin = res.data.cin;
                this.profile.ptax = res.data.ptax;
            });
        },

        update_profile(){
            this.loading = true;
            axios.post('/organisation_settings/company_profile/update', this.profile).then(res => {
                this.loading = false;
                this.fetch_profile();
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
.bg-soft-info { background-color: #f0f9ff; color: #0284c7; }
.bg-light-subtle { background-color: #f8fafc !important; }
.border-top-primary { border-top: 4px solid #6366f1 !important; }

.transition-all { transition: all 0.3s ease; }
</style>