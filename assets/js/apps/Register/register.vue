<template>
    <div id="register">
        <div class="row">
            <div class="col-md-2">
                <h2>{{$t("search.name")}}</h2>
                <div class="card">
                    <div class="card-body">
                        <base-text-input v-model="filter.numberText">
                            {{$t("issue.number")}}
                        </base-text-input>
                        <base-checkbox v-model="filter.onlyMarked">
                            {{$t("search.only_marked")}}
                        </base-checkbox>
                        <base-checkbox v-model="filter.onlyOverLimit">
                            {{$t("search.only_over_limit")}}
                        </base-checkbox>
                    </div>
                </div>

                <div class="vertical-spacer-big"></div>
                <h2>{{$t("filter.name")}}</h2>
                <div class="card">
                    <div class="card-body">
                        <status-filter :filter="filter.status"/>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <atom-spinner v-if="isLoading"
                                      :animation-duration="1000"
                                      :size="60"
                                      :color="'#ff1d5e'"
                        />
                        <craftsman-filter v-else :filter="filter.craftsman" :craftsmen="craftsmen"/>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <atom-spinner v-if="isLoading"
                                      :animation-duration="1000"
                                      :size="60"
                                      :color="'#ff1d5e'"
                        />
                        <trade-filter v-else :filter="filter.trade" :craftsmen="craftsmen"/>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <atom-spinner v-if="isLoading"
                                      :animation-duration="1000"
                                      :size="60"
                                      :color="'#ff1d5e'"
                        />
                        <map-filter v-else :filter="filter.map" :maps="maps"/>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <time-filter :filter="filter.time"/>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <atom-spinner
                        v-if="isLoading"
                        :animation-duration="1000"
                        :size="60"
                        :color="'#ff1d5e'"
                />
                <div v-else>
                    <issue-edit-table :craftsmen="craftsmen"
                                      :issues="filteredIssues"
                                      @update-issues="updateIssues"
                                      @update-status="updateStatus">
                    </issue-edit-table>
                </div>
            </div>
            <div class="col-md-2">
                <h2>{{$t("export.name")}}</h2>
                <div class="card">
                    <div class="card-body">
                        <link-export :filter="filter"/>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <pdf-export :filter="filter"/>
                    </div>
                </div>

            </div>

        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";
    import IssueEditTable from "../components/IssueEditTable"
    import TimeFilter from "./components/TimeFilter"
    import StatusFilter from "./components/StatusFilter"
    import CraftsmanFilter from "./components/CraftsmanFilter"
    import TradeFilter from "./components/TradeFilter"
    import MapFilter from "./components/MapFilter"
    import BaseTextInput from "../components/Base/BaseTextInput"
    import BaseCheckbox from "../components/Base/BaseCheckbox"
    import PdfExport from "./components/PdfExport"
    import LinkExport from "./components/LinkExport"
    import {de} from 'vuejs-datepicker/dist/locale'
    import {AtomSpinner} from 'epic-spinners'
    import notifications from '../mixins/Notifications'

    moment.locale('de');

    export default {
        data: function () {
            return {
                constructionSiteId: null,
                issues: [],
                isLoading: true,
                craftsmen: [],
                trade: [],
                maps: [],
                date_picker_locale: de,
                filter: {
                    constructionSiteId: null,
                    status: {
                        enabled: false,
                        registered: false,
                        read: false,
                        responded: false,
                        reviewed: false
                    },
                    craftsman: {
                        enabled: false,
                        craftsmen: []
                    },
                    trade: {
                        enabled: false,
                        trades: []
                    },
                    map: {
                        enabled: false,
                        maps: []
                    },
                    time: {
                        enabled: false,
                        read: {
                            active: false
                        },
                        registered: {
                            active: false,
                            start: null,
                            end: null
                        },
                        responded: {
                            active: false,
                            start: null,
                            end: null
                        },
                        reviewed: {
                            active: false,
                            start: null,
                            end: null
                        }
                    },
                    onlyMarked: false,
                    onlyOverLimit: false,
                    numberText: ""
                }
            }
        },
        mixins: [notifications],
        components: {
            IssueEditTable,
            TimeFilter,
            CraftsmanFilter,
            TradeFilter,
            MapFilter,
            AtomSpinner,
            BaseTextInput,
            BaseCheckbox,
            PdfExport,
            LinkExport,
            StatusFilter
        },
        computed: {
            filteredIssues: function () {
                let res = this.issues;

                const statusFilter = this.filter.status;
                if (statusFilter.enabled) {
                    if (!statusFilter.registered) {
                        res = res.filter(i => i.isRead || i.reviewedAt !== null || i.respondedAt !== null);
                    }
                    if (!statusFilter.read) {
                        res = res.filter(i => i.reviewedAt !== null || i.respondedAt !== null || (!i.isRead && i.respondedAt === null && i.reviewedAt === null));
                    }
                    if (!statusFilter.responded) {
                        res = res.filter(i => i.reviewedAt !== null || (i.respondedAt === null && i.reviewedAt === null));
                    }
                    if (!statusFilter.reviewed) {
                        res = res.filter(i => i.reviewedAt === null);
                    }
                }

                const numberText = this.filter.numberText;
                if (numberText.length > 0) {
                    res = res.filter(i => i.number.startsWith(numberText));
                }

                if (this.filter.onlyMarked) {
                    res = res.filter(i => i.isMarked === true);
                }

                const timeFilter = this.filter.time;
                if (timeFilter.enabled) {
                    if (timeFilter.read.active) {
                        res = res.filter(i => i.isRead === timeFilter.read.active);
                    }

                    if (timeFilter.registered.active) {
                        res = this.filterStartEnd(res, timeFilter.registered, "registeredAt");
                    }

                    if (timeFilter.responded.active) {
                        res = this.filterStartEnd(res, timeFilter.responded, "respondedAt");
                    }

                    if (timeFilter.reviewed.active) {
                        res = this.filterStartEnd(res, timeFilter.reviewed, "reviewedAt");
                    }
                }

                const craftsmanFilter = this.filter.craftsman;
                if (craftsmanFilter.enabled) {
                    const ids = craftsmanFilter.craftsmen.map(c => c.id);
                    res = res.filter(i => ids.indexOf(i.craftsmanId) >= 0);
                }

                const mapFilter = this.filter.map;
                if (mapFilter.enabled) {
                    const ids = mapFilter.maps.map(c => c.id);
                    res = res.filter(i => ids.indexOf(i.mapId) >= 0);
                }

                const tradeFilter = this.filter.trade;
                if (tradeFilter.enabled) {
                    const ids = this.craftsmen.filter(c => tradeFilter.trades.indexOf(c.trade) >= 0).map(c => c.id);
                    res = res.filter(i => ids.indexOf(i.craftsmanId) >= 0);
                }

                if (this.filter.onlyOverLimit) {
                    const today = (new Date()).toISOString();
                    res = res.filter(i => i.responseLimit < today);
                }

                return res;
            }
        },
        methods: {
            filterStartEnd: function (issues, startEnd, property) {
                //filter mandates that value exists
                issues = issues.filter(i => i[property] !== null);

                //filter by time
                const start = startEnd.start;
                const end = startEnd.end;
                if (start !== null) {
                    return issues.filter(i => i[property] >= start);
                }
                if (end !== null) {
                    console.log(end);
                    console.log(issues[0][property]);
                    return issues.filter(i => i[property] <= end);
                }
                return issues;
            },
            updateIssues: function (issues) {
                axios.post("/api/register/issue/update", {
                    "constructionSiteId": this.constructionSiteId,
                    "updateIssues": issues
                }).then((response) => {
                    this.writeProperties(response.data.issues);
                    this.displayInfoFlash(this.$t("messages.success.saved_changes"));
                });
            },
            updateStatus: function (issues, respondedStatusSet, reviewedStatusSet) {
                axios.post("/api/register/issue/status", {
                    "constructionSiteId": this.constructionSiteId,
                    "issueIds": issues.map(i => i.id),
                    respondedStatusSet,
                    reviewedStatusSet
                }).then((response) => {
                    this.writeProperties(response.data.issues);
                    this.displayInfoFlash(this.$t("messages.success.saved_changes"));
                });
            },
            writeProperties: function (newIssues) {
                newIssues.forEach(c => {
                    let match = this.issues.filter(i => i.id === c.id)[0];
                    match.craftmanId = c.craftsmanId;
                    match.description = c.description;
                    match.reponseLimit = c.reponseLimit;
                    match.respondedAt = c.respondedAt;
                    match.responseByName = c.responseByName;
                    match.reviewedAt = c.reviewedAt;
                    match.reviewByName = c.reviewByName;
                });
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

            const url = new URL(window.location.href);

            //fill register
            axios.get("/api/configuration").then((response) => {
                this.constructionSiteId = response.data.constructionSite.id;
                this.filter.constructionSiteId = this.constructionSiteId;

                axios.post("/api/register/issue/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.issues = response.data.issues;
                    this.isLoading = false;

                    if (url.searchParams.has("issue")) {
                        this.filter.status.enabled = false;
                        this.filter.numberText = url.searchParams.get("issue");
                    }
                });

                axios.post("/api/register/craftsman/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.craftsmen = response.data.craftsmen;

                    if (url.searchParams.has("craftsman")) {
                        this.filter.status.enabled = false;

                        this.filter.craftsman.enabled = true;
                        this.filter.craftsman.craftsmen = this.craftsmen.filter(c => c.id === url.searchParams.get("craftsman"));
                    }
                });

                axios.post("/api/register/map/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.maps = response.data.maps;
                });
            });

            if (url.searchParams.has("view")) {
                //set filter default values
                if (url.searchParams.get("view") === "overdue") {
                    this.filter.status.enabled = false;
                    this.filter.onlyOverLimit = true;
                } else if (url.searchParams.get("view") === "marked") {
                    this.filter.status.enabled = false;
                    this.filter.onlyMarked = true;
                } else if (url.searchParams.get("view") === "open") {
                    this.filter.status.responded.active = false;
                } else {
                    //to_inspect is the default view
                }
            }
        }
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