<template>
  <div>
    <button-with-modal-confirm :title="$t('switch.actions.create_construction_site')" :can-confirm="canConfirm" @confirm="confirm">
      <construction-site-edit @update="constructionSite = $event" :construction-sites="constructionSites"/>
    </button-with-modal-confirm>
  </div>
</template>

<script>

import ConstructionSiteEdit from "./Edit/ConstructionSiteEdit";
import ButtonWithModalConfirm from "./Behaviour/ButtonWithModalConfirm";

export default {
  components: {ButtonWithModalConfirm, ConstructionSiteEdit},
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
      return this.constructionSite !== null
    }
  },
  methods: {
    confirm: function () {
      this.$emit('add', this.constructionSite)
    }
  }
}
</script>
