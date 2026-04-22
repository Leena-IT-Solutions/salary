<template>
    <div class="org-settings-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Corporate Identity</h4>
                    <p class="text-muted small mb-0">Manage your organization's legal name, primary address, and global contact credentials.</p>
                </div>
                <div class="col-auto">
                    <div class="badge bg-soft-primary text-primary rounded-pill px-3 py-2 border border-primary border-opacity-10">
                        <i class="bi bi-shield-check me-2"></i> Authorized Entity
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Profile Form -->
        <div v-if="profile" class="row g-4">
            <!-- Left Column: Core Identity -->
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-lg rounded-4 p-4 mb-4 overflow-hidden border-top-primary">
                    <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">
                        <i class="bi bi-building me-2"></i> Legal Entity Information
                    </h6>
                    <div class="row g-3">
                        <forms-text-field name="company_name" label="Organization Legal Name" v-model="profile.company_name" placeholder="ABC Solutions Pvt. Ltd." classes="col-12"></forms-text-field>
                        <forms-text-field name="address" label="Registered Head Office Address" v-model="profile.address" placeholder="123 Business Avenue, Suite 100" classes="col-12"></forms-text-field>
                        
                        <div class="col-md-6 col-lg-3">
                            <forms-text-field name="city" label="City" v-model="profile.city" placeholder="Mumbai" classes="col-12"></forms-text-field>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <forms-text-field name="pincode" label="ZIP / Pincode" v-model="profile.pincode" placeholder="400001" classes="col-12"></forms-text-field>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <forms-text-field name="state" label="State / Province" v-model="profile.state" placeholder="Maharashtra" classes="col-12"></forms-text-field>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <forms-text-field name="country" label="Country" v-model="profile.country" placeholder="India" classes="col-12"></forms-text-field>
                        </div>
                    </div>
                </div>

                <!-- Connectivity Section -->
                <div class="card border-0 shadow-lg rounded-4 p-4">
                    <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">
                        <i class="bi bi-envelope-at me-2"></i> Communication Channels
                    </h6>
                    <div class="row g-3">
                        <forms-text-field name="phone" label="Primary Support Number" v-model="profile.phone" placeholder="+91 (000) 000-0000" classes="col-12 col-lg-6"></forms-text-field>
                        <forms-text-field name="email" label="Official Corporate Email" v-model="profile.email" placeholder="admin@organization.com" classes="col-12 col-lg-6"></forms-text-field>
                    </div>
                </div>
            </div>

            <!-- Right Column: Brand Preview -->
            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-lg rounded-4 p-4 h-100 bg-white d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="brand-visual p-5 bg-light rounded-circle shadow-inner mb-4 transition-all hover-glow">
                        <i class="bi bi-buildings display-1 text-primary"></i>
                    </div>
                    
                    <h5 class="fw-bold text-dark mb-1">{{ profile.company_name || 'Organization Name' }}</h5>
                    <p class="text-muted small px-3">
                        <i class="bi bi-geo-alt me-1"></i>
                        {{ profile.city || 'City' }}, {{ profile.country || 'Country' }}
                    </p>
                    
                    <div class="mt-4 w-100 px-4">
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Global ID</span>
                            <span class="fw-mono text-dark">#ORG-CORP-01</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 small">
                            <span class="text-muted">Verification</span>
                            <span class="text-success fw-bold"><i class="bi bi-patch-check-fill me-1"></i> Verified</span>
                        </div>
                        
                        <forms-submit-button name="" v-model="loading" label="Authorize Changes" @click="update_profile()" classes="w-100 btn-lg shadow-sm rounded-pill"></forms-submit-button>
                    </div>

                    <div class="mt-5 p-3 bg-soft-info rounded-4 border border-info border-opacity-10 text-start w-100">
                        <p class="small text-info-emphasis mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i> Updating these details will reflect on your payroll PDFs, tax filings, and internal communications.
                        </p>
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
                company_name: null,
                address: null,
                city: null,
                state: null,
                pincode: null,
                country: null,
                phone: null,
                email: null,
            },
        };
    },

    methods: {

        fetch_profile(){
            axios.get('/organisation_settings/company_profile/fetch').then(res => {
                this.profile.company_name = res.data.company_name;
                this.profile.address = res.data.address;
                this.profile.city = res.data.city;
                this.profile.state = res.data.state;
                this.profile.pincode = res.data.pincode;
                this.profile.country = res.data.country;
                this.profile.phone = res.data.phone;
                this.profile.email = res.data.email;
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
.org-settings-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.bg-soft-primary { background-color: #eef2ff; color: #6366f1; }
.bg-soft-info { background-color: #f0f9ff; color: #0284c7; }

.border-top-primary { border-top: 4px solid #6366f1 !important; }

.brand-visual {
    width: 180px;
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}

.hover-glow:hover {
    box-shadow: 0 0 20px rgba(99, 102, 241, 0.15);
    background-color: white !important;
}

.transition-all { transition: all 0.3s ease; }

.fw-mono { font-family: ui-monospace, SFMono-Regular, monospace; }
</style>