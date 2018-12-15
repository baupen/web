<template>
    <div id="edit">
        <h2>{{$t("map.plural")}}</h2>
        <p class="text-secondary">{{$t("edit_maps.help")}}</p>
        <table v-if="maps.length > 0" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th>Name</th>
                <th>Adresse</th>
                <th>erstellt am</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <MapTreeLevel v-for="rootMap in maps.filter(m => m.parentId === null)" :map="rootMap" :maps="maps"
                          :indent-size="0"/>
            </tbody>
        </table>
        <atom-spinner v-if="isMapsLoading"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
    </div>
</template>

<style>
    .grid-item {
        margin-bottom: 10px;
    }
</style>

<script>
    import axios from "axios"
    import moment from "moment";
    import bAlert from 'bootstrap-vue/es/components/alert/alert'
    import {AtomSpinner} from 'epic-spinners'
    import notifications from '../mixins/Notifications'
    import ConstructionSite from './components/ConstructionSite'
    import MapTreeLevel from "./components/MapTreeLevel";

    const lang = document.documentElement.lang.substr(0, 2);

    moment.locale('de');

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
                locale: lang
            }
        },
        computed: {
            managingConstructionSites: function () {
                return this.constructionSites.filter(c => c.isConstructionManagerOf);
            }
        },
        mixins: [notifications],
        components: {
            MapTreeLevel,
            bAlert,
            AtomSpinner
        },
        methods: {
            formatDateTime: function (dateTime) {
                return moment(dateTime).locale(this.locale).fromNow();
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