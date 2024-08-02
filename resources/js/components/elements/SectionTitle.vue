<template>
    <div class="h5 fw-bold">
        <span class="d-inline-block">
            {{ title }}
            <hr class="w-50 mt-2 mb-3 opacity-100 border-3 border-primary">
        </span>
        
    </div>
</template>

<script>
export default {
    props: ['title'],
}
</script>