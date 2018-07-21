<template>
    <div class="custom-control custom-checkbox">
        <input type="checkbox"
               class="custom-control-input"
               :id="id"
               true-value="yes"
               false-value="no"
               v-model="checkboxValue">
        <label class="custom-control-label" :for="id">
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
        data: function() {
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
        mounted () {
            //get unique id of component for id attribute
            this.id = this._uid;
            this.checkboxValue = this.value ? 'yes' : 'no';
        }
    }
</script>