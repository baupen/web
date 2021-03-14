<template>
  <button-with-modal-confirm
      :button-disabled="posting" :title="$t('_action.associate_construction_manager.title')" :can-confirm="canConfirm"
      @confirm="confirm">
    <construction-manager-email-form @update="post = $event" />
  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
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
    confirm: function () {
      this.posting = true
      api.postConstructionManager(this.post)
          .then(constructionManager => {
            const constructionManagers = this.constructionSite.constructionManagers.filter(c => c['@id'] !== constructionManager['@id'])
            constructionManagers.push(constructionManager['@id'])

            api.patch(this.constructionSite, { constructionManagers }, this.$t('_action.associate_construction_manager.associated'))
                .then(_ => {
                  this.posting = false
                  this.$emit('added', constructionManager)
                })
          })
    }
  }
}
</script>
