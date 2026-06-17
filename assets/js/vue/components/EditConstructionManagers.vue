<template>
  <loading-indicator :spin="!constructionManagers">
    <h2 class="mt-5">{{ $t('construction_manager._plural') }}</h2>
    <p>{{ $t('edit.construction_managers_help') }}</p>
    <div class="btn-group" v-if="constructionManager?.canAssociateSelf">
      <associate-construction-manager-button :construction-site="constructionSite" @added="constructionManagers.push($event)" />
    </div>
    <p class="alert alert-info" v-else>
      {{ $t('edit.construction_managers_disabled') }}
    </p>
    <construction-manager-association-table class="mt-2" :construction-site="constructionSite" :construction-managers="constructionManagers" :self-construction-manager="constructionManager" @removed="remove" />
  </loading-indicator>
</template>

<script>
import { api } from '../domain/api'
import AssociateConstructionManagerButton from './Action/AssociateConstructionManagerButton'
import ConstructionManagerAssociationTable from './View/ConstructionManagerAssociationTable'
import LoadingIndicator from './Library/View/LoadingIndicator.vue'
import { store } from '../domain/stores'

export default {
  components: {
    LoadingIndicator,
    ConstructionManagerAssociationTable,
    AssociateConstructionManagerButton,
  },
  data() {
    return {
      constructionManagers: null
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: true
    },
  },
  computed: {
    constructionManager: function () {
      return this.constructionManagers?.find(cm => cm['@id'] === this.constructionManagerIri)
    }
  },
  methods: {
    remove: function (constructionManager) {
      this.constructionManagers = this.constructionManagers.filter(cm => cm !== constructionManager)
    }
  },
  mounted () {
    this.constructionManagers = [...store.constructionManagers]
  }
}
</script>
