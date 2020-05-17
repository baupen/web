<template>
    <div class="clickable" @click.prevent.stop="$emit('toggle-was-added-with-client')">
        <font-awesome-icon v-if="issue.wasAddedWithClient" :icon="['fas', 'user-check']"/>
        <font-awesome-icon v-else :icon="['fal', 'user-check']"/>
    </div>
</template>


<script>
    export default {
        props: {
            issue: {
                type: Object,
                required: true
            }
        }
    }

</script>
