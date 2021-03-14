<template>
  <button v-if="!filter" class="btn btn-primary" :disabled="posting"
          @click="generateFilter">
    {{ $t('_action.generate_issues_link.title') }}
  </button>

  <div class="input-group" v-if="filter">
    <input type="text" class="form-control" readonly :value="filterUrl">
    <div class="input-group-append">
      <div class="input-group-text">
        <a :href="filterUrl" target="_blank">
          <font-awesome-icon :icon="['fal', 'external-link']" />
        </a>
      </div>
    </div>
  </div>
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
      filter: null
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
    filterEntity: function () {
      return Object.assign({}, this.filterConfiguration, filterTransformer.filterToFilterEntity(this.query, this.constructionSite))
    },
    filterUrl: function () {
      if (!this.filter) {
        return null;
      }

      return window.location.origin + this.filter.filteredUrl
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
      api.postFilter(this.filterEntity, this.$t('_action.generate_issues_link.generated'))
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
