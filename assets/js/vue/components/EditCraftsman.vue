<template>
  <h2 class="mt-5">{{ $t('craftsman._plural') }}</h2>
  <p>{{ $t('edit.craftsmen_help') }}</p>
  <add-craftsman-button :construction-site="constructionSite" @added="craftsmen.push($event)" />
  <craftsmen-edit-table class="mt-2" :construction-site="constructionSite" :craftsmen="notDeletedCraftsmen" />
</template>

<script>
import AddCraftsmanButton from './Action/AddCraftsmanButton'
import CraftsmenEditTable from './View/CraftsmenEditTable'
import { api } from '../services/api'

export default {
  components: {
    CraftsmenEditTable,
    AddCraftsmanButton
  },
  data() {
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
  computed: {
    notDeletedCraftsmen: function () {
      if (!this.craftsmen) {
        return null;
      }

      return this.craftsmen.filter(c => !c.isDeleted)
    }
  },
  mounted () {
    api.getCraftsmen(this.constructionSite)
        .then(craftsmen => this.craftsmen = craftsmen)
  }
}
</script>
