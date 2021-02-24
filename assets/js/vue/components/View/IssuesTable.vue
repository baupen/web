<template>
  <table class="table table-striped-2 table-hover border">
    <thead>
    <tr class="bg-light">
      <th></th>
      <th colspan="8">
        <span class="reset-table-styles">
          <filter-issues-button
              v-if="canFilter"
              :disabled="isLoading" :craftsmen="craftsmen" :maps="maps"
              :template="filterTemplate" :configuration-template="filterConfigurationTemplate"
              @update="filter = $event"
              @update-configuration="filterConfiguration = $event"
          />
          <order-checkbox
              :class="{'ml-3': canFilter}"
              class="d-inline-block"
              property="lastChangedAt" order-value="desc" id="order-by-last-changed-at"
              :label="$t('actions.sort_by_last_activity')" :order="order"
              @ordered="order = $event" />
        </span>
        <span class="text-right float-right" v-if="canEdit">
            <span class="btn-group reset-table-styles">
              <edit-issues-button
                  :construction-manager-iri="constructionManagerIri" :issues="selectedIssues"
                  :craftsmen="craftsmen" />
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
      <th class="w-40">
          <span class="mr-1">
            {{ $t('issue.description') }}
          </span>
      </th>
      <th>
        {{ $t('map._name') }}
      </th>
      <th>
        {{ $t('craftsman._name') }}
      </th>
      <order-table-head class="white-space-nowrap" :order="order" property="deadline" @ordered="order = $event">
        {{ $t('issue.deadline') }}
      </order-table-head>
      <th class="w-minimal">
      </th>
    </tr>
    </thead>
    <tbody>
    <loading-indicator-table-body v-if="isLoading" />
    <tr v-else-if="issueContainers.length === 0 && !issuesLoading">
      <td colspan="99">
        <p class="text-center">{{ $t('view.no_issues') }}</p>
      </td>
    </tr>
    <tr v-else v-for="iwr in issueContainers" @click.stop="toggleSelectedIssue(iwr.issue)" :key="iwr.issue['@id']"
        class="clickable">
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
        {{ iwr.map?.name }}<br />
        <span class="text-muted">{{ iwr.mapParentNames?.join(' > ') }}</span>
      </td>
      <td>
        {{ iwr.craftsman?.trade }}<br />
        <span class="text-muted">{{ iwr.craftsman?.company }}</span>
      </td>
      <td>
        <date-human-readable :value="iwr.issue.deadline" />
      </td>
      <td @click.stop="" class="cursor-normal">
        <view-issue-button
            :construction-site="constructionSite"
            :constructionManagers="constructionManagers"
            :map="iwr.map" :map-parent-names="iwr.mapParentNames"
            :craftsman="iwr.craftsman" :resolvedBy="iwr.resolvedBy"
            :issue="iwr.issue" />
      </td>
    </tr>
    <loading-indicator-table-body v-if="issuesLoading && !isLoading" />
    </tbody>
    <caption class="caption-top">
      <template v-if="view === 'foyer'">
        <div v-if="issuesWithoutDescription.length" class="form-check form-check-inline mr-4">
          <custom-checkbox id="issues-without-description"
                           :label="$t('issue_table.without_description')"
                           @click.prevent="toggleSelectedIssues(issuesWithoutDescription)">
            <input class="custom-control-input" type="checkbox"
                   :checked="entityListsAreEqual(issuesWithoutDescription, selectedIssues)">
          </custom-checkbox>
        </div>
        <div v-if="issuesWithoutCraftsman.length" class="form-check form-check-inline mr-4">
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
import { addNonDuplicatesById, api } from '../../services/api'
import { arraysAreEqual } from '../../services/algorithms'
import ImageLightbox from './ImageLightbox'
import { filterTransformer, mapTransformer } from '../../services/transformers'
import FilterIssuesButton from '../Action/FilterIssuesButton'
import LoadingIndicatorTableBody from '../Library/View/LoadingIndicatorTableBody'
import ToggleIcon from '../Library/View/ToggleIcon'
import AddCraftsmanButton from '../Action/AddCraftsmanButton'
import ViewIssueButton from '../Action/ViewIssueButton'
import OrderTableHead from '../Library/Behaviour/OrderTableHead'
import OrderCheckbox from '../Library/Behaviour/OrderCheckbox'

