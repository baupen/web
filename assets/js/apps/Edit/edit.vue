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
        <div class="vertical-spacer-big"></div>
        <h2>{{$t("craftsman.plural")}}</h2>
        <p class="text-secondary">{{$t("edit_craftsmen.help")}}</p>
        <atom-spinner v-if="isCraftsmenLoading"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <template v-else>
            <craftsman-view
                    :craftsman-containers="craftsmanContainers"
                    @craftsman-add="addCraftsman(arguments[0])"
                    @craftsman-save="saveCraftsman(arguments[0])"
                    @craftsman-remove="removeCraftsman(arguments[0])"
            />
        </template>
        <button class="btn btn-primary" :disabled="isMapsLoading" v-if="pendingCraftsmanChanges > 0"
                @click="startProcessCraftsmanChanges">
            {{$t('edit_craftsmen.actions.save_changes', {pendingChangesCount: pendingCraftsmanChanges}) }}
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
    import CraftsmanView from "./components/CraftsmanView";

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
            CraftsmanView,
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
                        isAutomaticEditEnabled: false,
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
            addCraftsman: function (afterAddAction) {
                const newCraftsmanContainer = {
                    pendingChange: 'add',
                    craftsman: {
                        id: uuid(),
                        contactName: this.$t("edit_craftsmen.defaults.contact_name"),
                        email: this.$t("edit_craftsmen.defaults.email"),
                        company: this.$t("edit_craftsmen.defaults.company"),
                        trade: this.$t("edit_craftsmen.defaults.trade"),
                        issueCount: 0
                    }
                };

                this.craftsmanContainers.push(newCraftsmanContainer);

                this.$nextTick(() => {
                    afterAddAction(newCraftsmanContainer);
                });
            },
            saveCraftsman: function (craftsmanContainer) {
                if (craftsmanContainer.pendingChange !== 'add') {
                    craftsmanContainer.pendingChange = 'update';
                }
            },
            removeCraftsman: function (craftsmanContainer) {
                if (craftsmanContainer.pendingChange !== 'add') {
                    craftsmanContainer.pendingChange = 'remove';
                } else {
                    //directly remove
                    this.craftsmanContainers = this.craftsmanContainers.filter(mc => mc !== craftsmanContainer);
                }
            },
            mapFileDropped: function (file) {
                let mapFile = {
                    filename: file.name,
                    issueCount: 0,
                    createdAt: new Date().toISOString(),
                    mapId: null,
                    isAutomaticEditEnabled: true,
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

                const context = this;
                reader.onload = function () {
                    let fileResult = this.result;
                    let fileWordArray = CryptoJS.lib.WordArray.create(fileResult);
                    payload.mapFile.hash = CryptoJS.SHA256(fileWordArray).toString();

                    axios.post("/api/edit/map_file/check", payload).then((response) => {
                        const uploadCheck = response.data.uploadFileCheck;
                        mapFileContainer.uploadCheck = uploadCheck;

                        // fast forward if possible
                        if (uploadCheck.sameHashConflicts.length === 0 && uploadCheck.fileNameConflict === null) {
                            console.log("calling mapFileUpda");
                            context.mapFileUpload(mapFileContainer);
                            console.log("called mapFileUpda");
                        } else {
                            mapFileContainer.pendingChange = "confirm_upload";
                        }
                    });
                };

                reader.readAsArrayBuffer(mapFileContainer.uploadFile);
            },
            mapFileUpload(mapFileContainer) {
                console.log("arrived");
                mapFileContainer.pendingChange = "finish_upload";
                console.log("arrived 2");

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
                        data: {
                            constructionSiteId: this.constructionSiteId
                        }
                    }).then((response) => {
                        // continue process
                        this.mapContainers = this.mapContainers.filter(cc => cc !== mapContainer);
                        this.processMapChanges();
                    });
                } else if (this.pendingMapFileUpdate.length) {
                    const mapFileContainer = this.pendingMapFileUpdate[0];
                    axios.put("/api/edit/map_file/" + mapFileContainer.mapFile.id, {
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
            },
            startProcessCraftsmanChanges: function () {
                if (!this.isCraftsmenLoading) {
                    this.isCraftsmenLoading = true;
                    this.processCraftsmanChanges();
                }
            },
            processCraftsmanChanges: function () {
                if (this.pendingCraftsmanAdd.length > 0) {
                    const craftsmanContainer = this.pendingCraftsmanAdd[0];
                    axios.post("/api/edit/craftsman", {
                        constructionSiteId: this.constructionSiteId,
                        craftsman: craftsmanContainer.craftsman
                    }).then((response) => {
                        craftsmanContainer.craftsman.id = response.data.craftsman.id;

                        // continue process
                        craftsmanContainer.pendingChange = null;
                        this.processCraftsmanChanges();
                    });
                } else if (this.pendingCraftsmanUpdate.length) {
                    const craftsmanContainer = this.pendingCraftsmanUpdate[0];
                    axios.put("/api/edit/craftsman/" + craftsmanContainer.craftsman.id, {
                        constructionSiteId: this.constructionSiteId,
                        craftsman: craftsmanContainer.craftsman
                    }).then((response) => {
                        // continue process
                        craftsmanContainer.pendingChange = null;
                        this.processCraftsmanChanges();
                    });
                } else if (this.pendingCraftsmanRemove.length) {
                    const craftsmanContainer = this.pendingCraftsmanRemove[0];
                    axios.delete("/api/edit/craftsman/" + craftsmanContainer.craftsman.id, {
                        data: {
                            constructionSiteId: this.constructionSiteId
                        }
                    }).then((response) => {
                        // continue process
                        this.craftsmanContainers = this.craftsmanContainers.filter(cc => cc !== craftsmanContainer);
                        this.processCraftsmanChanges();
                    });
                } else {
                    this.isCraftsmenLoading = false;
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
            pendingCraftsmanAdd: function () {
                return this.craftsmanContainers.filter(mfc => mfc.pendingChange === "add");
            },
            pendingCraftsmanUpdate: function () {
                return this.craftsmanContainers.filter(mfc => mfc.pendingChange === "update");
            },
            pendingCraftsmanRemove: function () {
                return this.craftsmanContainers.filter(mfc => mfc.pendingChange === "remove");
            },
            pendingMapFileUpdate: function () {
                return this.mapFileContainers.filter(mfc => mfc.pendingChange === "update");
            },
            pendingMapChanges: function () {
                return this.pendingMapAdd.length + this.pendingMapUpdate.length + this.pendingMapRemove.length + this.pendingMapFileUpdate.length;
            },
            pendingCraftsmanChanges: function () {
                return this.pendingCraftsmanAdd.length + this.pendingCraftsmanUpdate.length + this.pendingCraftsmanRemove.length;
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