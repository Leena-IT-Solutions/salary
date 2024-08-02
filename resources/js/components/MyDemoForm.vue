<template>
    <div class="row g-4">

        <forms-text-field name="fullname" label="Name" v-model="fullname" error="" classes=""></forms-text-field>

        <forms-date-field name="from" label="From Date" v-model="employeeShiftForm.from" error="" classes=""></forms-date-field>

        <forms-textarea-field name="message" label="Message" v-model="message" error="" classes=""></forms-textarea-field>

        <forms-select-field name="number" label="Number" v-model="number" error="" classes="" :options="options"></forms-select-field>

        <forms-radio-field name="gen" label="Select Gender" v-model="gen" error="" classes="" :options="gender"></forms-radio-field>

        <forms-checkbox-field name="book" label="Select Books" v-model="book" error="" classes="" :options="books"></forms-checkbox-field>

        <forms-file-field name="photo" label="Photo" v-model="photo" error="" classes=""></forms-file-field>

        <forms-submit-button name="" label="Update Profile"></forms-submit-button>

    </div>
</template>

<script>
export default {

    data(){
        return {
            fullname: "",
            message: "",
            number: "1",
            options: [
                { key: 'One', val: '1' },
                { key: 'Two', val: '2' },
            ],
            gender: [
                { key: 'Male', val: 'Male' },
                { key: 'Female', val: 'Female' },
            ],
            books: [
                { key: 'Bible', val: 'Bible' },
                { key: 'Maths', val: 'Maths' },
                { key: 'Coding is Game', val: 'Coding is Game' },
            ],
            gen : 'Male',
            book: ['Bible', 'Maths'],
            photo: null,
        };
    },

}
</script>