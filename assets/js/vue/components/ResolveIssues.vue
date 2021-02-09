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
            <div class="card-body">
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
                    <button v-if="canShowMapContainer(mapContainer)" class="btn btn-secondary" @click="shownMapContainers.push(mapContainer)">
                      anzeigen
                    </button>
                  </td>
                </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <issues-resolve-masonry class="mt-5"
          v-for="mapContainer in shownMapContainers"
          :map="mapContainer.entity" :map-context="getMapContext(mapContainer)"
          :craftsman="craftsman" :construction-site="constructionSite" :construction-managers="constructionManagers" />
    </loading-indicator-secondary>
  </div>
</template>

<script>
import ConstructionSitesEnterMasonry from './View/ConstructionSitesEnterMasonry'
import ConstructionSitesParticipationTable from './View/ConstructionSitesParticipationTable'
import AddConstructionSiteButton from './Action/AddConstructionSiteButton'
import LoadingIndicator from './Library/View/LoadingIndicator'
import { addNonDuplicatesById, api, iriToId } from '../services/api'
import LoadingIndicatorSecondary from './Library/View/LoadingIndicatorSecondary'
import IssuesResolveMasonry from './View/MapIssuesResolveMasonry'
import { createEntityIdLookup } from '../services/algorithms'
import { mapTransformer } from '../services/transformers'
import DateHumanReadable from './Library/View/DateHumanReadable'
import ButtonWithModal from './Library/Behaviour/ButtonWithModal'

export default {
  components: {
    ButtonWithModal,
    DateHumanReadable,
    IssuesResolveMasonry,
    LoadingIndicatorSecondary,
    ConstructionSitesEnterMasonry,
    ConstructionSitesParticipationTable,
    AddConstructionSiteButton,
    LoadingIndicator
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
    isOverdue: function (mapContainer) {
      if (!mapContainer.group || !mapContainer.group.maxDeadline) {
        return false
      }

      return Date.now() > new Date(mapContainer.group.maxDeadline)
    },
    canShowMapContainer: function (mapContainer) {
      return mapContainer.group && mapContainer.group.count > 0 && !this.shownMapContainers.includes(mapContainer);
    },
    getMapContext: function (mapContainer) {
      let parentList = ''
      let currentContainer = mapContainer
      while (currentContainer.parent) {
        parentList = currentContainer.parent.name + ' > ' + parentList
        currentContainer = currentContainer.parent
      }
      return parentList
    }
  },
  computed: {
    groupCountSum: function () {
      return this.mapGroups.reduce((sum, entry) => sum + entry.count, 0)
    },
    isLoading: function () {
      return !this.maps || !this.mapGroups
    },
    flatMaps: function () {
      return mapTransformer.flatHierarchy(this.maps)
    },
    mapContainerWithGroups: function () {
      let groupLookup = {}
      this.mapGroups.forEach(mg => groupLookup[mg.entity] = mg)

      let mapContainerWithGroups = [];
      this.flatMaps.forEach(mapContainer => {
        mapContainerWithGroups.push(Object.assign(mapContainer, {
          group: groupLookup[mapContainer.entity['@id']]
        }));
      })

      return mapContainerWithGroups
    },
  },
  mounted () {
    api.getConstructionManagers(this.constructionSite)
        .then(constructionManagers => {
          this.constructionManagers = constructionManagers
        })

    api.getMaps(this.constructionSite)
        .then(maps => this.maps = maps)

    api.getIssuesGroup(this.constructionSite, 'map', {craftsman: iriToId(this.craftsman['@id'])})
        .then(groups => this.mapGroups = groups)
  }
}
</script>
