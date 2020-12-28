<template>
  <table class="table table-striped-2 table-hover border">
    <thead>
    <tr class="bg-light">
      <th></th>
      <th colspan="8">
        <span class="mt-2 d-inline-block">{{ $t('issue._name') }}</span>
        <span class="text-right float-right">
          <span class="btn-group reset-table-styles">
            <span class="btn btn-link" v-if="prePatchedIssues.length > 0">{{ prePatchedIssues.length }}</span>
            <edit-issues-button :issues="selectedIssues" :craftsmen="craftsmen" :disabled="selectedIssues.length === 0"
                                @save="saveIssues"/>
            <remove-issues-button :issues="selectedIssues" :disabled="selectedIssues.length === 0"
                                  @remove="removeIssues"/>
          </span>
        </span>
      </th>
    </tr>
    <tr class="text-secondary">
      <th class="w-minimal">
        <custom-checkbox id="all-issues"
                         @click.prevent="toggleSelectedIssues(issues)">
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(issues, selectedIssues)">
        </custom-checkbox>
      </th>
      <th class="w-minimal"></th>
      <th class="w-minimal"></th>
      <th class="w-minimal"></th>
      <th>{{ $t('issue.description') }}</th>
      <th>{{ $t('craftsman._name') }}</th>
      <th>{{ $t('issue.deadline') }}</th>
      <th>{{ $t('map._name') }}</th>
      <th class="w-minimal">{{ $t('issue.status') }}</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="iwr in orderedIssuesWithRelations" @click.stop="toggleSelectedIssue(iwr.issue)" class="clickable">
      <td class="w-minimal">
        <custom-checkbox>
          <input
              class="custom-control-input" type="checkbox"
              v-model="selectedIssues"
              :value="iwr.issue">
        </custom-checkbox>
      </td>
      <td>{{ iwr.issue.number }}</td>
      <td>
        <icon-with-tooltip :icon="iwr.issue.isMarked ? ['fas', 'star'] : ['fal', 'star']"
                           :tooltip-title="$t('issue.is_marked')"/>
        <br/>
        <icon-with-tooltip :icon="iwr.issue.wasAddedWithClient ? ['fas', 'user-check'] : ['fal', 'user-check']"
                           :tooltip-title="$t('issue.was_added_with_client')"/>
      </td>
      <td>
        <lightbox @click.stop="" v-if="iwr.issue.imageUrl"
                  :src="iwr.issue.imageUrl" :src-full="iwr.issue.imageUrl + '?size=full'"
                  :alt="'thumbnail of ' + iwr.issue.number"/>
      </td>
      <td>{{ iwr.issue.description }}</td>
      <td>
        <text-with-tooltip v-if="iwr.craftsman" :tooltip-title="iwr.craftsman.contactName">
          {{ iwr.craftsman.company }}<br/>
          {{ iwr.craftsman.trade }}
        </text-with-tooltip>
      </td>
      <td>
        <human-readable-date :value="iwr.issue.deadline"/>
      </td>
      <td>
        <text-with-tooltip v-if="iwr.map" :tooltip-title="iwr.mapParents.map(m => m.name).join(' > ')">
          {{ iwr.map.name }}
        </text-with-tooltip>
      </td>
      <td class="w-minimal white-space-nowrap">
        <template v-if="iwr.closedBy">
          <text-with-tooltip
              class="mr-1" :tooltip-title="iwr.closedBy.givenName + ' ' + iwr.closedBy.familyName ">
            <b>{{ $t('issue.state.closed') }}</b>
          </text-with-tooltip>
          <human-readable-date-time :value="iwr.issue.closedAt"/>
        </template>

        <template v-else-if="iwr.resolvedBy">
          <text-with-tooltip
              class="mr-1" :tooltip-title="iwr.resolvedBy.contactName">
            <b>{{ $t('issue.state.resolved') }}</b>
          </text-with-tooltip>
          <human-readable-date-time :value="iwr.issue.resolvedAt"/>
        </template>

        <template v-else-if="iwr.registeredBy">
          <text-with-tooltip
              class="mr-1" :tooltip-title="iwr.registeredBy.givenName + ' ' + iwr.registeredBy.familyName">
            <b>{{ $t('issue.state.registered') }}</b>
          </text-with-tooltip>
          <human-readable-date-time :value="iwr.issue.registeredAt"/>
        </template>

        <template v-else>
          <text-with-tooltip
              class="mr-1" :tooltip-title="iwr.createdBy.givenName + ' ' + iwr.createdBy.familyName">
            <b>{{ $t('issue.state.created') }}</b>
          </text-with-tooltip>
          <human-readable-date-time :value="iwr.issue.createdAt"/>
        </template>
      </td>
    </tr>
    </tbody>
    <caption>
      <div v-if="issuesWithoutDescription.length" class="form-check form-check-inline">
        <custom-checkbox id="issues-without-description"
                         :label="$t('issue_table.without_description')"
                         @click.prevent="toggleSelectedIssues(issuesWithoutDescription)">
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(issuesWithoutDescription, selectedIssues)">
        </custom-checkbox>
      </div>
      <div v-if="issuesWithoutCraftsman.length" class="form-check form-check-inline">
        <custom-checkbox id="issues-without-craftsman"
                         :label="$t('issue_table.without_craftsman')"
                         @click.prevent="toggleSelectedIssues(issuesWithoutCraftsman)">
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(issuesWithoutCraftsman, selectedIssues)">
        </custom-checkbox>
      </div>
      <div v-if="issuesWithoutDeadline.length" class="form-check form-check-inline">
        <custom-checkbox id="issues-without-deadline"
                         :label="$t('issue_table.without_deadline')"
                         @click.prevent="toggleSelectedIssues(issuesWithoutDeadline)">
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(issuesWithoutDeadline, selectedIssues)">
        </custom-checkbox>
      </div>
    </caption>
  </table>
