<template>
    <div :class="item_classes">

        <div class="mb-2">
            <label class="">{{ item_label }}</label>
        </div>

        <div class="form-check" v-for="opt, ind in options" :key="ind">
            <input class="form-check-input" type="radio" 
            :name="name" 
            :id="opt.val + '_' + ind" 
            :value="opt.val"
            :checked="opt.val == modelValue"
            @input="$emit('update:modelValue', $event.target.value)">
            <label class="form-check-label" :for="opt.val + '_' + ind">{{ opt.key }}</label>
        </div>

        <div v-if="error" class="text-danger small px-3 mt-1">
            {{ error }}
        </div>
        
    </div>
</template>

<script>
export default {

    props: ['label', 'modelValue', 'classes', 'error', 'options', 'name'],

    data(){
        return {

            item_label: "Radio List",
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