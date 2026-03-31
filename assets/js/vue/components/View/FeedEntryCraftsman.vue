<template>
  <span>
    <b>
      {{ this.craftsman.company }}
    </b>
    {{ $t('_view.feed.entries.start') }}
    {{ text }}<template v-if="commentedIssueIds">
    (laden)
  </template>.
    <span class="text-secondary">
      -
      <date-human-readable :value="date"/>
    </span>
  </span>
</template>

<script>

import DateHumanReadable from '../Library/View/DateHumanReadable'
import {constructionManagerFormatter} from '../../services/formatters'

export default {
  components: {DateHumanReadable},
  props: {
    craftsman: {
      type: Object,
      required: true
    },
    date: {
      type: String,
      required: true
    },
    entries: {
      type: Array,
      required: true
    },
  },
  computed: {
    commentedIssueIds: function () {
      return this.entries.find(entry => entry.type === 'UNIQUE_ISSUES_COMMENTED_COUNT')?.issueIds
    },
    textBlocks: function () {
      return this.entries.map(entry => {
        if (entry.type === 'UNIQUE_ISSUES_COMMENTED_COUNT') {
          return this.$tc('_view.feed.entries.has_commented_issues', entry.count)
        } else if (entry.type === 'VISITED_WEBPAGE') {
          return this.$t('_view.feed.entries.has_visited_webpage', entry.count)
        } else if (entry.type === 'STATUS_SET_COUNT') {
          switch (entry.payload) {
            case 'RESOLVED':
              return this.$tc('_view.feed.entries.has_resolved', entry.count)
            default:
              return null
          }
        }

        return null
      }).filter(t => t !== null)
    },
    text: function () {
      let parts = [...this.textBlocks]
      if (parts.length > 1) {
        let lastEntry = parts.pop();
        let secondLastEntry = parts.pop()
        parts.push(`${secondLastEntry} ${this.$tc('_view.feed.entries.glue_end')} ${lastEntry}`)
      }

      return parts.join(', ')
    }
  }
}
</script>
