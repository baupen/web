<template>
    <div id="register">
        <div class="row">
            <div class="col-md-2">
                <issue-filter
                        :construction-site-id="constructionSiteId"
                        :craftsmen="craftsmen"
                        :maps="maps"
                        :filter="filter"/>
                <div class="vertical-spacer-big"></div>
                <issue-export
                        :construction-site-id="constructionSiteId"
                        :filter="filter" />
            </div>
            <div class="col-md-10">
                <atom-spinner
                        v-if="isLoading"
                        :animation-duration="1000"
                        :size="60"
                        :color="'#ff1d5e'"
                />
                <div v-else>
                    <issue-edit-table :craftsmen="craftsmen"
                                      :issues="filteredIssues"
                                      :filter="filter"
                                      @update-issues="updateIssues"
                                      @update-status="updateStatus">
                    </issue-edit-table>
                    <b-pagination :per-page="100" v-model="currentPage" :total-rows="allFilteredIssues.length"></b-pagination>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import IssueEditTable from "../components/IssueEditTable"
    import PdfExport from "./components/IssueExport/PdfExport"
    import LinkExport from "./components/IssueExport/LinkExport"
    import {AtomSpinner} from 'epic-spinners'
    import notifications from '../mixins/Notifications'
    import NormalizeFilter from "./mixins/NormalizeFilter";
    import IssueFilter from "./components/IssueFilter";
    import IssueExport from "./components/IssueExport";

    const lang = document.documentElement.lang.substr(0, 2);
    const datePickerLocale = require('vuejs-datepicker/dist/locale');
    const datePickerTranslation = datePickerLocale[lang];

    export default {
        data: function () {
            return {
                currentPage: 1,
                constructionSiteId: null,
                issues: [],
                isLoading: true,
                craftsmen: [],
                trade: [],
                maps: [],
                datePickerLocale: datePickerTranslation,
                initialTextFilterIssueEditTable: "",
                filter: {
                    onlyMarked: false,
                    onlyOverLimit: false,
                    onlyWasAddedWithClient: false,
                    onlyWithText: "",
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
                        registered: {
                            active: false,
                            enabled: false,
                            start: null,
                            end: null
                        },
                        responded: {
                            active: false,
                            enabled: false,
                            start: null,
                            end: null
                        },
                        reviewed: {
                            active: false,
                            enabled: false,
                            start: null,
                            end: null
                        }
                    }
                }
            }
        },
        mixins: [notifications, NormalizeFilter],
        components: {
            IssueExport,
            IssueFilter,
            IssueEditTable,
            AtomSpinner,
            PdfExport,
            LinkExport
        },
        computed: {
            filteredIssues: function() {
                let currentPage = this.currentPage;

                const paginatedStart = (currentPage-1)*100;
                if (this.allFilteredIssues.length < paginatedStart) {
                    currentPage = 0;
                }

                return this.allFilteredIssues.slice((currentPage-1)*100, (currentPage)*100);
            },
            allFilteredIssues: function () {
                let res = this.issues;

                const filter = this.normalizeFilter(this.filter);

                if (filter.onlyMarked) {
                    res = res.filter(i => i.isMarked === true);
                }

                if (filter.onlyWasAddedWithClient) {
                    res = res.filter(i => i.wasAddedWithClient === true);
                }

                if (filter.onlyOverLimit) {
                    const today = (new Date()).toISOString();
                    res = res.filter(i => i.responseLimit < today);
                }

                const statusFilter = filter.status;
                if (statusFilter.enabled) {
                    if (!statusFilter.registered) {
                        res = res.filter(i => i.isRead || i.reviewedAt !== null || i.respondedAt !== null);
                    }
                    if (!statusFilter.read) {
                        res = res.filter(i => i.reviewedAt !== null || i.respondedAt !== null || (!i.isRead));
                    }
                    if (!statusFilter.responded) {
                        res = res.filter(i => i.reviewedAt !== null || (i.respondedAt === null));
                    }
                    if (!statusFilter.reviewed) {
                        res = res.filter(i => i.reviewedAt === null);
                    }
                }

                const craftsmanFilter = filter.craftsman;
                if (craftsmanFilter.enabled) {
                    res = res.filter(i => craftsmanFilter.craftsmen.indexOf(i.craftsmanId) >= 0);
                }

                const tradeFilter = filter.trade;
                if (tradeFilter.enabled) {
                    const ids = this.craftsmen.filter(c => tradeFilter.trades.indexOf(c.trade) >= 0).map(c => c.id);
                    res = res.filter(i => ids.indexOf(i.craftsmanId) >= 0);
                }

                const mapFilter = filter.map;
                if (mapFilter.enabled) {
                    res = res.filter(i => mapFilter.maps.indexOf(i.mapId) >= 0);
                }

                const timeFilter = filter.time;
                if (timeFilter.enabled) {
                    if (timeFilter.registered.enabled) {
                        res = this.filterStartEnd(res, timeFilter.registered, "registeredAt");
                    }

                    if (timeFilter.responded.enabled) {
                        res = this.filterStartEnd(res, timeFilter.responded, "respondedAt");
                    }

                    if (timeFilter.reviewed.enabled) {
                        res = this.filterStartEnd(res, timeFilter.reviewed, "reviewedAt");
                    }
                }

                if (filter.onlyWithText) {
                  if (isNaN(filter.onlyWithText)) {
                    const lowercaseText = filter.onlyWithText.toLowerCase()
                    res = res.filter(i => i.description.toLowerCase().includes(lowercaseText));
                  } else {
                    console.log("looking for " + filter.onlyWithText)
                    res = res.filter(i => i.number === filter.onlyWithText);
                  }
                }

                console.log(res);
                console.log(timeFilter);

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

            if (url.searchParams.has("issue")) {
                this.initialTextFilterIssueEditTable = url.searchParams.get("issue");
                this.filter.status.enabled = false;
            }

            if (url.searchParams.has("view")) {
                //set filter default values
                if (url.searchParams.get("view") === "overdue") {
                    this.filter.onlyOverLimit = true;
                } else if (url.searchParams.get("view") === "marked") {
                    this.filter.onlyMarked = true;
                } else if (url.searchParams.get("view") === "open") {
                    this.filter.status.enabled = true;
                    this.filter.status.registered = true;
                    this.filter.status.read = true;
                    this.filter.status.responded = true;
                } else if (url.searchParams.get("view") === "to_inspect") {
                    this.filter.status.enabled = true;
                    this.filter.status.responded = true;
                }
            }

            //fill register
            axios.get("/api/configuration").then((response) => {
                this.constructionSiteId = response.data.constructionSite.id;
                this.filter.constructionSiteId = this.constructionSiteId;

                axios.post("/api/register/issue/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.issues = response.data.issues;
                    this.isLoading = false;
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
        }
    }

</script>

<style>
    .filter-property-wrapper {
        margin-bottom: 1em;
        margin-top: 0.2em;
        padding: 0.5rem 1rem;
        background-color: #efefef;
    }

    input[type=date].form-control-sm {
        padding-right: 0;
    }
</style>
