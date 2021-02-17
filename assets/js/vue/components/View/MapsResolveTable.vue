<template>
  <table class="table table-striped table-hover">
    <thead>
    <tr>
      <th class="w-minimal"></th>
      <th>{{$t('map._name')}}</th>
      <th>{{$t('issue._plural')}}</th>
      <th class="text-nowrap">{{$t('craftsman.next_deadline')}}</th>
      <th class="w-minimal">
        <a href="#" v-if="notYetLoadedMapContainers.length > 0" @click="loadAllContainers">
          {{ $t('actions.load_all_maps') }}
        </a>
      </th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="mapContainer in mapContainerWithGroups" :key="mapContainer.container.entity['@id']">
      <td>
        <map-render-lightbox
            :construction-site="constructionSite" :map="mapContainer.container.entity" :craftsman="craftsman" :state="2" />
      </td>
      <td class="text-nowrap">
        {{ '&nbsp;&nbsp;&nbsp;&nbsp;'.repeat(mapContainer.container.level) }}
        {{mapContainer.container.entity.name}}
      </td>
      <td>{{mapContainer.group ? mapContainer.group.count : null}}</td>
      <td>
        <date-human-readable v-if="mapContainer.group" :value="mapContainer.group.maxDeadline" /> <br/>
        <span v-if="isOverdue(mapContainer)" class="badge badge-danger">
          {{ $t('issue.state.overdue') }}
        </span>
      </td>
      <td class="text-right">
        <a href="#" v-if="mapContainer.canLoad && !loadedMapContainers.includes(mapContainer.container)" @click="loadContainer(mapContainer.container)">
          {{ $t('actions.load_map') }}
        </a>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>
import DateHumanReadable from '../Library/View/DateHumanReadable'
import ButtonWithModal from '../Library/Behaviour/ButtonWithModal'
import MapRenderLightbox from './MapRenderLightbox'

export default {
  emits: ['load', 'load-multiple'],
  components: {
    MapRenderLightbox,
    ButtonWithModal,
    DateHumanReadable
  },
  data() {
    return {
      loadedMapContainers: []
    }
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
    isOverdue: function (mapContainer) {
      if (!mapContainer.group || !mapContainer.group.maxDeadline) {
        return false
      }

      return Date.now() > new Date(mapContainer.group.maxDeadline)
    },
    loadContainer: function (mapContainer) {
      this.$emit('load', mapContainer)
      this.loadedMapContainers.push(mapContainer)
    },
    loadAllContainers: function () {
      this.$emit('load-multiple', this.notYetLoadedMapContainers)
      this.loadedMapContainers.push(...this.notYetLoadedMapContainers)
    }
  },
  computed: {
    loadableMapContains: function () {
      return this.mapContainerWithGroups.filter(mc => mc.canLoad)
    },
    notYetLoadedMapContainers: function () {
      return this.loadableMapContains
          .map(mc => mc.container)
          .filter(c => !this.loadedMapContainers.includes(c))
    },
    mapContainerWithGroups: function () {
      let groupLookup = {}
      this.mapGroups.forEach(mg => groupLookup[mg.entity] = mg)

      let mapContainerWithGroups = [];
      let dirtyLevel = -1
      for (let i = this.mapContainers.length-1; i >= 0; i--) {
        let mapContainer = this.mapContainers[i];

        const group = groupLookup[mapContainer.entity['@id']];

        const canLoad = group && group.count > 0
        if (canLoad) {
          dirtyLevel = mapContainer.level
        }

        // ensures parents are displayed too
        const show = dirtyLevel === mapContainer.level
        if (show) {
          dirtyLevel--;
        }

        const mapContainerWithGroup = {
          group,
          show,
          canLoad,
          container: mapContainer
        }

        mapContainerWithGroups.push(mapContainerWithGroup);
      }

      mapContainerWithGroups = mapContainerWithGroups.reverse()

      return mapContainerWithGroups.filter(mc => mc.show)
    },
  },
}
</script>
