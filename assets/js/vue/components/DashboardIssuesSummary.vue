<template>
  <div class="card-group shadow">
    <card-counter-animated
        color="primary"
        :target="issuesSummary.openCount" :description="$t('issue.state.open')"
        :href="registerOpenUrl" />
    <card-counter-animated
        color="warning"
        :target="issuesSummary.inspectableCount" :description="$t('issue.state.to_inspect')"
        :href="registerInspectUrl" />
    <card-counter-animated
        color="success"
        :target="issuesSummary.closedCount" :description="$t('issue.state.closed')"
        :href="registerClosedUrl" />
  </div>
  <p class="alert alert-info shadow" v-if="issuesSummary.newCount > 0">
    {{ $t('dashboard_issues_summary.new_issues') }}
    <a :href="foyerUrl">{{ $t('foyer.title') }}</a>
  </p>
</template>

<script>
import { api } from '../services/api'
import CardCounterAnimated from './Library/View/CardCounterAnimated'

export default {
  data () {
    return {
      issuesSummary: {
        openCount: 0,
        inspectableCount: 0,
        closedCount: 0,
      }
    }
  },
  components: {
    CardCounterAnimated
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  computed: {
    foyerUrl: function () {
      return api.currentFoyerUrl()
    },
    registerOpenUrl: function () {
      return api.currentRegisterUrl(2)
    },
    registerInspectUrl: function () {
      return api.currentRegisterUrl(4)
    },
    registerClosedUrl: function () {
      return api.currentRegisterUrl(8)
    },
  },
  mounted () {
    api.getIssuesSummary(this.constructionSite)
        .then(issuesSummary => this.issuesSummary = issuesSummary)
  }
}
</script>
