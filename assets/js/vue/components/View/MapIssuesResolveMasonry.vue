<template>
  <div>
    <h2>
      <span class="text-secondary"><small>{{ mapContext }}</small></span>
      {{ map.name }}
    </h2>
    <masonry
        :cols="{default: 5, 1720: 4, 1290: 3, 860: 2, 430: 1}"
        :gutter="{default: '10px'}"
        class="mt-2">
      <div class="grid-item mb-2">
        <div class="card">
          <div class="card-body bg-light-gray p-2">
            <map-render-lightbox
                :preview="true"
                :construction-site="constructionSite" :map="map" :craftsman="craftsman" :state="2" />
          </div>
        </div>
      </div>
      <div class="grid-item mb-2" v-for="issue in issues" :key="issue['@id']">
        <issue-resolve-card
            :issue="issue"
            :construction-site="constructionSite" :craftsman="craftsman" :map="map"
            :construction-managers="constructionManagers" />
      </div>
      <div v-if="issuesLoading" class="grid-item mb-2">
        <loading-indicator-secondary />
      </div>
    </masonry>
    <p class="text-center">
      <button class="btn btn-outline-secondary" v-if="notLoadedIssueCount > 0 && !issuesLoading" @click="loadNextPage">
        {{ $tc('actions.show_more_issues', notLoadedIssueCount) }}
      </button>
    </p>
  </div>
</template>

<script>

import ConstructionSitesEnterMasonryCard from './ConstructionSiteEnterCard'
import Masonry from '../Library/Behaviour/Masonry'
import IssueResolveCard from './IssueResolveCard'
import { createEntityIdLookup } from '../../services/algorithms'
import { mapTransformer } from '../../services/transformers'
import MapRenderLightbox from './MapRenderLightbox'
import { api, iriToId } from '../../services/api'
import LoadingIndicatorSecondary from '../Library/View/LoadingIndicatorSecondary'

export default {
  components: {
    LoadingIndicatorSecondary,
    MapRenderLightbox,
    IssueResolveCard,
    Masonry,
    ConstructionSitesEnterMasonryCard
  },
  data () {
    return {
      issues: [],
      issuePage: 1,
      totalIssues: 0,
      issuesLoading: true,
    }
  },
  props: {
    constructionManagers: {
      type: Array,
      default: []
    },
    constructionSite: {
      type: Object,
      required: true
    },
    map: {
      type: Object,
      required: true
    },
    mapContext: {
      type: String,
      required: true
    },
    craftsman: {
      type: Object,
      required: true
    }
  },
  computed: {
    notLoadedIssueCount: function () {
      return this.totalIssues - this.issues.length
    },
    query: function () {
      return {
        map: iriToId(this.map['@id']),
        craftsman: iriToId(this.craftsman['@id']),
        isDeleted: false,
        state: 2
      }
    },
  },
  methods: {
    loadNextPage () {
      this.loadIssues(this.issuePage + 1)
    },
    loadIssues (page = 1) {
      this.issuesLoading = true

      let query = Object.assign({}, this.query, { page })

      api.getPaginatedIssues(this.constructionSite, query)
          .then(payload => {
            if (page === 1) {
              this.issues = payload.items
            } else {
              this.issues = this.issues.concat(payload.items)
            }
            this.totalIssues = payload.totalItems
            this.issuePage = page

            this.issuesLoading = false
          })
    }
  },
  mounted () {
    this.loadIssues()
  }
}
</script>
