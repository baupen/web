<template>
    <div id="edit">
        <h2>{{$t("map.plural")}}</h2>
        <p class="text-secondary">{{$t("edit_maps.help")}}</p>
        <p>
            <button class="btn btn-primary" @click="addMap">
                {{$t("edit_maps.actions.add_map")}}
            </button>
            <button class="btn btn-outline-primary" @click="mapFileViewActive = true" v-if="!mapFileViewActive">
                {{$t("edit_maps.actions.add_map_files")}}
            </button>
        </p>

        <MapFileView v-if="mapFileViewActive" :map-files="mapFiles" :ordered-maps="orderedMaps"/>
        <table v-if="maps.length > 0" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>{{$t("map.name")}}</th>
                <th>{{$t("map.parent")}}</th>
                <th>{{$t("map_file.name")}}</th>
                <th>{{$t("set_automatically")}}</th>
                <th class="minimal-width">{{$t('issue_count')}}</th>
                <th class="minimal-width"></th>
            </tr>
            </thead>
            <tbody>
            <map-table-row v-for="orderedMap in orderedMaps" :key="orderedMap.id"
                           :map="orderedMap"
                           :ordered-maps="orderedMaps"
                           :map-files="mapFiles"
                           :indent-size="orderedMap.indentSize"
                           @removed="markMapForRemoval(orderedMap)"/>
            </tbody>
        </table>
        <atom-spinner v-if="isMapsLoading"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";
    import bAlert from 'bootstrap-vue/es/components/alert/alert'
    import {AtomSpinner} from 'epic-spinners'
    import notifications from '../mixins/Notifications'
    import MapTableRow from "./components/MapTableRow";
    import MapFileTableRow from "./components/MapFileTableRow";
    import uuid4 from "uuid/v4"
    import MapFileView from "./components/MapFileView";

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        data: function () {
            return {
                constructionSiteId: null,
                maps: [],
                mapFiles: [],
                craftsmen: [],
                isMapsLoading: true,
                isMapFilesLoading: true,
                isCraftsmenLoading: true,
                locale: lang,
                mapsToRemove: [],
                mapFileViewActive: false
            }
        },
        computed: {
            managingConstructionSites: function () {
                return this.constructionSites.filter(c => c.isConstructionManagerOf);
            },
            orderedMaps: function () {
                this.setOrderProperties(this.displayMaps, null, 0, 0);
                return this.displayMaps.sort((m1, m2) => m1.order - m2.order);
            },
            displayMaps: function () {
                return this.maps.filter(m => this.mapsToRemove.indexOf(m) === -1);
            }
        },
        mixins: [notifications],
        components: {
            MapFileView,
            MapTableRow,
            MapFileTableRow,
            bAlert,
            AtomSpinner
        },
        methods: {
            setOrderProperties: function (maps, parentId, order, indent) {
                const children = maps.filter(m => m.parentId === parentId);
                children.sort((c1, c2) => c1.name.localeCompare(c2.name));
                let maxOrder = order;
                children.forEach(c => {
                    c.order = maxOrder;
                    c.indentSize = indent;
                    maxOrder = this.setOrderProperties(maps, c.id, maxOrder + 1, indent + 1);
                });

                return maxOrder;
            },
            markMapForRemoval: function (map) {
                this.mapsToRemove.push(map);
                // fix parent ids of children
                this.maps.filter(m => m.parentId === map.id).forEach(m => m.parentId = map.parentId);
            },
            addMap: function () {
                this.maps.push({
                    id: uuid4(),
                    name: this.$t("edit_maps.default_map_name"),
                    parentId: null,
                    fileId: null,
                    order: 0,
                    indentSize: 0
                })
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
                    this.mapFiles = response.data.mapFiles;
                    this.isMapFilesLoading = false;

                    axios.post("/api/edit/maps", {
                        constructionSiteId: this.constructionSiteId
                    }).then((response) => {
                        let maps = response.data.maps;
                        maps.forEach(m => {
                            m.order = 0;
                            m.indentSize = 0;
                        });
                        this.maps = maps;
                        this.isMapsLoading = false;
                    });
                });


                /*

                axios.post("/api/edit/craftsmen", {
                    constructionSiteId: this.constructionSiteId
                }).then((response) => {
                    this.craftsmen = response.data.craftsmen;
                    this.isCraftsmenLoading = false;
                });
                */
            });
        },
    }

</script>