<template>
  <div class="table-responsive">
    <table class="table table-striped table-condensed table-bordered table-sm-small table-hover modal-width-expanding">
      <thead>
      <tr>
        <th>{{ $t('_view.maps_resolve.issues_by_map') }}</th>
        <th class="text-right">{{ $t('craftsman.next_deadline') }}</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="mapContainer in mapContainers" :key="mapContainer.entity['@id']">
        <td class="text-nowrap">
          <span :class="'space-'+mapContainer.level"></span>
          <a v-if="mapContainer.issueCount" href="#" @click="$emit('scroll-to-map', mapContainer.entity)">
            {{ mapContainer.entity.name }}
          </a>
          <template v-else>
            {{ mapContainer.entity.name }}
          </template>
          <span v-if="mapContainer.issueCount" class="badge badge-secondary ml-1">
            {{ mapContainer.issueCount }}
          </span>
        </td>
        <td class="text-right">
          <date-human-readable v-if="mapContainer.issueCount > 0" :value="mapContainer.maxDeadline" />
          <template v-if="isOverdue(mapContainer)">
            <br/>
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
import { mapTransformer } from '../../services/transformers'

export default {
  emits: ['scroll-to-map'],
  components: {
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
    maps: {
      type: Array,
      required: true
    },
    issuesGroupByMap: {
      type: Array,
      required: true
    }
  },
  methods: {
    isOverdue: function (mapContainer) {
      if (!mapContainer.maxDeadline) {
        return false
      }

      return Date.now() > new Date(mapContainer.maxDeadline)
    }
  },
  computed: {
    mapContainers: function () {
      let properties = mapTransformer.PROPERTY_HAS_CHILD_WITH_ISSUES | mapTransformer.PROPERTY_LEVEL
      return mapTransformer.orderedListWithIssuesGroups(this.maps, this.issuesGroupByMap, properties)
          .filter(m => m.issueCount > 0 || m.hasChildWithIssues)
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

.modal-width-expanding {
  width: auto;
  min-width: 500px;
}
</style>
