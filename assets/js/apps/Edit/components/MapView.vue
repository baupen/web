<template>
    <div>
        <p class="text-secondary">{{$t("edit_maps.help")}}</p>
        <p>
            <button class="btn btn-primary" @click="$emit('map-add')">
                {{$t("edit_maps.actions.add_map")}}
            </button>
            <button class="btn btn-outline-secondary" @click="$emit('maps-reorder')">
                {{$t("edit_maps.actions.reorder")}}
            </button>
        </p>

        <table v-if="mapContainers.length > 0" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>{{$t("map.name")}}</th>
                <th>{{$t("map.parent")}}</th>
                <th>{{$t("map_file._name")}}</th>
                <th class="minimal-width"></th>
                <th class="minimal-width">{{$t('issue_count')}}</th>
                <th class="minimal-width"></th>
            </tr>
            </thead>
            <tbody>
            <map-table-row v-for="mapContainer in mapContainers"
                           :key="mapContainer.map.id"
                           :map-container="mapContainer"
                           :map-containers="mapContainers"
                           :map-file-containers="mapFileContainers"
                           @remove="$emit('map-remove', mapContainer)"
                           @save="$emit('map-save', mapContainer)"
                           @draw="$emit('map-draw', mapContainer)"/>
            </tbody>
        </table>
    </div>
</template>

<script>
    import moment from "moment";
    import MapTableRow from "./MapTableRow";
    import MapFileView from "./MapFileView";

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        props: {
            mapContainers: {
                type: Array,
                required: true
            },
            mapFileContainers: {
                type: Array,
                required: true
            }
        },
        data: function () {
            return {
                locale: lang,
                mapFileViewActive: false
            }
        },
        components: {
            MapFileView,
            MapTableRow
        }
    }

</script>
