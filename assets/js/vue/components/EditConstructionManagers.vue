<template>
  <h2 class="mt-5">{{ $t('construction_manager._plural') }}</h2>
  <p>{{ $t('edit.construction_managers_help') }}</p>
  <div class="btn-group" v-if="constructionManager.canAssociateSelf">
    <associate-construction-manager-button :construction-site="constructionSite" @added="constructionManagers.push($event)" />
  </div>
  <p class="alert alert-info" v-else>
    {{ $t('edit.construction_managers_disabled') }}
  </p>
  <construction-manager-association-table class="mt-2" :construction-site="constructionSite" :construction-managers="constructionManagers" :self-construction-manager="constructionManager" @removed="remove" />
</template>

<script>
import AddCraftsmanButton from './Action/AddCraftsmanButton'
import CraftsmenEditTable from './View/CraftsmenEditTable'
import { addNonDuplicatesById, api } from '../services/api'
import ImportCraftsmenButton from './Action/ImportCraftsmenButton'
import AssociateConstructionManagerButton from './Action/AssociateConstructionManagerButton'
import ConstructionManagerAssociationTable from './View/ConstructionManagerAssociationTable'

export default {
  components: {
    ConstructionManagerAssociationTable,
    AssociateConstructionManagerButton,
    ImportCraftsmenButton,
    CraftsmenEditTable,
    AddCraftsmanButton
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
    constructionManager: {
      type: Object,
      required: true
    },
  },
  methods: {
    remove: function (constructionManager) {
      this.constructionManagers = this.constructionManagers.filter(cm => cm !== constructionManager)
    }
  },
  mounted () {
    this.constructionManagers = [this.constructionManager]
    api.getConstructionManagers(this.constructionSite)
    .then(addConstructionManagers => {
      addNonDuplicatesById(this.constructionManagers, addConstructionManagers)
    })
  }
}
</script>
