<template>
    <div id="dispatch">
        <div v-if="craftsmen.length > 0" class="selectable-table">
            <div class="filter-field">
                <div class="form-group">
                    <input class="form-control" id="filter" type="text" v-model="textFilter"
                           :placeholder="$t('table.filter_placeholder')"/>
                </div>
            </div>
            <table class="table table-hover">
                <thead>
                <tr>
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
                <tr v-for="craftsman in sortedCraftsmen"
                    @click.ctrl.exact="rowCtrlClicked(craftsman)"
                    @click.meta.exact="rowCtrlClicked(craftsman)"
                    @click.exact="rowClicked(craftsman)"
                    @click.shift.exact.prevent.stop="rowShiftClicked(craftsman)"
                    :class="rowClass(craftsman)">
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
            <button v-if="selectedCraftsmen.length === 0" class="btn btn-primary" :disabled="isLoading" v-on:click.prevent="sendEmails()">
                {{$t("actions.send_emails_to_all")}}
            </button>
            <button v-else class="btn btn-primary" :disabled="isLoading" @click.prevent="sendEmails()">
                {{$t("actions.send_emails_to_selected")}}
            </button>
        </div>
        <div v-else-if="!isLoading">
            <p>{{ $t("table.no_entries") }}</p>
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import notifications from "../mixins/Notifications"
    import moment from "moment";

    export default {
        data: function () {
            const sortOrders = {};
            ["name", "trade", "notReadIssuesCount", "notRespondedIssuesCount", "nextResponseLimit", "lastEmailSent", "lastOnlineVisit"].forEach(e => sortOrders[e] = 1);
            return {
                craftsmen: [],
                selectedCraftsmen: [],
                lastSelectedCraftsman: null,
                isLoading: true,
                constructionSiteId: null,
                sortKey: "name",
                sortOrders: sortOrders,
                textFilter: null,
                lastSortCache: null
            }
        },
        mixins: [notifications],
        methods: {
            rowClicked: function (craftsman) {
                if (this.selectedCraftsmen.length === 1 && this.selectedCraftsmen[0] === craftsman) {
                    this.selectedCraftsmen = [];
                    return;
                }

                //reset selection
                this.lastSelectedCraftsman = craftsman;
                this.selectedCraftsmen = [craftsman];
            },
            rowCtrlClicked: function (craftsman) {
                if (this.selectedCraftsmen.indexOf(craftsman) >= 0) {
                    this.selectedCraftsmen = this.selectedCraftsmen.filter(i => i !== craftsman);
                } else {
                    this.selectedCraftsmen.push(craftsman);
                }

                //remove all selections
                document.getSelection().removeAllRanges();
            },
            rowShiftClicked: function (craftsman) {
                //if none selected; select the one pressed
                if (this.lastSelectedCraftsman === null) {
                    this.rowClicked(craftsman);
                    return;
                }

                //remove all selections
                document.getSelection().removeAllRanges();

                //check indexes
                const index1 = this.craftsmen.indexOf(this.lastSelectedCraftsman);
                const index2 = this.craftsmen.indexOf(craftsman);

                //mark if both are valid
                if (index1 >= 0 && index2 >= 0) {
                    this.selectedCraftsmen = this.craftsmen.slice(Math.min(index1, index2), Math.max(index1, index2) + 1);
                }
            },
            sendEmails: function () {
                const recipients = this.selectedCraftsmen.length === 0 ? this.craftsmen : this.selectedCraftsmen;

                this.isLoading = true;
                axios.post("/api/dispatch", {
                    "constructionSiteId": this.constructionSiteId,
                    "craftsmanIds": recipients.map(c => c.id)
                }).then((response) => {
                    this.isLoading = false;
                    this.selectedCraftsmen.forEach(c => {
                        if (response.data.successfulIds.includes(c.id)) {
                            c.lastEmailSent = (new Date()).toISOString();
                        }
                    });

                    this.selectedCraftsmen = [];

                    this.displayInfoFlash(this.$t("messages.emails_sent"));

                    const skipped = response.data.skippedIds.length;
                    if (skipped > 0) {
                        this.displayWarningFlash(this.$t("messages.skipped_emails", {count: skipped}));
                    }
                });
            },
            formatDateTime: function (value) {
                if (value === null) {
                    return "-"
                }
                return moment(value).locale(document.documentElement.lang.substr(0, 2)).fromNow();
            },
            sortBy: function (key) {
                if (this.sortKey === key) {
                    this.sortOrders[key] *= -1;
                } else {
                    this.sortKey = key;
                }
            },
            rowClass: function(craftsman) {
                return this.selectedCraftsmen.indexOf(craftsman) >= 0 ? "table-active" : "";
            }
        },
        computed: {
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
