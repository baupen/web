<template>
  <h2 class="mt-5">{{ $t('construction_manager._plural') }}</h2>
  <p>{{ $t('edit.construction_managers_help') }}</p>
  <div class="btn-group" v-if="constructionManager?.canAssociateSelf">
    <associate-construction-manager-button :construction-site="constructionSite" @added="constructionManagers.push($event)" />
  </div>
  <p class="alert alert-info" v-else>
    {{ $t('edit.construction_managers_disabled') }}
  </p>
  <construction-manager-association-table class="mt-2" :construction-site="constructionSite" :construction-managers="constructionManagers" :self-construction-manager="constructionManager" @removed="remove" />
</template>

<script>
import { api } from '../domain/api'
import AssociateConstructionManagerButton from './Action/AssociateConstructionManagerButton'
import ConstructionManagerAssociationTable from './View/ConstructionManagerAssociationTable'

export default {
  components: {
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
      return this.constructionManagers?.find(cm => cm.iri === this.constructionManagerIri)
    }
  },
  methods: {
    remove: function (constructionManager) {
      this.constructionManagers = this.constructionManagers.filter(cm => cm !== constructionManager)
    }
  },
  mounted () {
    api.getConstructionManagers(this.constructionSite)
    .then(constructionManagers => {
      this.constructionManagers = constructionManagers
    })
  }
}
</script>
