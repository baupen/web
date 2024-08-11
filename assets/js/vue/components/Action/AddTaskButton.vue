<template>
  <button-with-modal-confirm
      :button-disabled="posting" :title="$t('_action.add_task.title')" :can-confirm="canConfirm"
      @confirm="confirm">
    <task-form @update="post = $event" />
  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import TaskForm from '../Form/TaskForm'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'

export default {
  emits: ['added'],
  components: {
    ButtonWithModalConfirm,
    TaskForm

  },
  data () {
    return {
      post: null,
      posting: false
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: true
    },
  },
  computed: {
    canConfirm: function () {
      return !!this.post
    }
  },
  methods: {
    confirm: function () {
      this.posting = true
      const payload = Object.assign({}, this.post, {
        constructionSite: this.constructionSite["@id"],
        createdBy: this.constructionManagerIri,
        createdAt: (new Date()).toISOString()
      })

      api.postTask(payload, this.$t('_action.add_task.added'))
          .then(task => {
            this.posting = false
            this.$emit('added', task)
          })
    }
  }
}
</script>
