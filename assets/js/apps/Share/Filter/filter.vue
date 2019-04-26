<template>
    <div>
        <lightbox :open="lightbox.enabled" :imageSrc="lightbox.imageFull" @close="lightbox.enabled = false"/>
        <vue-headful :title="$t('issues_overview')" :description="description"/>

        <atom-spinner
                v-if="readLoading"
                :animation-duration="1000"
                :size="60"
                :color="'#ff1d5e'"
        />
        <section v-else class="public-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <h1>{{ constructionSite.name }}</h1>
                    <p class="text-secondary">{{ $t('issues_overview') }}</p>
                </div>
                <div class="col-md-6">
                    <a target="_blank" :href="filter.reportUrl" class="btn btn-outline-primary btn-lg float-right">{{$t("actions.print")}}</a>
                </div>
            </div>

            <div class="public-content">
                <atom-spinner
                        v-if="mapsLoading"
                        :animation-duration="1000"
                        :size="60"
                        :color="'#ff1d5e'"
                />
                <div v-else>
                    <template v-if="maps.length === 0">
                        <span class="alert alert-info">
                            {{$t("no_issues")}}
                        </span>
                    </template>
                    <template v-else>
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>{{ $t("map.name")}}</th>
                                <th>{{ $t("map.open_issues_count")}}</th>
                                <th>{{ $t("map.reviewed_issues_count")}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <map-row v-for="map in maps" v-bind:key="map.id" :map="map" class="clickable"
                                     @clicked-row="scrollTo('map-' + map.id)"/>
                            </tbody>
                        </table>
                        <div class="map-content">
                            <div class="container">
                                <MapDetails v-for="map in maps" v-bind:key="map.id" :ref="'map-' + map.id"
                                            :map="map"
                                            @open-lightbox="openLightbox(arguments[0])"
                                />
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import axios from "axios"
    import Lightbox from '../../components/Lightbox'
    import notifications from '../../mixins/Notifications'
    import MapDetails from './components/MapDetails'
    import MapRow from './components/MapRow'
    import {AtomSpinner} from 'epic-spinners'


    export default {
        data: function () {
            return {
                identifier: null,

                constructionSite: null,
                filter: null,
                readLoading: true,

                maps: [],
                mapsLoading: true,

                lightbox: {
                    enabled: false,
                    imageFull: null
                }
            }
        },
        components: {
            Lightbox,
            MapDetails,
            MapRow,
            AtomSpinner
        },
        mixins: [notifications],
        methods: {
            scrollTo: function (ref) {
                const messageDisplay = this.$refs[ref][0];
                console.log(messageDisplay.$el);
                messageDisplay.$el.scrollIntoView();
            },
            openLightbox: function (url) {
                this.lightbox.enabled = true;
                this.lightbox.imageFull = url;
            }
        },
        computed: {
            openIssuesLength: function () {
                return this.maps.reduce((total, map) => total + map.issues.filter(i => this.issuesWithResponse.indexOf(i) === -1).length, 0);
            },
            description: function () {
                if (this.constructionSite !== null) {
                    return this.constructionSite.name;
                }
                return '';
            }
        },
        mounted() {
            // Add a response interceptor
            axios.interceptors.response.use(
                response => {
                    return response.data;
                },
                error => {
                    this.displayErrorFlash(this.$t("error") + " (" + error.response.data.message + ")");
                    return Promise.reject(error);
                }
            );
            let url = window.location.href.split("/");
            this.identifier = url[6];

            axios.get("/external/api/share/f/" + this.identifier + "/read").then((response) => {
                this.constructionSite = response.data.constructionSite;
                this.filter = response.data.filter;
                this.readLoading = false;
                axios.get("/external/api/share/f/" + this.identifier + "/maps/list").then((response) => {
                    this.maps = response.data.maps;
                    this.mapsLoading = false;
                });
            });
        },
    }

</script>

<style>
    .clickable {
        cursor: pointer;
    }

    .map-content {
        padding-top: 4rem;
        padding-bottom: 2rem;
        background-color: rgba(0, 0, 0, 0.05);
    }

    .container {
        max-width: 1400px;
    }

    .map-wrapper {
        margin-top: 5rem;
    }

    .numbered-card {
        position: relative;
    }

    .card-number {
        position: absolute;
        top: 0;
        left: 0;
        padding: 0.5rem;
        background-color: rgba(255, 255, 255, 0.7);
    }

    @media (max-width: 680px) {
        .map-wrapper {
            margin-top: 2rem;
        }

        .map-content {
            padding-top: 2rem;
        }
    }

</style>