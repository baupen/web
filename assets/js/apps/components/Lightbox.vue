<template>
    <div v-if="open" class="lightbox" @click="$emit('close')">
        <div class="lightbox-content">
            <img :src="imageSrc"/>
            <slot></slot>
        </div>
        <font-awesome-icon class="lightbox-close" :icon="['fal', 'times']"/>
    </div>
</template>

<script>
    export default {
        props: {
            open: {
                type: Boolean,
                required: true
            },
            imageSrc: {
                type: String,
                required: false
            }
        }
    }
</script>