<template>
    <div class="page-header-wrapper bg-white py-4 px-4 shadow-sm mb-1 d-flex align-items-center justify-content-between">
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item small text-secondary">Dashboard</li>
                    <li class="breadcrumb-item small text-primary active" aria-current="page">{{ title }}</li>
                </ol>
            </nav>
            <h3 class="m-0 fw-bold text-dark letter-spacing-1 text-uppercase small">{{ title }}</h3>
        </div>
        <div class="header-actions">
            <!-- Dynamic actions can go here -->
        </div>
    </div>
</template>

<script>
export default {
    props: ['title'],
}
</script>

<style scoped>
.page-header-wrapper {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.letter-spacing-1 {
    letter-spacing: 0.5px;
}
</style>