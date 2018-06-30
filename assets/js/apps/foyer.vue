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

                    <th class="sortable" @click="sortBy('craftsman')" :class="{ active: 'craftsman' === sortKey }">
                        {{ $t("issue.craftsman")}}
                        <font-awesome-icon v-if="sortKey === 'craftsman'"
                                           :icon="sortOrders['craftsman'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>

                    <th class="sortable" @click="sortBy('responseLimit')"
                        :class="{ active: 'responseLimit' === sortKey }">
                        {{ $t("issue.response_limit")}}
                        <font-awesome-icon v-if="sortKey === 'responseLimit'"
                                           :icon="sortOrders['responseLimit'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>

                    <th class="sortable" @click="sortBy('map')" :class="{ active: 'map' === sortKey }">
                        {{ $t("issue.map")}}
                        <font-awesome-icon v-if="sortKey === 'map'"
                                           :icon="sortOrders['map'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>

                    <th class="sortable" @click="sortBy('uploadByName')"
                        :class="{ active: 'uploadByName' === sortKey }">

                        <font-awesome-icon v-if="sortKey === 'uploadByName'"
                                           :icon="sortOrders['uploadByName'] > 0 ? 'sort-up' : 'sort-down'"/>
                        <font-awesome-icon v-else :icon="['fal', 'sort']"/>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="issue in sortedIssues" v-on:click.prevent="issue.selected = !issue.selected"
                    v-bind:class="{ 'table-active': issue.selected, 'table-success': issue.number > 0 }">
                    <td class="minimal-width">
                        <span v-if="issue.number">#{{issue.number}}</span>
                        <input v-else title="check" type="checkbox" v-model="issue.selected"/>
                    </td>
                    <td class="minimal-width">
                        <font-awesome-icon v-if="issue.isMarked" :icon="['fas', 'star']"/>
                        <font-awesome-icon v-else :icon="['fal', 'star']"/>
                    </td>
                    <td>
                        <span v-if="editDescription === null || !issue.selected" class="editable" @click.prevent.stop="startEditDescription(issue)">
                            {{issue.description}}
                        </span>
                        <input class="form-control" v-else type="text" v-model="editDescription" @click.prevent.stop="" @keyup.enter.prevent.stop="saveDescription"
                               @keyup.escape.prevent.stop="abortDescription"/>
                    </td>
                    <td>
                        <span class="editable">
                            {{ issue.craftsmanName}} <br/>
                            {{ issue.craftsmanTrade}}
                        </span>
                    </td>
                    <td>
                        {{ formatDateTime(issue.responseLimit)}}
                    </td>
                    <td>
                        {{issue.map}}
                    </td>
                    <td class="minimal-width">
                        {{issue.uploadByName}} <br/>
                        <span class="small">{{ formatDateTime(issue.uploadedAt)}}</span>
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
            ["isMarked", "description", "craftsman", "responseLimit", "map", "uploadByName"].forEach(e => sortOrders[e] = 1);
            return {
                issues: [],
                craftsmen: null,
                craftsmenLookup: null,
                trades: [],
                isLoading: true,
                constructionSiteId: null,
                sortKey: "isMarked",
                sortOrders: sortOrders,
                textFilter: null,
                editDescription: null
            }
        },
        methods: {
            startEditDescription: function (issue) {
                if (!issue.selected) {
                    issue.selected = true;
                } else {
                    this.editDescription = issue.description;
                }
            },
            saveDescription: function () {
                this.issues.filter(i => i.selected).forEach(i => i.description = this.editDescription);
                this.editDescription = null;
                this.save();
            },
            abortDescription: function () {
                this.editDescription = null;
            },
            confirm: function () {
                this.isLoading = true;
                axios.post("/api/foyer/issue/confirm", {
                    "constructionSiteId": this.constructionSiteId,
                    "issueIds": this.issues.filter(c => c.selected).map(c => c.id)
                }).then((response) => {
                    this.isLoading = false;
                    let issueNumberLookup = [];
                    response.data.issues.forEach(i => {
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
            save: function () {
                this.isLoading = true;
                axios.post("/api/foyer/issue/update", {
                    "constructionSiteId": this.constructionSiteId,
                    "issues": this.issues.filter(c => c.selected)
                }).then((response) => {
                    this.isLoading = false;
                    const activeIssues = this.issues.filter(c => c.selected);
                    response.data.issues.forEach(c => {
                        let match = activeIssues.filter(i => i.id === c.id);
                        if (match.length === 1) {
                            match[0].description = c.description;
                            match[0].craftsmanId = c.craftsmanId;
                            match[0].responseLimit = c.responseLimit;
                        }
                        console.log("found: " + match.length);
                    });

                    this.displayInfoFlash(this.$t("saved"));
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
                this.issues.forEach(c => c.selected = newVal);
            },
            sortBy: function (key) {
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
                if (this.issues !== null && this.craftsmenLookup !== null) {
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
                    data = data.sort((a, b) => {
                        if (sortKey === 'craftsman') {
                            a = this.craftsmanName(a) + this.craftsmanTrade(a);
                            b = this.craftsmanName(b) + this.craftsmanTrade(b);
                        } else {
                            a = a[sortKey];
                            b = b[sortKey];
                        }
                        return (a === b ? 0 : a > b ? 1 : -1) * order;
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
                        i.craftsmanName = null;
                        i.craftsmanTrade = null;
                    });
                    this.issues = response.data.issues;
                    this.isLoading = false;
                    this.refreshComputedIssueProperties();
                });

                axios.post("/api/foyer/craftsman/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.craftsmen = response.data.craftsmen;

                    const newLookup = [];
                    this.craftsmen.forEach(c => newLookup[c.id] = c);
                    this.craftsmenLookup = newLookup;

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

    .editable {
        display: inline-block;
        border: 1px solid rgba(0, 0, 0, 0)
    }

    .editable:hover {
        border: 1px solid
    }
</style>