<template>
    <div class="employee-portal animate__animated animate__fadeIn">
        
        <!-- Loading State -->
        <div v-if="loading && !loadingMore" class="d-flex flex-column align-items-center justify-content-center py-5 my-5">
            <div class="spinner-premium mb-3"></div>
            <p class="text-muted fw-bold animate__animated animate__pulse animate__infinite">Synchronizing your professional workspace...</p>
        </div>

        <div v-else>
            <!-- 1. OVERVIEW VIEW -->
            <div v-show="activeTab === 'overview'" class="tab-pane-content animate__animated animate__fadeIn">
                <div class="card border-0 shadow-premium rounded-4 overflow-hidden mb-4">
                    <div class="card-body p-0">
                        <div class="profile-hero p-5 text-white text-center">
                            <div class="avatar-container mb-4 position-relative d-inline-block">
                                <img v-if="employee && employee.employee_photo" :src="'/storage'+employee.employee_photo.media" class="rounded-circle border border-4 border-white shadow-lg profile-img-large">
                                <div v-else class="rounded-circle border border-4 border-white shadow-lg profile-img-large bg-light text-primary d-flex align-items-center justify-content-center">
                                    <i class="bi bi-person-fill display-4"></i>
                                </div>
                            </div>
                            <h2 class="fw-bold mb-1">Welcome back, {{ employee?.first_name }}! 👋</h2>
                            <p class="mb-0 text-white text-opacity-75 fs-5">
                                <span class="badge bg-white bg-opacity-25 rounded-pill px-3 me-2">#{{ employee?.employee_code }}</span>
                                {{ employee?.employee_designation?.designation?.designation || 'Staff Member' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <div class="row g-3">
                            <div v-for="stat in summaryStats" :key="stat.label" class="col-md-3">
                                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white hover-lift h-100">
                                    <div class="d-flex align-items-center gap-3">
                                        <div :class="stat.bgClass" class="p-3 rounded-4 shadow-sm">
                                            <i :class="stat.icon" class="fs-3"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.05em;">{{ stat.label }}</div>
                                            <div class="fs-3 fw-bold text-dark">{{ stat.value }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card border-0 shadow-premium rounded-4 p-4 h-100 bg-white d-flex flex-column justify-content-center border">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div class="mb-2">
                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold small text-uppercase" style="letter-spacing: 0.1em;">Current Schedule</span>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-2">
                                        <span v-if="current_shift && current_shift.working_shift">
                                            {{ current_shift.working_shift.name }}: {{ formatTime(current_shift.working_shift.in) }} - {{ formatTime(current_shift.working_shift.out) }}
                                        </span>
                                        <span v-else>Ready for your shift?</span>
                                    </h4>
                                    <p class="text-muted mb-4 small fw-semibold">
                                        <i class="bi bi-geo-alt-fill me-1"></i> {{ employee?.employee_work_location?.work_location?.location || 'Assigned Office' }} 
                                        • <i class="bi bi-info-circle-fill ms-2 me-1"></i> Your attendance is tracked in real-time.
                                    </p>
                                    
                                    <div v-if="current_shift && current_shift.employee_attendance.length > 0" class="today-punches mb-4 p-3 bg-light rounded-4 border border-dashed d-flex gap-4">
                                        <div class="punch-in">
                                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">First Punch</div>
                                            <div class="fw-bold text-success fs-5"><i class="bi bi-box-arrow-in-right me-1"></i>{{ current_shift.employee_attendance[0].tm }}</div>
                                        </div>
                                        <div v-if="current_shift.employee_attendance.length > 1" class="punch-out border-start ps-4">
                                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Latest Pulse</div>
                                            <div class="fw-bold text-primary fs-5"><i class="bi bi-box-arrow-left me-1"></i>{{ current_shift.employee_attendance[current_shift.employee_attendance.length - 1].tm }}</div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-play-circle-fill me-2"></i>Mark Active</button>
                                        <button @click="activeTab = 'attendance'" class="btn btn-outline-primary rounded-pill px-4 fw-bold">View Schedule</button>
                                    </div>
                                </div>
                                <div class="col-md-5 text-center d-none d-md-block">
                                    <i class="bi bi-clock-history text-primary opacity-10" style="font-size: 8rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-premium rounded-4 p-4 h-100 bg-white border celebrations-card overflow-hidden position-relative">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <div class="bg-warning bg-opacity-10 p-2 rounded-3 text-warning">
                                    <i class="bi bi-cake2-fill fs-4"></i>
                                </div>
                                <h5 class="fw-bold m-0 text-dark">Employee Birthdays</h5>
                            </div>

                            <div v-if="birthdays && birthdays.length > 0" class="birthday-scroller pe-2" style="max-height: 250px; overflow-y: auto;">
                                <div v-for="b in birthdays" :key="b.code" class="d-flex align-items-center justify-content-between p-2 rounded-3 hover-light transition-all mb-2 border-bottom border-light pb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle-sm bg-primary bg-opacity-10 text-primary fw-bold text-uppercase d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px; border-radius: 50%; font-size: 0.8rem;">
                                            <img v-if="b.photo" :src="'/storage'+b.photo" class="rounded-circle w-100 h-100 object-fit-cover shadow-sm">
                                            <span v-else>{{ b.name.split(' ').map(n => n[0]).join('') }}</span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small" style="font-size: 0.85rem;">{{ b.name }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">{{ b.code }}</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-primary border shadow-sm rounded-pill px-3 py-2 fw-bold" style="font-size: 0.75rem;">{{ b.birth_date }}</span>
                                </div>
                            </div>
                            <div v-else class="text-center py-5">
                                <i class="bi bi-stars fs-1 text-warning opacity-25"></i>
                                <p class="text-muted small mt-2">No celebrations this month.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Salary Breakup Section -->
                    <div v-if="salary_breakup" class="col-12 mt-4" data-aos="fade-up">
                        <div class="card border-0 shadow-premium rounded-4 overflow-hidden bg-white border">
                            <div class="card-header bg-primary bg-opacity-5 py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                                        <i class="bi bi-wallet2 fs-5"></i>
                                    </div>
                                    <h5 class="fw-bold m-0 text-dark">My Monthly Salary Breakup</h5>
                                </div>
                                <div class="badge bg-white text-primary border shadow-sm rounded-pill px-3 py-2 fw-bold">Current Cycle</div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-4">
                                    <div class="col-lg-5">
                                        <div class="salary-main-stats d-flex flex-column gap-3">
                                            <div class="p-3 rounded-4 bg-light border border-dashed text-center">
                                                <div class="small text-muted fw-bold text-uppercase mb-1" style="letter-spacing: 0.05em;">Monthly Gross Salary</div>
                                                <div class="fs-2 fw-bold text-dark">₹{{ formatCurrency(salary_breakup.gross) }}</div>
                                            </div>
                                            <div class="p-3 rounded-4 bg-primary bg-opacity-10 text-center border border-primary border-opacity-10">
                                                <div class="small text-primary fw-bold text-uppercase mb-1" style="letter-spacing: 0.05em;">Estimated Net Payable</div>
                                                <div class="display-6 fw-bold text-primary">₹{{ formatCurrency(salary_breakup.net) }}</div>
                                            </div>
                                            <div class="small text-muted italic text-center px-4">
                                                <i class="bi bi-info-circle me-1"></i> Net pay is estimated based on your current attendance and statutory rules.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-7 border-start ps-lg-5">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead>
                                                    <tr class="text-secondary small fw-bold text-uppercase">
                                                        <th class="border-0 ps-0">Earning Component</th>
                                                        <th class="border-0 text-end pe-0">Monthly Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="earn in salary_breakup.earnings" :key="earn.name">
                                                        <td class="ps-0 py-3">
                                                            <div class="fw-bold text-dark">{{ earn.name }}</div>
                                                        </td>
                                                        <td class="text-end pe-0 fw-bold text-dark">
                                                            ₹{{ formatCurrency(earn.amount) }}
                                                        </td>
                                                    </tr>
                                                    <tr class="table-light">
                                                        <td class="ps-0 fw-bold text-dark">Total Monthly Gross</td>
                                                        <td class="text-end pe-0 fw-bold text-primary fs-5">₹{{ formatCurrency(salary_breakup.gross) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. DEDICATED ATTENDANCE VIEW -->
            <div v-show="activeTab === 'attendance'" class="tab-pane-content animate__animated animate__fadeIn text-dark">

                <!-- Page Header -->
                <div class="hol-header rounded-4 p-4 mb-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h4 class="fw-bold m-0 text-dark"><i class="bi bi-calendar-check text-primary me-2"></i>Attendance Log</h4>
                        <p class="text-muted mb-0 mt-1 small">Your daily shift records, punch times and performance stats</p>
                    </div>
                    <!-- Month Navigator -->
                    <div class="d-flex align-items-center gap-2">
                        <button @click="changeDashboardMonth(-1)" class="btn btn-white border rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                            <i class="bi bi-chevron-left small"></i>
                        </button>
                        <div class="hol-month-pill px-4 py-2 rounded-pill fw-bold text-dark border bg-white shadow-sm" style="min-width:150px;text-align:center;">
                            {{ currentDashboardMonthName }}
                        </div>
                        <button @click="changeDashboardMonth(1)" class="btn btn-white border rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                            <i class="bi bi-chevron-right small"></i>
                        </button>
                    </div>
                </div>

                <!-- Status Filter Pills -->
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <button @click="attStatusFilter = 'All'"
                            :class="attStatusFilter === 'All' ? 'hol-pill-active' : 'hol-pill'"
                            class="btn rounded-pill px-4 py-2 fw-bold small border">
                        <i class="bi bi-grid-fill me-1"></i> All
                        <span class="ms-1 badge rounded-pill" :class="attStatusFilter === 'All' ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'">
                            {{ shifts.length }}
                        </span>
                    </button>
                    <button v-for="cat in attSummary" :key="cat.filter"
                            @click="attStatusFilter = cat.filter"
                            :class="attStatusFilter === cat.filter ? 'hol-pill-active' : 'hol-pill'"
                            class="btn rounded-pill px-4 py-2 fw-bold small border">
                        <i :class="cat.icon" class="me-1"></i> {{ cat.label }}
                        <span class="ms-1 badge rounded-pill" :class="attStatusFilter === cat.filter ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'">
                            {{ cat.count }}
                        </span>
                    </button>
                </div>

                <!-- Empty State -->
                <div v-if="filteredShifts.length === 0" class="text-center py-5 card border-0 shadow-sm rounded-4 bg-white">
                    <i class="bi bi-calendar2-x display-1 text-muted opacity-25 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">No records found</h5>
                    <p class="text-muted small">No {{ attStatusFilter === 'All' ? 'attendance data' : attStatusFilter.toLowerCase() + ' records' }} for {{ currentDashboardMonthName }}.</p>
                    <div><button @click="attStatusFilter = 'All'" class="btn btn-outline-primary rounded-pill px-4 fw-bold small">Show All</button></div>
                </div>

                <!-- Attendance Cards Grid -->
                <div v-else class="row g-3">
                    <div v-for="shift in filteredShifts" :key="shift.id" class="col-12 col-sm-6 col-xl-4">
                        <div class="hol-card rounded-4 border overflow-hidden h-100" :class="getAttCardNew(shift.status)">

                            <div class="d-flex align-items-stretch">
                                <!-- Date block -->
                                <div class="hol-date-block d-flex flex-column align-items-center justify-content-center px-3 py-3" :class="getAttDateBlock(shift.status)" style="min-width:72px;">
                                    <span class="fw-bold lh-1" style="font-size:1.9rem;">{{ getDayNum(shift.dt) }}</span>
                                    <span class="text-uppercase fw-bold" style="font-size:0.7rem;letter-spacing:1px;">{{ getMonthShort(shift.dt) }}</span>
                                    <span class="mt-1 opacity-75" style="font-size:0.65rem;">{{ getDayName(shift.dt).slice(0,3) }}</span>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow-1 p-3 d-flex flex-column gap-2">
                                    <!-- Status -->
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="badge rounded-pill px-3 py-1 fw-bold" :class="getStatusClass(shift.status)" style="font-size:0.72rem;">
                                            <i :class="getAttStatusIcon(shift.status)" class="me-1"></i>{{ shift.status }}
                                        </span>
                                        <span v-if="shift.lop > 0" class="badge bg-danger rounded-pill px-2 py-1 fw-bold" style="font-size:0.68rem;">LOP: {{ shift.lop }}d</span>
                                    </div>

                                    <!-- Punch Times -->
                                    <div class="d-flex flex-wrap gap-1 align-items-center">
                                        <i class="bi bi-fingerprint text-muted" style="font-size:0.8rem;"></i>
                                        <span v-for="p in shift.employee_attendance" :key="p.id"
                                              class="badge bg-white text-primary border font-monospace shadow-sm px-2 py-1" style="font-size:0.7rem;">{{ p.tm }}</span>
                                        <span v-if="shift.employee_attendance.length === 0" class="text-muted fst-italic" style="font-size:0.75rem;">No punches</span>
                                    </div>

                                    <!-- Late / Early / On Time -->
                                    <div class="d-flex flex-wrap gap-1">
                                        <span v-if="shift.late > 0" class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 fw-bold" style="font-size:0.68rem;">
                                            <i class="bi bi-clock-history me-1"></i>Late {{ shift.late }}m
                                        </span>
                                        <span v-if="shift.early > 0" class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 py-1 fw-bold" style="font-size:0.68rem;">
                                            <i class="bi bi-box-arrow-in-left me-1"></i>Early {{ shift.early }}m
                                        </span>
                                        <span v-if="!shift.late && !shift.early && shift.status === 'Present'" class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 fw-bold" style="font-size:0.68rem;">
                                            <i class="bi bi-check2-circle me-1"></i>On Time
                                        </span>
                                    </div>

                                    <!-- Audit badges -->
                                    <div class="d-flex flex-wrap gap-1 pt-1 border-top border-light">
                                        <span v-if="shift.leave"       class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1 fw-bold" style="font-size:0.65rem;"><i class="bi bi-envelope-paper-fill me-1"></i>LEAVE</span>
                                        <span v-if="shift.time_update" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 fw-bold" style="font-size:0.65rem;"><i class="bi bi-pencil-fill me-1"></i>FIX</span>
                                        <span v-if="shift.short_leave" class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1 fw-bold" style="font-size:0.65rem;"><i class="bi bi-hourglass-split me-1"></i>SHORT</span>
                                        <span v-if="shift.overtime"    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 fw-bold" style="font-size:0.65rem;"><i class="bi bi-plus-circle-fill me-1"></i>OT</span>
                                        <span v-if="shift.on_duty"     class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-15 rounded-pill px-2 py-1 fw-bold" style="font-size:0.65rem;"><i class="bi bi-briefcase-fill me-1"></i>OD</span>
                                        <span v-if="!shift.leave && !shift.time_update && !shift.short_leave && !shift.overtime && !shift.on_duty" class="text-muted fst-italic" style="font-size:0.7rem;">—</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- 3. COMPREHENSIVE PROFILE VIEW -->
            <div v-show="activeTab === 'profile'" class="tab-pane-content animate__animated animate__fadeIn text-dark">
                <div class="row g-4">
                    <!-- Identity Sidebar -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-premium rounded-4 p-4 text-center sticky-top" style="top: 2rem; z-index: 10;">
                            <div class="position-relative d-inline-block mx-auto mb-4">
                                <img v-if="employee && employee.employee_photo" :src="'/storage'+employee.employee_photo.media" class="rounded-circle border border-4 border-primary shadow profile-img-xl">
                                <div class="status-indicator bg-success"></div>
                            </div>
                            <h4 class="fw-bold mb-1">{{ employee?.first_name }} {{ (employee?.middle_name ? employee.middle_name + ' ' : '') }}{{ employee?.last_name }}</h4>
                            <p class="text-muted fw-semibold mb-3">{{ employee?.employee_designation?.designation?.designation }}</p>
                            
                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">#{{ employee?.employee_code }}</span>
                                <span class="badge bg-light text-dark px-3 py-2 rounded-pill fw-bold border">{{ employee?.gender }}</span>
                            </div>

                            <div class="text-start border-top pt-4">
                                <div class="profile-info-item mb-3">
                                    <div class="small text-muted text-uppercase fw-bold mb-1">Official Email</div>
                                    <div class="fw-bold text-dark"><i class="bi bi-envelope-at-fill text-primary me-2"></i>{{ employee?.email }}</div>
                                </div>
                                <div class="profile-info-item mb-3">
                                    <div class="small text-muted text-uppercase fw-bold mb-1">Mobile Contact</div>
                                    <div class="fw-bold text-dark"><i class="bi bi-phone-fill text-success me-2"></i>{{ employee?.phone || 'N/A' }}</div>
                                </div>
                                <div class="profile-info-item mb-0">
                                    <div class="small text-muted text-uppercase fw-bold mb-1">Service Tenure</div>
                                    <div class="fw-bold text-primary"><i class="bi bi-award-fill text-warning me-2"></i>{{ calculateTenure(employee?.doj) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Modules -->
                    <div class="col-lg-8">
                        <!-- Professional Section -->
                        <div class="card border-0 shadow-premium rounded-4 p-4 mb-4">
                            <h5 class="fw-bold mb-4 border-start border-4 border-primary ps-3">Professional Blueprint</h5>
                            <div class="row g-4 text-dark">
                                <div class="col-md-6"><div class="p-3 bg-light rounded-4"><div class="small text-muted fw-bold mb-1">Department</div><div class="fw-bold fs-5">{{ employee?.employee_department?.department?.department || 'N/A' }}</div></div></div>
                                <div class="col-md-6"><div class="p-3 bg-light rounded-4"><div class="small text-muted fw-bold mb-1">Work Location</div><div class="fw-bold fs-5">{{ employee?.employee_work_location?.work_location?.location || 'Main' }}</div></div></div>
                                <div class="col-md-4 col-6"><div class="small text-muted fw-bold uppercase-tracking">Joined Date</div><div class="fw-bold">{{ formatDateDetailed(employee?.doj) }}</div></div>
                                <div class="col-md-4 col-6"><div class="small text-muted fw-bold uppercase-tracking">Employment ID</div><div class="fw-bold text-primary">{{ employee?.employee_code }}</div></div>
                                <div class="col-md-4 col-6"><div class="small text-muted fw-bold uppercase-tracking">Tag Access ID</div><div class="fw-bold">{{ employee?.tagid || 'N/A' }}</div></div>
                            </div>
                        </div>

                        <!-- Personal & Background Section -->
                        <div class="card border-0 shadow-premium rounded-4 p-4 mb-4">
                            <h5 class="fw-bold mb-4 border-start border-4 border-warning ps-3">Personal & Background</h5>
                            <div class="row g-4 text-dark">
                                <div class="col-md-4 col-6"><div class="small text-muted fw-bold uppercase-tracking">Birth Date</div><div class="fw-bold">{{ formatDateDetailed(employee?.dob) }}</div></div>
                                <div class="col-md-4 col-6"><div class="small text-muted fw-bold uppercase-tracking">Blood Group</div><div class="fw-bold text-danger"><i class="bi bi-droplet-fill me-1"></i>{{ employee?.blood_group || 'N/A' }}</div></div>
                                <div class="col-md-4 col-6"><div class="small text-muted fw-bold uppercase-tracking">Marital Status</div><div class="fw-bold">{{ employee?.marital_status || 'N/A' }}</div></div>
                                <div class="col-md-4 col-6"><div class="small text-muted fw-bold uppercase-tracking">Mother Tongue</div><div class="fw-bold">{{ employee?.mothertongue || 'N/A' }}</div></div>
                                <div class="col-md-4 col-6"><div class="small text-muted fw-bold uppercase-tracking">Nationality</div><div class="fw-bold">{{ employee?.nationality || 'Indian' }}</div></div>
                                <div class="col-md-4 col-6"><div class="small text-muted fw-bold uppercase-tracking">Religion / Caste</div><div class="fw-bold">{{ employee?.religion }} / {{ employee?.cast }}</div></div>
                            </div>
                        </div>

                        <!-- Academic Section -->
                        <div class="card border-0 shadow-premium rounded-4 p-4 mb-4">
                            <h5 class="fw-bold mb-4 border-start border-4 border-info ps-3">Academic Foundation</h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 p-3 border rounded-4 bg-white shadow-sm">
                                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle"><i class="bi bi-mortarboard-fill fs-4"></i></div>
                                        <div><div class="small text-muted fw-bold">Highest Qualification</div><div class="fw-bold text-dark fs-5">{{ employee?.qualification || 'Graduate' }}</div></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 p-3 border rounded-4 bg-white shadow-sm">
                                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle"><i class="bi bi-award-fill fs-4"></i></div>
                                        <div><div class="small text-muted fw-bold">Primary Degree</div><div class="fw-bold text-dark fs-5">{{ employee?.degree || 'N/A' }}</div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statutory Section -->
                        <div class="card border-0 shadow-premium rounded-4 p-4 mb-4">
                            <h5 class="fw-bold mb-4 border-start border-4 border-danger ps-3">Statutory Compliance</h5>
                            <div class="row g-3">
                                <div v-for="cid in [{l:'Aadhar ID', v:employee?.aadhar}, {l:'PAN Card', v:employee?.pan}, {l:'PF / UAN', v:employee?.uan}, {l:'ESIC ID', v:employee?.esic}]" :key="cid.l" class="col-md-6 col-lg-3">
                                    <div class="p-3 border rounded-4 bg-light"><div class="small text-muted fw-bold mb-1">{{ cid.l }}</div><div class="fw-bold font-monospace text-dark">{{ cid.v || 'Not Linked' }}</div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Hub -->
                        <div v-if="employee?.employee_bank" class="card border-0 shadow-premium rounded-4 p-4 mb-4 bg-primary bg-opacity-5 border border-primary border-opacity-10">
                            <h5 class="fw-bold mb-4 border-start border-4 border-primary ps-3">Financial Hub</h5>
                            <div class="row g-4 align-items-center">
                                <div class="col-md-7">
                                    <div class="display-6 fw-bold text-dark mb-2">{{ employee.employee_bank.bank_name }}</div>
                                    <div class="fs-4 text-primary fw-bold font-monospace mb-1">ACC: {{ employee.employee_bank.acc_no }}</div>
                                    <div class="fw-semibold text-muted">IFSC: {{ employee.employee_bank.ifsc_code }}</div>
                                </div>
                                <div class="col-md-5 text-md-end">
                                    <div class="bg-white p-3 rounded-4 shadow-sm border d-inline-block text-start">
                                        <div class="small text-muted fw-bold mb-1">Status</div>
                                        <div class="fw-bold text-success"><i class="bi bi-check-circle-fill me-2"></i>Primary Salary Account</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Residential Settlement -->
                        <div v-if="employee?.employee_address" class="card border-0 shadow-premium rounded-4 p-4">
                            <h5 class="fw-bold mb-4 border-start border-4 border-success ps-3">Residential Settlement</h5>
                            <div class="p-3 bg-light rounded-4">
                                <div class="small text-muted fw-bold mb-2">CURRENT RESIDENCE</div>
                                <div class="fw-bold text-dark fs-5">{{ employee.employee_address.current_address }}</div>
                                <div class="fw-semibold text-muted">{{ employee.employee_address.current_city }}, {{ employee.employee_address.current_state }} - {{ employee.employee_address.current_pin }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. HOLIDAY VIEW -->
            <div v-show="activeTab === 'holidays'" class="tab-pane-content animate__animated animate__fadeIn text-dark">

                <!-- Page Header -->
                <div class="hol-header rounded-4 p-4 mb-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h4 class="fw-bold m-0 text-dark"><i class="bi bi-calendar-heart text-danger me-2"></i>Holidays & Off Days</h4>
                        <p class="text-muted mb-0 mt-1 small">Your scheduled breaks, weekoffs and special days</p>
                    </div>
                    <!-- Month Navigator -->
                    <div class="d-flex align-items-center gap-2">
                        <button @click="prevMonth" class="btn btn-white border rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                            <i class="bi bi-chevron-left small"></i>
                        </button>
                        <div class="hol-month-pill px-4 py-2 rounded-pill fw-bold text-dark border bg-white shadow-sm" style="min-width:150px;text-align:center;">
                            {{ currentViewMonthName }}
                        </div>
                        <button @click="nextMonth" class="btn btn-white border rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                            <i class="bi bi-chevron-right small"></i>
                        </button>
                        <button v-if="viewDate.getMonth() !== new Date().getMonth() || viewDate.getFullYear() !== new Date().getFullYear()"
                                @click="goToCurrentMonth"
                                class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1">
                            Today
                        </button>
                    </div>
                </div>

                <!-- Summary Pills -->
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <button @click="holidayCategoryFilter = 'All'"
                            :class="holidayCategoryFilter === 'All' ? 'hol-pill-active' : 'hol-pill'"
                            class="btn rounded-pill px-4 py-2 fw-bold small border">
                        <i class="bi bi-grid-fill me-1"></i> All
                        <span class="ms-1 badge rounded-pill" :class="holidayCategoryFilter === 'All' ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'">
                            {{ currentMonthHolidays.length }}
                        </span>
                    </button>
                    <button v-for="cat in holidaySummary" :key="cat.filter"
                            @click="holidayCategoryFilter = cat.filter"
                            :class="holidayCategoryFilter === cat.filter ? 'hol-pill-active' : 'hol-pill'"
                            class="btn rounded-pill px-4 py-2 fw-bold small border">
                        <i :class="cat.icon" class="me-1"></i> {{ cat.label }}
                        <span class="ms-1 badge rounded-pill" :class="holidayCategoryFilter === cat.filter ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'">
                            {{ cat.count }}
                        </span>
                    </button>
                </div>

                <!-- Empty State -->
                <div v-if="filteredHolidays.length === 0" class="text-center py-5 card border-0 shadow-sm rounded-4 bg-white">
                    <i class="bi bi-calendar2-x display-1 text-muted opacity-25 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">No entries found</h5>
                    <p class="text-muted small">No {{ holidayCategoryFilter === 'All' ? 'holidays' : holidayCategoryFilter.toLowerCase() + 's' }} scheduled for {{ currentViewMonthName }}.</p>
                    <div><button @click="holidayCategoryFilter = 'All'" class="btn btn-outline-primary rounded-pill px-4 fw-bold small">Show All</button></div>
                </div>

                <!-- Holiday Cards Grid -->
                <div v-else class="row g-3">
                    <div v-for="day in filteredHolidays" :key="day.id" class="col-12 col-sm-6 col-xl-4">
                        <div class="hol-card rounded-4 border p-0 overflow-hidden h-100" :class="getHolCardClass(day.day_type)">

                            <!-- Colour stripe + date block -->
                            <div class="d-flex align-items-stretch">
                                <div class="hol-date-block d-flex flex-column align-items-center justify-content-center px-3 py-3" :class="getHolDateBlockClass(day.day_type)" style="min-width:72px;">
                                    <span class="fw-bold lh-1" style="font-size:1.9rem;">{{ getDayNum(day.special_day) }}</span>
                                    <span class="text-uppercase fw-bold" style="font-size:0.7rem;letter-spacing:1px;">{{ getMonthShort(day.special_day) }}</span>
                                    <span class="mt-1 opacity-75" style="font-size:0.65rem;">{{ getDayName(day.special_day).slice(0,3) }}</span>
                                </div>

                                <div class="flex-grow-1 p-3 d-flex flex-column justify-content-between gap-2">
                                    <!-- Name -->
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:0.95rem;line-height:1.3;">{{ day.remark || 'Day Off' }}</div>
                                        <div class="text-muted small mt-1">{{ getDayName(day.special_day) }}</div>
                                    </div>
                                    <!-- Badge -->
                                    <div>
                                        <span class="badge rounded-pill px-3 py-1 fw-bold" :class="getHolidayBadgeClassPlain(day.day_type)" style="font-size:0.7rem;">
                                            <i :class="getHolidayIcon(day.day_type)" class="me-1"></i>{{ day.day_type }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            <!-- 5. PAYSLIPS VIEW -->
            <div v-show="activeTab === 'payslips'" class="tab-pane-content animate__animated animate__fadeIn text-dark">
                <div class="card border-0 shadow-premium rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-4 px-4 border-0 d-flex justify-content-between align-items-center"><h5 class="fw-bold m-0 text-dark">Payroll Archive</h5><span class="badge bg-light text-primary border rounded-pill px-3">Row interaction enabled</span></div>
                    <div class="card-body p-4 pt-0">
                        <div class="accordion accordion-flush" id="payslipAccordion">
                            <div v-for="pay in payslips" :key="pay.id" class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                                <h2 class="accordion-header">
                                    <button @click="togglePayslip(pay)" class="accordion-button collapsed bg-white p-3 border-0 transition-all rounded-4" type="button">
                                        <div class="row align-items-center g-3 w-100 me-2">
                                            <div class="col-md-4"><div class="d-flex align-items-center gap-3"><div class="bg-primary bg-opacity-10 w-50px h-50px rounded-circle d-flex align-items-center justify-content-center text-primary"><i class="bi bi-cash-stack fs-4"></i></div><div><h6 class="fw-bold text-dark mb-0 text-uppercase">{{ pay.payroll?.payroll_name }}</h6><p class="small text-muted mb-0">Monthly Statement</p></div></div></div>
                                            <div class="col-6 col-md-2 text-md-center"><div class="small text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Gross</div><div class="fw-bold">₹{{ formatCurrency(pay.gross_salary) }}</div></div>
                                            <div class="col-6 col-md-2 text-md-center"><div class="small text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Net Payout</div><div class="fw-bold text-primary">₹{{ formatCurrency(pay.net_payable_amount) }}</div></div>
                                            <div class="col-md-4 text-md-end"><div class="d-flex gap-2 justify-content-md-end"><button @click.stop="togglePayslip(pay)" class="btn btn-light btn-sm rounded-pill px-3 fw-bold border"><i class="bi bi-eye me-1"></i> {{ pay.expanded ? 'Hide' : 'View' }}</button><a :href="'/pdf/single_payslip/'+pay.id" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-sm"><i class="bi bi-download me-1"></i> Download</a></div></div>
                                        </div>
                                    </button>
                                </h2>
                                <div class="accordion-collapse collapse" :class="{ 'show': pay.expanded }">
                                    <div class="accordion-body bg-light bg-opacity-10 p-4 border-top">
                                        <div v-if="pay.details" class="details-container animate__animated animate__fadeIn border bg-white rounded-4 shadow-sm p-4">
                                            <!-- Payslip Header -->
                                            <div class="payslip-inner-header d-flex flex-wrap justify-content-between align-items-center mb-4 pb-4 border-bottom">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar-circle-lg bg-primary bg-opacity-10 text-primary fw-bold fs-4 d-flex align-items-center justify-content-center" style="width: 55px; height: 55px; border-radius: 50%;">
                                                        {{ initials(pay.details.employee?.first_name) }}
                                                    </div>
                                                    <div>
                                                        <h5 class="fw-bold mb-0 text-dark">{{ pay.details.employee?.first_name }} {{ pay.details.employee?.last_name }}</h5>
                                                        <p class="small text-muted mb-0 fw-semibold">{{ pay.details.employee?.employee_code }} • {{ pay.details.employee?.employee_designation?.designation?.designation }}</p>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-5 text-end mt-3 mt-md-0 align-items-center">
                                                    <div class="stat-group">
                                                        <span class="small fw-bold text-muted text-uppercase d-block mb-1 opacity-75" style="font-size: 0.6rem;">PAYABLE DAYS</span>
                                                        <div class="small fw-bold text-dark"><i class="bi bi-calendar-check text-success me-1"></i> <span class="fs-6">{{ pay.details.payroll_employee_attendances?.total_days || 0 }} Days</span></div>
                                                    </div>
                                                    <div class="stat-group">
                                                        <span class="small fw-bold text-muted text-uppercase d-block mb-1 opacity-75" style="font-size: 0.6rem;">NET SALARY</span>
                                                        <h5 class="fw-bold text-primary mb-0">₹ {{ formatCurrency(pay.net_payable_amount) }}</h5>
                                                    </div>
                                                    <button @click="pay.expanded = false" class="btn btn-outline-secondary btn-sm rounded-pill px-3 border-opacity-25 d-flex align-items-center gap-2">
                                                        <span class="fw-bold">Hide Details</span>
                                                        <i class="bi bi-chevron-up small"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="row g-4 mb-4">
                                                <!-- Earnings Column -->
                                                <div class="col-lg-6">
                                                    <div class="card border-0 bg-light bg-opacity-25 rounded-4 h-100 p-3 border">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="fw-bold m-0 text-dark">Earnings</h6>
                                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small fw-bold" style="font-size: 0.7rem;">₹ {{ formatCurrency(pay.details.total_earning) }}</span>
                                                        </div>
                                                        <div class="earning-list">
                                                            <div v-for="item in pay.details.payroll_employee_earnings" :key="item.id" class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-10 last-border-0">
                                                                <span class="text-muted small fw-semibold">{{ item.name_in_payslip }}</span>
                                                                <span class="fw-bold text-dark small font-monospace">₹ {{ formatCurrency(item.actual_payable_amount) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Deductions Column -->
                                                <div class="col-lg-6">
                                                    <div class="card border-0 bg-light bg-opacity-25 rounded-4 h-100 p-3 border">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="fw-bold m-0 text-dark">Deductions</h6>
                                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small fw-bold" style="font-size: 0.7rem;">₹ {{ formatCurrency(pay.details.gross_deduction) }}</span>
                                                        </div>
                                                        <div class="deduction-list">
                                                            <div v-for="item in pay.details.payroll_employee_deductions" :key="item.id" class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-10 last-border-0">
                                                                <span class="text-muted small fw-semibold">{{ item.name_in_payslip }}</span>
                                                                <span class="fw-bold text-dark small font-monospace">₹ {{ formatCurrency(item.actual_payable_amount) }}</span>
                                                            </div>
                                                            <div v-if="pay.details.gross_deduction == 0" class="text-center py-4 text-muted small italic opacity-50">No deductions.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Summary Footer -->
                                            <div class="row g-3 align-items-stretch mb-4">
                                                <div class="col-lg-8">
                                                    <div class="card border-light rounded-4 h-100 p-3 bg-white">
                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <div class="small text-muted fw-bold text-uppercase mb-1 opacity-75" style="font-size: 0.6rem;">BANK ACCOUNT</div>
                                                                <div class="fw-bold text-primary small">{{ pay.details.employee?.employee_bank?.bank_name || 'N/A' }}</div>
                                                                <div class="small fw-bold text-dark font-monospace">{{ pay.details.employee?.employee_bank?.acc_no || '--------' }}</div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="small text-muted fw-bold text-uppercase mb-1 opacity-75" style="font-size: 0.6rem;">EPF ACCOUNT</div>
                                                                <div class="fw-bold text-dark small">{{ pay.details.employee?.pf || 'NA' }}</div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="small text-muted fw-bold text-uppercase mb-1 opacity-75" style="font-size: 0.6rem;">JOINED DATE</div>
                                                                <div class="fw-bold text-dark small">{{ formatDateDetailed(pay.details.employee?.doj) }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="card border-0 bg-primary bg-opacity-5 rounded-4 h-100 p-3 d-flex flex-column align-items-center justify-content-center text-center">
                                                        <div class="small text-muted fw-bold text-uppercase mb-1" style="font-size: 0.6rem;">NET PAYABLE AMOUNT</div>
                                                        <h4 class="fw-bold text-dark mb-1">₹ {{ formatCurrency(pay.net_payable_amount) }} /-</h4>
                                                        <div class="small text-muted italic px-2 fw-semibold" style="font-size: 0.75rem;">"{{ pay.wordAmt }} Rupees Only"</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-center py-5">
                                            <div class="spinner-premium mb-3"></div>
                                            <p class="text-muted fw-bold">Synchronizing breakdown...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-if="hasMorePayslips" class="col-12 text-center mt-3"><button @click="loadMorePayslips" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold" :disabled="loadingMore">Load More Historical Records</button></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. DIRECT CHANNELS (REQUESTS) VIEW -->
            <div v-show="activeTab === 'requests'" class="tab-pane-content animate__animated animate__fadeIn text-dark">
                <div class="requests-hero p-5 rounded-4 mb-4 text-white shadow-premium overflow-hidden position-relative">
                    <div class="position-relative z-index-1">
                        <h2 class="fw-bold mb-2">Direct Channels Hub</h2>
                        <p class="mb-0 opacity-75 fs-5">Submit and track your professional requests with ease.</p>
                    </div>
                    <i class="bi bi-lightning-charge-fill position-absolute end-0 bottom-0 opacity-10" style="font-size: 15rem; margin-right: -2rem; margin-bottom: -4rem;"></i>
                </div>

                <!-- 6.1 LEAVE STATISTICS -->
                <div v-if="leave_stats && leave_stats.length > 0" class="row g-3 mb-5 animate__animated animate__fadeInDown px-1 text-dark">
                    <div class="col-12"><h5 class="fw-bold text-dark opacity-75 mb-1"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>My Leave Balance ({{ new Date().getFullYear() }})</h5></div>
                    <div v-for="stat in leave_stats" :key="stat.type" class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white hover-lift h-100 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">{{ stat.type }}</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2" style="font-size: 0.65rem;">{{ stat.balance }} DP Balance</span>
                            </div>
                            <div class="progress mb-2 bg-light" style="height: 6px; border-radius: 10px;">
                                <div class="progress-bar bg-primary" :style="{ width: (stat.used / stat.total * 100) + '%' }"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted fw-bold" style="font-size: 0.7rem;">
                                <span>Used: {{ stat.used }}</span>
                                <span>Total: {{ stat.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div v-for="btn in [{t:'leave', l:'Apply for Leave', d:'Request formal absence from work.', i:'bi-send-plus-fill', c:'bg-primary'}, {t:'time', l:'Time Update', d:'Correct your daily punch timings.', i:'bi-clock-fill', c:'bg-info'}, {t:'short', l:'Short Leave', d:'Brief absence for personal work.', i:'bi-hourglass-split', c:'bg-secondary'}, {t:'overtime', l:'Overtime Request', d:'Approval for extra working hours.', i:'bi-plus-circle-fill', c:'bg-success'}]" :key="btn.t" class="col-md-6 col-lg-3">
                        <div @click="openForm(btn.t)" class="card border-0 shadow-sm rounded-4 p-4 hover-lift h-100 bg-white cursor-pointer text-center">
                            <div :class="btn.c" class="bg-opacity-10 p-3 rounded-circle d-inline-flex mx-auto mb-3" style="width: 70px; height: 70px; align-items: center; justify-content: center;">
                                <i :class="[btn.i, btn.c.replace('bg-', 'text-')]" class="fs-2"></i>
                            </div>
                            <h5 class="fw-bold mb-2">{{ btn.l }}</h5>
                            <p class="text-muted small mb-0">{{ btn.d }}</p>
                        </div>
                    </div>
                </div>

                <!-- REQUEST HISTORY CARDS -->
                <div class="mt-5 animate__animated animate__fadeInUp text-dark">
                    <div class="hol-header rounded-4 p-4 mb-4 d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <h5 class="fw-bold m-0 text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Request History</h5>
                            <p class="text-muted mb-0 mt-1 small">All your submitted requests and their current status</p>
                        </div>
                        <span class="badge bg-white border shadow-sm text-dark rounded-pill px-3 py-2 fw-bold">{{ all_requests.length }} Total</span>
                    </div>

                    <!-- Empty State -->
                    <div v-if="all_requests.length === 0" class="text-center py-5 card border-0 shadow-sm rounded-4 bg-white">
                        <i class="bi bi-folder-x display-1 text-muted opacity-25 d-block mb-3"></i>
                        <h5 class="fw-bold text-dark">No requests yet</h5>
                        <p class="text-muted small">Your submitted requests will appear here.</p>
                    </div>

                    <!-- Cards Grid -->
                    <div v-else class="row g-3">
                        <div v-for="req in all_requests" :key="req.type + req.id" class="col-12 col-sm-6 col-xl-4">
                            <div class="hol-card rounded-4 border overflow-hidden h-100" :class="getReqCardClass(req.type)">
                                <div class="d-flex align-items-stretch">

                                    <!-- Icon block -->
                                    <div class="hol-date-block d-flex flex-column align-items-center justify-content-center px-3 py-3 gap-1" :class="getReqIconBlock(req.type)" style="min-width:68px;">
                                        <i :class="getRequestIcon(req.type)" style="font-size:1.5rem;"></i>
                                        <span class="text-uppercase fw-bold text-center lh-1" style="font-size:0.58rem;letter-spacing:0.8px;">{{ req.type }}</span>
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-grow-1 p-3 d-flex flex-column gap-2">

                                        <!-- Date + Status -->
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <span class="fw-bold text-dark" style="font-size:0.9rem;">{{ req.date_display || '—' }}</span>
                                            <span :class="getRequestBadgeClass(req.status)" class="badge rounded-pill px-2 py-1 fw-bold text-uppercase flex-shrink-0" style="font-size:0.62rem;letter-spacing:0.5px;">
                                                <i :class="getReqStatusIcon(req.status)" class="me-1"></i>{{ req.status }}
                                            </span>
                                        </div>

                                        <!-- Time detail -->
                                        <div v-if="req.type === 'Time Update'" class="small text-muted fw-semibold">
                                            <i class="bi bi-clock me-1"></i>{{ formatTime(req.in_time) }} – {{ formatTime(req.out_time) }}
                                        </div>
                                        <div v-if="req.type === 'Short Leave'" class="small text-muted fw-semibold">
                                            <i class="bi bi-clock me-1"></i>{{ formatTime(req.from_time || req.in_time) }} – {{ formatTime(req.to_time || req.out_time) }}
                                        </div>
                                        <div v-if="req.type === 'Overtime'" class="small text-muted fw-semibold">
                                            <i class="bi bi-hourglass-top me-1"></i>{{ req.hrs }} Hours
                                        </div>

                                        <!-- Reason -->
                                        <div class="small text-muted fst-italic text-truncate" :title="req.reason || req.note" style="font-size:0.78rem;">
                                            {{ req.reason || req.note || 'No description provided' }}
                                        </div>

                                        <!-- Footer: leave type + requested on -->
                                        <div class="d-flex align-items-center justify-content-between pt-1 border-top border-light flex-wrap gap-1">
                                            <span v-if="req.type === 'Leave' && req.leave_master" class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1 fw-bold" style="font-size:0.62rem;">{{ req.leave_master.leave_type }}</span>
                                            <span v-else class="text-transparent" style="font-size:0.62rem;">–</span>
                                            <span class="text-muted fw-bold" style="font-size:0.68rem;"><i class="bi bi-send me-1"></i>{{ formatDateDetailed(req.created_at) }}</span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Modal -->
        <div v-if="showFormModal" class="modal-overlay" @click.self="closeForm">
            <div class="custom-modal animate__animated animate__zoomIn">
                <div class="modal-header-custom">
                    <h5 class="mb-0 text-white">{{ formTitles[currentFormType] }}</h5>
                    <button class="btn-close-custom" @click="closeForm"><i class="bi bi-x"></i></button>
                </div>
                <div class="modal-body-custom">
                    <!-- LEAVE FORM -->
                    <div v-if="currentFormType === 'leave'" class="form-grid text-white">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase opacity-75 mb-1">Leave Type</label>
                            <select v-model="form.leave_master_id" class="form-select bg-dark border-secondary text-white rounded-3">
                                <option :value="null">Select Type</option>
                                <option v-for="t in leave_types" :key="t.id" :value="t.id">{{ t.leave_type }}</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold text-uppercase opacity-75 mb-1">From Date</label>
                                <input type="date" v-model="form.from" class="form-control bg-dark border-secondary text-white rounded-3">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold text-uppercase opacity-75 mb-1">To Date</label>
                                <input type="date" v-model="form.to" class="form-control bg-dark border-secondary text-white rounded-3">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-12 mb-3">
                                <label class="small fw-bold text-uppercase opacity-75 mb-1">Is Half Day?</label>
                                <select v-model="form.is_halfday" class="form-select bg-dark border-secondary text-white rounded-3">
                                    <option value="No">No</option>
                                    <option value="Yes">Yes</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase opacity-75 mb-1">Reason</label>
                            <textarea v-model="form.reason" class="form-control bg-dark border-secondary text-white rounded-3" rows="3" placeholder="Explain your absence..."></textarea>
                        </div>
                    </div>

                    <!-- TIME UPDATE FORM -->
                    <div v-if="currentFormType === 'time'" class="form-grid text-white">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase opacity-75 mb-1">Date</label>
                            <input type="date" v-model="form.on_date" @change="handleDateChange" class="form-control bg-dark border-secondary text-white rounded-3">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold text-uppercase opacity-75 mb-1">In Time</label>
                                <input type="time" v-model="form.in_time" class="form-control bg-dark border-secondary text-white rounded-3">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold text-uppercase opacity-75 mb-1">Out Time</label>
                                <input type="time" v-model="form.out_time" class="form-control bg-dark border-secondary text-white rounded-3">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase opacity-75 mb-1">Reason</label>
                            <textarea v-model="form.reason" class="form-control bg-dark border-secondary text-white rounded-3" rows="3" placeholder="Correction details..."></textarea>
                        </div>
                    </div>

                    <!-- SHORT LEAVE FORM -->
                    <div v-if="currentFormType === 'short'" class="form-grid text-white">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase opacity-75 mb-1">Date</label>
                            <input type="date" v-model="form.on_date" @change="handleDateChange" class="form-control bg-dark border-secondary text-white rounded-3">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold text-uppercase opacity-75 mb-1">From Time</label>
                                <input type="time" v-model="form.from_time" class="form-control bg-dark border-secondary text-white rounded-3">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold text-uppercase opacity-75 mb-1">To Time</label>
                                <input type="time" v-model="form.to_time" class="form-control bg-dark border-secondary text-white rounded-3">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase opacity-75 mb-1">Reason</label>
                            <textarea v-model="form.reason" class="form-control bg-dark border-secondary text-white rounded-3" rows="3" placeholder="Personal work, doctor visit, etc..."></textarea>
                        </div>
                    </div>

                    <!-- OVERTIME FORM -->
                    <div v-if="currentFormType === 'overtime'" class="form-grid text-white">
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase opacity-75 mb-1">Date</label>
                            <input type="date" v-model="form.on_date" class="form-control bg-dark border-secondary text-white rounded-3">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase opacity-75 mb-1">Hours Worked</label>
                            <input type="number" step="0.5" v-model="form.hrs" class="form-control bg-dark border-secondary text-white rounded-3" placeholder="e.g. 2.5">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-uppercase opacity-75 mb-1">Note/Works Done</label>
                            <textarea v-model="form.note" class="form-control bg-dark border-secondary text-white rounded-3" rows="3" placeholder="Tasks completed during overtime..."></textarea>
                        </div>
                    </div>

                    <div v-if="formError" class="alert alert-danger py-2 mb-0 mt-3 small fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ formError }}
                    </div>
                </div>
                <div class="modal-footer-custom pb-4">
                    <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold" @click="submitForm" :disabled="formLoading">
                        <span v-if="formLoading" class="spinner-border spinner-border-sm me-2"></span>
                        {{ formLoading ? 'Processing Request...' : 'Submit Request' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['activeTab'],
    data() {
        return {
            loading: true,
            loadingMore: false,
            employee: null,
            shifts: [],
            current_shift: null,
            leave_types: [],
            payslips: [],
            holidays: [],
            all_requests: [],
            leave_stats: [],
            birthdays: [],
            salary_breakup: null,
            payslipOffset: 5,
            hasMorePayslips: true,
            stats: { present: 0, late: 0, lop: 0, leave: 0 },
            viewDate: new Date(),
            dashboardDate: new Date(),
            
            // FORM MODAL STATE
            showFormModal: false,
            currentFormType: 'leave',
            formLoading: false,
            formError: null,
            form: {},
            formTitles: {
                'leave': 'Apply for Leave',
                'time': 'Attendance Correction (Time Update)',
                'short': 'Short Leave Request',
                'overtime': 'Overtime Approval Request'
            },
            holidayCategoryFilter: 'All',
            attStatusFilter: 'All'
        };
    },
    computed: {
        summaryStats() {
            return [
                { label: 'Present Days', value: this.stats.present, icon: 'bi-calendar-check', bgClass: 'bg-success bg-opacity-10 text-success' },
                { label: 'Late Arrival', value: this.stats.late + 'm', icon: 'bi-clock-history', bgClass: 'bg-warning bg-opacity-10 text-warning' },
                { label: 'Pending LOP', value: this.stats.lop, icon: 'bi-slash-circle', bgClass: 'bg-danger bg-opacity-10 text-danger' },
                { label: 'Short Leave', value: '4h', icon: 'bi-hourglass-split', bgClass: 'bg-info bg-opacity-10 text-info' },
            ];
        },
        currentDashboardMonthName() {
            return this.dashboardDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        },
        currentViewMonthName() {
            return this.viewDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        },
        currentMonthHolidays() {
            if (!this.holidays) return [];
            const compareMonth = this.viewDate.getMonth();
            const compareYear = this.viewDate.getFullYear();
            return this.holidays.filter(h => {
                const d = new Date(h.special_day);
                return d.getMonth() === compareMonth && d.getFullYear() === compareYear;
            });
        },
        filteredHolidays() {
            if (this.holidayCategoryFilter === 'All') return this.currentMonthHolidays;
            return this.currentMonthHolidays.filter(h => h.day_type === this.holidayCategoryFilter);
        },
        filteredShifts() {
            if (this.attStatusFilter === 'All') return this.shifts;
            return this.shifts.filter(s => s.status === this.attStatusFilter);
        },
        attSummary() {
            const s = this.shifts;
            return [
                { label: 'Present',  filter: 'Present',  icon: 'bi-check-circle-fill',  color: 'att-pill-present',  count: s.filter(x => x.status === 'Present').length },
                { label: 'Absent',   filter: 'Absent',   icon: 'bi-x-circle-fill',       color: 'att-pill-absent',   count: s.filter(x => x.status === 'Absent').length },
                { label: 'Leave',    filter: 'Leave',    icon: 'bi-file-earmark-text-fill', color: 'att-pill-leave', count: s.filter(x => ['Leave','Halfday Leave','Short Leave'].includes(x.status)).length },
                { label: 'Weekoff',  filter: 'Weekoff',  icon: 'bi-house-heart-fill',    color: 'att-pill-weekoff',  count: s.filter(x => x.status === 'Weekoff' || x.status === 'Holiday').length },
            ];
        },
        holidaySummary() {
            const list = this.currentMonthHolidays;
            return [
                { label: 'Total Holidays', count: list.filter(h => h.day_type === 'Holiday').length, filter: 'Holiday', icon: 'bi-umbrella-fill', bg: 'bg-holiday-icon' },
                { label: 'Weekoffs', count: list.filter(h => h.day_type === 'Weekoff').length, filter: 'Weekoff', icon: 'bi-house-heart-fill', bg: 'bg-weekoff-icon' },
                { label: 'Half Days', count: list.filter(h => h.day_type === 'Half Day').length, filter: 'Half Day', icon: 'bi-clock-history', bg: 'bg-halfday-icon' }
            ];
        }
    },
    methods: {
        initials(name) { return name ? name.charAt(0).toUpperCase() : '?'; },
        formatDateShort(dt) { return new Date(dt).toLocaleDateString('en-US', { day: 'numeric', month: 'short' }); },
        formatDateDetailed(dt) { return dt ? new Date(dt).toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' }) : 'N/A'; },
        getMonthShort(dt) { return new Date(dt).toLocaleDateString('en-US', { month: 'short' }); },
        getDayNum(dt) { return new Date(dt).getDate(); },
        getDayName(dt) { return new Date(dt).toLocaleDateString('en-US', { weekday: 'long' }); },
        calculateTenure(doj) {
            if (!doj) return 'N/A';
            const joined = new Date(doj);
            const today = new Date();
            let years = today.getFullYear() - joined.getFullYear();
            let months = today.getMonth() - joined.getMonth();
            if (months < 0) { years--; months += 12; }
            return `${years}Y ${months}M`;
        },
        formatTime(time) {
            if (!time) return '--:--';
            const [h, m] = time.split(':');
            const hour = parseInt(h);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${m} ${ampm}`;
        },
        formatCurrency(val) { return (val === null || val === undefined || isNaN(val)) ? '0' : parseFloat(val).toLocaleString('en-IN'); },
        getAttCardNew(status) {
            switch(status) {
                case 'Present':      return 'att-card-present';
                case 'Absent':       return 'att-card-absent';
                case 'Weekoff':
                case 'Holiday':      return 'att-card-weekoff';
                case 'Leave':
                case 'Halfday Leave':
                case 'Short Leave':  return 'att-card-leave';
                case 'On Duty':
                case 'Time Update':  return 'att-card-duty';
                default:             return 'att-card-default';
            }
        },
        getAttDateBlock(status) {
            switch(status) {
                case 'Present':      return 'att-date-present';
                case 'Absent':       return 'att-date-absent';
                case 'Weekoff':
                case 'Holiday':      return 'att-date-weekoff';
                case 'Leave':
                case 'Halfday Leave':
                case 'Short Leave':  return 'att-date-leave';
                case 'On Duty':
                case 'Time Update':  return 'att-date-duty';
                default:             return 'att-date-default';
            }
        },
        getAttStatusIcon(status) {
            switch(status) {
                case 'Present':      return 'bi bi-check-circle-fill';
                case 'Absent':       return 'bi bi-x-circle-fill';
                case 'Weekoff':
                case 'Holiday':      return 'bi bi-house-heart-fill';
                case 'Leave':
                case 'Halfday Leave':
                case 'Short Leave':  return 'bi bi-file-earmark-text-fill';
                case 'On Duty':      return 'bi bi-geo-alt-fill';
                case 'Time Update':  return 'bi bi-clock-fill';
                default:             return 'bi bi-dash-circle';
            }
        },
        getAttCardClass(status) {
            switch(status) {
                case 'Present':     return 'att-card-present';
                case 'Absent':      return 'att-card-absent';
                case 'Weekoff':
                case 'Holiday':     return 'att-card-weekoff';
                case 'Leave':
                case 'Halfday Leave':
                case 'Short Leave': return 'att-card-leave';
                case 'On Duty':
                case 'Time Update': return 'att-card-duty';
                default:            return 'att-card-default';
            }
        },
        getStatusClass(status) {
            switch(status) {
                case 'Present': return 'bg-success bg-opacity-10 text-success';
                case 'Absent': return 'bg-danger bg-opacity-10 text-danger';
                case 'Weekoff': return 'bg-light text-muted';
                default: return 'bg-primary bg-opacity-10 text-primary';
            }
        },
        getHolidayBadgeClass(type) {
            switch(type) {
                case 'Holiday': return 'bg-danger bg-opacity-10 text-danger';
                case 'Weekoff': return 'bg-primary bg-opacity-10 text-primary';
                case 'Half Day': return 'bg-warning bg-opacity-10 text-warning';
                default: return 'bg-light text-muted';
            }
        },
        getReqCardClass(type) {
            switch(type) {
                case 'Leave':       return 'req-card-leave';
                case 'Time Update': return 'req-card-time';
                case 'Short Leave': return 'req-card-short';
                case 'Overtime':    return 'req-card-ot';
                default:            return 'req-card-default';
            }
        },
        getReqIconBlock(type) {
            switch(type) {
                case 'Leave':       return 'req-block-leave';
                case 'Time Update': return 'req-block-time';
                case 'Short Leave': return 'req-block-short';
                case 'Overtime':    return 'req-block-ot';
                default:            return 'req-block-default';
            }
        },
        getReqStatusIcon(status) {
            switch(status) {
                case 'Approved': return 'bi bi-check-circle-fill';
                case 'Rejected': return 'bi bi-x-circle-fill';
                case 'Pending':  return 'bi bi-hourglass-split';
                default:         return 'bi bi-dash-circle';
            }
        },
        getRequestBadgeClass(status) {
            switch(status) {
                case 'Approved': return 'bg-success bg-opacity-10 text-success';
                case 'Rejected': return 'bg-danger bg-opacity-10 text-danger';
                case 'Pending': return 'bg-warning bg-opacity-10 text-warning';
                default: return 'bg-light text-muted';
            }
        },
        getRequestIcon(type) {
            switch(type) {
                case 'Leave': return 'bi-send-plus-fill';
                case 'Time Update': return 'bi-clock-fill';
                case 'Short Leave': return 'bi-hourglass-split';
                case 'Overtime': return 'bi-plus-circle-fill';
                default: return 'bi-file-earmark';
            }
        },
        getRequestIconClass(type) {
            switch(type) {
                case 'Leave': return 'text-primary';
                case 'Time Update': return 'text-info';
                case 'Short Leave': return 'text-secondary';
                case 'Overtime': return 'text-success';
                default: return 'text-dark';
            }
        },
        getHolidayHeaderClass(type) {
            switch(type) {
                case 'Festival': return 'bg-festival';
                case 'National Holiday': return 'bg-national';
                case 'Regional Holiday': return 'bg-regional';
                default: return 'bg-other';
            }
        },
        getHolidayIcon(type) {
            switch(type) {
                case 'Festival': return 'bi-stars';
                case 'National Holiday': return 'bi-flag-fill';
                case 'Regional Holiday': return 'bi-map-fill';
                case 'Weekoff': return 'bi-house-heart-fill';
                case 'Half Day': return 'bi-clock-history';
                case 'Holiday': return 'bi-umbrella-fill';
                default: return 'bi-calendar-heart';
            }
        },
        getCategoryIconColor(type) {
            switch(type) {
                case 'Holiday': return 'border-holiday';
                case 'Weekoff': return 'border-weekoff';
                case 'Half Day': return 'border-halfday';
                default: return 'border-secondary';
            }
        },
        getHolCardClass(type) {
            switch(type) {
                case 'Holiday':  return 'hol-card-holiday';
                case 'Weekoff':  return 'hol-card-weekoff';
                case 'Half Day': return 'hol-card-halfday';
                default:         return 'hol-card-default';
            }
        },
        getHolDateBlockClass(type) {
            switch(type) {
                case 'Holiday':  return 'hol-date-holiday';
                case 'Weekoff':  return 'hol-date-weekoff';
                case 'Half Day': return 'hol-date-halfday';
                default:         return 'hol-date-default';
            }
        },
        getHolidayBadgeClassPlain(type) {
            switch(type) {
                case 'Festival': return 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25';
                case 'National Holiday': return 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25';
                case 'Regional Holiday': return 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                default: return 'bg-light text-muted border';
            }
        },
        prevMonth() { this.viewDate = new Date(this.viewDate.getFullYear(), this.viewDate.getMonth() - 1, 1); },
        nextMonth() { this.viewDate = new Date(this.viewDate.getFullYear(), this.viewDate.getMonth() + 1, 1); },
        goToCurrentMonth() { this.viewDate = new Date(); },
        changeDashboardMonth(delta) {
            this.dashboardDate = new Date(this.dashboardDate.getFullYear(), this.dashboardDate.getMonth() + delta, 1);
            this.getData(this.dashboardDate.getMonth() + 1, this.dashboardDate.getFullYear());
        },
        getData(m = null, y = null) {
            this.loading = true;
            const params = {};
            if(m) params.month = m;
            if(y) params.year = y;
            axios.get('/employee/dashboard/get_my_data', { params }).then(res => {
                this.employee = res.data.employee;
                this.shifts = res.data.shifts;
                this.current_shift = res.data.current_shift;
                this.leave_types = res.data.leave_types;
                this.payslips = res.data.payslips.map(p => ({ ...p, expanded: false, details: null, wordAmt: '' }));
                this.holidays = res.data.holidays;
                this.all_requests = res.data.all_requests || [];
                this.leave_stats = res.data.leave_stats || [];
                this.birthdays = res.data.birthdays || [];
                this.salary_breakup = res.data.salary_breakup || null;
                this.calculateStats();
                this.loading = false;
                if (this.payslips && this.payslips.length < 5) this.hasMorePayslips = false;
            }).catch(err => {
                console.error("Sync error:", err);
                this.loading = false;
            });
        },
        loadMorePayslips() {
            this.loadingMore = true;
            axios.get('/employee/dashboard/get_my_data', { params: { offset: this.payslipOffset } }).then(res => {
                const newPayslips = res.data.payslips.map(p => ({ ...p, expanded: false, details: null, wordAmt: '' }));
                if (newPayslips.length > 0) {
                    this.payslips = [...this.payslips, ...newPayslips];
                    this.payslipOffset += 5;
                    if (newPayslips.length < 5) this.hasMorePayslips = false;
                } else {
                    this.hasMorePayslips = false;
                }
                this.loadingMore = false;
            });
        },
        togglePayslip(pay) {
            pay.expanded = !pay.expanded;
            if (pay.expanded && !pay.details) {
                axios.get('/employee/dashboard/get_payslip_details/'+pay.id).then(res => { pay.details = res.data.payslip; pay.wordAmt = res.data.amount_in_words; });
            }
        },
        calculateStats() {
            this.stats.present = this.shifts.filter(s => s.status === 'Present').length;
            this.stats.late = this.shifts.reduce((acc, s) => acc + (s.late || 0), 0);
            this.stats.lop = this.shifts.reduce((acc, s) => acc + (s.lop || 0), 0);
        },
        openForm(type) {
            this.currentFormType = type;
            this.formError = null;
            this.formLoading = false;
            
            // Initialize form based on type
            const today = new Date().toISOString().split('T')[0];
            if (type === 'leave') {
                this.form = { employee_id: this.employee.id, leave_master_id: null, from: today, to: today, reason: '', is_halfday: 'No', is_lop: 'Yes', status: 'Pending' };
            } else if (type === 'time') {
                this.form = { employee_id: this.employee.id, on_date: today, in_time: '09:00', out_time: '18:00', reason: '', status: 'Pending' };
            } else if (type === 'short') {
                this.form = { employee_id: this.employee.id, on_date: today, from_time: '14:00', to_time: '16:00', reason: '', status: 'Pending', is_lop: 'Yes' };
            } else if (type === 'overtime') {
                this.form = { employee_id: this.employee.id, on_date: today, hrs: 1, note: '', status: 'Pending' };
            }
            
            this.handleDateChange();
            this.showFormModal = true;
        },
        handleDateChange() {
            if (this.currentFormType === 'time' || this.currentFormType === 'short') {
                const shiftData = this.shifts.find(s => s.dt === this.form.on_date);
                if (shiftData && shiftData.working_shift) {
                    const shiftIn = shiftData.working_shift.in;
                    const shiftOut = shiftData.working_shift.out;
                    if (this.currentFormType === 'time') {
                        this.form.in_time = shiftIn;
                        this.form.out_time = shiftOut;
                    } else if (this.currentFormType === 'short') {
                        this.form.from_time = shiftIn;
                        this.form.to_time = shiftOut;
                    }
                }
            }
        },
        closeForm() {
            this.showFormModal = false;
        },
        submitForm() {
            this.formLoading = true;
            this.formError = null;
            
            let url = '';
            if (this.currentFormType === 'leave') url = '/approvals/leave/add';
            else if (this.currentFormType === 'time') url = '/approvals/time_update/add';
            else if (this.currentFormType === 'short') url = '/approvals/shortleave/add';
            else if (this.currentFormType === 'overtime') url = '/approvals/overtime/add';

            axios.post(url, this.form).then(res => {
                this.formLoading = false;
                this.showFormModal = false;
                alert('Request submitted successfully with Pending status.');
                this.getData(); // Refresh data
            }).catch(err => {
                this.formLoading = false;
                const response = err.response?.data || {};
                const validationErrors = response.errors ? Object.values(response.errors).flat().join(' ') : '';
                this.formError = response.message || validationErrors || 'Verification failed. Please check your inputs.';
            });
        }
    },
    created() {
        this.getData();
    }
}
</script>

<style scoped lang="scss">
.profile-img-xl { width: 140px; height: 140px; object-fit: cover; }
.profile-img-large { width: 120px; height: 120px; object-fit: cover; }
.status-indicator { width: 22px; height: 22px; border-radius: 50%; border: 4px solid white; position: absolute; bottom: 10px; right: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.shadow-premium { box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
/* ── Request History Cards ────────────────────── */
.req-card-leave   { border-color: #a5b4fc !important; background: #f5f3ff; }
.req-card-time    { border-color: #7dd3fc !important; background: #f0f9ff; }
.req-card-short   { border-color: #cbd5e1 !important; background: #f8fafc; }
.req-card-ot      { border-color: #a7f3d0 !important; background: #f0fdf8; }
.req-card-default { border-color: #e2e8f0 !important; background: #ffffff; }

.req-block-leave   { background: #ede9fe; color: #4338ca; border-right: 1px solid #a5b4fc; }
.req-block-time    { background: #e0f2fe; color: #0369a1; border-right: 1px solid #7dd3fc; }
.req-block-short   { background: #f1f5f9; color: #475569; border-right: 1px solid #cbd5e1; }
.req-block-ot      { background: #d1fae5; color: #065f46; border-right: 1px solid #a7f3d0; }
.req-block-default { background: #f8fafc; color: #64748b; border-right: 1px solid #e2e8f0; }

/* ── Attendance Cards ─────────────────────────── */
.att-card-present  { border-color: #a7f3d0 !important; background: #f0fdf8; }
.att-card-absent   { border-color: #fca5a5 !important; background: #fff9f9; }
.att-card-weekoff  { border-color: #cbd5e1 !important; background: #f8fafc; }
.att-card-leave    { border-color: #a5b4fc !important; background: #f5f3ff; }
.att-card-duty     { border-color: #fde68a !important; background: #fffdf4; }
.att-card-default  { border-color: #e2e8f0 !important; background: #ffffff; }

.att-date-present  { background: #d1fae5; color: #065f46; border-right: 1px solid #a7f3d0; }
.att-date-absent   { background: #fee2e2; color: #b91c1c; border-right: 1px solid #fca5a5; }
.att-date-weekoff  { background: #f1f5f9; color: #475569; border-right: 1px solid #cbd5e1; }
.att-date-leave    { background: #ede9fe; color: #4338ca; border-right: 1px solid #a5b4fc; }
.att-date-duty     { background: #fef3c7; color: #92400e; border-right: 1px solid #fde68a; }
.att-date-default  { background: #f8fafc; color: #64748b; border-right: 1px solid #e2e8f0; }
.hover-lift { transition: all 0.2s ease; &:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important; } }
.font-monospace { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; }
.avatar-circle { width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.uppercase-tracking { text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.72rem; color: #6b7280; font-weight: 700; }
.min-w-160 { min-width: 160px; }
.min-w-100 { min-width: 100px; }
.profile-hero {
    min-height: 250px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    &::after {
        content: ""; position: absolute; top:0; right:0; bottom:0; left:0;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 86c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm66 3c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm-46-4c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm63-31c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM15 44c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm20-25c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM43 7c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm41 12c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM33 33c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm46 42c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
    }
}
.spinner-premium { width: 60px; height: 60px; border-radius: 50%; background: conic-gradient(#0000 10%, #6366f1); -webkit-mask: radial-gradient(farthest-side, #0000 calc(100% - 89.2px), #000 0); animation: s3 1s infinite linear; }
@keyframes s3 { to { transform: rotate(1turn); } }
.transition-all { transition: all 0.3s ease-in-out; }
.accordion-button::after { display: none; }
.z-index-1 { z-index: 1; }
.btn-white { background: white; color: #4f46e5; border: none; &:hover { background: #f8fafc; color: #4338ca; } }
.btn-outline-white { background: transparent; color: white; border: 2px solid rgba(255,255,255,0.4); &:hover { background: rgba(255,255,255,0.1); border-color: white; } }

/* Modal Styling */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.custom-modal {
    background: #1e293b;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1.5rem;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
}

.modal-header-custom {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    h5 { font-weight: 700; }
}

.btn-close-custom {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    &:hover { background: rgba(255, 255, 255, 0.2); }
}

.modal-body-custom {
    padding: 1.5rem;
}

.form-select, .form-control {
    &:focus {
        background-color: #0f172a;
        color: white;
        border-color: #6366f1;
        box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
    }
}

.modal-footer-custom {
    padding: 0 1.5rem 1.5rem;
}

.cursor-pointer { cursor: pointer; }

.requests-hero {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
}

.fw-900 { font-weight: 900; }
.fw-300 { font-weight: 300; }
.line-height-1 { line-height: 1; }
.secondary-text { color: #64748b; }
.opacity-05 { opacity: 0.05; }
.h-20px { height: 20px; }

.hover-bg-light:hover { background: #f8fafc; }
.hover-scale { transition: transform 0.2s ease; &:hover { transform: scale(1.1); } }

/* ── Holiday Page ─────────────────────────────── */
.hol-header { background: #f8f9fb; border: 1px solid #e9ecef; }

.hol-pill {
    background: #fff; color: #6c757d;
    &:hover { background: #f1f3f5; color: #343a40; }
}
.hol-pill-active { background: #4f46e5 !important; color: #fff !important; border-color: #4f46e5 !important; }

/* Cards */
.hol-card {
    transition: box-shadow 0.2s ease, transform 0.2s ease;
    &:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); transform: translateY(-3px); }
}
.hol-card-holiday  { border-color: #fca5a5 !important; background: #fff9f9; }
.hol-card-weekoff  { border-color: #a5b4fc !important; background: #f9f9ff; }
.hol-card-halfday  { border-color: #fde68a !important; background: #fffdf4; }
.hol-card-default  { border-color: #e2e8f0 !important; background: #ffffff; }

/* Date block */
.hol-date-block { border-right: 1px solid rgba(0,0,0,0.06); }
.hol-date-holiday { background: #fee2e2; color: #b91c1c; }
.hol-date-weekoff { background: #ede9fe; color: #4338ca; }
.hol-date-halfday { background: #fef3c7; color: #92400e; }
.hol-date-default { background: #f1f5f9; color: #475569; }

.bg-holiday-icon { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.bg-weekoff-icon { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
.bg-halfday-icon { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.bg-all-icon     { background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%); }

.btn-soft-primary {
    background: #eef2ff;
    color: #4f46e5;
    border: 1px solid #c7d2fe;
    &:hover { background: #e0e7ff; color: #4338ca; }
}
</style>
