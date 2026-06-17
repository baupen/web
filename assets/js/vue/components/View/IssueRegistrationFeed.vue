<template>
  <template v-if="issueGroups.length > 0">
    <template v-for="(entry, index) in issueGroups" :key="index">
      <hr v-if="index !== 0" />

      <span>
        {{ getConstructionManagerName(entry.registeredBy) }}
        {{$t('_view.feed.entries.has_registered', {count: entry.count})}}
      </span>
      <span class="text-secondary">
        -
        <date-human-readable :value="entry.date" />
      </span>
    </template>
  </template>
  <span v-else><i>{{ $t('_view.feed.no_entries') }}</i></span>
</template>
<script>

import DateHumanReadable from "../Library/View/DateHumanReadable.vue";
import {constructionManagerFormatter} from "../../domain/formatters";

export default {
  components: {
    DateHumanReadable,
  },
  props: {
    constructionManagers: {
      type: Array,
      required: true
    },
    issues: {
      type: Array,
      required: true
    }
  },
  computed: {
    issueGroups: function () {
      const groups = {}
      this.issues.forEach(issue => {
        if (!issue.registeredAt || !issue.registeredBy) {
          return
        }

        const day = new Date(issue.registeredAt).toISOString().substring(0, 10)
        const key = `${day}__${issue.registeredBy}`

        if (!groups[key]) {
          groups[key] = {
            registeredBy: issue.registeredBy,
            date: day,
            count: 0
          }
        }

        groups[key].count += 1
      })

      return Object.values(groups)
    },
  },
  methods: {
    getConstructionManagerName: function (constructionManagerIri) {
      const constructionManager = this.constructionManagers.find(c => c['@id'] === constructionManagerIri);
      if (!constructionManager) {
        return null;
      }

      return constructionManagerFormatter.name(constructionManager);
    }
  }
}
</script>
