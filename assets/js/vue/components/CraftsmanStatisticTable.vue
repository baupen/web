<template>
  <table class="table table-striped-2 table-hover border">
    <thead>
    <tr class="bg-light">
      <th></th>
      <th colspan="3">{{ $t('craftsman._name') }}</th>
      <th class="border-left" colspan="2">{{ $t('issue._plural') }}</th>
      <th class="border-left" colspan="3">{{ $t('dispatch.craftsmen_table.last_activity') }}</th>
    </tr>
    <tr class="text-secondary">
      <th>
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
    <tr v-for="cws in orderedCraftsmenWithStatistics">
      <td>
        <checkbox v-model="selectedCraftsmen" :value="cws.craftsman" :id="'craftsman2-'+cws.craftsman['@id']" />
      </td>
      <td>{{ cws.craftsman.company }}</td>
      <td>{{ cws.craftsman.contactName }}</td>
      <td>{{ cws.craftsman.trade }}</td>

      <td class="border-left">
        <issue-statistics :statistics="cws.statistics"/>
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
</template>

<script>

import IssueStatistics from "./CraftsmanStatisticsTable/IssueStatistics";
import HumanReadableDate from "./Shared/HumanReadableDate";
import HumanReadableDateTime from "./Shared/HumanReadableDateTime";
import Checkbox from "./Form/Checkbox";

export default {
  components: {HumanReadableDateTime, HumanReadableDate, IssueStatistics, Checkbox},
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
      const craftsmanWithStatistics = this.craftsmen.map(craftsman => ({ craftsman, statistics: statisticsLookup[craftsman['@id']]}));
      return craftsmanWithStatistics.sort((a, b) => a.craftsman.company.localeCompare(b.craftsman.company))
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
</style>
