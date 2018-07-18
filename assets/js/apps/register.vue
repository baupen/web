<template>
    <div id="register">
        <div class="row">
            <div class="col-md-2">
                <h2>{{$t("headers.search")}}</h2>

                <div class="vertical-spacer-big"></div>
                <h2>{{$t("headers.filter")}}</h2>
                <div class="card">
                    <div class="card-body">
                        <h4>{{$t("issue.status")}}</h4>
                        <base-checkbox v-model="filter.status.registered.active">
                            {{$t("filter.status.registered")}}
                        </base-checkbox>
                        <div class="filter-property-wrapper" v-if="filter.status.registered.active">
                            <filter-status :status="filter.status.registered" allow-value-choose="false" allow-date-choose="true"/>
                        </div>

                        <base-checkbox v-model="filter.status.read.active">
                            {{$t("filter.status.read")}}
                        </base-checkbox>
                        <div class="filter-property-wrapper" v-if="filter.status.read.active">
                            <filter-status :status="filter.status.read" />
                        </div>


                        <base-checkbox v-model="filter.status.responded.active">
                            {{$t("filter.status.responded")}}
                        </base-checkbox>
                        <div class="filter-property-wrapper" v-if="filter.status.responded.active">
                            <filter-status :status="filter.status.responded" allow-date-choose="true"/>
                        </div>

                        <base-checkbox v-model="filter.status.reviewed.active">
                            {{$t("filter.status.reviewed")}}
                        </base-checkbox>
                        <div class="filter-property-wrapper" v-if="filter.status.reviewed.active">
                            <filter-status :status="filter.status.reviewed" allow-date-choose="true"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <issue-edit-table :is-mounting="isLoading"
                                  :craftsmen="craftsmen"
                                  :issues="filteredIssues">
                </issue-edit-table>
            </div>
            <div class="col-md-2">

            </div>

        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";
    import IssueEditTable from "./components/IssueEditTable"
    import BaseCheckbox from "./components/BaseCheckbox"
    import BaseDateInput from './components/BaseDateInput'
    import BaseSliderCheckbox from "./components/BaseSliderCheckbox"
    import FilterStatus from './components/FilterStatus'
    import {de} from 'vuejs-datepicker/dist/locale'

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
                date_picker_locale: de,
                filter: {
                    status: {
                        enabled: true,
                        read: {
                            active: false,
                            value: true
                        },
                        registered: {
                            active: false,
                            start: null,
                            end: null
                        },
                        responded: {
                            active: false,
                            value: true,
                            start: null,
                            end: null
                        },
                        reviewed: {
                            active: false,
                            value: false,
                            start: null,
                            end: null
                        }
                    }
                }
            }
        },
        components: {
            IssueEditTable,
            BaseCheckbox,
            BaseSliderCheckbox,
            BaseDateInput,
            FilterStatus
        },
        computed: {
            filteredIssues: function () {
                let res = this.issues;
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