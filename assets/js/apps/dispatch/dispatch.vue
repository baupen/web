<template>
    <div id="dispatch">
        <div v-if="craftsmen.length > 0" class="selectable-table">
            <table class="table table-hover table-striped">
                <thead>
                <tr>
                    <th class="minimal-width"></th>
                    <th>{{ $t("craftsman.name")}}</th>
                    <th>{{ $t("craftsman.trade")}}</th>
                    <th>{{ $t("craftsman.not_read_issues_count")}}</th>
                    <th>{{ $t("craftsman.not_responded_issues_count")}}</th>
                    <th>{{ $t("craftsman.next_response_limit")}}</th>
                    <th>{{ $t("craftsman.last_email_sent")}}</th>
                    <th>{{ $t("craftsman.last_online_visit")}}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="craftsman in craftsmen" v-on:click.prevent="craftsman.selected = !craftsman.selected">
                    <td class="minimal-width">
                        <input title="check" type="checkbox" v-model="craftsman.selected"/>
                    </td>
                    <td>
                        {{ craftsman.name }}
                    </td>
                    <td>
                        {{craftsman.trade}}
                    </td>
                    <td>
                        {{craftsman.notReadIssuesCount}}
                    </td>
                    <td>
                        {{craftsman.notRespondedIssuesCount}}
                    </td>
                    <td>
                        {{ formatDateTime(craftsman.nextResponseLimit)}}
                    </td>
                    <td>
                        {{ formatDateTime(craftsman.lastEmailSent)}}
                    </td>
                    <td>
                        {{ formatDateTime(craftsman.lastOnlineVisit)}}
                    </td>
                </tr>

                </tbody>
            </table>
            <button class="btn btn-primary" v-on:click.prevent="sendEmails()">{{$t("send_emails")}}</button>
        </div>
        <div v-else-if="!isLoading">
            <p>{{ $t("no_craftsmen") }}</p>
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";

    moment.locale('de');

    export default {
        data() {
            return {
                craftsmen: [],
                isLoading: true,
                constructionSiteId: null
            }
        },
        components: {},
        methods: {
            sendEmails: function () {
                this.isLoading = true;
                axios.post("/api/dispatch", {
                    "constructionSiteId": this.constructionSiteId,
                    "craftsmanIds": this.craftsmen.filter(c => c.selected).map(c => c.id)
                }).then((response) => {
                    this.isLoading = false;
                });
            },
            formatDateTime: function (value) {
                if (value === null) {
                    return "-"
                }
                return moment(value).fromNow();
            }
        },
        mounted() {
            // Add a response interceptor
            axios.interceptors.response.use(
                response => {
                    return response.data;
                },
                error => {
                    console.log(error.response.data.message);
                    return Promise.reject(error);
                }
            );

            axios.get("/api/configuration").then((response) => {
                this.constructionSiteId = response.data.constructionSite.id;
                axios.post("/api/dispatch/craftsman/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    response.data.craftsmen.forEach(a => a.selected = false);
                    this.craftsmen = response.data.craftsmen;
                    this.isLoading = false;
                });
            });
        },
    }

</script>