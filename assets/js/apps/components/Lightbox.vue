<template>
    <div v-if="open" class="lightbox" @click="$emit('close')">
        <div class="lightbox-content">
            <img class="img-fluid preview-image" :src="imageSrc"/>
        </div>
        <div class="file-upload-field">
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