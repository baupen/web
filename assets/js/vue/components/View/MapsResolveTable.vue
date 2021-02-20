<template>
  <div class="table-responsive">
    <table class="table table-striped table-bordered table-sm-small table-hover">
      <thead>
      <tr>
        <th colspan="99">{{ $t("view.issues_by_map")}}</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="mapContainer in mapContainerWithGroups" :key="mapContainer.container.entity['@id']">
        <td class="text-nowrap">
          <span :class="'space-'+mapContainer.container.level"></span>
          <a v-if="mapContainer.hasIssues" href="#" @click="$emit('scroll-to', mapContainer.container)">
            {{ mapContainer.container.entity.name }}
          </a>
          <template v-else>
            {{ mapContainer.container.entity.name }}
          </template>
          <span v-if="mapContainer.group" class="badge badge-secondary ml-1">
            {{ mapContainer.group.count }}
          </span>
          <template v-if="isOverdue(mapContainer)">
            <span class="badge badge-danger">
              {{ $t('issue.state.overdue') }}
            </span>
          </template>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import DateHumanReadable from '../Library/View/DateHumanReadable'
import ButtonWithModal from '../Library/Behaviour/ButtonWithModal'
import MapRenderLightbox from './MapRenderLightbox'

export default {
  emits: ['scroll-to'],
  components: {
    MapRenderLightbox,
    ButtonWithModal,
    DateHumanReadable
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    craftsman: {
      type: Object,
      required: true
    },
    mapContainers: {
      type: Array,
      required: true
    },
    mapGroups: {
      type: Array,
      required: true
    }
  },
  methods: {
    isOverdue: function (container) {
      if (!container.group) {
        return false
      }

      return Date.now() > new Date(container.group.maxDeadline)
    }
  },
  computed: {
    maxDeadline: function () {
      const orderedDeadlines = this.mapContainerWithGroups
          .filter(mc => mc.group)
          .map(mc => mc.group.maxDeadline)
          .sort()

      if (!orderedDeadlines.length) {
        return null
      }

      return orderedDeadlines[0]
    },
    loadableMapContains: function () {
      return this.mapContainerWithGroups.filter(mc => mc.canLoad)
    },
    mapContainerWithGroups: function () {
      let groupLookup = {}
      this.mapGroups.forEach(mg => groupLookup[mg.entity] = mg)

      let mapContainerWithGroups = []
      let dirtyLevel = -1
      for (let i = this.mapContainers.length - 1; i >= 0; i--) {
        let mapContainer = this.mapContainers[i]

        const group = groupLookup[mapContainer.entity['@id']]

        const hasIssues = group && group.count > 0
        if (hasIssues) {
          dirtyLevel = mapContainer.level
        }

        // ensures parents are displayed too
        const show = dirtyLevel === mapContainer.level
        if (show) {
          dirtyLevel--
        }

        const mapContainerWithGroup = {
          group,
          show,
          hasIssues,
          container: mapContainer
        }

        mapContainerWithGroups.push(mapContainerWithGroup)
      }

      mapContainerWithGroups = mapContainerWithGroups.reverse()

      return mapContainerWithGroups.filter(mc => mc.show)
    },
  },
}
</script>

<style scoped="true" lang="scss">
@for $i from 1 through 10 {
  .space-#{$i} {
    display: inline-block;
    width: $i*1em;
  }
}

</style>
