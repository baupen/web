<template>
  <table class="table table-striped table-hover">
    <thead>
    <tr class="bg-light">
      <th colspan="3">{{ $t('craftsman._name') }}</th>
      <th class="border-left"  colspan="2">{{ $t('issue._plural') }}</th>
      <th class="border-left"  colspan="3">{{ $t('dispatch.craftsmen_table.last_activity') }}</th>
    </tr>
    <tr class="text-secondary">
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
    <tr v-for="craftsman in this.orderedCraftsmenWithStatistics">
      <td>{{ craftsman.company }}</td>
      <td>{{ craftsman.contactName }}</td>
      <td>{{ craftsman.trade }}</td>

      <td class="border-left">
        <issue-statistics :statistics="craftsman.statistics" />
      </td>
      <td>
        <human-readable-date :value="craftsman.statistics.next_deadline" />
      </td>

      <td class="border-left">
        <human-readable-date :value="craftsman.statistics.last_email_received" />
      </td>
      <td>
        <human-readable-date-time :value="craftsman.statistics.last_visit_online" />
      </td>
      <td>
        <human-readable-date-time :value="craftsman.statistics.last_issue_resolved" />
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>

import IssueStatistics from "./CraftsmanStatisticsTable/IssueStatistics";
import HumanReadableDate from "./Shared/HumanReadableDate";
import HumanReadableDateTime from "./Shared/HumanReadableDateTime";
export default {
  components: {HumanReadableDateTime, HumanReadableDate, IssueStatistics},
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
      const statisticsLookup = {};
      this.statistics.forEach(s => statisticsLookup[s["craftsman"]] = s)
      const craftsmanWithStatistics = this.craftsmen.map(c => Object.assign({statistics: statisticsLookup[c['@id']]}, c));
      console.log(statisticsLookup)
      console.log(this.craftsmen)
      console.log(craftsmanWithStatistics)
      return craftsmanWithStatistics.sort((a,b) => a.company.localeCompare(b.company))
    }
  },
}
</script>
