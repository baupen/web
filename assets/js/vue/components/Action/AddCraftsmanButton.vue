<template>
  <button-with-modal-confirm
      :button-disabled="posting" :title="$t('_action.add_craftsman')" :can-confirm="canConfirm"
      @confirm="confirm">
    <craftsman-form @update="post = $event" />
  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import CraftsmanForm from '../Form/CraftsmanForm'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'

export default {
  emits: ['added'],
  components: {
    ButtonWithModalConfirm,
    CraftsmanForm

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
  },
  computed: {
    canConfirm: function () {
      return !!this.post
    }
  },
  methods: {
    confirm: function () {
      this.posting = true
      const payload = Object.assign({}, this.post, {constructionSite: this.constructionSite["@id"]})
      api.postCraftsman(payload, this.$t('_action.messages.success.craftsman_added'))
          .then(craftsman => {
            this.posting = false
            this.$emit('added', craftsman)
          })
    }
  }
}
</script>
