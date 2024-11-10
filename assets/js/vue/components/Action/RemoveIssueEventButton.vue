<template>
  <button-with-modal-confirm
      color="danger"
      :title="$t('_action.remove_issue_event.title')" :can-confirm="canConfirm"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']" />
    </template>

    <p class="alert alert-info">
      {{ $t('_action.remove_issue_event.help') }}
    </p>

    <delete-form @update="canConfirm = $event" />

  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import DeleteForm from '../Form/DeleteForm'
import { displaySuccess } from '../../services/notifiers'

export default {
  emits: ['removed'],
  components: {
    DeleteForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      canConfirm: false,
    }
  },
  props: {
    issueEvent: {
      type: Object,
      required: true
    }
  },
  methods: {
    confirm: function () {
      api.delete(this.issueEvent, this.$t('_action.remove_issue_event.removed'))
          .then(_ => this.$emit('removed'))
    },
  }
}
</script>
