<template>
    <div id="dashboard">
        <div class="row">
            <div class="col">
                <h2>{{$t("register.name")}}</h2>
                <atom-spinner v-if="isOverviewLoading"
                              :animation-duration="1000"
                              :size="60"
                              :color="'#ff1d5e'"
                />
                <b-alert v-if="!isOverviewLoading" :show="overview.newIssuesCount > 0" variant="warning">
                    <h4>{{ $tc("dialog.new_issues_in_foyer", overview.newIssuesCount, {count:
                        overview.newIssuesCount})}}</h4>
                    <p><a href="/foyer">{{$t("dialog.add_to_register")}}</a></p>
                </b-alert>
                <overview v-if="!isOverviewLoading" :overview="overview" />

                <div class="vertical-spacer-big" ></div>
                <h2>{{$t("notes.name")}}</h2>
                <notes v-if="constructionSiteId !== null" :construction-site-id="constructionSiteId" />
            </div>
            <div class="col">

                <h2>{{$t("feed.name")}}</h2>
                <feed v-if="constructionSiteId !== null" :construction-site-id="constructionSiteId" />
            </div>
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";
    import bAlert from 'bootstrap-vue/es/components/alert/alert'
    import {AtomSpinner} from 'epic-spinners'
    import notifications from '../mixins/Notifications'
    import Feed from "./components/Feed";
    import Notes from "./components/Notes";
    import Overview from "./components/Overview";

    moment.locale('de');

    export default {
        data: function () {
            return {
                constructionSiteId: null,
                isOverviewLoading: true,
                overview: null
            }
        },
        mixins: [notifications],
        components: {
            Overview,
            Notes,
            Feed,
            bAlert,
            AtomSpinner
        },
        computed: {},
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

            //fill register
            axios.get("/api/configuration").then((response) => {
                this.constructionSiteId = response.data.constructionSite.id;

                axios.post("/api/statistics/issues/overview", {
                    "constructionSiteId": this.constructionSiteId,
                }).then((response) => {
                    this.overview = response.data.overview;
                    this.isOverviewLoading = false;
                });
            });
        },
    }

</script>

<style>
    .filter-property-wrapper {
        margin-bottom: 1em;
        margin-top: 0.2em;
        margin-left: 1.5rem;
        padding: 0.5rem 1rem;
        background-color: #efefef;
    }

    input[type=date].form-control-sm {
        padding-right: 0;
    }
</style>