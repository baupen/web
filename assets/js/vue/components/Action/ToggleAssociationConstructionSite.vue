<template>
  <button type="button" class="btn btn-toggle"
          :class="{'active': ownsConstructionSite}"
          @click="toggleAssociation">
    <div class="handle"></div>
  </button>
</template>

<script>

import { api } from '../../services/api'

export default {
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: true
    }
  },
  computed: {
    ownsConstructionSite: function () {
      return this.constructionSite.constructionManagers.includes(this.constructionManagerIri)
    },
  },
  methods: {
    toggleAssociation: function () {
      const ownsConstructionSite = this.ownsConstructionSite
      const constructionManagers = this.constructionSite.constructionManagers.filter(cm => cm !== this.constructionManagerIri)
      if (ownsConstructionSite) {
        api.patch(this.constructionSite, { constructionManagers }, this.$t('_action.toggle_association_construction_site.associated'))
      } else {
        constructionManagers.push(this.constructionManagerIri)
        api.patch(this.constructionSite, { constructionManagers }, this.$t('_action.toggle_association_construction_site.dissociated'))
      }
    }
  }
}
</script>
