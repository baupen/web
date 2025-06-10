<template>
  <table class="table table-striped table-hover border shadow">
    <thead>
    <tr class="bg-light">
      <th></th>
      <th colspan="8">
        <span class="reset-table-styles">
          <filter-issues-button
              v-if="canFilter"
              :craftsmen="craftsmen" :maps="maps" :construction-managers="constructionManagers"
              :template="filter" :configuration-template="filterConfiguration"
              :default="defaultFilter" :default-configuration="defaultFilterConfiguration"
              @update="filter = $event"
              @update-configuration="filterConfiguration = $event"
          />
          <order-checkbox
              :class="{'ms-3': canFilter}"
              class="d-inline-block"
              property="lastChangedAt" order-value="desc" id="order-by-last-changed-at"
              :label="$t('_view.issues.sort_by_last_activity')" :order="order"
              @ordered="order = $event"/>
        </span>
        <span class="text-end float-end" v-if="canEdit">
            <span class="reset-table-styles me-2">
              <add-issue-button
                  v-if="view === 'foyer'"
                  :construction-manager-iri="constructionManagerIri"
                  :construction-site="constructionSite" :maps="maps" :craftsmen="craftsmen"
                  @added="addIssue($event)"/>
            </span>

            <span class="btn-group reset-table-styles">

              <edit-issues-button
                  ref="edit-issues"
                  :enable-state-edit="canEditState"
                  :construction-manager-iri="constructionManagerIri" :construction-site="constructionSite"
                  :issues="selectedIssues"
                  :craftsmen="craftsmen" :maps="maps"/>
              <remove-issues-button :issues="selectedIssues" @removed="removeIssue($event)"/>
            </span>
          </span>
      </th>
    </tr>
    <tr class="text-secondary">
      <th class="w-minimal">
        <div
            id="all-issues"
            @click="toggleSelectedIssues(displayedIssues)">
          <input class="form-check-input" type="checkbox"
                 :disabled="!displayedIssues"
                 :checked="displayedIssues && displayedIssues.length > 0 && entityListsAreEqual(displayedIssues, selectedIssues)">
        </div>
      </th>
      <th class="w-minimal">
      </th>
      <th class="w-minimal">
      </th>
      <th class="w-thumbnail"></th>
      <order-table-head class="w-40" :order="order" property="description" @ordered="order = $event">
          <span class="me-1">
            {{ $t('issue.description') }}
          </span>
      </order-table-head>
      <order-table-head :order="order" property="map.name" @ordered="order = $event">
          {{ $t('map.name') }}
      </order-table-head>
      <order-table-head class="white-space-nowrap" :order="order" property="craftsman.trade" @ordered="order = $event">
        {{ $t('craftsman._name') }}
      </order-table-head>
      <order-table-head class="white-space-nowrap" :order="order" property="deadline" @ordered="order = $event">
        {{ $t('issue.deadline') }}
      </order-table-head>
      <th class="w-minimal">
      </th>
    </tr>
    </thead>
    <tbody>
    <loading-indicator-table-body v-if="isLoading"/>
    <tr v-else-if="issueContainers.length === 0 && !issuesLoading">
      <td colspan="99">
        <p class="text-center">{{ $t('_view.no_issues') }}</p>
      </td>
    </tr>
    <tr v-else v-for="iwr in issueContainers" @click.stop="toggleSelectedIssue(iwr.issue)" :key="iwr.issue['@id']"
        class="clickable">
      <td class="w-minimal">
        <div>
          <input
              class="form-check-input" type="checkbox"
              v-model="selectedIssues"
              :value="iwr.issue">
        </div>
      </td>
      <td>{{ iwr.issue.number }}</td>
      <td>
        <toggle-icon
            icon="star"
            :value="iwr.issue.isMarked"/>
        <br/>
        <toggle-icon
            icon="user-check"
            :value="iwr.issue.wasAddedWithClient"/>
      </td>
      <td>
        <image-lightbox
            :src="iwr.issue.imageUrl" :subject="iwr.issue.number"
            @click.stop=""/>
      </td>
      <td>
        <span class="clickable-text" @click.stop="editDescription(iwr.issue)">
          <template v-if="iwr.issue.description">
          {{ iwr.issue.description }}
            </template>
          <i v-else>
            {{ $t("issue.no_description_yet") }}
          </i>
        </span>
      </td>
      <td>
        {{ iwr.map?.name }}<br/>
        <span class="text-muted">{{ iwr.mapParentNames?.join(' > ') }}</span>
      </td>
      <td>
        {{ iwr.craftsman?.trade }}<br/>
        <span class="text-muted">{{ iwr.craftsman?.company }}</span>
      </td>
      <td>
        <date-human-readable :value="iwr.issue.deadline"/>
      </td>
      <td @click.stop="" class="cursor-normal">
        <view-issue-button
            :construction-site="constructionSite"
            :construction-managers="constructionManagers"
            :construction-manager-iri="constructionManagerIri"
            :craftsmen="craftsmen"
            :map="iwr.map" :map-parent-names="iwr.mapParentNames"
            :issue="iwr.issue"/>
      </td>
    </tr>
    <loading-indicator-table-body v-if="issuesLoading && !isLoading"/>
    </tbody>
    <caption class="caption-top">
      <template v-if="view === 'foyer'">
        <div v-if="issuesWithoutDescription.length" class="form-check-inline me-4">
          <custom-checkbox id="issues-without-description"
                           :label="$t('_view.issues.without_description')"
                           @click.prevent="toggleSelectedIssues(issuesWithoutDescription)">
            <input class="form-check-input" type="checkbox"
                   :checked="entityListsAreEqual(issuesWithoutDescription, selectedIssues)">
          </custom-checkbox>
        </div>
        <div v-if="issuesWithoutCraftsman.length" class="form-check-inline me-4">
          <custom-checkbox id="issues-without-craftsman"
                           :label="$t('_view.issues.without_craftsman')"
                           @click.prevent="toggleSelectedIssues(issuesWithoutCraftsman)">
            <input class="form-check-input" type="checkbox"
                   :checked="entityListsAreEqual(issuesWithoutCraftsman, selectedIssues)">
          </custom-checkbox>
        </div>
        <div v-if="issuesWithoutDeadline.length" class="form-check-inline">
          <custom-checkbox id="issues-without-deadline"
                           :label="$t('_view.issues.without_deadline')"
                           @click.prevent="toggleSelectedIssues(issuesWithoutDeadline)">
            <input class="form-check-input" type="checkbox"
                   :checked="entityListsAreEqual(issuesWithoutDeadline, selectedIssues)">
          </custom-checkbox>
        </div>
      </template>
      <div class="float-end">
        {{ displayedIssues.length }} / {{ displayableIssueCount }} {{ $t('issue._plural') }}
      </div>
    </caption>
  </table>
  <p class="text-center">
    <button class="btn btn-outline-secondary" v-if="notLoadedIssueCount > 0 && !issuesLoading" @click="loadNextPage">
      {{ $tc('_view.show_more_issues', notLoadedIssueCount) }}
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
import {addNonDuplicatesById, api} from '../../services/api'
import {arraysAreEqual} from '../../services/algorithms'
import ImageLightbox from './ImageLightbox'
import {filterTransformer, mapTransformer} from '../../services/transformers'
import FilterIssuesButton from '../Action/FilterIssuesButton'
import LoadingIndicatorTableBody from '../Library/View/LoadingIndicatorTableBody'
import ToggleIcon from '../Library/View/ToggleIcon'
import AddCraftsmanButton from '../Action/AddCraftsmanButton'
import ViewIssueButton from '../Action/ViewIssueButton'
import OrderTableHead from '../Library/Behaviour/OrderTableHead'
import OrderCheckbox from '../Library/Behaviour/OrderCheckbox'
import RegisterIssuesButton from "../Action/RegisterIssuesButton.vue";
import AddIssueButton from "../Action/AddIssueButton.vue";

