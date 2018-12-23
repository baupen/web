<template>
    <div id="edit">
        <p>
            <button class="btn btn-primary" @click="$emit('map-add')">
                {{$t("edit_maps.actions.add_map")}}
            </button>
            <button class="btn btn-outline-primary" @click="mapFileViewActive = !mapFileViewActive">
                <span v-if="!mapFileViewActive">
                    {{$t("edit_maps.actions.add_map_files")}}
                </span>
                <span v-else>
                    {{$t("edit_maps.actions.hide_map_files")}}
                </span>
            </button>
        </p>

        <map-file-view
                v-if="mapFileViewActive"
                :map-file-containers="mapFileContainers"
                :ordered-map-containers="orderedMapContainers"
                @file-dropped="$emit('map-file-dropped', arguments[0])"
                @start-upload="$emit('map-file-upload', arguments[0])"
                @abort-upload="$emit('map-file-abort-upload', arguments[0])"
                @save="$emit('map-file-save', arguments[0])"
        />
        <table v-if="mapContainers.length > 0" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>{{$t("map.name")}}</th>
                <th>{{$t("map.parent")}}</th>
                <th>{{$t("map_file.name")}}</th>
                <th>{{$t("set_automatically")}}</th>
                <th class="minimal-width">{{$t('issue_count')}}</th>
                <th class="minimal-width"></th>
            </tr>
            </thead>
            <tbody>
            <map-table-row v-for="mapContainer in orderedMapContainers"
                           :key="mapContainer.map.id"
                           :map-container="mapContainer"
                           :ordered-map-containers="orderedMapContainers"
                           :map-file-containers="mapFileContainers"
                           :indent-size="mapContainer.indentSize"
                           @remove="$emit('map-remove', mapContainer)"
                           @save="$emit('map-save', mapContainer)"/>
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
        computed: {
            orderedMapContainers: function () {
                this.setOrderProperties(this.displayedMapContainers, null, 0, 0);
                return this.displayedMapContainers.sort((m1, m2) => m1.order - m2.order);
            },
            displayedMapContainers: function () {
                return this.mapContainers.filter(m => m.pendingChange !== "remove");
            }
        },
        components: {
            MapFileView,
            MapTableRow
        },
        methods: {
            setOrderProperties: function (mapContainers, parentId, order, indent) {
                const children = mapContainers.filter(m => m.map.parentId === parentId);
                children.sort((c1, c2) => c1.map.name.localeCompare(c2.map.name));
                let maxOrder = order;
                children.forEach(c => {
                    c.order = maxOrder;
                    c.indentSize = indent;
                    maxOrder = this.setOrderProperties(mapContainers, c.map.id, maxOrder + 1, indent + 1);
                });

                return maxOrder;
            }
        }
    }

</script>