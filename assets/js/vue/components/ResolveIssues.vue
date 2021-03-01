<template>
  <div class="mb-5">
    <p class="alert alert-info">
      {{ $t('resolve.help') }}
    </p>

    <loading-indicator-secondary :spin="isLoading">
      <p v-if="groupCountSum === 0" class="alert alert-success">
        {{ $t('resolve.thanks') }}
      </p>

      <template v-else>
        <maps-resolve-table
            :maps="maps" :issues-group-by-map="issuesGroupByMap"
            :craftsman="craftsman" :construction-site="constructionSite"
            @scroll-to-map="scrollToMap($event)" />

        <div class="card limited-width mb-2">
          <div class="card-body">
            <h3>{{ $t('export_issues_button.export_type.report.name') }}</h3>
            <p>
              {{ $t('export_issues_button.export_type.report.help') }}
            </p>

            <generate-issues-report
                :construction-site="constructionSite" :maps="maps"
                :query="craftsmanQuery" :query-result-size="groupCountSum"
                :report-configuration="reportConfiguration" />
          </div>
        </div>

        <issues-resolve-view
            class="mt-4 mt-md-5" :ref="mapContainer.entity['@id']"
            v-for="mapContainer in mapContainers" :key="mapContainer.entity['@id']"
            :map="mapContainer.entity" :map-parent-names="mapContainer.mapParentNames"
            :craftsman="craftsman" :construction-site="constructionSite"
            :construction-managers="constructionManagers" />
      </template>
    </loading-indicator-secondary>
  </div>
</template>

<script>
import { api, iriToId } from '../services/api'
import LoadingIndicatorSecondary from './Library/View/LoadingIndicatorSecondary'
import IssuesResolveView from './View/MapIssuesResolveView'
import { mapTransformer } from '../services/transformers'
import MapsResolveTable from './View/MapsResolveTable'
import GenerateIssuesReport from './Action/GenerateIssuesReport'

export default {
  components: {
    GenerateIssuesReport,
    MapsResolveTable,
    IssuesResolveView,
    LoadingIndicatorSecondary,
  },
  data () {
    return {
      constructionManagers: null,
      maps: null,
      issuesGroupByMap: null
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
        const newDisplayedMap = this.$refs[map['@id']]
        if (!newDisplayedMap || !newDisplayedMap.$el) {
          return
        }

        const element = newDisplayedMap.$el
        const newDisplayedMapOffset = $(element).offset().top

        $('html').animate({ scrollTop: newDisplayedMapOffset })
      })
    },
  },
  computed: {
    groupCountSum: function () {
      return this.issuesGroupByMap.reduce((sum, entry) => sum + entry.issueCount, 0)
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
        state: 2,
        isDeleted: false
      }
    },
    reportConfiguration: function () {
      return {
        withImages: true,
        tableByCraftsman: false,
        tableByMap: true
      }
    }
  },
  mounted () {
    api.getConstructionManagers(this.constructionSite)
        .then(constructionManagers => {
          this.constructionManagers = constructionManagers
        })

    api.getMaps(this.constructionSite)
        .then(maps => this.maps = maps)

    api.getIssuesGroup(this.constructionSite, 'map', this.craftsmanQuery)
        .then(groups => this.issuesGroupByMap = groups)
  }
}
</script>

<style scoped="true">
.limited-width {
  max-width: 500px;
}
</style>
