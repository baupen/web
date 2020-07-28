<template>
    <div>
        <lightbox :open="lightbox.enabled" :imageSrc="lightbox.imageFull" @close="lightbox.enabled = false"/>
        <div class="selectable-table">
            <div class="row">
                <div class="col-9">
                    <div class="filter-field">
                        <div class="form-group">
                            <input class="form-control" id="filter" type="text" v-model="filter.onlyWithText"
                                   :placeholder="$t('table.filter_placeholder')"/>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <p class="text-right mb-0 mt-4 text-secondary">{{allFilteredIssuesLength}} {{$t("search.issues")}}</p>
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

                    <sortable-header @do-sort="sortBy('wasAddedWithClient')"
                                     :sort-state="sortKey === 'wasAddedWithClient' ? sortOrders[sortKey] : 0"/>

                    <th></th>

                    <sortable-header @do-sort="sortBy('description')"
                                     :sort-state="sortKey === 'description' ? sortOrders[sortKey] : 0">
                        {{ $t("issue.description")}}
                    </sortable-header>

                    <sortable-header @do-sort="sortBy('craftsmanId')"
                                     :sort-state="sortKey === 'craftsmanId' ? sortOrders[sortKey] : 0">
                        {{ $t("issue.craftsman")}}
                    </sortable-header>

                    <sortable-header @do-sort="sortBy('responseLimit')"
                                     :sort-state="sortKey === 'responseLimit' ? sortOrders[sortKey] : 0">
                        {{ $t("issue.response_limit")}}
                    </sortable-header>

                    <sortable-header @do-sort="sortBy('map')" :sort-state="sortKey === 'map' ? sortOrders[sortKey] : 0">
                        {{ $t("issue.map")}}
                    </sortable-header>

                    <sortable-header @do-sort="sortBy('status')"
                                     :sort-state="sortKey === 'status' ? sortOrders[sortKey] : 0">
                        {{ $t("issue.status")}}
                    </sortable-header>
                </tr>
                </thead>
                <tbody>
                <tr v-if="issues.length === 0">
                    <td colspan="7">{{ $t("table.no_entries") }}</td>
                </tr>
                <tr v-for="issue in sortedIssues"
                    @click.ctrl.exact="issueCtrlClicked(issue)"
                    @click.meta.exact="issueCtrlClicked(issue)"
                    @click.exact="issueClicked(issue)"
                    @click.shift.exact.prevent.stop="issueShiftClicked(issue)"
                    class="selectable"
                    :class="rowClass(issue)">

                    <td class="minimal-width">
                        {{issue.number}}
                    </td>
                    <td class="minimal-width">
                        <marked-cell
                                @edit-confirm="cellEditConfirm('isMarked', issue)"
                                @edit-start="cellEditStart('isMarked', issue)"
                                :issue="issue"
                        />
                    </td>
                    <td class="minimal-width">
                        <was-added-with-client-cell
                                @edit-confirm="cellEditConfirm('wasAddedWithClient', issue)"
                                @edit-start="cellEditStart('wasAddedWithClient', issue)"
                                :issue="issue"
                        />
                    </td>
                    <td class="minimal-width">
                        <img class="lightbox-thumbnail" @click.prevent.stop="openLightbox(issue.imageFull)"
                             :src="issue.imageThumbnail">
                    </td>
                    <td>
                        <description-cell @edit-confirm="cellEditConfirm('description', issue)"
                                          @edit-abort="cellEditAbort('description')"
                                          @edit-start="cellEditStart('description', issue)"
                                          :issue="issue"
                                          :edit-enabled="editIssue === issue && editEnabled.description">
                            <template slot="save-button-content">
                                <save-button :multiple="selectedIssues.length > 1"/>
                            </template>
                        </description-cell>
                    </td>
                    <td>
                        <craftsman-cell @edit-confirm="cellEditConfirm('craftsmanId', issue)"
                                        @edit-abort="cellEditAbort('craftsmanId')"
                                        @edit-start="cellEditStart('craftsmanId', issue)"
                                        :issue="issue"
                                        :craftsmen="craftsmen"
                                        :edit-enabled="editIssue === issue && editEnabled.craftsmanId">
                            <template slot="save-button-content">
                                <save-button :multiple="selectedIssues.length > 1"/>
                            </template>
                        </craftsman-cell>
                    </td>
                    <td>
                        <response-limit-cell @edit-confirm="cellEditConfirm('responseLimit', issue)"
                                             @edit-abort="cellEditAbort('responseLimit')"
                                             @edit-start="cellEditStart('responseLimit', issue)"
                                             :issue="issue"
                                             :edit-enabled="editIssue === issue && editEnabled.responseLimit">
                            <template slot="save-button-content">
                                <save-button :multiple="selectedIssues.length > 1"/>
                            </template>
                        </response-limit-cell>
                    </td>
                    <td>
                        {{issue.map}}
                    </td>
                    <td class="minimal-width status-column">
                        <status-cell @edit-confirm="cellEditConfirm('status', issue, arguments)"
                                     @edit-abort="cellEditAbort('status')"
                                     @edit-start="cellEditStart('status', issue)"
                                     :issue="issue"
                                     :edit-enabled="editIssue === issue && editEnabled.status">
                            <template slot="save-button-content">
                                <save-button :multiple="selectedIssues.length > 1"/>
                            </template>
                        </status-cell>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>


<style>
    .added-with-client {
        background-color: #e6e6f9!important;
    }

    .added-with-client:hover {
        background-color: #d3d3e7 !important;
    }

    .added-with-client-selected,
    .added-with-client-selected:hover {
        background-color: #c6c6d9!important;
    }
