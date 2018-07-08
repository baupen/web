<template>
    <div id="dispatch">
        <div v-if="craftsmen.length > 0" class="selectable-table">
            <div class="filter-field">
                <div class="form-group">
                    <input class="form-control" id="filter" type="text" v-model="textFilter"
                           :placeholder="$t('filter')"/>
                </div>
            </div>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th class="minimal-width">
                        <input title="check" type="checkbox"
                               v-bind:indeterminate.prop="indeterminate"
                               v-bind:checked="selected"
                               v-on:click.prevent="selectAll()"/>
                    </th>
                    <th class="sortable" @click="sortBy('name')" :class="{ active: 'name' === sortKey }">
                        {{ $t("craftsman.name")}}
                        <font-awesome-icon v-if="sortKey === 'name'"
                                           :icon="sortOrders['name'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>

                    <th class="sortable" @click="sortBy('trade')" :class="{ active: 'trade' === sortKey }">
                        {{ $t("craftsman.trade")}}
                        <font-awesome-icon v-if="sortKey === 'trade'"
                                           :icon="sortOrders['trade'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>

                    <th class="sortable" @click="sortBy('notReadIssuesCount')"
                        :class="{ active: 'notReadIssuesCount' === sortKey }">
                        {{ $t("craftsman.not_read_issues_count")}}
                        <font-awesome-icon v-if="sortKey === 'notReadIssuesCount'"
                                           :icon="sortOrders['notReadIssuesCount'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>


                    <th class="sortable" @click="sortBy('notRespondedIssuesCount')"
                        :class="{ active: 'notRespondedIssuesCount' === sortKey }">
                        {{ $t("craftsman.not_responded_issues_count")}}
                        <font-awesome-icon v-if="sortKey === 'notRespondedIssuesCount'"
                                           :icon="sortOrders['notRespondedIssuesCount'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>


                    <th class="sortable" @click="sortBy('nextResponseLimit')"
                        :class="{ active: 'nextResponseLimit' === sortKey }">
                        {{ $t("craftsman.next_response_limit")}}
                        <font-awesome-icon v-if="sortKey === 'nextResponseLimit'"
                                           :icon="sortOrders['nextResponseLimit'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>


                    <th class="sortable" @click="sortBy('lastEmailSent')"
                        :class="{ active: 'lastEmailSent' === sortKey }">
                        {{ $t("craftsman.last_email_sent")}}
                        <font-awesome-icon v-if="sortKey === 'lastEmailSent'"
                                           :icon="sortOrders['lastEmailSent'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>


                    <th class="sortable" @click="sortBy('lastOnlineVisit')"
                        :class="{ active: 'lastOnlineVisit' === sortKey }">
                        {{ $t("craftsman.last_online_visit")}}
                        <font-awesome-icon v-if="sortKey === 'lastOnlineVisit'"
                                           :icon="sortOrders['lastOnlineVisit'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>
                    <th>

                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="craftsman in sortedCraftsmen" v-on:click.prevent="craftsman.selected = !craftsman.selected"
                    v-bind:class="{ 'table-active': craftsman.selected   }">
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
                    <td>
                        <a :href="craftsman.personalUrl" target="_blank">
                            <font-awesome-icon v-on:click.stop="" :icon="['fal', 'user-alt']"/>
                        </a>
                    </td>
                </tr>

                </tbody>
            </table>
            <button class="btn btn-primary" v-bind:disabled="isLoading" v-on:click.prevent="sendEmails()">
                {{$t("send_emails")}}
            </button>
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
        data: function () {
            const sortOrders = {};
            ["name", "trade", "notReadIssuesCount", "notRespondedIssuesCount", "nextResponseLimit", "lastEmailSent", "lastOnlineVisit"].forEach(e => sortOrders[e] = 1);
            return {
                craftsmen: [],
                isLoading: true,
                constructionSiteId: null,
                sortKey: "name",
                sortOrders: sortOrders,
                textFilter: null,
                lastSortCache: null
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
            indeterminate: function () {
                return !this.selected && this.craftsmen.filter(c => c.selected).length > 0;
            },
            selected: function () {
                return this.craftsmen.filter(c => !c.selected).length === 0;
            },
            sortedCraftsmen: function () {
                const sortKey = this.sortKey;
                const filterKey = this.textFilter && this.textFilter.toLowerCase();
                const order = this.sortOrders[sortKey];
                let data = this.craftsmen;
                if (this.lastSortCache != null) {
                    data = this.lastSortCache;
                }
                if (filterKey) {
                    data = data.filter(craftsman => craftsman.name.toLowerCase().indexOf(filterKey) > -1 || craftsman.trade.toLowerCase().indexOf(filterKey) > -1);
                }
                if (sortKey) {
                    data = data.sort(function (a, b) {
                        a = a[sortKey];
                        b = b[sortKey];

                        if (sortKey === "nextResponseLimit" || sortKey === "lastEmailSent" || sortKey === "lastOnlineVisit") {
                            if (a === null && b !== null) {
                                return 1;
                            } else if (a !== null && b === null) {
                                return -1;
                            }
                        }

                        let currentOrder = order;
                        if (sortKey === "notRespondedIssuesCount" || sortKey === "notReadIssuesCount") {
                            currentOrder *= -1;
                        }
                        return (a === b ? 0 : a > b ? 1 : -1) * currentOrder;
                    })
                }
                if (filterKey === null) {
                    this.lastSortCache = data;
                } else {
                    this.lastSortCache = null;
                }
                return data;
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

<style>
    .filter-field {
        max-width: 400px;
    }
</style>