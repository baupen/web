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
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab"
                       aria-controls="nav-home" aria-selected="true">Home</a>
                    <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab"
                       aria-controls="nav-profile" aria-selected="false">Profile</a>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                    <map-view
                            v-if="!mapFileViewActive"
                            :map-containers="mapContainers"
                            :map-file-containers="mapFileContainers"
                            @map-add="addMap"
                            @map-save="saveMap(arguments[0])"
                            @map-remove="removeMap(arguments[0])"
                    />
                </div>
                <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                    <map-file-view
                            v-if="mapFileViewActive"
                            :map-containers="mapContainers"
                            :map-file-containers="mapFileContainers"
                            @file-dropped="mapFileDropped(arguments[0])"
                            @start-upload="mapFileUpload(arguments[0])"
                            @abort-upload="mapFileAbortUpload(arguments[0])"
                            @save="mapFileSave(arguments[0])"
                    />
                </div>
            </div>
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
    import MapFileView from "./components/MapFileView";

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
                isLoading: false,
                locale: lang,
                actionQueue: []
            }
        },
        mixins: [notifications],
        components: {
            MapFileView,
            CraftsmanView,
            MapView,
            AtomSpinner
        },
        methods: {
            addMap: function () {
                const mapContainer = {
                    map: {
                        id: uuid(),
                        name: this.$t("edit_maps.default_map_name"),
                        parentId: null,
                        fileId: null,
                        issueCount: 0
                    },
                    order: 0,
                    indentSize: 0
                };

                this.mapContainers.push(mapContainer);

                axios.post("/api/edit/map", {
                    constructionSiteId: this.constructionSiteId,
                    map: mapContainer.map
                }).then((response) => {
                    mapContainer.map = response.data.map;
                });

            },
            saveMap: function (mapContainer) {
                axios.put("/api/edit/map/" + mapContainer.map.id, {
                    constructionSiteId: this.constructionSiteId,
                    map: mapContainer.map
                });
            },
            removeMap: function (mapContainer) {
                axios.delete("/api/edit/map/" + mapContainer.map.id, {
                    data: {
                        constructionSiteId: this.constructionSiteId
                    }
                }).then(() => {
                    this.mapContainers = this.mapContainers.filter(cc => cc !== mapContainer);
                });

                if (mapContainer.pendingChange !== 'add') {
                    mapContainer.pendingChange = 'remove';
                } else {
                    //directly remove
                    this.mapContainers = this.mapContainers.filter(mc => mc !== mapContainer);
                }
            },
            addCraftsman: function (afterAddAction) {
                const craftsmanContainer = {
                    craftsman: {
                        id: uuid(),
                        contactName: this.$t("edit_craftsmen.defaults.contact_name"),
                        email: this.$t("edit_craftsmen.defaults.email"),
                        company: this.$t("edit_craftsmen.defaults.company"),
                        trade: this.$t("edit_craftsmen.defaults.trade"),
                        issueCount: 0
                    }
                };

                this.craftsmanContainers.unshift(craftsmanContainer);

                axios.post("/api/edit/craftsman", {
                    constructionSiteId: this.constructionSiteId,
                    craftsman: craftsmanContainer.craftsman
                }).then((response) => {
                    craftsmanContainer.craftsman.id = response.data.craftsman.id;
                });

                afterAddAction(craftsmanContainer);
            },
            saveCraftsman: function (craftsmanContainer) {
                axios.put("/api/edit/craftsman/" + craftsmanContainer.craftsman.id, {
                    constructionSiteId: this.constructionSiteId,
                    craftsman: craftsmanContainer.craftsman
                });
            },
            removeCraftsman: function (craftsmanContainer) {
                axios.delete("/api/edit/craftsman/" + craftsmanContainer.craftsman.id, {
                    data: {
                        constructionSiteId: this.constructionSiteId
                    }
                }).then(() => {
                    // continue process
                    this.craftsmanContainers = this.craftsmanContainers.filter(cc => cc !== craftsmanContainer);
                });
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
                            context.mapFileUpload(mapFileContainer);
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

                        // replace id
                        const oldId = mapFileContainer.mapFile.id;
                        const newId = mapFile.id;
                        this.mapContainers.filter(c => c.map.fileId === oldId).forEach(c => c.map.fileId = newId);

                        mapFileContainer.mapFile.id = newId;
                        mapFileContainer.mapFile.filename = mapFile.filename;
                        mapFileContainer.mapFile.createdAt = mapFile.createdAt;
                        mapFileContainer.mapFile.mapId = mapFile.mapId;
                        mapFileContainer.pendingChange = null;
                    });
            },
            mapFileSave: function (mapFileContainer) {
                axios.put("/api/edit/map_file/" + mapFileContainer.mapFile.id, {
                    constructionSiteId: this.constructionSiteId,
                    mapFile: mapFileContainer.mapFile
                });
            },
            mapFileAbortUpload: function (mapFileContainer) {
                this.mapFileContainers = this.mapFileContainers.filter(mf => mf !== mapFileContainer);
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
            },
            canChangeTab: function () {
                return this.pendingMapAdd.length + this.pendingMapUpdate.length + this.pendingMapRemove.length + this.pendingMapFileUpdate.length === 0;
            }
        },
        mounted() {
            // Add a request interceptor
            axios.interceptors.request.use(
                config => {
                    this.isLoading = true;
                    return config;
                },
                error => {
                    return Promise.reject(error);
                }
            );

            // Add a response interceptor
            axios.interceptors.response.use(
                response => {
                    this.isLoading = false;
                    return response.data;
                },
                error => {
                    this.isLoading = false;
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
                            mapFile: mf
                        })
                    });

                    axios.post("/api/edit/maps", {
                        constructionSiteId: this.constructionSiteId
                    }).then((response) => {
                        let mapContainers = [];
                        response.data.maps.forEach(m => {
                            mapContainers.push({
                                map: m,
                                order: 0,
                                indentSize: 0
                            })
                        });

                        this.mapContainers = mapContainers;

                        this.isMapsLoading = false;
                    });
                });

                axios.post("/api/edit/craftsmen", {
                    constructionSiteId: this.constructionSiteId
                }).then((response) => {
                    response.data.craftsmen.forEach(c => {
                        this.craftsmanContainers.push({
                            craftsman: c
                        })
                    });
                    this.isCraftsmenLoading = false;
                });
            });
        },
    }

</script>