export default {
  emits: ['selected', 'query', 'queried-issue-count', 'loaded-maps'],
  components: {
    OrderCheckbox,
    OrderTableHead,
    ViewIssueButton,
    AddCraftsmanButton,
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
      filterConfiguration: null,

      order: null,

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
    constructionManagerIri: {
      type: String,
      required: false
    },
    view: {
      type: String,
      required: true,
    },
    presetFilter: {
      type: Object,
      required: false,
    },
    hiddenIssues: {
      type: Array,
      default: []
    }
  },
  computed: {
    isLoading: function () {
      return !this.constructionManagers || !this.maps || !this.craftsmen
    },
    notLoadedIssueCount: function () {
      return this.totalIssues - this.issues.length
    },
    displayedIssues: function () {
      return this.issues.filter(i => !this.hiddenIssues.includes(i))
    },
    craftsmanLookup: function () {
      let craftsmanLookup = {}
      this.craftsmen.forEach(c => craftsmanLookup[c['@id']] = c)

      return craftsmanLookup
    },
    mapContainerLookup: function () {
      return mapTransformer.lookup(this.maps, mapTransformer.PROPERTY_MAP_PARENT_NAMES)
    },
    canEdit: function () {
      return this.view === 'foyer' || this.view === 'register'
    },
    canFilter: function () {
      return this.canEdit
    },
    issueContainers: function () {
      return this.displayedIssues.map(issue => {
        let mapContainer = this.mapContainerLookup[issue.map]

        return {
          issue,
          craftsman: this.craftsmanLookup[issue.craftsman],
          map: mapContainer.entity,
          mapParentNames: mapContainer.mapParentNames,
          resolvedBy: this.craftsmanLookup[issue.resolvedBy]
        }
      })
    },
    issuesWithoutDescription: function () {
      return this.displayedIssues.filter(i => !i.description)
    },
    issuesWithoutDeadline: function () {
      return this.displayedIssues.filter(i => !i.deadline)
    },
    issuesWithoutCraftsman: function () {
      return this.displayedIssues.filter(i => !i.craftsman)
    },
    defaultFilter: function () {
      return filterTransformer.defaultFilter(this.view)
    },
    defaultFilterConfiguration: function () {
      return filterTransformer.defaultConfiguration(this.view)
    },
    filterTemplate: function () {
      return this.filter ?? this.defaultFilter
    },
    filterConfigurationTemplate: function () {
      return this.filterConfiguration ?? this.defaultFilterConfiguration
    }
  },
  methods: {
    removeIssue (issue) {
      this.issues = this.issues.filter(i => i !== issue)
      this.selectedIssues = this.selectedIssues.filter(i => i !== issue)
      this.totalIssues--
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
    loadNextPage () {
      this.loadIssues(this.filter, this.issuePage + 1)
    },
    loadIssues (filter, page = 1) {
      this.issuesLoading = true
      this.issuePage = page
      if (page === 1) {
        this.issues = []
      }

      let query = filterTransformer.filterToQuery(this.defaultFilter, filter, this.filterConfiguration, this.craftsmen, this.maps)

      // set order
      const currentOrder = this.order ? this.order : {
        property: 'number',
        value: 'desc'
      }
      query['order[' + currentOrder.property + ']'] = currentOrder.value

      this.$emit('query', query)

      query.page = page
      api.getPaginatedIssues(this.constructionSite, query)
          .then(payload => {
            addNonDuplicatesById(this.issues, payload.items)
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
    hiddenIssues: {
      deep: true,
      handler: function () {
        this.selectedIssues = this.selectedIssues.filter(i => !this.hiddenIssues.includes(i))
      }
    },
    order: function () {
      this.loadIssues(this.filter)
    },
    filter: {
      handler: debounce(function (newVal) {
        this.loadIssues(newVal)
      }, 200, { 'leading': true }),
      deep: true
    }
  },
  mounted () {
    this.loadIssues(this.presetFilter ?? this.defaultFilter)

    api.getCraftsmen(this.constructionSite)
        .then(craftsmen => {
          this.craftsmen = craftsmen
        })

    api.getMaps(this.constructionSite)
        .then(maps => {
          this.maps = maps
          this.$emit('loaded-maps', this.maps)
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
