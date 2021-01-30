<template>
  <table class="table table-striped-2 table-hover border">
    <thead>
    <tr class="bg-light">
      <th class="w-minimal"></th>
      <th colspan="2">{{ $t('craftsman._name') }}</th>
      <th class="border-left" colspan="2">{{ $t('issue._plural') }}</th>
      <th class="border-left" colspan="2">{{ $t('dispatch.craftsmen_table.last_activity') }}</th>
      <th class="border-left"></th>
    </tr>
    <tr class="text-secondary">
      <th class="w-minimal">
        <custom-checkbox @click.prevent="toggleSelectedCraftsmen(craftsmen)">
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmen, selectedCraftsmen)">
        </custom-checkbox>
      </th>
      <th>{{ $t('craftsman.trade') }}</th>
      <th>{{ $t('craftsman.company') }}</th>

      <th class="border-left">{{ $t('view.count') }}</th>
      <th>{{ $t('craftsman.next_deadline') }}</th>

      <th class="border-left">{{ $t('craftsman.visited_webpage') }}</th>
      <th>{{ $t('craftsman.resolved_issue') }}</th>

      <th class="border-left">{{ $t('craftsman.received_email') }}</th>
    </tr>
    </thead>
    <tbody>
    <loading-indicator-table-body v-if="isLoading" />
    <tr v-else v-for="cws in orderedCraftsmenWithStatistics" @click.stop="toggleSelectedCraftsman(cws.craftsman)"
        class="clickable">
      <td class="w-minimal">
        <custom-checkbox>
          <input
              class="custom-control-input" type="checkbox"
              v-model="selectedCraftsmen"
              :value="cws.craftsman">
        </custom-checkbox>
      </td>
      <td>{{ cws.craftsman.trade }}</td>
      <td>
        {{ cws.craftsman.company }} <br/>
        <span class="text-secondary">{{ cws.craftsman.contactName }}</span>
      </td>

      <td class="border-left">
        <issue-summary-badges :summary="cws.statistics.issueSummary" />
      </td>
      <td>
        <date-human-readable :value="cws.statistics.nextDeadline" /><br/>
        <span v-if="cws.statistics.issueOverdueCount" class="badge badge-danger">
          {{ cws.statistics.issueOverdueCount }} {{$t('issue.state.overdue')}}
        </span>
      </td>

      <td class="border-left">
        <date-time-human-readable :value="cws.statistics.lastVisitOnline" /><br/>
        <span v-if="cws.statistics.issueUnreadCount" class="badge badge-warning">
          {{ cws.statistics.issueUnreadCount }} {{$t('issue.state.unread')}}
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
      <div v-if="craftsmenWithIssuesOpen.length" class="form-check form-check-inline mr-4">
        <custom-checkbox id="issues-open-craftsmen"
                         :label="$t('dispatch.craftsmen_table.with_open_issues')"
                         @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesOpen)">
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmenWithIssuesOpen, selectedCraftsmen)">
        </custom-checkbox>
      </div>
      <div v-if="craftsmenWithIssuesUnread.length" class="form-check form-check-inline mr-4">
        <custom-checkbox id="issues-unread-craftsmen"
                         :label="$t('dispatch.craftsmen_table.with_unread_issues')"
                         @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesUnread)">
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmenWithIssuesUnread, selectedCraftsmen)">
        </custom-checkbox>
      </div>
      <div v-if="craftsmenWithIssuesOverdue.length" class="form-check form-check-inline">
        <custom-checkbox id="issues-overdue-craftsmen"
                         :label="$t('dispatch.craftsmen_table.with_overdue_issues')"
                         @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesOverdue)">
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmenWithIssuesOverdue, selectedCraftsmen)">
        </custom-checkbox>
      </div>
      <div class="float-right" v-if="!isLoading">
        {{ craftsmen.length }} {{ $t('craftsman._plural') }}
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

export default {
  emits: ['selected'],
  components: {
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
        this.selectedCraftsmen.push(toggleCraftsman)
      }
    },
    entityListsAreEqual (array1, array2) {
      return arraysAreEqual(array1, array2, (a, b) => {
        return a['@id'].localeCompare(b['@id'])
      })
    }
  },
  watch: {
    isLoading: function ()  {
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


<style scoped="true">
.table-striped-2 tbody tr:nth-of-type(2n) {
  background-color: rgba(0, 0, 0, 0.05);
}

.custom-checkbox {
  margin-right: -0.5em;
}
</style>
