<template>
    <div :class="item_classes">
        <div class="form-floating">
            <select class="form-select" id="floatingSelect" aria-label="Floating label select example"
            placeholder=""
            :name="name"
            :class="[error ? 'is-invalid' : '']"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)">
                <option :value="null" selected>Select {{ item_label }}</option>
                <option v-for="opt,ind in options" :key="ind" :value="opt.val">{{ opt.key }}</option>
            </select>
            <label for="floatingSelect">{{ item_label }}</label>
            <div v-if="error" class="text-danger small px-3 mt-1">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script>
export default {

    props: ['label', 'modelValue', 'classes', 'error', 'options', 'name'],

    data(){
        return {

            item_label: "Select List",
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