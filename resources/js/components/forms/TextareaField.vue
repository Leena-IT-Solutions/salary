<template>
    <div class="col-12">
        <div class="form-floating">
            <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="height: 100px;"
            :name="name"
            :class="[error ? 'is-invalid' : '']"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"></textarea>
            <label for="floatingTextarea">{{ item_label }}</label>

            <div v-if="error" class="text-danger small px-3 mt-1">
                {{ error }}
            </div>
        </div>
    </div>
</template>

<script>
export default {

    props: ['label', 'modelValue', 'classes', 'error', 'name'],

    data(){
        return {

            item_label: "Textarea Field",
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