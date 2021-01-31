<template>
  <button-with-modal-confirm
      color="danger"
      :title="$t('actions.dissociate_construction_manager')"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']" />
    </template>

    <p class="alert alert-info">
      {{ $t('actions.dissociate_construction_manager_help') }}
    </p>

  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import DeleteForm from '../Form/DeleteForm'

export default {
  components: {
    DeleteForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      patching: false
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
    }
  },
  methods: {
    confirm: function () {
      this.patching = true
      const constructionManagers = this.constructionSite.constructionManagers.filter(cm => cm !== this.constructionManager['@id'])
      api.patch(this.constructionSite, { constructionManagers }, this.$t('action.messages.success.construction_manager_dissociated'))
        .then(_ => {
          this.patching = false
        })
    }
  }
}
</script>
