<template>
  <button-with-modal-confirm
      :button-disabled="posting" :title="$t('_action.associate_construction_manager.title')" :can-confirm="canConfirm"
      @confirm="confirm">
    <construction-manager-email-form @update="post = $event" />
  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../domain/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import ConstructionManagerEmailForm from '../Form/ConstructionManagerEmailForm'

export default {
  emits: ['added'],
  components: {
    ConstructionManagerEmailForm,
    ButtonWithModalConfirm,
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
    confirm: async function () {
      this.posting = true
      try {
        const constructionManager = await api.postConstructionManager(this.post)
        if (!this.constructionSite.constructionManagers.some(c => c === constructionManager['@id'])) {
          const constructionManagers = [...this.constructionSite.constructionManagers, constructionManager['@id']]
          await api.patch(this.constructionSite, { constructionManagers }, this.$t('_action.associate_construction_manager.associated'))
          this.$emit('added', constructionManager)
        }

      } catch (e) {
        console.log(e)
      }

      this.posting = false
    }
  }
}
</script>
