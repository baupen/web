<template>
    <div class="form-group">
        <label class="col-form-label-sm" :for="id">
            <slot>

            </slot>
        </label>
        <input :id="id" class="form-control form-control-sm" type="date"
               v-model="dateValue"/>
    </div>
</template>

<script>
    export default {
        props: {
            value: String
        },
        data: function () {
            return {
                dateValue: null,
                id: null
            }
        },
        watch: {
            dateValue: function () {
                //emit event to work as v-model
                console.log("emmitting");
                this.$emit('input', this.dateValue);
            }
        },
        mounted() {
            //get unique id of component for id attribute
            this.id = this._uid;
            this.dateValue = this.value;
        }
    }
</script>