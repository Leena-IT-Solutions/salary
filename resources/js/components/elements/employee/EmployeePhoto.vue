<template>
    <div class="identity-photo-suite">
        <div class="row g-4">
            <!-- Upload Section -->
            <div class="col-12 col-xl-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h6 class="fw-bold mb-0 text-dark">Enroll Identity Image</h6>
                    </div>
                    <div class="card-body p-4 bg-light-subtle">
                        <div class="text-center mb-4">
                            <div class="photo-placeholder mx-auto rounded-circle shadow-inner d-flex align-items-center justify-content-center bg-white" style="width: 150px; height: 150px;">
                                <i v-if="!employee_photo.photo" class="bi bi-camera display-4 text-muted opacity-25"></i>
                                <img v-else :src="previewUrl" class="rounded-circle w-100 h-100 object-fit-cover shadow">
                            </div>
                            <p class="text-muted small mt-3 px-3">Professional headshots are required for biometric identification and ID card generation.</p>
                        </div>

                        <div class="row g-3">
                            <forms-file-field @change="getImageObject($event)" v-model="employee_photo.photo" name="media" label="Capture or Select Image" error="" classes="col-12 custom-file-modern"></forms-file-field>
                            
                            <div class="col-12 d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div v-if="employee_photo.id">
                                    <button v-if="!isDelete" class="btn btn-outline-danger btn-sm rounded-pill px-3" @click="isDelete = true">
                                        Remove Image
                                    </button>
                                    <button v-else class="btn btn-danger btn-sm rounded-pill px-3 animate-pulse" @click="deleteNow()">
                                        Confirm Delete
                                    </button>
                                </div>
                                <div v-else></div>
                                
                                <forms-submit-button name="" v-model="loading" :label="employee_photo.id ? 'Modify Identity' : 'Register Photo'" @click="save()" classes="px-4 shadow-sm"></forms-submit-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Section -->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Identity Archive</h6>
                        <span class="badge bg-soft-primary text-primary rounded-pill px-3">Active History</span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light-subtle">
                                <tr class="text-uppercase small fw-800 text-muted">
                                    <th class="ps-4 py-3" style="width: 80px;">ID</th>
                                    <th>Image Asset</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="wl in employee_photos" :key="wl.id" class="transition-all hover-glow border-bottom border-light">
                                    <td class="ps-4">
                                        <span class="badge bg-white text-muted border">#{{ wl.id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="archive-preview me-3 rounded-3 shadow-sm overflow-hidden border">
                                                <img :src="'/storage' + wl.media" class="w-100 h-100 object-fit-cover">
                                            </div>
                                            <div class="small fw-semibold text-truncate" style="max-width: 200px;">{{ wl.media.split('/').pop() }}</div>
                                        </div>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-sm btn-white border shadow-sm rounded-circle p-2" @click="edit(wl)">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="employee_photos.length === 0">
                                    <td colspan="3" class="text-center py-5">
                                        <div class="opacity-25 py-4">
                                            <i class="bi bi-person-bounding-box display-1"></i>
                                            <p class="mt-2 fw-bold">No active identity photos</p>
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
            previewUrl: null,
            employee_photo: {
                employee_id: null,
                id: null,
                photo: null,
                media: null,
            },
            employee_photos: [],
            params: { key: null, value: null, by: 'id', order: 'desc', rows: 10 }
        };
    },
    methods: {
        getImageObject(e){
            const file = e.target.files[0];
            this.employee_photo.media = file;
            if (file) {
                this.previewUrl = URL.createObjectURL(file);
            }
        },
        fetch(){
            axios.get('/employee/employee_photo/' + this.employee_id + '/fetch', {params: this.params}).then(res => {
                this.employee_photos = res.data.data;
                this.loading = false;
            });
        },
        save(){
            this.loading = true;
            let fd = new FormData();
            if (this.employee_photo.id) fd.append('id', this.employee_photo.id);
            fd.append('media', this.employee_photo.media);
            fd.append('employee_id', this.employee_id);

            let url = this.employee_photo.id ? '/employee/employee_photo/update' : '/employee/employee_photo/add';
            axios.post(url, fd).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        reset(){
            this.employee_photo.id = null;
            this.employee_photo.media = null;
            this.employee_photo.photo = null;
            this.previewUrl = null;
            this.isDelete = false;
        },
        deleteNow(){
            this.loading = true;
            axios.post('/employee/employee_photo/delete', this.employee_photo).then(res => {
                this.reset();
                this.fetch();
            }).finally(() => this.loading = false);
        },
        edit(item){
            this.employee_photo.id = item.id;
            this.previewUrl = '/storage' + item.media;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    },
    created(){
        this.fetch();
    },
}
</script>

<style scoped>
.identity-photo-suite {
    padding: 1rem 0;
}
.photo-placeholder {
    border: 4px dashed #e2e8f0;
    transition: all 0.3s ease;
}
.photo-placeholder:hover {
    border-color: #6366f1;
}
.archive-preview {
    width: 60px;
    height: 45px;
}
.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06);
}
.bg-soft-primary { background-color: #eef2ff; color: #6366f1; }
.transition-all { transition: all 0.2s ease; }
.hover-glow:hover { background-color: #f8fafc; }
.animate-pulse { animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
</style>