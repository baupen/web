<template>
    <span>
        <span v-if="multiple">{{$t("edit.save_all")}}</span>
        <span v-else>{{$t("edit.save")}}</span>
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