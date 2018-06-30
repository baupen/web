<template>
    <div id="dispatch">
        <div v-if="craftsmen.length > 0" class="selectable-table">
            <input type="text" v-model="textFilter" title="search"/>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th class="minimal-width">
                        <input title="check" type="checkbox"
                               v-bind:indeterminate.prop="indeterminate"
                               v-bind:checked="selected"
                               v-on:click.prevent="selectAll()"/>
                    </th>
                    <th @click="sortBy('name')" :class="{ active: 'name' === sortKey }">
                        {{ $t("craftsman.name")}}
                        <font-awesome-icon v-if="sortKey === 'name'"
                                           :icon="sortOrders['name'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>

                    <th @click="sortBy('trade')" :class="{ active: 'trade' === sortKey }">
                        {{ $t("craftsman.trade")}}
                        <font-awesome-icon v-if="sortKey === 'trade'"
                                           :icon="sortOrders['trade'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>

                    <th>{{ $t("craftsman.not_read_issues_count")}}</th>
                    <th>{{ $t("craftsman.not_responded_issues_count")}}</th>
                    <th>{{ $t("craftsman.next_response_limit")}}</th>
                    <th>{{ $t("craftsman.last_email_sent")}}</th>
                    <th>{{ $t("craftsman.last_online_visit")}}</th>
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
            ["name", "trade"].forEach(e => sortOrders[e] = 1);
            return {
                craftsmen: [],
                isLoading: true,
                constructionSiteId: null,
                sortKey: "name",
                sortOrders: sortOrders,
                textFilter: null
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
                    this.craftsmen.filter(c => c.selected).forEach(c => {
                        c.lastEmailSent = (new Date()).toISOString();
                        c.selected = false;
                    });
                });
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
                if (!(key in this.sortOrders)) {
                    this.sortOrders[key] = 1;
                }

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
                if (filterKey) {
                    data = data.filter(craftsman => craftsman.name.toLowerCase().indexOf(filterKey) > -1);
                }
                if (sortKey) {
                    data = data.sort(function (a, b) {
                        a = a[sortKey];
                        b = b[sortKey];
                        return (a === b ? 0 : a > b ? 1 : -1) * order
                    })
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