<template>
    <div id="share">

        <section class="public-wrapper">
            <template>
                <h1 v-if="issues === null">{{ $tc("loading_open_issues")}}</h1>
                <h1 v-else>{{ $tc("open_issues_header", issuesLength, {count: issuesLength})}}</h1>
            </template>
            <p v-if="craftsman !== null" class="text-secondary">{{ $t("of_craftsman", {craftsman: craftsman.name})
                }}</p>
            <div class="public-content">
                <div v-if="issues !== null && issues.length === 0">
                    <h2 class="display-1">{{ $t("thanks") }}</h2>
                </div>
                <div class="row" v-else>
                    <div class="col-md-6">
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
                    </div>
                    <div class="col-md-6">
                        <div v-for="map in maps">
                            <img :src="map.imageFilePath" class="img-responsive"/>
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
                maps: null
            }
        },
        methods: {
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
                    console.log(response);
                    this.maps = response.data.maps;
                    console.log(this.maps);
                    let issues = [];
                    this.maps.forEach(m => {
                        issues = issues.concat(m.issues);
                        m.issues.forEach(i => i.responded = false);
                    });
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
</style>