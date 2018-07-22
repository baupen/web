<template>
    <div>

        <vue-headful :title="title" :description="description"/>
        <lightbox :open="lightbox.enabled" :imageSrc="lightbox.imageFull" @close="lightbox.enabled = false"/>

        <section class="public-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <h1>{{title}}</h1>
                    <p v-if="description !== ''" class="text-secondary">
                        {{description}}
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="float-right">
                        <div class="btn-group-vertical">
                            <a v-if="craftsman !== null" :href="craftsman.reportUrl" target="_blank"
                               class="btn btn-outline-primary btn-lg">{{$t("actions.print")}}</a> <br/>
                            <a v-if="craftsman !== null" :href="craftsman.readOnlyViewUrl" target="_blank"
                               class="btn btn-outline-primary btn">{{$t("actions.read_only_view")}}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="public-content">
                <atom-spinner
                        v-if="isLoading"
                        :animation-duration="1000"
                        :size="60"
                        :color="'#ff1d5e'"
                />
                <div v-else-if="openIssuesLength === 0">
                    <h2 class="display-1">{{ $t("dialog.thanks") }}</h2>
                </div>
                <div v-else>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>{{ $t("map.name")}}</th>
                            <th>{{ $t("map.open_issues_count")}}</th>
                            <th>{{ $t("map.next_response_limit")}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <map-row v-for="map in maps" v-bind:key="map.id" :map="map" class="clickable"
                                 @clicked-row="scrollTo('map-' + map.id)" :issues-with-response="issuesWithResponse"/>
                        </tbody>
                    </table>
                    <div class="map-content">
                        <div class="container">
                            <MapDetails v-for="map in maps" v-bind:key="map.id" :ref="'map-' + map.id"
                                        :map="map" :issues-with-response="issuesWithResponse"
                                        @open-lightbox="openLightbox(arguments[0])"
                                        @issue-send-response="sendResponse(arguments[0])"
                                        @issue-remove-response="removeResponse(arguments[0])"
                            />
                        </div>
                    </div>
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
                craftsman: null,
                isLoading: true,
                issuesWithResponse: [],
                identifier: null,
                maps: [],
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
            },
            sendResponse: function (issue) {
                axios.post("/external/api/share/c/" + this.identifier + "/issue/respond", {issueId: issue.id}).then((response) => {
                    if (response.data.successfulIds.length > 0) {
                        this.issuesWithResponse.push(issue);
                    }
                });
            },
            removeResponse: function (issue) {
                axios.post("/external/api/share/c/" + this.identifier + "/issue/remove_response", {issueId: issue.id}).then((response) => {
                    if (response.data.successfulIds.length > 0) {
                        this.issuesWithResponse = this.issuesWithResponse.filter(i => i !== issue);
                    }
                });
            },
        },
        computed: {
            openIssuesLength: function () {
                return this.maps.reduce((total, map) => total + map.issues.filter(i => this.issuesWithResponse.indexOf(i) === -1).length, 0);
            },
            title: function () {
                if (this.isLoading) {
                    return this.$t("loading_open_issues");
                } else {
                    return this.$tc("open_issues_header", this.openIssuesLength, {count: this.openIssuesLength});
                }
            },
            description: function () {
                if (this.craftsman !== null) {
                    return this.$t("of_craftsman", {craftsman: this.craftsman.name});
                }
                return "";

            },
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

            axios.get("/external/api/share/c/" + this.identifier + "/read").then((response) => {
                this.craftsman = response.data.craftsman;
                axios.get("/external/api/share/c/" + this.identifier + "/maps/list").then((response) => {
                    this.maps = response.data.maps;
                    this.isLoading = false;
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