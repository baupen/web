<template>
  <button-with-modal-confirm
      :button-disabled="patching" :title="$t('_action.edit_craftsman.title')"
      :confirm-title="$t('_action.save_changes')" :can-confirm="canConfirm"
      @confirm="confirm" >
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'pencil']" />
    </template>

    <craftsman-form @update="patch = $event" :template="craftsman" />
  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import CraftsmanForm from '../Form/CraftsmanForm'

export default {
  components: {
    CraftsmanForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      patch: null,
      patching: false
    }
  },
  props: {
    craftsman: {
      type: Object,
      required: true
    }
  },
  computed: {
    canConfirm: function () {
      return !!(this.patch && Object.keys(this.patch).length)
    }
  },
  methods: {
    confirm: function () {
      this.$emit('edit', this.patch)

      this.patching = true
      api.patch(this.craftsman, this.patch, this.$t('_action.edit_craftsman.saved'))
          .then(_ => { this.patching = false})
    }
  }
}
</script>
