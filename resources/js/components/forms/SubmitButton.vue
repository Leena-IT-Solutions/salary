<template>
    <button type="submit" :class="['btn btn-primary', button_classes]"
    :disabled="proxyChecked"
    @click="$emit('update:modelValue', $event.target.value)">
        {{ proxyChecked ? 'Saving...' : item_label }}
    </button>
</template>

<script>
export default {

    props: ['label', 'modelValue', 'classes'],

    data(){
        return {
            item_label: "Save",
            button_classes: "",
        };
    },

    methods: {
        init(){
            this.item_label = this.label ? this.label : this.item_label;
            this.button_classes = this.classes ? this.classes : "";
        },
    },

    created(){
        this.init();
    },

    computed: {
        proxyChecked(){
            return this.modelValue;
        },
    },

}
</script>