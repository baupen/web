<template>
    <div id="edit">
        <h2>{{$t("map.plural")}}</h2>
        <p class="text-secondary">{{$t("edit_maps.help")}}</p>
        <atom-spinner v-if="isMapsLoading"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <template v-else>
            <map-view
                    :map-containers="mapContainers"
                    :map-file-containers="mapFileContainers"
                    @map-add="addMap"
                    @map-save="saveMap(arguments[0])"
                    @map-remove="removeMap(arguments[0])"
                    @map-file-dropped="mapFileDropped(arguments[0])"
                    @map-file-upload="mapFileUpload(arguments[0])"
                    @map-file-save="mapFileSave(arguments[0])"
                    @map-file-abort-upload="mapFileAbortUpload(arguments[0])"
            />
        </template>
        <button class="btn btn-primary" :disabled="isMapsLoading" v-if="pendingMapChanges > 0"
                @click="startProcessMapChanges">
            {{$t('edit_maps.actions.save_changes', {pendingChangesCount: pendingMapChanges}) }}
        </button>
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";
    import {AtomSpinner} from 'epic-spinners'
    import notifications from '../mixins/Notifications'
    import uuid from "uuid/v4"
    import MapView from "./components/MapView";
    import CryptoJS from 'crypto-js'

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        data: function () {
            return {
                constructionSiteId: null,
                mapContainers: [],
                mapFileContainers: [],
                craftsmanContainers: [],
                isMapsLoading: true,
                isCraftsmenLoading: true,
                locale: lang,
                actionQueue: []
            }
        },
        mixins: [notifications],
        components: {
            MapView,
            AtomSpinner
        },
        methods: {
            addMap: function () {
                this.mapContainers.push({
                    pendingChange: 'add',
                    map: {
                        id: uuid(),
                        name: this.$t("edit_maps.default_map_name"),
                        parentId: null,
                        fileId: null,
                        issueCount: 0
                    },
                    order: 0,
                    indentSize: 0
                })
            },
            saveMap: function (mapContainer) {
                if (mapContainer.pendingChange !== 'add') {
                    mapContainer.pendingChange = 'update';
                }
            },
            removeMap: function (mapContainer) {
                // fix parent ids of children
                this.mapContainers.filter(m => m.map.parentId === mapContainer.map.id).forEach(container => {
                    container.map.parentId = mapContainer.map.parentId;
                    this.saveMap(container);
                });

                if (mapContainer.pendingChange !== 'add') {
                    mapContainer.pendingChange = 'remove';
                } else {
                    //directly remove
                    this.mapContainers = this.mapContainers.filter(mc => mc !== mapContainer);
                }
            },
            mapFileDropped: function (file) {
                let mapFile = {
                    filename: file.name,
                    issueCount: 0,
                    createdAt: new Date().toISOString(),
                    mapId: null,
                    id: uuid()
                };

                const newMapFileContainer = {
                    mapFile: mapFile,
                    pendingChange: 'upload_check',
                    uploadFile: file,
                    uploadCheck: null,
                    uploadProgress: 0
                };
                this.mapFileContainers.push(newMapFileContainer);

                // perform upload check
                this.mapFileUploadCheck(newMapFileContainer);
            },
            mapFileUploadCheck: function (mapFileContainer) {
                let reader = new FileReader();

                const payload = {
                    constructionSiteId: this.constructionSiteId,
                    mapFile: {
                        filename: mapFileContainer.mapFile.filename
                    }
                };

                reader.onload = function () {
                    let fileResult = this.result;
                    let fileWordArray = CryptoJS.lib.WordArray.create(fileResult);
                    payload.mapFile.hash = CryptoJS.SHA256(fileWordArray).toString();

                    axios.post("/api/edit/map_file/check", payload).then((response) => {
                        const uploadCheck = response.data.uploadFileCheck;
                        mapFileContainer.uploadCheck = uploadCheck;

                        // fast forward if possible
                        if (uploadCheck.sameHashConflicts.length === 0 && uploadCheck.fileNameConflict === null) {
                            this.mapFileUpload(mapFileContainer);
                        } else {
                            mapFileContainer.pendingChange = "confirm_upload";
                        }
                    });
                };

                reader.readAsArrayBuffer(mapFileContainer.uploadFile);
            },
            mapFileUpload(mapFileContainer) {
                mapFileContainer.pendingChange = "finish_upload";

                let data = new FormData();
                data.append("file", mapFileContainer.uploadFile);
                data.append("message", JSON.stringify({
                    constructionSiteId: this.constructionSiteId,
                    mapFile: {
                        filename: mapFileContainer.uploadCheck.derivedFileName
                    }
                }));

                const config = {
                    onUploadProgress: function (progressEvent) {
                        mapFileContainer.uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    },
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                };

                axios.post('/api/edit/map_file', data, config)
                    .then(response => {
                        mapFileContainer.uploadProgress = 100;

                        const mapFile = response.data.mapFile;
                        mapFileContainer.mapFile.filename = mapFile.filename;
                        mapFileContainer.mapFile.createdAt = mapFile.createdAt;
                        mapFileContainer.mapFile.mapId = mapFile.mapId;
                        mapFileContainer.pendingChange = null;
                    });
            },
            mapFileSave: function (mapFileContainer) {
                mapFileContainer.pendingChange = 'update';
            },
            mapFileAbortUpload: function (mapFileContainer) {
                this.mapFileContainers = this.mapFileContainers.filter(mf => mf !== mapFileContainer);
            },
            startProcessMapChanges: function () {
                if (!this.isMapsLoading) {
                    this.isMapsLoading = true;
                    this.processMapChanges();
                } else {
                    console.log("stopped");
                }
            },
            processMapChanges: function () {
                if (this.pendingMapAdd.length > 0) {
                    const mapContainer = this.pendingMapAdd[0];
                    axios.post("/api/edit/map", {
                        constructionSiteId: this.constructionSiteId,
                        map: mapContainer.map
                    }).then((response) => {
                        const oldId = mapContainer.map.id;
                        mapContainer.map = response.data.map;
                        const newId = mapContainer.map.id;

                        // refreshIds
                        this.mapFileContainers.filter(mfc => mfc.mapFile.mapId === oldId).forEach(mfc => {
                            mfc.pendingChange = "update";
                            mfc.mapFile.mapId = newId
                        });

                        this.mapContainers.filter(mc => mc.map.parentId === oldId).forEach(mc => {
                            mc.pendingChange = "update";
                            mc.map.parentId = newId
                        });

                        // continue process
                        mapContainer.pendingChange = null;
                        this.processMapChanges();
                    });
                } else if (this.pendingMapUpdate.length) {
                    const mapContainer = this.pendingMapUpdate[0];
                    axios.put("/api/edit/map/" + mapContainer.map.id, {
                        constructionSiteId: this.constructionSiteId,
                        map: mapContainer.map
                    }).then((response) => {
                        // continue process
                        mapContainer.pendingChange = null;
                        this.processMapChanges();
                    });
                } else if (this.pendingMapRemove.length) {
                    const mapContainer = this.pendingMapRemove[0];
                    axios.delete("/api/edit/map/" + mapContainer.map.id, {
                        constructionSiteId: this.constructionSiteId,
                        map: mapContainer.map
                    }).then((response) => {
                        // continue process
                        mapContainer.pendingChange = null;
                        this.processMapChanges();
                    });
                } else if (this.pendingMapFileUpdate.length) {
                    const mapFileContainer = this.pendingMapFileUpdate[0];
                    axios.delete("/api/edit/map_file/" + mapFileContainer.mapFile.id, {
                        constructionSiteId: this.constructionSiteId,
                        mapFile: mapFileContainer.mapFile
                    }).then((response) => {
                        // continue process
                        mapFileContainer.pendingChange = null;
                        this.processMapChanges();
                    });
                } else {
                    this.isMapsLoading = false;
                }
            }
        },
        computed: {
            pendingMapAdd: function () {
                return this.mapContainers.filter(mfc => mfc.pendingChange === "add");
            },
            pendingMapUpdate: function () {
                return this.mapContainers.filter(mfc => mfc.pendingChange === "update");
            },
            pendingMapRemove: function () {
                return this.mapContainers.filter(mfc => mfc.pendingChange === "remove");
            },
            pendingMapFileUpdate: function () {
                return this.mapFileContainers.filter(mfc => mfc.pendingChange === "update");
            },
            pendingMapChanges: function () {
                return this.pendingMapAdd.length + this.pendingMapUpdate.length + this.pendingMapRemove.length + this.pendingMapFileUpdate.length;
            }
        },
        mounted() {
            // Add a response interceptor
            axios.interceptors.response.use(
                response => {
                    return response.data;
                },
                error => {
                    this.displayErrorFlash(this.$t("messages.danger.unrecoverable") + " (" + error.response.data.message + ")");
                    console.log("request failed");
                    console.log(error.response.data);
                    return Promise.reject(error);
                }
            );

            axios.get("/api/configuration").then((response) => {
                this.constructionSiteId = response.data.constructionSite.id;

                axios.post("/api/edit/map_files", {
                    constructionSiteId: this.constructionSiteId
                }).then((response) => {
                    response.data.mapFiles.forEach(mf => {
                        this.mapFileContainers.push({
                            mapFile: mf,
                            pendingChange: null
                        })
                    });

                    axios.post("/api/edit/maps", {
                        constructionSiteId: this.constructionSiteId
                    }).then((response) => {
                        response.data.maps.forEach(m => {
                            this.mapContainers.push({
                                map: m,
                                order: 0,
                                indentSize: 0,
                                pendingChange: null
                            })
                        });

                        this.isMapsLoading = false;
                    });
                });

                axios.post("/api/edit/craftsmen", {
                    constructionSiteId: this.constructionSiteId
                }).then((response) => {
                    response.data.craftsmen.forEach(c => {
                        this.craftsmanContainers.push({
                            craftsman: c,
                            pendingChange: null
                        })
                    });
                    this.isCraftsmenLoading = false;
                });
            });
        },
    }

</script>