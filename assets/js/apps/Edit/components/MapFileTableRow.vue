<template>
    <tr>
        <td>
            {{mapFile.filename}}
            <div v-if="mapFileContainer.pendingChange === 'upload_check'" class="alert alert-info">
                {{$t("edit_map_files.performing_upload_check", {files: sameMapFileNames})}}
            </div>
            <div v-else-if="mapFileContainer.pendingChange === 'confirm_upload'" class="alert alert-warning">
                <span v-if="mapFileContainer.uploadCheck">
                    <span v-if="sameMapFileNames.length > 0">
                        {{$t("edit_map_files.identical_content_than", {files: sameMapFileNames})}}
                        <br/>
                    </span>
                    <span v-if="mapFileContainer.uploadCheck.derivedFileName !== mapFile.filename">
                        {{$t("edit_map_files.identical_name", {new_name: mapFileContainer.uploadCheck.derivedFileName})}}
                        <br/>
                    </span>
                </span>
                <div class="btn-group">
                    <button class="btn btn-outline-danger" @click="$emit('abort-upload')">
                        {{$t("edit_map_files.actions.abort_upload") }}
                    </button>
                    <button class="btn btn-primary" @click="$emit('start-upload')">
                        {{$t("edit_map_files.actions.confirm_upload")}}
                    </button>
                </div>
            </div>
            <div v-else-if="mapFileContainer.pendingChange === 'finish_upload'" class="alert alert-info">
                {{$t("edit_map_files.upload_active", {percentage: mapFileContainer.uploadProgress})}}
            </div>
        </td>
        <td>{{formatDateTime(mapFile.createdAt)}}</td>
        <td>
            <select class="form-control form-control-sm" v-if="selectableMaps.length > 1"
                    :disabled="mapFile.automaticEditEnabled || [null, 'update'].indexOf(mapFileContainer.pendingChange) === false"
                    v-model="mapFile.mapId">
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
            mapFileContainer: {
                type: Object,
                required: true
            },
            mapFileContainers: {
                type: Array,
                required: true
            },
            orderedMapContainers: {
                type: Array,
                required: true
            }
        },
        data() {
            return {
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
            formatDateTime: function (dateTime) {
                return moment(dateTime).locale(this.locale).fromNow();
            },

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
            },
            mapFileHashMap: function () {
                let array = [];
                this.mapFileContainers.map(mfc => mfc.mapFile).forEach(mf => array[mf.id] = mf);
                return array;
            },
            sameMapFileNames: function () {
                if (this.mapFileContainer.uploadCheck === null || this.mapFileContainer.uploadCheck.sameHashConflicts.length === 0) {
                    return [];
                }

                return this.mapFileContainer.uploadCheck.sameHashConflicts.map(shc => this.mapFileHashMap[shc]).filter(mf => mf !== undefined).map(mf => mf.filename).join(", ");
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
        }
    }
</script>