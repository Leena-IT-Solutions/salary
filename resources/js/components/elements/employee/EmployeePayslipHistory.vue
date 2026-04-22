<template>
    <div class="payslip-history-suite mt-5">
        <div class="workspace-vault shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="workspace-nav-header bg-white py-4 px-4 border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="fw-900 mb-0"><i class="bi bi-file-earmark-diff text-primary me-2"></i>Compensation Disbursal History</h5>
                        <p class="text-muted tiny mb-0 fw-semibold">Interactive ledger of historical salary slips, disbursements, and digital delivery.</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light-subtle">
                            <tr class="text-uppercase tiny fw-800 text-muted">
                                <th class="ps-4">Disbursal Period</th>
                                <th>Earnings Detail</th>
                                <th>Net Payable</th>
                                <th class="text-center">Email Status</th>
                                <th class="pe-4 text-end">Electronic Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in items" :key="row.id" class="transition-all hover-glow border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="period-icon me-3 bg-soft-primary text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="bi bi-calendar-check-fill"></i>
                                        </div>
                                        <div>
                                            <div class="fw-900 text-dark">{{ row.payroll?.payroll_name || 'Regular Cycle' }}</div>
                                            <div class="tiny text-muted fw-bold">Ref: #SLIP-{{ row.id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark">G: ₹ {{ row.gross_salary }}</div>
                                    <div class="tiny text-muted">Ded: ₹ {{ row.gross_deduction }}</div>
                                </td>
                                <td>
                                    <div class="h6 mb-0 fw-900 text-success">₹ {{ row.net_payable_amount }}</div>
                                </td>
                                <td class="text-center">
                                    <span v-if="emailingId === row.id" class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                    <i v-else class="bi bi-check2-circle text-success fs-5 opacity-50"></i>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a :href="'/payslip/single/' + row.id" target="_blank" class="btn btn-sm btn-white border shadow-sm rounded-pill px-3">
                                            <i class="bi bi-eye me-1 text-primary"></i> View
                                        </a>
                                        <a :href="'/pdf/single_payslip/' + row.id" target="_blank" class="btn btn-sm btn-white border shadow-sm rounded-pill px-3">
                                            <i class="bi bi-download me-1 text-success"></i> PDF
                                        </a>
                                        <button @click="sendEmail(row)" class="btn btn-sm btn-white border shadow-sm rounded-pill px-3" :disabled="emailingId === row.id">
                                            <i class="bi bi-send me-1 text-info"></i> Email
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="items.length === 0 && !loading">
                                <td colspan="5" class="text-center py-5 opacity-25">
                                    <i class="bi bi-wallet2 display-1"></i>
                                    <p class="mt-2 fw-bold">No historical disbursements discovered</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-4 text-center border-top bg-light-subtle" v-if="next_page_url">
                    <button class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold" @click="fetchHistory()" :disabled="loading">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-arrow-down-circle me-2"></i>
                        Load Historical Records
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    props: ['employee_id'],
    data() {
        return {
            loading: false,
            items: [],
            next_page_url: null,
            emailingId: null
        };
    },
    methods: {
        fetchHistory() {
            this.loading = true;
            let url = this.next_page_url || ('/payslip/history/' + this.employee_id);
            axios.get(url).then(res => {
                this.items = [...this.items, ...res.data.data];
                this.next_page_url = res.data.next_page_url;
            }).finally(() => {
                this.loading = false;
            });
        },
        sendEmail(row) {
            this.emailingId = row.id;
            axios.post('/payroll/send_email', {
                id: row.id,
                type: 'single'
            }).then(res => {
                alert('Payslip has been dispatched to employee email successfully.');
            }).finally(() => {
                this.emailingId = null;
            });
        }
    },
    created() {
        this.fetchHistory();
    }
}
</script>

<style scoped>
.payslip-history-suite { animation: slideUp 0.6s ease-out; }
@keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
.bg-soft-primary { background-color: #eef2ff; }
.tiny { font-size: 0.7rem; }
.fw-900 { font-weight: 900; }
.fw-800 { font-weight: 800; }
.fw-mono { font-family: ui-monospace, SFMono-Regular, monospace; }
.hover-glow:hover { background-color: #f8fafc; }
.btn-white { background: white; }
.btn-white:hover { background: #f8fafc; border-color: #cbd5e1; }
.transition-all { transition: all 0.2s ease; }
</style>
