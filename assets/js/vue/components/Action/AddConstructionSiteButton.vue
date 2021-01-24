<template>
  <div>
    <button-with-modal-confirm :title="$t('switch.actions.create_construction_site')" :can-confirm="canConfirm" @confirm="confirm">
      <construction-site-form @update="constructionSite = $event" :construction-sites="constructionSites"/>
    </button-with-modal-confirm>
  </div>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import ConstructionSiteForm from '../Form/ConstructionSiteForm'
export default {
  components: {
    ConstructionSiteForm,
    ButtonWithModalConfirm
  },
  emits: ['add'],
  data() {
    return {
      constructionSite: null
    }
  },
  props: {
    constructionSites: {
      type: Array,
      required: true
    }
  },
  computed: {
    canConfirm: function () {
      return !!this.constructionSite
    }
  },
  methods: {
    confirm: function () {
      this.$emit('add', this.constructionSite)

      // reset state for next display
      this.constructionSite = null
    }
  }
}
</script>
