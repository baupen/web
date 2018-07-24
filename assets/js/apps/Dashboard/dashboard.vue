<template>
    <div id="dashboard">
        <b-alert v-if="!isOverviewLoading" :show="overview.newIssuesCount > 0" variant="warning">
            <h4>{{ $tc("dialog.new_issues_in_foyer", overview.newIssuesCount, {count:
                overview.newIssuesCount})}}</h4>
            <p><a href="/foyer">{{t("dialog.add_to_register")}}</a></p>
        </b-alert>

        <h2><a href="/register">{{$t("register.name")}}</a></h2>
        <atom-spinner v-if="isOverviewLoading"
                      :animation-duration="1000"
                      :size="60"
                      :color="'#ff1d5e'"
        />
        <div v-else class="row">
            <number-tile class="col-md-3" link="/register?view=open"
                         :number="overview.openIssuesCount"
                         :description="$t('issue.status_values.open')"/>

            <number-tile class="col-md-3" link="/register?view=overdue"
                         :number="overview.overdueIssuesCount"
                         :description="$t('register.status_action.overdue')"/>

            <number-tile class="col-md-3" link="/register?view=to_inspect"
                         :number="overview.respondedNotReviewedIssuesCount"
                         :description="$t('register.status_action.to_inspect')"/>

            <number-tile class="col-md-3" link="/register?view=marked"
                         :number="overview.markedIssuesCount"
                         :description="$t('register.status_action.marked')"/>
        </div>
        <div class="row">
            <div class="col">
                <h2>{{$t("feed.name")}}</h2>
                <feed />
            </div>
            <div class="col">
                <h2>{{$t("notes.name")}}</h2>

                <notes />

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
    import NumberTile from "./components/NumberTile";
    import Feed from "./components/Feed";
    import Notes from "./components/Notes";

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
            Notes,
            Feed,
            NumberTile,
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
                this.filter.constructionSiteId = this.constructionSiteId;

                axios.post("/api/dashboard/statistics/overview", {
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