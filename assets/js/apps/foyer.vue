<template>
    <div id="foyer">
        <div v-if="issues.length > 0" class="selectable-table">
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

                    <th class="sortable" @click="sortBy('isMarked')" :class="{ active: 'isMarked' === sortKey }">

                        <font-awesome-icon v-if="sortKey === 'isMarked'"
                                           :icon="sortOrders['isMarked'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>

                    <th class="sortable" @click="sortBy('description')" :class="{ active: 'description' === sortKey }">
                        {{ $t("issue.description")}}
                        <font-awesome-icon v-if="sortKey === 'description'"
                                           :icon="sortOrders['description'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>

                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="issue in sortedIssues" v-on:click.prevent="issue.selected = !issue.selected"
                    v-bind:class="{ 'table-active': issue.selected, 'table-success': issue.number > 0 }">
                    <td class="minimal-width">
                        <span v-if="issue.number">#{{issue.number}}</span>
                        <input v-else title="check" type="checkbox" v-model="issue.selected"/>
                    </td>
                    <td>
                        <font-awesome-icon v-if="issue.isMarked" :icon="['fas', 'star']"/>
                        <font-awesome-icon v-else :icon="['fal', 'star']"/>
                    </td>
                    <td>
                        {{issue.description}}
                    </td>
                    <td>
                        {{issue.map}}
                    </td>
                    <td>
                        {{issue.uploadedByName}}
                    </td>
                    <td>
                        {{ issue.craftsmanName}}
                    </td>
                    <td>
                        {{ issue.craftsmanTrade}}
                    </td>
                    <td>
                        {{ formatDateTime(issue.responseLimit)}}
                    </td>
                    <td>
                        {{ formatDateTime(issue.uploadedAt)}}
                    </td>
                </tr>

                </tbody>
            </table>
            <button class="btn btn-primary" v-bind:disabled="isLoading" v-on:click.prevent="confirm()">
                {{$t("confirm_issues")}}
            </button>
        </div>
        <div v-else-if="!isLoading">
            <p>{{ $t("no_issues") }}</p>
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";

    moment.locale('de');

    Array.prototype.unique = function () {
        return Array.from(new Set(this));
    };

    export default {
        data: function () {
            const sortOrders = {};
            ["isMarked"].forEach(e => sortOrders[e] = 1);
            return {
                issues: [],
                craftsmen: [],
                craftsmenLookup: {},
                trades: [],
                isLoading: true,
                constructionSiteId: null,
                sortKey: "isMarked",
                sortOrders: sortOrders,
                textFilter: null
            }
        },
        methods: {
            confirm: function () {
                this.isLoading = true;
                axios.post("/api/foyer/issue/confirm", {
                    "constructionSiteId": this.constructionSiteId,
                    "issueIds": this.issues.filter(c => c.selected).map(c => c.id)
                }).then((response) => {
                    this.isLoading = false;
                    let issueNumberLookup = [];
                    response.data.numberIssues.forEach(i => {
                        issueNumberLookup[i.id] = i.number;
                    });
                    this.issues.filter(c => c.selected).forEach(c => {
                        if (c.id in issueNumberLookup) {
                            c.number = issueNumberLookup[c.id];
                        }
                        c.selected = false;
                    });

                    this.displayInfoFlash(this.$t("added_to_register"));
                    window.setTimeout(e => this.issues = this.issues.filter(i => i.number === null), 3000);
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
                if (!(key in this.sortOrders)) {
                    this.sortOrders[key] = 1;
                }

                if (this.sortKey === key) {
                    this.sortOrders[key] *= -1;
                } else {
                    this.sortKey = key;
                }
            },
            craftsmanTrade: function (issue) {
                if (issue.craftsmanId in this.craftsmenLookup) {
                    return this.craftsmenLookup[issue.craftsmanId].trade;
                }
                return "-";
            },
            craftsmanName: function (issue) {
                if (issue.craftsmanId in this.craftsmenLookup) {
                    return this.craftsmenLookup[issue.craftsmanId].name;
                }
                return "-";
            },
            refreshComputedIssueProperties: function () {
                if (this.issues.length > 0 && this.craftsmenLookup.length > 0) {
                    this.issues.filter(i => i.craftsmanId in this.craftsmenLookup).forEach(i => {
                        const craftsman = this.craftsmenLookup[i.craftsmanId];
                        i.craftsmanName = craftsman.name;
                        i.craftsmanTrade = craftsman.trade;
                    })
                }
            }
        },
        computed: {
            indeterminate: function () {
                return !this.selected && this.issues.filter(c => c.selected).length > 0;
            },
            selected: function () {
                return this.issues.filter(c => !c.selected).length === 0;
            },
            sortedIssues: function () {
                const sortKey = this.sortKey;
                const filterKey = this.textFilter && this.textFilter.toLowerCase();
                const order = this.sortOrders[sortKey];
                let data = this.issues;
                if (filterKey) {
                    data = data.filter(issues => issues.description.toLowerCase().indexOf(filterKey) > -1);
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
                    console.log(error.response.data);
                    this.displayErrorFlash(this.$t("error") + " (" + error.response.data.message + ")");
                    return Promise.reject(error);
                }
            );
            axios.get("/api/configuration").then((response) => {
                this.constructionSiteId = response.data.constructionSite.id;

                axios.post("/api/foyer/issue/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    response.data.issues.forEach(i => {
                        i.selected = false;
                        i.number = null;
                    });
                    this.issues = response.data.issues;
                    this.isLoading = false;
                    this.refreshComputedIssueProperties();
                });

                axios.post("/api/foyer/craftsman/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.craftsmen = response.data.craftsmen;
                    this.craftsmen.forEach(c => this.craftsmenLookup[c.id] = c);
                    this.trades = response.data.craftsmen.map(a => a.trade).unique();
                    this.refreshComputedIssueProperties();
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