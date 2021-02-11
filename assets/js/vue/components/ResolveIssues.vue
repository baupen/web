<template>
  <div class="mb-5">
    <p class="alert alert-info">
      {{ $t('resolve.help') }}
    </p>

    <loading-indicator-secondary :spin="isLoading">
      <p v-if="groupCountSum === 0" class="alert alert-success">
        {{ $t('resolve.thanks') }}
      </p>

      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body p-2">
              <div class="table-responsive">
                <maps-resolve-table
                    :map-containers="flatMaps" :map-groups="mapGroups"
                    @show="showMapContainer($event)" @showMultiple="showMultipleMapContainers($event)" />
              </div>
            </div>
          </div>
        </div>
      </div>
      <issues-resolve-masonry class="mt-5"
          v-for="mapContainer in shownMapContainers" :key="mapContainer.entity['@id']" :ref="mapContainer.entity['@id']"
          :map="mapContainer.entity" :map-context="getMapContext(mapContainer)"
          :craftsman="craftsman" :construction-site="constructionSite" :construction-managers="constructionManagers" />
    </loading-indicator-secondary>
  </div>
</template>

<script>
import { api, iriToId } from '../services/api'
import LoadingIndicatorSecondary from './Library/View/LoadingIndicatorSecondary'
import IssuesResolveMasonry from './View/MapIssuesResolveMasonry'
import { mapTransformer } from '../services/transformers'
import MapsResolveTable from './View/MapsResolveTable'

export default {
  components: {
    MapsResolveTable,
    IssuesResolveMasonry,
    LoadingIndicatorSecondary,
  },
  data () {
    return {
      constructionManagers: null,
      maps: null,
      mapGroups: null,

      shownMapContainers: []
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
      return parents.map(p => p.name).join(" > ");
    },
    showMultipleMapContainers: function(mapContainers) {
      mapContainers.forEach(mc => this.showMapContainer(mc, false))
    },
    showMapContainer: function(mapContainer, scrollTo = true) {
      this.shownMapContainers.push(mapContainer);

      if (!scrollTo) {
        return
      }

      this.$nextTick(() => {
        const newDisplayedMap = this.$refs[mapContainer.entity['@id']].$el;
        const newDisplayedMapOffset = $(newDisplayedMap).offset().top

        $('html').animate({
          scrollTop: newDisplayedMapOffset
        });
      })
    }
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
  },
  mounted () {
    api.getConstructionManagers(this.constructionSite)
        .then(constructionManagers => {
          this.constructionManagers = constructionManagers
        })

    api.getMaps(this.constructionSite)
        .then(maps => this.maps = maps)

    api.getIssuesGroup(this.constructionSite, 'map', {craftsman: iriToId(this.craftsman['@id']), state: 2})
        .then(groups => this.mapGroups = groups)
  }
}
</script>
