<template>
  <h3 v-if="showTitle">{{ $t('_action.export_issues_link.title') }}</h3>
  <p class="alert alert-info">
    {{ $t('_action.export_issues_link.help') }}
  </p>

  <issue-link-form :template="filter" @update="filter = $event" />

  <generate-issues-filter
      :construction-site="constructionSite" :filter-configuration="filter"
      :query="query" />
</template>

<script>

import IssueLinkForm from '../Form/IssueLinkForm'
import GenerateIssuesFilter from './GenerateIssuesLink'

export default {
  components: {
    IssueLinkForm,
    GenerateIssuesFilter
  },
  data () {
    return {
      filter: {
        accessAllowedBefore: null
      },
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    query: {
      type: Object,
      required: true
    },
    showTitle: {
      type: Boolean,
      default: true
    }
  },
}
</script>
