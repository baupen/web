<template>
    <div id="register">
        <div class="row">
            <div class="col-md-2">
                <h2>{{$t("headers.search")}}</h2>
                <div class="card">
                    <div class="card-body">
                        <base-text-input v-model="filter.numberText">
                            {{$t("issue.number")}}
                        </base-text-input>
                        <base-checkbox v-model="filter.onlyMarked">
                            {{$t("only_marked")}}
                        </base-checkbox>
                        <base-checkbox v-model="filter.onlyOverLimit">
                            {{$t("only_over_limit")}}
                        </base-checkbox>
                    </div>
                </div>

                <div class="vertical-spacer-big"></div>
                <h2>{{$t("headers.filter")}}</h2>
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
                                      :issues="filteredIssues">
                    </issue-edit-table>
                </div>
            </div>
            <div class="col-md-2">
                <h2>{{$t("headers.export")}}</h2>
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
    import IssueEditTable from "./components/IssueEditTable"
    import StatusFilter from "./components/StatusFilter"
    import CraftsmanFilter from "./components/CraftsmanFilter"
    import TradeFilter from "./components/TradeFilter"
    import MapFilter from "./components/MapFilter"
    import BaseTextInput from "./components/BaseTextInput"
    import BaseCheckbox from "./components/BaseCheckbox"
    import PdfExport from "./components/PdfExport"
    import LinkExport from "./components/LinkExport"
    import {de} from 'vuejs-datepicker/dist/locale'
    import {AtomSpinner} from 'epic-spinners'

    moment.locale('de');

    Array.prototype.unique = function () {
        return Array.from(new Set(this));
    };

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
                        enabled: true,
                        read: {
                            active: false,
                            value: true
                        },
                        registered: {
                            active: false,
                            value: true,
                            start: null,
                            end: null
                        },
                        responded: {
                            active: true,
                            value: true,
                            start: null,
                            end: null
                        },
                        reviewed: {
                            active: true,
                            value: false,
                            start: null,
                            end: null
                        }
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
                    onlyMarked: false,
                    onlyOverLimit: false,
                    numberText: ""
                }
            }
        },
        components: {
            IssueEditTable,
            StatusFilter,
            CraftsmanFilter,
            TradeFilter,
            MapFilter,
            AtomSpinner,
            BaseTextInput,
            BaseCheckbox,
            PdfExport,
            LinkExport
        },
        computed: {
            filteredIssues: function () {
                let res = this.issues;

                const numberText = this.filter.numberText;
                if (numberText.length > 0) {
                    res = res.filter(i => i.number.startsWith(numberText));
                }

                if (this.filter.onlyMarked) {
                    res = res.filter(i => i.isMarked === true);
                }

                const statusFilter = this.filter.status;
                if (statusFilter.enabled) {
                    if (statusFilter.read.active) {
                        res = res.filter(i => i.isRead === statusFilter.read.value);
                    }

                    if (statusFilter.registered.active) {
                        res = this.filterStartEnd(res, null, statusFilter.registered, "registeredAt");
                    }

                    if (statusFilter.responded.active) {
                        res = this.filterStartEnd(res, statusFilter.responded.value, statusFilter.responded, "respondedAt");
                    }

                    if (statusFilter.reviewed.active) {
                        res = this.filterStartEnd(res, statusFilter.reviewed.value, statusFilter.reviewed, "reviewedAt");
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
                    res = res.filter(i => i.responseLimit > today);
                }

                return res;
            }
        },
        methods: {
            filterStartEnd: function (issues, enabled, startEnd, property) {
                //no start/end sorting possible
                if (enabled === false) {
                    return issues.filter(i => i[property] === null);
                }

                //only filter if enabled is true
                if (enabled === true) {
                    issues = issues.filter(i => i[property] !== null);
                }

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
                    console.log("request failed");
                    console.log(error.response.data);
                    return Promise.reject(error);
                }
            );
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
                });

                axios.post("/api/register/map/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.maps = response.data.maps;
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