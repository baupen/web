<template>
  <div class="mb-5">
    <div class="row g-4">
      <div class="col-md-auto">
        <construction-site-view class="limited-width" :construction-site="constructionSite"/>
      </div>
      <div class="col-md-auto">
        <div class="card limited-width">
          <div class="card-body limited-height">
            <div class="loading-center" v-if="!constructionManagers || !feedEntries">
              <loading-indicator-secondary/>
            </div>
            <feed v-else :construction-managers="constructionManagers" :craftsmen="[craftsman]"
                  :feed-entries="feedEntries"/>
          </div>
          <div class="card-footer">
            <b v-if="!isLoading">{{ $tc('resolve.total_open', groupCountSum) }}</b>
          </div>
        </div>
      </div>
    </div>

    <hr class="my-4"/>

    <loading-indicator-secondary :spin="isLoading">
      <p v-if="groupCountSum === 0" class="alert alert-success">
        {{ $t('resolve.thanks') }}
      </p>

      <template v-else>
        <div class="row g-4">
          <div class="col-md-auto">
            <maps-resolve-table
                :maps="maps" :issues-group-by-map="issuesGroupByMap"
                :craftsman="craftsman" :construction-site="constructionSite"
                @scroll-to-map="scrollToMap($event)"/>
          </div>
          <div class="col-md-auto">
            <div class="card limited-width mb-2">
              <div class="card-body">
                <export-issues-report-view
                    :construction-site="constructionSite" :maps="maps"
                    :query="issuesQuery" :query-result-size="groupCountSum"
                    :default-report-configuration="reportConfiguration"/>
              </div>
            </div>
          </div>
        </div>

        <hr class="my-4"/>

        <p class="alert alert-info">
          {{ $t('resolve.help') }}
        </p>

        <div class="row g-4 mt-4">
          <div class="col-12" v-for="mapContainer in mapContainers" :key="mapContainer.entity['@id']">

            <issues-resolve-view
                ref="maps"
                :map="mapContainer.entity" :map-parent-names="mapContainer.mapParentNames"
                :craftsman="craftsman" :construction-site="constructionSite"
                :construction-managers="constructionManagers"/>
          </div>
        </div>
      </template>
    </loading-indicator-secondary>
  </div>
</template>

<script>
import {api, iriToId} from '../services/api'
import LoadingIndicatorSecondary from './Library/View/LoadingIndicatorSecondary'
import IssuesResolveView from './View/MapIssuesResolveView'
import {mapTransformer} from '../services/transformers'
import MapsResolveTable from './View/MapsResolveTable'
import GenerateIssuesReport from './Action/GenerateIssuesReport'
import ExportIssuesReportView from './Action/ExportIssuesReportView'
import {constructionSiteFormatter} from '../services/formatters'
import ConstructionSiteView from './View/ConstructionSiteView'
import Feed from './View/Feed'

export default {
  components: {
    Feed,
    ConstructionSiteView,
    ExportIssuesReportView,
    GenerateIssuesReport,
    MapsResolveTable,
    IssuesResolveView,
    LoadingIndicatorSecondary,
  },
  data() {
    return {
      constructionManagers: null,
      maps: null,
      issuesGroupByMap: null,
      feedEntries: null
    }
  },
  props: {
    craftsman: {
      type: Object,
      required: true
    },
    constructionSite: {
      type: Object,
      required: true
    }
  },
  methods: {
    scrollToMap: function (map) {
      this.$nextTick(() => {
        const newDisplayedMap = this.$refs.maps.find(mapRef => mapRef.map['@id'] === map['@id'])
        if (!newDisplayedMap || !newDisplayedMap.$el) {
          return
        }

        const element = newDisplayedMap.$el
        const newDisplayedMapOffset = element.getBoundingClientRect().top + window.scrollY

        window.scrollTo({
          top: newDisplayedMapOffset,
          behavior: 'smooth' // This enables smooth scrolling
        });
      })
    },
  },
  computed: {
    groupCountSum: function () {
      return this.issuesGroupByMap.reduce((sum, entry) => sum + entry.count, 0)
    },
    address: function () {
      return constructionSiteFormatter.address(this.constructionSite)
    },
    isLoading: function () {
      return !this.maps || !this.issuesGroupByMap
    },
    mapContainers: function () {
      return mapTransformer.orderedListWithIssuesGroups(this.maps, this.issuesGroupByMap, mapTransformer.PROPERTY_MAP_PARENT_NAMES)
          .filter(container => container.issueCount > 0)
    },
    craftsmanQuery: function () {
      return {
        craftsman: iriToId(this.craftsman['@id']),
        isDeleted: false
      }
    },
    issuesQuery: function () {
      return Object.assign({}, this.craftsmanQuery, {
        state: 2,
      })
    },
    reportConfiguration: function () {
      return {
        withRenders: true,
        withImages: true,
        tableByCraftsman: false,
        tableByMap: true
      }
    }
  },
  mounted() {
    api.getConstructionManagers(this.constructionSite)
        .then(constructionManagers => {
          this.constructionManagers = constructionManagers
        })

    api.getMaps(this.constructionSite)
        .then(maps => this.maps = maps)

    api.getIssuesGroup(this.constructionSite, 'map', this.issuesQuery)
        .then(groups => this.issuesGroupByMap = groups)

    api.getIssuesFeedEntries(this.constructionSite, 0, this.craftsmanQuery)
        .then(issuesFeedEntries => {
          this.feedEntries = issuesFeedEntries
        })
  }
}
</script>

<style scoped>
.limited-width {
  max-width: 500px;
}

.limited-height {
  max-height: 24.5em;
  overflow-y: auto;
}
</style>
