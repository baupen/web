<template>
    <div id="foyer">
        <div v-if="issues.length > 0" class="selectable-table">
            <div v-if="lightbox.enabled" class="lightbox" @click="closeLightbox()">
                <div class="lightbox-content">
                    <img :src="lightbox.issue.imageFull"/>
                    <div class="file-upload-field">
                        <input class="form-control" @click.stop="" type="file" @change="processFile($event)"/>
                    </div>
                </div>
                <font-awesome-icon class="lightbox-close" :icon="['fal', 'times']"/>
            </div>
            <div class="filter-field">
                <div class="form-group">
                    <input class="form-control" id="filter" type="text" v-model="textFilter"
                           :placeholder="$t('table.filter_placeholder')"/>
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

                    <th></th>

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
                <tr v-for="issue in sortedIssues" v-on:click.prevent="selectIssue(issue)"
                    class="selectable"
                    v-bind:class="{ 'table-active': !onDelete && issue.selected && !issue.error, 'table-success': issue.number > 0, 'table-danger': onDelete && issue.selected, 'table-warning':  issue.error}">
                    <td class="minimal-width">
                        <span v-if="issue.number">#{{issue.number}}</span>
                        <input v-else title="check" type="checkbox" v-model="issue.selected"/>
                    </td>
                    <td class="minimal-width clickable" @click.prevent.stop="markIssue(issue)">
                        <font-awesome-icon v-if="issue.isMarked" :icon="['fas', 'star']"/>
                        <font-awesome-icon v-else :icon="['fal', 'star']"/>
                    </td>
                    <td class="minimal-width">
                        <img class="lightbox-thumbnail" @click="openLightbox(issue)" :src="issue.imageThumbnail">
                    </td>
                    <td>
                        <span v-if="editDescription === null || !issue.selected" class="editable"
                              @click.prevent.stop="startEditDescription(issue)">
                            {{issue.description}}
                        </span>
                        <div v-else>
                            <input :ref="'description-' + issue.id" class="form-control" type="text"
                                   v-model="editDescription"
                                   @click.prevent.stop="" @keyup.enter.prevent.stop="saveDescription"
                                   @keyup.escape.prevent.stop="abortDescription"/>

                            <button class="btn btn-secondary" @click="saveDescription">{{$t("actions.save")}}</button>
                        </div>
                    </td>
                    <td>
                        <span v-if="selectedTrade === null || !issue.selected" class="editable"
                              @click.prevent.stop="startEditCraftsman(issue)">
                            <span v-if="issue.craftsmanId == null">{{$t("issue.no_craftsman")}}</span>
                            <span v-else>
                            {{ issue.craftsmanTrade}}<br/>
                            {{ issue.craftsmanName}}
                                </span>
                        </span>
                        <div class="form-group" v-else>
                            <select class="form-control form-control-sm" v-model="selectedTrade"
                                    @click.prevent.stop="" @keyup.tab.prevent="saveCraftsmanTrade(issue)"
                                    @keyup.escape.prevent.stop="abortCraftsman"
                                    :ref="'trade-' + issue.id">
                                <option v-for="trade in trades" v-bind:value="trade">
                                    {{ trade }}
                                </option>
                            </select>
                            <template v-if="craftsmenByTrade.length > 1">
                                <select class="form-control form-control-sm" v-model="selectedCraftsman"
                                        @click.prevent.stop=""
                                        @keyup.escape.prevent.stop="abortCraftsman"
                                        :ref="'craftsman-' + issue.id">
                                    <option v-for="craftsman in craftsmenByTrade" v-bind:value="craftsman">
                                        {{ craftsman.name }}
                                    </option>
                                </select>
                            </template>
                            <template v-else>
                                <div class="form-control-preselected">
                                    <span>{{selectedCraftsman.name}}</span>
                                </div>
                            </template>
                            <button class="btn btn-secondary" @click="saveCraftsman">{{$t("actions.save")}}</button>
                        </div>
                    </td>
                    <td>
                        <span v-if="editResponseLimit === null || !issue.selected" class="editable"
                              @click.prevent.stop="startEditResponseLimit(issue)">
                            {{ formatLimitDateTime(issue.responseLimit)}}
                        </span>
                        <div v-else @click.prevent.stop="">
                            <datepicker :lang="de" format="dd.MM.yyyy" :ref="'response-limit-' + issue.id"
                                        v-model="editResponseLimit">
                            </datepicker>
                            <button class="btn btn-secondary" @click="saveResponseLimit">{{$t("actions.save")}}</button>
                            <button class="btn btn-secondary" @click="removeResponseLimit">{{$t("actions.remove")}}</button>
                        </div>
                    </td>
                    <td>
                        {{issue.map}}
                    </td>
                    <td class="minimal-width" :class="{'bg-primary text-white': issue.wasAddedWithClient}">
                        {{issue.uploadByName}} <br/>
                        <span class="small">{{ formatDateTime(issue.uploadedAt)}}</span>
                    </td>
                </tr>

                </tbody>
            </table>
            <div v-if="!onDelete">
                <button class="btn btn-primary"
                        v-bind:disabled="isLoading || issues.filter(i => i.selected).length === 0"
                        v-on:click.prevent="confirm()">
                    {{$t("actions.confirm_issues")}}
                </button>
                <button class="btn btn-outline-danger"
                        v-bind:disabled="isLoading || issues.filter(i => i.selected).length === 0"
                        v-on:click.prevent="remove()">
                    {{$t("actions.remove_all")}}
                </button>
            </div>
            <div v-else>
                <p class="text-danger">
                    {{$t("dialog.cant_undo_remove")}}
                </p>
                <button class="btn btn-danger"
                        v-on:click.prevent="removeConfirm()">
                    {{$t("actions.remove_all")}}
                </button>
                <button class="btn"
                        v-on:click.prevent="abortRemove()">
                    {{$t("actions.abort")}}
                </button>
            </div>
        </div>
        <div v-else-if="!isLoading">
            <p>{{ $t("table.no_entries") }}</p>
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";
    import Datepicker from 'vuejs-datepicker';
    import {de} from 'vuejs-datepicker/dist/locale'


    moment.locale('de');

    export default {
        data: function () {
            const sortOrders = {};
            ["isMarked", "description", "craftsman", "responseLimit", "map", "uploadByName"].forEach(e => sortOrders[e] = 1);
            return {
                de: de,
                issues: [],
                craftsmen: null,
                trades: [],
                isLoading: true,
                constructionSiteId: null,
                sortKey: "isMarked",
                sortOrders: sortOrders,
                textFilter: null,
                editDescription: null,
                selectedTrade: null,
                selectedCraftsman: null,
                editResponseLimit: null,
                lastSelectedIssue: null,
                lightbox: {
                    enabled: false,
                    issue: null
                },
                onDelete: false
            }
        },
        components: {
            Datepicker
        },
        watch: {
            selectedTrade: function () {
                this.changedTrade();
            }
        },
        methods: {
            processFile: function (event) {
                let data = new FormData();
                data.append('message', JSON.stringify({
                    "constructionSiteId": this.constructionSiteId,
                    "issueId": this.lightbox.issue.id
                }));
                data.append('file', event.target.files[0]);

                axios.post("/api/foyer/issue/image", data).then((response) => {
                    this.lightbox.issue.imageFull = response.data.issue.imageFull;
                    this.lightbox.issue.imageThumbnail = response.data.issue.imageThumbnail;
                });
            },
            openLightbox: function (issue) {
                this.lightbox.enabled = true;
                this.lightbox.issue = issue;
            },
            closeLightbox: function () {
                this.lightbox.enabled = false;
            },
            startEditDescription: function (issue) {
                if (!issue.selected) {
                    issue.selected = true;
                }
                this.editDescription = issue.description;

                this.$nextTick(() => {
                    let input = this.$refs["description-" + issue.id][0];
                    input.focus();
                });
            },
            saveDescription: function () {
                this.issues.filter(i => i.selected).forEach(i => i.description = this.editDescription);
                this.abortDescription();
                this.save();
            },
            abortDescription: function () {
                this.editDescription = null;
            },
            startEditResponseLimit: function (issue) {
                if (!issue.selected) {
                    issue.selected = true;
                }
                this.lastSelectedIssue = issue;
                if (issue.responseLimit !== null) {
                    this.editResponseLimit = Date.parse(issue.responseLimit);
                } else {
                    this.editResponseLimit = new Date();
                }

                this.$nextTick(() => {
                    let input = this.$refs["response-limit-" + issue.id][0];
                    //input.focus();
                });
            },
            saveResponseLimit: function () {
                //library destroys element if not set newly
                let responseLimit = this.editResponseLimit;
                if (typeof responseLimit.toISOString !== "function") {
                    if (this.lastSelectedIssue.responseLimit !== null) {
                        responseLimit = Date.parse(issue.responseLimit);
                    } else {
                        responseLimit = new Date();
                    }
                }
                this.issues.filter(i => i.selected).forEach(i => i.responseLimit = responseLimit.toISOString());
                this.save();
                this.abortResponseLimit();
            },
            removeResponseLimit: function () {
                this.issues.filter(i => i.selected).forEach(i => i.responseLimit = null);
                this.abortResponseLimit();
                this.save();
            },
            abortResponseLimit: function () {
                this.editResponseLimit = null;
            },
            changedTrade: function () {
                this.selectedCraftsman = this.craftsmenByTrade[0];
            },
            startEditCraftsman: function (issue) {
                if (!issue.selected) {
                    issue.selected = true;
                }

                if (issue.craftsmanId in this.craftsmanById) {
                    this.selectedTrade = this.craftsmanById[issue.craftsmanId].trade;
                } else {
                    this.selectedTrade = this.craftsmanById[this.craftsmen[0].id].trade;
                }

                this.$nextTick(() => {
                    let input = this.$refs["trade-" + issue.id][0];
                    input.focus();
                });
            },
            saveCraftsmanTrade: function (issue) {
                this.$nextTick(() => {
                    let input = this.$refs["craftsman-" + issue.id][0];
                    input.focus();
                });
            },
            abortCraftsman: function () {
                this.selectedTrade = null;
                this.selectedCraftsman = null;
            },
            saveCraftsman: function () {
                this.issues.filter(i => i.selected).forEach(i => i.craftsmanId = this.selectedCraftsman.id);
                this.abortCraftsman();
                this.save();
            },
            selectIssue: function (issue) {
                issue.selected = !issue.selected;
                if (this.issues.filter(i => i.selected).length === 0) {
                    this.abortDescription();
                    this.abortResponseLimit();
                    this.abortCraftsman();
                }
            },
            markIssue: function (issue) {
                issue.isMarked = !issue.isMarked;
                this.save();
            },
            confirm: function () {
                this.isLoading = true;
                let errorIssues = this.issues.filter(c => c.selected && (c.description.length === 0 || c.craftsmanId === null));
                if (errorIssues.length > 0) {
                    this.displayWarningFlash(this.$t("messages.danger.confirm_issues_impossible"));
                    errorIssues.forEach(i => i.error = true);
                    window.setTimeout(e => errorIssues.forEach(i => i.error = false), 3000);
                    return;
                }

                axios.post("/api/foyer/issue/confirm", {
                    "constructionSiteId": this.constructionSiteId,
                    "issueIds": this.issues.filter(c => c.selected).map(c => c.id)
                }).then((response) => {
                    this.isLoading = false;
                    console.log("done");
                    console.log(response);
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

                    console.log("here1");
                    this.displayInfoFlash(this.$t("messages.success.added_to_register"));
                    window.setTimeout(e => this.issues = this.issues.filter(i => i.number === null), 3000);
                });
            },
            remove: function () {
                this.onDelete = true;
            },
            abortRemove: function () {
                this.onDelete = false;
            },
            removeConfirm: function () {
                this.isLoading = true;
                axios.post("/api/foyer/issue/delete", {
                    "constructionSiteId": this.constructionSiteId,
                    "issueIds": this.issues.filter(c => c.selected).map(c => c.id)
                }).then((response) => {
                    this.isLoading = false;

                    let idLookup = [];
                    response.data.deletedIssues.forEach(i => idLookup[i.id] = true);
                    this.issues = this.issues.filter(i => !(i.id in idLookup));
                    this.abortRemove();
                    this.displayInfoFlash(this.$t("messages.success.removed_entries"));
                });
            },
            save: function () {
                this.isLoading = true;
                axios.post("/api/foyer/issue/update", {
                    "constructionSiteId": this.constructionSiteId,
                    "updateIssues": this.issues.filter(c => c.selected)
                }).then((response) => {
                    this.isLoading = false;
                    const activeIssues = this.issues.filter(c => c.selected);
                    response.data.issues.forEach(c => {
                        let match = activeIssues.filter(i => i.id === c.id);
                        if (match.length === 1) {
                            match[0].description = c.description;
                            match[0].craftsmanId = c.craftsmanId;
                            match[0].responseLimit = c.responseLimit;
                            match[0].imageFull = c.imageFull;
                            match[0].imageThumbnail = c.imageThumbnail;
                        }
                    });

                    this.refreshComputedIssueProperties();
                    this.displayInfoFlash(this.$t("messages.success.saved_changes"));
                });
            },
            displayInfoFlash: function (content) {
                this.displayFlash(content, "success");
            },
            displayErrorFlash: function (content) {
                this.displayFlash(content, "danger");
            },
            displayWarningFlash: function (content) {
                this.displayFlash(content, "warning");
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
            formatLimitDateTime: function (value) {
                if (value === null) {
                    return this.$t("issue.no_response_limit");
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
                if (issue.craftsmanId in this.craftsmanById) {
                    return this.craftsmanById[issue.craftsmanId].trade;
                }
                return "-";
            },
            craftsmanName: function (issue) {
                if (issue.craftsmanId in this.craftsmanById) {
                    return this.craftsmanById[issue.craftsmanId].name;
                }
                return "-";
            },
            refreshComputedIssueProperties: function () {
                if (this.issues !== null && this.craftsmen !== null) {
                    this.issues.filter(i => i.craftsmanId in this.craftsmanById).forEach(i => {
                        const craftsman = this.craftsmanById[i.craftsmanId];
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
            craftsmenByTrade: function () {
                return this.craftsmen.filter(c => c.trade === this.selectedTrade);
            },
            craftsmanById: function () {
                let res = [];
                this.craftsmen.forEach(c => res[c.id] = c);
                return res;
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
                            a = this.craftsmanTrade(a) + this.craftsmanName(a);
                            b = this.craftsmanTrade(b) + this.craftsmanName(b);
                        } else {
                            a = a[sortKey];
                            b = b[sortKey];
                        }

                        let currentOrder = order;
                        if (sortKey === 'isMarked') {
                            currentOrder *= -1;
                        }
                        return (a === b ? 0 : a > b ? 1 : -1) * currentOrder;
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
                    this.displayErrorFlash(this.$t("messages.danger.unrecoverable") + " (" + error.response.data.message + ")");
                    console.log("request failed");
                    console.log(error.response.data);
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
                        i.error = false;
                    });
                    this.issues = response.data.issues;
                    this.isLoading = false;
                    this.refreshComputedIssueProperties();
                });

                axios.post("/api/foyer/craftsman/list", {
                    "constructionSiteId": this.constructionSiteId
                }).then((response) => {
                    this.craftsmen = response.data.craftsmen;
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

    .clickable {
        cursor: pointer;
    }

    .file-upload-field > .form-control {
        width: 100%;
        padding: 1rem;
        margin: 0.5rem 0;
    }

    input[type=checkbox] {
        transform: scale(1.4);
    }

    .form-control-preselected {
        padding: 0.5rem;
    }
</style>