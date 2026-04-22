<template>
    <div class="calendar-container animate__animated animate__fadeIn">
        
        <!-- Year Selection Header -->
        <div class="card border-0 shadow-premium mb-4 bg-white">
            <div class="card-body p-3">
                <year-form @yearChange="onYearChange($event)"></year-form>
            </div>
        </div>

        <div class="row g-4" v-if="currentYear">
            
            <!-- Main Calendar Card -->
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-premium calendar-card overflow-hidden">
                    <div class="calendar-header p-4 bg-primary text-white d-flex align-items-center justify-content-between">
                        <button class="btn btn-glass-circle" :disabled="currentMonth <= 0" @click="navigateMonth('Prev')">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="text-center">
                            <h2 class="m-0 fw-bold">{{ months[currentMonth] }}</h2>
                            <p class="m-0 text-white text-opacity-75 small letter-spacing-2">{{ currentYear ? currentYear.key : '' }}</p>
                        </div>
                        <button class="btn btn-glass-circle" :disabled="currentMonth >= 11" @click="navigateMonth('Next')">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>

                    <div class="calendar-body p-0">
                        <div class="calendar-grid">
                            <!-- Weekdays Header -->
                            <div v-for="dd in days" :key="'h-'+dd" class="weekday-cell">
                                {{ dd }}
                            </div>

                            <!-- Calendar Days -->
                            <template v-for="n of 42" :key="n">
                                <div
                                    v-if="n-firstDay > 0 && n-firstDay <= day_count[currentMonth]"
                                    @click="addRemoveDate(n - firstDay)"
                                    class="calendar-day-cell"
                                    :class="[
                                        getTypeClass(n-firstDay),
                                        (sendIfExist(n-firstDay) ? 'is-selected' : '')
                                    ]"
                                >
                                    <span class="day-number">{{ n - firstDay }}</span>
                                    <div class="day-status-indicator" v-if="checkData(n-firstDay)">
                                        {{ checkData(n-firstDay) }}
                                    </div>
                                </div>
                                <div v-else class="calendar-day-cell empty"></div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Panel -->
            <div class="col-12 col-xl-4 d-flex flex-column gap-4">
                
                <!-- Quick Selection Info -->
                <div class="card border-0 shadow-premium" v-if="dates.length > 0">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Modify Days ({{ dates.length }} selected)</h5>
                        <div class="row g-3">
                            <forms-select-field name="day_type" label="Day Type" v-model="fd.day_type" error="" classes="col-12" :options="[
                                {key: 'Weekoff', val: 'Weekoff'},
                                {key: 'Holiday', val: 'Holiday'},
                                {key: 'Halfday', val: 'Halfday'},
                            ]"></forms-select-field>
            
                            <forms-text-field name="remark" label="Remark" v-model="fd.remark" error="" classes="col-12"></forms-text-field>

                            <div class="col-12 d-flex gap-2 mt-2">
                                <button class="btn btn-primary flex-grow-1 py-2" @click="save()">
                                    <i class="bi bi-check2-circle me-1"></i> Save Changes
                                </button>
                                <button class="btn btn-outline-danger" @click="deleteRecords()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Holidays List -->
                <div class="card border-0 shadow-premium flex-grow-1 overflow-hidden">
                    <div class="card-header bg-light py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold m-0"><i class="bi bi-calendar-event me-2 text-primary"></i>Holidays</h5>
                        <span class="badge bg-primary rounded-pill">{{ monthlyHolidays.length }}</span>
                    </div>
                    <div class="card-body p-0 overflow-auto custom-scrollbar" style="max-height: 400px;">
                        <div v-if="monthlyHolidays.length == 0" class="p-5 text-center text-muted opacity-50">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            <p class="m-0">No holidays this month</p>
                        </div>
                        <div v-else class="list-group list-group-flush">
                            <div v-for="sd in monthlyHolidays" :key="sd.id" class="list-group-item border-start-4 px-4 py-3" 
                                 :class="{'border-danger': sd.day_type == 'Holiday', 'border-success': sd.day_type == 'Weekoff', 'border-warning': sd.day_type == 'Halfday'}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="fw-bold mb-0 text-dark">{{ formatDate(sd.special_day) }}</p>
                                        <p class="mb-0 text-secondary small">{{ sd.remark || 'No remark' }}</p>
                                    </div>
                                    <span class="badge" :class="getTypeBadgeClass(sd.day_type)">{{ sd.day_type }}</span>
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
            fd: {
                day_type: null,
                remark: null
            },
            currentYear: null,
            currentMonth: 0,
            months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            month_numbers: ['01','02','03','04','05','06','07','08','09','10','11','12'],
            days: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            day_count: [31,28,31,30,31,30,31,31,30,31,30,31],
            isLeapYear: false,
            firstDay: null,
            dates: [],
            special_days: [],
        };
    },
    computed: {
        monthlyHolidays() {
            return this.special_days.filter(sd => sd.day_type === 'Holiday' && this.isValidRecord(sd.special_day));
        }
    },
    methods: {
        formatDate(dateString) {
            const d = new Date(dateString);
            return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        },
        getTypeClass(day) {
            const type = this.checkData(day);
            if (!type) return '';
            return `is-${type.toLowerCase()}`;
        },
        getTypeBadgeClass(type) {
            const classes = {
                'Weekoff' : 'bg-success bg-opacity-10 text-success',
                'Holiday' : 'bg-danger bg-opacity-10 text-danger',
                'Halfday' : 'bg-warning bg-opacity-10 text-warning'
            };
            return classes[type] || 'bg-secondary';
        },
        isValidRecord(dt){
            let d = new Date(dt);
            return d.getMonth() == this.currentMonth;
        },
        checkData(dt){
            if(this.currentYear && dt > 0){
                let dayStr = dt < 10 ? '0' + dt : dt;
                let newDT = this.currentYear.key + '-' + this.month_numbers[this.currentMonth] + '-' + dayStr;
                const record = this.special_days.find(item => item.special_day == newDT);
                return record ? record.day_type : null;
            }
        },
        fetch(){
            axios.get('/calender/special_day/fetch/' + this.currentYear.key)
            .then(res => {
                this.special_days = res.data;
            });
        },
        deleteRecords(){
            let data = { ...this.fd, dates: this.dates };
            axios.post('/calender/special_day/delete', data)
            .then(() => {
                this.fetch();
                this.reset();
            });
        },
        reset(){
            this.fd.day_type = null;
            this.fd.remark = null;
            this.dates = [];
        },
        save(){
            let data = { ...this.fd, dates: this.dates };
            axios.post('/calender/special_day/save', data)
            .then(() => {
                this.fetch();
                this.reset();
            });
        },
        sendIfExist(dt){
            if(this.currentYear && dt > 0){
                let dayStr = dt < 10 ? '0' + dt : dt;
                let newDT = this.currentYear.key + '-' + this.month_numbers[this.currentMonth] + '-' + dayStr;
                return this.dates.includes(newDT);
            }
            return false;
        },
        addRemoveDate(dt){
            if(this.currentYear && dt > 0){
                let dayStr = dt < 10 ? '0' + dt : dt;
                let newDT = this.currentYear.key + '-' + this.month_numbers[this.currentMonth] + '-' + dayStr;
                if(!this.dates.includes(newDT)){
                    this.dates.push(newDT);
                } else {
                    this.dates.splice(this.dates.indexOf(newDT), 1);
                }
            }
        },
        setFirstDay(){
            let dt = this.currentYear.key + "-" + this.month_numbers[this.currentMonth] + "-01";
            let d = new Date(dt);
            this.firstDay = d.getDay();
        },
        navigateMonth(what){
            if(what == 'Prev' && this.currentMonth > 0){
                this.currentMonth--;
            } else if(what == 'Next' && this.currentMonth < 11){
                this.currentMonth++;
            }
            this.setFirstDay();
            this.reset(); // Clear selection when changing months
        },
        onYearChange(e){
            this.currentYear = e;
            if(this.currentYear != null){
                let r = parseInt(this.currentYear.key) % 4;
                this.day_count[1] = r == 0 ? 29 : 28;
                let d = new Date();
                this.currentMonth = d.getMonth();
                this.setFirstDay();
                this.fetch();
                this.reset();
            }
        },
    }
}
</script>

