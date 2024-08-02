<template>
    <div class="col-12">

        <div class="mb-2">
            <label class="">{{ item_label }}</label>
        </div>

        <div class="form-check" v-for="opt, ind in options" :key="ind">
            <input class="form-check-input" type="checkbox"
            :checked="modelValue.includes(opt.val)"
            :value="opt.val" 
            :id="opt.key + ind" 
            :name="name"
            @change="update($event)">
            <label class="form-check-label" :for="opt.key + ind">{{ opt.key }}</label>
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

            item_label: "Upload File",
            item_classes: "col-12",
            
        };
    },

    methods: {

        update(ev){
            let a = this.modelValue;
            if(ev.target.checked){
                a.push(ev.target.value);
            } else {
                const index = this.modelValue.indexOf(ev.target.value);
                const x = this.modelValue.splice(index, 1);
            }
        },

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