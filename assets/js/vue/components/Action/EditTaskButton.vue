<template>
  <button-with-modal-confirm
      color="secondary"
      :button-disabled="patching" :title="$t('_action.edit_task.title')"
      :confirm-title="$t('_action.save_changes')" :can-confirm="canConfirm"
      @confirm="confirm" >
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'pencil']" />
    </template>

    <task-form @update="patch = $event" :template="task" />
  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import TaskForm from "../Form/TaskForm.vue";

export default {
  components: {
    TaskForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      patch: null,
      patching: false
    }
  },
  props: {
    task: {
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
      api.patch(this.task, this.patch, this.$t('_action.edit_craftsman.saved'))
          .then(_ => { this.patching = false})
    }
  }
}
</script>
