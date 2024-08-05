<template>
  <h2 class="mt-5">{{ $t('craftsman._plural') }}</h2>
  <p>{{ $t('edit.craftsmen_help') }}</p>
  <div class="btn-group">
    <add-craftsman-button
        :construction-site="constructionSite" @added="craftsmen.push($event)" />
  </div>
  <div class="btn-group ms-2">
    <import-craftsmen-button
        :construction-site="constructionSite" :craftsmen="notDeletedCraftsmen"
        @imported="reload" />
    <export-craftsmen-button v-if="craftsmen && craftsmen.length > 0" :craftsmen="notDeletedCraftsmen" />
  </div>
  <craftsmen-edit-table class="mt-2 mb-0" :construction-site="constructionSite" :craftsmen="notDeletedCraftsmen" />
</template>

<script>
import AddCraftsmanButton from './Action/AddCraftsmanButton'
import CraftsmenEditTable from './View/CraftsmenEditTable'
import { api } from '../services/api'
import ImportCraftsmenButton from './Action/ImportCraftsmenButton'
import ExportCraftsmenButton from './Action/ExportCraftsmenButton'

export default {
  components: {
    ExportCraftsmenButton,
    ImportCraftsmenButton,
    CraftsmenEditTable,
    AddCraftsmanButton
  },
  data () {
    return {
      craftsmen: null
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  methods: {
    reload: function () {
      this.craftsmen = null

      api.getCraftsmen(this.constructionSite)
          .then(craftsmen => this.craftsmen = craftsmen)
    }
  },
  computed: {
    notDeletedCraftsmen: function () {
      if (!this.craftsmen) {
        return null
      }

      return this.craftsmen.filter(c => !c.isDeleted)
    }
  },
  mounted () {
    this.reload()
  }
}
</script>
