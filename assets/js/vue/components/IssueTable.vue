<template>
  <table class="table table-striped-2 table-hover border">
    <thead>
    <tr class="bg-light">
      <th class="w-minimal"></th>
      <th colspan="6">{{ $t('issue._name') }}</th>
      <th class="border-left" colspan="2">{{ $t('issue_table.last_activity') }}</th>
    </tr>
    <tr class="text-secondary">
      <th class="w-minimal">
        <custom-checkbox @click.prevent="toggleSelectedIssues(orderedIssues)">
          <input class="custom-control-input" type="checkbox"
                 :checked="arraysAreEqual(orderedIssues, selectedIssues)">
        </custom-checkbox>
      </th>
      <th class="w-minimal"></th>
      <th class="w-minimal"></th>
      <th class="w-minimal"></th>
      <th>{{ $t('issue.description') }}</th>
      <th>{{ $t('craftsman.name') }}</th>
      <th>{{ $t('issue.deadline') }}</th>
      <th class="border-left">{{ $t('issue.status') }}</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="issue in orderedIssues" @click.stop="toggleSelectedIssue(issue)" class="clickable">
      <td class="w-minimal">
        <custom-checkbox>
          <input
              class="custom-control-input" type="checkbox"
              v-model="selectedIssues"
              :value="issue">
        </custom-checkbox>
      </td>
      <td>{{ issue.number }}</td>
      <td>
        <icon-with-tooltip :icon="issue.isMarked ? ['fas', 'star'] : ['fal', 'star']" :tooltip-title="$t('issue.is_marked')" /> <br/>
        <icon-with-tooltip :icon="issue.wasAddedWithClient ? ['fas', 'user-check'] : ['fal', 'user-check']" :tooltip-title="$t('issue.was_added_with_client')" />
      </td>
      <td>
        <img v-if="issue.imageUrl" :src="issue.imageUrl" :alt="'thumbnail of ' + issue.number">
      </td>
      <td>{{ issue.description }}</td>
      <td>
        {{ issue.craftsman.company }}<br/>
        {{ issue.craftsman.trade }}
      </td>
      <td>
        <human-readable-date :value="issue.deadline" />
      </td>
      <td class="border-left">
        <span>{{ $t('issue_table.state_archived_by', {'state': $t('issue.state.created'), 'subject': issue.createdBy.givenName + ' ' + issue.createdBy.familyName }) }}</span>
        <human-readable-date-time :value="issue.createdAt" />
      </td>
    </tr>
    </tbody>
    <caption>
      <div v-if="issuesWithoutDescription.length" class="form-check form-check-inline">
        <custom-checkbox id="issues-without-description"
                         :label="$t('issue_table.without_description')"
                         @click.prevent="toggleSelectedIssues(issuesWithoutDescription)" >
          <input class="custom-control-input" type="checkbox"
                 :checked="arraysAreEqual(issuesWithoutDescription, selectedIssues)">
        </custom-checkbox>
      </div>
      <div v-if="issuesWithoutCraftsman.length" class="form-check form-check-inline">
        <custom-checkbox id="issues-without-craftsman"
                         :label="$t('issue_table.without_craftsman')"
                         @click.prevent="toggleSelectedIssues(issuesWithoutCraftsman)" >
          <input class="custom-control-input" type="checkbox"
                 :checked="arraysAreEqual(issuesWithoutCraftsman, selectedIssues)">
        </custom-checkbox>
      </div>
      <div v-if="issuesWithoutDeadline.length" class="form-check form-check-inline">
        <custom-checkbox id="issues-without-deadline"
                         :label="$t('issue_table.without_deadline')"
                         @click.prevent="toggleSelectedIssues(issuesWithoutDeadline)" >
          <input class="custom-control-input" type="checkbox"
                 :checked="arraysAreEqual(issuesWithoutDeadline, selectedIssues)">
        </custom-checkbox>
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
import IconWithTooltip from "./View/IconWithTooltip";

export default {
  emits: ['selected'],
  components: {
    IconWithTooltip,
    CustomCheckbox,
    NumberWithTooltip,
    OrderedTableHead,
    HumanReadableDateTime,
    HumanReadableDate
  },
  data () {
    return {
      selectedIssues: [],
    }
  },
  props: {
    issues: {
      type: Array,
      required: true
    },
    proposedSelectedIssues: {
      type: Array
    }
  },
  computed: {
    orderedIssues: function () {
      return this.issues.sort((a, b) => a.number - b.number)
    },
    issuesWithoutDescription: function () {
      return this.issues.filter(i => i.description === null || i.description === "")
    },
    issuesWithoutDeadline: function () {
      return this.issues.filter(i => i.deadline === null)
    },
    issuesWithoutCraftsman: function () {
      return this.issues.filter(i => i.craftsman === null)
    }
  },
  methods: {
    toggleSelectedIssues (toggleArray) {
      if (this.arraysAreEqual(toggleArray, this.selectedIssues)) {
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
    arraysAreEqual (array1, array2) {
      return arraysAreEqual(array1, array2)
    }
  },
  watch: {
    proposedSelectedIssues: function () {
      this.selectedIssues = this.proposedSelectedIssues
    },
    selectedIssues: function () {
      this.$emit('selected', this.selectedIssues)
    }
  },
  mounted() {
    if (this.proposedSelectedIssues) {
      this.selectedIssues = this.proposedSelectedIssues
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
