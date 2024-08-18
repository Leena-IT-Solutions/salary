<template>
    <div>

        <!-- Navigation Buttons -->
        <div class="mb-5">
            <button :class="[what == 'Photo' ? 'active' : '']" @click="what = 'Photo'" class="btn btn-outline-primary btn-sm me-2">Photo</button>
            <button :class="[what == 'Documents' ? 'active' : '']" @click="what = 'Documents'" class="btn btn-outline-primary btn-sm me-2">Documents</button>
            <button :class="[what == 'Address' ? 'active' : '']" @click="what = 'Address'" class="btn btn-outline-primary btn-sm me-2">Address</button>
            <button :class="[what == 'Work Location' ? 'active' : '']" @click="what = 'Work Location'" class="btn btn-outline-primary btn-sm me-2">Work Location</button>
            <button :class="[what == 'Department' ? 'active' : '']" @click="what = 'Department'" class="btn btn-outline-primary btn-sm me-2">Department</button>
            <button :class="[what == 'Designation' ? 'active' : '']" @click="what = 'Designation'" class="btn btn-outline-primary btn-sm me-2">Designation</button>
            <button :class="[what == 'Leave Group' ? 'active' : '']" @click="what = 'Leave Group'" class="btn btn-outline-primary btn-sm me-2">Leave Group</button>
            <button :class="[what == 'Salary' ? 'active' : '']" @click="what = 'Salary'" class="btn btn-outline-primary btn-sm me-2">Salary</button>
            <button :class="[what == 'Services' ? 'active' : '']" @click="what = 'Services'" class="btn btn-outline-primary btn-sm me-2">Services</button>
            <button :class="[what == 'Bank' ? 'active' : '']" @click="what = 'Bank'" class="btn btn-outline-primary btn-sm me-2">Bank</button>
            <button v-if="what != null" @click="what = null" class="btn btn-sm btn-danger">Close</button>
        </div>

        <employee-photo :employee_id="employee.id" v-if="what=='Photo'"></employee-photo>
        <employee-documents :employee_id="employee.id" v-if="what=='Documents'"></employee-documents>
        <employee-address :employee_id="employee.id" v-if="what=='Address'"></employee-address>
        <employee-work-location :locations="locations" :employee_id="employee.id" v-if="what=='Work Location'"></employee-work-location>
        <employee-designation :designations="designations" :employee_id="employee.id" v-if="what=='Designation'"></employee-designation>
        <employee-department :departments="departments" :employee_id="employee.id" v-if="what=='Department'"></employee-department>
        <employee-leave-group :leave_groups="leave_groups" :employee_id="employee.id" v-if="what=='Leave Group'"></employee-leave-group>
        <employee-salary :salary_groups="salary_groups" :employee="employee" v-if="what=='Salary'"></employee-salary>
        <employee-services :employee_id="employee.id" :services="services" v-if="what=='Services'"></employee-services>
        <employee-bank :employee_id="employee.id" v-if="what=='Bank'"></employee-bank>


    </div>
</template>

<script>
export default {

    props: ['employee', 'work_locations', 'designations', 'departments', 'leave_groups', 'salary_groups', 'services'],

    data(){
        return {
            what: null, // Photo | Documents | Address | Work Location | Designation | Department
            locations : [],
        };
    },

    created(){
        /* this.locations.push({
            key: 'Head Office',
            val: 0
        }); */

        this.work_locations.forEach(loc => {
            this.locations.push(loc);
        });
    },

}
</script>