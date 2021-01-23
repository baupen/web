<template>
  <button-with-modal-confirm
      :button-disabled="patching" :title="$t('actions.edit_craftsman')"
      :confirm-title="$t('actions.save_changes')" :can-confirm="canConfirm"
      @confirm="confirm" >
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'pencil']" />
    </template>

    <craftsman-form @update="patch = $event" :template="craftsman" />
  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Behaviour/ButtonWithModalConfirm'
import CraftsmanForm from '../Edit/CraftsmanForm'
import { api } from '../../services/api'
import { displaySuccess } from '../../services/notifiers'

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
      return this.patch && Object.keys(this.patch).length
    }
  },
  methods: {
    confirm: function () {
      this.$emit('edit', this.patch)

      this.patching = true
      api.patch(this.craftsman, this.patch, this.$t('actions.messages.success.craftsman_saved'))
          .then(_ => { this.patching = false})
    }
  }
}
</script>
