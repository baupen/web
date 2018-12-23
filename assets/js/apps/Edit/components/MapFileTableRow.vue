<template>
    <tr>
        <template v-if="mapFileContainer.pendingChange === 'upload'">
            <td>{{mapFile.filename}}</td>
            <td>{{formatDateTime(mapFile.createdAt)}}</td>
            <td>
                -
            </td>
            <td class="text-right">-</td>
        </template>
        <template v-else>
            <td>{{mapFile.filename}}</td>
            <td>{{formatDateTime(mapFile.createdAt)}}</td>
            <td>
                <select v-if="selectableMaps.length > 1" :disabled="mapFile.automaticEditEnabled"
                        v-model="mapFile.mapId">
                    <option v-for="map in selectableMaps" :value="map.id">{{map.name}}</option>
                </select>
                <template v-else>
                    {{selectedMapName}}
                </template>
            </td>
            <td class="text-right">{{mapFile.issueCount}}</td>
        </template>
    </tr>
</template>

<script>
    import bCard from 'bootstrap-vue/es/components/card/card'
    import bButton from 'bootstrap-vue/es/components/button/button'
    import moment from "moment";

    const lang = document.documentElement.lang.substr(0, 2);

    export default {
        props: {
            mapFileContainer: {
                type: Object,
                required: true
            },
            orderedMapContainers: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                beforeEditData: null,
                afterEditData: null,
                locale: lang,
                mapFile: this.mapFileContainer.mapFile
            }
        },
        components: {
            bCard,
            bButton
        },
        methods: {
            getData: function (mapFile) {
                return {
                    mapId: mapFile.mapId
                }
            },
            setData: function (mapFile, data) {
                mapFile.mapId = data.mapId;
            },
            toggleEdit: function () {
                const mapData = this.getData(this.mapFile);
                if (this.mapFile.automaticEditEnabled) {
                    if (this.afterEditData !== null) {
                        this.setData(this.mapFile, this.afterEditData);
                    }
                } else {
                    this.afterEditData = mapData;
                    this.setData(this.mapFile, this.beforeEditData);
                }

                this.mapFile.automaticEditEnabled = !this.mapFile.automaticEditEnabled;
            },
            formatDateTime: function (dateTime) {
                return moment(dateTime).locale(this.locale).fromNow();
            }
        },
        computed: {
            selectableMaps: function () {
                return this.orderedMapContainers.filter(m => m.pendingChange !== "remove").map(m => m.map);
            },
            selectedMapName: function () {
                const match = this.selectableMaps.filter(m => this.mapFile.mapId === m.id);
                if (match.length === 1) {
                    return match[0].name;
                }
                return "-";
            }
        },
        watch: {
            mapFile: {
                handler: function (after, before) {
                    // skip initial assign
                    if (before === null) {
                        return;
                    }

                    this.$emit('save');
                },
                deep: true,
            }
        },
        mounted() {
            this.beforeEditData = this.getData(this.mapFile);
        }
    }
</script>