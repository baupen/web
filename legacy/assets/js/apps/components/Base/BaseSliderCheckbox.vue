<template>
    <div class="switch-wrapper">
        <span class="switch">
            <input type="checkbox"
                   class="switch"
                   :id="'switch-' + id"
                   true-value="yes"
                   false-value="no"
                   v-model="checkboxValue">
            <label :for="'switch-' + id"></label>
        </span>
        <label class="col-form-label-sm" :for="id">
            <slot>
            </slot>
        </label>
    </div>
</template>

<script>
    export default {
        props: {
            value: Boolean
        },
        data: function () {
            return {
                checkboxValue: 'no',
                id: null
            }
        },
        watch: {
            checkboxValue: function () {
                //emit event to work as v-model
                this.$emit('input', this.checkboxValue === 'yes');
            }
        },
        mounted() {
            //get unique id of component for id attribute
            this.id = this._uid;
            this.checkboxValue = this.value ? 'yes' : 'no';
        }
    }
</script>