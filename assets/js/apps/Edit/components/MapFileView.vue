<template>
    <div class="jumbotron" :class="{'drag-active': dragActive}"
         @dragover="dragEventOccurred($event, true)"
         @dragenter="dragEventOccurred($event, true)"
         @dragleave="dragEventOccurred($event, false)"
         @dragend="dragEventOccurred($event, false)"
         @drop="filesDropped($event)"
    >
        <h2>{{$t("map_file.plural")}}</h2>
        <p class="text-secondary">{{$t("edit_map_files.help")}}</p>
        <p class="alert alert-info">{{$t("edit_map_files.drag_files_to_upload")}}</p>
        <table v-if="orderedMapFiles.length > 0" class="table table-hover table-sm">
            <thead>
            <tr>
                <th>{{$t("map_file.name")}}</th>
                <th>{{$t("map_file.created_at")}}</th>
                <th>{{$t("map.name")}}</th>
                <th class="minimal-width">{{$t('issue_count')}}</th>
            </tr>
            </thead>
            <tbody>
            <MapFileTableRow v-for="mapFile in orderedMapFiles" :map-file="mapFile" :ordered-maps="orderedMaps"/>
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
    import bCard from 'bootstrap-vue/es/components/card/card'
    import bButton from 'bootstrap-vue/es/components/button/button'
    import moment from "moment";
    import MapFileTableRow from "./MapFileTableRow";

    const lang = document.documentElement.lang.substr(0, 2);

    export default {
        props: {
            mapFiles: {
                type: Array,
                required: true
            },
            orderedMaps: {
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
            MapFileTableRow,
            bCard,
            bButton
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
                console.log(e);
                let droppedFiles = e.dataTransfer.files;
                console.log(droppedFiles);
                this.dragActive = false;

                var ajaxData = new FormData();
                droppedFiles.forEach(f => ajaxData.append("file", f));

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: ajaxData,
                    dataType: 'json',
                    complete: function() {
                        $form.removeClass('is-uploading');
                    },
                    success: function(data) {

                    },
                    error: function() {
                        // Log the error, show an alert, whatever works for you
                    }
                });
            }
        },
        computed: {
            orderedMapFiles: function () {
                return this.mapFiles.sort((mf1, mf2) => mf1.filename.localeCompare(mf2.filename));
            }
        }
    }
</script>