<template>
  <table class="table table-striped table-hover border">
    <thead>
    <tr class="text-sm">
      <th class="w-minimal"></th>
      <th colspan="4">{{ $t('craftsman._name') }}</th>
      <th class="border-left" colspan="2">{{ $t('issue._plural') }}</th>
      <th class="border-left" colspan="2">{{ $t('_view.craftsmen.last_activity') }}</th>
      <th class="border-left"></th>
    </tr>
    <tr>
      <th class="w-minimal">
        <custom-checkbox @click.prevent="toggleSelectedCraftsmen(craftsmen)">
          <input class="form-check-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmen, selectedCraftsmen)">
        </custom-checkbox>
      </th>
      <th>{{ $t('craftsman.trade') }}</th>
      <th>{{ $t('craftsman.company') }}</th>
      <th class="w-minimal"></th>
      <th class="w-minimal"></th>

      <th class="border-left">{{ $t('_view.craftsmen_statistics.count') }}</th>
      <th>{{ $t('craftsman.next_deadline') }}</th>

      <th class="border-left">{{ $t('craftsman.last_visit_online') }}</th>
      <th>{{ $t('craftsman.last_issue_resolved_at') }}</th>

      <th class="border-left">{{ $t('craftsman.last_email_received') }}</th>
    </tr>
    </thead>
    <tbody>
    <loading-indicator-table-body v-if="isLoading" />
    <tr v-else-if="orderedCraftsmenWithStatistics.length === 0">
      <td colspan="99">
        <p class="text-center">{{ $t('_view.no_craftsmen') }}</p>
      </td>
    </tr>
    <tr v-else v-for="cws in orderedCraftsmenWithStatistics" @click.stop="toggleSelectedCraftsman(cws.craftsman)"
        class="clickable">
      <td class="w-minimal">
        <custom-checkbox>
          <input
              class="form-check-input" type="checkbox"
              v-model="selectedCraftsmen"
              :value="cws.craftsman">
        </custom-checkbox>
      </td>
      <td>{{ cws.craftsman.trade }}</td>
      <td>{{ cws.craftsman.company }}</td>
      <td>
        <toggle-can-edit @click.prevent.stop="" :craftsman="cws.craftsman" />
      </td>
      <td>
        <a @click.stop=""  :href="cws.craftsman.resolveUrl" target="_blank">
          <font-awesome-icon :icon="['fal', 'user']" />
        </a>
      </td>

      <td class="border-left">
        <issue-summary-badges :summary="cws.statistics.issueSummary" />
      </td>
      <td>
        <date-human-readable :value="cws.statistics.nextDeadline" />
        <br />
        <span v-if="cws.statistics.issueOverdueCount" class="badge bg-danger">
          {{ cws.statistics.issueOverdueCount }} {{ $t('issue.state.overdue') }}
        </span>
      </td>

      <td class="border-left">
        <date-time-human-readable :value="cws.statistics.lastVisitOnline" />
        <br />
        <span v-if="cws.statistics.issueUnreadCount" class="badge bg-secondary">
          {{ cws.statistics.issueUnreadCount }} {{ $t('issue.state.unread') }}
        </span>
      </td>
      <td>
        <date-time-human-readable :value="cws.statistics.lastIssueResolved" />
      </td>

      <td class="border-left">
        <date-time-human-readable :value="cws.statistics.lastEmailReceived" />
      </td>
    </tr>
    </tbody>
    <caption class="caption-top">
      <div v-if="craftsmenWithIssuesOpen.length" class="form-check-inline me-4">
        <custom-checkbox id="issues-open-craftsmen"
                         :label="$t('_view.craftsmen.with_open_issues')"
                         @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesOpen)">
          <input class="form-check-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmenWithIssuesOpen, selectedCraftsmen)">
        </custom-checkbox>
      </div>
      <div v-if="craftsmenWithIssuesUnread.length" class="form-check-inline me-4">
        <custom-checkbox id="issues-unread-craftsmen"
                         :label="$t('_view.craftsmen.with_unread_issues')"
                         @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesUnread)">
          <input class="form-check-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmenWithIssuesUnread, selectedCraftsmen)">
        </custom-checkbox>
      </div>
      <div v-if="craftsmenWithIssuesOverdue.length" class="form-check-inline">
        <custom-checkbox id="issues-overdue-craftsmen"
                         :label="$t('_view.craftsmen.with_overdue_issues')"
                         @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesOverdue)">
          <input class="form-check-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmenWithIssuesOverdue, selectedCraftsmen)">
        </custom-checkbox>
      </div>
      <div class="float-end">
        {{ totalCraftsmen }} {{ $t('craftsman._plural') }}
      </div>
    </caption>
  </table>
