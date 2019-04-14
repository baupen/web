<template>
    <div>
        <p>
            <button class="btn btn-primary" @click="$emit('map-add')">
                {{$t("edit_maps.actions.add_map")}}
            </button>
        </p>

        <table v-if="mapContainers.length > 0" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>{{$t("map.name")}}</th>
                <th>{{$t("map.parent")}}</th>
                <th>{{$t("map_file.name")}}</th>
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
                           :has-children="mapContainer.hasChildren"
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
                    c.hasChildren = mapContainers.filter(m => m.map.parentId === c.map.id).length > 0;
                    maxOrder = this.setOrderProperties(mapContainers, c.map.id, maxOrder + 1, indent + 1);
                });

                return maxOrder;
            }
        }
    }

</script>