<template>
    <div class="clickable" @click.prevent.stop="$emit('toggle-mark')">
        <font-awesome-icon v-if="issue.isMarked" :icon="['fas', 'star']"/>
        <font-awesome-icon v-else :icon="['fal', 'star']"/>
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
