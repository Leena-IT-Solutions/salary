<template>
    <div class="org-settings-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-4 mb-5">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Work Site Management</h4>
                    <p class="text-muted small mb-0">Manage global office locations, physical branches, and satellite facilities.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-x-circle' : 'bi bi-geo-fill']" class="me-2"></i>
                        {{ isForm ? 'Close Editor' : 'Register Location' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Location Grid -->
        <div class="row g-4 align-items-stretch mb-5">
            <div class="col-12 col-md-6 col-xxl-4" v-for="wl in workLocations" :key="wl.id">
                <div class="card location-card border-0 shadow-sm rounded-4 h-100 overflow-hidden transition-all">
                    <div class="card-header border-0 bg-transparent p-4 pb-0 d-flex justify-content-between align-items-start">
                        <div class="location-badge bg-soft-primary text-primary">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                <li><a class="dropdown-item py-2" href="#wlform" @click="edit(wl)"><i class="bi bi-pencil me-2 text-primary"></i> Edit Site</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item py-2 text-danger" v-if="isDelete !== wl.id" @click="isDelete = wl.id"><i class="bi bi-trash me-2"></i> Delete</button>
                                    <button class="dropdown-item py-2 bg-danger text-white" v-if="isDelete === wl.id" @click="deleteWL(wl.id)">Confirm Deletion</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 pt-3">
                        <h5 class="fw-bold text-dark mb-1">{{ wl.location_name }}</h5>
                        <div class="small text-primary fw-semibold mb-3">{{ profile ? profile.company_name : 'Organizational Unit' }}</div>
                        
                        <div class="address-box p-3 bg-light rounded-4 mb-3 border">
                            <p class="small text-muted mb-0 lh-base">
                                <i class="bi bi-pin-map me-2"></i>
                                {{ wl.address }}, {{ wl.city }}, {{ wl.state }}<br>
                                {{ wl.country }} {{ wl.pincode }}
                            </p>
                        </div>
                        
                        <div class="contact-details row g-2">
                            <div class="col-12">
                                <div class="d-flex align-items-center small text-muted">
                                    <i class="bi bi-telephone me-2 opacity-50"></i> {{ wl.phone || 'No phone' }}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center small text-muted">
                                    <i class="bi bi-envelope me-2 opacity-50"></i> {{ wl.email || 'No email' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light-subtle border-0 p-3 text-center">
                        <button class="btn btn-link btn-sm text-decoration-none fw-bold" @click="edit(wl)">Quick Manage <i class="bi bi-arrow-right ms-1"></i></button>
                    </div>
                </div>
            </div>

            <!-- Add Location Placeholder -->
            <div class="col-12 col-md-6 col-xxl-4" v-if="!isForm">
                <div @click="toggleForm" class="card add-location-card border-dashed border-2 rounded-4 h-100 d-flex flex-column justify-content-center align-items-center p-5 transition-all text-muted cursor-pointer hover-bg-primary">
                    <i class="bi bi-plus-circle-dotted display-4 mb-3 opacity-25"></i>
                    <h6 class="fw-bold mb-1">New Site Enrollment</h6>
                    <p class="small text-center opacity-75">Click to register a new physical work location or branch.</p>
                </div>
            </div>
        </div>

        <!-- Modern Location Form -->
        <transition name="fade-slide">
            <div v-if="isForm" id="wlform" class="card border-0 shadow-lg rounded-4 mb-5 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ workLocation.id ? 'Modify Site Credentials' : 'Register New Work Site' }}</h5>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="row g-4">
                        <div class="col-12 col-xl-8">
                            <!-- Site Core Info -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Location Identity</h6>
                                <div class="row g-3">
                                    <forms-text-field name="location_name" @input="errors.location_name = null" label="Building / Branch Name" v-model="workLocation.location_name" :error="errors.location_name" placeholder="e.g. North Plaza Office" classes="col-12"></forms-text-field>
                                    <forms-text-field name="address" @input="errors.address = null" label="Street Address" v-model="workLocation.address" :error="errors.address" placeholder="Full address details..." classes="col-12"></forms-text-field>
                                </div>
                            </div>
                            
                            <!-- Geographic Details -->
                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Geography & Contact</h6>
                                <div class="row g-3">
                                    <forms-text-field name="city" @input="errors.city = null" label="City" v-model="workLocation.city" :error="errors.city" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="pincode" @input="errors.pincode = null" label="Pincode / Zip" v-model="workLocation.pincode" :error="errors.pincode" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="state" @input="errors.state = null" label="State / Province" v-model="workLocation.state" :error="errors.state" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="country" @input="errors.country = null" label="Country" v-model="workLocation.country" :error="errors.country" classes="col-md-6"></forms-text-field>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4">
                            <!-- Site Connectivity -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2">Site Connectivity</h6>
                                <div class="row g-3">
                                    <forms-text-field name="phone" @input="errors.phone = null" label="Site Phone" v-model="workLocation.phone" :error="errors.phone" placeholder="+1..." classes="col-12"></forms-text-field>
                                    <forms-text-field name="email" @input="errors.email = null" label="Site Email Address" v-model="workLocation.email" :error="errors.email" placeholder="site@company.com" classes="col-12"></forms-text-field>
                                </div>
                                <div class="mt-4 p-3 bg-soft-info rounded-4 border border-info border-opacity-25">
                                    <p class="small text-info-emphasis mb-0"><i class="bi bi-info-circle-fill me-2"></i> This site will appear as a selectable location for employee assignment and shift scheduling.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="col-12 d-flex justify-content-between align-items-center mt-4">
                            <div v-if="workLocation.id">
                                <button v-if="isDelete !== workLocation.id" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="isDelete = workLocation.id">
                                    <i class="bi bi-trash3 me-2"></i> Delete Site
                                </button>
                                <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteWL(workLocation.id)">
                                    Confirm Permanent Deletion
                                </button>
                            </div>
                            <div v-else></div>
                            
                            <div class="d-flex gap-2">
                                <button class="btn btn-light btn-lg px-4 rounded-pill shadow-none" @click="toggleForm">Discard</button>
                                <forms-submit-button name="" v-model="loading" :label="workLocation.id ? 'Update Site Logic' : 'Initiate Location'" @click="saveWorkLocation()" classes="btn-lg px-5 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
import axios from "axios";
export default {

    data(){
        return {
            isForm: false,
            isDelete: null,
            loading: false,
            profile: null,
            workLocation: {
                id: null,
                location_name: null,
                address: null,
                city: null,
                state: null,
                pincode: null,
                country: null,
                phone: null,
                email: null,
            },
            errors: {
                location_name: null,
                address: null,
                city: null,
                state: null,
                pincode: null,
                country: null,
                phone: null,
                email: null,
            },
            workLocations: [],
        };
    },

    methods: {
        toggleForm() {
            if(this.isForm) {
                this.reset();
                this.isForm = false;
            } else {
                this.isForm = true;
            }
        },

        edit(wl){
            this.isForm = true;
            this.workLocation.id = wl.id;
            this.workLocation.location_name = wl.location_name;
            this.workLocation.address = wl.address;
            this.workLocation.city = wl.city;
            this.workLocation.state = wl.state;
            this.workLocation.pincode = wl.pincode;
            this.workLocation.country = wl.country;
            this.workLocation.phone = wl.phone;
            this.workLocation.email = wl.email;
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        },

        reset(){
            Object.keys(this.workLocation).forEach(key => this.workLocation[key] = null);
            this.isDelete = null;
            this.resetErrors();
        },

        resetErrors(){
            Object.keys(this.errors).forEach(key => this.errors[key] = null);
        },

        setErrors(errors){
            Object.keys(this.errors).forEach(key => {
                this.errors[key] = errors[key] ? errors[key][0] : null;
            });
        },

        fetch_profile(){
            axios.get('/organisation_settings/company_profile/fetch')
            .then(res => {
                this.profile = res.data;
            });
        },

        fetch_work_locations(){
            axios.get('/organisation_settings/work_location/fetch').then(res => {
                this.loading = false;
                this.workLocations = res.data;
            });
        },

        add(){
            this.resetErrors();
            axios.post('/organisation_settings/work_location/add', this.workLocation)
            .then(res => {
                this.loading = false;
                this.fetch_work_locations();
                this.reset();
                this.isForm = false;
            }).catch(error => {
                this.loading = false;
                if (error.response && error.response.data && error.response.data.errors) {
                    this.setErrors(error.response.data.errors);
                }
            });
        },

        update(){
            this.resetErrors();
            axios.post('/organisation_settings/work_location/update', this.workLocation).then(res => {
                this.fetch_work_locations();
                this.reset();
                this.isForm = false;
            }).catch(error => {
                 this.loading = false;
                 if (error.response && error.response.data && error.response.data.errors) {
                    this.setErrors(error.response.data.errors);
                }
            });
        },

        deleteWL(id){
            axios.post('/organisation_settings/work_location/delete', {id: id}).then(res => {
                this.fetch_work_locations();
                this.reset();
            });
        },

        saveWorkLocation(){
            this.loading = true;
            if(this.workLocation.id == null){
                this.add();
            } else {
                this.update();
            }
        }

    },

    created(){
        this.fetch_profile();
        this.fetch_work_locations();
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

.location-badge {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 1.5rem;
}

.bg-soft-primary { background-color: #eef2ff; color: #6366f1; }

.location-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
}

.add-location-card {
    border-style: dashed !important;
    background-color: transparent;
}

.hover-bg-primary:hover {
    background-color: #eef2ff;
    border-color: #6366f1 !important;
    color: #6366f1 !important;
}

.address-box {
    background-color: #f8fafc;
}

.transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

.cursor-pointer { cursor: pointer; }

/* Transitions */
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.4s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-15px); }

@keyframes shakeX {
    from, to { transform: translate3d(0, 0, 0); }
    10%, 30%, 50%, 70%, 90% { transform: translate3d(-10px, 0, 0); }
    20%, 40%, 60%, 80% { transform: translate3d(10px, 0, 0); }
}
.animate__shakeX { animation-name: shakeX; animation-duration: 0.5s; }

.bg-light-subtle { background-color: #f8fafc !important; }
</style>