<template>
  <button-with-modal-confirm
      color="danger"
      :title="$t('_action.remove_task.title')" :can-confirm="canConfirm"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']" />
    </template>

    <p class="alert alert-info">
      {{ $t('_action.remove_task.help') }}
    </p>

    <delete-form @update="canConfirm = $event" />

  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import DeleteForm from '../Form/DeleteForm'
import IssueSummaryBadges from '../View/IssueSummaryBadges'

export default {
  components: {
    DeleteForm,
    ButtonWithModalConfirm
  },
  emits: ['removed'],
  data () {
    return {
      canConfirm: false
    }
  },
  props: {
    task: {
      type: Object,
      required: true
    }
  },
  methods: {
    confirm: function () {
      api.delete(this.task, this.$t('_action.remove_task.removed'))
      this.$emit('removed', this.task)
    },
  },
}
</script>
