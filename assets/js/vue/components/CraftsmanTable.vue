<template>
  <div>
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
          <raw-checkbox :id="'all-craftsmen'"
                        :checked="arraysAreEqual(craftsmen, selectedCraftsmen)"
                        @click.prevent="toggleSelectedCraftsmen(craftsmen)">
          </raw-checkbox>
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
          <checkbox @click.stop="" v-model="selectedCraftsmen" :value="cws.craftsman"
                    :id="'craftsman-'+cws.craftsman['@id']"/>
        </td>
        <td>{{ cws.craftsman.company }}</td>
        <td>{{ cws.craftsman.contactName }}</td>
        <td>{{ cws.craftsman.trade }}</td>

        <td class="border-left">
          <number-with-tooltip color-if-nonzero="secondary" :value="cws.statistics.issueOpenCount"
                               :tooltip-title="$t('issue.state.open')"/>
          /
          <number-with-tooltip color-if-nonzero="warning" :value="cws.statistics.issueUnreadCount"
                               :tooltip-title="$t('issue.state.unread')"/>
          /
          <number-with-tooltip color-if-nonzero="danger" :value="cws.statistics.issueOverdueCount"
                               :tooltip-title="$t('issue.state.overdue')"/>
          /
          <number-with-tooltip color-if-nonzero="success" :value="cws.statistics.issueClosedCount"
                               :tooltip-title="$t('issue.state.closed')"/>
        </td>
        <td>
          <human-readable-date :value="cws.statistics.next_deadline"/>
        </td>

        <td class="border-left">
          <human-readable-date :value="cws.statistics.last_email_received"/>
        </td>
        <td>
          <human-readable-date-time :value="cws.statistics.last_visit_online"/>
        </td>
        <td>
          <human-readable-date-time :value="cws.statistics.last_issue_resolved"/>
        </td>
      </tr>
      </tbody>
    </table>
    <div>
      <div v-if="craftsmenWithIssuesUnread.length" class="form-check form-check-inline">
        <raw-checkbox :id="'issues-open-craftsmen'"
                      :checked="arraysAreEqual(craftsmenWithIssuesOpen, selectedCraftsmen)"
                      @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesOpen)">
          {{ $t('dispatch.craftsmen_table.with_open_issues') }}
        </raw-checkbox>
      </div>
      <div v-if="craftsmenWithIssuesUnread.length" class="ml-4 form-check form-check-inline">
        <raw-checkbox :id="'issues-unread-craftsmen'"
                      :checked="arraysAreEqual(craftsmenWithIssuesUnread, selectedCraftsmen)"
                      @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesUnread)">
          {{ $t('dispatch.craftsmen_table.with_unread_issues') }}
        </raw-checkbox>
      </div>
      <div v-if="craftsmenWithIssuesOverdue.length" class="ml-4 form-check form-check-inline">
        <raw-checkbox :id="'issues-overdue-craftsmen'"
                      :checked="arraysAreEqual(craftsmenWithIssuesOverdue, selectedCraftsmen)"
                      @click.prevent="toggleSelectedCraftsmen(craftsmenWithIssuesOverdue)">
          {{ $t('dispatch.craftsmen_table.with_overdue_issues') }}
        </raw-checkbox>
      </div>
    </div>
  </div>
</template>

<script>

import HumanReadableDate from "./View/HumanReadableDate";
import HumanReadableDateTime from "./View/HumanReadableDateTime";
import Checkbox from "./Edit/Input/Checkbox";
import RawCheckbox from "./Edit/Input/RawCheckbox";
import OrderedTableHead from "./View/OrderedTableHead";
import NumberWithTooltip from "./View/NumberWithTooltip";
import {arraysAreEqual} from '../services/algorithms'

export default {
  components: {
    RawCheckbox, NumberWithTooltip, OrderedTableHead, HumanReadableDateTime, HumanReadableDate, Checkbox},
  data() {
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
      const statisticsLookup = {};
      this.statistics.forEach(statistics => statisticsLookup[statistics["craftsman"]] = statistics)
      const craftsmanWithStatistics = this.craftsmen.map(craftsman => ({
        craftsman,
        statistics: statisticsLookup[craftsman['@id']]
      }));

      return craftsmanWithStatistics.sort((a, b) => a.craftsman.company.localeCompare(b.craftsman.company))
    },
    craftsmenWithIssuesOpen: function () {
      return this.orderedCraftsmenWithStatistics.filter(cws => cws.statistics.issueOpenCount > 0).map(cws => cws.craftsman);
    },
    craftsmenWithIssuesUnread: function () {
      return this.orderedCraftsmenWithStatistics.filter(cws => cws.statistics.issueUnreadCount > 0).map(cws => cws.craftsman);
    },
    craftsmenWithIssuesOverdue: function () {
      return this.orderedCraftsmenWithStatistics.filter(cws => cws.statistics.issueOverdueCount > 0).map(cws => cws.craftsman);
    }
  },
  methods: {
    setOrderBy(property, order) {
      this.orderBy = {property, order}
    },
    toggleSelectedCraftsmen(toggleArray) {
      if (this.arraysAreEqual(toggleArray, this.selectedCraftsmen)) {
        this.selectedCraftsmen = [];
      } else {
        this.selectedCraftsmen = [...toggleArray];
      }
    },
    toggleSelectedCraftsman(toggleCraftsman) {
      if (this.selectedCraftsmen.includes(toggleCraftsman)) {
        this.selectedCraftsmen = this.selectedCraftsmen.filter(c => c !== toggleCraftsman);
      } else {
        this.selectedCraftsmen.push(toggleCraftsman)
      }
    },
    arraysAreEqual(array1, array2) {
      return arraysAreEqual(array1, array2);
    }
  },
  mounted() {
    this.selectedCraftsmen = [this.craftsmen[0]]
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
