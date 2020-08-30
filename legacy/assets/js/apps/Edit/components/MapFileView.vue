<template>
    <div :class="{'drag-active': dragActive}"
         @dragover="dragEventOccurred($event, true)"
         @dragenter="dragEventOccurred($event, true)"
         @dragleave="dragEventOccurred($event, false)"
         @dragend="dragEventOccurred($event, false)"
         @drop="filesDropped($event)"
    >
        <p class="text-secondary">{{$t("edit_map_files.help")}}</p>
        <p class="alert alert-info">{{$t("edit_map_files.drag_files_to_upload")}}</p>
        <table v-if="mapFileContainers.length > 0" class="table table-hover table-sm">
            <thead>
            <tr>
                <th>{{$t("map_file.name")}}</th>
                <th>{{$t("map_file.created_at")}}</th>
                <th>{{$t("map._name")}}</th>
                <th class="minimal-width">{{$t('issue_count')}}</th>
            </tr>
            </thead>
            <tbody>
            <map-file-table-row
                    v-for="mapFileContainer in orderedMapFileContainers"
                    :key="mapFileContainer.mapFile.id"
                    :map-file-container="mapFileContainer"
                    :map-file-containers="mapFileContainers"
                    :map-containers="mapContainers"
                    @start-upload="$emit('start-upload', mapFileContainer)"
                    @save="$emit('save', mapFileContainer)"
                    @abort-upload="$emit('abort-upload', mapFileContainer)"/>
            </tbody>
        </table>
    </div>
</template>

<style>
    .drag-active {
        border: solid 2px black
    }
</style>

<script>
    import MapFileTableRow from "./MapFileTableRow";

    const lang = document.documentElement.lang.substr(0, 2);

    export default {
        props: {
            mapFileContainers: {
                type: Array,
                required: true
            },
            mapContainers: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                locale: lang,
                dragActive: false,
            }
        },
        components: {
            MapFileTableRow
        },
        methods: {
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
                    if (droppedFiles[i].name.endsWith(".pdf")) {
                        this.$emit('file-dropped', droppedFiles[i]);
                    }
                }

                this.dragActive = false;
            },
        },
        computed: {
            orderedMapFileContainers: function () {
                return this.mapFileContainers.sort((mf1, mf2) => mf1.mapFile.filename.localeCompare(mf2.mapFile.filename));
            }
        }
    }
</script>
