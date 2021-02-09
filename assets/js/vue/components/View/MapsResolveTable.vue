<template>
  <table class="table table-striped table-hover">
    <thead>
    <tr>
      <th>{{$t('map._name')}}</th>
      <th>{{$t('issue._plural')}}</th>
      <th>{{$t('craftsman.next_deadline')}}</th>
      <th class="w-minimal">
        <a href="#" v-if="notShownMapContainers.length > 0" @click="showAllContainers">
          {{ $t('actions.load_all_maps') }}
        </a>
      </th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="mapContainer in mapContainerWithGroups" :key="mapContainer.container.entity['@id']">
      <td>
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
        <a href="#"  v-if="notShownMapContainers.includes(mapContainer.container)" @click="showContainer(mapContainer.container)">
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

export default {
  emits: ['show', 'show-multiple'],
  components: {
    ButtonWithModal,
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
    showContainer: function (mapContainer) {
      this.shownMapContainers.push(mapContainer)
      this.$emit('show', mapContainer)
    },
    showAllContainers: function () {
      this.$emit('show-multiple', this.notShownMapContainers)
      this.shownMapContainers.push(...this.notShownMapContainers)
    }
  },
  computed: {
    notShownMapContainers: function () {
      return this.mapContainerWithGroups.filter(mc => mc.group && mc.group.count > 0)
          .map(mcc => mcc.container)
          .filter(mc => !this.shownMapContainers.includes(mc))
    },
    mapContainerWithGroups: function () {
      let groupLookup = {}
      this.mapGroups.forEach(mg => groupLookup[mg.entity] = mg)

      let mapContainerWithGroups = [];
      this.mapContainers.forEach(mapContainer => {
        mapContainerWithGroups.push({
          group: groupLookup[mapContainer.entity['@id']],
          container: mapContainer
        });
      })

      return mapContainerWithGroups
    },
  },
}
</script>
