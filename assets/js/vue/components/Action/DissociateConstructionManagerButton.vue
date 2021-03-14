<template>
  <button-with-modal-confirm
      color="danger"
      :title="$t('_action.dissociate_construction_manager.title')"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']" />
    </template>

    <p class="alert alert-info">
      {{ $t('_action.dissociate_construction_manager.help') }}
    </p>

  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import DeleteForm from '../Form/DeleteForm'

export default {
  emits: ['dissociated'],
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
      api.patch(this.constructionSite, { constructionManagers }, this.$t('_action.dissociate_construction_manager.dissociated'))
        .then(_ => {
          this.$emit('dissociated')
          this.patching = false
        })
    }
  }
}
</script>
