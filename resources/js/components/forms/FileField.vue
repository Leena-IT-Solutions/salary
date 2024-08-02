<template>
    <div :class="item_classes">
        <div class="input-group">
            <label class="input-group-text" for="inputGroupFile01">{{ item_label }}</label>
            <input type="file" class="form-control upload shadow-none" id="inputGroupFile01"
            :name="name"
            :class="[error ? 'is-invalid' : '']"
            :value="modelValue"
            @change="update($event)">
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

            item_label: "Text Field",
            item_classes: "col-12",
            
        };
    },

    methods: {

        update(ev){
            this.$emit('update:modelValue', ev.target.value);
        },

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

<style>
.upload::file-selector-button{
    display: none;
}
</style>