export default {
  emits: ['selected', 'query', 'queried-issue-count', 'loaded-craftsmen', 'loaded-maps', 'reset-hidden'],
  components: {
    AddIssueButton,
    RegisterIssuesButton,
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
  data() {
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
    initialState: {
      type: Number,
      required: false
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
    displayableIssueCount: function () {
      return this.totalIssues - this.hiddenIssues.length
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
    canEditState: function () {
      return this.view !== 'foyer'
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
      return this.presetFilter ?? filterTransformer.defaultFilter(this.view)
    },
    defaultFilterConfiguration: function () {
      return filterTransformer.defaultConfiguration(this.view)
    },
    persistFilterKey: function () {
      if (this.view === "foyer" || this.view === "register") {
        return 'issues-table-' + this.view + '-' + this.constructionSite['@id'] + '-' + this.constructionManagerIri
      }
      return null
    },
  },
  methods: {
    addIssue: function (issue) {
      // prevent reload in easy default cause
      if (this.filter === null && this.order === null && this.issuePage === 1) {
        this.issues.unshift(issue)
        this.totalIssues += 1
      } else {
        this.loadIssues(this.filter, this.issuePage)
      }
    },
    editDescription: function (issue) {
      this.selectedIssues = [issue]
      this.$nextTick(() => {
        this.$refs['edit-issues'].$el.nextSibling.click()
        this.$refs['edit-issues'].selectDescription()
      })
    },
    removeIssue(issue) {
      this.issues = this.issues.filter(i => i !== issue)
      this.selectedIssues = this.selectedIssues.filter(i => i !== issue)
      this.totalIssues--
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
        this.selectedIssues = [...this.selectedIssues, toggleIssue]
      }
    },
    entityListsAreEqual(array1, array2) {
      return arraysAreEqual(array1, array2, (a, b) => {
        return a['@id'].localeCompare(b['@id'])
      })
    },
    loadNextPage() {
      if (this.hiddenIssues.length > 0) {
        this.$emit('reset-hidden')
        this.loadIssues(this.filter, 1)
      } else {
        this.loadIssues(this.filter, this.issuePage + 1)
      }
    },
    loadIssues(filter, page = 1) {
      this.issuesLoading = true
      this.issuePage = page
      if (page === 1) {
        this.issues = []
        this.selectedIssues = []
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
        if (this.persistFilterKey) {
          const payload = {filter: newVal, filterConfiguration: this.filterConfiguration}
          localStorage.setItem(this.persistFilterKey, JSON.stringify(payload))
        }
      }, 200, {'leading': true}),
      deep: true
    }
  },
  mounted() {

    if (this.initialState) {
      this.filter = Object.assign({}, this.defaultFilter, {
        state: this.initialState
      })
      this.filterConfiguration = Object.assign({}, this.defaultFilterConfiguration, {
        state: true
      })
    } else if (this.persistFilterKey) {
      try {
        const persistedPayload = localStorage.getItem(this.persistFilterKey);
        const payload = JSON.parse(persistedPayload)
        this.filter = payload.filter
        this.filterConfiguration = payload.filterConfiguration
      } catch (e) {
        // we do not care
      }
    }

    this.loadIssues(this.filter ?? this.defaultFilter)

    let craftsmanQuery = {}
    if (this.presetFilter && this.presetFilter['craftsman[]']) {
      craftsmanQuery['id[]'] = this.presetFilter['craftsman[]']
    }
    api.getCraftsmen(this.constructionSite, craftsmanQuery)
        .then(craftsmen => {
          this.craftsmen = craftsmen
          this.$emit('loaded-craftsmen', this.craftsmen)
        })

    let mapQuery = {}
    if (this.presetFilter && this.presetFilter['map[]']) {
      mapQuery['id[]'] = this.presetFilter['map[]']
    }
    api.getMaps(this.constructionSite, mapQuery)
        .then(maps => {
          this.maps = maps
          this.$emit('loaded-maps', this.maps)
        })

    api.getConstructionManagers(this.constructionSite)
        .then(constructionManagers => this.constructionManagers = constructionManagers)
  }
}
</script>


<style scoped>

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

.clickable-text {
  display: inline-block;
}

.clickable-text:hover {
  border: 1px solid;
}
</style>