</style>

<script>
    import CraftsmanCell from './components/CraftsmanCell'
    import DescriptionCell from './components/DescriptionCell'
    import ResponseLimitCell from './components/ResponseLimitCell'
    import SortableHeader from './components/SortableHeader'
    import SaveButton from './components/SaveButton'
    import StatusCell from './components/StatusCell'
    import Lightbox from './Lightbox'
    import MarkedCell from "./components/MarkedCell";
    import WasAddedWithClientCell from "./components/WasAddedWithClientCell";

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
            allFilteredIssuesLength: {
                type: Number,
                required: true
            },
            filter: {
                type: Object,
                required: true
            }
        },
        data: function () {
            const sortOrders = {};
            ["number", "wasAddedWithClient", "isMarked", "description", "craftsmanId", "responseLimit", "map", "status"].forEach(e => sortOrders[e] = 1);
            return {
                sortKey: "number",
                sortOrders: sortOrders,

                editIssue: null,
                selectedIssues: [],
                lastSelectedIssue: null,

                lightbox: {
                    enabled: false,
                    imageFull: null
                },

                editEnabled: {
                    craftsmanId: false,
                    description: false,
                    responseLimit: false,
                    status: false
                }
            }
        },
        components: {
            WasAddedWithClientCell,
            MarkedCell,
            ResponseLimitCell,
            CraftsmanCell,
            SortableHeader,
            SaveButton,
            DescriptionCell,
            StatusCell,
            Lightbox
        },
        methods: {
            issueClicked: function (issue) {
                if (this.selectedIssues.length === 1 && this.selectedIssues[0] === issue) {
                    this.selectedIssues = [];
                    return;
                }

                //reset selection
                this.lastSelectedIssue = issue;
                this.selectedIssues = [issue];

                //reset edit
                this.editIssue = null;
            },
            issueCtrlClicked: function (issue) {
                if (this.selectedIssues.indexOf(issue) >= 0) {
                    this.selectedIssues = this.selectedIssues.filter(i => i !== issue);
                } else {
                    this.selectedIssues.push(issue);
                }

                //remove all selections
                document.getSelection().removeAllRanges();
            },
            issueShiftClicked: function (issue) {
                //if none selected; select the one pressed
                if (this.lastSelectedIssue === null) {
                    this.issueClicked(issue);
                    return;
                }

                //remove all selections
                document.getSelection().removeAllRanges();

                //check indexes
                const index1 = this.issues.indexOf(this.lastSelectedIssue);
                const index2 = this.issues.indexOf(issue);

                //mark if both are valid
                if (index1 >= 0 && index2 >= 0) {
                    this.selectedIssues = this.issues.slice(Math.min(index1, index2), Math.max(index1, index2) + 1);
                }
            },
            rowClass: function(issue) {
                if (issue.wasAddedWithClient) {
                    return "added-with-client" + (this.selectedIssues.indexOf(issue) >= 0 ? "-selected" : "");
                }
                return this.selectedIssues.indexOf(issue) >= 0 ? "table-active" : "";
            },
            cellEditStart: function (cell, issue) {
                //select issue if not done already
                if (this.selectedIssues.indexOf(issue) === -1) {
                    this.issueClicked(issue);
                }

                //stop other edits
                Object.keys(this.editEnabled).filter(key => this.editEnabled[key]).forEach(key => {
                    this.cellEditAbort(key);
                });

                //start edit
                this.editEnabled[cell] = true;
                this.editIssue = issue;
            },
            cellEditConfirm: function (cell, issue, args = null) {
                if (this.selectedIssues.length === 0) {
                  this.selectedIssues.push(issue);
                }

                if (cell === "status") {
                    this.$emit('update-status', this.selectedIssues, args[0], args[1]);
                }
                else {
                    //set property to all selected cells & save
                    this.selectedIssues.filter(i => i !== issue).forEach(i => i[cell] = issue[cell]);
                    this.$emit('update-issues', this.selectedIssues);
                }

                this.cellEditAbort(cell);
            },
            cellEditAbort: function (cell) {
                //disable edit
                this.editEnabled[cell] = false;
                this.editIssue = null;
            },
            toggleMark: function (issue) {
                issue.isMarked = !issue.isMarked;
                this.$emit('update-issues', [issue]);
            },
            toggleWasAddedWithClient: function (issue) {
                issue.wasAddedWithClient = !issue.wasAddedWithClient;
                this.$emit('update-issues', [issue]);
            },
            openLightbox: function (url) {
                this.lightbox.enabled = true;
                this.lightbox.imageFull = url;
            },
            sortBy: function (key) {
                if (this.sortKey === key) {
                    this.sortOrders[key] *= -1;
                } else {
                    this.sortKey = key;
                }
            }
        },
        watch: {
            selectedIssues: function () {
                //call event
                this.$emit('selection-changed', this.selectedIssues);
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
                const order = this.sortOrders[sortKey];
                let data = this.issues;
                if (sortKey) {
                    const statusScore = function (issue) {
                        return 1 * issue.isRead + (2 * (issue.respondedAt !== null)) + (4 * (issue.reviewedAt !== null));
                    };
                    data = data.sort((a, b) => {
                        if (sortKey === 'craftsmanId') {
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
                        } else if (sortKey === 'status') {
                            a = statusScore(a);
                            b = statusScore(b);
                        } else {
                            a = a[sortKey];
                            b = b[sortKey];
                        }

                        let currentOrder = order;
                        if (sortKey === 'isMarked' || sortKey === 'wasAddedWithClient') {
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

    .status-column {
        min-width: 9em;
    }
</style>
