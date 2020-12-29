<template>
  <table class="table table-striped-2 table-hover border">
    <thead>
    <tr class="bg-light">
      <th class="w-minimal"></th>
      <th colspan="3">{{ $t('craftsman._name') }}</th>
      <th class="border-left" colspan="2">{{ $t('issue._plural') }}</th>
      <th class="border-left" colspan="3">{{ $t('dispatch.craftsmen_table.last_activity') }}</th>
    </tr>
    <tr class="text-secondary">
      <th class="w-minimal">
        <custom-checkbox @click.prevent="toggleSelectedCraftsmen(craftsmen)">
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmen, selectedCraftsmen)">
        </custom-checkbox>
      </th>
      <th>{{ $t('craftsman.company') }}</th>
      <th>{{ $t('craftsman.contact_name') }}</th>
      <th>{{ $t('craftsman.trade') }}</th>

      <th class="border-left">{{ $t('view.count') }}</th>
      <th>{{ $t('craftsman.next_deadline') }}</th>

      <th class="border-left">{{ $t('craftsman.received_email') }}</th>
      <th>{{ $t('craftsman.visited_webpage') }}</th>
      <th>{{ $t('craftsman.resolved_issue') }}</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="cws in orderedCraftsmenWithStatistics" @click.stop="toggleSelectedCraftsman(cws.craftsman)"
        class="clickable">
      <td class="w-minimal">
        <custom-checkbox>
          <input
              class="custom-control-input" type="checkbox"
              v-model="selectedCraftsmen"
              :value="cws.craftsman">
        </custom-checkbox>
      </td>
      <td>{{ cws.craftsman.company }}</td>
      <td>{{ cws.craftsman.contactName }}</td>
      <td>{{ cws.craftsman.trade }}</td>

      <td class="border-left">
        <number-with-tooltip color-if-nonzero="danger" :value="cws.statistics.issueOverdueCount"
                             :tooltip-title="$t('issue.state.overdue')" />
        /
        <number-with-tooltip color-if-nonzero="warning" :value="cws.statistics.issueUnreadCount"
                             :tooltip-title="$t('issue.state.unread')" />
        /
        <number-with-tooltip color-if-nonzero="secondary" :value="cws.statistics.issueOpenCount"
                             :tooltip-title="$t('issue.state.open')" />
        /
        <number-with-tooltip color-if-nonzero="success" :value="cws.statistics.issueClosedCount"
                             :tooltip-title="$t('issue.state.closed')" />
      </td>
      <td>
        <human-readable-date :value="cws.statistics.nextDeadline" />
      </td>

      <td class="border-left">
        <human-readable-date-time :value="cws.statistics.lastEmailReceived" />
      </td>
      <td>
        <human-readable-date-time :value="cws.statistics.lastVisitOnline" />
      </td>
      <td>
        <human-readable-date-time :value="cws.statistics.lastIssueResolved" />
      </td>
    </tr>
    </tbody>
    <caption class="caption-top">
      <div v-if="craftsmenWithIssuesOpen.length" class="form-check form-check-inline">
        <custom-checkbox id="issues-open-craftsmen"
            :label="$t('dispatch.craftsmen_table.with_open_issues')"
            @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesOpen)" >
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmenWithIssuesOpen, selectedCraftsmen)">
        </custom-checkbox>
      </div>
      <div v-if="craftsmenWithIssuesUnread.length" class="ml-4 form-check form-check-inline">
        <custom-checkbox id="issues-unread-craftsmen"
                         :label="$t('dispatch.craftsmen_table.with_unread_issues')"
                         @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesUnread)" >
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmenWithIssuesUnread, selectedCraftsmen)">
        </custom-checkbox>
      </div>
      <div v-if="craftsmenWithIssuesOverdue.length" class="ml-4 form-check form-check-inline">
        <custom-checkbox id="issues-overdue-craftsmen"
                         :label="$t('dispatch.craftsmen_table.with_overdue_issues')"
                         @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesOverdue)" >
          <input class="custom-control-input" type="checkbox"
                 :checked="entityListsAreEqual(craftsmenWithIssuesOverdue, selectedCraftsmen)">
        </custom-checkbox>
      </div>
      <div class="float-right">
        {{craftsmen.length}} {{$t('craftsman._plural')}}
      </div>
    </caption>
  </table>
</template>

<script>

import HumanReadableDate from './View/HumanReadableDate'
import HumanReadableDateTime from './View/HumanReadableDateTime'
import OrderedTableHead from './View/OrderedTableHead'
import NumberWithTooltip from './View/NumberWithTooltip'
import { arraysAreEqual } from '../services/algorithms'
import CustomCheckbox from './Edit/Input/CustomCheckbox'

export default {
  emits: ['selected'],
  components: {
    CustomCheckbox,
    NumberWithTooltip,
    OrderedTableHead,
    HumanReadableDateTime,
    HumanReadableDate
  },
  data () {
    return {
      selectedCraftsmen: [],
    }
  },
  props: {
    craftsmen: {
      type: Array,
      required: true
    },
    statistics: {
      type: Array,
      required: true
    },
  },
  computed: {
    orderedCraftsmenWithStatistics: function () {
      const statisticsLookup = {}
      this.statistics.forEach(statistics => statisticsLookup[statistics['craftsman']] = statistics)
      const craftsmanWithStatistics = this.craftsmen.map(craftsman => ({
        craftsman,
        statistics: statisticsLookup[craftsman['@id']]
      }))

      return craftsmanWithStatistics.sort((a, b) => a.craftsman.company.localeCompare(b.craftsman.company))
    },
    craftsmenWithIssuesOpen: function () {
      return this.orderedCraftsmenWithStatistics.filter(cws => cws.statistics.issueOpenCount > 0)
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
    entityListsAreEqual(array1, array2) {
      return arraysAreEqual(array1, array2, (a, b) => {
        return a['@id'].localeCompare(b['@id'])
      })
    }
  },
  watch: {
    selectedCraftsmen: function () {
      this.$emit('selected', this.selectedCraftsmen)
    }
  },
  mounted () {
    this.selectedCraftsmen = [...this.craftsmenWithIssuesOpen]
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
