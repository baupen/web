<template>
    <div id="share">

        <vue-headful
                :title="title"
                :description="description"
        />

        <lightbox :open="lightbox.enabled" :imageSrc="lightbox.imageFull" @close="lightbox.enabled = false" />

        <section class="public-wrapper">
            <div class="row">
                <div class="col-md-6">
                    <h1>{{title}}</h1>
                    <p v-if="description !== ''" class="text-secondary">
                        {{description}}
                    </p>
                </div>
                <div class="col-md-6">
                    <a :href="craftsman.reportUrl" target="_blank" class="btn btn-outline-primary btn-lg float-right">{{$t("print")}}</a>
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
                        <tr v-for="map in maps" class="clickable" @click.prevent="scrollTo('map-' + map.id)">
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
                            <div :ref="'map-' + map.id" v-for="map in maps" class="map-wrapper">
                                <h2>{{map.name}}</h2>
                                <p v-if="map.context !== ''" class="text-secondary"> {{ map.context }} </p>
                                <div class="card-columns">
                                    <div v-if="map.imageShareView !== ''" class="card">
                                        <img class="card-img clickable" :src="map.imageShareView"
                                             @click.prevent="openLightbox(map)">
                                    </div>
                                    <div class="card numbered-card" :class="{ 'border-success' : issue.responded }"
                                         v-for="issue in map.issues">
                                        <img v-if="issue.imageShareView !== ''" class="card-img-top clickable"
                                             :src="issue.imageShareView" @click.prevent="openLightbox(issue)">
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
    import Lightbox from '../components/Lightbox'

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
                    imageFull: null
                }
            }
        },
        components: {
            Lightbox
        },
        methods: {
            scrollTo: function (ref) {
                const messageDisplay = this.$refs[ref][0];
                console.log(messageDisplay);
                messageDisplay.scrollIntoView();
            },
            openLightbox: function (element) {
                this.lightbox.enabled = true;
                this.lightbox.imageFull = element.imageFull;
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
                        this.adaptImageSrc(issue.map);
                    }
                });
            },
            removeResponse: function (issue) {
                axios.post("/external/api/share/c/" + this.identifier + "/issue/remove_response", {issueId: issue.id}).then((response) => {
                    if (response.data.successfulIds.length > 0) {
                        issue.responded = false;
                        this.adaptImageSrc(issue.map);
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
            },
            adaptImageSrc: function (map) {
                let str = map.id + "," + map.issues.filter(i => !i.responded).map(i => i.id).join(",");
                this.hash(str).then(hash => {
                    let prepared = map.imageFilePath.substring(0, map.imageFilePath.lastIndexOf("/"));
                    console.log(prepared);
                    map.imageFilePath = prepared + "/" + hash;
                })
            },
            hash: function (message) {
                // We transform the string into an arraybuffer.
                const buffer = new TextEncoder("utf-8").encode(message);
                return crypto.subtle.digest("SHA-256", buffer).then(function (hash) {
                    const hexCodes = [];
                    const view = new DataView(hash);
                    for (let i = 0; i < view.byteLength; i += 4) {
                        // Using getUint32 reduces the number of iterations needed (we process 4 bytes each time)
                        const value = view.getUint32(i);
                        // toString(16) will give the hex representation of the number without padding
                        const stringValue = value.toString(16);
                        // We use concatenation and slice for padding
                        const padding = '00000000';
                        const paddedValue = (padding + stringValue).slice(-padding.length);
                        hexCodes.push(paddedValue);
                    }

                    // Join all the hex strings into one
                    return hexCodes.join("");
                });
            },
        },
        computed: {
            issuesLength: function () {
                if (this.issues === null) {
                    return 0;
                } else {
                    return this.issues.filter(i => !i.responded).length;
                }
            },
            title: function () {
                if (this.issues !== null) {
                    return this.$tc("open_issues_header", this.issuesLength, {count: this.issuesLength});
                }
                return this.$t("loading_open_issues");
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
                    let issues = [];
                    this.maps.forEach(m => {
                        m.issues.forEach(i => i.map = m);
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

    @media (max-width : 680px) {
        .map-wrapper {
            margin-top: 2rem;
        }

        .map-content {
            padding-top: 2rem;
        }
    }

</style>