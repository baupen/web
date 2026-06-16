<template>
  <div>
    <button-with-modal-confirm
        :button-disabled="posting" :title="$t('_action.add_sample_construction_site.title')" :can-confirm="canConfirm"
        @confirm="confirm">
      <sample-construction-site-form :template="template" @update="post = $event" />
    </button-with-modal-confirm>
  </div>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import { api } from '../../domain/api'
import SampleConstructionSiteForm from '../Form/SampleConstructionSiteForm.vue'

export default {
  emits: ['added'],
  components: {
    SampleConstructionSiteForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      image: null,
      post: null,
      posting: false
    }
  },
  props: {
    constructionManagerIri: {
      type: String,
      required: true
    }
  },
  computed: {
    canConfirm: function () {
      return !!this.post
    },
    template: function () {
      return {
        name: 'Beispiel: Sanierung EG & OG',
        streetAddress: "Bahnhofsstrasse 1",
        postalCode: 3000,
        locality: 'Bern'
      }
    }
  },
  methods: {
    confirm: function () {
      this.posting = true
      const payload = Object.assign({}, this.template, this.post)

      const successMessage = this.$t('_action.add_sample_construction_site.added')

      api.postSampleConstructionSite(payload, successMessage)
        .then(constructionSite => {
          this.posting = false
          this.$emit('added', constructionSite)
        })
    }
  }
}
</script>
