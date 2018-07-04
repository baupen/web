<template>
    <div id="share">

        <section class="public-wrapper">
            <template>
                <h1 v-if="issues === null">{{ $tc("loading_open_issues")}}</h1>
                <h1 v-else>{{ $tc("open_issues", issuesLength, {count: issuesLength})}}</h1>
            </template>
            <p v-if="craftsman !== null" class="text-secondary">{{ $t("of_craftsman", {craftsman: craftsman.name}) }}</p>
            <div class="public-content">
                <div v-if="issues !== null && issues.length === 0">
                    <h2 class="display-1">{{ $t("thanks") }}</h2>
                </div>
                <div v-else>

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
            sendEmails: function () {
                this.isLoading = true;
                axios.post("/api/dispatch", {
                    "constructionSiteId": this.constructionSiteId,
                    "craftsmanIds": this.craftsmen.filter(c => c.selected).map(c => c.id)
                }).then((response) => {
                    this.isLoading = false;
                    this.craftsmen.filter(c => c.selected).forEach(c => {
                        if (response.data.successfulIds.includes(c.id)) {
                            c.lastEmailSent = (new Date()).toISOString();
                        }
                        c.selected = false;
                    });

                    this.displayInfoFlash(this.$t("emails_sent"));
                });
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
            selectAll: function () {
                let newVal = !(this.indeterminate || this.selected);
                this.craftsmen.forEach(c => c.selected = newVal);
            },
            sortBy: function (key) {
                if (this.sortKey === key) {
                    this.sortOrders[key] *= -1;
                } else {
                    this.sortKey = key;
                }
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
                    this.maps.forEach(m => issues = issues.concat(m.issues));
                    this.issues = issues;
                    this.isLoading = false;
                });
            });
        },
    }

</script>

<style>
    .filter-field {
        max-width: 400px;
    }
</style>