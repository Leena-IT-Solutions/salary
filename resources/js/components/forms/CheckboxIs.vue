<template>
    <div class="col-12">

        <div class="form-check">
            <input class="form-check-input" type="checkbox"
            :checked="modelValue"
            :value="modelValue" 
            :id="name" 
            :name="name"
            @change="$emit('update:modelValue', $event.target.checked)">
            <label class="form-check-label" :for="name">{{ item_label }}</label>
        </div>

        <div v-if="error" class="text-danger small px-3 mt-1">
            {{ error }}
        </div>
        
    </div>
</template>

<script>
export default {

    props: ['label', 'modelValue', 'classes', 'error', 'name'],

    data(){
        return {

            item_label: "Upload File",
            item_classes: "col-12",
            
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

}
</script>