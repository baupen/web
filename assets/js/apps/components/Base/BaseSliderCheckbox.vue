<template>
    <div class="switch-wrapper">
        <label class="col-form-label-sm" :for="id">
            <slot>
            </slot>
        </label><br/>
        <label class="switch">
            <input type="checkbox"
                   true-value="yes"
                   false-value="no"
                   v-model="checkboxValue"
                   :id="id">
            <span class="slider"></span>
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