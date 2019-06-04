<template>
    <tr>
        <td class="map-indent" :class="'map-indent-' + this.mapContainer.indentSize">
            <input
                    type="text"
                    v-model.lazy="map.name"
                    class="form-control form-control-sm"/>
        </td>
        <td>
            <select class="form-control form-control-sm" v-if="selectableMaps.length > 1" v-model="map.parentId">
                <option v-for="map in selectableMaps" :value="map.id">{{map.name}}</option>
            </select>
            <template v-else>
                {{map.name}}
            </template>
        </td>
        <td>
            <select class="form-control form-control-sm" v-if="selectableMapFiles.length > 1" v-model="map.fileId">
                <option v-for="mapFile in selectableMapFiles" :value="mapFile.id">{{mapFile.filename}}</option>
            </select>
            <template v-else>
                {{selectedMapFileName}}
            </template>
        </td>
        <td class="text-right">{{map.issueCount}}</td>
        <td>
            <button class="btn btn-danger" v-if="canRemove" @click="$emit('remove')">
                <font-awesome-icon :icon="['fal', 'trash']"/>
            </button>
        </td>
    </tr>
</template>

<script>
    import bCard from 'bootstrap-vue/es/components/card/card'
    import bButton from 'bootstrap-vue/es/components/button/button'

    export default {
        props: {
            mapContainer: {
                type: Object,
                required: true
            },
            mapContainers: {
                type: Array,
                required: true
            },
            mapFileContainers: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
                map: this.mapContainer.map
            }
        },
        components: {
            bCard,
            bButton
        },
        methods: {
            hasParentId: function (id, parentId) {
                let currentParentId = this.mapIdToParentId[id];

                while (currentParentId !== null) {
                    if (currentParentId === parentId) {
                        return true;
                    }

                    currentParentId = this.mapIdToParentId[currentParentId];
                }

                return false;
            }
        },
        computed: {
            selectableMapFiles: function () {
                return this.mapFileContainers.map(mfc => mfc.mapFile).filter(m => m.mapId === this.map.id);
            },
            canRemove: function() {
                return this.map.issueCount === 0 && this.mapContainers.filter(m => m.map.parentId === this.map.id).length === 0;
            },
            mapIdToParentId: function() {
                let parentIdDictionary = [];
                this.mapContainers.forEach(mc => {
                    parentIdDictionary[mc.map.id] = mc.map.parentId;
                });

                return parentIdDictionary;
            },
            selectableMaps: function () {
                return [{
                    name: "-",
                    id: null
                }].concat(this.mapContainers.map(mc => mc.map).filter(m => m.id !== this.mapContainer.map.id && !this.hasParentId(m.id, this.mapContainer.map.id)));
            },
            selectedMapFileName: function () {
                const match = this.selectableMapFiles.filter(mf => this.map.fileId === mf.id);
                if (match.length === 1) {
                    return match[0].filename;
                }

                if (this.selectableMapFiles.length > 0)  {
                    return this.selectableMapFiles[0].filename;
                }

                return "-";
            }
        },
        watch: {
            map: {
                handler: function (after, before) {
                    // skip initial assign
                    if (before === null) {
                        return;
                    }

                    this.$emit('save');
                },
                deep: true,
            },
            selectableMapFiles: function (after, before) {
                // assign if before none selected
                if (before.length === 0 && after.length === 1) {
                    this.map.fileId = after[0].id;

                    this.$emit('save');
                } else if (before.length === 1 && after.length === 0) {
                    this.map.fileId = null;

                    this.$emit('save');
                }
            }
        }
    }
</script>
