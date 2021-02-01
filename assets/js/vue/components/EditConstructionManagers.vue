<template>
  <h2 class="mt-5">{{ $t('construction_manager._plural') }}</h2>
  <p>{{ $t('edit.construction_managers_help') }}</p>
  <div class="btn-group">
    <associate-construction-manager-button :construction-site="constructionSite" @added="constructionManagers.push($event)" />
  </div>
  <construction-manager-association-table class="mt-2" :construction-site="constructionSite" :construction-managers="constructionManagers" @removed="remove" />
</template>

<script>
import AddCraftsmanButton from './Action/AddCraftsmanButton'
import CraftsmenEditTable from './View/CraftsmenEditTable'
import { api } from '../services/api'
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
