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
                    <sortable-header @do-sort="sortBy('number')"
                                     :sort-state="sortKey === 'number' ? sortOrders[sortKey] : 0">
                        #
                    </sortable-header>
                    <sortable-header @do-sort="sortBy('isMarked')"
                                     :sort-state="sortKey === 'isMarked' ? sortOrders[sortKey] : 0"/>
                    <th></th>

                    <sortable-header @do-sort="sortBy('description')"
                                     :sort-state="sortKey === 'description' ? sortOrders[sortKey] : 0">
                        {{ $t("issue.description")}}
                    </sortable-header>

                    <sortable-header @do-sort="sortBy('craftsman')"
                                     :sort-state="sortKey === 'craftsman' ? sortOrders[sortKey] : 0">
                        {{ $t("issue.craftsman")}}
                    </sortable-header>

                    <sortable-header @do-sort="sortBy('responseLimit')"
                                     :sort-state="sortKey === 'responseLimit' ? sortOrders[sortKey] : 0">
                        {{ $t("issue.response_limit")}}
                    </sortable-header>

                    <sortable-header @do-sort="sortBy('map')" :sort-state="sortKey === 'map' ? sortOrders[sortKey] : 0">
                        {{ $t("issue.map")}}
                    </sortable-header>

                </tr>
                </thead>
                <tbody>
                <tr v-for="issue in sortedIssues"
                    @click.ctrl.exact="issueCtrlClicked(issue)"
                    @click.exact="issueClicked(issue)"
                    @click.shift.exact="issueShiftClicked(issue)"
                    class="selectable"
                    v-bind:class="{ 'table-active': issue in selectedIssues}">

                    <td class="minimal-width">
                        {{issue.number}}
                    </td>
                    <td class="minimal-width clickable" @click.prevent.stop="markIssue(issue)">
                        <font-awesome-icon v-if="issue.isMarked" :icon="['fas', 'star']"/>
                        <font-awesome-icon v-else :icon="['fal', 'star']"/>
                    </td>
                    <td class="minimal-width">
                        <img class="lightbox-thumbnail" :src="issue.imageThumbnail">
                    </td>
                    <td>
                        {{issue.description}}
                    </td>
                    <td>
                        <craftsman-cell v-on:confirm-edit="cellEdited" v-on:abort-edit="" :issue="issue"
                                        :craftsmen="craftsmen" :edit-enabled="false"></craftsman-cell>
                    </td>
                    <td>
                        {{ formatLimitDateTime(issue.responseLimit)}}
                    </td>
                    <td>
                        {{issue.map}}
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
        <div v-else-if="!isMounting">
            <p>{{ $t("no_issues") }}</p>
        </div>
        <div v-else>
            <atom-spinner
                    :animation-duration="1000"
                    :size="60"
                    :color="'#ff1d5e'"
            />
        </div>
    </div>
</template>

<script>
    import axios from "axios"
    import moment from "moment";
    import Datepicker from 'vuejs-datepicker';
    import {de} from 'vuejs-datepicker/dist/locale'
    import {AtomSpinner} from 'epic-spinners'
    import notifications from '../mixins/Notifications'
    import CraftsmanCell from './CraftsmanCell'
    import SortableHeader from './SortableHeader'


    moment.locale('de');

    Array.prototype.unique = function () {
        return Array.from(new Set(this));
    };

    export default {
        props: {
            craftsmen: {
                type: Array,
                required: true
            },
            issues: {
                type: Array,
                required: true
            },
            isMounting: {
                type: Boolean,
                required: true
            }
        },
        mixins: [notifications],
        data: function () {
            const sortOrders = {};
            ["number", "isMarked", "description", "craftsman", "responseLimit", "map"].forEach(e => sortOrders[e] = 1);
            return {
                datePickerLocale: de,

                sortKey: "number",
                sortOrders: sortOrders,
                textFilter: null,

                selectedIssues: [],
                lastSelectedIssue: null,

                lightbox: {
                    enabled: false,
                    issue: null
                }
            }
        },
        components: {
            Datepicker,
            AtomSpinner,
            CraftsmanCell,
            SortableHeader
        },
        methods: {
            issueClicked: function (issue) {
                //reset selection
                this.lastSelectedIssue = issue;
                this.selectedIssues = [issue];
            },
            issueCtrlClicked: function (issue) {
                if (issue in this.selectedIssues) {
                    this.selectedIssues = this.selectedIssues.filter(i => i !== issue);
                } else {
                    this.selectedIssues.push(issue);
                }
            },
            issueShiftClicked: function (issue) {
                //if none selected; select the one pressed
                if (this.lastSelectedIssue === null) {
                    this.issueClicked(issue);
                    return;
                }

                //check indexes
                const index1 = this.issues.indexOf(this.lastSelectedIssue);
                const index2 = this.issues.indexOf(issue);

                //mark if both are valid
                if (index1 >= 0 && index2 >= 0) {
                    this.selectedIssues = this.issues.slice(min(index1, index2), max(index1, index2));
                }
            },
            cellEdited: function () {

            },
            cellEditAborted: function () {

            },
            formatLimitDateTime: function (value) {
                if (value === null) {
                    return this.$t("limit_not_set");
                }
                return moment(value).fromNow();
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
            selectAllEnabled: function () {
                return this.issues.filter(c => !c.selected).length > 0;
            },
            selectNoneEnabled: function () {
                return this.issues.filter(c => c.selected).length > 0;
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
                            if (a.craftsmanId in this.craftsmanById) {
                                a = this.craftsmanById[a.craftsmanId].trade + "_" + this.craftsmanById[a.craftsmanId].name
                            } else {
                                a = ""
                            }
                            if (b.craftsmanId in this.craftsmanById) {
                                b = this.craftsmanById[b.craftsmanId].trade + "_" + this.craftsmanById[b.craftsmanId].name
                            } else {
                                b = "";
                            }
                        } else if (sortKey === 'number') {
                            a = Number(a[sortKey]);
                            b = Number(b[sortKey]);
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
            },
            craftsmanById: function () {
                let res = [];
                this.craftsmen.forEach(c => res[c.id] = c);
                return res;
            }
        }
    }

</script>

<style>
    .filter-field {
        max-width: 400px;
    }

    .clickable {
        cursor: pointer;
    }

    input[type=checkbox] {
        transform: scale(1.4);
    }
</style>