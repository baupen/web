<template>
    <table class="table table-striped-2 table-hover border">
      <thead>
      <tr class="bg-light">
        <th></th>
        <th colspan="8">
          <span class="reset-table-styles">
            <filter-issues-button
                :view="view"
                :disabled="isLoading"
                :template="filterTemplate" :craftsmen="craftsmen" :maps="maps"
                @update="filter = $event"
            />
          </span>
          <span class="text-right float-right">
            <span class="btn-group reset-table-styles">
              <edit-issues-button :issues="selectedIssues" :craftsmen="craftsmen" />
              <remove-issues-button :issues="selectedIssues" @removed="removeIssue($event)" />
            </span>
          </span>
        </th>
      </tr>
      <tr class="text-secondary">
        <th class="w-minimal">
          <custom-checkbox
              id="all-issues"
              @click.prevent="toggleSelectedIssues(issues)">
            <input class="custom-control-input" type="checkbox"
                   :disabled="!issues"
                   :checked="issues && issues.length > 0 && entityListsAreEqual(issues, selectedIssues)">
          </custom-checkbox>
        </th>
        <th class="w-minimal">
        </th>
        <th class="w-minimal">
        </th>
        <th class="w-thumbnail"></th>
        <th>
          <span class="mr-1">
            {{ $t('issue.description') }}
          </span>
        </th>
        <th>
          {{ $t('craftsman._name') }}
        </th>
        <th>
          {{ $t('map._name') }}
        </th>
        <th class="border-left">
          {{ $t('issue.deadline') }}
        </th>
        <th class="w-minimal">
          {{ $t('issue.status') }}
        </th>
      </tr>
      </thead>
      <tbody>
      <loading-indicator-table-body v-if="isLoading" />
      <tr v-else-if="issues.length === 0">
        <td colspan="9">
          <p class="text-center">{{ $t('view.no_issues') }}</p>
        </td>
      </tr>
      <tr v-else v-for="iwr in issuesWithRelations" @click.stop="toggleSelectedIssue(iwr.issue)" :key="iwr.issue['@id']" class="clickable">
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
          <toggle-icon
              icon="star"
              :value="iwr.issue.isMarked" />
          <br />
          <toggle-icon
              icon="user-check"
              :value="iwr.issue.wasAddedWithClient" />
        </td>
        <td>
          <image-lightbox
              :src="iwr.issue.imageUrl" :subject="iwr.issue.number"
              @click.stop="" />
        </td>
        <td>{{ iwr.issue.description }}</td>
        <td>
            {{ iwr.craftsman.trade }}<br />
            <span class="text-muted">{{ iwr.craftsman.company }}</span>
        </td>
        <td>
          {{ iwr.map.name }}<br />
          <span class="text-muted">{{ iwr.mapParents.map(m => m.name).join(' > ') }}</span>
        </td>
        <td class="border-left">
          <date-human-readable :value="iwr.issue.deadline" />
        </td>
        <td class="w-minimal white-space-nowrap">
          (visual)
        </td>
      </tr>
      </tbody>
      <caption class="caption-top">
        <template v-if="view === 'foyer'">
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
        </template>
        <div class="float-right">
          {{ totalIssues }} {{ $t('issue._plural') }}
        </div>
      </caption>
    </table>
    <p class="text-center">
      <button class="btn btn-outline-secondary" v-if="notLoadedIssueCount > 0 && !issuesLoading" @click="loadNextPage">
        {{ $tc('actions.show_more_issues', notLoadedIssueCount) }}
      </button>
    </p>
</template>

<script>

import LoadingIndicator from '../Library/View/LoadingIndicator'
import EditIssuesButton from '../Action/EditIssuesButton'
import RemoveIssuesButton from '../Action/RemoveIssuesButton'
import CustomCheckbox from '../Library/FormInput/CustomCheckbox'
import TooltipToggleIcon from '../Library/View/TooltipToggleIcon'
import TooltipText from '../Library/View/TooltipText'
import DateHumanReadable from '../Library/View/DateHumanReadable'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import debounce from 'lodash.debounce'
import { api } from '../../services/api'
import { arraysAreEqual } from '../../services/algorithms'
import ImageLightbox from './ImageLightbox'
import { filterTransformer, mapTransformer } from '../../services/transformers'
import FilterIssuesButton from '../Action/FilterIssuesButton'
import LoadingIndicatorTableBody from '../Library/View/LoadingIndicatorTableBody'
import ToggleIcon from '../Library/View/ToggleIcon'

