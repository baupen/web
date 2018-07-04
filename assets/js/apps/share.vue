<template>
    <div id="share">

        <div v-if="lightbox.enabled" class="lightbox" @click="closeLightbox()">
            <div class="lightbox-content">
                <img :src="lightbox.imageFilePath"/>
            </div>
            <font-awesome-icon class="lightbox-close" :icon="['fal', 'times']"/>
        </div>

        <section class="public-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <template>
                        <h1 v-if="issues === null">{{ $tc("loading_open_issues")}}</h1>
                        <h1 v-else>{{ $tc("open_issues_header", issuesLength, {count: issuesLength})}}</h1>
                    </template>
                    <p v-if="craftsman !== null" class="text-secondary">
                        {{ $t("of_craftsman", {craftsman: craftsman.name}) }}
                    </p>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-outline-primary btn-lg float-right">{{$t("print")}}</button>
                </div>
            </div>

            <div class="public-content">
                <div v-if="issues !== null && issues.length === 0">
                    <h2 class="display-1">{{ $t("thanks") }}</h2>
                </div>
                <div v-else>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>{{ $t("map.name")}}</th>
                            <th>{{ $t("open_issues")}}</th>
                            <th>{{ $t("next_response_limit")}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="map in maps" class="clickable">
                            <td>
                                {{map.name}}<br/>
                                <span class="small">{{map.context}}</span>
                            </td>
                            <td>{{map.issues.filter(i => !i.responded).length}}</td>
                            <td>{{nextResponseLimit(map.issues.filter(i => !i.responded))}}</td>
                        </tr>
                        </tbody>
                    </table>
                    <div class="map-content">
                        <div class="container">
                            <div v-for="map in maps" class="map-wrapper">
                                <h2>{{map.name}}</h2>
                                <p v-if="map.context !== ''" class="text-secondary"> {{ map.context }} </p>
                                <div class="card-columns">
                                    <div v-if="map.imageFilePath !== ''" class="card">
                                        <img class="card-img clickable" :src="map.imageFilePath"
                                             @click.prevent="openLightbox(map)">
                                    </div>
                                    <div class="card numbered-card" :class="{ 'border-success' : issue.responded }"
                                         v-for="issue in map.issues">
                                        <img v-if="issue.imageFilePath !== ''" class="card-img-top clickable"
                                             :src="issue.imageFilePath" @click.prevent="openLightbox(issue)">
                                        <div class="card-number"
                                             :class="{ 'bg-success text-white' : issue.responded, 'bg-warning text-white': !issue.responded }">
                                            {{ issue.number }}
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text">{{issue.description}}</p>
                                            <p class="card-text">
                                                <small class="small">{{$t("issue.response_limit")}}: {{
                                                    formatDateTime(issue.responseLimit) }}
                                                </small>
                                            </p>
                                            <template>
                                                <button v-if="!issue.responded" @click.prevent="sendResponse(issue)"
                                                        class="btn btn-outline-success">
                                                    {{$t("send_response")}}
                                                </button>
                                                <button v-else="issue.responded" @click.prevent="removeResponse(issue)"
                                                        class="btn btn-outline-warning">
                                                    {{$t("remove_response")}}
                                                </button>
                                            </template>
                                        </div>
                                        <div class="card-footer">
                                            <small class="text-muted">{{issue.registrationByName}} -
                                                {{formatDateTime(issue.registeredAt)}}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";

    moment.locale('de');

    export default {
        data: function () {
            return {
                craftsman: null,
                isLoading: true,
                issues: null,
                identifier: null,
                maps: null,
                lightbox: {
                    enabled: false,
                    imageFilePath: null
                }
            }
        },
        methods: {
            openLightbox: function (element) {
                this.lightbox.enabled = true;
                this.lightbox.imageFilePath = element.imageFilePath;
            },
            closeLightbox: function () {
                this.lightbox.enabled = false;
            },
            displayInfoFlash: function (content) {
                this.displayFlash(content, "success");
            },
            displayErrorFlash: function (content) {
                this.displayFlash(content, "danger");
            },
            displayFlash: function (content, alertType) {
                let alert = $('#alert-template').html();
                const uniqueId = 'id-' + Math.random().toString(36).substr(2, 16);
                alert = alert.replace("ALERT_TYPE", alertType).replace("ID", uniqueId).replace("MESSAGE", content);

                $('.flash-wrapper').append(alert);
                $('#' + uniqueId).alert();

                setTimeout(function () {
                    $('#' + uniqueId).alert('close');
                }, 3000);
            },
            formatDateTime: function (value) {
                if (value === null) {
                    return "-"
                }
                return moment(value).fromNow();
            },
            sendResponse: function (issue) {
                axios.post("/external/api/share/c/" + this.identifier + "/issue/respond", {issueId: issue.id}).then((response) => {
                    if (response.data.successfulIds.length > 0) {
                        issue.responded = true;
                        console.log("here");
                        console.log(issue);
                    }
                });
            },
            removeResponse: function (issue) {
                axios.post("/external/api/share/c/" + this.identifier + "/issue/remove_response", {issueId: issue.id}).then((response) => {
                    if (response.data.successfulIds.length > 0) {
                        issue.responded = false;
                        console.log("here");
                        console.log(issue);
                    }
                });
            },
            nextResponseLimit: function (array) {
                let currentResponseLimit = null;
                array.forEach(i => {
                    if (currentResponseLimit === null || (i.responseLimit !== null && i.responseLimit < currentResponseLimit)) {
                        currentResponseLimit = i.responseLimit;
                    }
                });

                if (currentResponseLimit === null) {
                    return "-"
                }
                return this.formatDateTime(currentResponseLimit);
            }
        },
        computed: {
            issuesLength: function () {
                if (this.issues === null) {
                    return 0;
                } else {
                    return this.issues.length;
                }
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

            axios.get("/external/api/share/c/" + this.identifier + "/read").then((response) => {
                this.craftsman = response.data.craftsman;
                axios.get("/external/api/share/c/" + this.identifier + "/maps/list").then((response) => {
                    this.maps = response.data.maps;
                    let issues = [];
                    this.maps.forEach(m => {
                        issues = issues.concat(m.issues);
                    });
                    issues.forEach(i => this.$set(i, "responded", false));
                    this.issues = issues;
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
</style>