<template>
    <div id="edit">
        <h2>{{$t("edit_construction_site.title")}}</h2>
        <p class="text-secondary">{{$t("edit_construction_site.help")}}</p>
        <atom-spinner v-if="isConstructionSiteLoading"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />

        <construction-site-edit
                v-else
                :construction-site="constructionSite"
                @save="saveConstructionSite"
                @upload-image="saveConstructionSiteImage(arguments[0])"
        />

        <div class="vertical-spacer-big"></div>
        <h2>{{$t("map._plural")}}</h2>
        <p class="text-secondary">{{$t("edit_maps.help")}}</p>
        <atom-spinner v-if="isMapsLoading"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <map-edit
                :map-containers="mapContainers"
                :map-file-containers="mapFileContainers"
                @maps-reorder="reorderMaps"
                @map-add="addMap"
                @map-save="saveMap(arguments[0])"
                @map-remove="removeMap(arguments[0])"
                @map-draw="openMapModal(arguments[0])"
                @map-file-dropped="mapFileDropped(arguments[0])"
                @map-file-start-upload="mapFileUpload(arguments[0])"
                @map-file-abort-upload="mapFileAbortUpload(arguments[0])"
                @map-file-save="mapFileSave(arguments[0])"
        />
        <div class="vertical-spacer-big"></div>
        <h2>{{$t("craftsman._plural")}}</h2>
        <p class="text-secondary">{{$t("edit_craftsmen.help")}}</p>
        <atom-spinner v-if="isCraftsmenLoading"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <craftsman-view v-else
                        :craftsman-containers="craftsmanContainers"
                        @add="craftsmanContainers.push(arguments[0])"
                        @save="saveCraftsman(arguments[0])"
                        @remove="removeCraftsman(arguments[0])"
        />


        <b-modal ref="my-modal" hide-footer hide-header size="full" :scrollable="true">
            <draw-sectors v-if="selectedMapFile !== null"
                          :construction-site-id="constructionSiteId"
                          :map-file="selectedMapFile"
                          @close="hideMapModal"
            />
        </b-modal>
    </div>
</template>

<style>
    .modal-full {
        max-width: unset !important;
        margin: 2em;
    }
</style>

<script>
    import axios from "axios"
    import moment from "moment";
    import {AtomSpinner} from 'epic-spinners'
    import notifications from '../mixins/Notifications'
    import uuid from "uuid/v4"
    import CryptoJS from 'crypto-js'
    import CraftsmanView from "./components/CraftsmanView";
    import MapEdit from "./components/MapEdit";
    import ConstructionSiteEdit from "./components/ConstructionSiteEdit";
    import BModal from "bootstrap-vue/src/components/modal/modal";
    import DrawSectors from "./components/Draw";

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        data: function () {
            return {
                constructionSiteId: null,
                constructionSite: null,
                mapContainers: [],
                mapFileContainers: [],
                craftsmanContainers: [],
                isConstructionSiteLoading: true,
                isMapsLoading: true,
                isCraftsmenLoading: true,
                isLoading: false,
                locale: lang,
                actionQueue: [],
                selectedMapFile: null
            }
        },
        mixins: [notifications],
        components: {
            DrawSectors,
            ConstructionSiteEdit,
            MapEdit,
            CraftsmanView,
            AtomSpinner,
            BModal
        },
        methods: {
            saveConstructionSite: function () {
                axios.put("/api/edit/construction_site/save", {
                    constructionSiteId: this.constructionSiteId,
                    constructionSite: {
                        streetAddress: this.constructionSite.streetAddress,
                        postalCode: this.constructionSite.postalCode,
                        locality: this.constructionSite.locality
                    }
                });
            },
            saveConstructionSiteImage: function (uploadFile) {
                let data = new FormData();
                data.append("file", uploadFile);
                data.append("message", JSON.stringify({
                    constructionSiteId: this.constructionSiteId
                }));

                const config = {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                };

                axios.post('/api/edit/construction_site/image', data, config)
                    .then(_ => {
                        this.loadConstructionSite();
                    });
            },
            openMapModal: function (mapContainer) {
                this.selectedMapFile = this.mapFileContainers.map(mfc => mfc.mapFile).filter(m => m.id === mapContainer.map.fileId)[0];
                this.$refs['my-modal'].show()
            },
            hideMapModal: function () {
                this.$refs['my-modal'].hide()
            },
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

                this.mapContainers.unshift(mapContainer);

                axios.post("/api/edit/map", {
                    constructionSiteId: this.constructionSiteId,
                    map: mapContainer.map
                }).then((response) => {
                    mapContainer.map = response.data.map;
                });
            },
            reorderMaps: function () {
                this.setMapOrderProperties(this.mapContainers, null, 0, 0);
                this.mapContainers = this.mapContainers.sort((m1, m2) => m1.order - m2.order);
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
            },
            saveCraftsman: function (craftsmanContainer) {
                if (craftsmanContainer.new) {
                    axios.post("/api/edit/craftsman", {
                        constructionSiteId: this.constructionSiteId,
                        craftsman: craftsmanContainer.craftsman
                    }).then((response) => {
                        craftsmanContainer.craftsman.id = response.data.craftsman.id;
                        craftsmanContainer.new = false;
                    });
                } else {
                    axios.put("/api/edit/craftsman/" + craftsmanContainer.craftsman.id, {
                        constructionSiteId: this.constructionSiteId,
                        craftsman: craftsmanContainer.craftsman
                    });
                }
            },
            removeCraftsman: function (craftsmanContainer) {
                if (craftsmanContainer.new) {
                    this.craftsmanContainers = this.craftsmanContainers.filter(cc => cc !== craftsmanContainer);
                    return;
                }

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

                this.mapFileContainers.unshift(newMapFileContainer);

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
            },
            setMapOrderProperties: function (mapContainers, parentId, order, indent) {
                const children = mapContainers.filter(m => m.map.parentId === parentId);
                children.sort((c1, c2) => c1.map.name.localeCompare(c2.map.name));
                let maxOrder = order;

                if (children.filter(c => c.indentSize !== indent)) {
                    children.forEach(c => {
                        c.order = maxOrder;
                        c.indentSize = indent;
                        maxOrder = this.setMapOrderProperties(mapContainers, c.map.id, maxOrder + 1, indent + 1);
                    });
                } else {
                    children.forEach(c => {
                        this.setMapOrderProperties(mapContainers, c.map.id, maxOrder + 1, indent + 1);
                    });
                }


                return maxOrder;
            },
            loadConstructionSite: function () {
                axios.post("/api/edit/construction_site", {
                    constructionSiteId: this.constructionSiteId
                }).then((response) => {
                    this.constructionSite = response.data.constructionSite;
                    this.isConstructionSiteLoading = false;
                });
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

                this.loadConstructionSite();

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
                        response.data.maps.forEach(m => {
                            this.mapContainers.push({
                                map: m,
                                order: 0,
                                indentSize: -1
                            })
                        });

                        this.reorderMaps();

                        this.isMapsLoading = false;
                    });
                });

                axios.post("/api/edit/craftsmen", {
                    constructionSiteId: this.constructionSiteId
                }).then((response) => {
                    response.data.craftsmen.forEach(c => {
                        this.craftsmanContainers.push({
                            new: false,
                            craftsman: c
                        })
                    });
                    this.isCraftsmenLoading = false;
                });
            });
        },
    }

</script>