</template>

<script>

import LoadingIndicatorTableBody from '../Library/View/LoadingIndicatorTableBody'
import CustomCheckbox from '../Library/FormInput/CustomCheckbox'
import TooltipNumber from '../Library/View/TooltipBadge'
import DateHumanReadable from '../Library/View/DateHumanReadable'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import { arraysAreEqual } from '../../services/algorithms'
import IssueSummaryBadges from './IssueSummaryBadges'
import ToggleCanEdit from '../Action/ToggleCanEdit'

export default {
  emits: ['selected'],
  components: {
    ToggleCanEdit,
    IssueSummaryBadges,
    DateTimeHumanReadable,
    DateHumanReadable,
    TooltipNumber,
    CustomCheckbox,
    LoadingIndicatorTableBody,
  },
  data () {
    return {
      selectedCraftsmen: [],
    }
  },
  props: {
    craftsmen: {
      type: Array,
      required: false
    },
    statistics: {
      type: Array,
      required: false
    },
  },
  computed: {
    isLoading: function () {
      return !this.craftsmen || !this.statistics
    },
    totalCraftsmen: function () {
      return this.craftsmen ? this.craftsmen.length : 0
    },
    orderedCraftsmenWithStatistics: function () {
      if (this.isLoading) {
        return []
      }

      const statisticsLookup = {}
      this.statistics.forEach(statistics => statisticsLookup[statistics['craftsman']] = statistics)
      const craftsmanWithStatistics = this.craftsmen.map(craftsman => ({
        craftsman,
        statistics: statisticsLookup[craftsman['@id']]
      }))

      return craftsmanWithStatistics.sort((a, b) => a.craftsman.trade.localeCompare(b.craftsman.trade))
    },
    craftsmenWithIssuesOpen: function () {
      return this.orderedCraftsmenWithStatistics.filter(cws => cws.statistics.issueSummary.openCount > 0)
          .map(cws => cws.craftsman)
    },
    craftsmenWithIssuesUnread: function () {
      return this.orderedCraftsmenWithStatistics.filter(cws => cws.statistics.issueUnreadCount > 0)
          .map(cws => cws.craftsman)
    },
    craftsmenWithIssuesOverdue: function () {
      return this.orderedCraftsmenWithStatistics.filter(cws => cws.statistics.issueOverdueCount > 0)
          .map(cws => cws.craftsman)
    }
  },
  methods: {
    setOrderBy (property, order) {
      this.orderBy = {
        property,
        order
      }
    },
    toggleSelectedCraftsmen (toggleArray) {
      if (this.entityListsAreEqual(toggleArray, this.selectedCraftsmen)) {
        this.selectedCraftsmen = []
      } else {
        this.selectedCraftsmen = [...toggleArray]
      }
    },
    toggleSelectedCraftsman (toggleCraftsman) {
      if (this.selectedCraftsmen.includes(toggleCraftsman)) {
        this.selectedCraftsmen = this.selectedCraftsmen.filter(c => c !== toggleCraftsman)
      } else {
        this.selectedCraftsmen = [...this.selectedCraftsmen, toggleCraftsman]
      }
    },
    entityListsAreEqual (array1, array2) {
      return arraysAreEqual(array1, array2, (a, b) => {
        return a['@id'].localeCompare(b['@id'])
      })
    }
  },
  watch: {
    isLoading: function () {
      if (!this.isLoading) {
        this.selectedCraftsmen = [...this.craftsmenWithIssuesOpen]
      }
    },
    selectedCraftsmen: function () {
      this.$emit('selected', this.selectedCraftsmen)
    }
  }
}
</script>


<style scoped>
.custom-checkbox {
  margin-right: -0.5em;
}
</style>
