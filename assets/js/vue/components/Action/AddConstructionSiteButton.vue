<template>
  <div>
    <button-with-modal-confirm
        :title="$t('switch.actions.create_construction_site')" :can-confirm="canConfirm" :button-disabled="buttonDisabled"
        @confirm="confirm">
      <construction-site-form @update="constructionSite = $event" :construction-sites="constructionSites" />
    </button-with-modal-confirm>
  </div>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import ConstructionSiteForm from '../Form/ConstructionSiteForm'
import { api } from '../../services/api'

export default {
  components: {
    ConstructionSiteForm,
    ButtonWithModalConfirm
  },
  emits: ['added'],
  data () {
    return {
      constructionSite: null,
      posting: false,
    }
  },
  props: {
    disabled: {
      type: Boolean,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: true
    },
    constructionSites: {
      type: Array,
      required: false
    }
  },
  computed: {
    canConfirm: function () {
      return !!this.constructionSite
    },
    buttonDisabled: function () {
      return this.disabled || this.posting
    }
  },
  methods: {
    confirm: function () {
      this.posting = true;
      const payload = Object.assign({}, this.constructionSite, { constructionManagers: [this.constructionManagerIri]})
      api.postConstructionSite(payload, this.$t('switch.messages.success.added_construction_site'))
          .then(constructionSite => {
            this.$emit('added', constructionSite)
            this.posting = false;
          })
    }
  }
}
</script>