export default {
  emits: ['selected', 'query', 'queried-issue-count'],
  components: {
    ToggleIcon,
    LoadingIndicatorTableBody,
    FilterIssuesButton,
    ImageLightbox,
    DateTimeHumanReadable,
    DateHumanReadable,
    TooltipText,
    TooltipToggleIcon,
    CustomCheckbox,
    RemoveIssuesButton,
    EditIssuesButton,
    LoadingIndicator
  },
  data () {
    return {
      constructionManagers: null,
      craftsmen: null,
      maps: null,

      filter: null,

      issues: [],
      issuePage: 0,
      totalIssues: 0,
      issuesLoading: true,

      selectedIssues: [],
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    view: {
      type: String,
      required: true,
    }
  },
  computed: {
    isLoading: function () {
      return !this.constructionManagers || !this.maps || !this.craftsmen || (this.issuesLoading && this.issuePage === 1);
    },
    filterTemplate: function () {
      let defaultFilter = Object.assign({isDeleted: false}, this.filter)
      if (this.view === 'foyer') {
        return Object.assign(defaultFilter, {state: 1})
      } else if (this.view === 'register') {
        return Object.assign(defaultFilter, {state: 8})
      } else {
        return defaultFilter
      }
    },
    notLoadedIssueCount: function () {
      return this.totalIssues - this.issues.length
    },
    editButtonPendingRequestCount: function () {
      return this.prePatchedIssues.length + this.prePostedIssueImages.length
    },
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
      return mapTransformer.parentsLookup(this.maps)
    },
    constructionManagerLookup: function () {
      let constructionManagerLookup = {}
      this.constructionManagers.forEach(cm => constructionManagerLookup[cm['@id']] = cm)

      return constructionManagerLookup
    },
    issuesWithRelations: function () {
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
        }
      })
    },
    issuesWithoutDescription: function () {
      return this.issues.filter(i => !i.description)
    },
    issuesWithoutDeadline: function () {
      return this.issues.filter(i => !i.deadline)
    },
    issuesWithoutCraftsman: function () {
      return this.issues.filter(i => !i.craftsman)
    },
  },
  methods: {
    removeIssue(issue) {
      this.issues = this.issues.filter(i => i !== issue)
      this.selectedIssues = this.selectedIssues.filter(i => i !== issue)
      this.totalIssues--;
    },
    toggleSelectedIssues (toggleArray) {
      if (this.entityListsAreEqual(toggleArray, this.selectedIssues)) {
        this.selectedIssues = []
      } else {
        this.selectedIssues = [...toggleArray]
      }
    },
    toggleSelectedIssue (toggleIssue) {
      if (this.selectedIssues.includes(toggleIssue)) {
        this.selectedIssues = this.selectedIssues.filter(c => c !== toggleIssue)
      } else {
        this.selectedIssues = [...this.selectedIssues, toggleIssue]
      }
    },
    entityListsAreEqual (array1, array2) {
      return arraysAreEqual(array1, array2, (a, b) => {
        return a['@id'].localeCompare(b['@id'])
      })
    },
    filterAsQuery: function (filter) {
      return filterTransformer.filterToQuery(filter)
    },
    loadNextPage () {
      this.loadIssues(this.filter, this.issuePage + 1)
    },
    loadIssues (filter, page = 1) {
      this.issuesLoading = true
      this.issuePage = page

      let query = this.filterAsQuery(filter)
      this.$emit('query', query)

      query = Object.assign({}, query, { page })

      api.getPaginatedIssues(this.constructionSite, query)
          .then(payload => {
            if (page === 1) {
              this.issues = payload.items
            } else {
              this.issues = this.issues.concat(payload.items)
            }
            this.totalIssues = payload.totalItems

            this.$emit('queried-issue-count', payload.totalItems)

            this.issuesLoading = false
          })
    }
  },
  watch: {
    selectedIssues: {
      deep: true,
      handler: function () {
        this.$emit('selected', this.selectedIssues)
      }
    },
    filter: {
      handler: debounce(function (newVal) {
        this.loadIssues(newVal)
      }, 200, { 'leading': true }),
      deep: true
    }
  },
  mounted () {
    this.filter = this.filterTemplate;

    api.getCraftsmen(this.constructionSite)
        .then(craftsmen => {
          this.craftsmen = craftsmen
        })

    api.getMaps(this.constructionSite)
        .then(maps => {
          this.maps = maps
        })

    api.getConstructionManagers(this.constructionSite)
        .then(constructionManagers => this.constructionManagers = constructionManagers)
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
