<template>
    <div id="edit">
        <h2>{{$t("map.plural")}}</h2>
        <p class="text-secondary">{{$t("edit_maps.help")}}</p>
        <atom-spinner v-if="isMapsLoading"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <map-view v-if="!isMapsLoading"
                  :map-containers="mapContainers"
                  :map-file-containers="mapFileContainers"
                  @add-map="addMap"
                  @save-map="saveMap(arguments[0])"
                  @remove-map="removeMap(arguments[0])"
                  @map-file-dropped="mapFileDropped(arguments[0])"
                  @upload-map-file="uploadMapFile(arguments[0])"
                  @save-map-file="saveMapFile(arguments[0])"
        />
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
                locale: lang
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
                        order: 0,
                        indentSize: 0
                    }
                })
            },
            saveMap: function (mapContainer) {
                if (mapContainer.pendingChange !== 'add') {
                    mapContainer.pendingChange = 'update';
                }
            },
            removeMap: function (mapContainer) {
                mapContainer.pendingChange = 'remove';

                // fix parent ids of children
                this.mapContainers.filter(m => m.map.parentId === mapContainer.map.id).forEach(container => {
                    container.map.parentId = mapContainer.map.parentId;
                    this.saveMap(container);
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
                    pendingChange: 'upload',
                    uploadFile: file,
                    uploadCheck: null,
                    uploadProgress: 0
                };
                this.mapFileContainers.push(newMapFileContainer);

                // perform upload check
                this.performUploadMapFileCheck(newMapFileContainer);
            },
            performUploadMapFileCheck: function (mapFileContainer) {
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
                            this.uploadMapFile(mapFileContainer);
                        }
                    });
                };

                reader.readAsArrayBuffer(mapFileContainer.uploadFile);
            },
            uploadMapFile(mapFileContainer) {
                let data = new FormData();
                data.append("file", mapFileContainer.uploadFile);
                data.append("constructionSiteId", this.constructionSiteId);
                data.append("mapFile", {
                    filename: mapFileContainer.uploadCheck.derivedFileName
                });

                const config = {
                    onUploadProgress: function (progressEvent) {
                        mapFileContainer.uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
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
            saveMapFile: function (mapFileContainer) {
                mapFileContainer.pendingChange = 'update';
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