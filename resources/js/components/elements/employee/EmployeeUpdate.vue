<template>
    <div class="employee-administrative-suite">
        <!-- Modern Horizontal Tab Bar -->
        <div class="tab-scroller mb-4">
            <div class="d-flex gap-2 pb-2">
                <button 
                    v-for="tab in availableTabs" 
                    :key="tab.id"
                    @click="what = tab.id"
                    :class="['tab-modern-btn', { 'active': what === tab.id }]"
                >
                    <i :class="['bi', tab.icon, 'me-2']"></i>
                    {{ tab.name }}
                </button>
                <button v-if="what != null" @click="what = null" class="btn btn-sm btn-soft-danger rounded-pill px-3 ms-auto text-nowrap d-inline-flex align-items-center">
                    <i class="bi bi-x-lg me-1"></i> Reset View
                </button>
            </div>
        </div>

        <!-- Dynamic Component Canvas -->
        <transition name="fade-slide" mode="out-in">
            <div :key="what || 'empty'" class="component-container">
                <div v-if="what == null" class="empty-state-canvas py-5 text-center bg-light-subtle rounded-4 p-4 border border-light mx-4 my-2">
                    <div class="pulse-icon mb-4">
                        <i class="bi bi-person-gear display-1 opacity-25"></i>
                    </div>
                    <h5 class="fw-900 text-dark">Administrative Workspace</h5>
                    <p class="text-muted small mx-auto fw-semibold" style="max-width: 450px;">
                        Select a specialized module from the navigation above to access and modify employee records, document vaults, or compensation structures.
                    </p>
                </div>

                <!-- Component Views -->
                <div v-else class="p-4 pt-2">
                    <employee-documents :employee_id="employee.id" v-if="what=='Documents'"></employee-documents>
                    <employee-education :employee_id="employee.id" v-if="what=='Education'"></employee-education>
                    <employee-address :employee_id="employee.id" v-if="what=='Address'"></employee-address>
                    <employee-work-location :locations="locations" :employee_id="employee.id" v-if="what=='Work Location'"></employee-work-location>
                    <employee-designation :designations="designations" :employee_id="employee.id" v-if="what=='Designation'"></employee-designation>
                    <employee-department :departments="departments" :employee_id="employee.id" v-if="what=='Department'"></employee-department>
                    <employee-leave-group :leave_groups="leave_groups" :employee_id="employee.id" v-if="what=='Leave Group'"></employee-leave-group>
                    <employee-salary :salary_groups="salary_groups" :employee="employee" v-if="what=='Salary'"></employee-salary>
                    <employee-services :employee_id="employee.id" :services="services" v-if="what=='Services'"></employee-services>
                    <employee-bank :employee_id="employee.id" v-if="what=='Bank'"></employee-bank>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    props: ['employee', 'work_locations', 'designations', 'departments', 'leave_groups', 'salary_groups', 'services'],

    data(){
        return {
            what: null,
            locations : [],
            availableTabs: [
                { id: 'Documents', name: 'Document Vault', icon: 'bi-folder2-open' },
                { id: 'Education', name: 'Education Docs', icon: 'bi-mortarboard' },
                { id: 'Address', name: 'Residence', icon: 'bi-geo' },
                { id: 'Work Location', name: 'Work Location', icon: 'bi-building-up' },
                { id: 'Department', name: 'Department', icon: 'bi-grid-1x2' },
                { id: 'Designation', name: 'Designation', icon: 'bi-award' },
                { id: 'Leave Group', name: 'Leave Policy', icon: 'bi-calendar-check' },
                { id: 'Salary', name: 'Payroll & CTC', icon: 'bi-wallet2' },
                { id: 'Services', name: 'Core Services', icon: 'bi-gear' },
                { id: 'Bank', name: 'Bank Details', icon: 'bi-bank' },
            ]
        };
    },

    created(){
        this.work_locations.forEach(loc => {
            this.locations.push(loc);
        });
    },
}
</script>

<style scoped>
.employee-administrative-suite {
    padding: 1rem; /* Reduced for mobile */
}

@media (min-width: 768px) {
    .employee-administrative-suite {
        padding: 1.5rem 2rem;
    }
}

.tab-scroller {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    margin-bottom: 1rem;
}
.tab-scroller::-webkit-scrollbar {
    display: none;
}

.tab-modern-btn {
    white-space: nowrap;
    padding: 0.5rem 1rem; /* Slightly compact for mobile */
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: white;
    color: #64748b;
    font-weight: 700;
    font-size: 0.75rem; /* Smaller icons/text for mobile tabs */
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    min-height: 40px;
}

@media (min-width: 768px) {
    .tab-modern-btn {
        padding: 0.65rem 1.15rem;
        font-size: 0.825rem;
    }
}

.tab-modern-btn:hover {
    background: #f8fafc;
    color: #334155;
    border-color: #cbd5e1;
}

.tab-modern-btn.active {
    background: #6366f1;
    color: white;
    border-color: #6366f1;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.component-container {
    min-height: 450px;
}

.bg-light-subtle {
    background-color: #f8fafc;
}

.btn-soft-danger {
    background: #fff1f2;
    color: #e11d48;
    border: 1px solid #fecdd3;
    font-weight: 700;
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
}

.fade-slide-enter-active, .fade-slide-leave-active {
    transition: all 0.25s ease-out;
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(15px);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-15px);
}
</style>