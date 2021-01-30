<template>
  <loading-indicator :spin="filtersLoading">
    <table class="table table-striped-2 table-hover border">
      <thead>
      <tr class="bg-light">
        <th></th>
        <th colspan="8">
          <span class="mt-2 d-inline-block">{{ $t('issue._name') }}</span>
          <span class="text-right float-right">
          <span class="btn-group reset-table-styles" v-if="craftsmen">
            <span class="btn btn-link" v-if="editButtonPendingRequestCount > 0">
              {{ editButtonPendingRequestCount }}
            </span>
            <edit-issues-button :issues="selectedIssues" :craftsmen="craftsmen"
                                :disabled="selectedIssues.length === 0 || editButtonPendingRequestCount > 0"
                                @save="saveIssues"
                                @save-image="saveIssueImages" />

            <span class="btn btn-link" v-if="preDeletedIssues.length > 0">
              {{ preDeletedIssues.length }}
            </span>
            <remove-issues-button :issues="selectedIssues"
                                  :disabled="selectedIssues.length === 0 || preDeletedIssues.length > 0"
                                  @remove="removeIssues" />
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
          <search-popover
              :title="$t('issue_table.filter.by_number')" :valid="!!(filter.number || filter.number === 0)"
              @shown="$refs['filter-number'].focus()">
            <input class="form-control" ref="filter-number" v-model.number="filter.number" type="number"
                   name="filter-number">
          </search-popover>
        </th>
        <th class="w-minimal">
          <filter-popover
              :title="$t('issue_table.filter.by_is_marked_or_added_with_client')"
              :valid="!!(filter.isMarked || filter.wasAddedWithClient)">

            <is-marked-filter class="mt-2" @input="filter.isMarked = $event" />
            <was-added-with-client-filter class="mb-2" @input="filter.wasAddedWithClient = $event" />

          </filter-popover>
        </th>
        <th class="w-thumbnail"></th>
        <th>
          <span class="mr-1">
            {{ $t('issue.description') }}
          </span>
          <search-popover
              :title="$t('issue_table.filter.by_description')" :valid="!!(filter.description)"
              @shown="$refs['filter-description'].focus()">
            <input class="form-control" ref="filter-description" v-model="filter.description" type="text"
                   name="filter-description">
          </search-popover>
        </th>
        <th>
          {{ $t('craftsman._name') }}

          <filter-popover
              :title="$t('issue_table.filter.by_craftsman')"
              :valid="filter.craftsmen.length < craftsmen.length && filter.craftsmen.length > 0">

            <craftsmen-filter class="mt-2" :craftsmen="craftsmen" @input="filter.craftsmen = $event" />
          </filter-popover>
        </th>
        <th>
          {{ $t('map._name') }}

          <filter-popover
              :title="$t('issue_table.filter.by_maps')"
              :valid="filter.maps.length < maps.length && filter.maps.length > 0">

            <map-filter class="mt-2" :maps="maps" @input="filter.maps = $event" />
          </filter-popover>
        </th>
        <th>
          {{ $t('issue.deadline') }}

          <filter-popover
              size="filter-wide"
              :title="$t('issue_table.filter.by_deadline')"
              :valid="!!(filter['deadline[before]'] && filter['deadline[after]'])">

            <deadline-filter
                @input-deadline-before="filter['deadline[before]'] = $event"
                @input-deadline-after="filter['deadline[after]'] = $event"
            />
          </filter-popover>
        </th>
        <th class="w-minimal">
          {{ $t('issue.status') }}


          <filter-popover
              size="filter-wide"
              :title="$t('issue_table.filter.by_state')"
              :valid="!forceState && filter.state !== 7">
            <template v-if="!forceState">
              <p class="font-weight-bold">{{ $t('issue_table.filter_state.by_active_state') }}</p>

              <state-filter
                  :minimal-state="minimalState"
                  @input="filter.state = $event" />

              <hr />
            </template>

            <p class="font-weight-bold">{{ $t('issue_table.filter_time.by_time') }}</p>
            <time-filter
                :minimal-state="minimalState" :force-state="forceState"
                @input-registered-at-before="filter['registeredAt[before]'] = $event"
                @input-registered-at-after="filter['registeredAt[after]'] = $event"
                @input-resolved-at-before="filter['resolvedAt[before]'] = $event"
                @input-resolved-at-after="filter['resolvedAt[after]'] = $event"
                @input-closed-at-before="filter['closedAt[before]'] = $event"
                @input-closed-at-after="filter['closedAt[after]'] = $event"
            />
          </filter-popover>
        </th>
      </tr>
      </thead>
      <tbody v-if="issues">
      <tr v-if="issues.length === 0 && !issuesLoading">
        <td colspan="9">
          <p class="text-center">no issues found</p>
        </td>
      </tr>
      <tr v-for="iwr in issuesWithRelations" @click.stop="toggleSelectedIssue(iwr.issue)" class="clickable">
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
          <tooltip-toggle-icon
              icon="star"
              :value="iwr.issue.isMarked"
              :tooltip-title="$t('issue.is_marked')" />
          <br />
          <tooltip-toggle-icon
              icon="user-check"
              :value="iwr.issue.wasAddedWithClient"
              :tooltip-title="$t('issue.was_added_with_client')" />
        </td>
        <td>
          <image-lightbox
              :src="iwr.issue.imageUrl" :subject="iwr.issue.number"
              @click.stop="" />
        </td>
        <td>{{ iwr.issue.description }}</td>
        <td>
          <tooltip-text v-if="iwr.craftsman" :tooltip-title="iwr.craftsman.contactName">
            {{ iwr.craftsman.trade }}<br />
            <span class="text-muted">{{ iwr.craftsman.company }}</span>
          </tooltip-text>
        </td>
        <td>
          <tooltip-text v-if="iwr.map" :tooltip-title="iwr.mapParents.map(m => m.name).join(' > ')">
            {{ iwr.map.name }}
          </tooltip-text>
        </td>
        <td>
          <date-human-readable :value="iwr.issue.deadline" />
        </td>
        <td class="w-minimal white-space-nowrap">
          <template v-if="iwr.closedBy">
            <tooltip-text
                class="mr-1" :tooltip-title="iwr.closedBy.givenName + ' ' + iwr.closedBy.familyName ">
              <b>{{ $t('issue.state.closed') }}</b>
            </tooltip-text>
            <date-time-human-readable :value="iwr.issue.closedAt" />
          </template>

          <template v-else-if="iwr.resolvedBy">
            <tooltip-text
                class="mr-1" :tooltip-title="iwr.resolvedBy.contactName">
              <b>{{ $t('issue.state.resolved') }}</b>
            </tooltip-text>
            <date-time-human-readable :value="iwr.issue.resolvedAt" />
          </template>

          <template v-else-if="iwr.registeredBy">
            <tooltip-text
                class="mr-1" :tooltip-title="iwr.registeredBy.givenName + ' ' + iwr.registeredBy.familyName">
              <b>{{ $t('issue.state.registered') }}</b>
            </tooltip-text>
            <date-time-human-readable :value="iwr.issue.registeredAt" />
          </template>

          <template v-else>
            <tooltip-text
                class="mr-1" :tooltip-title="iwr.createdBy.givenName + ' ' + iwr.createdBy.familyName">
              <b>{{ $t('issue.state.created') }}</b>
            </tooltip-text>
            <date-time-human-readable :value="iwr.issue.createdAt" />
          </template>
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
        {{ $tc('issue_table.show_more_issues', notLoadedIssueCount) }}
      </button>
    </p>
  </loading-indicator>
