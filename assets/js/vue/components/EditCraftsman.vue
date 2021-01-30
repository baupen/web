<template>
  <h2 class="mt-5">{{ $t('craftsman._plural') }}</h2>
  <p>{{ $t('edit.craftsmen_help') }}</p>
  <add-craftsman-button :construction-site="constructionSite" />
  <craftsmen-edit-table class="mt-2" :construction-site="constructionSite" />
</template>

<script>
import AddCraftsmanButton from './Action/AddCraftsmanButton'
import CraftsmenEditTable from './View/CraftsmenEditTable'

export default {
  components: {
    CraftsmenEditTable,
    AddCraftsmanButton
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  }
}
</script>
