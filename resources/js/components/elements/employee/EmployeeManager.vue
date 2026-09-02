<template>
    <div class="employee-manager-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-4 mb-4">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="fw-bold mb-1 text-dark">Staff Directory</h4>
                    <p class="text-muted small mb-0">Unified lifecycle management for all corporate employees and workforce data.</p>
                </div>
                <div class="col-auto">
                    <button 
                        @click="toggleForm"
                        :class="[isForm ? 'btn-danger' : 'btn-primary']"
                        class="btn btn-lg shadow-sm px-4 fw-bold rounded-pill transition-all">
                        <i :class="[isForm ? 'bi bi-person-x' : 'bi bi-person-plus-fill']" class="me-2"></i>
                        {{ isForm ? 'Close Enrollment' : 'Enroll Employee' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Multi-Section Enrollment Form -->
        <transition name="fade-slide">
            <div v-if="isForm" class="card border-0 shadow-lg rounded-4 mb-5 overflow-hidden border-top-primary">
                <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom-0">
                    <h5 class="fw-bold mb-0 text-primary">{{ employee.id ? 'Edit Employee Credentials' : 'New Staff Registration' }}</h5>
                    <span v-if="employee.id" class="badge bg-light text-muted border fw-mono">UID: #{{ employee.id }}</span>
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

                    <div class="row g-4">
                        <!-- Left Column: Primary & Personal -->
                        <div class="col-12 col-xl-8">
                            <!-- Basic Profile -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-person me-2"></i>Basic Identity</h6>
                                <div class="row g-3">
                                    <forms-text-field name="first_name" label="First Name" v-model="employee.first_name" :error="errors.first_name ? errors.first_name[0] : ''" placeholder="Legal first name" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="middle_name" label="Middle Name" v-model="employee.middle_name" :error="errors.middle_name ? errors.middle_name[0] : ''" placeholder="Optional" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="last_name" label="Last Name" v-model="employee.last_name" :error="errors.last_name ? errors.last_name[0] : ''" placeholder="Legal last name" classes="col-md-4"></forms-text-field>
                                    
                                    <forms-text-field name="employee_code" label="Corporate ID / Staff Code" v-model="employee.employee_code" :error="errors.employee_code ? errors.employee_code[0] : ''" placeholder="EMP-001" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="tagid" label="RFID / Biometric Tag ID" v-model="employee.tagid" :error="errors.tagid ? errors.tagid[0] : ''" placeholder="8-digit hex code" classes="col-md-6"></forms-text-field>
                                </div>
                            </div>

                            <!-- Demographics -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-info-circle me-2"></i>Demographics</h6>
                                <div class="row g-3">
                                    <forms-select-field name="gender" label="Gender Identity" v-model="employee.gender" :error="errors.gender ? errors.gender[0] : ''" :options="[{key: 'Male', val: 'Male'}, {key: 'Female', val: 'Female'}, {key: 'Other', val: 'Other'}]" classes="col-md-4"></forms-select-field>
                                    <forms-select-field name="marital_status" label="Marital Status" v-model="employee.marital_status" :error="errors.marital_status ? errors.marital_status[0] : ''" :options="[{key: 'Married', val: 'Married'}, {key: 'Widowed', val: 'Widowed'}, {key: 'Separated', val: 'Separated'}, {key: 'Divorced', val: 'Divorced'}, {key: 'Single', val: 'Single'}]" classes="col-md-4"></forms-select-field>
                                    <forms-select-field name="blood_group" label="Blood Group" v-model="employee.blood_group" :error="errors.blood_group ? errors.blood_group[0] : ''" :options="[{key: 'O +ve', val: 'O +ve'}, {key: 'O -ve', val: 'O -ve'}, {key: 'A +ve', val: 'A +ve'}, {key: 'B +ve', val: 'B +ve'}, {key: 'AB +ve', val: 'AB +ve'}]" classes="col-md-4"></forms-select-field>
                                    <forms-text-field name="nationality" label="Nationality" v-model="employee.nationality" :error="errors.nationality ? errors.nationality[0] : ''" placeholder="e.g. Indian" classes="col-md-6"></forms-text-field>
                                    <forms-select-field name="religion" label="Religion" v-model="employee.religion" :error="errors.religion ? errors.religion[0] : ''" :options="[{key: 'Hindu', val: 'Hindu'}, {key: 'Muslim', val: 'Muslim'}, {key: 'Christian', val: 'Christian'}, {key: 'Sikh', val: 'Sikh'}, {key: 'Buddhist', val: 'Buddhist'}, {key: 'Jain', val: 'Jain'}, {key: 'Atheist', val: 'Atheist'}, {key: 'Other', val: 'Other'}]" classes="col-md-6"></forms-select-field>
                                    <forms-text-field name="cast" label="Caste" v-model="employee.cast" :error="errors.cast ? errors.cast[0] : ''" placeholder="Caste" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="subcast" label="Sub-caste" v-model="employee.subcast" :error="errors.subcast ? errors.subcast[0] : ''" placeholder="Sub-caste" classes="col-md-6"></forms-text-field>
                                </div>
                            </div>

                            <!-- Statutory & Documents -->
                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-file-earmark-lock me-2"></i>Statutory IDs & Banking</h6>
                                <div class="row g-3">
                                    <forms-text-field name="aadhar" label="National Identification (Aadhar)" v-model="employee.aadhar" :error="errors.aadhar ? errors.aadhar[0] : ''" placeholder="12-digit number" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="pan" label="Tax ID (PAN)" v-model="employee.pan" :error="errors.pan ? errors.pan[0] : ''" placeholder="Alpha-numeric PAN" classes="col-md-6"></forms-text-field>
                                    <forms-text-field name="pf" label="PF Registration Number" v-model="employee.pf" :error="errors.pf ? errors.pf[0] : ''" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="uan" label="Universal Account (UAN)" v-model="employee.uan" :error="errors.uan ? errors.uan[0] : ''" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="esic" label="ESIC Number" v-model="employee.esic" :error="errors.esic ? errors.esic[0] : ''" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="old_pf" label="Old PF Registration Number" v-model="employee.old_pf" :error="errors.old_pf ? errors.old_pf[0] : ''" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="old_uan" label="Old Universal Account (UAN)" v-model="employee.old_uan" :error="errors.old_uan ? errors.old_uan[0] : ''" classes="col-md-4"></forms-text-field>
                                    <forms-text-field name="old_esic" label="Old ESIC Number" v-model="employee.old_esic" :error="errors.old_esic ? errors.old_esic[0] : ''" classes="col-md-4"></forms-text-field>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Timeline & Attributes -->
                        <div class="col-12 col-xl-4">
                            <!-- Contact & Communication -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-envelope me-2"></i>Communication Channel</h6>
                                <div class="row g-3">
                                    <forms-text-field name="email" label="Professional Email" v-model="employee.email" :error="errors.email ? errors.email[0] : ''" placeholder="work@company.com" classes="col-12"></forms-text-field>
                                    <forms-text-field name="phone" label="Primary Phone" v-model="employee.phone" :error="errors.phone ? errors.phone[0] : ''" placeholder="+1 (000) 000-0000" classes="col-12"></forms-text-field>
                                </div>
                            </div>

                            <!-- Key Timelines -->
                            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-calendar-event me-2"></i>Critical Timelines</h6>
                                <div class="row g-3">
                                    <forms-date-field name="doj" label="Joining Date" v-model="employee.doj" :error="errors.doj ? errors.doj[0] : ''" classes="col-12"></forms-date-field>
                                    <forms-date-field name="dob" label="Date of Birth" v-model="employee.dob" :error="errors.dob ? errors.dob[0] : ''" classes="col-12"></forms-date-field>
                                    <forms-date-field name="doe" label="Resignation / Exit Date" v-model="employee.doe" :error="errors.doe ? errors.doe[0] : ''" classes="col-12"></forms-date-field>
                                </div>
                            </div>



                            <!-- Professional & Cultural -->
                            <div class="card border-0 shadow-sm rounded-4 p-4">
                                <h6 class="text-uppercase small fw-bold text-muted mb-4 border-bottom pb-2"><i class="bi bi-translate me-2"></i>Cultural & Professional</h6>
                                <div class="row g-3">
                                    <forms-text-field name="mothertongue" label="Native Language" v-model="employee.mothertongue" :error="errors.mothertongue ? errors.mothertongue[0] : ''" classes="col-12"></forms-text-field>
                                    <forms-select-field name="qualification" label="Top Qualification" v-model="employee.qualification" :error="errors.qualification ? errors.qualification[0] : ''" :options="[{key: 'No School', val: 'No School'}, {key: 'Primary', val: 'Primary'}, {key: 'Secondary', val: 'Secondary'}, {key: 'Higher secondary', val: 'Higher secondary'}, {key: 'Undergraduate', val: 'Undergraduate'}, {key: 'Graduate', val: 'Graduate'}, {key: 'Masters', val: 'Masters'}, {key: 'Doctorate', val: 'Doctorate'}, {key: 'Other', val: 'Other'}]" classes="col-12"></forms-select-field>
                                    <forms-text-field name="degree" label="Highest Educational Degree" v-model="employee.degree" :error="errors.degree ? errors.degree[0] : ''" placeholder="e.g. B.Tech, MBA" classes="col-12"></forms-text-field>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Form Action Bar (Full-Width Card Footer) -->
                <div class="card-footer bg-white py-3 px-4 border-top d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3">
                    <div v-if="employee.id" class="d-grid d-md-block">
                        <button v-if="!isDelete" class="btn btn-outline-danger btn-lg px-4 rounded-pill transition-all" @click="deleteItem()">
                            <i class="bi bi-person-dash me-2"></i> Terminate Account
                        </button>
                        <button v-else class="btn btn-danger btn-lg px-4 rounded-pill animate__animated animate__shakeX" @click="deleteNow()">
                            Confirm Removal
                        </button>
                    </div>
                    <div v-else class="d-none d-md-block"></div>

                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                        <button class="btn btn-light btn-lg px-4 rounded-pill shadow-none" @click="toggleForm">Discard Changes</button>
                        <forms-submit-button name="" v-model="loading" :label="employee.id ? 'Save Personnel Data' : 'Submit Enrollment'" @click="save()" classes="btn-lg px-5 shadow-sm rounded-pill"></forms-submit-button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Search & Staff Library -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white p-4 border-0 pb-2">
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-12 col-md">
                        <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
                            <i class="bi bi-person-lines-fill text-primary me-2 fs-5"></i>
                            Staff Inventory
                        </h5>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="search-container position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" class="form-control rounded-pill ps-5 py-2.5 border-0 bg-light shadow-none small" v-model="params.value" placeholder="Search by name, email, or employee code..." @keyup.enter="search()">
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Filters Panel -->
                <div class="row g-3 pt-3 border-top border-light-subtle align-items-end">
                    <!-- Status Filter -->
                    <div class="col-12 col-sm-4 col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1.5 d-flex align-items-center">
                            <i class="bi bi-person-check-fill text-primary me-1.5"></i>Status
                        </label>
                        <select class="form-select rounded-pill border-0 bg-light py-2 px-3 shadow-none small fw-medium" v-model="params.status" @change="search()">
                            <option value="">All Statuses</option>
                            <option value="current">Current Employees</option>
                            <option value="exited">Exited Staff</option>
                        </select>
                    </div>
                    <!-- Department Filter -->
                    <div class="col-12 col-sm-4 col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1.5 d-flex align-items-center">
                            <i class="bi bi-building-fill text-primary me-1.5"></i>Department
                        </label>
                        <select class="form-select rounded-pill border-0 bg-light py-2 px-3 shadow-none small fw-medium" v-model="params.department_id" @change="search()">
                            <option value="">All Departments</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.department }}</option>
                        </select>
                    </div>
                    <!-- Designation Filter -->
                    <div class="col-12 col-sm-4 col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1.5 d-flex align-items-center">
                            <i class="bi bi-briefcase-fill text-primary me-1.5"></i>Designation
                        </label>
                        <select class="form-select rounded-pill border-0 bg-light py-2 px-3 shadow-none small fw-medium" v-model="params.designation_id" @change="search()">
                            <option value="">All Designations</option>
                            <option v-for="desg in designations" :key="desg.id" :value="desg.id">{{ desg.designation }}</option>
                        </select>
                    </div>
                    <!-- Action Column (Download & Clear) -->
                    <div class="col-12 col-md-1 text-md-end text-start d-flex align-items-center justify-content-md-end justify-content-start gap-2">
                        <button 
                            type="button"
                            class="btn btn-outline-primary border-0 rounded-circle p-2 shadow-none hover-scale animate__animated animate__fadeIn" 
                            @click="openDownloadModal()" 
                            title="Export Staff Records"
                            style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; background-color: #f0f4ff;">
                            <i class="bi bi-cloud-arrow-down-fill text-primary fs-5"></i>
                        </button>
                        <button 
                            v-if="hasActiveFilters" 
                            type="button"
                            class="btn btn-outline-danger border-0 rounded-circle p-2 shadow-none hover-scale animate__animated animate__zoomIn" 
                            @click="clearFilters()" 
                            title="Clear All Filters"
                            style="width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; background-color: #fef2f2;">
                            <i class="bi bi-x-lg text-danger fw-bold"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light-subtle">
                        <tr class="text-uppercase small fw-bold text-muted border-bottom-0">
                            <th class="ps-4 py-3 cursor-pointer" @click="orderBy('id')">Staff ID <i class="bi bi-sort-down ms-1"></i></th>
                            <th class="cursor-pointer" @click="orderBy('first_name')">Personnel Profile</th>
                            <th>Job Function</th>
                            <th class="cursor-pointer" @click="orderBy('employee_code')">Code</th>
                            <th>Connectivity</th>
                            <th class="pe-4 text-end">Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="emp in employees" :key="emp.id" 
                            class="cursor-pointer transition-all hover-glow border-bottom border-light" 
                            :class="{ 'tr-exited': emp.doe }"
                            @click="edit(emp)">
                            <td class="ps-4">
                                <span class="badge bg-white text-muted border fw-mono">#{{ emp.id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="emp-avatar me-3" :class="[emp.doe ? 'bg-gradient-staff-exited text-white-50' : 'bg-gradient-staff']">
                                        {{ (emp.first_name && emp.first_name.length > 0) ? emp.first_name.charAt(0).toUpperCase() : '?' }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6">
                                            {{ emp.first_name }} {{ emp.last_name }}
                                            <span v-if="emp.doe" class="badge bg-danger-subtle text-danger border border-danger border-opacity-10 rounded-pill px-2 py-0.5 ms-2 align-middle fw-semibold" style="font-size: 0.7rem;">Exited</span>
                                        </div>
                                        <div class="small fw-semibold" :class="[emp.doe ? 'text-muted' : 'text-primary']">{{ (emp.employee_designation && emp.employee_designation.designation) ? emp.employee_designation.designation.designation : 'Draft Profile' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div v-if="emp.employee_department" class="small fw-medium text-muted">
                                    <i class="bi bi-building me-1"></i> {{ emp.employee_department.department.department }}
                                </div>
                                <div v-else class="text-muted opacity-50 tiny">Dept unassigned</div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-soft-info text-info border border-info border-opacity-25 px-3 py-2 fw-mono">
                                    {{ emp.employee_code || 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="connectivity-grid">
                                    <div class="small text-muted mb-1"><i class="bi bi-telephone-outbound me-2 opacity-75"></i> {{ emp.phone || '—' }}</div>
                                    <div class="small text-muted"><i class="bi bi-envelope-at me-2 opacity-75"></i> {{ emp.email || '—' }}</div>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                                    <a :href="'/employee/profile/'+emp.id" class="btn btn-white btn-sm px-3 border-end" title="Full Profile" @click.stop><i class="bi bi-person-vcard text-primary"></i></a>
                                    <button class="btn btn-white btn-sm px-3" title="Assign Details" @click.stop="edit(emp)"><i class="bi bi-pencil-square text-muted"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="employees.length === 0">
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-state py-5">
                                    <i class="bi bi-people-fill display-1 text-light-emphasis d-block mb-3 opacity-25"></i>
                                    <h6 class="text-muted fw-bold">No Personnel Records Matching Search</h6>
                                    <p class="text-muted small">Try adjusting your filters or search keywords.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card-footer bg-white p-4 border-0 text-center">
                <button class="btn btn-outline-dark rounded-pill px-5 transition-all hover-shadow-sm" :disabled="next_page_url == null" @click="fetch()">
                    <i v-if="loading" class="spinner-border spinner-border-sm me-2"></i>
                    <i v-else class="bi bi-chevron-bar-down me-2"></i>
                    Load Additional Staff
                </button>
            </div>
        </div>
    </div>

    <!-- Export Fields Custom Modal -->
    <transition name="fade-blur">
        <div v-if="isExportModal" class="custom-modal-overlay d-flex align-items-center justify-content-center" @click.self="closeDownloadModal">
            <div class="custom-modal-card card border-0 shadow-2xl rounded-4 overflow-hidden animate__animated animate__zoomIn">
                <div class="card-header bg-white py-3 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-cloud-arrow-down-fill me-2"></i>Export Staff Records</h5>
                        <p class="text-muted small mb-0 mt-0.5">Select the data fields to include in the exported report.</p>
                    </div>
                    <button class="btn-close border-0 bg-transparent shadow-none" @click="closeDownloadModal"></button>
                </div>
                
                <div class="card-body p-4 overflow-y-auto" style="max-height: 60vh;">
                    <!-- Select All / Clear Row -->
                    <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-3 rounded-3">
                        <span class="small fw-bold text-dark">{{ selectedFields.length }} of {{ fieldOptions.length }} fields selected</span>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-white border px-3 rounded-pill fw-semibold text-primary" @click="selectAllFields">Select All</button>
                            <button class="btn btn-sm btn-white border px-3 rounded-pill fw-semibold text-muted" @click="selectNoneFields">Clear All</button>
                        </div>
                    </div>
                    
                    <!-- Categorized Field Options -->
                    <div class="row g-4">
                        <div v-for="(options, category) in groupedFieldOptions" :key="category" class="col-12">
                            <h6 class="text-uppercase small fw-extrabold text-primary border-bottom pb-2 mb-2.5 d-flex align-items-center">
                                <i :class="getCategoryIcon(category)" class="me-2 text-primary opacity-75"></i>{{ category }}
                            </h6>
                            <div class="row g-2">
                                <div v-for="option in options" :key="option.key" class="col-6 col-sm-4">
                                    <div 
                                        class="field-pill-checkbox transition-all" 
                                        :class="{ 'active': selectedFields.includes(option.key) }"
                                        @click="toggleField(option.key)">
                                        <i :class="[selectedFields.includes(option.key) ? 'bi bi-check-circle-fill text-success' : 'bi bi-circle text-muted']" class="me-2 fs-6"></i>
                                        <span class="fw-medium small text-dark">{{ option.label }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer Actions -->
                <div class="card-footer bg-white py-3 px-4 border-top d-flex flex-wrap gap-2 justify-content-end align-items-center">
                    <button 
                        class="btn btn-light btn-lg px-3 rounded-pill fw-semibold small shadow-none" 
                        @click="closeDownloadModal">
                        Cancel
                    </button>
                    <button 
                        class="btn btn-purple btn-lg px-4 rounded-pill fw-bold small d-inline-flex align-items-center gap-2 shadow-sm border-0 text-white"
                        style="background: linear-gradient(135deg, #7d2ae8 0%, #00c4cc 100%);"
                        :disabled="exportLoading"
                        @click="triggerExport('canva_csv')"
                        title="Download Canva ICards CSV (photo, myname, designation, phone, employee_code, blood_group, dob, addr)">
                        <i v-if="exportLoading && exportType === 'canva_csv'" class="spinner-border spinner-border-sm"></i>
                        <i v-else class="bi bi-person-badge-fill"></i>
                        Canva ICard CSV
                    </button>
                    <button 
                        class="btn btn-info text-white btn-lg px-4 rounded-pill fw-bold small d-inline-flex align-items-center gap-2 shadow-sm border-0"
                        :disabled="selectedFields.length === 0 || exportLoading"
                        @click="triggerExport('csv')">
                        <i v-if="exportLoading && exportType === 'csv'" class="spinner-border spinner-border-sm"></i>
                        <i v-else class="bi bi-file-earmark-text-fill"></i>
                        Export CSV
                    </button>
                    <button 
                        class="btn btn-success btn-lg px-4 rounded-pill fw-bold small d-inline-flex align-items-center gap-2 shadow-sm border-0"
                        :disabled="selectedFields.length === 0 || exportLoading"
                        @click="triggerExport('excel')">
                        <i v-if="exportLoading && exportType === 'excel'" class="spinner-border spinner-border-sm"></i>
                        <i v-else class="bi bi-file-earmark-excel-fill"></i>
                        Export Excel
                    </button>
                    <button 
                        class="btn btn-danger btn-lg px-4 rounded-pill fw-bold small d-inline-flex align-items-center gap-2 shadow-sm border-0"
                        :disabled="selectedFields.length === 0 || exportLoading"
                        @click="triggerExport('pdf')">
                        <i v-if="exportLoading && exportType === 'pdf'" class="spinner-border spinner-border-sm"></i>
                        <i v-else class="bi bi-file-earmark-pdf-fill"></i>
                        Export PDF
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import axios from 'axios'
export default {
    props: {
        departments: {
            type: Array,
            default: () => []
        },
        designations: {
            type: Array,
            default: () => []
        }
    },

    data(){
        return {
            isForm: false,
            loading: false,
            isDelete: false,
            employee: {
                id: null,
                first_name: null,
                middle_name: null,
                last_name: null,
                employee_code: null,
                tagid: null,
                email: null,
                phone: null,
                doj: null,
                doe: null,
                dob: null,
                gender: null,
                blood_group: null,
                religion: null,
                cast: null,
                subcast: null,
                mothertongue: null,
                nationality: 'Indian',
                marital_status: null,
                qualification: null,
                degree: null,
                aadhar: null,
                pan: null,
                pf: null,
                old_pf: null,
                uan: null,
                old_uan: null,
                esic: null,
                old_esic: null,
            },
            errors: {},
            employees: [],
            next_page_url: null,
            current_page: 1,
            isExportModal: false,
            exportLoading: false,
            exportType: null,
            selectedFields: ['id', 'first_name', 'last_name', 'employee_code', 'email', 'phone', 'department', 'designation'],
            fieldOptions: [
                { key: 'id', label: 'Staff ID', category: 'Personal' },
                { key: 'first_name', label: 'First Name', category: 'Personal' },
                { key: 'middle_name', label: 'Middle Name', category: 'Personal' },
                { key: 'last_name', label: 'Last Name', category: 'Personal' },
                { key: 'gender', label: 'Gender', category: 'Personal' },
                { key: 'dob', label: 'Date of Birth', category: 'Personal' },
                { key: 'blood_group', label: 'Blood Group', category: 'Personal' },
                { key: 'marital_status', label: 'Marital Status', category: 'Personal' },
                { key: 'nationality', label: 'Nationality', category: 'Personal' },
                { key: 'religion', label: 'Religion', category: 'Personal' },
                
                { key: 'email', label: 'Email', category: 'Contact' },
                { key: 'phone', label: 'Phone', category: 'Contact' },
                
                { key: 'employee_code', label: 'Employee Code', category: 'Company' },
                { key: 'doj', label: 'Joining Date', category: 'Company' },
                { key: 'doe', label: 'Exit Date', category: 'Company' },
                { key: 'department', label: 'Department', category: 'Company' },
                { key: 'designation', label: 'Designation', category: 'Company' },
                { key: 'work_location', label: 'Work Location', category: 'Company' },
                
                { key: 'qualification', label: 'Qualification', category: 'Education' },
                { key: 'degree', label: 'Degree', category: 'Education' },
                
                { key: 'aadhar', label: 'Aadhar Card', category: 'Statutory' },
                { key: 'pan', label: 'PAN Card', category: 'Statutory' },
                { key: 'pf', label: 'PF Registration', category: 'Statutory' },
                { key: 'uan', label: 'UAN Account', category: 'Statutory' },
                { key: 'esic', label: 'ESIC Number', category: 'Statutory' }
            ],
            params: {
                key: null,
                value: null,
                by: 'id',
                order: 'desc',
                rows: 10,
                status: 'current',
                department_id: '',
                designation_id: '',
            },
            searchTimer: null
        };
    },

    computed: {
        hasActiveFilters() {
            return this.params.status !== 'current' || 
                   this.params.department_id !== '' || 
                   this.params.designation_id !== '' || 
                   (this.params.value && this.params.value.trim() !== '');
        },
        groupedFieldOptions() {
            const groups = {};
            this.fieldOptions.forEach(opt => {
                if (!groups[opt.category]) {
                    groups[opt.category] = [];
                }
                groups[opt.category].push(opt);
            });
            return groups;
        }
    },

    watch: {
        "params.value": function (val) {
            if (this.searchTimer) clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => {
                this.search();
            }, 500);
        },
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

        fetch(){
            let url = '/employee/employee_manager/fetch';
            if(this.next_page_url != null){
                url = this.next_page_url;
            }

            axios.get(url, {params: this.params}).then(res => {
                this.next_page_url = res.data.next_page_url;
                this.current_page = res.data.current_page;

                if(this.current_page == 1){
                    this.employees = res.data.data;
                } else {
                    res.data.data.forEach(item => {
                        this.employees.push(item);
                    });
                }
                this.loading = false;
            });
        },

        search(){
            this.current_page = 1;
            this.next_page_url = null;
            this.employees = [];
            this.fetch();
        },

        clearFilters() {
            this.params.value = '';
            this.params.status = 'current';
            this.params.department_id = '';
            this.params.designation_id = '';
            this.search();
        },

        openDownloadModal() {
            this.isExportModal = true;
        },
        closeDownloadModal() {
            this.isExportModal = false;
            this.exportLoading = false;
            this.exportType = null;
        },
        toggleField(key) {
            const idx = this.selectedFields.indexOf(key);
            if (idx > -1) {
                this.selectedFields.splice(idx, 1);
            } else {
                this.selectedFields.push(key);
            }
        },
        selectAllFields() {
            this.selectedFields = this.fieldOptions.map(opt => opt.key);
        },
        selectNoneFields() {
            this.selectedFields = [];
        },
        getCategoryIcon(cat) {
            switch(cat) {
                case 'Personal': return 'bi-person-fill';
                case 'Contact': return 'bi-chat-left-text-fill';
                case 'Company': return 'bi-briefcase-fill';
                case 'Education': return 'bi-mortarboard-fill';
                case 'Statutory': return 'bi-shield-lock-fill';
                default: return 'bi-collection-fill';
            }
        },
        triggerExport(type) {
            this.exportLoading = true;
            this.exportType = type;
            
            // Map selected fields to headings
            const headings = [];
            const fields = [];
            this.fieldOptions.forEach(opt => {
                if (this.selectedFields.includes(opt.key)) {
                    fields.push(opt.key);
                    headings.push(opt.label);
                }
            });
            
            const payload = {
                ...this.params,
                fields: fields,
                headings: headings
            };
            
            let url = '/employee/employee_manager/export/excel';
            let defaultFilename = 'employees_export.xlsx';

            if (type === 'pdf') {
                url = '/employee/employee_manager/export/pdf';
                defaultFilename = 'employees_export.pdf';
            } else if (type === 'csv') {
                url = '/employee/employee_manager/export/csv';
                defaultFilename = 'employees_export.csv';
            } else if (type === 'canva_csv') {
                url = '/employee/employee_manager/export/canva_csv';
                defaultFilename = 'canva_icard_employees.csv';
            }
                
            axios.post(url, payload, { responseType: 'blob' }).then(response => {
                const blob = new Blob([response.data], { type: response.headers['content-type'] });
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                
                let filename = defaultFilename;
                const disposition = response.headers['content-disposition'];
                if (disposition && disposition.indexOf('attachment') !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) { 
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }
                
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }).catch(err => {
                console.error('Export failed:', err);
            }).finally(() => {
                this.exportLoading = false;
                this.exportType = null;
                this.isExportModal = false;
            });
        },

        orderBy(col){
            this.params.by = col;
            this.params.order = this.params.order == 'asc' ? 'desc' : 'asc';
            this.search();
        },

        save(){
            if(this.employee.id == null){
                this.add();
            } else {
                this.update();
            }
        },

        reset(){
            Object.keys(this.employee).forEach(key => this.employee[key] = null);
            this.employee.nationality = 'Indian';
            this.errors = {};
            this.isDelete = false;
        },

        add(){
            this.loading = true;
            this.errors = {};
            axios.post('/employee/employee_manager/add', this.employee).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).catch(err => {
                if (err.response && err.response.status === 422) {
                    this.errors = err.response.data.errors;
                }
            }).finally(() => {
                this.loading = false;
            });
        },

        update(){
            this.loading = true;
            this.errors = {};
            axios.post('/employee/employee_manager/update', this.employee).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).catch(err => {
                if (err.response && err.response.status === 422) {
                    this.errors = err.response.data.errors;
                }
            }).finally(() => {
                this.loading = false;
            });
        },

        deleteItem(){
            this.isDelete = true;
        },

        deleteNow(){
            this.loading = true;
            this.errors = {};
            axios.post('/employee/employee_manager/delete', this.employee).then(res => {
                this.reset();
                this.search();
                this.isForm = false;
            }).finally(() => {
                this.loading = false;
            });
        },

        edit(item){
            this.reset();
            this.isForm = true;
            Object.keys(this.employee).forEach(key => {
                this.employee[key] = item[key];
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

    },

    created(){
        this.fetch();
    },

}
</script>

<style scoped>
.employee-manager-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.emp-avatar {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-weight: 700;
    color: white;
    font-size: 1.1rem;
}

.bg-gradient-staff {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
}

.bg-gradient-staff-exited {
    background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%) !important;
}

.border-top-primary {
    border-top: 4px solid #6366f1 !important;
}

.bg-soft-info { background-color: #e0f2fe; }
.text-info { color: #0ea5e9; }

.hover-glow:hover {
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
    background-color: #f1f5f9;
}

.transition-all { transition: all 0.25s ease; }
.tiny { font-size: 0.7rem; }
.fw-mono { font-family: ui-monospace, SFMono-Regular, monospace; }

/* Transitions */
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.4s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }

@keyframes shakeX {
    from, to { transform: translate3d(0, 0, 0); }
    10%, 30%, 50%, 70%, 90% { transform: translate3d(-10px, 0, 0); }
    20%, 40%, 60%, 80% { transform: translate3d(10px, 0, 0); }
}
.animate__shakeX { animation-name: shakeX; animation-duration: 0.5s; }

.bg-light-subtle { background-color: #f8fafc !important; }

.tr-exited {
    opacity: 0.55;
}

.tr-exited td {
    background-color: #f8fafc !important;
    color: #94a3b8 !important;
}

.tr-exited .text-dark,
.tr-exited .text-primary,
.tr-exited .fw-bold {
    color: #64748b !important;
}

.hover-scale {
    transition: transform 0.2s ease, background-color 0.2s ease;
}
.hover-scale:hover {
    transform: scale(1.1);
}

/* Custom Modal Overlay */
.custom-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(15, 23, 42, 0.35);
    backdrop-filter: blur(8px);
    z-index: 1050;
}

/* Custom Modal Card */
.custom-modal-card {
    width: 90%;
    max-width: 720px;
    background: white;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(226, 232, 240, 0.8);
}

/* Field Pill Checkbox */
.field-pill-checkbox {
    display: flex;
    align-items: center;
    padding: 0.55rem 0.8rem;
    background-color: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 50rem;
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
}
.field-pill-checkbox:hover {
    background-color: #f1f5f9;
    border-color: #cbd5e1;
}
.field-pill-checkbox.active {
    background-color: #ecfdf5;
    border-color: #10b981;
    color: #065f46;
}

/* Category Fonts */
.fw-extrabold {
    font-weight: 800;
}

/* Animation for Blur Transition */
.fade-blur-enter-active, .fade-blur-leave-active {
    transition: opacity 0.25s ease, backdrop-filter 0.25s ease;
}
.fade-blur-enter-from, .fade-blur-leave-to {
    opacity: 0;
    backdrop-filter: blur(0px);
}
</style>