<style scoped lang="scss">
.letter-spacing-2 { letter-spacing: 2px; }

.btn-glass-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.15);
    color: white;
    border: 1px solid rgba(255,255,255,0.2);
    transition: all 0.2s;
    &:hover:not(:disabled) {
        background: rgba(255,255,255,0.25);
        color: white;
        transform: scale(1.05);
    }
    &:disabled {
        opacity: 0.3;
    }
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8f9fa;
    min-width: 300px; // Ensure it doesn't collapse too much
}

.weekday-cell {
    padding: 1.25rem 0;
    text-align: center;
    font-weight: 700;
    color: var(--bs-primary);
    background: white;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 1px;
    border-bottom: 1px solid rgba(0,0,0,0.05);

    @media (max-width: 768px) {
        padding: 0.75rem 0;
        font-size: 0.6rem;
    }
}

.calendar-day-cell {
    height: 100px;
    background: white;
    border-right: 1px solid rgba(0,0,0,0.04);
    border-bottom: 1px solid rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;

    @media (max-width: 992px) {
        height: 80px;
        padding: 0.6rem;
    }

    @media (max-width: 768px) {
        height: 60px;
        padding: 0.4rem;
        
        .day-number { font-size: 0.9rem !important; }
        .day-status-indicator { font-size: 0.5rem !important; display: none; } // Hide text status on very small screens to save space
    }

    &:hover:not(.empty) {
        background: #fdfdfd;
        z-index: 1;
        box-shadow: inset 0 0 0 2px rgba(var(--bs-primary-rgb), 0.1);
    }


    &.is-selected {
        background: rgba(var(--bs-primary-rgb), 0.05);
        box-shadow: inset 0 0 0 2px var(--bs-primary);
    }

    &.is-holiday {
        background: rgba(220, 53, 69, 0.03);
        .day-status-indicator { color: #dc3545; background: rgba(220, 53, 69, 0.1); }
    }
    &.is-weekoff {
        background: rgba(25, 135, 84, 0.03);
        .day-status-indicator { color: #198754; background: rgba(25, 135, 84, 0.1); }
    }
    &.is-halfday {
        background: rgba(255, 193, 7, 0.03);
        .day-status-indicator { color: #ffc107; background: rgba(255, 193, 7, 0.1); }
    }

    .day-number {
        font-weight: 600;
        font-size: 1.1rem;
        color: #444;
    }

    .day-status-indicator {
        margin-top: auto;
        font-size: 0.65rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.5px;
        padding: 2px 6px;
        border-radius: 4px;
        text-align: center;
    }
}

.border-start-4 {
    border-left-width: 4px !important;
}

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0, 0, 0, 0.1); border-radius: 10px; }
</style>