</template>

<script>

import HumanReadableDate from './View/HumanReadableDate'
import HumanReadableDateTime from './View/HumanReadableDateTime'
import OrderedTableHead from './View/OrderedTableHead'
import NumberWithTooltip from './View/NumberWithTooltip'
import {arraysAreEqual} from '../services/algorithms'
import CustomCheckbox from './Edit/Input/CustomCheckbox'
import IconWithTooltip from "./View/IconWithTooltip";
import TextWithTooltip from "./View/TextWithTooltip";
import Lightbox from "./Behaviour/Lightbox";
import ButtonWithModalConfirm from "./Behaviour/ButtonWithModalConfirm";
import EditIssuesButton from "./EditIssuesButton";
import {api} from "../services/api";
import {displaySuccess} from "../services/notifiers";
import RemoveIssuesButton from "./RemoveIssuesButton";

export default {
  emits: ['selected', 'save', 'delete'],
  components: {
    RemoveIssuesButton,
    EditIssuesButton,
    ButtonWithModalConfirm,
    Lightbox,
    TextWithTooltip,
    IconWithTooltip,
    CustomCheckbox,
    NumberWithTooltip,
    OrderedTableHead,
    HumanReadableDateTime,
    HumanReadableDate
  },
  data() {
    return {
      selectedIssues: [],
      prePatchedIssues: [],
      preDeletedIssues: [],
    }
  },
  props: {
    issues: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true
    },
    maps: {
      type: Array,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    },
    proposedSelectedIssues: {
      type: Array
    }
  },
  computed: {
    craftsmanLookup: function () {
      let craftsmanLookup = {}
      this.craftsmen.forEach(c => craftsmanLookup[c['@id']] = c)

      return craftsmanLookup
    },
    mapLookup: function () {
      let mapLookup = {}
      this.maps.forEach(m => mapLookup[m['@id']] = m)

      return mapLookup
    },
    mapParentsLookup: function () {
      let mapParentsLookup = {}
      this.maps.forEach(m => {
        let currentMap = m
        let mapParents = []
        let infiniteLoopPrevention = 10

        // collect array with parents, in order
        // for EG -> Haus -> Baustelle: mapParent = [Haus, Baustelle]
        while (currentMap.parent && infiniteLoopPrevention-- > 0) {
          currentMap = this.mapLookup[currentMap.parent]
          mapParents.push(currentMap)

          if (currentMap.parent && mapParentsLookup[currentMap.parent['@id']] !== undefined) {
            mapParents = mapParents.concat(...mapParentsLookup[currentMap.parent['@id']])
            break
          }
        }

        mapParentsLookup[m['@id']] = mapParents

        while (mapParents.length > 0) {
          let nextInPath = [...mapParents].shift()
          if (mapParentsLookup[nextInPath['@id']]) {
            break;
          }

          mapParentsLookup[nextInPath['@id']] = mapParents
        }
      });

      return mapParentsLookup
    },
    constructionManagerLookup: function () {
      let constructionManagerLookup = {}
      this.constructionManagers.forEach(cm => constructionManagerLookup[cm['@id']] = cm)

      return constructionManagerLookup
    },
    orderedIssuesWithRelations: function () {
      return this.issues.map(issue => {
        return {
          issue,
          craftsman: this.craftsmanLookup[issue.craftsman],
          map: this.mapLookup[issue.map],
          mapParents: this.mapParentsLookup[issue.map],
          createdBy: this.constructionManagerLookup[issue.createdBy],
          registeredBy: this.constructionManagerLookup[issue.registeredBy],
          resolvedBy: this.craftsmanLookup[issue.resolvedBy],
          closedBy: this.constructionManagerLookup[issue.closedBy]
        };
      }).sort((a, b) => a.issue.number - b.issue.number)
    },
    issuesWithoutDescription: function () {
      return this.issues.filter(i => i.description === null || i.description === "")
    },
    issuesWithoutDeadline: function () {
      return this.issues.filter(i => i.deadline === null)
    },
    issuesWithoutCraftsman: function () {
      return this.issues.filter(i => i.craftsman === null)
    },
  },
  methods: {
    saveIssues: function (patch) {
      this.prePatchedIssues = this.selectedIssues.map(issue => {
        return {issue, patch: Object.assign({}, patch)}
      })

      this.patchIssues()
    },
    removeIssues: function () {
      this.preDeletedIssues = [...this.selectedIssues]

      this.deleteIssues()
    },
    patchIssues() {
      const payload = this.prePatchedIssues[0]
      api.patch(payload.issue, payload.patch)
          .then(_ => {
                this.prePatchedIssues.shift()

                if (this.prePatchedIssues.length === 0) {
                  displaySuccess(this.$t('issue_table.messages.success.saved_issues'))
                } else {
                  this.patchIssues()
                }
              }
          )
    },
    deleteIssues() {
      const issue = this.preDeletedIssues[0]
      api.delete(issue)
          .then(_ => {
                this.preDeletedIssues.shift()
                this.$emit('delete', issue)
                this.selectedIssues = this.selectedIssues.filter(i => i !== issue)

                if (this.preDeletedIssues.length === 0) {
                  displaySuccess(this.$t('issue_table.messages.success.deleted_issues'))
                } else {
                  this.deleteIssues()
                }
              }
          )
    },
    toggleSelectedIssues(toggleArray) {
      if (this.entityListsAreEqual(toggleArray, this.selectedIssues)) {
        this.selectedIssues = []
      } else {
        this.selectedIssues = [...toggleArray]
      }
    },
    toggleSelectedIssue(toggleIssue) {
      if (this.selectedIssues.includes(toggleIssue)) {
        this.selectedIssues = this.selectedIssues.filter(c => c !== toggleIssue)
      } else {
        this.selectedIssues.push(toggleIssue)
      }
    },
    entityListsAreEqual(array1, array2) {
      return arraysAreEqual(array1, array2, (a, b) => {
        return a['@id'].localeCompare(b['@id'])
      })
    }
  },
  watch: {
    proposedSelectedIssues: function () {
      this.selectedIssues = this.proposedSelectedIssues
    },
    selectedIssues: function () {
      this.$emit('selected', this.selectedIssues)
    }
  },
  mounted() {
    if (this.proposedSelectedIssues) {
      this.selectedIssues = this.proposedSelectedIssues
    }
  }
}
</script>


<style scoped="true">
.table-striped-2 tbody tr:nth-of-type(2n) {
  background-color: rgba(0, 0, 0, 0.05);
}

.custom-checkbox {
  margin-right: -0.5em;
}

.white-space-nowrap {
  white-space: nowrap
}

.reset-table-styles {
  text-align: left;
  font-weight: normal;
}
</style>
