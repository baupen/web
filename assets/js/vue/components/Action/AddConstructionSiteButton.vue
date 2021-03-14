<template>
  <div>
    <button-with-modal-confirm
        :button-disabled="posting || !constructionSites" :title="$t('_action.add_construction_site.title')" :can-confirm="canConfirm"
        @confirm="confirm">
      <construction-site-form :construction-sites="constructionSites" @update="post = $event" />
      <image-form @update="image = $event" />
    </button-with-modal-confirm>
  </div>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import ConstructionSiteForm from '../Form/ConstructionSiteForm'
import { api } from '../../services/api'
import FileForm from '../Form/FileForm'
import ImageForm from '../Form/ImageForm'

export default {
  emits: ['added'],
  components: {
    ImageForm,
    FileForm,
    ConstructionSiteForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      image: null,
      post: null,
      posting: false,
    }
  },
  props: {
    constructionManagerIri: {
      type: String,
      required: true
    },
    constructionSites: {
      type: Array,
      default: []
    }
  },
  computed: {
    canConfirm: function () {
      return !!this.post
    }
  },
  methods: {
    confirm: function () {
      this.posting = true;
      const payload = Object.assign({}, this.post, { constructionManagers: [this.constructionManagerIri]})

      let successMessage = this.$t('_action.add_construction_site.added')

      if (!this.image) {
        api.postConstructionSite(payload, successMessage)
            .then(constructionSite => {
              this.posting = false
              this.$emit('added', constructionSite)
            })
        return
      }

      api.postConstructionSite(payload)
          .then(constructionSite => {
            api.postConstructionSiteImage(constructionSite, this.image, successMessage)
                .then(_ => {
                  this.posting = false
                  this.$emit('added', constructionSite)
                })
          })
    }
  }
}
</script>
