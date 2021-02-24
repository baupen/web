<template>
  <button v-if="!filter" class="btn btn-primary" :disabled="posting"
          @click="generateFilter">
    {{ $t('actions.generate_filter') }}
  </button>

  {{ filter.filteredUrl }}
</template>

<script>
import { api, iriToId, maxIssuesPerReport } from '../../services/api'
import { filterTransformer, mapTransformer } from '../../services/transformers'

export default {
  components: {},
  emits: ['generation-finished'],
  data () {
    return {
      posting: false,
      filter: false
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    filterConfiguration: {
      type: Object,
      required: true
    },
    query: {
      type: Object,
      required: true
    }
  },
  computed: {
    filterQuery: function () {
      return {
        'filter[accessAllowedBefore]': this.filterConfiguration.accessAllowedBefore,
      }
    },
    filterPost: function () {
      return Object.assign({}, this.filterConfiguration, filterTransformer.queryToFilterPost(this.query, this.constructionSite))
    }
  },
  methods: {
    setGenerationStatus: function (label, progress = 0) {
      this.reportGenerationStatus = {
        label,
        progress: Math.round(progress)
      }
    },
    generateFilter: function () {
      this.posting = true
      api.postFilter(this.filterPost, this.$t('actions.messages.filter_created'))
          .then(filter => {
            this.filter = filter
          })
    },
  },
  unmounted () {
    this.abortRequested = true
  }
}
</script>
