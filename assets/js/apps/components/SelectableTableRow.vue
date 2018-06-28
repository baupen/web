<template>
    <div class="col-1">
        <input type="checkbox" v-model="checked" />
        <slot></slot>
    </div>
</template>


<script>
    export default {
        props: {
            checked: {
                type: Boolean,
                required: true,
                default: false
            }
        }
    }
</script>