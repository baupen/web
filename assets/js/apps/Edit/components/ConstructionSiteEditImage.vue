<template>
    <div :class="{'drag-active': dragActive}" class="image-container"
         @dragover="dragEventOccurred($event, true)"
         @dragenter="dragEventOccurred($event, true)"
         @dragleave="dragEventOccurred($event, false)"
         @dragend="dragEventOccurred($event, false)"
         @drop="filesDropped($event)"
    >
        <p class="alert alert-info overlay">{{$t("edit_construction_site.drag_files_to_upload")}}</p>
        <img :src="constructionSite.imageMedium" class="img-fluid" alt="header image">
    </div>
</template>

<style scoped>
    .image-container {
        position: relative;
    }

    .overlay {
        position: absolute;
        bottom: 0;
        margin-bottom: 0;
        opacity: 0.9;
    }
</style>

<script>
    export default {
        data: function () {
            return {
                dragActive: false
            }
        },
        props: {
            constructionSite: {
                type: Object,
                required: true
            }
        },
        methods: {
            submit: function () {
                this.$v.$touch();
                if (!this.$v.$invalid) {
                    this.constructionSite.streetAddress = this.streetAddress;
                    this.constructionSite.postalCode = this.postalCode;
                    this.constructionSite.locality = this.locality;
                    this.$emit("construction-site-save");
                }
            },
            dragEventOccurred: function (e, newDragActiveState) {
                e.preventDefault();
                e.stopPropagation();
                this.dragActive = newDragActiveState;
            },
            filesDropped: function (e) {
                e.preventDefault();
                e.stopPropagation();

                let droppedFiles = e.dataTransfer.files;
                for (let i = 0; i < droppedFiles.length; i++) {
                    if (droppedFiles[i].name.endsWith(".jpg") || droppedFiles[i].name.endsWith(".png") || droppedFiles[i].name.endsWith(".jpeg")) {
                        this.$emit('file-dropped', droppedFiles[i]);
                        break;
                    }
                }

                this.dragActive = false;
            },
        }
    }
</script>
