<template>
    <div class="preferences-dashboard">
        <!-- Top Header -->
        <div class="dashboard-header p-4 mb-4">
            <h4 class="fw-bold mb-1 text-dark">System Preferences</h4>
            <p class="text-muted small mb-0">Customize global calculation logic, payroll cycles, and attendance penalties.</p>
        </div>

        <div class="row g-4">
            <!-- Attendance Policy Group -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="fw-bold mb-0 text-primary d-flex align-items-center">
                            <i class="bi bi-clock-history me-2 fs-5"></i> Late Mark & Early Going
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div v-for="(setting, ind) in lateEarlySettings" :key="setting.key" 
                             class="setting-row p-4 border-bottom-light transition-all">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="fw-bold text-dark small text-uppercase opacity-75 mb-1">{{ setting.key }}</div>
                                    <div class="text-muted small">Configure threshold and penalty logic for this metric.</div>
                                </div>
                                <div class="col-auto">
                                    <div v-if="setting.type === 'Input'" class="input-group input-group-sm shadow-sm rounded-3 overflow-hidden" style="width: 140px;">
                                        <input type="text" v-model="setting.val" @input="saveOriginal(setting)" class="form-control border-0 bg-light text-center fw-bold">
                                        <span class="input-group-text border-0 bg-white small">{{ getSuffix(setting.key) }}</span>
                                    </div>

                                    <select v-if="setting.type === 'Select'" v-model="setting.val" @change="saveOriginal(setting)" 
                                            class="form-select form-select-sm border-0 bg-light fw-semibold shadow-sm rounded-3" style="width: 140px;">
                                        <option v-for="opt in setting.options" :key="opt.val" :value="opt.val">{{ opt.key }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payroll & Cycle Group -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="fw-bold mb-0 text-success d-flex align-items-center">
                            <i class="bi bi-calendar-check me-2 fs-5"></i> Salary Cycle & Calculation
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div v-for="(setting, ind) in payrollSettings" :key="setting.key" 
                             class="setting-row p-4 border-bottom-light transition-all">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="fw-bold text-dark small text-uppercase opacity-75 mb-1">{{ setting.key }}</div>
                                    <div class="text-muted small">Define how working days and monthly cycles are tracked.</div>
                                </div>
                                <div class="col-auto">
                                    <select v-model="setting.val" @change="saveOriginal(setting)" 
                                            class="form-select form-select-sm border-0 bg-light fw-semibold shadow-sm rounded-3" style="width: 160px;">
                                        <option v-for="opt in setting.options" :key="opt.val" :value="opt.val">{{ opt.key }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light-subtle p-4 border-0">
                        <div class="d-flex align-items-center p-3 bg-white rounded-4 border border-dashed shadow-sm">
                            <div class="icon-circle bg-soft-warning text-warning me-3">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                            <div class="small">
                                <strong class="d-block">Global Impact</strong>
                                Changes to these preferences will immediately affect all active salary calculations.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Integration Group -->
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="fw-bold mb-0 text-info d-flex align-items-center">
                            <i class="bi bi-cpu me-2 fs-5"></i> API & Integrations
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div v-for="(setting, ind) in integrationSettings" :key="setting.key" 
                             class="setting-row p-4 border-bottom-light transition-all">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="fw-bold text-dark small text-uppercase opacity-75 mb-1">{{ setting.key }}</div>
                                    <div class="text-muted small">Access token for biometric machine data synchronization.</div>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex flex-column gap-2" style="width: 280px;">
                                        <div class="input-group input-group-sm shadow-sm rounded-3 overflow-hidden">
                                            <input :type="showToken ? 'text' : 'password'" v-model="setting.val" @input="saveOriginal(setting)" 
                                                   class="form-control border-0 bg-light fw-bold" placeholder="Enter or generate token">
                                            <button @click="showToken = !showToken" class="btn btn-light border-0">
                                                <i :class="showToken ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button @click="generateToken(setting)" class="btn btn-info btn-sm text-white rounded-3 px-3 shadow-sm flex-grow-1">
                                                <i class="bi bi-key-fill me-1"></i> Generate
                                            </button>
                                            <button @click="copyToken(setting.val)" class="btn btn-outline-secondary btn-sm rounded-3 px-3 shadow-sm" :disabled="!setting.val">
                                                <i class="bi bi-clipboard me-1"></i> Copy
                                            </button>
                                        </div>
                                    </div>
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
import axios from 'axios';

export default {
    data(){
        return {
            yesNo: [{key: 'Yes', val: 'Yes'}, {key: 'No', val: 'No'}],
            hourMinute: [{key: 'Hour', val: 'Hour'}, {key: 'Minute', val: 'Minute'}],
            daysForSalary: [{key: 'Actual Days', val: 'Actual Days'}, {key: 'Working Days', val: 'Working Days'}],
            days: Array.from({length: 31}, (_, i) => ({key: i + 1, val: i + 1})),

            settings: [
                {type: 'Input', key: "Late Days", val: 3},
                {type: 'Input', key: "Late Minutes", val: 5},
                {type: 'Select', key: "Calculate Late Day's Salary on Pro-rata basis", val: 'Yes', options: []},
                {type: 'Select', key: "On Late Calculate LOP as per", val: 'Hour', options: []},
                {type: 'Input', key: "Penalty On Late Mark in LOP", val: 1},
                {type: 'Input', key: "Early Going Days", val: 3},
                {type: 'Input', key: "Early Going Minutes", val: 5},
                {type: 'Select', key: "Calculate Early Going Day's Salary on Pro-rata basis", val: 'Yes', options: []},
                {type: 'Select', key: "On Early Going Calculate LOP as per", val: 'Hour', options: []},
                {type: 'Input', key: "Penalty On Early Going Mark in LOP", val: 1},
                {type: 'Select', key: "Working Days Consideration", val: 'Working Days', options: []},
                {type: 'Select', key: "Salary Cycle Start Date", val: 1, options: []},
                {type: 'Select', key: "Salary Release Date", val: 1, options: []},
                {type: 'Input', key: "Attendance Machine API Token", val: ''},
            ],
            saveTimer: null,
            showToken: false
        };
    },

    computed: {
        lateEarlySettings() {
            return this.settings.slice(0, 10);
        },
        payrollSettings() {
            return this.settings.slice(10, 13);
        },
        integrationSettings() {
            return this.settings.slice(13);
        }
    },

    methods: {
        getSuffix(key) {
            if (key.includes('Minutes')) return 'Min';
            if (key.includes('Days')) return 'Days';
            return '';
        },

        saveOriginal(setting) {
            if (this.saveTimer) clearTimeout(this.saveTimer);
            this.saveTimer = setTimeout(() => {
                axios.post('/application_settings/preference/save', {
                    key: setting.key,
                    value: setting.val
                });
            }, 300);
        },

        generateToken(setting) {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let token = '';
            for (let i = 0; i < 32; i++) {
                token += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            setting.val = token;
            this.saveOriginal(setting);
            this.showToken = true;
        },

        copyToken(val) {
            navigator.clipboard.writeText(val);
            alert('Token copied to clipboard');
        },

        async fetch(){
            let resp = await axios.get('/application_settings/preference/fetch').then(res => res.data);
            if(resp){
                resp.forEach(item => {
                    let pos = this.settings.map(e => e.key).indexOf(item.key);
                    if (pos !== -1) {
                        this.settings[pos].val = item.value;
                    }
                });
            }
        },
    },

    created(){
        this.settings[2].options = this.yesNo;
        this.settings[3].options = this.hourMinute;
        this.settings[7].options = this.yesNo;
        this.settings[8].options = this.hourMinute;
        this.settings[10].options = this.daysForSalary;
        this.settings[11].options = this.days;
        this.settings[12].options = this.days;
        this.fetch();
    }
}
</script>

<style scoped>
.preferences-dashboard {
    background-color: #f8fafc;
    min-height: 100vh;
    padding: 1.5rem;
}

.dashboard-header {
    background: white;
    border-radius: 1.25rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.setting-row {
    background-color: white;
}

.setting-row:hover {
    background-color: #f8fafc;
}

.border-bottom-light {
    border-bottom: 1px solid #f1f5f9;
}

.setting-row:last-child {
    border-bottom: none;
}

.icon-circle {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.25rem;
}

.bg-soft-warning { background-color: #fffbeb; }
.transition-all { transition: all 0.2s ease-in-out; }

.bg-light-subtle {
    background-color: #f8fafc !important;
}

.form-select-sm, .form-control {
    font-size: 0.85rem;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}
</style>