</template>

<script>

import LoadingIndicator from '../Library/View/LoadingIndicator'
import EditIssuesButton from '../Action/EditIssuesButton'
import RemoveIssuesButton from '../Action/RemoveIssuesButton'
import CustomCheckbox from '../Library/FormInput/CustomCheckbox'
import SearchPopover from '../Action/SearchPopover'
import FilterPopover from '../Action/FilterPopover'
import IsMarkedFilter from '../Filter/IsMarkedFilter'
import WasAddedWithClientFilter from '../Filter/WasAddedWithClientFilter'
import CraftsmenFilter from '../Filter/CraftsmenFilter'
import MapFilter from '../Filter/MapFilter'
import DeadlineFilter from '../Filter/DeadlineFilter'
import StateFilter from '../Filter/StateFilter'
import TimeFilter from '../Filter/TimeFilter'
import TooltipToggleIcon from '../Library/View/TooltipToggleIcon'
import TooltipText from '../Library/View/TooltipText'
import DateHumanReadable from '../Library/View/DateHumanReadable'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import debounce from 'lodash.debounce'
import { api } from '../../services/api'
import { displaySuccess } from '../../services/notifiers'
import { arraysAreEqual } from '../../services/algorithms'
import ImageLightbox from './ImageLightbox'

export default {
  emits: ['selected', 'query', 'queried-issue-count'],
  components: {
    ImageLightbox,
    DateTimeHumanReadable,
    DateHumanReadable,
    TooltipText,
    TooltipToggleIcon,
    TimeFilter,
    StateFilter,
    DeadlineFilter,
    MapFilter,
    CraftsmenFilter,
    WasAddedWithClientFilter,
    IsMarkedFilter,
    FilterPopover,
    SearchPopover,
    CustomCheckbox,
    RemoveIssuesButton,
    EditIssuesButton,
    LoadingIndicator
  },
  data () {
    return {
      issues: [],
      issuePage: 1,
      totalIssues: 0,
      issuesLoading: true,

      constructionManagers: null,
      craftsmen: null,
      maps: null,

      filter: {
        number: null,

        isMarked: null,
        wasAddedWithClient: null,

        description: '',
        craftsmen: [],
        maps: [],
        'deadline[before]': null,
        'deadline[after]': null,

        state: null,

        'createdAt[before]': null,
        'createdAt[after]': null,
        'registeredAt[before]': null,
        'registeredAt[after]': null,
        'resolvedAt[before]': null,
        'resolvedAt[after]': null,
        'closedAt[before]': null,
        'closedAt[after]': null,

        isDeleted: false
      },
      selectedIssues: [],
      prePatchedIssues: [],
      prePostedIssueImages: [],
      preDeletedIssues: [],
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    minimalState: {
      type: Number,
      required: false,
      default: null
    },
    forceState: {
      type: Number,
      required: false,
      default: null
    },
    view: {
      type: String,
      required: false,
      default: 'register'
    }
  },
  computed: {
    notLoadedIssueCount: function () {
      return this.totalIssues - this.issues.length
    },
    editButtonPendingRequestCount: function () {
      return this.prePatchedIssues.length + this.prePostedIssueImages.length
    },
    filtersLoading: function () {
      return !this.constructionManagers || !this.craftsmen || !this.maps
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
            break
          }

          mapParentsLookup[nextInPath['@id']] = mapParents
        }
      })

      return mapParentsLookup
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
    saveIssues: function (patch) {
      this.prePatchedIssues = this.selectedIssues.map(issue => {
        return {
          issue,
          patch: Object.assign({}, patch)
        }
      })

      this.patchIssues()
    },
    saveIssueImages: function (image) {
      this.prePostedIssueImages = this.selectedIssues.map(issue => {
        return {
          issue,
          image
        }
      })

      this.postIssueImages()
    },
    removeIssues: function () {
      this.preDeletedIssues = [...this.selectedIssues]

      this.deleteIssues()
    },
    patchIssues () {
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
    deleteIssues () {
      const issue = this.preDeletedIssues[0]
      api.delete(issue)
          .then(_ => {
                this.preDeletedIssues.shift()
                this.$emit('deleted', issue)
                this.selectedIssues = this.selectedIssues.filter(i => i !== issue)

                if (this.preDeletedIssues.length === 0) {
                  displaySuccess(this.$t('issue_table.messages.success.remove_issues'))
                } else {
                  this.deleteIssues()
                }
              }
          )
    },
    postIssueImages () {
      const payload = this.prePostedIssueImages[0]
      api.postIssueImage(payload.issue, payload.image)
          .then(_ => {
                this.prePostedIssueImages.shift()

                if (this.prePostedIssueImages.length === 0) {
                  displaySuccess(this.$t('issue_table.messages.success.save_issue_images'))
                } else {
                  this.postIssueImages()
                }
              }
          )
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
        this.selectedIssues.push(toggleIssue)
      }
    },
    entityListsAreEqual (array1, array2) {
      return arraysAreEqual(array1, array2, (a, b) => {
        return a['@id'].localeCompare(b['@id'])
      })
    },
    filterAsQuery: function (filter) {
      let query = {}

      for (const fieldName in filter) {
        if (!Object.prototype.hasOwnProperty.call(filter, fieldName)) {
          continue
        }

        const fieldValue = filter[fieldName]

        if (fieldName === 'craftsmen') {
          if (fieldValue.length === 0 || fieldValue.length !== this.craftsmen.length) {
            query['craftsman[]'] = fieldValue.map(e => iriToId(e['@id']))
          }
        } else if (fieldName === 'maps') {
          if (fieldValue.length === 0 || fieldValue.length !== this.maps.length) {
            query['map[]'] = fieldValue.map(e => iriToId(e['@id']))
          }
        } else if (fieldValue || fieldValue === false) {
          // "false" is the only Falsy value applicable as filter
          query[fieldName] = fieldValue
        }
      }

      return query
    },
    loadNextPage () {
      this.loadIssues(this.filter, this.issuePage + 1)
    },
    loadIssues (filter, page = 1) {
      this.issuesLoading = true

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
            this.issuePage = page

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
        // skip first watch
        if (newVal.state === null) {
          return
        }

        this.loadIssues(newVal)
      }, 200, { 'leading': true }),
      deep: true
    }
  },
  mounted () {
    api.getCraftsmen(this.constructionSite)
        .then(craftsmen => {
          this.craftsmen = craftsmen
          this.filter.craftsmen = this.craftsmen
        })

    api.getMaps(this.constructionSite)
        .then(maps => {
          this.maps = maps
          this.filter.maps = this.maps
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
