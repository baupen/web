<template>
  <table class="table table-striped table-hover">
    <thead>
    <tr>
      <th>{{$t('map._name')}}</th>
      <th>{{$t('issue._plural')}}</th>
      <th>{{$t('craftsman.next_deadline')}}</th>
      <th class="w-minimal"></th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="mapContainer in mapContainerWithGroups" :key="mapContainer.entity['@id']">
      <td>
        {{ '&nbsp;&nbsp;&nbsp;&nbsp;'.repeat(mapContainer.level) }}
        {{mapContainer.entity.name}}
      </td>
      <td>{{mapContainer.group ? mapContainer.group.count : null}}</td>
      <td>
        <date-human-readable v-if="mapContainer.group" :value="mapContainer.group.maxDeadline" /> <br/>
        <span v-if="isOverdue(mapContainer)" class="badge badge-danger">
          {{ $t('issue.state.overdue') }}
        </span>
      </td>
      <td>
        <button v-if="canShowMapContainer(mapContainer)" class="btn btn-secondary" @click="showMapContainer(mapContainer)">
          anzeigen
        </button>
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>
import DateHumanReadable from '../Library/View/DateHumanReadable'
import { mapTransformer } from '../../services/transformers'
export default {
  emits: ['show'],
  components: {
    DateHumanReadable
  },
  data() {
    return {
      shownMapContainers: []
    }
  },
  props: {
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
    canShowMapContainer: function (mapContainer) {
      return mapContainer.group && mapContainer.group.count > 0 && !this.shownMapContainers.includes(mapContainer);
    },
    showMapContainer: function (mapContainer) {
      this.shownMapContainers.push(mapContainer)
      this.$emit('show', mapContainer)
    }
  },
  computed: {
    mapContainerWithGroups: function () {
      let groupLookup = {}
      this.mapGroups.forEach(mg => groupLookup[mg.entity] = mg)

      let mapContainerWithGroups = [];
      this.mapContainers.forEach(mapContainer => {
        mapContainerWithGroups.push(Object.assign(mapContainer, {
          group: groupLookup[mapContainer.entity['@id']]
        }));
      })

      return mapContainerWithGroups
    },
  },
}
</script>
