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
      <th class="w-minimal"></th>
      <order-indicator-th property="company" :order-by="orderBy" @order="orderBy = $event">
        {{ $t('craftsman.company') }}
      </order-indicator-th>
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
      <td class="w-minimal">
        <checkbox v-model="selectedCraftsmen" :value="cws.craftsman" :id="'craftsman2-'+cws.craftsman['@id']"/>
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
import OrderIndicatorTh from "./Table/OrderIndicatorTh";

const defaultOrderBy = {property: 'company', asc: true}

export default {
  components: {OrderIndicatorTh, HumanReadableDateTime, HumanReadableDate, IssueStatistics, Checkbox},
  data() {
    return {
      orderBy: null,
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

      const orderBy = this.orderBy ?? defaultOrderBy;
      if (!orderBy.asc) {
        return craftsmanWithStatistics.sort((a, b) => b.craftsman[orderBy.property].localeCompare(a.craftsman[orderBy.property]))
      }
      return craftsmanWithStatistics.sort((a, b) => a.craftsman[orderBy.property].localeCompare(b.craftsman[orderBy.property]))
    }
  },
  methods: {
    setOrderBy(property, order) {
      this.orderBy = {property, order}
    },
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
