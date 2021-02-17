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
            :map-containers="flatMaps" :map-groups="mapGroups"
            :craftsman="craftsman" :construction-site="constructionSite"
            @scroll-to="scrollTo($event)" />

        <issues-resolve-view
            class="mt-5" :ref="mapContainer.entity['@id']"
            v-for="mapContainer in mapsWithIssues" :key="mapContainer.entity['@id']"
            :map="mapContainer.entity" :map-context="getMapContext(mapContainer)"
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

export default {
  components: {
    MapsResolveTable,
    IssuesResolveView,
    LoadingIndicatorSecondary,
  },
  data () {
    return {
      constructionManagers: null,
      maps: null,
      mapGroups: null
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
    getMapContext: function (mapContainer) {
      const parents = this.mapParentsLookup[mapContainer.entity['@id']]
      if (!parents.length) {
        return ''
      }

      return parents.map(p => p.name)
          .join(' > ') + ' > '
    },
    scrollTo: function (mapContainer) {
      this.$nextTick(() => {
        const newDisplayedMap = this.$refs[mapContainer.entity['@id']].$el
        if (!newDisplayedMap) {
          return;
        }

        const newDisplayedMapOffset = $(newDisplayedMap).offset().top

        $('html').animate({scrollTop: newDisplayedMapOffset})
      })
    },
  },
  computed: {
    groupCountSum: function () {
      return this.mapGroups.reduce((sum, entry) => sum + entry.count, 0)
    },
    isLoading: function () {
      return !this.maps || !this.mapGroups
    },
    mapParentsLookup: function () {
      return mapTransformer.parentsLookup(this.maps)
    },
    flatMaps: function () {
      return mapTransformer.flatHierarchy(this.maps)
    },
    mapsWithIssues: function () {
      let groupLookup = {}
      this.mapGroups.forEach(mg => groupLookup[mg.entity] = mg)

      return this.flatMaps.filter(m => {
        const group = groupLookup[m.entity['@id']]
        return group && group.count > 0
      })
    }
  },
  mounted () {
    api.getConstructionManagers(this.constructionSite)
        .then(constructionManagers => {
          this.constructionManagers = constructionManagers
        })

    api.getMaps(this.constructionSite)
        .then(maps => this.maps = maps)

    api.getIssuesGroup(this.constructionSite, 'map', {
      craftsman: iriToId(this.craftsman['@id']),
      state: 2
    }).then(groups => this.mapGroups = groups)
  }
}
</script>
