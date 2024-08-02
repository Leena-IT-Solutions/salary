<template>
    <div :class="item_classes">
        <input type="submit" class="btn btn-primary"
        :disabled="proxyChecked"
        :value="proxyChecked ? 'Saving...' : item_label"
        @click="$emit('update:modelValue', $event.target.value)">
    </div>
</template>

<script>
export default {

    props: ['label', 'modelValue', 'classes'],

    data(){
        return {
            item_label: "Save",
        };
    },

    methods: {
        init(){
            this.item_label = this.label ? this.label : this.item_label;
            this.item_classes = this.classes ? this.classes : this.item_classes;
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