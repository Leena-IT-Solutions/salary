<template>
    <div class="pending-approvals-container">
        <!-- Stats Overview -->
        <div class="row g-4 mb-5">
            <div class="col-xl col-lg-4 col-md-6" v-for="stat in statsDisplay" :key="stat.label" data-aos="fade-up" :data-aos-delay="stat.delay">
                <div class="stat-card" :class="{ 'active': filterType === stat.type }" @click="setFilter(stat.type)">
                    <div class="stat-icon" :style="{ background: stat.bg }">
                        <i :class="stat.icon"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-label">{{ stat.label }}</span>
                        <h3 class="stat-value">{{ stat.value }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Table -->
        <div class="card main-card overflow-hidden" data-aos="fade-up" data-aos-delay="400">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Queued for Review</h5>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" v-model="search" placeholder="Search requests...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Requested On</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="request in filteredRequests" :key="request.type + request.id">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3">
                                            {{ request.employee.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-white">{{ request.employee }}</div>
                                            <small class="text-muted">{{ request.employee_code }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="type-badge" :style="{ background: getTypeColor(request.type) }">
                                        {{ request.type }}
                                    </span>
                                </td>
                                <td>{{ request.date }}</td>
                                <td>
                                    <span v-if="request.status" class="status-badge" :class="request.status.toLowerCase()">
                                        {{ request.status }}
                                    </span>
                                    <span v-else class="status-badge pending">Pending</span>
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <button class="btn btn-icon text-success" @click="updateStatus(request, 'Approved')" title="Approve">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button class="btn btn-icon text-danger" @click="updateStatus(request, 'Rejected')" title="Reject">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        <button class="btn btn-icon text-primary" @click="viewDetails(request)" title="View Reason">
                                            <i class="bi bi-info-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="requests.length === 0">
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-clipboard-check mb-3"></i>
                                        <p>No pending approvals at the moment.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center py-3">
                <small class="text-muted">Showing {{ requests.length }} entries</small>
                <div class="pagination-controls">
                    <button class="btn btn-sm btn-outline-light me-2" disabled>
                        <i class="bi bi-chevron-left"></i> Previous
                    </button>
                    <button class="btn btn-sm btn-outline-light" disabled>
                        Next <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Details Modal -->
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
            <div class="custom-modal animate__animated animate__zoomIn">
                <div class="modal-header-custom">
                    <h5 class="mb-0">{{ selectedRequest.type }} Details</h5>
                    <button class="btn-close-custom" @click="closeModal"><i class="bi bi-x"></i></button>
                </div>
                <div class="modal-body-custom">
                    <div class="detail-row">
                        <span class="detail-label">Employee</span>
                        <span class="detail-value fw-bold text-indigo">{{ selectedRequest.employee }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Employee Code</span>
                        <span class="detail-value">{{ selectedRequest.employee_code }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Request Date</span>
                        <span class="detail-value">{{ selectedRequest.date }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Specifics</span>
                        <span class="detail-value text-white">{{ selectedRequest.details }}</span>
                    </div>
                    <hr class="modal-divider">
                    <div class="reason-section">
                        <span class="detail-label">Reason provided by employee:</span>
                        <p class="reason-text">{{ selectedRequest.reason || 'No specific reason provided.' }}</p>
                    </div>
                </div>
                <div class="modal-footer-custom">
                    <button class="btn btn-primary px-4 rounded-pill" @click="closeModal">Close Details</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: ['fy'],
    data() {
        return {
            search: '',
            filterType: 'Total',
            requests: [], // Will be populated from API
            stats: {
                total: 0,
                leave: 0,
                time: 0,
                short: 0,
                overtime: 0,
            },
            loading: false,
            showModal: false,
            selectedRequest: null,
        };
    },
    computed: {
        statsDisplay() {
            return [
                { label: 'Total Pending', value: this.stats.total, icon: 'bi bi-clock', bg: 'linear-gradient(135deg, #6366f1, #818cf8)', delay: 0, type: 'Total' },
                { label: 'Leave Requests', value: this.stats.leave, icon: 'bi bi-calendar-check', bg: 'linear-gradient(135deg, #f59e0b, #fbbf24)', delay: 100, type: 'Leave' },
                { label: 'Time Updates', value: this.stats.time, icon: 'bi bi-clock-history', bg: 'linear-gradient(135deg, #10b981, #34d399)', delay: 200, type: 'Time Update' },
                { label: 'Short Leaves', value: this.stats.short, icon: 'bi bi-hourglass-split', bg: 'linear-gradient(135deg, #3b82f6, #60a5fa)', delay: 300, type: 'Short Leave' },
                { label: 'Overtime', value: this.stats.overtime, icon: 'bi bi-stopwatch', bg: 'linear-gradient(135deg, #ef4444, #f87171)', delay: 400, type: 'Overtime' },
            ]
        },
        filteredRequests() {
            let filtered = this.requests;
            
            if (this.filterType !== 'Total') {
                filtered = filtered.filter(r => r.type === this.filterType);
            }
            
            if (this.search) {
                const s = this.search.toLowerCase();
                filtered = filtered.filter(r => 
                    r.employee.toLowerCase().includes(s) || 
                    r.employee_code.toLowerCase().includes(s) ||
                    r.type.toLowerCase().includes(s) ||
                    (r.reason && r.reason.toLowerCase().includes(s))
                );
            }
            
            return filtered;
        }
    },
    methods: {
        setFilter(type) {
            this.filterType = type;
        },
        getTypeColor(type) {
            const colors = {
                'Leave': 'rgba(245, 158, 11, 0.2)',
                'Time': 'rgba(16, 185, 129, 0.2)',
                'On Duty': 'rgba(99, 102, 241, 0.2)',
                'Overtime': 'rgba(239, 68, 68, 0.2)',
            };
            return colors[type] || 'rgba(156, 163, 175, 0.2)';
        },
        fetchRequests() {
            this.loading = true;
            axios.get('/approvals/pending/fetch').then(res => {
                this.requests = res.data.data;
                this.stats = res.data.stats;
            }).finally(() => {
                this.loading = false;
            });
        },
        updateStatus(request, status) {
            if (confirm(`Are you sure you want to change this ${request.type} request to ${status}?`)) {
                axios.post('/approvals/pending/updateStatus', {
                    id: request.id,
                    type: request.type,
                    status: status
                }).then(res => {
                    alert(res.data.message);
                    this.fetchRequests();
                }).catch(err => {
                    alert('Error: Something went wrong');
                });
            }
        },
        viewDetails(request) {
            this.selectedRequest = request;
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.selectedRequest = null;
        }
    },
    mounted() {
        this.fetchRequests();
    }
};
</script>

<style lang="scss" scoped>
.pending-approvals-container {
    color: #f8fafc;
}

.stat-card {
    cursor: pointer;
    background: #1e293b; 
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1.25rem;
    padding: 1.25rem 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    height: 100%;

    &.active {
        background: #2d3748;
        border-color: #6366f1;
        box-shadow: 0 0 0 2px #6366f1;
    }

    &:hover {
        transform: translateY(-5px);
        background: #2d3748;
        border-color: rgba(255, 255, 255, 0.2);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        flex-shrink: 0;
    }

    .stat-label {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.7); /* Brighter for contrast */
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: block;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0;
        color: white;
    }
}

.main-card {
    background: #1e293b; /* Solid dark background */
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);

    .card-header {
        background: rgba(255, 255, 255, 0.05);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1.5rem;

        h5 {
            color: #f8fafc;
            font-weight: 600;
        }
    }

    .card-body {
        background: #1e293b; /* Ensure table body is dark */
    }
}

.search-box {
    position: relative;
    i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.6);
    }
    input {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 0.75rem;
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        color: white;
        font-size: 0.9rem;
        width: 300px;
        transition: all 0.2s ease;

        &::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        &:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.12);
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
    }
}

.table {
    background: #1e293b !important;
    margin-bottom: 0;

    thead th {
        background: rgba(255, 255, 255, 0.05) !important;
        color: rgba(255, 255, 255, 0.8) !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    tbody tr {
        background: #1e293b !important;
        transition: all 0.2s ease;
    }

    tbody td {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: rgba(255, 255, 255, 0.95) !important;
        background: transparent !important;
    }

    tbody tr:hover {
        background: rgba(255, 255, 255, 0.05) !important;
    }
}

.avatar {
    width: 40px;
    height: 40px;
    background: #6366f1;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
}

.type-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 500;
    color: white;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;

    &.pending { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
    &.approved { background: rgba(16, 185, 129, 0.2); color: #34d399; }
    &.rejected { background: rgba(239, 68, 68, 0.2); color: #f87171; }
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    background: rgba(255, 255, 255, 0.05);
    margin-left: 0.5rem;
    transition: all 0.2s ease;

    &:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: scale(1.1);
    }
}

.empty-state {
    padding: 3rem 0;
    i {
        font-size: 3rem;
        color: rgba(255, 255, 255, 0.3);
        display: block;
    }
    p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 1.1rem;
        margin-top: 1rem;
        font-weight: 500;
    }
}

.pagination-controls {
    .btn {
        border-color: rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.8) !important;
        &:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.1);
            color: white !important;
        }
        &:disabled {
            opacity: 0.4;
            color: rgba(255, 255, 255, 0.4) !important;
        }
    }
}

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
    z-index: 9999;
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

    h5 {
        color: white;
        font-weight: 600;
        margin: 0;
    }
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
    transition: all 0.2s;

    &:hover {
        background: rgba(255, 255, 255, 0.2);
    }
}

.modal-body-custom {
    padding: 2rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    font-size: 0.95rem;
}

.detail-label {
    color: rgba(255, 255, 255, 0.7);
}

.detail-value {
    color: #ffffff;
}

.text-indigo {
    color: #a5b4fc !important;
}

.modal-divider {
    border-color: rgba(255, 255, 255, 0.1);
    margin: 1.5rem 0;
}

.reason-section {
    .detail-label {
        display: block;
        margin-bottom: 0.75rem;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 500;
    }
    
    .reason-text {
        background: rgba(0, 0, 0, 0.3);
        padding: 1.25rem;
        border-radius: 1rem;
        font-style: italic;
        line-height: 1.6;
        color: rgba(255, 255, 255, 0.9) !important;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
}

.modal-footer-custom {
    padding: 1.5rem;
    padding-top: 0;
    display: flex;
    justify-content: center;
}

.card-footer {
    background: rgba(255, 255, 255, 0.02);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.7) !important;
    padding: 1.25rem 1.5rem;
}
</style>
