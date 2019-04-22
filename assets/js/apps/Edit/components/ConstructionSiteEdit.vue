<template>
    <div class="d-flex">
        <construction-site-edit-image class="edit-column mr-4" :construction-site="constructionSite"
                                      @file-dropped="$emit('upload-image', arguments[0])"/>
        <construction-site-edit-form class="edit-column" :construction-site="constructionSite"
                                       @save="$emit('save')"/>
    </div>
</template>

<style scoped>
    .edit-column {
        max-width: 30em;
        min-height: 20em;
    }
</style>

<script>
    import bButton from 'bootstrap-vue/es/components/button/button'
    import {required} from 'vuelidate/lib/validators'
    import axios from 'axios';
    import debounce from 'debounce';
    import ConstructionSiteEditForm from "./ConstructionSiteEditForm";
    import ConstructionSiteEditImage from "./ConstructionSiteEditImage";

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
                    }
                }

                this.dragActive = false;
            },
        },
        components: {
            ConstructionSiteEditImage,
            ConstructionSiteEditForm
        }
    }
</script>
