<template>
  <button-with-modal-confirm
      :button-disabled="posting" :title="$t('actions.add_craftsman')" :can-confirm="canConfirm"
      @confirm="confirm">
    <craftsman-form @update="post = $event" />
  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Behaviour/ButtonWithModalConfirm'
import CraftsmanForm from '../Edit/CraftsmanForm'
import { api } from '../../services/api'
import { displaySuccess } from '../../services/notifiers'

export default {
  emits: ['added'],
  components: {
    CraftsmanForm,
    ButtonWithModalConfirm
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
      return this.post
    }
  },
  methods: {
    confirm: function () {
      this.posting = true
      const payload = Object.assign({}, this.post, {constructionSite: this.constructionSite["@id"]})
      api.postCraftsman(payload, this.$t('actions.messages.success.craftsman_added'))
          .then(craftsman => {
            this.posting = false
            this.$emit('added', craftsman)
          })
    }
  }
}
</script>
