<template>
  <div>
    <button-with-modal-confirm :title="$t('switch.actions.create_construction_site')" :can-confirm="canConfirm" @confirm="confirm">
      <construction-site-edit v-model="constructionSite" @valid="canConfirm = $event" :construction-sites="constructionSites"/>
    </button-with-modal-confirm>
  </div>
</template>

<script>

import ConstructionSiteEdit from "./Edit/ConstructionSiteEdit";
import ButtonWithModalConfirm from "./Behaviour/ButtonWithModalConfirm";

const defaultConstructionSite = {}

export default {
  components: {ButtonWithModalConfirm, ConstructionSiteEdit},
  emits: ['add'],
  data() {
    return {
      constructionSite: Object.assign({}, defaultConstructionSite),
      show: false,
      canConfirm: true
    }
  },
  props: {
    constructionSites: {
      type: Array,
      required: true
    }
  },
  methods: {
    confirm: function () {
      this.$emit('add', this.constructionSite)
      this.constructionSite = Object.assign({}, defaultConstructionSite)
    }
  }
}
</script>
