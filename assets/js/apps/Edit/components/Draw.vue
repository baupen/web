<template>
    <div>
        <template v-if="view === 'loading'">
            <atom-spinner
                    :animation-duration="1000"
                    :size="60"
                    :color="'#ff1d5e'"
            />
        </template>
        <template v-else-if="view === 'sector-frame'">
            <draw-sector-frame :map-file="mapFile" :frame="frame" @save-frame="saveFrame(arguments[0])" />
        </template>
        <template v-else>
            <draw-sectors :map-file="mapFile" :sectors="sectors"
                          @draw-outline="view = 'sector-frame'"
                          @close="$emit('close')"
                          @save="saveSectors(arguments[0])"
            />
        </template>
    </div>
</template>

<style>

</style>

<script>
    import moment from "moment";
    import axios from "axios"
    import AtomSpinner from "epic-spinners/src/components/lib/AtomSpinner";
    import DrawSectorFrame from "./DrawSectorFrame";
    import DrawSectors from "./DrawSectors";

    const lang = document.documentElement.lang.substr(0, 2);
    moment.locale(lang);

    export default {
        props: {
            mapFile: {
                type: Object,
                required: true
            },
            constructionSiteId: {
                type: String,
                required: true
            }
        },
        data: function () {
            return {
                view: "loading",
                frame: null,
                sectors: []
            }
        },
        components: {
            DrawSectors,
            DrawSectorFrame,
            AtomSpinner
        },
        computed: {
            baseUrl: function () {
                return "/api/edit/map_file/" + this.mapFile.id;
            }
        },
        methods: {
            saveFrame: function (frame) {
                axios.post(this.baseUrl  + "/sector_frame/save", {
                    constructionSiteId: this.constructionSiteId,
                    sectorFrame: frame
                }).then((response) => {
                    this.view = 'sectors'
                });
            },
            saveSectors: function (sectors) {
                axios.post(this.baseUrl  + "/map_sectors/save", {
                    constructionSiteId: this.constructionSiteId,
                    mapSectors: sectors
                }).then((response) => {
                    this.view = 'sectors'
                });
            }
        },
        mounted() {
            axios.post(this.baseUrl  + "/sector_frame", {
                constructionSiteId: this.constructionSiteId
            }).then((response) => {
                this.frame = response.data.sectorFrame;

                axios.post(this.baseUrl  + "/map_sectors", {
                    constructionSiteId: this.constructionSiteId
                }).then((response) => {
                    this.sectors = response.data.mapSectors;

                    this.view = this.frame !== null ? "sectors" : "sector-frame";
                });
            });
        }
    }

</script>
