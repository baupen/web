<template>
    <div class="form-group">
        <label class="col-form-label" :for="id">
            <slot>

            </slot>
        </label>
        <textarea :id="id" class="form-control"
                  rows="4"
                  v-bind:value="value"
                  v-on:input="$emit('input', $event.target.value)">
        </textarea>
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