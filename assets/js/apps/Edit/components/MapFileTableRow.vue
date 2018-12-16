<template>
    <tr>
        <td>{{mapFile.filename}}</td>
        <td>{{formatDateTime(mapFile.createdAt)}}</td>
        <td>
            <select v-if="selectableMaps.length > 1" :disabled="mapFile.automaticEditEnabled" v-model="mapFile.mapId">
                <option v-for="map in selectableMaps" :value="map.id">{{map.name}}</option>
            </select>
            <template v-else>
                {{selectedMapName}}
            </template>
        </td>
        <td class="text-right">{{mapFile.issueCount}}</td>
    </tr>
</template>

<script>
    import bCard from 'bootstrap-vue/es/components/card/card'
    import bButton from 'bootstrap-vue/es/components/button/button'
    import moment from "moment";

    const lang = document.documentElement.lang.substr(0, 2);

    export default {
        props: {
            mapFile: {
                type: Object,
                required: true
            },
            orderedMaps: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                beforeEditData: null,
                afterEditData: null,
                locale: lang
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
                return this.orderedMaps;
            },
            selectedMapName: function () {
                const match = this.selectableMaps.filter(m => this.mapFile.mapId === m.id);
                if (match.length === 1) {
                    return match[0].name;
                }
                return "-";
            }
        },
        mounted() {
            this.beforeEditData = this.getData(this.mapFile);
        }
    }
</script>