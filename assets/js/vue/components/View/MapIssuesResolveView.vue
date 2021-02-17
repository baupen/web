<template>
  <div>
    <h2>
      <span class="text-secondary" v-if="mapContext"><small>{{ mapContext }}</small><br/></span>
      {{ map.name }}
    </h2>
    <div class="row">
      <div class="col-md-3">
        <map-render-lightbox
            :preview="true"
            :construction-site="constructionSite" :map="map" :craftsman="craftsman" :state="2" />
      </div>
      <div class="col-md-9">
        <div class="grid-item mb-2" v-for="issue in issues" :key="issue['@id']">
          <issue-resolve-card
              :issue="issue"
              :construction-site="constructionSite" :craftsman="craftsman" :map="map"
              :construction-managers="constructionManagers" />
        </div>
        <loading-indicator-secondary v-if="issuesLoading" />
        <p v-else class="text-center">
          <button class="btn btn-outline-secondary" v-if="notLoadedIssueCount > 0 && !issuesLoading"
                  @click="loadNextPage">
            {{ $tc('actions.show_more_issues', notLoadedIssueCount) }}
          </button>
        </p>
      </div>
    </div>
  </div>
</template>

<script>

import ConstructionSitesEnterMasonryCard from './ConstructionSiteEnterCard'
import IssueResolveCard from './IssueResolveCard'
import MapRenderLightbox from './MapRenderLightbox'
import { api, iriToId } from '../../services/api'
import LoadingIndicatorSecondary from '../Library/View/LoadingIndicatorSecondary'

export default {
  components: {
    LoadingIndicatorSecondary,
    MapRenderLightbox,
    IssueResolveCard,
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
