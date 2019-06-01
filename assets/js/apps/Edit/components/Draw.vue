<template>
    <div>
        <template v-if="view === 'loading'">
            <atom-spinner
                    :animation-duration="1000"
                    :size="60"
                    :color="'#ff1d5e'"
            />
        </template>
        <template v-else-if="view === 'frame'">
            <draw-sector-frame :map-file="mapFile" :frame="frame" />
        </template>
        <template v-else>

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
            DrawSectorFrame,
            AtomSpinner
        },
        computed: {
            baseUrl: function () {
                return "/api/edit/map_file/" + this.mapFile.id;
            }
        },
        methods: {

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

                    this.view = this.frame !== null ? "sectors" : "frame";
                });
            });
        }
    }

</script>
