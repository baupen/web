<template>
    <div class="form-group">
        <label class="col-form-label-sm" :for="id">
            <slot>

            </slot>
        </label>
        <input :id="id" class="form-control form-control-sm"
               type="text"
               v-bind:value="value"
               v-on:input="$emit('input', $event.target.value)"/>
    </div>
</template>

<script>
    export default {
        props: {
            value: String
        },
        data: function () {
            return {
                id: null
            }
        },
        mounted() {
            this.id = this._uid;
        }
    }
</script>