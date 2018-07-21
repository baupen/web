<template>
    <span>
        <span v-if="multiple">{{$t("actions.save_all")}}</span>
        <span v-else>{{$t("actions.save")}}</span>
    </span>
</template>

<script>

    export default {
        props: {
            multiple: {
                type: Boolean,
                required: true
            }
        }
    }
</script>