<template>
    <tr>
        <td class="map-indent" :class="'map-indent-' + indentSize">
            <input v-if="!map.automaticEditEnabled"
                   type="text"
                   v-model="map.name"
                   class="form-control form-control-sm"/>
            <span v-else>
                {{map.name}}
            </span>
        </td>
        <td>
            <select v-if="selectableMaps.length > 1" :disabled="map.automaticEditEnabled" v-model="map.parentId">
                <option v-for="map in selectableMaps" :value="map.id">{{map.name}}</option>
            </select>
            <template v-else>
                {{map.name}}
            </template>
        </td>
        <td>
            <select v-if="selectableMapFiles.length > 1" :disabled="map.automaticEditEnabled" v-model="map.fileId">
                <option v-for="mapFile in selectableMapFiles" :value="mapFile.id">{{mapFile.filename}}</option>
            </select>
            <template v-else-if="selectableMapFiles.length === 1">
                {{selectedMapFileName}}
            </template>
        </td>
        <td>
            <span class="switch">
                <input type="checkbox"
                       class="switch"
                       :id="'switch-map-' + map.id"
                       :checked="map.automaticEditEnabled"
                       @change="toggleEdit">
                <label :for="'switch-map-' + map.id"></label>
            </span>
        </td>
        <td class="text-right">{{map.issueCount}}</td>
        <td>
            <button class="btn btn-danger" v-if="map.issueCount === 0" @click="$emit('removed')">
                <font-awesome-icon :icon="['fal', 'trash']"/>
            </button>
        </td>
    </tr>
</template>

<script>
    import bCard from 'bootstrap-vue/es/components/card/card'
    import bButton from 'bootstrap-vue/es/components/button/button'

    export default {
        name: 'map-tree-level',
        props: {
            map: {
                type: Object,
                required: true
            },
            orderedMaps: {
                type: Array,
                required: true
            },
            mapFiles: {
                type: Array,
                required: true
            },
            indentSize: {
                type: Number,
                required: true
            }
        },
        data() {
            return {
                beforeEditData: null,
                afterEditData: null,
            }
        },
        components: {
            bCard,
            bButton
        },
        methods: {
            getData: function (map) {
                return {
                    name: map.name,
                    parentId: map.parentId,
                    fileId: map.fileId,
                }
            },
            setData: function (map, data) {
                map.name = data.name;
                map.parentId = data.parentId;
                map.fileId = data.fileId;
            },
            toggleEdit: function () {
                const mapData = this.getData(this.map);
                if (this.map.automaticEditEnabled) {
                    if (this.afterEditData !== null) {
                        this.setData(this.map, this.afterEditData);
                    }
                } else {
                    this.afterEditData = mapData;
                    this.setData(this.map, this.beforeEditData);
                }

                this.map.automaticEditEnabled = !this.map.automaticEditEnabled;
            }
        },
        computed: {
            selectableMapFiles: function () {
                return this.mapFiles.filter(m => m.mapId === this.map.id)
            },
            selectableMaps: function () {
                // find first map not a child of this.map
                let skipUntilOrder = 0;
                let mapFound = false;
                for (let i = 0; i < this.orderedMaps.length; i++) {
                    const currentMap = this.orderedMaps[i];
                    if (!mapFound) {
                        if (currentMap === this.map) {
                            mapFound = true;
                        }
                    } else if (currentMap.indentSize <= this.map.indentSize) {
                        break;
                    }
                    skipUntilOrder++;
                }

                // can choose all maps before & all maps after & including the first map not a child of this.map
                return [{
                    id: null,
                    name: "-"
                }].concat(this.orderedMaps.filter(m => m.order < this.map.order || m.order >= skipUntilOrder));
            },
            selectedMapFileName: function () {
                const match = this.selectableMapFiles.filter(mf => this.map.fileId === mf.id);
                if (match.length === 1) {
                    return match[0].filename;
                }
                return "-";
            }
        },
        mounted() {
            this.beforeEditData = this.getData(this.map);
        }
    }
</script>